<?php

$mysqli = new mysqli("localhost", "root", "password", "db");
if ($mysqli->connect_errno) {
  echo "Failed to connect to MySQL: " . $mysqli->connect_error;
  die;
}


header('Content-type: text/html; charset=utf-8');

function query($q)
{
  global $mysqli;

  return $mysqli->query($q);
}


$r = query('SELECT * FROM uk_postcodes');
$rows = array();
while ($row = $r->fetch_assoc())
{
  array_push($rows, $row);
}


$countries = array();

foreach ($rows as $row)
{
  if (empty($countries[$row['country_string']]))
  {
    $countries[$row['country_string']] = array(
      'name' => $row['country_string'],
      'latitude' => $row['latitude'],
      'longitude' => $row['longitude'],
      'matches' => 1
    );
  }
  else
  {
    $countries[$row['country_string']]['latitude'] += $row['latitude'];
    $countries[$row['country_string']]['longitude'] += $row['longitude'];
    $countries[$row['country_string']]['matches'] += 1;
  }
}

foreach ($countries as $key => $val)
{
  $countries[$key]['latitude'] /= $countries[$key]['matches'];
  $countries[$key]['longitude'] /= $countries[$key]['matches'];
}


$regions = array();

foreach ($rows as $row)
{
  if (empty($regions[$row['region']]))
  {
    $regions[$row['region']] = array(
      'name' => $row['region'],
      'latitude' => $row['latitude'],
      'longitude' => $row['longitude'],
      'country' => $row['country_string'],
      'matches' => 1
    );
  }
  else
  {
    $regions[$row['region']]['latitude'] += $row['latitude'];
    $regions[$row['region']]['longitude'] += $row['longitude'];
    $regions[$row['region']]['matches'] += 1;
  }
}

foreach ($regions as $key => $val)
{
  $regions[$key]['latitude'] /= $regions[$key]['matches'];
  $regions[$key]['longitude'] /= $regions[$key]['matches'];
}



$towns = array();

foreach ($rows as $row)
{
  $town = trim($row['town']);
  if ($town == '') continue;

  if (empty($towns[$town]))
  {
    $towns[$town] = array(
      'name' => $row['town'],
      'latitude' => $row['latitude'],
      'longitude' => $row['longitude'],
      'region' => $row['region'],
      'matches' => 1
    );
  }
  else
  {
    $towns[$town]['latitude'] += $row['latitude'];
    $towns[$town]['longitude'] += $row['longitude'];
    $towns[$town]['matches'] += 1;
  }
}

foreach ($towns as $key => $val)
{
  $towns[$key]['latitude'] /= $towns[$key]['matches'];
  $towns[$key]['longitude'] /= $towns[$key]['matches'];
}


query('TRUNCATE TABLE app_geolocations');

foreach ($countries as $key => $row)
{
  query('INSERT INTO app_geolocations SET
    name = "'.$mysqli->real_escape_string($row['name']).'",
    type = "country",
    latitude = '.$row['latitude'].',
    longitude = '.$row['longitude'].'
  ');

  $r = query('SELECT LAST_INSERT_ID()');
  $countries[$key]['id'] = $r->fetch_row()[0];
  //var_dump($countries[$key]);  die;
}

foreach ($regions as $key => $row)
{
  $parent_id = 0;

  if (!empty($countries[$row['country']])) {
    $parent_id = intval($countries[$row['country']]['id']);
  }

  query('INSERT INTO app_geolocations SET
    parent_id = "'.$parent_id.'",
    name = "'.$mysqli->real_escape_string($row['name']).'",
    type = "region",
    latitude = '.$row['latitude'].',
    longitude = '.$row['longitude'].'
  ');

  $r = query('SELECT LAST_INSERT_ID()');
  $regions[$key]['id'] = $r->fetch_row()[0];
  //var_dump($countries[$key]);  die;
}

foreach ($towns as $key => $row)
{
  $parent_id = 0;

  if (!empty($towns[$row['region']])) {
    $parent_id = intval($towns[$row['region']]['id']);
  }

  query('INSERT INTO app_geolocations SET
    parent_id = "'.$parent_id.'",
    name = "'.$mysqli->real_escape_string($row['name']).'",
    type = "town",
    latitude = '.$row['latitude'].',
    longitude = '.$row['longitude'].'
  ');

  $r = query('SELECT LAST_INSERT_ID()');
  $regions[$key]['id'] = $r->fetch_row()[0];
  //var_dump($countries[$key]);  die;
}

