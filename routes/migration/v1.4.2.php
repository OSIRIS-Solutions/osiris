<?php

require_once BASEPATH . '/php/HttpClient.php';

// Download from GitHub: stefangabos/world_countries
try {
    $json = HttpClient::get('https://raw.githubusercontent.com/stefangabos/world_countries/refs/heads/master/data/countries/_combined/world.json', ['Accept: application/json'], 30);
} catch (\RuntimeException $e) {
    error_log('OSIRIS: Country download failed. ' . $e->getMessage());
    echo "Error downloading file.";
    include BASEPATH . "/footer.php";
    die;
}
$countries = json_decode($json, true, 512, JSON_NUMERIC_CHECK);
if ($countries === null) {
    echo "Error decoding JSON.";
    include BASEPATH . "/footer.php";
    die;
}
// check if countries is an array
if (!is_array($countries)) {
    echo "Error: countries is not an array.";
    include BASEPATH . "/footer.php";
    die;
}
// check if countries is empty
if (empty($countries) || !array_is_list($countries)) {
    echo "Error: countries is empty.";
    include BASEPATH . "/footer.php";
    die;
}
// Do not replace existing data with an unexpected response from the remote server.
foreach ($countries as $country) {
    if (!is_array($country)
        || !is_string($country['alpha2'] ?? null) || !preg_match('/^[a-z]{2}$/i', $country['alpha2'])
        || !is_string($country['alpha3'] ?? null) || !preg_match('/^[a-z]{3}$/i', $country['alpha3'])
        || !is_string($country['en'] ?? null) || $country['en'] === ''
        || (isset($country['de']) && !is_string($country['de']))) {
        echo "Error: invalid country data.";
        include BASEPATH . "/footer.php";
        die;
    }
}

// update countries
$osiris->countries->deleteMany([]);
foreach ($countries as $country) {
    $iso = strtoupper($country['alpha2']);
    $osiris->countries->insertOne([
        'iso' => $iso,
        'iso3' => strtoupper($country['alpha3']),
        'name' => $country['en'],
        'name_de' => $country['de'] ?? $country['en'],
        'continent' => Country::countryToContinent($iso),
    ]);
}

// success message
echo "Countries updated successfully. <br>";

// flush();
// ob_flush();
