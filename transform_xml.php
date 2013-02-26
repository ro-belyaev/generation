<?php

$stringXML = file_get_contents('./all_nodes.xml');

$xml = new DOMDocument();
$xml->loadXML($stringXML);

$xsl = new DOMDocument();
$xsl->load('xslt_test.xslt');

$proc = new XSLTProcessor();
$proc->registerPHPFunctions('trim');
$proc->importStyleSheet($xsl);

//$tree = new SimpleXMLElement($stringXML);
//$dependence_between_classes = $tree->xpath('/tree/dependences-between-classes');
//$dependence_between_criterions = $tree->xpath('/tree/dependences-between-criterions');


$json_data = array();

$json_data['xml'] = $proc->transformToXML($xml);
$json_data['tree'] = $stringXML;
//$json_data['dependence_between_classes'] = $dependence_between_classes[0]->asXML();
//$json_data['dependence_between_criterions'] = $dependence_between_criterions[0]->asXML();

$json_data = json_encode($json_data);
echo $json_data;
