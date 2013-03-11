<?php

apache_setenv('no-gzip', 1);
ini_set('zlib.output_compression', 0);

include_once('check_dependences.php');
$nodes_from_client = $_POST['tests'];
$xml_string = file_get_contents('./all_nodes.xml');
$xml = new SimpleXMLElement($xml_string);

if(count($nodes_from_client) == 0) {
    die('no nodes from client');
}

if(!general_check($nodes_from_client, $xml)) {
    die('criterions ID or values were modified');
}

$nodesID = array();

foreach($nodes_from_client as $node) {
    $pattern = "/^(.+)_.+$/";
    preg_match($pattern, $node, $matches);
    $nodesID[] = $matches[1];
}

if(!criterions_dependence_check($nodesID, $xml) || !classes_dependence_check($nodesID, $xml)) {
    die('bad dependence');
}

$criterions = array();

foreach($nodes_from_client as $node) {
    $pattern = "/^(.+)_(.+)$/";
    preg_match($pattern, $node, $matches);
    $node_id = $matches[1];
    $node_value = $matches[2];
    if(!in_array($node_id, array_keys($criterions))) {
	$criterions[$node_id] = array($node_value);
    }
    else {
	$criterions[$node_id][] = $node_value;
    }
}
include_once('sets_new.php');
$num_of_tests = count($confs) * count($filters) * count($processes) * count($outputs) * count($templates);
$process = 0;
define('STATE_PROGRESS', 0);
define('STATE_COMPLETE', 1);
define('STATE_CANCEL', 2);

$host = "127.0.0.1";
$login = "root";
$passw = "12345";
$db = "sqli_benchmark";
$table = "generation";
$query = "INSERT INTO $table (`num_of_tests`, `process`, `state`)".
	" VALUES($num_of_tests, $process, ". STATE_PROGRESS  .")";
$connection = mysql_connect($host, $login, $passw);
mysql_select_db($db, $connection);
mysql_query($query, $connection);
$id = mysql_insert_id($connection);

ob_end_clean();
header("Connection: close");
ob_start();
//$response_array = array('generation_id' => $id);
echo $id;
header("Content-Length: ". strlen((string)$id));
//header("Location: http://localhost/generation/generation.html?id=$id");
ob_end_flush();
flush();
include_once('master_new.php');

/*if(!($handle = fopen('./tmpFile.txt', 'w'))) {
    die("Can't create file");
}

foreach($confs as $conf) {fwrite($handle, $conf);fwrite($handle, "\n");}
fwrite($handle, "\n\n\n\n\n\n\n\n\n\n");
foreach($filters as $filter) {fwrite($handle, $filter);fwrite($handle, "\n");}
fwrite($handle, "\n\n\n\n\n\n\n\n\n\n");
foreach($processes as $process) {fwrite($handle, $process);fwrite($handle, "\n");}
fwrite($handle, "\n\n\n\n\n\n\n\n\n\n");
foreach($outputs as $output) {fwrite($handle, $output);fwrite($handle, "\n");}
fwrite($handle, "\n\n\n\n\n\n\n\n\n\n");
foreach($templates as $template) {fwrite($handle, $template);fwrite($handle, "\n");}


fclose($handle);
*/
