<?php
@date_default_timezone_set("Europe/London");
require_once("config.php");
require_once("catch_errors.php");
require_once("functions.php");



// if the currencies file doesnt exist then a function to create one will be called 
if (!file_exists("currencies.xml")){
    generate_curr_file();
}
// if the rates file doesnt exist then a function to create one will be called 
if (!file_exists("rates.xml")){
    generate_rates_file();
}

// creating a copy of the rates file every time the service is used
if(file_exists("rates.xml")){
    copy("rates.xml", "rates"."_".time().".xml");
}

//loading the rates file to a variable to be used for getting data for the conversion
$rates_xml = simplexml_load_file("rates.xml");

// pulling the  $_GET params into PHP variables
extract($_GET);

// if no format is specified then it is set to xml
if (empty($format)){
    $format ="xml";
}

//gettting and formating the date from the timestamp
$rates_timestamp = $rates_xml["timestamp"];

// formating the timestamp to make it readable
$format_ts = date("l dS F Y H:i:s", (int)$rates_timestamp);

// if the timestamp is 2 hours or more older than when it was last called then it will update with fresh rates 
if((int)$rates_timestamp + 7200 < time()){
    update();
}

// Getting the $_GET["from"] data
$dump_code_from = $rates_xml->xpath("//code[.='$from']/parent::*");
//var_dump($dump_code_from); // vardump to test the xpath is correct
$rate_from = number_format((float)$dump_code_from[0]->rate, 2);
//echo $rate_from; // printing the data being pulled from the xpath to test if its correct
$from_code = $dump_code_from[0]->code;
//echo $from_code;
$from_cName = $dump_code_from[0]->cName;
//echo $from_cName;
$code_from_loc = $dump_code_from[0]->country;
//echo $code_from_loc;


// Getting the $_GET["to"] data
$dump_code_to = $rates_xml->xpath("//code[.='$to']/parent::*");
//var_dump($dump_rates_to); // vardump to test the xpath is correct
$rate_to = number_format((float)$dump_code_to[0]->rate, 2);
//echo $rate_to; // printing the data being pulled from the xpath to test if its correct
$to_code = $dump_code_to[0]->code;
//echo $to_code;
$to_cName = $dump_code_to[0]->cName;
//echo $to_cName;
$code_to_loc = $dump_code_to[0]->country;
//echo $code_to_loc;

//calculating the conversion rate
$new_amnt = number_format(((float)$amnt)*($rate_to*$rate_from), 2);

if ($format == "xml"){
    
    $xml = "<?xml version='1.0' encoding = 'UTF-8'?>";
    $xml.= "<conv>";
    $xml.= "<at>$format_ts</at>";
    $xml.= "<rate>$rate_to</rate>";
    $xml.= "<from>";
    $xml.= "<code>$from_code</code>";
    $xml.= "<curr>$from_cName</curr>";
    $xml.= "<loc>$code_from_loc</loc>";
    $xml.= "<amnt>$amnt</amnt>";
    $xml.= "</from>";
    $xml.= "<to>";
    $xml.= "<code>$to_code</code>";
    $xml.= "<curr>$to_cName</curr>";
    $xml.= "<loc>$code_to_loc</loc>";
    $xml.= "<amnt>$new_amnt</amnt>";
    $xml.= "</to>";
    $xml.= "</conv>";

    header("Content-type: text/xml");
    echo $xml;
    
}elseif ($format == "json"){

    $json = array("conv" => array("at" => "$format_ts", "rate" => "$rate_to"), 
            "from"=> array("code" => "$from_code", "curr" => "$from_cName", "loc" => "$code_from_loc", "amnt" => "$amnt"),
            "to" => array("code" => "$to_code", "curr" => "$to_cName", "loc" => "$code_to_loc", "amnt" => "$new_amnt"));

    header("Content-type: application/json");
    echo json_encode($json, JSON_PRETTY_PRINT);

}

?>