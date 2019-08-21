<?php
// Core functions
//require('db_functions.php');


// Generate csrf token, encode it to hex, render into input element, set session variable.
function generateCSRFToken() {
    $t = openssl_random_pseudo_bytes(64);
    $bt = bin2hex($t);

    $out = '<input type="hidden" name="_csrf" value="' . $bt . '" />';
    $_SESSION['csrf'] = $bt;

    return $out;
}




// Returns list of searchable element names.
function getFormKeys() {
    // DB lookup
    $keysDB = getValidSearchCriteria();
//var_dump($keysDB);
    // Initiliase array
    $out = [];

    // Populate with each element key
    foreach($keysDB as $k=> $v) {
        $out[] = $v->search_key;
    }

    return $out;
}







