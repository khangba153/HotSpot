"""Read-only comparison evidence for the local db_hospot draft.

Run from repository root: python -X utf8 docs/audit/read_draft_db.py
Only SELECT statements are executed. No application records/password hashes are exported.
Requires the existing XAMPP MySQL client and the locally available root connection.
"""
from pathlib import Path
import datetime
import hashlib
import json
import subprocess

ROOT = Path(__file__).resolve().parents[2]
OUT = Path(__file__).resolve().parent
CLIENT = Path(r'C:\xampp\mysql\bin\mysql.exe')
BASE = [str(CLIENT), '--default-character-set=utf8mb4', '--host=127.0.0.1',
        '--port=3306', '--user=root', '--batch', '--raw', '--skip-column-names']

def select(query):
    if not query.lstrip().upper().startswith('SELECT '):
        raise ValueError('Only SELECT queries are permitted by this audit script')
    p = subprocess.run(BASE + ['--execute=' + query], capture_output=True, timeout=20)
    if p.returncode:
        raise RuntimeError(p.stderr.decode('utf-8', errors='replace'))
    return [json.loads(line) for line in p.stdout.decode('utf-8').splitlines() if line.strip()]

queries = {
    'server': "SELECT JSON_OBJECT('version',VERSION(),'sql_mode',@@sql_mode,'time_zone',@@time_zone)",
    'tables': "SELECT JSON_OBJECT('table',TABLE_NAME,'engine',ENGINE,'collation',TABLE_COLLATION) FROM information_schema.TABLES WHERE TABLE_SCHEMA='db_hospot' ORDER BY TABLE_NAME",
    'columns': "SELECT JSON_OBJECT('table',TABLE_NAME,'column',COLUMN_NAME,'type',COLUMN_TYPE,'nullable',IS_NULLABLE,'default',COLUMN_DEFAULT,'extra',EXTRA) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='db_hospot' ORDER BY TABLE_NAME,ORDINAL_POSITION",
    'constraints': "SELECT JSON_OBJECT('table',TABLE_NAME,'name',CONSTRAINT_NAME,'type',CONSTRAINT_TYPE) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA='db_hospot' ORDER BY TABLE_NAME,CONSTRAINT_NAME",
    'indexes': "SELECT JSON_OBJECT('table',TABLE_NAME,'name',INDEX_NAME,'non_unique',NON_UNIQUE,'sequence',SEQ_IN_INDEX,'column',COLUMN_NAME) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA='db_hospot' ORDER BY TABLE_NAME,INDEX_NAME,SEQ_IN_INDEX",
    'foreign_keys': "SELECT JSON_OBJECT('table',k.TABLE_NAME,'name',k.CONSTRAINT_NAME,'column',k.COLUMN_NAME,'parent_table',k.REFERENCED_TABLE_NAME,'parent_column',k.REFERENCED_COLUMN_NAME,'update_rule',r.UPDATE_RULE,'delete_rule',r.DELETE_RULE) FROM information_schema.KEY_COLUMN_USAGE k JOIN information_schema.REFERENTIAL_CONSTRAINTS r ON r.CONSTRAINT_SCHEMA=k.CONSTRAINT_SCHEMA AND r.CONSTRAINT_NAME=k.CONSTRAINT_NAME AND r.TABLE_NAME=k.TABLE_NAME WHERE k.TABLE_SCHEMA='db_hospot' AND k.REFERENCED_TABLE_NAME IS NOT NULL ORDER BY k.TABLE_NAME,k.CONSTRAINT_NAME,k.ORDINAL_POSITION",
    'routines': "SELECT JSON_OBJECT('name',ROUTINE_NAME,'type',ROUTINE_TYPE,'body',ROUTINE_DEFINITION,'sql_mode',SQL_MODE,'security_type',SECURITY_TYPE) FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA='db_hospot' ORDER BY ROUTINE_NAME",
    'parameters': "SELECT JSON_OBJECT('routine',SPECIFIC_NAME,'position',ORDINAL_POSITION,'name',PARAMETER_NAME,'mode',PARAMETER_MODE,'type',DTD_IDENTIFIER) FROM information_schema.PARAMETERS WHERE SPECIFIC_SCHEMA='db_hospot' ORDER BY SPECIFIC_NAME,ORDINAL_POSITION",
}
snapshot = {'captured_at': datetime.datetime.now(datetime.timezone(datetime.timedelta(hours=7))).isoformat(),
            'database': 'db_hospot', 'access': 'SELECT metadata and counts only',
            'queries': queries}
for key, query in queries.items():
    snapshot[key] = select(query)
snapshot['row_counts'] = []
for item in snapshot['tables']:
    table = item['table']
    if table not in ['roles','users','categories','districts','place_statuses','places',
                     'place_images','review_statuses','reviews','favorites']:
        raise ValueError('Unexpected table: stop instead of querying an unreviewed identifier')
    snapshot['row_counts'] += select("SELECT JSON_OBJECT('table','" + table +
                                     "','count',COUNT(*)) FROM `db_hospot`.`" + table + "`")
(OUT/'draft-db-metadata.json').write_text(json.dumps(snapshot,ensure_ascii=False,indent=2),encoding='utf-8')
manifest = []
for file in sorted(ROOT.rglob('*')):
    if not file.is_file() or '.git' in file.relative_to(ROOT).parts or OUT in file.parents:
        continue
    manifest.append({'file':file.relative_to(ROOT).as_posix(),'bytes':file.stat().st_size,
                     'sha256':hashlib.sha256(file.read_bytes()).hexdigest()})
(OUT/'source-inventory.json').write_text(json.dumps(manifest,ensure_ascii=False,indent=2),encoding='utf-8')
print('Server:', snapshot['server'][0]['version'])
print('Tables:', len(snapshot['tables']), 'Routines:', len(snapshot['routines']))
print('Row counts:', {r['table']:r['count'] for r in snapshot['row_counts']})
print('Evidence saved in docs/audit; no DB writes or routine CALLs executed.')
