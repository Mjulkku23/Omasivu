<?php
// /~220112/Omasivu/actions/save_score.php
// Tallentaa tietovisan tuloksen tiedostoon ja palauttaa JSON-vastauksen.

header('Content-Type: application/json; charset=utf-8');

// Sallitaan vain POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'error'   => 'Vain POST-pyyntö sallittu.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Luetaan ja siistitään syöte
$nick  = isset($_POST['nick'])  ? trim($_POST['nick'])  : '';
$score = isset($_POST['score']) ? (int)$_POST['score']  : null;
$total = isset($_POST['total']) ? (int)$_POST['total']  : null;

if ($nick === '') {
    $nick = 'Anonyymi';
}

// Perusvalidointi – jos ei pisteitä, ei tallenneta
if ($score === null || $total === null) {
    echo json_encode([
        'success' => false,
        'error'   => 'Puuttuva data (score/total).'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Pieni varmistus ettei mene ihan överiksi
if ($score < 0)   $score = 0;
if ($total <= 0)  $total = 10;

// Rajoitetaan nimimerkki ja poistetaan HTML-tagit
$nick = strip_tags($nick);
if (function_exists('mb_substr')) {
    $nick = mb_substr($nick, 0, 40, 'UTF-8');
} else {
    $nick = substr($nick, 0, 40);
}

// Rakennetaan tallennettava rivi
$entry = [
    'nick'  => $nick,
    'score' => $score,
    'total' => $total,
    'ts'    => date('c') // ISO8601-aikaleima
];

$file = __DIR__ . '/quiz_scores.json';

// Luetaan olemassa oleva tiedosto (jos on)
$data = [];
if (is_file($file)) {
    $json = @file_get_contents($file);
    if ($json !== false) {
        $tmp = json_decode($json, true);
        if (is_array($tmp)) {
            $data = $tmp;
        }
    }
}

// Lisätään uusi tulos listan loppuun
$data[] = $entry;

// Halutessasi voit rajata esim. viimeiseen 100 tulokseen:
$maxEntries = 100;
if (count($data) > $maxEntries) {
    $data = array_slice($data, -$maxEntries);
}

// Kirjoitetaan takaisin tiedostoon
$ok = @file_put_contents(
    $file,
    json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

if ($ok === false) {
    echo json_encode([
        'success' => false,
        'error'   => 'Tulosta ei voitu tallentaa tiedostoon.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// JS odottaa kenttää "success": true
echo json_encode([
    'success' => true
], JSON_UNESCAPED_UNICODE);
