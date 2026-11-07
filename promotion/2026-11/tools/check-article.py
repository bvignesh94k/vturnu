#!/usr/bin/env python3
"""Pre-send checks for a campaign article written in the Markdown format that
build-article-docx.js reads.

Usage: python3 -I check-article.py <article.md>

Fails (exit 1) on: em or en dashes, banned superlatives, raw URLs outside
link syntax, a sources or references section, bracket placeholders, or no
link to vturnu.com. Prints every link so each can be checked before sending.
"""
import re
import sys

BANNED = [
    r"\bbest\b", r"\bNo\.\s?1\b", r"\bnumber one\b",
    r"\b(a|the|one of the)\s+leading\b",
    r"\bleading\s+(agency|agencies|company|companies|provider|providers|firm|firms|brand|brands|expert|experts|platform|platforms|solution|solutions)\b",
    r"\bworld[- ]class\b", r"\baward[- ]winning\b", r"\bguarantee[ds]?\b",
    r"\brevolutionary\b", r"\bgame[- ]chang", r"\bunparalleled\b",
    r"\bcutting[- ]edge\b", r"\bstate[- ]of[- ]the[- ]art\b",
]
PLACEHOLDER = re.compile(r"\[(?:TBD|TODO|VERIFY|INSERT|XX+|placeholder)[^\]]*\]", re.I)
LINK = re.compile(r"\[([^\]]+)\]\((https?://[^)\s]+)\)")


def main(path):
    text = open(path, encoding="utf-8").read()
    problems = []
    for n, line in enumerate(text.splitlines(), 1):
        if re.search("[\u2013\u2014]", line):
            problems.append(f"line {n}: en or em dash")
        stripped = LINK.sub(lambda m: m.group(1), line)
        if re.search(r"https?://", stripped) and not line.startswith(("author_bio:",)):
            problems.append(f"line {n}: raw URL outside link syntax")
        if re.match(r"^#+\s*(sources|references|bibliography|further reading)\b", line, re.I):
            problems.append(f"line {n}: sources or references section")
        if PLACEHOLDER.search(line):
            problems.append(f"line {n}: placeholder")
        for pat in BANNED:
            if re.search(pat, stripped, re.I):
                problems.append(f"line {n}: banned wording /{pat}/")
    links = LINK.findall(text)
    own = [u for _, u in links if re.match(r"https://(www\.)?vturnu\.com", u)]
    if not own:
        problems.append("no link to vturnu.com")
    body = re.sub(r"^---\n.*?\n---\n", "", text, flags=re.S)
    words = len(re.findall(r"[A-Za-z0-9'’]+", LINK.sub(lambda m: m.group(1), body)))
    print(f"Body words (approx.): {words}")
    print(f"Links: {len(links)} total, {len(own)} to vturnu.com")
    for anchor, url in links:
        print(f"  - {anchor} -> {url}")
    if problems:
        print("\nPROBLEMS:")
        for p in problems:
            print("  " + p)
        return 1
    print("\nAll checks passed.")
    return 0


if __name__ == "__main__":
    sys.exit(main(sys.argv[1]))
