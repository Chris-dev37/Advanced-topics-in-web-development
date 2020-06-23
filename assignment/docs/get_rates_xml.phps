<?php
include("config.php");

//$one_codes = array();

if(file_exists("rates.xml")){
    copy("rates.xml", "rates"."_".time().".xml");
    $xml = simplexml_load_file("rates.xml");
    //$get_live_att = $xml->xpath("//@live");
    foreach($xml->code as $c){
        if((string) $c["@rate"] == 1){
            $one_codes[] = ((string) $c);
        }
    }

}
else{
    $one_codes[] = &$mnyCodes;
}
print_r($one_codes);
?>