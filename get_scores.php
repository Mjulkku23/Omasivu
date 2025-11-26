<?php
// /~220112/Omasivu/actions/get_scores.php
// Lukee quiz_scores.json -tiedoston ja palauttaa sen sisällön JSON:ina.

header('Content-Type: application/json; charset=utf-8');

$file = __DIR__ . '/quiz_scores.json';

// Jos tiedostoa ei ole, palautetaan tyhjä lista
if (!is_file($file)) {
    echo '[]';
    exit;
}

$json = @file_get_contents($file);
if ($json === false) {
    echo '[]';
    exit;
}

$data = json_decode($json, true);
if (!is_array($data)) {
    echo '[]';
    exit;
}

// Palautetaan tulokset
echo json_encode($data, JSON_UNESCAPED_UNICODE);
