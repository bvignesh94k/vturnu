import sys
for f in sys.argv[1:]:
    t = open(f, encoding='utf-8').read()
    bad = [(i+1, l) for i, l in enumerate(t.splitlines()) if '—' in l or '–' in l]
    print(f, 'DASHES:' if bad else 'clean', bad[:5])
