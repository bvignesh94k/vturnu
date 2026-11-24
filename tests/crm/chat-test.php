<?php
declare(strict_types=1);
ob_start();
require '/t/boot.php';

/**
 * An in-memory ChatPgStore, for tests only: same methods, no SQL. Lets the
 * pure chat.php logic (redaction, cleaning, the model chain, the lead and
 * email pipeline) run without a real Postgres chat_sessions table.
 */
final class FakeChatStore
{
    private array $sessions = [];
    private array $messages = [];
    private array $usage = [];
    private int $nextId = 1;
    private int $nextMsgId = 1;

    private function defaults(): array
    {
        return [
            'id' => 0, 'token' => '', 'created_at' => date('c'), 'updated_at' => date('c'), 'last_visitor_at' => null,
            'ip_hash' => '', 'country' => '', 'time_zone' => '', 'first_page' => '', 'last_page' => '', 'attribution' => '',
            'name' => '', 'email' => '', 'phone' => '', 'company' => '', 'website' => '', 'service' => '', 'budget' => '',
            'budget_label' => '', 'timeline' => '', 'intent' => '', 'summary' => '', 'handoff' => false, 'visitor_msgs' => 0,
            'ai_replies' => 0, 'spam_hits' => 0, 'status' => 'open', 'enquiry_id' => 0, 'lead_mailed_at' => null,
            'mailed_msgs' => 0, 'mailed_msg_id' => 0, 'mailed_at' => null,
        ];
    }

    public function session(string $token): ?array
    {
        foreach ($this->sessions as $s) {
            if ($s['token'] === $token) {
                return $s;
            }
        }
        return null;
    }

    public function sessionById(int $id): ?array
    {
        return $this->sessions[$id] ?? null;
    }

    public function create(array $f): array
    {
        $id = $this->nextId++;
        $s = $this->defaults();
        $s['id'] = $id;
        $s['token'] = bin2hex(random_bytes(16));
        foreach (['ip_hash', 'country', 'time_zone', 'first_page', 'last_page', 'attribution'] as $k) {
            $s[$k] = (string) ($f[$k] ?? '');
        }
        return $this->sessions[$id] = $s;
    }

    public function update(int $id, array $set, array $where = []): bool
    {
        if (!isset($this->sessions[$id])) {
            return false;
        }
        foreach ($where as $k => $v) {
            if (($this->sessions[$id][$k] ?? null) !== $v) {
                return false;
            }
        }
        foreach ($set as $k => $v) {
            $this->sessions[$id][$k] = $v === 'now()' ? date('c') : $v;
        }
        return true;
    }

    public function addMessage(int $sessionId, string $role, string $body, array $meta = []): int
    {
        $id = $this->nextMsgId++;
        $this->messages[] = ['id' => $id, 'session_id' => $sessionId, 'role' => $role, 'body' => $body, 'meta' => $meta, 'created_at' => date('c')];
        return $id;
    }

    public function messages(int $sessionId, int $afterId = 0, int $limit = 400): array
    {
        $out = array_values(array_filter($this->messages, static fn(array $m): bool => $m['session_id'] === $sessionId && $m['id'] > $afterId));
        return array_slice($out, 0, $limit);
    }

    public function usage(string $day): array
    {
        return $this->usage[$day] ?? [];
    }

    public function addUsage(string $day, string $model, int $requests, int $units, int $failures, int $coolUntil = 0): void
    {
        $u = $this->usage[$day][$model] ?? ['requests' => 0, 'units' => 0, 'failures' => 0, 'cool_until' => 0];
        $u['requests'] += $requests;
        $u['units'] += $units;
        $u['failures'] += $failures;
        if ($coolUntil > 0) {
            $u['cool_until'] = $coolUntil;
        }
        $this->usage[$day][$model] = $u;
    }

    public function pending(int $limit = 40): array
    {
        return array_slice(array_values(array_filter($this->sessions, static fn(array $s): bool => $s['status'] !== 'spam' && $s['visitor_msgs'] > $s['mailed_msgs'])), 0, $limit);
    }

