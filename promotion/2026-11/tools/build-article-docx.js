#!/usr/bin/env node
// Builds a clean Word file for a guest article from a small Markdown file.
//
// Usage: node build-article-docx.js <article.md> <output.docx>
//
// Input format:
//   ---
//   abstract: ...             (optional standfirst, printed in italics under the title)
//   seo_title: ...            (optional, printed under the title)
//   meta_description: ...     (optional)
//   slug: ...                 (optional)
//   author_heading: About the author
//   author_bio: Text with [anchor](https://link) links
//   ---
//   # Article title
//   Body in Markdown: ## and ### headings, paragraphs, "- " bullets,
//   "1. " numbered items, **bold**, *italic* and [anchor](https://url) links.
//
// Raw URLs are never printed: every link becomes a hyperlink on its anchor
// text. The script refuses input that contains an em dash or en dash.

const fs = require('fs');
const path = require('path');
const {
  Document, Packer, Paragraph, TextRun, ExternalHyperlink, HeadingLevel,
  AlignmentType, LevelFormat, PageBreak, BorderStyle,
} = require('docx');

const [, , inPath, outPath] = process.argv;
if (!inPath || !outPath) {
  console.error('Usage: node build-article-docx.js <article.md> <output.docx>');
  process.exit(1);
}

const raw = fs.readFileSync(inPath, 'utf8').replace(/\r\n/g, '\n');
if (/[\u2013\u2014]/.test(raw)) {
  const line = raw.split('\n').findIndex((l) => /[\u2013\u2014]/.test(l)) + 1;
  console.error(`Refusing to build: en or em dash found on line ${line}.`);
  process.exit(2);
}

// Front matter.
let meta = {};
let body = raw;
const fm = raw.match(/^---\n([\s\S]*?)\n---\n/);
if (fm) {
  body = raw.slice(fm[0].length);
  for (const line of fm[1].split('\n')) {
    const m = line.match(/^([a-z_]+):\s*(.*)$/);
    if (m) meta[m[1]] = m[2].trim();
  }
}

const FONT = 'Calibri';
const BODY_SIZE = 22; // half-points: 11 pt

// Inline Markdown: **bold**, *italic*, [text](url).
function inline(text, base = {}) {
  const runs = [];
  const re = /(\*\*([^*]+)\*\*|\*([^*]+)\*|\[([^\]]+)\]\((https?:\/\/[^)\s]+)\))/g;
  let last = 0;
  let m;
  while ((m = re.exec(text)) !== null) {
    if (m.index > last) runs.push(new TextRun({ text: text.slice(last, m.index), ...base }));
    if (m[2]) runs.push(new TextRun({ text: m[2], bold: true, ...base }));
    else if (m[3]) runs.push(new TextRun({ text: m[3], italics: true, ...base }));
    else if (m[4]) {
      runs.push(new ExternalHyperlink({
        link: m[5],
        children: [new TextRun({ text: m[4], style: 'Hyperlink', ...base })],
      }));
    }
    last = re.lastIndex;
  }
  if (last < text.length) runs.push(new TextRun({ text: text.slice(last), ...base }));
  return runs;
}

const children = [];
const lines = body.split('\n');
let para = [];
let numberedRef = 0; // each numbered list restarts at 1

function flushPara() {
  if (!para.length) return;
  const text = para.join(' ').trim();
  if (text) children.push(new Paragraph({ children: inline(text) }));
  para = [];
}

