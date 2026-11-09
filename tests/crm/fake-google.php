<?php
/* A stand-in for Google Calendar, as far as the booking code talks to it, for
   tests/crm/booking-test.php and the local preview (tests/booking/preview.php).
   Records every call. */
declare(strict_types=1);

final class FakeGoogle
{
    public array $calls = [];
    public array $busy = [];
    public array $events = [];
    public int $n = 0;
    public bool $hideOwn = false;
    public bool $failFreeBusy = false;
    public function __invoke(string $method, string $url, ?array $body): array
    {
        $this->calls[] = [$method, $url, $body];
        $path = (string) parse_url($url, PHP_URL_PATH);
        parse_str((string) parse_url($url, PHP_URL_QUERY), $q);
        if (str_contains($url, 'oauth2.googleapis.com/token')) {
            return ($body['refresh_token'] ?? '') === 'revoked' ? [400, ['error' => 'invalid_grant', 'error_description' => 'Token has been expired or revoked.']] : [200, ['access_token' => 'ya29.test', 'expires_in' => 3599]];
        }
        if ($path === '/calendar/v3/freeBusy' && $this->failFreeBusy) {
            return [503, ['error' => ['message' => 'Backend Error']]];
        }
        if ($path === '/calendar/v3/freeBusy') {
            $from = strtotime($body['timeMin']);
            $to = strtotime($body['timeMax']);
            $busy = [];
            foreach (array_merge($this->busy, $this->hideOwn ? [] : $this->blocks()) as [$s, $e]) {
                if ($s < $to && $e > $from) {
                    $busy[] = ['start' => gmdate('Y-m-d\TH:i:s\Z', max($s, $from)), 'end' => gmdate('Y-m-d\TH:i:s\Z', min($e, $to))];
                }
            }
            return [200, ['calendars' => ['primary' => ['busy' => $busy]]]];
        }
        if ($path === '/calendar/v3/calendars/primary/events' && $method === 'POST') {
            $id = 'ev' . (++$this->n);
            $ev = $body + ['id' => $id, 'status' => 'confirmed', 'iCalUID' => $id . '@google.com', 'htmlLink' => 'https://www.google.com/calendar/event?eid=' . $id, 'sequence' => 0,
                'hangoutLink' => 'https://meet.google.com/abc-defg-' . str_pad((string) $this->n, 3, '0', STR_PAD_LEFT)];
            $ev['attendees'] = array_map(static fn(array $a): array => $a + ['responseStatus' => 'needsAction'], $body['attendees'] ?? []);
            $this->events[$id] = $ev;
            return [200, $ev];
        }
        if ($path === '/calendar/v3/calendars/primary/events' && $method === 'GET') {
            $from = strtotime($q['timeMin']);
            $to = strtotime($q['timeMax']);
            $items = $this->hideOwn ? [] : array_values(array_filter($this->events, static fn(array $e): bool => strtotime($e['start']['dateTime']) < $to && strtotime($e['end']['dateTime']) > $from));
            foreach ($this->busy as [$s, $e]) {
                if ($s < $to && $e > $from) {
                    $items[] = ['id' => 'other', 'status' => 'confirmed', 'start' => ['dateTime' => gmdate('c', $s)], 'end' => ['dateTime' => gmdate('c', $e)]];
                }
            }
            return [200, ['timeZone' => 'Asia/Kolkata', 'items' => $items]];
        }
        if (preg_match('#^/calendar/v3/calendars/primary/events/([^/]+)$#', $path, $m)) {
            $id = rawurldecode($m[1]);
            if (!isset($this->events[$id])) {
                return [404, ['error' => ['message' => 'Not Found']]];
            }
            if ($method === 'GET') {
                return [200, $this->events[$id]];
            }
            if ($method === 'PATCH') {
                $this->events[$id] = array_merge($this->events[$id], $body);
                $this->events[$id]['sequence']++;
                return [200, $this->events[$id]];
            }
            if ($method === 'DELETE') {
                unset($this->events[$id]);
                return [204, []];
            }
        }
        return [500, ['error' => ['message' => 'FakeGoogle does not know ' . $method . ' ' . $url]]];
    }
    private function blocks(): array
    {
        return array_map(static fn(array $e): array => [strtotime($e['start']['dateTime']), strtotime($e['end']['dateTime'])], array_values($this->events));
    }
    public function last(string $method, string $needle = ''): ?array
    {
        foreach (array_reverse($this->calls) as $c) {
            if ($c[0] === $method && ($needle === '' || str_contains($c[1], $needle))) {
                return $c;
            }
        }
        return null;
    }
}