    public function recent(int $limit = 100): array
    {
        return array_slice(array_values(array_filter($this->sessions, static fn(array $s): bool => $s['visitor_msgs'] > 0)), 0, $limit);
    }

    public function byEnquiry(int $enquiryId): ?array
    {
        foreach (array_reverse($this->sessions) as $s) {
            if ($s['enquiry_id'] === $enquiryId) {
                return $s;
            }
        }
        return null;
    }

    public function purge(int $days): int
    {
        return 0;
    }
}

/** A canned HTTP layer for chat_http_post(): a queue of responses, one per call, read in order. */
function chat_test_http(array $queue): callable
{
    $i = 0;
    return function (string $url, array $headers, string $body, int $timeout) use (&$i, $queue): array {
        $r = $queue[$i] ?? ['status' => 500, 'body' => '', 'headers' => [], 'error' => 'no stub queued'];
        $i++;
        return $r;
    };
}

function chat_test_ok(string $reply, array $extra = []): array
{
    $json = array_merge([
        'reply' => $reply, 'intent' => 'general', 'service' => '', 'handoff' => false, 'ask_contact' => false, 'summary' => '',
        'lead' => ['name' => '', 'company' => '', 'website' => '', 'budget' => '', 'timeline' => ''],
    ], $extra);
    return ['status' => 200, 'body' => json_encode(['choices' => [['message' => ['content' => json_encode($json)]]], 'usage' => ['prompt_tokens' => 50, 'completion_tokens' => 20]]), 'headers' => [], 'error' => ''];
}

function chat_reset(): void
{
    $GLOBALS['CHAT_STORE'] = new FakeChatStore();
    /* No stub queued answers "no response" rather than reaching the real
       network, which this sandbox cannot reach anyway. Tests that care about
       a model's answer set their own queue right after calling this. */
    $GLOBALS['CHAT_HTTP'] = chat_test_http([]);
    $GLOBALS['captured_mail'] = [];
    putenv('GROQ_API_KEY=test-groq-key');
    putenv('CF_ACCOUNT_ID=');
    putenv('CF_API_TOKEN=');
}
chat_reset();

/* ---------------- contact extraction and redaction ---------------- */

check('email found', chat_extract_contacts('reach me at jane@example.com please', 'IN')[0] === 'jane@example.com');
check('email not matched inside a longer token', chat_extract_contacts('no email here, just text', 'IN')[0] === '');
[$e, $p] = chat_extract_contacts('call me on +91 98765 43210 or jane@firm.co', 'IN');
check('both found at once', $e === 'jane@firm.co' && $p === '+919876543210', "$e|$p");
check('bare national number gets the visitor country code', chat_extract_contacts('WhatsApp 9876543210', 'IN')[1] === '+919876543210');
check('a price is not read as a phone', chat_extract_contacts('budget is about 500000 this quarter', 'IN')[1] === '');
check('a year range is not read as a phone', chat_extract_contacts('we have been live since 2020-2023', 'IN')[1] === '');
check('too short a run is not a phone', chat_extract_contacts('room 12345', 'IN')[1] === '');
check('redaction hides email and phone from the model', chat_redact('I am jane@example.com, call +91 98765 43210') === 'I am [email shared], call [phone shared]');

/* ---------------- cleaning what the visitor reads ---------------- */

