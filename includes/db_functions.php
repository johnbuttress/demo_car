<?php

// Get records using supplied query.
function sqlRead($query, $params = null) {
	
	global $db;
	
	$sqlQ = $db->prepare($query);
	
	if ($params != null) {
		$sqlQ->execute($params);
	} else {
		$sqlQ->execute();
	}
	
	$result = $sqlQ->fetchAll(PDO::FETCH_OBJ);
	
	return (!$result) ? false : $result;
}

function sqlWrite($query, $params) {
	
	global $db;
	
	$sqlQ = $db->prepare($query);
	$sqlQ->execute($params);
	$result = $sqlQ->errorInfo();
	
	return $result;
}




// Writes a new record to a SQL table and returns the new ID for that record.
// If a SQL error occurs, the error information is returned instead of a lastID.
function sql_write_lastID($query, $params) {
	
	global $db;
	
	$sqlQ = $db->prepare($query);
	
	try {
		$db->beginTransaction();
		$sqlQ->execute($params);
		$lID = $db->lastInsertId();
	    $db->commit();
		$result = $sqlQ->errorInfo();
	    $out = [$lID, $result];
	} catch(PDOException $e) {
		$db->rollback();
		$out = [$sqlQ->errorInfo(), false];
	}

	return $out;
}


// Returns the model details from a given id
function getModelFromID($id) {
	$query = "SELECT * FROM model WHERE id=?";
	$params = [$id];
	$result = sqlRead($query, $params);
	
	return $result;
}

function getAllManufacturers() {
	$query = "SELECT * FROM manufacturer ORDER BY name ASC";
	$result = sqlRead($query);

	return $result;

}

// Returns all models from a given manufacturer id
function getModelsFromManufacturerID($id) {
	$query = "SELECT * FROM model WHERE manufacturer_id=?";
	$params = [$id];
	$result = sqlRead($query, $params);
	
	return $result;
}

// Obtain all vehicle feature data types
function getDataTypes() {
	$query = "SELECT * FROM feature_var_types ORDER BY type_id ASC";
	$result = sqlRead($query);

	return $result;
}



// Get all relevant vehicle data of given ID
function getVehicleByID($id) {

	$query = "SELECT * FROM vehicle_data 
		INNER JOIN feature_var_types
		ON feature_var_types.type_id=vehicle_data.data_type_id 
		AND vehicle_data.vehicle_id=? 
		ORDER BY vehicle_data.vehicle_id ASC,
		vehicle_data.data_type_id ASC";

	$params = [$id];
	$result = sqlRead($query, $params);

	return $result;
}

// Returns the selected option name given the data type and index
function getSelectedOption($data_type_id, $index_id) {

	$query = "SELECT * FROM data_options WHERE data_type_id=? AND index_id=?";
	$params = [$data_type_id, $index_id];
	$result = sqlRead($query, $params);
	
	return $result[0]->value;
}


// Returns all options for a given multi option id.
function getOptionsFromDataType($id) {

	$query = "SELECT * FROM data_options WHERE data_type_id=? ORDER BY index_id ASC";
	$params = [$id];
	$result = sqlRead($query, $params);

	return $result;
}


// Returns all options for a given multi option id.
function getCheckboxOptions($id) {

	$query = "SELECT * FROM feature_var_types WHERE data_type_id=?";
	$params = [$id];
	$result = sqlRead($query, $params);

	return $result[0];
}


// Returns option ranges for a given multi option id.
function getOptionRangeFromDataType($id) {

	$query = "SELECT * FROM ranges WHERE data_type_id=?";
	$params = [$id];
	$result = sqlRead($query, $params);

	return $result[0];
}


function getValidSearchCriteria() {
	$query = "SELECT * FROM available_search_criteria WHERE search_level IN (?,?) order by data_type_id ASC, id ASC";
	$params = [1,2];
	$result = sqlRead($query, $params);
//var_dump($result);	
	return $result;
}


function getVED($fuelID, $co2) {
	$query = "SELECT * FROM ved_rates_from_2001 WHERE fuel_type_id=? AND min_co2 < ? AND max_co2 > ?";
	$params = [$fuelID, $co2, $co2];
	
	$result = sqlRead($query, $params);

	return $result;

}