let titleDone = false;
let inNumbered = false;
for (const line of lines) {
  const t = line.trim();
  if (!t) { flushPara(); inNumbered = false; continue; }
  let m;
  if ((m = t.match(/^# (.+)$/))) {
    flushPara();
    children.push(new Paragraph({ heading: HeadingLevel.TITLE, children: inline(m[1]) }));
    if (!titleDone) {
      titleDone = true;
      if (meta.abstract) {
        children.push(new Paragraph({
          spacing: { before: 0, after: 200 },
          children: inline(meta.abstract, { italics: true, size: 24, color: '404040' }),
        }));
      }
      const seo = [
        ['SEO title', meta.seo_title],
        ['Meta description', meta.meta_description],
        ['Slug', meta.slug],
      ].filter(([, v]) => v);
      seo.forEach(([k, v], i) => {
        children.push(new Paragraph({
          spacing: { before: 0, after: i === seo.length - 1 ? 240 : 40 },
          border: i === seo.length - 1
            ? { bottom: { style: BorderStyle.SINGLE, size: 4, color: 'BFBFBF', space: 8 } }
            : undefined,
          children: [
            new TextRun({ text: `${k}: `, bold: true, size: 18, color: '595959' }),
            new TextRun({ text: v, size: 18, color: '595959' }),
          ],
        }));
      });
    }
  } else if ((m = t.match(/^### (.+)$/))) {
    flushPara();
    children.push(new Paragraph({ heading: HeadingLevel.HEADING_2, children: inline(m[1]) }));
  } else if ((m = t.match(/^## (.+)$/))) {
    flushPara();
    children.push(new Paragraph({ heading: HeadingLevel.HEADING_1, children: inline(m[1]) }));
  } else if ((m = t.match(/^[-*] (.+)$/))) {
    flushPara();
    children.push(new Paragraph({ numbering: { reference: 'bullets', level: 0 }, children: inline(m[1]) }));
  } else if ((m = t.match(/^\d+\. (.+)$/))) {
    flushPara();
    if (!inNumbered) { numberedRef += 1; inNumbered = true; }
    children.push(new Paragraph({ numbering: { reference: 'numbers', level: 0, instance: numberedRef }, children: inline(m[1]) }));
  } else {
    para.push(t);
  }
}
flushPara();

// Author page.
if (meta.author_bio) {
  children.push(new Paragraph({ children: [new PageBreak()] }));
  children.push(new Paragraph({ heading: HeadingLevel.HEADING_1, children: [new TextRun(meta.author_heading || 'About the author')] }));
  children.push(new Paragraph({ children: inline(meta.author_bio) }));
}

const doc = new Document({
  creator: 'VTurnU',
  title: (body.match(/^# (.+)$/m) || [])[1] || 'Article',
  styles: {
    default: { document: { run: { font: FONT, size: BODY_SIZE }, paragraph: { spacing: { after: 160, line: 276 } } } },
    paragraphStyles: [
      { id: 'Title', name: 'Title', basedOn: 'Normal', next: 'Normal', quickFormat: true,
        run: { font: FONT, size: 40, bold: true, color: '1F1F1F' },
        paragraph: { spacing: { before: 0, after: 160 }, keepNext: true } },
      { id: 'Heading1', name: 'Heading 1', basedOn: 'Normal', next: 'Normal', quickFormat: true,
        run: { font: FONT, size: 28, bold: true, color: '1F1F1F' },
        paragraph: { spacing: { before: 320, after: 120 }, keepNext: true, outlineLevel: 0 } },
      { id: 'Heading2', name: 'Heading 2', basedOn: 'Normal', next: 'Normal', quickFormat: true,
        run: { font: FONT, size: 24, bold: true, color: '1F1F1F' },
        paragraph: { spacing: { before: 240, after: 80 }, keepNext: true, outlineLevel: 1 } },
    ],
    characterStyles: [
      { id: 'Hyperlink', name: 'Hyperlink', basedOn: 'DefaultParagraphFont',
        run: { color: '0B57D0', underline: { type: 'single' } } },
    ],
  },
  numbering: {
    config: [
      { reference: 'bullets', levels: [{ level: 0, format: LevelFormat.BULLET, text: '•', alignment: AlignmentType.LEFT,
        style: { paragraph: { indent: { left: 720, hanging: 360 } } } }] },
      { reference: 'numbers', levels: [{ level: 0, format: LevelFormat.DECIMAL, text: '%1.', alignment: AlignmentType.LEFT,
        style: { paragraph: { indent: { left: 720, hanging: 360 } } } }] },
    ],
  },
  sections: [{
    properties: { page: { size: { width: 11906, height: 16838 }, margin: { top: 1440, right: 1440, bottom: 1440, left: 1440 } } },
    children,
  }],
});

Packer.toBuffer(doc).then((buf) => {
  fs.mkdirSync(path.dirname(path.resolve(outPath)), { recursive: true });
  fs.writeFileSync(outPath, buf);
  console.log(`Wrote ${outPath} (${buf.length} bytes)`);
});
