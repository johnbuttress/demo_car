<?php

session_start();

// Guard against invalid execution
if (!isset($_SESSION['csrf'])) {
    echo 'No token';
}

// Check for invalid search keys. First

// collect list of valid keys
include('../includes/init.php');

$validKeys = getFormKeys();
// Check for data submitted via get

if (!isset($_GET)) {
    $out = 'No search options selected';

} else {

    // Ensure all GET requests are genuine
    $v = validateGet($_GET, $validKeys);
    
    if (!$v) {
        die('Invalid option detected');
    }

    
    // Get data type IDs and matching search_keys
    $types = getDataTypesIDs($_GET);

    // Get vehicle IDs from matching specs
    $vehID = getVehiclesBySearch($_GET, $types);

    $records = [];
    $cars = [];


    foreach($vehID as $veh => $ve) {
        var_dump($ve);

        // Get all vehicles of matchng IDs
       // $data = getVehicleByID($ve->vehicle_id);
        $cars[$ve->vehicle_id] = getVehicleByID($ve->vehicle_id);

    }

        foreach($cars as $car => $vdvR) {

            foreach($vdvR as $e => $vdv) {
                // Whether or not to include this vehicle specification. Initially false so no null or zero length strings creep in.
                $inc = false;

                if ($vdv->data_type_id == 1) {
                    // Get manufacturer name
                    $mm  = getModelManufacturerFromModelID($vdv->index_id);
                    $out = implode(' ' , $mm);
            //                    $records[$vdv->vehicle_id]['make_model'] = $out;
                    $inc = true;
                } else {
				
					if ($vdv->element_type == 't') {
						// Data value
					//    $records[$vdv->vehicle_id][$vdv->caption] = $vdv->data_value;
						$out = $vdv->data_value;
						$inc = true;            
					} 
					if ($vdv->element_type == 's') {
						// Index ID. Data value is empty
						$out = getValueFromIndex($vdv->data_type_id, $vdv->index_id);
?><pre><?php
var_dump($vdv->data_type_id, $vdv->index_id, $out);
?></pre><?php    
					//    $records[$vdv->vehicle_id][$vdv->caption] = $valFromIndex;

						//$out = $valFromIndex;
						$inc = true;
					}
					if ($vdv->element_type == 'c') {
					//    $records[$vdv->vehicle_id][$vdv->caption] = 'Yes';
						$out = 'Yes';
						$inc = true;
					}

				
				}	
				

                // Only if flagged for inclusion, update the vehicle data array
                if ($inc) {
                    $records[$vdv->vehicle_id][$vdv->caption] = $out;
                    $specsInc ++;
                }


            }
                        
        }

        $list = '';
        
    // Render specs to a list
    $html = '';

    foreach($records as $recK => $c) {
    
        $newItem = new vehicle();
        $html .= $newItem->renderResult($c);
        
    }

    $results = $html;

    include(TEMPLATES . "results.php");}


?>