check('em dash becomes a comma', chat_clean_reply("fast \u{2014} clear") === 'fast, clear');
check('markdown bold is stripped', chat_clean_reply('This is **important** news') === 'This is important news');
check('a heading marker is stripped', chat_clean_reply("## Pricing\nDetails") === "Pricing\nDetails");
check('a vturnu.com link is kept', chat_clean_reply('See vturnu.com/seo-services/ for more') === 'See vturnu.com/seo-services/ for more');
check('an outside link is dropped', chat_clean_reply('Read more at competitor.com/blog for tips') === 'Read more at for tips', chat_clean_reply('Read more at competitor.com/blog for tips'));
check('the real WhatsApp link is kept', chat_clean_reply('Chat at wa.me/919363731498 any time') === 'Chat at wa.me/919363731498 any time');
check('a lookalike WhatsApp link is dropped', str_contains(chat_clean_reply('Try wa.me/15551234567 instead'), 'wa.me') === false);
check('a stray email becomes the real one', chat_clean_reply('Write to sales@random.test today') === 'Write to connect@vturnu.com today');
check('an hourly rate is refused outright', str_contains(chat_clean_reply('Our rate is $50 per hour for this work'), 'per hour') === false);
check('an hourly rate reply gives the minimum budget instead', str_contains(chat_clean_reply('Our rate is $50 per hour for this work'), min_budget()));
$long = str_repeat('This is a long sentence about SEO. ', 30);
check('a very long reply is cut to size', mb_strlen(chat_clean_reply($long)) <= 700);

/* ---------------- splitting a reply into bubbles ---------------- */

check('a short reply stays one bubble', chat_split_reply('Hi there, happy to help.') === ['Hi there, happy to help.']);
$two = chat_split_reply('This is the first half of a longer reply, written with enough words on purpose to pass well past the short-reply bubble limit of two hundred and forty characters easily. This is the second half, which should land in its own bubble once the reply is split in two.');
check('a long reply becomes two bubbles', count($two) === 2 && implode(' ', $two) !== '', json_encode($two));

/* ---------------- parsing a model's answer ---------------- */

$ok = chat_parse_reply(json_encode(['reply' => 'Hello there', 'intent' => 'sales_web', 'service' => 'Website design and development',
    'handoff' => false, 'ask_contact' => true, 'summary' => 'Wants a new site', 'lead' => ['name' => 'Jane', 'company' => '', 'website' => '', 'budget' => 'b1', 'timeline' => 'As soon as possible']]));
check('a well-formed reply parses', $ok !== null && $ok['reply'] === 'Hello there' && $ok['intent'] === 'sales_web' && $ok['lead']['name'] === 'Jane', json_encode($ok));
check('an unknown intent falls back to general', chat_parse_reply(json_encode(['reply' => 'Hi', 'intent' => 'nonsense', 'lead' => []]))['intent'] === 'general');
check('plain text with no JSON is still a reply', chat_parse_reply('Sure, happy to help with that.')['reply'] === 'Sure, happy to help with that.');
check('an empty answer parses to nothing', chat_parse_reply('') === null);
check('broken JSON with no reply field parses to nothing', chat_parse_reply('{"oops": true}') === null);
check('text wrapped around JSON is still read', chat_parse_reply('Sure! ' . json_encode(['reply' => 'Hi', 'intent' => 'general', 'lead' => []]))['reply'] === 'Hi');

/* ---------------- guessing an intent without AI ---------------- */

check('guest post wording is spotted', chat_guess_intent('Can I write a guest post for your blog?') === 'guest_post');
check('link wording is spotted', chat_guess_intent('I would like to exchange backlinks with you') === 'link_outreach');
check('partnership wording is spotted', chat_guess_intent('We would love to explore a partnership') === 'partnership');
check('a website question is spotted', chat_guess_intent('How much for a new website?') === 'sales_web');
check('plain chat falls back to general', chat_guess_intent('Hello, how are you?') === 'general');

/* ---------------- asking for a person, directly and by phrase ---------------- */

/* chat_handle() (unlike chat_send(), called directly everywhere else in this
   file) also checks the request's Origin, so the notify calls below need it set. */
$_SERVER['HTTP_ORIGIN'] = 'https://vturnu.com';
$_SERVER['HTTP_HOST'] = 'vturnu.com';

check('the exact quick-reply phrase is recognised', chat_wants_human('I want to talk to someone'));
check('"speak to a human" is recognised', chat_wants_human('Can I speak to a human please'));
check('"connect me with an agent" is recognised', chat_wants_human('Please connect me with an agent'));
check('a plain question is not mistaken for it', !chat_wants_human('How long does SEO take?'));
check('mentioning a person in passing is not mistaken for it', !chat_wants_human('I spoke to your sales team last year'));

