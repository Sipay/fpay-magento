<?php

namespace Sipay\Sipay\Helper;

class Constants{

  CONST PAYMENT_METHOD_CODE = 'sipay_sipay';

  CONST ENVIROMENTS_URLS = [
    "sandbox" => "https://sandbox.sipay.es/",
    "live"    => "https://live.sipay.es/",
    "develop" => "https://develop.sipay.es/"
  ];

  CONST SDK_JS_URL 	= "https://cdn.jsdelivr.net/gh/Sipay/fpay-sdk-javascript@1.0/build/pwall-sdk.min.js";



  CONST EXPRESS_CHECKOUT_TAG      = "express";

  CONST CHECKOUT_TAG_VIRTUAL      = "digital";
  CONST CHECKOUT_TAG_NOVIRTUAL    = "fisico";
  CONST CHECKOUT_TAG_BOTH         = "mixto";

  CONST POSTCODE_REGIONID_SPAIN = [
    "01"	=> "131",
    "02"	=> "132",
    "03"	=> "133",
    "04"	=> "134",
    "05"	=> "136",
    "06"	=> "137",
    "07"	=> "138",
    "08"	=> "139",
    "09"	=> "140",
    "10"	=> "141",
    "11"	=> "142",
    "12"	=> "144",
    "13"	=> "146",
    "14"	=> "147",
    "15"	=> "130",
    "16"	=> "148",
    "17"	=> "149",
    "18"	=> "150",
    "19"	=> "151",
    "20"	=> "152",
    "21"	=> "153",
    "22"	=> "154",
    "23"	=> "155",
    "24"	=> "158",
    "25"	=> "159",
    "26"	=> "156",
    "27"	=> "160",
    "28"	=> "161",
    "29"	=> "162",
    "30"	=> "164",
    "31"	=> "165",
    "32"	=> "166",
    "33"	=> "135",
    "34"	=> "167",
    "35"	=> "157",
    "36"	=> "168",
    "37"	=> "169",
    "38"	=> "170",
    "39"	=> "143",
    "40"	=> "171",
    "41"	=> "172",
    "42"	=> "173",
    "43"	=> "174",
    "44"	=> "175",
    "45"	=> "176",
    "46"	=> "177",
    "47"	=> "178",
    "48"	=> "179",
    "49"	=> "180",
    "50"	=> "181",
    "51"	=> "145",
    "52"	=> "163",
  ];

  CONST SIPAY_EC_NOREGION_ID = "sipay_sipay_no_validate_region";
}



