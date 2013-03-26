<?php

define('STATE_PROGRESS', 0);
define('STATE_COMPLETE', 1);
define('STATE_CANCEL', 2);

$host = "127.0.0.1";
$login = "root";
$passw = "12345";
$db = "sqli_benchmark";
$table = "generation";

$connection = mysql_connect($host, $login, $passw);
mysql_select_db($db, $connection);

$id = mysql_real_escape_string($_GET['id']);
$query = "UPDATE $table SET `state`=". STATE_CANCEL ." WHERE id=$id";
mysql_query($query, $connection);

include './clear.php';

