<?php
/**
 * What Vee knows: the facts it always has, and the passages of the site it
 * looks up for each question.
 *
 * Nothing here is written twice. The service pages, their FAQs, the 90-day
 * plans, the Enterprise and content pages and the named case studies are read
 * from the same data the site renders, so Vee can never say something the site
 * does not. The owner's own policies (guest posts, partnerships, hours...) live
 * in includes/data/chat-kb.php.
 *
 * Deliberately left out: the anonymous cases in includes/data/cases.php (not
 * verified), blog articles (they quote market price ranges, and the site rule
 * is the minimum budget only), the target-country list and the street address.
 *
 * Search is plain BM25 over short passages with a small synonym list: no
 * embeddings, no extra service, a few milliseconds per question.
 */

declare(strict_types=1);

/** The owner's chat policies and wording, includes/data/chat-kb.php. */
function chat_kb_policy(): array
{
    static $p = null;
    return $p ??= (array) require BASE_PATH . '/includes/data/chat-kb.php';
}

/** A page's name as a topic, the way a person would say it: "SEO", "Shopify web design". */
function chat_kb_topic(string $slug, array $page): string
{
    if (($page['template'] ?? '') === 'landing' && !empty($GLOBALS['LANDINGS'][$slug]['eyebrow'])) {
        $name = (string) $GLOBALS['LANDINGS'][$slug]['eyebrow'];
    } else {
        $name = (string) ($page['h1'] ?? $slug);
    }
    /* "Search Engine Optimization Services" -> "search engine optimization";
       "SAP CPQ Services That Turn..." -> "SAP CPQ"; anything after a colon goes. */
    $name = trim((string) preg_replace('/\s*[:|].*$/u', '', $name));
    $name = trim((string) preg_replace('/\s+(services?|that|which|for|to|in)\b.*$/i', '', $name));
    if ($name === '' || mb_strlen($name) > 40) {
        return 'this service';
    }
    return strtr(mb_strtolower($name), [
        'seo' => 'SEO', 'ai ' => 'AI ', 'ppc' => 'PPC', 'chatgpt' => 'ChatGPT', 'google' => 'Google', 'perplexity' => 'Perplexity',
        'facebook' => 'Facebook', 'instagram' => 'Instagram', 'wordpress' => 'WordPress', 'shopify' => 'Shopify', 'magento' => 'Magento',
        'woocommerce' => 'WooCommerce', 'bigcommerce' => 'BigCommerce', 'sap cpq' => 'SAP CPQ', 'salesforce' => 'Salesforce',
        'odoo erp' => 'Odoo ERP', 'odoo' => 'Odoo', 'erpnext' => 'ERPNext', 'it staffing' => 'IT staffing', 'cbd' => 'CBD', 'b2b' => 'B2B',
    ]);
}

/**
 * Which greeting a page gets: home, service, tool, case, blog or default.
 * Service covers every page that sells something (service, hub, landing and
 * content pages).
 */
function chat_page_kind(string $slug, array $page): string
{
    if ($slug === '') {
        return 'home';
    }
    $tpl = (string) ($page['template'] ?? 'service');
    return match (true) {
        in_array($tpl, ['service', 'hub', 'landing', 'content-landing'], true) => 'service',
        in_array($tpl, ['audit', 'ai-crawler-checker'], true) => 'tool',
        $tpl === 'case-detail' => 'case',
        in_array($tpl, ['blog-post', 'update-post'], true) => 'blog',
        default => 'default',
    };
}

/** The measured results Vee may quote: the named case studies only, figures exactly as published. */
function chat_kb_cases(): array
{
    $out = [];
    foreach ($GLOBALS['CASES'] ?? [] as $cs => $c) {
        if (empty($c['real']) || empty($c['client'])) {
            continue;
        }
        $facts = array_map(static fn(array $r): string => $r[0] . ' ' . $r[1], array_slice((array) ($c['results'] ?? []), 0, 3));
        $out[] = $c['client'] . (!empty($c['industry']) ? ' (' . $c['industry'] . (!empty($c['location']) ? ', ' . $c['location'] : '') . ')' : '')
            . ': ' . implode('; ', $facts) . '. ' . SITE_URL . '/case-studies/' . $cs . '/';
    }
    return $out;
}

/**
 * The facts Vee always has, in this visitor's currency. Kept short: it is sent
 * with every message, and the free AI plans count every token.
 */
