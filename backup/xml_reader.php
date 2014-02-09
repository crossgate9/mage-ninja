<?php

error_reporting(0);

$_filename = $argv[1];
$_path = $argv[2];

$_xml_content = file_get_contents($_filename);
$_xml = new SimpleXMLElement($_xml_content);
$_node = $_xml->xpath($_path);
list(, $_value) = each($_node);

echo $_value;