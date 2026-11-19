# Usage: python3 md2docx.py article.md out.docx
# Format: first "# " line = title. *italic* runs, ![caption](image path) pictures and | markdown | tables are supported. "## " H2, "### " H3, "- " bullet, "1. " numbered, blank line separates paragraphs.
# A line "===META===" starts a new page; lines after it are "Label: value" pairs printed one per line.
import sys, re
from docx import Document
from docx.shared import Pt, RGBColor
from docx.enum.text import WD_BREAK
src, out = sys.argv[1], sys.argv[2]
lines = open(src, encoding='utf-8').read().splitlines()
d = Document()
import os
st = d.styles['Normal']; st.font.name = os.environ.get('DOCX_FONT','Calibri'); st.font.size = Pt(float(os.environ.get('DOCX_SIZE','11')))
if os.environ.get('DOCX_SPACING'): st.paragraph_format.line_spacing = float(os.environ['DOCX_SPACING'])
if os.environ.get('DOCX_FONT'):
    for hs in ['Heading 1','Heading 2','Heading 3','Title']:
        try: d.styles[hs].font.name = os.environ['DOCX_FONT']
        except KeyError: pass
def add_runs(p, text):
    # **bold** and [text](url) -> text (url shown as hyperlink text only)
    parts = re.split(r'(\*\*[^*]+\*\*|\*[^*]+\*|\[[^\]]+\]\([^)]+\))', text)
    for part in parts:
        if not part: continue
        if part.startswith('**'):
            r = p.add_run(part[2:-2]); r.bold = True
        elif part.startswith('*') and part.endswith('*') and len(part) > 2:
            add_runs_italic(p, part[1:-1])
        elif part.startswith('['):
            m = re.match(r'\[([^\]]+)\]\(([^)]+)\)', part)
            add_hyperlink(p, m.group(2), m.group(1))
        else:
            p.add_run(part)
def add_runs_italic(p, text):
    for part in re.split(r'(\[[^\]]+\]\([^)]+\))', text):
        if not part: continue
        if part.startswith('['):
            m = re.match(r'\[([^\]]+)\]\(([^)]+)\)', part); add_hyperlink(p, m.group(2), m.group(1))
        else:
            r = p.add_run(part); r.italic = True
def add_hyperlink(paragraph, url, text):
    from docx.oxml.shared import OxmlElement, qn
    part = paragraph.part
    r_id = part.relate_to(url, 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/hyperlink', is_external=True)
    h = OxmlElement('w:hyperlink'); h.set(qn('r:id'), r_id)
    r = OxmlElement('w:r'); rPr = OxmlElement('w:rPr')
    c = OxmlElement('w:color'); c.set(qn('w:val'), '1155CC'); rPr.append(c)
    u = OxmlElement('w:u'); u.set(qn('w:val'), 'single'); rPr.append(u)
    r.append(rPr); t = OxmlElement('w:t'); t.text = text; t.set(qn('xml:space'), 'preserve'); r.append(t); h.append(r)
    paragraph._p.append(h)
meta = False
tbl_rows = []
def flush_table():
    rows = [r for r in tbl_rows if not all(re.fullmatch(r':?-{2,}:?', c or '--') for c in r)]
    if rows:
        t = d.add_table(rows=len(rows), cols=len(rows[0])); t.style = 'Table Grid'
        for i, r in enumerate(rows):
            for j, c in enumerate(r[:len(rows[0])]):
                cell = t.cell(i, j); cell.text = ''
                para = cell.paragraphs[0]; add_runs(para, ('**' + c + '**') if i == 0 and c else c)
    tbl_rows.clear()
for ln in lines:
    s = ln.rstrip()
    if s == '===META===':
        d.add_paragraph().add_run().add_break(WD_BREAK.PAGE)
        d.add_heading('Publishing details', level=2); meta = True; continue
    if not s.strip(): continue
    if meta:
        p = d.add_paragraph(); k, _, v = s.partition(':'); r = p.add_run(k + ':'); r.bold = True; p.add_run(' ' + v.strip()); continue
    mi = re.match(r'^!\[([^\]]*)\]\(([^)]+)\)$', s.strip())
    if mi:
        from docx.shared import Inches
        img = mi.group(2) if os.path.isabs(mi.group(2)) else os.path.join(os.path.dirname(os.path.abspath(src)), mi.group(2))
        d.add_picture(img, width=Inches(6.2))
        if mi.group(1): cp = d.add_paragraph(); rr = cp.add_run(mi.group(1)); rr.italic = True
        continue
    if s.startswith('|'):
        tbl_rows.append([c.strip() for c in s.strip().strip('|').split('|')]); continue
    elif tbl_rows:
        flush_table()
    if s.startswith('# '): d.add_heading(s[2:], level=0 if False else 1); continue
    if s.startswith('## '): d.add_heading(s[3:], level=2); continue
    if s.startswith('### '): d.add_heading(s[4:], level=3); continue
    if s.startswith('- '): p = d.add_paragraph(style='List Bullet'); add_runs(p, s[2:]); continue
    m = re.match(r'^(\d+)\. (.*)', s)
    if m: p = d.add_paragraph(style='List Number'); add_runs(p, m.group(2)); continue
    p = d.add_paragraph(); add_runs(p, s)
if tbl_rows: flush_table()
d.save(out); print(out)