function chat_kb_core(): string
{
    $p = chat_kb_policy();
    $services = [];
    foreach (LEAD_SERVICES as $group => $items) {
        $services[] = $group . ': ' . implode(', ', $items);
    }
    $bands = implode('; ', array_map(static fn(array $b): string => $b[0] . ' ' . $b[1], budget_bands()));
    $note = trim((string) preg_replace('/^The minimum budget for any project or monthly plan\.\s*/', '', min_budget_note()));
    $booking = function_exists('booking_settings') ? booking_settings() : ['enabled' => false];
    $lines = [
        'VTurnU (legal name ' . LEGAL_NAME . ') is a digital marketing, branding, web development and AI company in Chennai, India, founded in ' . FOUNDING_YEAR . '. It works with clients worldwide, in every industry, mostly remotely.',
        ...array_map('strval', (array) ($p['facts'] ?? [])),
        'SERVICES. ' . implode('. ', $services) . '.',
        'Minimum budget: ' . min_budget() . ' for any project or monthly plan. ' . $note . ' Exact quotes come after a short call. There is no price list and no hourly rate.',
        'BUDGET BANDS per month: ' . $bands . '.',
        'How work starts: a senior strategist reads the enquiry and replies within one working day, then a short call and a free audit. Search programs have a 90-day minimum, then run month to month.',
        'Offers: ' . ($p['offers'] ?? ''),
        'Contact: email ' . CONTACT_EMAIL . ', WhatsApp or phone ' . CONTACT_PHONE . ' (https://wa.me/' . CONTACT_WHATSAPP . '). The team replies ' . ($p['reply_time'] ?? 'as soon as possible') . '.',
    ];
    if (!empty($booking['enabled'])) {
        $lines[] = 'A free 30-minute call on Google Meet with ' . ($booking['host_name'] ?? 'the team') . (($booking['host_title'] ?? '') !== '' ? ', ' . $booking['host_title'] : '')
            . ': ' . SITE_URL . '/' . BOOKING_PATH . '/ . ' . ($p['booking'] ?? '');
    }
    $lines[] = 'Free tools: SEO audit ' . SITE_URL . '/free-seo-audit/ , AI Crawler Checker ' . SITE_URL . '/ai-crawler-checker/ , free e-books and guides ' . SITE_URL . '/ebooks/';
    $cases = chat_kb_cases();
    if ($cases) {
        $lines[] = 'Measured client results, the only ones to quote: ' . implode(' | ', $cases);
    }
    return implode("\n", array_map(static fn(string $l): string => '- ' . $l, $lines));
}

/* ------------------------------------------------------------------ */
/* Passages                                                            */
/* ------------------------------------------------------------------ */

/**
 * Every passage Vee can look up: [slug, title, url, kind, text]. Short on
 * purpose (one FAQ, one page overview, one plan), so the few that fit in a
 * message are the ones that answer it.
 *
 * @return list<array{slug:string,title:string,url:string,kind:string,text:string}>
 */
