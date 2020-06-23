<?php
require_once("config.php");

// function handling the error message outputs, sets format to xml if undefined 
// the code in this function was adapted from the example given from p-chatterjee 
function generate_errors($errNum, $format = "xml", $aa = 0){

    $msg = ERROR_MSGS[$errNum];

    if ($format == "json"){
        $json = array("conv" => array("error" => array("code" => "$errNum", "msg" => "$msg")));
        header("Content-type: application/json");
        echo json_encode($json, JSON_PRETTY_PRINT);
    }
    else{
        $xml = "<?xml version='1.0' encoding = 'UTF-8'?>";
        $xml .= "<conv><error>";
        $xml .= "<code>". $errNum . "</code>";
        $xml .= "<msg>" . $msg . "</msg>";
        $xml .= "</error></conv>";

        if($aa == 1){ // s2-rouf helped with this if statment
            echo $xml;

        }else{
        header("Content-type: text/xml");
        echo $xml;
        }
    }

};

//echo generate_errors(1200, $error_msgs);
//echo generate_errors(1300, $error_msgs, 'json'); // a couple tests for the generate_errors function

// if the currencies file doesnt exist when index.php runs then this is the fuction thats called
// the code in this function was adapted from the example given from p-chatterjee 
function generate_curr_file(){

    
    $xml = simplexml_load_file(ISO_XML);

    $curDoc = new xmlWriter();
    $curDoc->openURI("currencies.xml");
    $curDoc->setIndent(true);
    $curDoc->startDocument("1.0");
    $curDoc->startElement("currencies");

    $codes = $xml ->xpath("//CcyNtry/Ccy");
    $curCodes = [];

    // making an array with currency codes & storing them as a string
    foreach($codes as $code){
        if (!in_array($code, $curCodes)){
            $curCodes[] = (string) $code;
        }
    }

    foreach($curCodes as $cCode){

        $nodes = $xml->xpath("//Ccy[.='$cCode']/parent::*");

        $cName = $nodes[0]->CcyNm;

        $curDoc->startElement("currency");
            $curDoc->startElement("cCode");
            $curDoc->text("$cCode");
            $curDoc->endElement();
            $curDoc->startElement("cName");
            $curDoc->text($cName);
            $curDoc->endElement();
            $curDoc->startElement("cntry");

                $last = count($nodes) - 1;

                //Grouping countries using the same code together & uppercasing the first letter in name
                foreach($nodes as $index=>$node) {
                    $curDoc->text(mb_convert_case($node->CtryNm, MB_CASE_TITLE, "UTF-8"));
                    if ($index!=$last) {
                        $curDoc->text(", ");
                    }
                    
                }
            $curDoc->endElement();
            
        $curDoc->endElement();    
    }

    $curDoc->endDocument();
    $curDoc->flush();

};

// if the rates file doesnt exist when index.php runs then this is the fuction thats called
// the code in this function was adapted from the example given from p-chatterjee 
function generate_rates_file(){

    $json_rates = file_get_contents(API_URL) or die ("Error: Unable to load file");
    $rates = json_decode($json_rates);

    //var_dump ($rates->rates->USD);

    $load_currencies_xml = simplexml_load_file("currencies.xml") or die ("Unable to open file");

    // determing the "live" attribute
    foreach($rates->rates as $k => $v){
        
        if(in_array($k, MNYCODES)) {
            $live_stat_array[$k] = 1;
            
        }
        else{
            $live_stat_array[$k] = 0;
            
        }


    }
    //print_r to check the live attribute of each currency code
    //print_r($live_stat_array);
    //exit;

    $gbp_value = 1/ $rates->rates->GBP;

    $ts = $rates->timestamp;

    $rateDoc = new XMLWriter();
    $rateDoc->openURI("rates.xml");
    $rateDoc->setIndent(true);
    $rateDoc->startDocument("1.0");
    $rateDoc->startElement("currencies");
    $rateDoc->writeAttribute("base", "GDP");
    $rateDoc->writeAttribute("timestamp", $ts);


    foreach(MNYCODES as $codes){
        if(isset($rates->rates->$codes)){

            $nodes = $load_currencies_xml->xpath("//cCode[.='$codes']/parent::*");

            $rateDoc->startElement("currency");
                $rateDoc->startElement("code");
                $rateDoc->writeAttribute("live", $live_stat_array[$codes]);
                $rateDoc->text($codes);
                $rateDoc->endElement();
                $rateDoc->startElement("rate");
                $rateDoc->text($rates->rates->$codes * $gbp_value);
                $rateDoc->endElement();
                $rateDoc->startElement("cName");
                $rateDoc->text($nodes[0]->cName);
                $rateDoc->endElement();
                $rateDoc->startElement("country");
                    //tidying up the country node
                    $country = trim(preg_replace("/[\t\n\r\s]+/", " ",$nodes[0]->cntry));
                    $wrong = array("Of", "And", "U.s.", "(The)", " , ");
                    $right = array("of", "and", "U.S.", "", ", ");
                    $country_fix = str_replace($wrong, $right, $country);

                $rateDoc->text($country_fix);
                $rateDoc->endElement();
            $rateDoc->endElement();

        }

    }
    $rateDoc->endElement();
    $rateDoc->endDocument();
    $rateDoc->flush();

    
};

// function updating the rates in the "rates.xml" file if the timestamp is 2 or more hours old
function update(){
    $json_rates = file_get_contents(API_URL) or die ("Error: Unable to load file");
    $rates = json_decode($json_rates);

    $rates_xml = simplexml_load_file("rates.xml");
    
    $gbp_value = 1/ $rates->rates->GBP;

    // update the timestamp to the time when the update happens
    $rates_xml["timestamp"] = time();

    // for each currency node in the rates file, the rate of each code is updated from the fixer API
    foreach($rates_xml->currency as $curr){
        $a = $curr->code;
        $curr->rate = $rates->rates->$a * $gbp_value;
    }

    file_put_contents("rates.xml", $rates_xml->asXML());
};

?>