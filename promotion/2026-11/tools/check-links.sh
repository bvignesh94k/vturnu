#!/usr/bin/env bash
# Loads each URL (one per line on stdin or as arguments) with a browser user
# agent, logged out, and prints: HTTP status, final URL after redirects, and
# the page title. vturnu.com refuses non-browser user agents.
UA="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36"
check() {
  local url="$1" body code final title
  body=$(curl -sSL --compressed --max-time 30 -A "$UA" -H "Accept-Language: en" \
    -w '\n__META__%{http_code} %{url_effective}' "$url" 2>/dev/null)
  code=$(printf '%s' "$body" | sed -n 's/^__META__\([0-9]*\) .*/\1/p' | tail -1)
  final=$(printf '%s' "$body" | sed -n 's/^__META__[0-9]* \(.*\)/\1/p' | tail -1)
  title=$(printf '%s' "$body" | tr '\n' ' ' | sed -n 's/.*<title[^>]*>\([^<]*\)<\/title>.*/\1/p' | head -c 110)
  printf '%s | %s | %s | %s\n' "${code:-ERR}" "$url" "$final" "$title"
}
if [ "$#" -gt 0 ]; then for u in "$@"; do check "$u"; done
else while read -r u; do [ -n "$u" ] && check "$u"; done; fi
