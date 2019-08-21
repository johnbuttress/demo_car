<?php

// Set up site constants
ini_set('display_errors', 1);
include('../includes/init.php');


// Extract all manufacturers
$m = new manufacturer();
$manufacturers = $m->renderDropDown();

// search criteria
$basic = getSearchCriteria(1);

// Initialise form content
$form = generateCSRFToken();

foreach($basic as $bK=>$bV) {

    $element = new searchForm();
    $newEl = $element->render($bV);
    
    // Concatenate form with rendered element provided not empty.
    $form .= ($newEl !== FALSE) ? '<p>' . $newEl . '</p>' : '';
}


include(TEMPLATES . "views.php");