<?php

// validators.php

// Series of validation functions






/* Checks that submitted search keys are in the list of those permitted.
Paramaters: $get (array) = info submitted via $_GET
            $available (array) = list of available permittedkeys

Return: False if all submitted are available
        True if one or more keys are not available
*/        
function validateGet($get, $available) {

    // Initialise validation flag
    $valid = true;

//http://cd.test/search.php?_csrf=beb20e9b3d8dfa824da598722f9ef50bed2735106bdd37a327302f58a5ff57175a920e2ffc5cedaa8f2ac388eff0bb762b5cccf7cd8f4adb720fb5c82356f7f0&search_year_min=2004&search_year_max=2007&search_colour=4&search_engine_size_min=0&search_engine_size_max=0&search_fuel=0&search_body_style=0&search_transmission=0&search_alloy_wheels=on&search_colour_coded_bumper=on&search_insurance_group_min=0&search_insurance_group_max=0&search_price_min=0&search_price_max=0


    // List of exceptions for $_GET
    $exceptions = [
        '_csrf',
        'search_manufacturer_id',
        'search_year_min',
        'search_year_max',
        'search_insurance_group_min',
        'search_insurance_group_max',
        'search_engine_size_min',
        'search_engine_size_max',
        'search_price_min',
        'search_price_max',
    ];

    // Check each submitted key providing flag is false
    foreach($get as $gK => $v) {

        // If the validity flag is true
        if ($valid) {

            if (!in_array($gK, $available)) {
                // Not in the permitted GET key list, so need to check exception list
                $chkExc = true;
            } else {
                $chkExc = false;
            }
    
            // Check the exceptions list
            if ($chkExc) {
    
                if (in_array($gK, $exceptions)) {
                    // If key is found
                    $valid = true;
                } else {
                    // If key is not found
                    $valid = false;
                }
                
            }
    
        }

    }

    return $valid;

}
