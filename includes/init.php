<?php
session_start();

include('define_constants.php');

// Database connection
include('.db_connect.php');

// Database queries
include('db_functions.php');

// Various site functions which use queries
include('core_functions.php');

// Data validation functions
include('validators.php');

// Classes
require('class/class.manufacturer.php');
require('class/class.vehicle.php');
require('class/class.searchForm.php');