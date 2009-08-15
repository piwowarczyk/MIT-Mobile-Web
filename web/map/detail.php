<?
/**
 * Copyright (c) 2008 Massachusetts Institute of Technology
 * 
 * Licensed under the MIT License
 * Redistributions of files must retain the above copyright notice.
 * 
 */

require_once "../../lib/db.php";
require_once "../page_builder/page_header.php";
require_once "../../config.gen.inc.php";
require_once "lib/map.lib.inc.php";

define('ZOOM', 16);
define('LAT',  39.634419);
define('LONG', -79.954054);
define('MAPTYPE', "roadmap");

switch($phone) {
 case "sp":
  define('INIT_FACTOR', 3);
  break;

 case "fp":
  define('INIT_FACTOR', 2);
  break;
}


function determine_type() {
  $types = array(
    "G"  => "Courtyards",
    "P"  => "Parking",
    "L"  => "Landmarks"
  );

  if(preg_match("/^(P|G|L)\d+/", select_value(), $match)) {
    return array("type" => $types[ $match[1] ], "field" => "Loc_ID");
  } else {
    return array("type" => "Buildings", "field" => "facility");
  }
}

function isID($id) {
  preg_match("/^([A-Z]*)/", $id, $match);
  return $match[0];
}

function pix($label, $phone) {
  //set the resolution
  $resolution = array(
    "sp" => array("220", "160"),
    "fp" => array("160", "160")
  );
  $labels = array("x" => 0, "y" => 1);
  return $resolution[$phone][ $labels[$label] ];
} 

function mapURL() {
  return "http://maps.google.com/staticmap";
}

function imageURL($phone) {

  $query = array(
    "maptype"      => mapType(),
    "key"          => "ABQIAAAAgl5MtLeiQwCMBX7FdoPP_BTfAZWzJoh_gYMfdqhKwTyraOPtpRSIZm3YBA6TbcecvlyiMX_gNejDzg", 
    "size"         => pix("x", $phone).'x'.pix("y", $phone),
    "center"       => lat().",".long(),
    "zoom"         => zoom(),
    "sensor"       => "false",
    "markers"      => marker()
  );

  return mapURL() . '?' . http_build_query($query);
}

function maptype() {
  return isset($_REQUEST['maptype']) ? $_REQUEST['maptype'] : MAPTYPE;
}

function zoom() {
  return isset($_REQUEST['zoom']) ? $_REQUEST['zoom'] : ZOOM;
}

function long() {
  return isset($_REQUEST['long']) ? $_REQUEST['long'] : LONG;
}

function lat() {
  return isset($_REQUEST['lat']) ? $_REQUEST['lat'] : LAT;
}

function tab() {
  return isset($_REQUEST['tab']) ? $_REQUEST['tab'] : "Map";
}

function marker_type($type) {
	$markers = array(
	    "Building" => "midredb",
	    "Parking Lot" => "midbluep",
	    "WiFi" => "midorangew",
	    "PRT" => "midblackp",
	    "Bus Stop" => "midgreens",
	    "Athletic" => "midyellowa",
	    "Library" => "midgrayl",
	    "Residence" => "midbrownr"
	  );
	return $markers[$type];	
}

function marker() {	
  
  if ((int)$_REQUEST['loc'] != 0) {
    $db = db::$connection;
	$stmt = $db->prepare("SELECT * FROM Buildings WHERE id = ".$_REQUEST['loc']);
	$stmt->execute();
	$data = $stmt->fetchAll();
	
	$lat = $data[0]['latitude'];
	$long = $data[0]['longitude'];
	$marker = marker_type($data[0]['type']);
	return $lat.",".$long.",".$marker;
  }
  else if ($_REQUEST['all']) {
	$db = db::$connection;
	$stmt = $db->prepare("SELECT * FROM Buildings WHERE type = ".$_REQUEST['all']);
	$stmt->execute();
	$results = $stmt->fetchAll();
    $markers = "";
    foreach ($results as $result) {
	 	$lat = $data[0]['latitude'];
		$long = $data[0]['longitude'];
		$marker = marker_type($data[0]['type']);
		$markers .= $lat.",".$long.",".$marker."|";
    }
	return $markers;
  }
}

function select_value() {
  return $_REQUEST['selectvalues'];
}

function scrollURL($dir) {
  $move = array(
    "12" => 0.02500,
    "13" => 0.01250,
    "14" => 0.00625,
    "15" => 0.00300,
    "16" => 0.00150,
    "17" => 0.00075
  );
  
  if ($dir == "E") {
    return moveURL(long()+$move[zoom()], lat(), zoom(),maptype());
  } else if ($dir == "W") {
    return moveURL(long()-$move[zoom()], lat(), zoom(),maptype());
  } else if ($dir == "N") {
    return moveURL(long(),lat()+$move[zoom()], zoom(),maptype());
  } else {
    return moveURL(long(),lat()-$move[zoom()], zoom(),maptype());
  }
}

function zoomInURL() {
  $zoom = zoom()+1;
  if ($zoom > 17) {
	$zoom = zoom();
  }
  return moveURL(long(), lat(), $zoom, maptype());
}

function zoomOutURL() {
  $zoom = zoom()-1;
  if ($zoom < 12) {
    $zoom = zoom();
  }
  return moveURL(long(), lat(), $zoom, maptype());
}

function mapTypeURL($type) {
  return moveURL(long(), lat(), zoom(), $type);
}

function selfURL() {
  return moveURL(long(), lat(), zoom(), maptype());
}

function moveURL($long, $lat, $zoom, $maptype) {
  $params = array(
    "zoom" => $zoom,
    "long" => $long,
    "lat" => $lat,
    "maptype" => $maptype,
    "loc" => (int)$_REQUEST['loc']
  );
  return "detail.php?" . http_build_query($params);
}

$tabs = new Tabs(selfURL(), "tab", array("Map"));
$tabs_html = $tabs->html();
$tab = $tabs->active();

$tab = tab();
$width = pix("x", $phone);
$height = pix("y", $phone);

if ($_REQUEST['loc']) {
    $db = db::$connection;
	$stmt = $db->prepare("SELECT * FROM Buildings WHERE id = ".$_REQUEST['loc']);
	$stmt->execute();
	$data = $stmt->fetchAll();
}

/**
 * this function makes the street address
 * more readable by google maps
 */
function cleanStreet($data) {    
  // remove things such as '(rear)' at the end of an address
  $street = preg_replace('/\(.*?\)$/', '', $data['street']);

  //remove 'Access Via' that appears at the begginning of some addresses
  return preg_replace('/^access\s+via\s+/i', '', $street);
} 

require "$prefix/detail.html";

$page->output();

?>
