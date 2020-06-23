<?php
require_once("../../config.php");
require_once("../../functions.php");

// loading the api with the converstion rates and the using json_decode to make them accessible 
$json_rates = file_get_contents(API_URL) or die ("Error: Unable to load file");
$rates = json_decode($json_rates);

foreach($rates->rates as $k=>$v){
    $fixer_c[]= (string) $k;
}
//checking if $action is containing a value 
if(!isset($_POST['actions'])){
    generate_errors(2000, ERROR_MSGS, $aa=1);
    exit;
}
//checking if $codes is containing a value
if(!isset($_POST['codes'])){
    generate_errors(2300, ERROR_MSGS, $aa=1);
    exit;
}
//checking if $codes is equal to GBP, as you cant update the base currency
if($_POST['codes'] == "GBP"){
    generate_errors(2400, ERROR_MSGS, $aa=1);
    exit;
}
//checking if $codes is containing a value that is avaible in the fixer api for a currency rate
if(!in_array($_POST['codes'], $fixer_c)){
    generate_errors(2300, ERROR_MSGS, $aa=1);
    exit;
}

$action = $_POST['actions'];
$code = $_POST['codes'];
    
    // setting GBP as the base currnecy
    $gbp_value = 1/ $rates->rates->GBP;

    // loading the currencies file from the dirctory above this file location, then looking for the "cCode" element anywhere in the document that matches the $_GET param "$cur" and then pulling their parent node 
    // This will be used for both post and put actions for retriving data 
    $curr_xml = simplexml_load_file("../../currencies.xml");
    $code_p = $curr_xml->xpath("//cCode[.='$code']/parent::*");
    //var_dump($code_p);

    // getting the currency code and currency name from the currencies xml xpath
    $cur_code = $code_p[0]->cCode;
    $cur_cName = $code_p[0]->cName;

    //tidying up the country node by replaceing the elements in the $wrong array with the elements in the $right array and using preg_replace to search for a patten search and replace
    $country = trim(preg_replace("/[\t\n\r\s]+/", " ",$code_p[0]->cntry));
    $wrong = array("Of", "And", "U.s.", "(The)", " , ");
    $right = array("of", "and", "U.S.", "", ", ");
    $cur_country = str_replace($wrong, $right, $country);

    // loading the rates file from the dirctory above this file location, then looking for the "code" element anywhere in the document that matches the $_GET param "$cur" and then pulling their parent node
    // this will be used for post, put and delete as a place where the new data from the currencies file will be stored and to edit the live attribute 
    $rates_xml = simplexml_load_file("../../rates.xml");
    $old_rate = $rates_xml->xpath("//code[.='$code']/parent::*");
    // var_dump($rates_xml);
    // exit;

    $rate_to = number_format((float)$rates->rates->$cur_code * $gbp_value, 2);
    $old_rate_final = number_format((float)$old_rate[0]->rate, 2);
    //var_dump($old_rate_final);
    //exit;

    $rates_ev_code = $rates_xml->xpath("//code");
    //var_dump($rates_ev_code);
    //exit;

    //gettting and formating the date from the timestamp
    $rates_timestamp = $rates_xml["timestamp"];

    // adding two hours to the original timestamp so it can be compared with this new timestamp to see if the rates file needs updating  
    $update_time = $rates_timestamp + 120;

    $format_ts = date("l dS F Y H:i:s", (int)$rates_timestamp);

    // if the timestamp is 2 hours or more older than when it was last called then it will update with fresh rates 
    if($rates_timestamp >= $update_time){
        update();
    }
    
    if($action == "post"){

        foreach($rates_xml->currency as $currency){
            if($currency->code == $code){
                $currency->rate = $rates->rates->$cur_code * $gbp_value;
                $currency->cName = $cur_cName;
                $currency->country = $cur_country;
            }
        }
            
        if(!in_array($code, $rates_ev_code)){
            $post = $rates_xml->addChild("currency");
            $post->addChild("code", $cur_code);
            $post->addAttribute("live", 1);
            $post->addChild("rate", $rates->rates->$cur_code * $gbp_value);
            $post->addChild("cName", $cur_cName);
            $post->addChild("country", $cur_country);
        }
        
        //$post->setIndent(true);
        file_put_contents("../../rates.xml", $rates_xml->asXML());

        $xml = "<?xml version='1.0' encoding = 'UTF-8'?>";
        $xml.= "<action type='post'>";
        $xml.= "<at>$format_ts</at>";
        $xml.= "<rate>$rate_to</rate>";
        $xml.= "<old_rate>$old_rate_final</old_rate>";
        $xml.= "<curr>";
        $xml.= "<code>$cur_code</code>";
        $xml.= "<curr>$cur_cName</curr>";
        $xml.= "<loc>$cur_country</loc>";
        $xml.= "</curr>";
        $xml.= "</action>";
        
        //header("Content-type: application/html");
        echo $xml;
        exit;

    }elseif($action == "put"){
        
        foreach($rates_xml->currency as $currency){
            if($currency->code == $code){
                $currency->code["live"] = 1;
                $currency->rate = $rates->rates->$cur_code * $gbp_value;
                $currency->cName = $cur_cName;
                $currency->country = $cur_country;
            }
        }
        file_put_contents("../rates.xml", $rates_xml->asXML());

        $xml = "<?xml version='1.0' encoding = 'UTF-8'?>";
        $xml.= "<action type='put'>";
        $xml.= "<at>$format_ts</at>";
        $xml.= "<rate>$rate_to</rate>";
        $xml.= "<curr>";
        $xml.= "<code>$cur_code</code>";
        $xml.= "<curr>$cur_cName</curr>";
        $xml.= "<loc>$cur_country</loc>";
        $xml.= "</curr>";
        $xml.= "</action>";

        //header("Content-type: text/xml");
        echo $xml;
        exit;

    // if the $_GET param $action = del then first the $_GET param $cur is checked to see if it matches the base currency GBP, if it does then a error appears and the system exits.
    // if $cur doesnt match to GBP then perfroms similarly to the put function, the $_GET param $cur is checked against all the the code elements in the currencies file, if they match then that codes "live attribute is set to 0     
    }elseif($action == "del"){

        foreach($rates_xml->currency as $currency){
            if($currency->code == $code){
                $currency->code["live"] = 0; 
            }
        }
        file_put_contents("../rates.xml", $rates_xml->asXML());  

        $xml = "<?xml version='1.0' encoding = 'UTF-8'?>";
        $xml.= "<action type='del'>";
        $xml.= "<at>$format_ts</at>";
        $xml.= "<code>$cur_code</code>";
        $xml.= "</action>";

        //header("Content-type: text/xml");
        echo $xml;
        exit;
    
}

?>