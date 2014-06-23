<?php

$mysqli = new mysqli("localhost", "root", "password", "db");
if ($mysqli->connect_errno) {
  echo "Failed to connect to MySQL: " . $mysqli->connect_error;
  die;
}


header('Content-type: text/html; charset=utf-8');

$f = fopen('postcodes.csv', 'r');

while ($row = fgetcsv($f))
{
  if (!$mysqli->query('INSERT INTO uk_postcodes VALUES(
    0,
    "'.$mysqli->real_escape_string($row[0]).'",
    "'.$mysqli->real_escape_string($row[1]).'",
    "'.$mysqli->real_escape_string($row[2]).'",
    "'.$mysqli->real_escape_string($row[3]).'",
    "'.$mysqli->real_escape_string($row[4]).'",
    "'.$mysqli->real_escape_string($row[5]).'",
    "'.$mysqli->real_escape_string($row[6]).'",
    "'.$mysqli->real_escape_string($row[7]).'",
    "'.$mysqli->real_escape_string($row[8]).'"
  )')) die($mysqli->error);

  echo $row[2].' / '.$row[8].'<br/>';
}
