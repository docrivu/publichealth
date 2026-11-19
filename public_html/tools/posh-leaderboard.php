<?php
/**
 * POSH Smart Check — leaderboard API.
 * Upload next to posh-game.html (public_html/tools/). Needs no database:
 * scores are kept in tools/posh-data/leaderboard.json, which this script
 * creates and protects with a "deny all" .htaccess.
 *
 *   GET  posh-leaderboard.php?action=top[&id=PLAYER_ID]   -> top players (JSON)
 *   POST posh-leaderboard.php  {id, name, levels:[6 x 0..100]} -> saves best score
 *
 * Trainer tools (only work once ADMIN_KEY below is set):
 *   GET  posh-leaderboard.php?action=export&key=ADMIN_KEY  -> CSV download of all players
 *   GET  posh-leaderboard.php?action=reset&key=ADMIN_KEY   -> clears the leaderboard
 */
declare(strict_types=1);

const ADMIN_KEY   = '';      // set a long random secret to enable export/reset; leave '' to disable
const TOP_N       = 50;      // rows returned to the game
const MAX_PLAYERS = 5000;    // hard cap on stored players
const LEVELS      = 6;
const IP_LIMIT    = 30;      // max submissions per IP per hour

$dataDir  = __DIR__ . '/posh-data';
$dataFile = $dataDir . '/leaderboard.json';

header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

function out(int $code, array $data): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function stars_of(int $s): int { return $s >= 90 ? 3 : ($s >= 60 ? 2 : ($s > 0 ? 1 : 0)); }

function str_len(string $s): int { return function_exists('mb_strlen') ? mb_strlen($s, 'UTF-8') : strlen($s); }

/** Open the store under an exclusive lock, let $fn change it, write it back if asked. */
function with_store(string $dir, string $file, callable $fn) {
    if (!is_dir($dir)) {
        if (!@mkdir($dir, 0750, true) && !is_dir($dir)) out(500, ['ok' => false, 'error' => 'Storage folder could not be created.']);
    }
    if (!file_exists($dir . '/.htaccess')) @file_put_contents($dir . '/.htaccess', "<IfModule mod_authz_core.c>\n  Require all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\n  Deny from all\n</IfModule>\n");
    if (!file_exists($dir . '/index.html')) @file_put_contents($dir . '/index.html', '');

    $fh = @fopen($file, 'c+');
    if (!$fh) out(500, ['ok' => false, 'error' => 'Storage file could not be opened.']);
    flock($fh, LOCK_EX);
    $raw  = stream_get_contents($fh);
    $data = json_decode($raw ?: '', true);
    if (!is_array($data)) $data = [];
    $data += ['players' => [], 'ips' => []];

    [$result, $write] = $fn($data);

    if ($write) {
        ftruncate($fh, 0);
        rewind($fh);
        fwrite($fh, json_encode($data, JSON_UNESCAPED_UNICODE));
        fflush($fh);
    }
    flock($fh, LOCK_UN);
    fclose($fh);
    return $result;
}

function ranked(array $players): array {
    $rows = array_values($players);
    usort($rows, fn($a, $b) => [$b['score'], $b['stars'], $a['at']] <=> [$a['score'], $a['stars'], $b['at']]);
    return $rows;
}

function public_row(array $p, int $rank, ?string $me): array {
    return ['rank' => $rank, 'name' => $p['name'], 'score' => $p['score'], 'stars' => $p['stars'],
            'date' => gmdate('Y-m-d', (int)$p['at']), 'me' => $me !== null && hash_equals($p['id'], $me)];
}

function valid_id($id): bool { return is_string($id) && preg_match('/^[a-f0-9]{16,40}$/', $id) === 1; }

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = $_GET['action'] ?? ($method === 'POST' ? 'submit' : 'top');

/* ---------- admin ---------- */
if ($action === 'export' || $action === 'reset') {
    $key = (string)($_GET['key'] ?? '');
    if (ADMIN_KEY === '' || !hash_equals(ADMIN_KEY, $key)) out(403, ['ok' => false, 'error' => 'Forbidden']);

    if ($action === 'reset') {
        with_store($dataDir, $dataFile, function (&$d) { $d = ['players' => [], 'ips' => []]; return [null, true]; });
        out(200, ['ok' => true, 'message' => 'Leaderboard cleared.']);
    }
    $rows = with_store($dataDir, $dataFile, fn(&$d) => [ranked($d['players']), false]);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="posh-leaderboard-' . gmdate('Y-m-d') . '.csv"');
    $o = fopen('php://output', 'w');
    fputcsv($o, ['Rank', 'Name', 'Total (of 600)', 'Stars (of 18)', 'L1 Spot It', 'L2 Red Flags', 'L3 Committee', 'L4 Journey', 'L5 Clock', 'L6 Final Call', 'Last improved (UTC)']);
    foreach ($rows as $i => $p) {
        // prefix risky leading characters so spreadsheet apps don't treat names as formulas
        $name = preg_match('/^[=+\-@]/', $p['name']) ? "'" . $p['name'] : $p['name'];
        fputcsv($o, array_merge([$i + 1, $name, $p['score'], $p['stars']], $p['levels'], [gmdate('Y-m-d H:i', (int)$p['at'])]));
    }
    exit;
}