function getLatestVehicles($limit) {
	// Get vehicle IDs for newest $limit and not sold
	$veh_ids = "SELECT vehicle_id FROM vehicle_data WHERE data_type_id=? AND data_value is NULL ORDER BY vehicle_id ASC LIMIT " . $limit;
	$p = [2011];
	
	$res = sqlRead($veh_ids, $p);

	// Send into parameter array for DB query
	$params = [];

	foreach($res as $r => $k) {
		$params[] = $k->vehicle_id;
	}

	// Generate placeholders
	$ph = str_repeat('?,', count($res));
	
	// Remove last comma
	$ph = substr($ph, 0, -1);

	// All vehicle data from listed IDs
	$query = "SELECT * FROM vehicle_data WHERE vehicle_id in (" . $ph . ") ORDER BY vehicle_id ASC, data_type_id ASC";

	$result = sqlRead($query, $params);

	return $result;
}

function getSearchCriteria($level) {
	$query = "SELECT * FROM available_search_criteria inner JOIN feature_var_types ON available_search_criteria.data_type_id=feature_var_types.type_id AND available_search_criteria.search_level=?";
	$params = [$level];
	$result = sqlRead($query, $params);

	return $result;
}

// Input parameters: $in = $_GET data
function getDataTypesIDs($get) {

    $keysIn = [];
//var_dump($in);
    // Cycle through submitted options and generate query.
    foreach($get as $gK => $v) {

		
		if ($gK != '_csrf') {
            $keysIn[] = $gK;

        }
    }

    // Generate placeholders
    $pha = str_repeat('?,', count($keysIn));
    $ph = substr($pha, 0, -1);

	// query for data_type_ids
    $query = "SELECT * FROM available_search_criteria WHERE search_key in (" . $ph . ")";

	$params = $keysIn;

	$result = sqlRead($query, $params);
var_dump($result);

	$dtid = [];
	
    foreach($result as $tK=>$tv) {
        $dtid[] = ['type_id'=> $tv->data_type_id, 'search_key' => $tv->search_key];
    }

	return $dtid;
}



function getVehiclesBySearch($g, $t) {

    $getRequest = mapDataToQuery($g, $t);
//var_dump($getRequest);
    $query = "SELECT * FROM vehicle_data WHERE " . $getRequest[0];
    
	$params = $getRequest[1];
	$result = sqlRead($query, $params);

	return $result;

}


function getElementTypeFromDataType($dt) {

	$query = "SELECT * FROM feature_var_types WHERE type_id=?";
	$params = [$dt];
	$result = sqlRead($query, $params);
	return $result[0]->element_type;
}


// Maps data type IDs to GET form data
function mapDataToQuery($get, $types) {
	// associative array for submitted keys and values

	$sp = [];

	foreach ($get as $gk => $gv) {

		foreach($types as $tk => $tv) {  

			if ($gk == $tv['search_key']) {


				// get element type from data type
				$elType = getElementTypeFromDataType($tv['type_id']);
				
				// If a value is checked
				if ($elType == 'c' ) {
					$out[] = '(data_type_id=? AND data_value=?) ';
					$p[] = '';
					$sp[] = 'Data type: ' . $tv['type_id']. ' selected.'.PHP_EOL;
				}
				
				// If a single selector value
				if ($elType == 's') {

					if ($gv != 0) {
						$end = substr($gk, -4);


						if (substr($gk, -4) == '_min') {
							$out[] = '(data_type_id=? AND index_id >= ?) ';
						} elseif (substr($gk, -4) == '_max') {
							$out[] = '(data_type_id=? AND index_id <= ?) ';
						} else {
							$out[] = '(data_type_id=? AND index_id = ?) ';
						}

						$p[] = $tv['type_id'];
						$p[] = $gv;
						
					}
	
				}

				if ($elType == 'r') {
					if ($gv != 0) {
						$end = substr($gk, -4);


						if (substr($gk, -4) == '_min') {
							$out[] = '(data_type_id=? AND data_value >= ?) ';
						} elseif (substr($gk, -4) == '_max') {
							$out[] = '(data_type_id=? AND data_value <= ?) ';
						} else {
							$out[] = '(data_type_id=? AND data_value =? ) ';
						}

						$p[] = $tv['type_id'];
						$p[] = $gv;
					}	
				}
				
			}
		}
	}

	$query = implode(" AND ", $out);

	return [$query, $p];
}


function getValueFromIndex($type_id, $index_id) {
	$query = "SELECT * FROM data_options WHERE data_type_id=? AND index_id=?";
	$params = [type_id, $index_id];
	$result = sqlRead($query, $params);
	return $result[0]->value;
}

function getModelManufacturerFromModelID($id) {
	$query = "SELECT * FROM model INNER JOIN manufacturer ON model.manufacturer_id=manufacturer.id AND model.id=?";
	$params = [$id];
	$result = sqlRead($query, $params);
	return [$result[0]->name, $result[0]->model_name];
}
?>
