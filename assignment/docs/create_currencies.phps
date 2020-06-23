<?php
include("config.php");

// the code in this function was adapted from the example given from p-chatterjee 
// loading the ISO list of global currencies xml file
$xml = simplexml_load_file(ISO_XML);

$curDoc = new xmlWriter();
$curDoc->openURI("currencies.xml");
$curDoc->setIndent(true);
$curDoc->startDocument("1.0");
$curDoc->startElement("currencies");

// going to all "CcyNtry" nodes with "Ccy" elements in the document no matter where they are positioned  
$codes = $xml ->xpath("//CcyNtry/Ccy");
$curCodes = [];

// making an array with currency codes & storing them as a string
foreach($codes as $code){
    if (!in_array($code, $curCodes)){
        $curCodes[] = (string) $code;
    }
}

foreach($curCodes as $cCode){

    // looking for the "Ccy" element anywhere in the document that matches $cCode and then pulling their parent node
    $nodes = $xml->xpath("//Ccy[.='$cCode']/parent::*");  

    $curDoc->startElement("currency");
        $curDoc->startElement("cCode");
        $curDoc->text("$cCode");
        $curDoc->endElement();
        $curDoc->startElement("cName");
        $curDoc->text($nodes[0]->CcyNm);
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
echo "<br>XML Created";
?>