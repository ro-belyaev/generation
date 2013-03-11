<?php

require_once('./choose_lang.php');

$language = 'en';
$cookie_name = 'sqli_benchmark_lang';

if(isset($_COOKIE[$cookie_name])) {
    $language = $_COOKIE[$cookie_name];
}
else {
    if(($accept_lang = choose_language_by_accept_lang()) != null) {
	$language = $accept_lang;
    }
    setcookie($cookie_name, $language);
}


$stringXML = file_get_contents('./all_nodes.xml');

$xml = new DOMDocument();
$xml->loadXML($stringXML);

$xsl = new DOMDocument();
$xsl->load('xslt_test.xslt');

$proc = new XSLTProcessor();
$proc->importStyleSheet($xsl);
$proc->setParameter('', 'lang', $language);


$json_data = array();

$json_data['xml'] = $proc->transformToXML($xml);
$json_data['tree'] = $stringXML;

$json_data = json_encode($json_data);
echo $json_data;
