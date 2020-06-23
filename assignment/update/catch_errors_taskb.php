<?php
require_once("../config.php");
require_once("../functions.php");

$json_rates_err = file_get_contents(API_URL) or die ("Error: Unable to load file");
$rates_err = json_decode($json_rates_err);

foreach($rates_err->rates as $k=>$v){
    $fixer_c[]= (string) $k;
}

// turn $_GET params into PHP variables
extract($_GET);

// creating a new array with the matching elements from $param_taskb and the key values from $_GET 
$get_taskB = array_intersect(PARAM_TESKB, array_keys($_GET));

//if the new $get_taskb array contains less than 2 elements then the appropriate message will appear
// if (count($get_taskB)< 2){
//     echo generate_errors(1000, $error_msgs);
//     exit;
// }

//if the new $get_taskb array contains less than 2 elements then the appropriate message will appear
if(count($get_taskB)< 2){
    generate_errors(2100, ERROR_MSGS);
    exit;
}

// loading the ISO list of global currencies xml file then getting all the "Ccy" elements regardless of their position in the file
$codes_xml = simplexml_load_file(ISO_XML)->xpath("//Ccy");

foreach($codes_xml as $key=>$val){
    $codes[] = (string) $val;
}

//if the format stored in our $frmts array in the config.php file doesnt match that of the $format extracted from the $_GET then the appropriate message will show
if(!in_array($action, ACTIONS_TASKB)){
    generate_errors(2000, ERROR_MSGS);
    exit;
}

// checking to see if the cur param is empty
if($cur == ""){
    generate_errors(2100, ERROR_MSGS);
    exit;
}

// test to see if $cur is part of the ISO's list of global currency codes, if it isnt then the appropriate message will appear
if(!in_array($cur, $codes)){
    generate_errors(2200, ERROR_MSGS);
    exit;
}

// test to see if $cur has a rate available within the fixer api
if(!in_array($cur, $fixer_c)){
    generate_errors(2300, ERROR_MSGS);
    exit;
}

// test to see if $cur is GBP, GBP cant be updated as its the base currency
if($cur == "GBP"){
    generate_errors(2400, ERROR_MSGS);
    exit;
}
?>