function chat_kb_chunks(): array
{
    static $chunks = null;
    if ($chunks !== null) {
        return $chunks;
    }
    $chunks = [];
    $clean = static fn($s): string => trim((string) preg_replace('/\s+/u', ' ', strip_tags(html_entity_decode((string) $s, ENT_QUOTES | ENT_HTML5, 'UTF-8'))));
    $add = static function (string $slug, string $title, string $kind, string $text) use (&$chunks, $clean): void {
        $text = $clean($text);
        if ($text === '') {
            return;
        }
        $chunks[] = [
            'slug' => $slug, 'title' => $clean($title), 'url' => SITE_URL . page_url($slug),
            'kind' => $kind, 'text' => mb_strlen($text) > 900 ? mb_substr($text, 0, 897) . '...' : $text,
        ];
    };
    $pairs = static fn(array $items): string => implode('; ', array_map(
        static fn($g): string => is_array($g) ? implode(': ', array_map('strval', array_slice($g, 0, 2))) : (string) $g,
        $items
    ));

    $plans = [];
    foreach ($GLOBALS['PAGES'] ?? [] as $slug => $p) {
        $slug = (string) $slug;
        if (!empty($p['noindex'])) {
            continue;
        }
        $tpl = (string) ($p['template'] ?? 'service');
        $h1 = (string) ($p['h1'] ?? $slug);
        $sells = in_array($tpl, ['service', 'hub', 'landing', 'content-landing'], true);
        $company = in_array($tpl, ['about', 'how-we-work', 'audit', 'ai-crawler-checker'], true);
        if (!$sells && !$company && $slug !== '') {
            continue;
        }

        /* The overview: what it is, what you get, who it suits. */
        $parts = [(string) ($p['lede'] ?? ''), (string) ($p['meta'] ?? '')];
        if ($tpl === 'service' || $tpl === 'hub') {
            $m = function_exists('money_data') ? money_data($slug) : [];
            if (!empty($m['gets']) && is_array($m['gets'])) {
                $gets = $m['gets'];
                $head = is_string($gets[0] ?? null) ? array_shift($gets) : '';
                $parts[] = 'What you get' . ($head !== '' ? ' (' . $head . ')' : '') . ': ' . $pairs($gets) . '.';
            }
            if (!empty($m['fit']) && is_array($m['fit'])) {
                $parts[] = 'A good fit when: ' . implode('; ', array_map('strval', $m['fit'])) . '.';
            }
            if (!empty($p['children']) && is_array($p['children'])) {
                $names = [];
                foreach ($p['children'] as $child) {
                    if (isset($GLOBALS['PAGES'][$child]['h1'])) {
                        $names[] = (string) $GLOBALS['PAGES'][$child]['h1'] . ' (' . SITE_URL . page_url((string) $child) . ')';
                    }
                }
                if ($names) {
                    $parts[] = 'Services in this group: ' . implode('; ', $names) . '.';
                }
            }
            if (!empty($p['cat']) && function_exists('money_plan')) {
                $plans[(string) $p['cat']] ??= $slug;
            }
        } elseif ($tpl === 'landing' && !empty($GLOBALS['LANDINGS'][$slug])) {
            $l = $GLOBALS['LANDINGS'][$slug];
            if (!empty($l['capabilities'])) {
                $parts[] = 'What we do: ' . $pairs((array) $l['capabilities']) . '.';
            }
        } elseif ($tpl === 'content-landing') {
            $parts[] = (string) ($p['card'] ?? '');
        }
        $add($slug, $h1, 'page', implode(' ', array_filter($parts)));

        /* One passage per FAQ: the answers buyers actually ask for. */
        $faqs = [];
        if ($tpl === 'service') {
            $faqs = service_faqs($p);
        } elseif (!empty($p['faqs']) && is_array($p['faqs'])) {
            $faqs = $p['faqs'];
        }
        if ($tpl === 'landing' && !empty($GLOBALS['LANDINGS'][$slug])) {
            $l = $GLOBALS['LANDINGS'][$slug];
            if (!empty($l['answer'])) {
                $faqs[] = $l['answer'];
            }
            foreach ((array) ($l['faqs'] ?? []) as $f) {
                $faqs[] = $f;
            }
        }
        foreach ($faqs as $f) {
            if (is_array($f) && isset($f[0], $f[1])) {
                $add($slug, $h1, 'faq', 'Q: ' . $f[0] . ' A: ' . $f[1]);
            }
        }
    }

    /* The first 90 days, once per service line. */
    foreach ($plans as $cat => $slug) {
        $steps = array_map(static fn(array $s): string => $s[0] . ', ' . $s[1] . ': ' . $s[2], money_plan($cat));
        $add($slug, 'The first 90 days: ' . (string) ($GLOBALS['PAGES'][$slug]['h1'] ?? $slug), 'plan', implode(' ', $steps));
    }

    /* The named case studies, with their published figures. */
    foreach ($GLOBALS['CASES'] ?? [] as $cs => $c) {
        if (empty($c['real'])) {
            continue;
        }
        $results = array_map(static fn(array $r): string => $r[0] . ' ' . $r[1], (array) ($c['results'] ?? []));
        $approach = array_map(static fn($a): string => is_array($a) ? (string) $a[0] : (string) $a, (array) ($c['approach'] ?? []));
        $add('case-studies/' . $cs, 'Case study: ' . (string) ($c['client'] ?? $cs), 'case',
            (string) ($c['lede'] ?? '') . ' What we did: ' . implode('; ', $approach) . '. Results: ' . implode('; ', $results) . '.');
    }

    /* Free e-books and guides, by name. */
    $books = [];
    foreach ($GLOBALS['RESOURCES'] ?? [] as $rs => $r) {
        if (!empty($r['title'])) {
            $books[] = preg_replace('/\s*\((Free|Free E-book|Free Guide)\)\s*/i', ' ', (string) $r['title']);
        }
    }
    if ($books) {
        $chunks[] = ['slug' => 'ebooks', 'title' => 'Free e-books and guides', 'url' => SITE_URL . '/ebooks/', 'kind' => 'page',
            'text' => 'Free to read, no sign-up needed: ' . implode('; ', array_map('trim', $books)) . '.'];
    }
    return $chunks;
}