/* A fresh visitor asks for a person straight away: no contact yet, so Vee
   asks for it instead of notifying (there is no one to reach yet). */
chat_reset();
$GLOBALS['CHAT_HTTP'] = chat_test_http([]);
$h1 = chat_send(['text' => 'I want to talk to someone', 'page' => '/', 'title' => 'Home'], '');
check('asking for a person with no contact yet gets asked for it', str_contains($h1['replies'][0], 'What') || str_contains($h1['replies'][0], 'email'), json_encode($h1));
check('handoff is set even from the scripted fallback', $h1['handoff'] === true);
check('nothing to notify yet, there is no contact to reach', $h1['notify'] === false);

/* The same visitor then gives an email in the same breath: a lead is saved
   and the team is told, same as any other first contact. */
$h2 = chat_send(['sid' => $h1['sid'], 'text' => 'sure, priya@gmail.com', 'page' => '/', 'title' => 'Home'], $h1['sid']);
check('giving contact right after asking for a person still notifies', $h2['notify'] === true, json_encode($h2));

/* A visitor who is ALREADY a known lead (already emailed once) asks for a
   person mid-conversation: this must reach the team now, not at the next
   quiet moment, which is the gap the owner asked to close (24 Nov 2026). */
chat_reset();
$GLOBALS['CHAT_HTTP'] = chat_test_http([chat_test_ok('Happy to help with your website.', ['intent' => 'sales_web', 'lead' => ['name' => 'Arjun', 'company' => '', 'website' => '', 'budget' => '', 'timeline' => '']])]);
$u1 = chat_send(['text' => 'Hi, I need a website, my email is arjun@gmail.com', 'page' => '/web-design/', 'title' => 'Website Design'], '');
chat_handle(['action' => 'notify', 'sid' => $u1['sid']]);
check('the first lead email went out as usual', count($GLOBALS['captured_mail']) === 1, (string) count($GLOBALS['captured_mail']));
$GLOBALS['CHAT_HTTP'] = chat_test_http([]);
$u2 = chat_send(['sid' => $u1['sid'], 'text' => 'Actually I want to talk to someone now', 'page' => '/web-design/', 'title' => 'Website Design'], $u1['sid']);
check('an already-known lead asking for a person is marked urgent', $u2['notify'] === true && $u2['urgent'] === true, json_encode($u2));
check('the warm, specific line is used, not a generic one', str_contains($u2['replies'][0], 'flagged'), json_encode($u2));
chat_handle(['action' => 'notify', 'sid' => $u1['sid'], 'urgent' => true]);
check('a second, immediate email went out for the urgent request', count($GLOBALS['captured_mail']) === 2, (string) count($GLOBALS['captured_mail']));
check('the urgent email subject flags that a reply is needed', str_contains($GLOBALS['captured_mail'][1]['subject'], 'needs a reply'), $GLOBALS['captured_mail'][1]['subject']);
/* Without the urgent flag, the exact same request would have waited (the
   quiet-window has not passed), proving the flag is what unblocks it. */
$beforeCount = count($GLOBALS['captured_mail']);
chat_handle(['action' => 'notify', 'sid' => $u1['sid']]);
check('a plain notify call (no urgent flag) does not also send one', count($GLOBALS['captured_mail']) === $beforeCount);

/* ---------------- the model chain ---------------- */

chat_reset();
$GLOBALS['CHAT_HTTP'] = chat_test_http([chat_test_ok('Hi, happy to help with your website.', ['intent' => 'sales_web'])]);
$r1 = chat_ai_reply([['role' => 'system', 'content' => 'x'], ['role' => 'user', 'content' => 'hi']]);
check('the first model answers when it can', $r1 !== null && $r1['model'] === 'groq-gpt-oss-120b' && $r1['reply'] === 'Hi, happy to help with your website.', json_encode($r1));

