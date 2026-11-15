# Usage: python3 md2docx.py article.md out.docx
# Format: first "# " line = title. "## " H2, "### " H3, "- " bullet, "1. " numbered, blank line separates paragraphs.
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
    parts = re.split(r'(\*\*[^*]+\*\*|\[[^\]]+\]\([^)]+\))', text)
    for part in parts:
        if not part: continue
        if part.startswith('**'):
            r = p.add_run(part[2:-2]); r.bold = True
        elif part.startswith('['):
            m = re.match(r'\[([^\]]+)\]\(([^)]+)\)', part)
            add_hyperlink(p, m.group(2), m.group(1))
        else:
            p.add_run(part)
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
for ln in lines:
    s = ln.rstrip()
    if s == '===META===':
        d.add_paragraph().add_run().add_break(WD_BREAK.PAGE)
        d.add_heading('Publishing details', level=2); meta = True; continue
    if not s.strip(): continue
    if meta:
        p = d.add_paragraph(); k, _, v = s.partition(':'); r = p.add_run(k + ':'); r.bold = True; p.add_run(' ' + v.strip()); continue
    if s.startswith('# '): d.add_heading(s[2:], level=0 if False else 1); continue
    if s.startswith('## '): d.add_heading(s[3:], level=2); continue
    if s.startswith('### '): d.add_heading(s[4:], level=3); continue
    if s.startswith('- '): p = d.add_paragraph(style='List Bullet'); add_runs(p, s[2:]); continue
    m = re.match(r'^(\d+)\. (.*)', s)
    if m: p = d.add_paragraph(style='List Number'); add_runs(p, m.group(2)); continue
    p = d.add_paragraph(); add_runs(p, s)
d.save(out); print(out)