/* ------------------------------------------------------------------ */
/* Search                                                              */
/* ------------------------------------------------------------------ */

const CHAT_KB_STOP = [
    'a', 'an', 'the', 'and', 'or', 'but', 'is', 'are', 'was', 'were', 'be', 'been', 'to', 'of', 'in', 'on', 'for', 'with', 'at', 'by',
    'from', 'as', 'it', 'its', 'this', 'that', 'these', 'those', 'i', 'you', 'we', 'me', 'my', 'our', 'your', 'us', 'they', 'them',
    'do', 'does', 'did', 'can', 'could', 'would', 'should', 'will', 'shall', 'what', 'which', 'who', 'whom', 'when', 'where', 'why',
    'there', 'here', 'so', 'if', 'not', 'no', 'yes', 'any', 'some', 'about', 'have', 'has', 'had', 'just', 'also', 'very', 'much',
    'please', 'hi', 'hello', 'hey', 'thanks', 'thank', 'ok', 'okay', 'into', 'than', 'then', 'too', 'am', 'im', 'ive', 'id',
];

/** Synonyms, applied after stemming: what a visitor types to the words the site uses. */
const CHAT_KB_SYNONYMS = [
    'site' => ['website'], 'web' => ['website'], 'webpage' => ['website'], 'homepage' => ['website'], 'redesign' => ['website', 'design'],
    'price' => ['cost', 'budget'], 'pricing' => ['cost', 'budget'], 'charge' => ['cost', 'budget'], 'fee' => ['cost', 'budget'],
    'rate' => ['cost', 'budget'], 'quote' => ['cost', 'budget'], 'expensive' => ['cost'], 'cheap' => ['cost'], 'afford' => ['cost', 'budget'],
    'ppc' => ['ad', 'paid', 'google'], 'adword' => ['ad', 'google'], 'sem' => ['ad', 'paid'], 'advertis' => ['ad', 'paid'],
    'aeo' => ['ai', 'search'], 'geo' => ['ai', 'search'], 'llm' => ['ai', 'search'], 'chatgpt' => ['ai', 'search'],
    'perplexity' => ['ai', 'search'], 'gemini' => ['ai', 'search'], 'copilot' => ['ai', 'search'], 'overview' => ['ai'],
    'shopify' => ['ecommerce', 'store'], 'woocommerce' => ['ecommerce', 'store'], 'magento' => ['ecommerce', 'store'],
    'bigcommerce' => ['ecommerce', 'store'], 'shop' => ['ecommerce', 'store'], 'store' => ['ecommerce'], 'online' => ['ecommerce'],
    'backlink' => ['link', 'authority', 'seo'], 'logo' => ['brand'], 'identity' => ['brand'], 'branding' => ['brand'],
    'instagram' => ['social'], 'facebook' => ['social'], 'linkedin' => ['social'], 'tiktok' => ['social'], 'youtube' => ['social'],
    'blog' => ['content', 'article'], 'article' => ['content', 'blog'], 'copywrit' => ['content', 'writ'], 'writer' => ['content', 'writ'],
    'long' => ['time', 'week'], 'duration' => ['time', 'week', 'long'], 'timeline' => ['time', 'week', 'long'], 'soon' => ['time', 'week'],
    'rank' => ['seo', 'search'], 'ranking' => ['seo', 'search'], 'google' => ['search'], 'erp' => ['odoo', 'erpnext'],
    'crm' => ['salesforce'], 'cpq' => ['sap'], 'hire' => ['staffing'], 'developer' => ['staffing', 'development'],
    'leads' => ['lead', 'enquiry'], 'enquiry' => ['lead'], 'inquiry' => ['lead', 'enquiry'], 'guarantee' => ['guarantee', 'promise'],
];