chat_reset();
$GLOBALS['CHAT_HTTP'] = chat_test_http([
    ['status' => 429, 'body' => '', 'headers' => ['retry-after' => '30'], 'error' => ''],
    chat_test_ok('Second model here.'),
]);
$r2 = chat_ai_reply([['role' => 'system', 'content' => 'x'], ['role' => 'user', 'content' => 'hi']]);
check('a 429 moves on to the next model', $r2 !== null && $r2['model'] === 'groq-qwen3.8-27b', json_encode($r2));
$usedUp = chat_store()->usage(gmdate('Y-m-d'));
check('the rested model is marked with a cool-down', ($usedUp['groq-gpt-oss-120b']['cool_until'] ?? 0) > time());

chat_reset();
$GLOBALS['CHAT_HTTP'] = chat_test_http([
    ['status' => 500, 'body' => 'oops', 'headers' => [], 'error' => ''],
    ['status' => 200, 'body' => 'not json at all {{{', 'headers' => [], 'error' => ''],
    chat_test_ok('Third model.'),
]);
$r3 = chat_ai_reply([['role' => 'system', 'content' => 'x'], ['role' => 'user', 'content' => 'hi']]);
check('a server error and a bad answer both fall through', $r3 !== null && $r3['model'] === 'groq-gpt-oss-20b', json_encode($r3));

chat_reset();
putenv('GROQ_API_KEY=');
$r4 = chat_ai_reply([['role' => 'system', 'content' => 'x'], ['role' => 'user', 'content' => 'hi']]);
check('no key configured means no model is tried, not a crash', $r4 === null);

chat_reset();
$GLOBALS['CHAT_HTTP'] = chat_test_http([]);
$store = chat_store();
foreach (chat_models() as $m) {
    if ($m['provider'] === 'groq') {
        $store->addUsage(gmdate('Y-m-d'), $m['key'], 1, $m['per_day'], 0);
    }
}
check('a model over its daily units is skipped without being called', chat_ai_reply([['role' => 'system', 'content' => 'x'], ['role' => 'user', 'content' => 'hi']]) === null);

/* ---------------- the system prompt and widget config carry the owner's rules ---------------- */

$prompt = chat_system_prompt();
check('the prompt says Vee is an AI assistant', str_contains($prompt, 'AI assistant') && str_contains($prompt, 'not a person'));
check('the prompt tells Vee to pass guest posts to the team', str_contains($prompt, 'guest_post'));
check('the prompt forbids inventing prices', str_contains(strtolower($prompt), 'invent') && str_contains($prompt, 'price'));

$cfg = chat_widget_config('', ['template' => 'home']);
check('the widget always carries the AI assistant label', $cfg['label'] === 'AI assistant' && $cfg['name'] === 'Vee');
check('the home greeting invites a question', str_contains($cfg['greeting'], "I'm Vee"));
$svcCfg = chat_widget_config('seo-services', ['template' => 'service', 'h1' => 'Search Engine Optimization Services']);
check('a service page greeting names the topic', str_contains($svcCfg['greeting'], 'search engine optimization') || str_contains($svcCfg['greeting'], 'SEO'), $svcCfg['greeting']);
check('a service page gets quick replies naming the topic', str_contains($svcCfg['quickReplies'][0], 'include'), json_encode($svcCfg['quickReplies']));
check('a tool page gets its own quick replies', chat_widget_config('free-seo-audit', ['template' => 'audit'])['quickReplies'][0] === 'What should I fix first?');
check('the avatar is the on-dark mark, legible on a coloured backdrop', str_contains($cfg['avatar'], 'icon-mark-on-dark'));

/* ---------------- team hours mean something outside India too ---------------- */

/* security_visitor_country() memoises its result for the whole process (it
   reads $_SERVER once), so these pass the country straight in rather than
   changing $_SERVER partway through one PHP run, which it would not see. */
