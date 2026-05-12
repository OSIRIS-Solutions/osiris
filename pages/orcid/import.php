<?php

/**
* See import-openalex.php for reference
* 
* Trigger once the function to get all works form ORCID and parse
*
* Visualize a list of all works of the user found in ORCID that are not yet in Osiris
* Make import/reject buttons for each work
*/

require_once BASEPATH . '/php/OrcidParser.php';

$username = $_SESSION['username'];

$orcid_parser = new OrcidParser($username);
$data = $orcid_parser->getWorks();

echo '<pre>';
print_r($data);
echo '</pre>';
?>