<?php

// Read site variable file into array
$env = file('../includes/.config');

foreach($env as $eK=> $eV){
    // split each entry
    $line = explode('=', $eV);
    
    // Define constants using value pairs
    define($line[0], trim($line[1]));
}