check('an Indian visitor gets the plain hours', chat_hours_phrase('Asia/Kolkata', 'IN') === 'Monday to Friday, 9:30 AM to 6:30 PM India time');
$usPhrase = chat_hours_phrase('America/Los_Angeles', 'US');
check('a US visitor gets the GMT offset, not a bare "India time"', str_contains($usPhrase, 'GMT+5:30') && !str_contains($usPhrase, 'India time'), $usPhrase);
check('a US visitor also sees their own local time', str_contains($usPhrase, 'for you'), $usPhrase);
check('an unknown timezone still gives the offset, just no local conversion', !str_contains(chat_hours_phrase('', 'US'), 'for you') && str_contains(chat_hours_phrase('', 'US'), 'GMT+5:30'));
check('a garbled timezone string does not crash the reply', is_string(chat_hours_phrase('not/a/real/zone', 'GB')));
check('the reply-time line wraps the hours phrase', str_contains(chat_reply_time_line('America/Los_Angeles', 'US'), 'GMT+5:30'));

/* ---------------- a full conversation: new session, a reply, a lead, one email ---------------- */

chat_reset();
$GLOBALS['CHAT_HTTP'] = chat_test_http([chat_test_ok('Happy to help with your website. What is your budget?', ['intent' => 'sales_web', 'service' => 'Website design and development'])]);
$_SERVER['HTTP_ORIGIN'] = 'https://vturnu.com';
$_SERVER['HTTP_HOST'] = 'vturnu.com';

$out1 = chat_send(['text' => 'Hi, I need a new website for my shop', 'page' => '/web-design/', 'title' => 'Website Design'], '');
check('a first message creates a session and answers', $out1['ok'] === true && $out1['sid'] !== '' && $out1['replies'] !== [], json_encode($out1));
check('no lead yet, nothing to email', $out1['notify'] === false);

$GLOBALS['CHAT_HTTP'] = chat_test_http([chat_test_ok('Thanks, I will pass this to the team.', ['intent' => 'sales_web', 'handoff' => true, 'lead' => ['name' => 'Priya', 'company' => '', 'website' => '', 'budget' => '', 'timeline' => '']])]);
$out2 = chat_send(['sid' => $out1['sid'], 'text' => 'My email is priya@gmail.com and I am Priya', 'page' => '/web-design/', 'title' => 'Website Design'], $out1['sid']);
check('an email in the message is picked up and triggers notify', $out2['notify'] === true, json_encode($out2));

$notifyResult = chat_handle(['action' => 'notify', 'sid' => $out1['sid']]);
check('notify succeeds', $notifyResult['ok'] === true);
check('exactly one email went out', count($GLOBALS['captured_mail']) === 1, (string) count($GLOBALS['captured_mail']));
$mail = $GLOBALS['captured_mail'][0];
check('the email goes to connect@ and replies to the visitor', in_array(ENQUIRY_TO, $mail['to'], true) && str_contains($mail['reply_to'], 'priya@gmail.com'), json_encode($mail));
check('the subject names the lead', str_contains($mail['subject'], 'Priya'), $mail['subject']);
check('the transcript carries both messages', str_contains($mail['html'], 'new website') && str_contains($mail['html'], 'Happy to help'), 'len=' . strlen($mail['html']));

$s1 = chat_store()->session($out1['sid']);
check('the chat became a lead', $s1['enquiry_id'] > 0);
check('a second notify call sends nothing new', chat_deliver($s1, 'notify') === '');

/* ---------------- an outreach chat never gets scored as junk, and stays off the sales pipeline ---------------- */

