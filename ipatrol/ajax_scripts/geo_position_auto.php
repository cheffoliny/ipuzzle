<?php

header("Pragma: no-cache");

define('INCLUDE_CHECK',true);

require_once '../config/connect.php';
require_once '../config/session.inc.php';

$geo = 'http://maps.google.com/maps/api/geocode/xml?latlng='.htmlentities(htmlspecialchars(strip_tags($_GET['latlng']))).'&sensor=true';
$xml = simplexml_load_file($geo);

foreach($xml->result->address_component as $component){
    if($component->type=='street_address'){
        $geodata['precise_address'] = $component->long_name;
    }
    if($component->type=='natural_feature'){
        $geodata['natural_feature'] = $component->long_name;
    }
    if($component->type=='airport'){
        $geodata['airport'] = $component->long_name;
    }
    if($component->type=='park'){
        $geodata['park'] = $component->long_name;
    }
    if($component->type=='point_of_interest'){
        $geodata['point_of_interest'] = $component->long_name;
    }
    if($component->type=='premise'){
        $geodata['named_location'] = $component->long_name;
    }
    if($component->type=='street_number'){
        $geodata['house_number'] = $component->long_name;
    }
    if($component->type=='route'){
        $geodata['street'] = $component->long_name;
    }
    if($component->type=='locality'){
        $geodata['town_city'] = $component->long_name;
    }
    if($component->type=='administrative_area_level_3'){
        $geodata['district_region'] = $component->long_name;
    }
    if($component->type=='neighborhood'){
        $geodata['neighborhood'] = $component->long_name;
    }
    if($component->type=='colloquial_area'){
        $geodata['locally_known_as'] = $component->long_name;
    }
    if($component->type=='administrative_area_level_2'){
        $geodata['county_state'] = $component->long_name;
    }
    if($component->type=='postal_code'){
        $geodata['postcode'] = $component->long_name;
    }
    if($component->type=='country'){
        $geodata['country'] = $component->long_name;
    }
}

list($lat,$long) = explode(',',htmlentities(htmlspecialchars(strip_tags($_GET['latlng']))));
$accuracy   =   htmlentities(htmlspecialchars(strip_tags($_GET['accuracy'])));

$geodata['latitude'             ] = $lat;
$geodata['longitude'            ] = $long;
$geodata['accuracy'             ] = htmlentities(htmlspecialchars(strip_tags($_GET['accuracy'])));
$geodata['altitude'             ] = htmlentities(htmlspecialchars(strip_tags($_GET['altitude'])));
$geodata['speed'                ] = htmlentities(htmlspecialchars(strip_tags($_GET['speed'])));
$geodata['formatted_address'    ] = $xml->result->formatted_address;
$geodata['altitude_accuracy'    ] = htmlentities(htmlspecialchars(strip_tags($_GET['altitude_accuracy'])));
$geodata['directional_heading'  ] = htmlentities(htmlspecialchars(strip_tags($_GET['heading'])));

//$geodata['google_api_src'] = $geo;
//echo '<img src="http://maps.google.com/maps/api/staticmap?center='.$lat.','.$long.'&zoom=14&size=150x150&maptype=roadmap&&sensor=true" width="150" height="150" alt="'.$geodata['formatted_address'].'" \/><br /><br />';
//echo 'Latitude: '.$lat.' Longitude: '.$long.'<br />';
//foreach($geodata as $name => $value){
//    echo ''.$name.': '.str_replace('&','&amp;',$value).'<br />';
//}

$sQuery	 = "SELECT a.id AS aID FROM auto a
                        JOIN road_lists rl ON ( a.id = rl.id_auto AND rl.end_time = '0000-00-00 00:00:00' )
            WHERE rl.persons = '".$_SESSION['id']."' ORDER BY rl.id DESC LIMIT 1 ";
$sResult	=	mysqli_query( $db_auto, $sQuery );
$num_sRows	=   mysqli_num_rows( $sResult       );

if( !$num_oRows ) { }

while( $sRow = mysqli_fetch_assoc( $sResult ) ) {

    $aQuery  = "UPDATE auto SET geo_lon = '".$long."', geo_lat = '".$lat."', geo_time= NOW(), accuracy= '".$accuracy."' WHERE id = '".$sRow['aID']."' ";
    $aResult = mysqli_query( $db_auto, $aQuery ) or die( "Error: ".$aQuery );

}

?>