<?php
// requiring the config.php file so i can use the data stored there
require_once("config.php");

// the code in this function was adapted from the example given from p-chatterjee 
// loading the api with the converstion rates and the using json_decode to make them accessible 
$json_rates = file_get_contents(API_URL) or die ("Error: Unable to load file");
$rates = json_decode($json_rates);
//var_dump ($rates->rates->USD);

$load_currencies_xml = simplexml_load_file("currencies.xml") or die ("Unable to open file");

// determing the "live" attribute by comparing the currency codes in my $mnyCodes array with the codes in the api json array, if they match then the code is stored in an array as a key and get the value 1, if they dont match then they get the value 0
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

// setting GBP as the base currnecy
$gbp_value = 1/ $rates->rates->GBP;

$rateDoc = new XMLWriter();
$rateDoc->openURI("rates.xml");
$rateDoc->setIndent(true); // used to format the xml so it doesnt layout all on one line
$rateDoc->startDocument("1.0");
$rateDoc->startElement("currencies");
$rateDoc->writeAttribute("base", "GBP");
$rateDoc->writeAttribute("timestamp", time());


foreach(MNYCODES as $codes){
    if(isset($rates->rates->$codes)){
        
        // looking for the "cCode" element anywhere in the document that matches $codes and then pulling their parent node 
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
            
                //tidying up the country node by replaceing the elements in the $wrong array with the elements in the $right array and using preg_replace to search for a patten search and replace
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

echo "Rate XML Done";

?>