/** Lower-case word stems, stop words removed. */
function chat_kb_tokens(string $s): array
{
    $words = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($s), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $out = [];
    foreach ($words as $w) {
        if (in_array($w, CHAT_KB_STOP, true) || mb_strlen($w) < 2) {
            continue;
        }
        $len = strlen($w);
        if ($len > 4 && str_ends_with($w, 'ies')) {
            $w = substr($w, 0, -3) . 'y';
        } elseif ($len > 5 && str_ends_with($w, 'ing')) {
            $w = substr($w, 0, -3);
        } elseif ($len > 4 && str_ends_with($w, 'ed') && !str_ends_with($w, 'eed')) {
            $w = substr($w, 0, -2);
        } elseif ($len > 3 && str_ends_with($w, 's') && !str_ends_with($w, 'ss') && !str_ends_with($w, 'us')) {
            $w = substr($w, 0, -1);
        }
        $out[] = $w;
    }
    return $out;
}

/** The search index, built once per warm function. */
function chat_kb_index(): array
{
    static $idx = null;
    if ($idx !== null) {
        return $idx;
    }
    $chunks = chat_kb_chunks();
    $tf = [];
    $len = [];
    $df = [];
    foreach ($chunks as $i => $c) {
        /* The title counts twice: "Shopify Web Design" should win a Shopify question. */
        $toks = chat_kb_tokens($c['title'] . ' ' . $c['title'] . ' ' . $c['text']);
        $counts = array_count_values($toks);
        $tf[$i] = $counts;
        $len[$i] = max(1, count($toks));
        foreach ($counts as $t => $_) {
            $df[$t] = ($df[$t] ?? 0) + 1;
        }
    }
    $n = max(1, count($chunks));
    return $idx = ['chunks' => $chunks, 'tf' => $tf, 'len' => $len, 'df' => $df, 'n' => $n, 'avg' => array_sum($len) / $n];
}

/**
 * The passages that best answer a question, within a character budget.
 * $queries are [text, weight] pairs: the latest message counts most, the one
 * before it gives context ("how long does it take?" after "I need a store").
 * The page the visitor is on gets a boost, and its overview is added when
 * there is room, so "how much is this?" is answered about this page.
 *
 * @param list<array{0:string,1:float}> $queries
 */
function chat_kb_search(array $queries, string $pageSlug = '', int $maxChars = 3200, int $maxChunks = 5): array
{
    $idx = chat_kb_index();
    $weights = [];
    foreach ($queries as [$text, $w]) {
        foreach (chat_kb_tokens((string) $text) as $t) {
            $weights[$t] = max($weights[$t] ?? 0, (float) $w);
            foreach (CHAT_KB_SYNONYMS[$t] ?? [] as $syn) {
                $weights[$syn] = max($weights[$syn] ?? 0, 0.6 * (float) $w);
            }
        }
    }
    $scores = [];
    if ($weights) {
        foreach ($idx['tf'] as $i => $tf) {
            $s = 0.0;
            foreach ($weights as $t => $w) {
                if (!isset($tf[$t])) {
                    continue;
                }
                $df = $idx['df'][$t];
                $idf = log(1 + ($idx['n'] - $df + 0.5) / ($df + 0.5));
                $f = $tf[$t];
                $s += $w * $idf * ($f * 2.2) / ($f + 1.2 * (0.25 + 0.75 * $idx['len'][$i] / $idx['avg']));
            }
            if ($s > 0) {
                if ($pageSlug !== '' && $idx['chunks'][$i]['slug'] === $pageSlug) {
                    $s *= 1.6;
                }
                $scores[$i] = $s;
            }
        }
    }
    arsort($scores);

    $picked = [];
    $seen = [];
    $used = 0;
    $take = static function (int $i) use (&$picked, &$seen, &$used, $idx, $maxChars): void {
        $c = $idx['chunks'][$i];
        /* The same FAQ answer appears on many pages with only the name changed: keep one. */
        $key = preg_replace('/\b(seo|services?|ppc|design|web|ai)\b/i', '', mb_strtolower(preg_replace('/^Q: .*? A: /', '', $c['text'])));
        if (isset($seen[$key]) || $used + mb_strlen($c['text']) > $maxChars) {
            return;
        }
        $seen[$key] = true;
        $used += mb_strlen($c['text']);
        $picked[] = $c;
    };
    /* A real match must beat a fraction of the best one, or it is noise. */
    $best = $scores ? reset($scores) : 0.0;
    foreach ($scores as $i => $s) {
        if (count($picked) >= $maxChunks || $s < $best * 0.25) {
            break;
        }
        $take((int) $i);
    }
    if ($pageSlug !== '' && count($picked) < $maxChunks) {
        foreach ($idx['chunks'] as $i => $c) {
            if ($c['slug'] === $pageSlug && $c['kind'] === 'page') {
                $take($i);
                break;
            }
        }
    }
    return $picked;
}
