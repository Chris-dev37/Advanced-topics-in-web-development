<?php
@date_default_timezone_set("Europe/London");

define("API_URL", "http://data.fixer.io/api/latest?access_key=26c34155de98e364fff0dd985c6db3a3");

define("ISO_XML", "http://www.currency-iso.org/dam/downloads/lists/list_one.xml");

define("PARAM", array("from", "to", "amnt", "format"));

define("FRMTS",  array("xml", "json"));

define("PARAM_TESKB", array("cur", "action"));

define("ACTIONS_TASKB", array("post", "put", "del"));

define("MNYCODES", array( 
    'AUD','BRL','CAD','CHF',
    'CNY','DKK','EUR','GBP',
    'HKD','HUF','INR','JPY',
    'MXN','MYR','NOK','NZD',
    'PHP','RUB','SEK','SGD',
    'THB','TRY','USD','ZAR'));

define("ERROR_MSGS", array(
    1000 => 'Required parameter is missing',
	1100 => 'Parameter not recognized',
	1200 => 'Currency type not recognized',
	1300 => 'Currency amount must be a decimal number',
	1400 => 'Format must be xml or json',
	1500 => 'Error in service',
	2000 => 'Action not recognized or is missing',
	2100 => 'Currency code in wrong format or is missing',
	2200 => 'Currency code not found for update',
	2300 => 'No rate listed for this currency',
	2400 => 'Cannot update base currency',
	2500 => 'Error in service'
));
?>