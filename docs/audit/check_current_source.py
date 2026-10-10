"""Lint current source and inspect its front controller over localhost, without DB writes."""
from pathlib import Path
import json
import socket
import subprocess
import tempfile
import time
import urllib.request
import urllib.error

ROOT=Path(__file__).resolve().parents[2]
OUT=Path(__file__).resolve().parent
results=[]
for directory in ['app','public','services']:
    for file in sorted((ROOT/directory).rglob('*.php')):
        p=subprocess.run(['php','-l',str(file)],capture_output=True,timeout=20)
        results.append({'check':'PHP lint','file':file.relative_to(ROOT).as_posix(),
                        'status':'PASS' if p.returncode==0 else 'FAIL',
                        'bytes':file.stat().st_size,
                        'output':(p.stdout+p.stderr).decode('utf-8',errors='replace')})
with tempfile.TemporaryDirectory(prefix='hotspot-current-source-') as td:
    with socket.socket() as sock:
        sock.bind(('127.0.0.1',0)); port=sock.getsockname()[1]
    with open(Path(td)/'server.log','wb') as log:
        server=subprocess.Popen(['php','-S',f'127.0.0.1:{port}','-t','public'],cwd=ROOT,stdout=log,stderr=log)
        try:
            for attempt in range(30):
                try:
                    with urllib.request.urlopen(f'http://127.0.0.1:{port}/',timeout=5) as response:
                        body=response.read()
                        results.append({'check':'Current homepage renders application',
                                        'status':'PASS' if body.strip() else 'FAIL',
                                        'http_status':response.status,'body_bytes':len(body)})
                    break
                except urllib.error.URLError:
                    time.sleep(.2)
            else: raise RuntimeError('Audit PHP server did not become ready')
        finally:
            server.terminate(); server.wait(timeout=10)
(OUT/'current-source-checks.json').write_text(json.dumps(results,ensure_ascii=False,indent=2),encoding='utf-8')
print('PHP lint:',sum(r['status']=='PASS' for r in results if r['check']=='PHP lint'),
      '/',sum(r['check']=='PHP lint' for r in results))
print('Homepage:',results[-1])
