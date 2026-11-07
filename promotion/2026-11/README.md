# VTurnU promotion campaign, November 2026

Guest articles and B2B directory listings for https://vturnu.com, run from 7 to 30 November 2026.

## Files

| File | What it holds |
| --- | --- |
| `tracker.csv` | One row per publisher or directory: date, type, site, url, contact route, topic or listing, status, live URL, link attribute, next action, notes |
| `log.md` | One dated entry per day: what was done, what was verified, what waits on whom |
| `articles/README.md` | Index of the articles: title, publisher, status and where the manuscript is kept |
| `listings/` | Field-by-field listing packs, one file per directory |
| `reports/` | The final report |

## Status words

Articles: `researched`, `drafted`, `awaiting approval`, `pitched`, `submitted`, `accepted`, `in revision`, `scheduled`, `published`, `rejected`, `no reply`, `blocked`.

Listings: `researched`, `pack ready`, `awaiting owner sign-up`, `submitted`, `pending review`, `live`, `rejected`, `skip (already listed)`, `blocked`.

`live` and `published` are used only after the public page was loaded while logged out and the listing or article was seen on it. The link attribute is recorded from the page's HTML as `followed`, `nofollow`, `ugc`, `sponsored` or `missing`.

## Privacy rules for this folder

This repository is public, so the folder never holds:

- editors' names or email addresses (they stay in the session messages to the owner)
- unpublished manuscripts or Word files (see `.gitignore`; a public copy can fail a publisher's originality check)
- the street address, GSTIN, LLPIN or any login detail (the listing packs point to the Listings Kit instead)

The folder is listed in `.vercelignore`, so it is never deployed.
