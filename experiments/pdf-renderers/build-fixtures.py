"""Create labelled synthetic inputs; there is no connection to Laravel or real records."""
import hashlib
import html
import json
from pathlib import Path

from PIL import Image, ImageDraw, ImageFont

WORK = Path('/work')
FONTS = Path('/opt/pdf-fonts')
STAMP = 'SAMPLE – NOT VALID'
for folder in ('html', 'assets', 'dompdf', 'chromium', 'previews'):
    (WORK / folder).mkdir(parents=True, exist_ok=True)

badge = Image.new('RGB', (144, 72), '#eaf1f5')
draw = ImageDraw.Draw(badge)
draw.rectangle((1, 1, 142, 70), outline='#264d65', width=2)
draw.text((72, 36), 'DEMO', fill='#264d65', anchor='mm', font=ImageFont.truetype(str(FONTS / 'DejaVuSans-Bold.ttf'), 27))
badge.save(WORK / 'assets/demo-mark.png')

css = '''
@font-face { font-family: DemoSans; src: url(file:///opt/pdf-fonts/DejaVuSans.ttf); font-weight: normal; }
@font-face { font-family: DemoSans; src: url(file:///opt/pdf-fonts/DejaVuSans-Bold.ttf); font-weight: bold; }
@font-face { font-family: DemoSerif; src: url(file:///opt/pdf-fonts/DejaVuSerif-Bold.ttf); font-weight: bold; }
@page { size: PAPER; margin: 24mm 18mm 20mm; }
* { box-sizing: border-box; }
body { font-family: DemoSans, sans-serif; font-size: 10pt; line-height: 1.5; color: #213847; margin: 0; }
.banner { position: fixed; top: -17mm; left: 0; right: 0; border-bottom: 1pt solid #ab2946; padding-bottom: 4mm; color: #ab2946; font-size: 11pt; font-weight: bold; }
.banner span { float: right; font-size: 8pt; font-weight: normal; padding-top: 2pt; }
.watermark { position: fixed; top: 88mm; left: 0; width: 100%; text-align: center; font-size: 36pt; font-weight: bold; color: #f2dce3; transform: rotate(-32deg); z-index: -1; }
.eyebrow { letter-spacing: 1.2pt; font-size: 8pt; color: #577183; margin: 0 0 8mm; }
.badge { width: 24mm; height: 12mm; float: right; }
h1 { font-family: DemoSerif, serif; font-weight: bold; font-size: 24pt; line-height: 1.2; margin: 9mm 0 5mm; }
.rule { width: 18mm; border-top: 3pt solid #42687d; margin-bottom: 8mm; }
.label { font-size: 8pt; color: #577183; margin: 0 0 2mm; }
.recipient { font-size: 18pt; font-weight: bold; line-height: 1.35; margin: 0 0 7mm; overflow-wrap: break-word; }
.description { margin: 0 0 7mm; }
table { width: 100%; border-collapse: collapse; font-size: 9pt; }
th, td { border-bottom: 0.6pt solid #cbd8df; padding: 6pt 7pt; text-align: left; vertical-align: top; }
th { background: #eaf1f5; color: #264d65; font-weight: bold; }
.facts { margin-bottom: 8mm; }
.facts th { width: 28%; }
h2 { font-size: 12pt; margin: 7mm 0 3mm; }
thead { display: table-header-group; }
tr { page-break-inside: avoid; break-inside: avoid; }
.rows td { padding: 4pt 7pt; line-height: 1.3; }
.end { font-size: 8pt; color: #577183; border-top: 1pt solid #42687d; margin-top: 7mm; padding-top: 3mm; }
'''
fixtures = []
for paper in ('A4', 'Legal'):
    for variant in ('standard', 'long-name', 'multipage'):
        key = f'{paper.lower()}-{variant}'
        name = 'Zoë Peña (Demo)'
        if variant == 'long-name':
            name = 'Maria Concepción Magdalena de los Santos-Villanueva y Fernández Ñuñez del Rosario de la Cruz (Demo)'
        rows = [f'Row {index:03d} (Demo)' for index in range(1, 61)] if variant == 'multipage' else []
        table = ''
        if rows:
            table = '<h2>Pagination exercise (Demo)</h2><table class="rows"><thead><tr><th>Test row (Demo)</th><th>Content check (Demo)</th></tr></thead><tbody>'
            table += ''.join(f'<tr><td>{row}</td><td>Repeated table layout, synthetic content only.</td></tr>' for row in rows)
            table += '</tbody></table>'
        title = f'{paper} / {variant} (Demo)'
        content = f'''<!doctype html><html lang="en"><head><meta charset="utf-8"><title>{html.escape(title)}</title><style>{css.replace('PAPER', paper)}</style></head><body>
<div class="banner">{STAMP}<span>{html.escape(title)}</span></div>
<div class="watermark">{STAMP}</div>
<img class="badge" src="file:///work/assets/demo-mark.png" alt="DEMO test badge">
<p class="eyebrow">RENDERING LAB / SYNTHETIC FIXTURE</p>
<h1>Fictional certificate</h1><div class="rule"></div>
<p class="label">RECIPIENT (DEMO)</p><p class="recipient">{html.escape(name)}</p>
<p class="description">This made-up certificate compares PDF tools. Its words, fields and markings are testing fixtures only. It asserts no academic fact and has no institutional validity.</p>
<table class="facts"><tbody><tr><th>Purpose (Demo)</th><td>Typesetting comparison only (Demo)</td></tr><tr><th>Input (Demo)</th><td>Generated synthetic content (Demo)</td></tr><tr><th>Fixture (Demo)</th><td>{html.escape(title)}</td></tr></tbody></table>
{table}<p class="end">END OF SYNTHETIC FIXTURE (Demo)<br>No seal, signatory, signature, official identifier, fee or approved template is used.</p></body></html>'''
        path = WORK / 'html' / f'{key}.html'
        path.write_text(content, encoding='utf-8')
        fixtures.append({'id': key, 'paper': paper, 'variant': variant, 'name': name, 'rows': rows, 'html_sha256': hashlib.sha256(content.encode()).hexdigest()})
(WORK / 'fixtures.json').write_text(json.dumps(fixtures, ensure_ascii=False, indent=2), encoding='utf-8')
print('Prepared six synthetic fixtures, one local DEMO image and shared bundled-font references.')
