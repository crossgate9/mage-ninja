<?php

# init 
error_reporting(E_ALL | E_STRICT);
define('MAGENTO_ROOT', './');
$_mage_file = MAGENTO_ROOT . '/app/Mage.php';
require_once $_mage_file;
Mage::app();

date_default_timezone_set('Asia/Hong_Kong');

require_once './vendor/autoload.php';

use Ulrichsg\Getopt;

$getopt = new Getopt(array(
  array(null, 'store', Getopt::REQUIRED_ARGUMENT, 'Store ID'),
  array(null, 'csv', Getopt::OPTIONAL_ARGUMENT, 'Update File'),
  array(null, 'help', Getopt::OPTIONAL_ARGUMENT, 'Help')
));

$getopt->parse();
$_store_id = $getopt->getOption('store');
if (isset($_store_id) === false) {
  $getopt->showHelp();
  die();
}

$_show_help = $getopt->getOption('help');
if (isset($_show_help) === true) {
  $_root_id = Mage::app()->getStore($_store_id)->getRootCategoryId();
  $_category = Mage::getModel('catalog/category')->setStoreId($_store_id)->load($_root_id);
  $_data = $_category->getData();
  echo 'Available Fileds: ' . PHP_EOL;
  foreach ($_data as $_key=>$_value) {
    echo $_key . PHP_EOL;
  }

  $getopt->showHelp();
  die();
}

$_csv = $getopt->getOption('csv');
if (isset($_csv) === false) {
  $getopt->showHelp();
  die();
}

if (($_fin = fopen($_csv, 'r')) !== false) {
  $_columns = fgetcsv($_fin);
  if (in_array('entity_id', $_columns) === false) {
    throw new Exception("Missing entity_id", 1);
  }
  $_id_key = array_search('entity_id', $_columns);
  while (($_data = fgetcsv($_fin)) !== false) {
    $_id = $_data[$_id_key];
    $_category = Mage::getModel('catalog/category')->setStoreId($_store_id)->load($_id);
    foreach($_data as $_key=>$_val) {
      $_category->setData($_columns[$_key], $_val);
    }
    $_category->save();
  }
}