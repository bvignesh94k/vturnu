# Helper scripts used in the cloud guest-posting run (14 and 15 Nov 2026)

These lived in the session's scratch folder, which is cleared when the container is replaced. They are kept here so a later session can reuse them. Run Python with `python3 -I`.

- `cover.cjs`: `node cover.cjs spec.json out.jpg`. Renders a featured image with Playwright from a JSON spec (size, fonts, colours, eyebrow, title, subtitle and a list of items). Fonts: the repo's `assets/fonts/poppins-*.woff2`, or `npm i @fontsource/roboto @fontsource/montserrat` in a scratch folder.
- `md2docx.py`: `python3 -I md2docx.py article.md article.docx`. Markdown to Word with real hyperlinks. A line `===META===` starts a new page of "Label: value" lines. Needs python-docx.
- `dashcheck.py`: `python3 -I dashcheck.py file`. Fails if a file contains an em dash or en dash.
- `pageinspect.py`: `python3 -I pageinspect.py URL needle`. Status, title and every link matching the needle with its rel. Used to confirm a live article and its vturnu.com link.
- `peek.py` and `readpage.py`: read a page as text, decode protected email addresses and list form fields.
- `study.cjs`: brand colours, fonts and recent articles of a host site, before designing its cover.
- `form-inspect.cjs`: lists the fields of a web form.

The environment's network allow-list applies: a site must be on the list before these can open it.
