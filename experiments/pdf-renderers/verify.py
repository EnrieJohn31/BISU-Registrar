"""Independent Poppler/pypdf checks and two mixed-paper sample packs."""
import hashlib
import json
import re
import statistics
import subprocess
import unicodedata
import xml.etree.ElementTree as ET
from pathlib import Path

from PIL import Image, ImageChops, ImageDraw, ImageFont
from pypdf import PdfReader, PdfWriter
import pypdf

WORK = Path('/work')
OUTPUT = Path('/output')
STAMP = 'SAMPLE – NOT VALID'
fixtures = json.loads((WORK / 'fixtures.json').read_text())
metrics = {engine: json.loads((WORK / engine / 'metrics.json').read_text()) for engine in ('dompdf', 'chromium')}
assert metrics['dompdf']['font_sha256'] == metrics['chromium']['font_sha256'], 'Renderer font files differ.'
OUTPUT.mkdir(parents=True, exist_ok=True)
(OUTPUT / 'previews').mkdir(exist_ok=True)


def normalized(text):
    # A printed line break after an existing hyphen is not a changed name.
    text = unicodedata.normalize('NFC', text)
    return re.sub(r'(?<=\w)-\s+(?=\w)', '-', ' '.join(text.split()))


def command(*args):
    return subprocess.run(args, check=True, capture_output=True, text=True).stdout


def embedded_fonts(path):
    rows = command('pdffonts', str(path)).splitlines()[2:]
    assert rows, f'No text fonts: {path.name}'
    for row in rows:
        match = re.search(r'\b(yes|no)\s+(yes|no)\s+(yes|no)\s+\d+\s+\d+\s*$', row)
        assert match and match[1] == 'yes' and match[3] == 'yes', f'Font not embedded/mapped: {row}'
    return rows


