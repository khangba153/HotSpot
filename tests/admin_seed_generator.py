"""Offline A03 administrative snapshot/generator validation; no DB connection."""
import copy
import hashlib
import json
import subprocess
import tempfile
import unicodedata
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
SOURCE = ROOT / 'database/sources/administrative-units-2025.json'
OUTPUT = ROOT / 'database/seed_admin_full.sql'
manifest = json.loads((SOURCE.parent / 'manifest.json').read_text(encoding='utf-8'))
data = json.loads(SOURCE.read_text(encoding='utf-8'))
checks = []

def check(name, ok):
    checks.append({'name': name, 'status': 'PASS' if ok else 'FAIL'})
    print(('PASS ' if ok else 'FAIL ') + name)

check('Pinned raw snapshot SHA256', hashlib.sha256(SOURCE.read_bytes()).hexdigest() == manifest['sha256'])
check('34 provinces / 3321 wards', len(data['provinces']) == 34 and len(data['wards']) == 3321)
before = OUTPUT.read_bytes()
process = subprocess.run(['php', str(ROOT / 'tools/generate_admin_seed.php')], capture_output=True)
check('Offline generator succeeds', process.returncode == 0)
check('Generated SQL exactly reproducible', OUTPUT.read_bytes() == before)
sql = OUTPUT.read_text(encoding='utf-8')
check('Generated SQL NFC', unicodedata.normalize('NFC', sql) == sql)
check('Hoang Sa is DAC_KHU', "('20333','48','Đặc khu Hoàng Sa','DAC_KHU',1)" in sql)
check('No unknown ward type', "'DAC_KHU'" in sql and "'PHUONG'" in sql and "'XA'" in sql)

def mutate_count(d): d['wards'].pop()
def mutate_province_count(d): d['provinces'].pop()
def mutate_ward_duplicate(d): d['wards'][1]['code'] = d['wards'][0]['code']
def mutate_province_duplicate(d): d['provinces'][1]['id'] = d['provinces'][0]['id']
def mutate_code_set(d): d['provinces'][0]['code'] = '99'
def mutate_parent(d): d['wards'][0]['province_id'] = 9999
def mutate_code_length(d): d['wards'][0]['code'] = '004'
def mutate_unknown_type(d): d['wards'][0]['name'] = 'Thị trấn Unsupported'
def mutate_empty_name(d): d['wards'][0]['name'] = ''
def mutate_long_name(d): d['wards'][0]['name'] = 'Xã ' + 'A' * 151

with tempfile.TemporaryDirectory(prefix='hotspot-admin-generator-') as tmp:
    for mutate in [mutate_count, mutate_province_count, mutate_ward_duplicate,
                   mutate_province_duplicate, mutate_code_set, mutate_parent,
                   mutate_code_length, mutate_unknown_type, mutate_empty_name,
                   mutate_long_name]:
        invalid = copy.deepcopy(data)
        mutate(invalid)
        fixture = Path(tmp) / 'invalid.json'
        fixture.write_text(json.dumps(invalid, ensure_ascii=False), encoding='utf-8')
        result = subprocess.run(['php', str(ROOT / 'tools/generate_admin_seed.php'), str(fixture)], capture_output=True)
        check(mutate.__name__ + ' rejected; valid SQL preserved', result.returncode != 0 and OUTPUT.read_bytes() == before)

result_file = ROOT / 'tests/results/admin-generator.json'
result_file.parent.mkdir(exist_ok=True)
result_file.write_text(json.dumps({'checks': checks, 'total': len(checks),
                                  'passed': sum(c['status'] == 'PASS' for c in checks)}, indent=2), encoding='utf-8')
raise SystemExit(0 if all(c['status'] == 'PASS' for c in checks) else 1)