chat_reset();
$GLOBALS['CHAT_HTTP'] = chat_test_http([chat_test_ok('Thanks for wanting to write for VTurnU. Please share a link to something you have published, and your email.', ['intent' => 'guest_post', 'handoff' => true])]);
$o1 = chat_send(['text' => 'Hi, I would like to write a guest post with backlinks to my site. We offer SEO services too.', 'page' => '/', 'title' => 'Home'], '');
$GLOBALS['CHAT_HTTP'] = chat_test_http([chat_test_ok('Got it, thank you.', ['intent' => 'guest_post'])]);
$o2 = chat_send(['sid' => $o1['sid'], 'text' => 'Sure, I am outreach@gmail.com and here is myblog.test', 'page' => '/', 'title' => 'Home'], $o1['sid']);
$GLOBALS['FAKE_DB']->log = [];
chat_handle(['action' => 'notify', 'sid' => $o1['sid']]);
$outreachSession = chat_store()->session($o1['sid']);
check('the outreach lead is saved, not marked junk', $outreachSession['enquiry_id'] > 0);
$outreachInsert = null;
foreach ($GLOBALS['FAKE_DB']->log as [$sql, $params]) {
    if (str_starts_with(trim($sql), 'INSERT INTO enquiries')) {
        $outreachInsert = $params;
        break;
    }
}
check('a lead insert happened for this chat', $outreachInsert !== null);
check('the lead did not land in the junk status despite guest-post wording', ($outreachInsert[':status'] ?? '') !== 'junk', json_encode($outreachInsert));
check('the outreach lead is stored under the outreach source, not the sales one', ($outreachInsert[':source'] ?? '') === 'live-chat-outreach', json_encode($outreachInsert));

/* ---------------- a chat that never leaves contact details still reaches the team ---------------- */

chat_reset();
$GLOBALS['CHAT_HTTP'] = chat_test_http([chat_test_ok('Sure, movement in six to eight weeks is typical.'), chat_test_ok('Happy to help further.')]);
$n1 = chat_send(['text' => 'How long does SEO take to show results?', 'page' => '/seo-services/', 'title' => 'SEO'], '');
$n2 = chat_send(['sid' => $n1['sid'], 'text' => 'Good to know, thanks', 'page' => '/seo-services/', 'title' => 'SEO'], $n1['sid']);
check('two messages with no contact details still owe an email', $n2['notify'] === false);
$noContactSession = chat_store()->session($n1['sid']);
chat_deliver($noContactSession, 'cron');
check('the no-contact chat is emailed once there are two messages', count($GLOBALS['captured_mail']) === 1, (string) count($GLOBALS['captured_mail']));
check('a no-contact email says so in the subject', str_contains($GLOBALS['captured_mail'][0]['subject'], 'no contact details'));

/* ---------------- guards ---------------- */

chat_reset();
check('an empty message is blocked', chat_send(['text' => '', 'page' => '/'], '')['ok'] === false);
check('a honeypot trip is blocked', chat_send(['text' => 'hello', 'hp' => 'filled', 'page' => '/'], '')['ok'] === false);
check('a very long message is refused with a clear error', chat_send(['text' => str_repeat('a', 1200), 'page' => '/'], '')['error'] === 'too_long');
$_SERVER['HTTP_ORIGIN'] = 'https://evil.example.com';
check('a different origin is blocked', chat_handle(['text' => 'hello there', 'page' => '/'])['ok'] === false);
$_SERVER['HTTP_ORIGIN'] = 'https://vturnu.com';

/* ---------------- spam closes the chat after two strikes, with no contact given ---------------- */

chat_reset();
$GLOBALS['CHAT_HTTP'] = chat_test_http([chat_test_ok('I cannot help with that here.', ['intent' => 'spam']), chat_test_ok('I cannot help with that here.', ['intent' => 'spam'])]);
$sp1 = chat_send(['text' => 'buy cheap viagra now', 'page' => '/'], '');
$sp2 = chat_send(['sid' => $sp1['sid'], 'text' => 'cheap casino bonus here', 'page' => '/'], $sp1['sid']);
check('two spam turns with no contact details close the chat', chat_store()->session($sp1['sid'])['status'] === 'spam');

/* ---------------- includes/chat-schema.sql matches db/schema.sql ---------------- */

$schemaFile = (string) file_get_contents('/site/db/schema.sql');
$chatOwn = (string) file_get_contents('/site/includes/chat-schema.sql');
$chatOwnBody = trim(substr($chatOwn, (int) strpos($chatOwn, 'create table if not exists chat_sessions')));
$chatStart = strpos($schemaFile, 'create table if not exists chat_sessions');
check('includes/chat-schema.sql matches the tail of db/schema.sql', $chatStart !== false && trim(substr($schemaFile, $chatStart)) === $chatOwnBody, (string) $chatStart);

done();
ob_end_flush();
