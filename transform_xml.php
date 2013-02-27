<?php

include_once('./choose_lang.php');

//$lang = $_GET['lang'];
$lang = 'ru';

if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    $string = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
    $possible_lang = choose_language_by_accept_lang($string);
    if($possible_lang != null) {
	$lang = $possible_lang;
    }
}


$stringXML = file_get_contents('./all_nodes.xml');

$xml = new DOMDocument();
$xml->loadXML($stringXML);

$xsl = new DOMDocument();
$xsl->load('xslt_test.xslt');

$proc = new XSLTProcessor();
$proc->importStyleSheet($xsl);
$proc->setParameter('', 'lang', $lang);


$json_data = array();

$json_data['xml'] = $proc->transformToXML($xml);
$json_data['tree'] = $stringXML;

$json_data = json_encode($json_data);
echo $json_data;
