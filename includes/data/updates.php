<?php
/**
 * Search updates shown at /updates/, newest first. The admin panel (Search
 * Updates) edits these and adds new ones; its records are stored in
 * content_overrides as type 'update' and laid over this list in pages.php.
 *
 * Fields: title + meta (search; includes/data/seo-meta.php overrides them),
 * h1, short (menu and card name), lede (the short answer), type (UPDATE_TYPES
 * in includes/update-kit.php), status (rolling, complete, announced, info),
 * started (Y-m-d), started_time, scope, rollout (Google's estimate in words),
 * days (that estimate in days, for the calendar), finished (Y-m-d, once Google
 * confirms), checked (when the status was last checked), source (Google's
 * announcement), date (published), modified, read, rich (a file in
 * includes/data/updates/), sections, takeaways, faqs, cta, draft.
 *
 * Facts rule: dates, durations and Google's statements come from Google's
 * Search Status Dashboard and Google Search Central, linked in the article.
 */

return [

'november-core-update-2026' => [
    'rich' => 'november-core-update-2026',
    'title' => 'Google November 2026 Core Update: Dates, Impact, Next Steps',
    'meta' => 'Google\'s November 2026 core update ran from 10 to 24 November. What it is, when to check your traffic and what to do if rankings dropped.',
    'h1' => 'Google November 2026 Core Update: What It Is and What to Do',
    'short' => 'November 2026 core update',
    'lede' => 'What happened: Google rolled out its November 2026 core update from 10 November (10:06 a.m. Pacific time) to 24 November 2026 (6:03 a.m. Pacific time), about 14 days, worldwide and in every language. The core message: this was a broad re-ranking, not a penalty, and Google gave no new guidance. If your traffic moved, do not rewrite pages in a hurry. Wait until after 1 December, compare 25 November to 1 December with 3 to 9 November in Search Console, and improve only the pages that lost ground.',
    'type' => 'Core update',
    'status' => 'complete',
    'started' => '2026-11-10',
    'started_time' => '10:06 a.m. Pacific time (11:36 p.m. IST)',
    'scope' => 'All countries and all languages',
    'rollout' => 'Google estimated up to 2 weeks; it took about 14 days',
    'days' => 14,
    'finished' => '2026-11-24',
    'checked' => '2026-11-24',
    'source' => 'https://status.search.google.com/incidents/WLxcJyC6jMWMfryxsKMc',
    'date' => '2026-11-12',
    'modified' => '2026-11-24',
    'read' => '11 min',
    'faqs' => [
        ['What is the Google November 2026 core update?', 'It is a broad change to Google\'s ranking systems that started on 10 November 2026 and applies to all countries and languages. Google described it as a regular update designed to better surface relevant, satisfying content.'],
        ['When did the November 2026 core update finish?', 'Google confirmed on its Search Status Dashboard that the rollout was complete on 24 November 2026 at 6:03 a.m. Pacific time (9:03 a.m. Eastern, 7:33 p.m. in India), after about 14 days. Google had estimated up to two weeks.'],
        ['Is a core update a penalty?', 'No. A core update changes how Google compares pages across the web and does not target specific sites. A penalty for breaking Google\'s spam policies is a manual action, and it appears in the Manual actions report in Search Console.'],
        ['How do I know if the core update affected my site?', 'The rollout is complete, so wait one more full week, which ends on 1 December. Then compare 25 November to 1 December with 3 to 9 November in Search Console, page by page, and check whether rankings, impressions or only clicks changed.'],
        ['What should I do if my traffic dropped?', 'Avoid quick fixes. Review your most affected pages against Google\'s helpful content questions, improve them for readers and give the changes time. Some improvements show within days; others take months or the next core update.'],
        ['Does the November 2026 core update affect AI Overviews?', 'Google has not said. Its documentation says there are no extra requirements to appear in AI Overviews or AI Mode beyond being indexed and eligible to show in Search with a snippet, so the same quality work applies.'],
        ['Why does the timing matter for online stores?', 'The first full week after the rollout ended includes Thanksgiving, Black Friday and Cyber Monday. Compare with the same weeks last year as well, so holiday demand is not mistaken for a ranking change.'],
        ['How many core updates has Google released in 2026?', 'Three so far: the March 2026 core update (started 27 March), the May 2026 core update (21 May) and the November 2026 core update (10 November), according to Google\'s Search Status Dashboard.'],
    ],
    'cta' => ['Not Sure What the Update Did to Your Site?', 'Send us your site and the dates your traffic changed. A senior strategist replies within one working day.', 'Ask a Strategist'],
],

];
