import re
import sys

import markdown
from xhtml2pdf import pisa

src_path = sys.argv[1]
out_path = sys.argv[2]

with open(src_path, "r", encoding="utf-8") as f:
    text = f.read()

# Strip pandoc YAML frontmatter, extract title/subtitle/date manually
title, subtitle, date = "", "", ""
m = re.match(r"^---\n(.*?)\n---\n", text, re.S)
if m:
    fm = m.group(1)
    for line in fm.splitlines():
        if line.startswith("title:"):
            title = line.split(":", 1)[1].strip().strip('"')
        elif line.startswith("subtitle:"):
            subtitle = line.split(":", 1)[1].strip().strip('"')
        elif line.startswith("date:"):
            date = line.split(":", 1)[1].strip().strip('"')
    text = text[m.end():]

body_html = markdown.markdown(
    text,
    extensions=["extra", "sane_lists", "toc"],
)

css = """
@page { size: A4; margin: 2cm; }
body { font-family: Helvetica, Arial, sans-serif; font-size: 10.5pt; line-height: 1.4; color: #222; }
h1 { font-size: 20pt; color: #2e3d3c; margin-bottom: 2pt; }
h2 { font-size: 14pt; color: #2e3d3c; border-bottom: 1pt solid #bfeceb; padding-bottom: 3pt; margin-top: 16pt; }
h3 { font-size: 11.5pt; color: #477e7b; margin-top: 12pt; margin-bottom: 3pt; }
p { margin: 4pt 0; }
ul, ol { margin: 4pt 0 8pt 0; padding-left: 18pt; }
li { margin-bottom: 2pt; }
code { background-color: #f3f4f4; padding: 1pt 3pt; font-size: 9.5pt; }
pre { background-color: #f3f4f4; padding: 6pt; font-size: 9pt; }
hr { border: none; border-top: 1pt solid #e1eeed; margin: 10pt 0; }
.titlepage { text-align: center; margin-bottom: 10pt; }
.titlepage h1 { border-bottom: none; }
.titlepage .subtitle { font-size: 13pt; color: #477e7b; }
.titlepage .date { font-size: 10pt; color: #5f6665; margin-top: 4pt; }
"""

title_html = f"""
<div class="titlepage">
  <h1>{title}</h1>
  <div class="subtitle">{subtitle}</div>
  <div class="date">{date}</div>
</div>
<hr/>
"""

full_html = f"""<!DOCTYPE html>
<html><head><meta charset="utf-8"><style>{css}</style></head>
<body>{title_html}{body_html}</body></html>"""

with open(out_path, "wb") as f:
    result = pisa.CreatePDF(src=full_html, dest=f)

if result.err:
    sys.exit(1)
print("PDF generated:", out_path)
