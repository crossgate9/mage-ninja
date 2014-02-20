<?php

# init 
error_reporting(E_ALL | E_STRICT);
define('MAGENTO_ROOT', './');
$_mage_file = MAGENTO_ROOT . 'app/Mage.php';
require_once $_mage_file;
Mage::app();

date_default_timezone_set('Asia/Hong_Kong');

require_once './vendor/autoload.php';

use Ulrichsg\Getopt;

$getopt = new Getopt(array(
  array(null, 'store', Getopt::REQUIRED_ARGUMENT, 'Store ID'),
  array(null, 'csv', Getopt::OPTIONAL_ARGUMENT, 'Update File'),
  array(null, 'images', Getopt::OPTIONAL_ARGUMENT, 'Image Folder'),
  // array(null, 'help', Getopt::OPTIONAL_ARGUMENT, 'Help')
));

$getopt->parse();
$_store_id = $getopt->getOption('store');
if (isset($_store_id) === false) {
  $getopt->showHelp();
  die();
}

// $_show_help = $getopt->getOption('help');
// if (isset($_show_help) === true) {
//   $getopt->showHelp();
//   die();
// } 

// get website id
$_store = Mage::app()->getStore($_store_id);
$_website_id = $_store->getData('website_id');

$_csv = $getopt->getOption('csv');
if (isset($_csv) === false) {
  $getopt->showHelp();
  die();
}

$_image_folder = $getopt->getOption('images');
$_import_image = true;
if (isset($_image_folder) === false) {
  $_import_image = false;
}

if (($_fin = fopen($_csv, 'r')) !== false) {
  $_columns = fgetcsv($_fin);

  $_ignore_key = array_search('ignore', $_columns);
  $_id_key = array_search('entity_id', $_columns);
  $_sku_key = array_search('sku', $_columns);
  $_gallery_key = array_search('gallery', $_columns);

  while (($_data = fgetcsv($_fin)) !== false) {
    if ($_data[$_ignore_key] === '1') continue;
    $_product = Mage::getModel('catalog/product')->setStoreId($_store_id);
    if ($_data[$_id_key] !== '0') {
      $_product->load($_data[$_id_key]);
    }
    foreach($_data as $_key=>$_val) {
      if ($_key === $_ignore_key) continue;
      if ($_key === $_id_key) continue;

      $_product->setData($_columns[$_key], $_val);
    }
    $_product->setData('website_id', array($_website_id));
    $_product->setTaxClassId(2);

    $_product->save();

    // import images
    if ($_import_image === true || $_data[$_gallery_key] === '1') {
      $_folder = $_image_folder . $_data[$_sku_key];
      if (file_exists($_folder)) {
        $_dir = opendir($_folder);
        while (($_entry = readdir($_dir)) !== false) {
          if ($_entry === '.' || $_entry === '..') continue;
          $_filename = $_folder . '/' . $_entry;
          var_dump($_filename);
          $_product->addImageToMediaGallery($_filename, array('image', 'small_image', 'thumbnail'), false, false);
          $_product->save();
        }
        closedir($_dir);
      }
    }
  } 
}