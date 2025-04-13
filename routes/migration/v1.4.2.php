<?php

// Download from GitHub: stefangabos/world_countries
$json = file_get_contents('https://raw.githubusercontent.com/stefangabos/world_countries/refs/heads/master/data/countries/_combined/world.json');
// check for errors
if ($json === false) {
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
if (empty($countries)) {
    echo "Error: countries is empty.";
    include BASEPATH . "/footer.php";
    die;
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
