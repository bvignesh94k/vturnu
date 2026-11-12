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
    'meta' => 'Google\'s November 2026 core update began on 10 November and may take two weeks. What it is, how to check your traffic and what to do if rankings drop.',
    'h1' => 'Google November 2026 Core Update: What It Is and What to Do',
    'short' => 'November 2026 core update',
    'lede' => 'Google started the November 2026 core update on 10 November 2026 at 10:06 a.m. Pacific time (11:36 p.m. in India). It applies to every country and language, and Google says the rollout may take up to two weeks, which points to a finish around 24 November. A core update is a broad change to how Google ranks pages, not a penalty, and Google published no new guidance with it. Wait until it finishes, then compare a full week of Search Console data with the week before 10 November.',
    'type' => 'Core update',
    'status' => 'rolling',
    'started' => '2026-11-10',
    'started_time' => '10:06 a.m. Pacific time (11:36 p.m. IST)',
    'scope' => 'All countries and all languages',
    'rollout' => 'Up to 2 weeks',
    'days' => 14,
    'finished' => '',
    'checked' => '2026-11-12',
    'source' => 'https://status.search.google.com/incidents/WLxcJyC6jMWMfryxsKMc',
    'date' => '2026-11-12',
    'modified' => '2026-11-12',
    'read' => '11 min',
    'faqs' => [
        ['What is the Google November 2026 core update?', 'It is a broad change to Google\'s ranking systems that started on 10 November 2026 and applies to all countries and languages. Google described it as a regular update designed to better surface relevant, satisfying content.'],
        ['When will the November 2026 core update finish?', 'Google said the rollout may take up to two weeks from 10 November, which points to around 24 November. Recent core updates took between about 12 and 18 days. Google\'s Search Status Dashboard shows the confirmed finish date, and we update this page when Google confirms it.'],
        ['Is a core update a penalty?', 'No. A core update changes how Google compares pages across the web and does not target specific sites. A penalty for breaking Google\'s spam policies is a manual action, and it appears in the Manual actions report in Search Console.'],
        ['How do I know if the core update affected my site?', 'Wait until Google confirms the rollout is complete, then wait one more full week. Compare that week with the week before 10 November in Search Console, page by page, and check whether rankings, impressions or only clicks changed.'],
        ['What should I do if my traffic dropped?', 'Avoid quick fixes. Review your most affected pages against Google\'s helpful content questions, improve them for readers and give the changes time. Some improvements show within days; others take months or the next core update.'],
        ['Does the November 2026 core update affect AI Overviews?', 'Google has not said. Its documentation says there are no extra requirements to appear in AI Overviews or AI Mode beyond being indexed and eligible to show in Search with a snippet, so the same quality work applies.'],
        ['Why does the timing matter for online stores?', 'If the rollout runs the full two weeks, the first full week after it ends includes Thanksgiving, Black Friday and Cyber Monday. Compare with the same weeks last year as well, so holiday demand is not mistaken for a ranking change.'],
        ['How many core updates has Google released in 2026?', 'Three so far: the March 2026 core update (started 27 March), the May 2026 core update (21 May) and the November 2026 core update (10 November), according to Google\'s Search Status Dashboard.'],
    ],
    'cta' => ['Not Sure What the Update Did to Your Site?', 'Send us your site and the dates your traffic changed. A senior strategist replies within one working day.', 'Ask a Strategist'],
],

];
