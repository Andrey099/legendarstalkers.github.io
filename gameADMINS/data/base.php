<?php
/* Database settings */
$db = [
  'settings' => [
    'host' => "localhost",
    'user' => "root",
    'pass' => "",
    'base' => "new",
    'char' => "utf8"
  ]
];

$dsn = "mysql:host=".$db['settings']['host'].";dbname=".$db['settings']['base'].";charset=".$db['settings']['char']."";
$opt = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES   => false,
];
$go = new PDO($dsn, $db['settings']['user'], $db['settings']['pass'], $opt);
if($go -> connect_errno) die('ERROR -> '.$go -> connect_error);

include_once ('array.php');
include_once ('other.php');
?>