results = []
for engine in ('dompdf', 'chromium'):
    writer = PdfWriter()
    preview_paths = []
    pack_text = []
    for fixture in fixtures:
        key = fixture['id']
        path = WORK / engine / f'{key}.pdf'
        reader = PdfReader(path)
        page_count = len(reader.pages)
        expected_size = (595.276, 841.89) if fixture['paper'] == 'A4' else (612, 1008)
        assert (2 <= page_count <= 3) if fixture['rows'] else page_count == 1, f'Unexpected pages: {engine}/{key}: {page_count}'
        texts = [normalized(page.extract_text()) for page in reader.pages]
        all_text = ' '.join(texts)
        assert fixture['name'] in all_text, f'Name/Unicode missing: {engine}/{key}'
        assert 'END OF SYNTHETIC FIXTURE (Demo)' in texts[-1], f'Missing final content: {engine}/{key}'
        for row in fixture['rows']:
            assert all_text.count(row) == 1, f'Duplicate/missing row: {engine}/{key}/{row}'
        for index, (page, text) in enumerate(zip(reader.pages, texts), 1):
            width, height = float(page.mediabox.width), float(page.mediabox.height)
            assert abs(width - expected_size[0]) < 1.5 and abs(height - expected_size[1]) < 1.5, f'Wrong paper: {engine}/{key}'
            assert STAMP in text, f'Missing sample stamp: {engine}/{key}/{index}'
            assert f'Page {index} of {page_count}' in text, f'Missing footer: {engine}/{key}/{index}'
            if fixture['rows']:
                assert 'Test row (Demo)' in text and 'Content check (Demo)' in text, f'Missing repeated header: {engine}/{key}/{index}'
        font_rows = embedded_fonts(path)
        assert all(any(name in row for row in font_rows) for name in ('DejaVuSans', 'DejaVuSerif')), f'Bundled typefaces missing: {engine}/{key}'
        image_count = len(reader.pages[0].images)
        assert image_count >= 1, f'Local demo image missing: {engine}/{key}'
        bbox_path = WORK / f'{engine}-{key}-bbox.html'
        subprocess.run(['pdftotext', '-bbox', str(path), str(bbox_path)], check=True)
        bounds = ET.parse(bbox_path).getroot()
        for page in bounds.iter('{http://www.w3.org/1999/xhtml}page'):
            width, height = float(page.attrib['width']), float(page.attrib['height'])
            header_words = set()
            for word in page.iter('{http://www.w3.org/1999/xhtml}word'):
                box = {k: float(word.attrib[k]) for k in ('xMin', 'xMax', 'yMin', 'yMax')}
                assert box['xMin'] >= -1 and box['yMin'] >= -1 and box['xMax'] <= width + 1 and box['yMax'] <= height + 1, f'Text outside paper: {engine}/{key}: {word.text}'
                if box['yMax'] < 60:
                    header_words.add(word.text)
            assert {'SAMPLE', 'NOT', 'VALID'} <= header_words, f'Running stamp missing from top margin: {engine}/{key}'
        prefix = OUTPUT / 'previews' / f'{engine}-{key}'
        for previous in prefix.parent.glob(prefix.name + '-*.png'):
            previous.unlink()
        subprocess.run(['pdftoppm', '-r', '110', '-png', str(path), str(prefix)], check=True, capture_output=True)
        images = sorted(prefix.parent.glob(prefix.name + '-*.png'))
        assert len(images) == page_count
        for image_path in images:
            with Image.open(image_path) as image:
                assert image.width > 800 and image.height > 1200
            preview_paths.append((image_path, fixture['id']))
        samples = next(row['milliseconds'] for row in metrics[engine]['fixtures'] if row['id'] == key)
        results.append({
            'engine': engine, 'fixture': key, 'pages': page_count,
            'paper_points': [float(reader.pages[0].mediabox.width), float(reader.pages[0].mediabox.height)],
            'bytes': path.stat().st_size, 'sha256': hashlib.sha256(path.read_bytes()).hexdigest(),
            'milliseconds': samples, 'median_ms': round(statistics.median(samples), 2),
            'rows_verified': len(fixture['rows']), 'font_rows': font_rows, 'first_page_images': image_count,
            'checks': 'PASS: paper, page count, Unicode/name, all rows, repeated headers, top-margin stamp/footer on every page, embedded/mapped bundled fonts, local image, text bounds, Poppler rendering',
        })
        first_page = len(writer.pages)
        for page in reader.pages:
            writer.add_page(page)
        writer.add_outline_item(f"{fixture['paper']} / {fixture['variant']} (Demo)", first_page)
        pack_text.extend(texts)
        print(f'PASS {engine}/{key}: {page_count} pages, {len(font_rows)} embedded/mapped fonts, {statistics.median(samples):.2f} ms median')
    writer.add_metadata({'/Title': f'{engine.title()} - synthetic renderer comparison', '/Subject': STAMP})
    pack_path = OUTPUT / f'{engine}-sample-pack.pdf'
    writer.write(pack_path)
    packed = PdfReader(pack_path)
    assert [normalized(page.extract_text()) for page in packed.pages] == pack_text, 'Pack assembly changed text.'
    embedded_fonts(pack_path)
    pack_prefix = WORK / f'{engine}-pack'
    for previous in WORK.glob(pack_prefix.name + '-*.png'):
        previous.unlink()
    subprocess.run(['pdftoppm', '-r', '110', '-png', str(pack_path), str(pack_prefix)], check=True, capture_output=True)
    packed_images = sorted(WORK.glob(pack_prefix.name + '-*.png'))
    assert len(packed_images) == len(preview_paths)
    for packed_image, (reviewed_image, _) in zip(packed_images, preview_paths):
        with Image.open(packed_image) as actual, Image.open(reviewed_image) as expected:
            assert actual.size == expected.size and ImageChops.difference(actual.convert('RGB'), expected.convert('RGB')).getbbox() is None, 'Pack assembly changed page pixels.'
        packed_image.unlink()

    # Contact sheets are overview aids; individual page PNGs support full visual review.
    thumb_width, thumb_height, caption = 360, 535, 36
    columns = 4
    rows = (len(preview_paths) + columns - 1) // columns
    sheet = Image.new('RGB', (columns * thumb_width, rows * (thumb_height + caption)), '#dce4e9')
    draw = ImageDraw.Draw(sheet)
    font = ImageFont.truetype('/opt/pdf-fonts/DejaVuSans.ttf', 13)
    for index, (image_path, label) in enumerate(preview_paths):
        x, y = (index % columns) * thumb_width, (index // columns) * (thumb_height + caption)
        with Image.open(image_path) as image:
            image.thumbnail((thumb_width - 16, thumb_height - 16))
            sheet.paste(image, (x + (thumb_width - image.width) // 2, y + 8))
        draw.text((x + 8, y + thumb_height + 5), label + ' / ' + image_path.stem.rsplit('-', 1)[-1], fill='#213847', font=font)
    sheet.save(OUTPUT / 'previews' / f'{engine}-contact-sheet.png')

summary = {
    'status': 'automated checks passed; visual review is a separate required step',
    'fixture_count': len(fixtures), 'engines': metrics, 'results': results,
    'fixtures': fixtures, 'qa': {'pypdf': pypdf.__version__, 'poppler': subprocess.run(['pdftoppm', '-v'], capture_output=True, text=True).stderr.splitlines()[0]},
    'constraints': {'network': 'none', 'root_filesystem': 'read-only', 'cpus': 1, 'memory_bytes': 1073741824, 'fonts': 'Identical bundled DejaVu TTF hashes in both engines'},
    'pack_pixel_matches': True,
}
(OUTPUT / 'results.json').write_text(json.dumps(summary, ensure_ascii=False, indent=2), encoding='utf-8')
print('All structural/content checks passed. Two mixed-paper sample packs and page previews are ready for visual review.')