/* ---------- top ---------- */
if ($action === 'top') {
    $me = isset($_GET['id']) && valid_id($_GET['id']) ? $_GET['id'] : null;
    $res = with_store($dataDir, $dataFile, function (&$d) use ($me) {
        $rows = ranked($d['players']);
        $entries = [];
        foreach (array_slice($rows, 0, TOP_N) as $i => $p) $entries[] = public_row($p, $i + 1, $me);
        $mine = null;
        if ($me !== null) foreach ($rows as $i => $p) if (hash_equals($p['id'], $me)) { $mine = public_row($p, $i + 1, $me); break; }
        return [['ok' => true, 'entries' => $entries, 'me' => $mine, 'players' => count($rows)], false];
    });
    out(200, $res);
}

/* ---------- submit ---------- */
if ($action === 'submit' && $method === 'POST') {
    $raw = file_get_contents('php://input', false, null, 0, 4096);
    $in  = json_decode($raw ?: '', true);
    if (!is_array($in)) out(400, ['ok' => false, 'error' => 'Bad request']);

    $id = $in['id'] ?? null;
    if (!valid_id($id)) out(400, ['ok' => false, 'error' => 'Bad player id']);

    $name = preg_replace('/\s+/u', ' ', trim((string)($in['name'] ?? '')));
    if ($name === null || str_len($name) < 2 || str_len($name) > 24 || !preg_match("/^[\p{L}\p{M}\p{N} .'\-]+$/u", $name)) {
        out(400, ['ok' => false, 'error' => 'Name must be 2–24 letters, numbers, spaces, dots, hyphens or apostrophes.']);
    }

    $levels = $in['levels'] ?? null;
    if (!is_array($levels) || count($levels) !== LEVELS) out(400, ['ok' => false, 'error' => 'Bad levels']);
    $levels = array_map(fn($v) => max(0, min(100, (int)$v)), array_values($levels));
    $score  = array_sum($levels);
    $stars  = array_sum(array_map('stars_of', $levels));

    $ipKey = hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . '|posh');
    $now   = time();

    $res = with_store($dataDir, $dataFile, function (&$d) use ($id, $name, $levels, $score, $stars, $ipKey, $now) {
        // rate limit per IP (hashed, never stored raw)
        foreach ($d['ips'] as $k => $times) {
            $d['ips'][$k] = array_values(array_filter($times, fn($t) => $t > $now - 3600));
            if (!$d['ips'][$k]) unset($d['ips'][$k]);
        }
        if (count($d['ips'][$ipKey] ?? []) >= IP_LIMIT) return [['ok' => false, 'error' => 'Too many submissions — try again later.', '_code' => 429], true];
        $d['ips'][$ipKey][] = $now;

        $p = $d['players'][$id] ?? null;
        if ($p === null) {
            if (count($d['players']) >= MAX_PLAYERS) return [['ok' => false, 'error' => 'Leaderboard is full.', '_code' => 507], true];
            $d['players'][$id] = ['id' => $id, 'name' => $name, 'score' => $score, 'stars' => $stars, 'levels' => $levels, 'at' => $now];
        } else {
            $d['players'][$id]['name'] = $name;
            if ($score > $p['score']) {
                $d['players'][$id] = array_merge($d['players'][$id], ['score' => $score, 'stars' => $stars, 'levels' => $levels, 'at' => $now]);
            }
        }
        $rows = ranked($d['players']);
        $rank = 1;
        foreach ($rows as $i => $r) if ($r['id'] === $id) { $rank = $i + 1; break; }
        return [['ok' => true, 'rank' => $rank, 'best' => $d['players'][$id]['score'], 'players' => count($rows)], true];
    });
    $code = $res['_code'] ?? 200;
    unset($res['_code']);
    out($code, $res);
}

out(405, ['ok' => false, 'error' => 'Method not allowed']);
