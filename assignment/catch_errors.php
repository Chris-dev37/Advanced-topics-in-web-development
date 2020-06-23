<?php
require_once("config.php");
require_once("functions.php");



//if $format is missing from the url then it is set to "xml"
if (!isset($_GET["format"]) || empty($_GET["format"])){
    $_GET["format"] = "xml";
}

// turn $_GET params into PHP variables
extract($_GET);

// creating a new array with the matching elements from $param and the key values from $_GET 
$get = array_intersect(PARAM, array_keys($_GET));

//if the new $get array contains less than 4 elements then the appropriate message will appear
if (count($get) < 4) {
	generate_errors(1000, $format);
	exit;
}
//if the new $get array contains more than 4 elements then the appropriate message will appear
if (count($get) > 4) {
    generate_errors(1100, $format);
    exit;
}

// loading the rates file then getting all the "code" elements regardless of their position in the file
$rates = simplexml_load_file("rates.xml");
$rate_loop = $rates->xpath("//code");


foreach ($rate_loop as $key=>$val){
    $codes[] = (string) $val;
}

// checking if the $from param is vaild in the rates file($codes array), because if it wasnt it would trown an error as it cant complete the xpath correctly. if the $from param is valid then the "live" node is checked, then if that is 0 then the error message is shown 
if(in_array($from, $codes)){
    $from_live = $rates->xpath("//code[.='$from']/@live");
    $check_live = $from_live[0]->live;

    if ($check_live == 0){
        generate_errors(1200, $format);
        exit;
    }
}

// $to and $from arnt recognised currencies
if(!in_array($to, $codes) || !in_array($from, $codes)){
    generate_errors(1200, $format);
    exit;
}

// $amnt is not a decimal value
if (!preg_match('/^[+-]?(\d*\.\d+([eE]?[+-]?\d+)?|\d+[eE][+-]?\d+)$/', $amnt)) {
	generate_errors(1300, $format);
	exit;
}

//if the format stored in our $frmts array in the config.php file doesnt match that of the $format extracted from the $_GET then the appropriate message will show
if (!in_array($format, FRMTS)){
    generate_errors(1400, $format);
    exit;
}

?>