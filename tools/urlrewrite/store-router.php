<?php

// Comment: Coming soon.

error_reporting(E_ALL | E_STRICT);

$compilerConfig = 'includes/config.php';
if (file_exists($compilerConfig)) {
    include $compilerConfig;
}

$mageFilename = 'app/Mage.php';
$maintenanceFile = 'maintenance.flag';

if (!file_exists($mageFilename)) {
    if (is_dir('downloader')) {
        header("Location: downloader");
    } else {
        echo $mageFilename." was not found";
    }
    exit;
}

if (file_exists($maintenanceFile)) {
    include_once dirname(__FILE__) . '/errors/503.php';
    exit;
}

require_once $mageFilename;

#Varien_Profiler::enable();

if (isset($_SERVER['MAGE_IS_DEVELOPER_MODE'])) {
    Mage::setIsDeveloperMode(true);
}

# ini_set('display_errors', 1);

umask(0);

/* Store or website code */
$mageRunCode = isset($_SERVER['MAGE_RUN_CODE']) ? $_SERVER['MAGE_RUN_CODE'] : '';

/* Run store or run website */
$mageRunType = isset($_SERVER['MAGE_RUN_TYPE']) ? $_SERVER['MAGE_RUN_TYPE'] : 'store';

Mage::app($mageRunCode, $mageRunType);

// get stores
$_websites = Mage::app()->getWebsites();
$_store_ids = array();

foreach ($_websites as $_website) {
  $_groups = $_website->getGroups();
  foreach ($_groups as $_group) {
    $_stores = $_group->getStores();
    foreach ($_stores as $_store) {
      $_store_ids[] = $_store->getData('store_id');
    }
  }
}
$_store_count = count($_store_ids);

echo 'The following store view will be included: ';
foreach ($_store_ids as $_id) {
  echo $_id . ', ';
}
echo "\n";

function addUrlRewrite($_idpath, $_source, $_dist, $_storeview) {
  try {
    var_dump($_idpath, $_source, $_dist, $_storeview);
    Mage::getModel('core/url_rewrite')
      ->setIsSystem(0)
      ->setOptions('RP')
      ->setStoreId($_storeview)
      ->setIdPath($_idpath)
      ->setTargetPath($_dist)
      ->setRequestPath($_source)
      ->save();
  } catch (Exception $_e) {

  }
}

function addSwitch($_a_id, $_b_id, $_a_url, $_b_url, $_suffix) {
  // when switch from A to B
  addUrlRewrite(
    sprintf('store-router-%s-%s-%s', $_a_id, $_b_id, $_suffix),
    $_a_url, $_b_url, $_b_id
  );

  // when switch from B to A
  addUrlRewrite(
    sprintf('store-router-%s-%s-%s', $_b_id, $_a_id, $_suffix),
    $_b_url, $_a_url, $_a_id
  );
}

// router the category
echo 'Check the Category ...' . "\n";
$_categories = Mage::getModel('catalog/category')->getCollection();
foreach ($_categories->getItems() as $_category) {
  $_category_id = $_category->getId();

  for($i=0;$i<$_store_count;$i++) {
    for ($j=$i+1;$j<$_store_count;$j++) {
      $_a_id = $_store_ids[$i];
      $_b_id = $_store_ids[$j];
      
      $_url_write_a = Mage::getModel('core/url_rewrite')->setStoreId($_a_id)->load('category/'.$_category_id, 'id_path')->getData('request_path');
      $_url_write_b = Mage::getModel('core/url_rewrite')->setStoreId($_b_id)->load('category/'.$_category_id, 'id_path')->getData('request_path');

      if ($_url_write_a !== $_url_write_b) {
        addSwitch($_a_id, $_b_id, $_url_write_a, $_url_write_b, 'category-'.$_category_id);
      }
    }
  }
}

echo 'Check the Product ...' . "\n";
$_products = Mage::getModel('catalog/product')->getCollection()
              -> addAttributeToFilter('visibility', 4);
foreach ($_products->getItems() as $_product) {
  $_product_id = $_product->getId();

  // direct path
  for($i=0;$i<$_store_count;$i++) {
    for ($j=$i+1;$j<$_store_count;$j++) {
      $_a_id = $_store_ids[$i];
      $_b_id = $_store_ids[$j];
      $_url_write_a = Mage::getModel('core/url_rewrite')->setStoreId($_a_id)->load('product/'.$_product_id, 'id_path')->getData('request_path');
      $_url_write_b = Mage::getModel('core/url_rewrite')->setStoreId($_b_id)->load('product/'.$_product_id, 'id_path')->getData('request_path');
      if ($_url_write_a !== $_url_write_b) {
        addSwitch($_a_id, $_b_id, $_url_write_a, $_url_write_b, 'product-'.$_product_id);
      }
    }
  }

  // path with category
  $_product = Mage::getModel('catalog/product')->load($_product_id);
  $_categories = $_product->getCategoryIds();
  foreach ($_categories as $_c_id) {
    $_key = 'product/' . $_id . '/' . $_c_id;
    for($i=0;$i<$_store_count;$i++) {
      for ($j=$i+1;$j<$_store_count;$j++) {
        $_a_id = $_store_ids[$i];
        $_b_id = $_store_ids[$j];
        $_url_write_a = Mage::getModel('core/url_rewrite')->setStoreId($_a_id)->load($_key, 'id_path')->getData('request_path');
        $_url_write_b = Mage::getModel('core/url_rewrite')->setStoreId($_b_id)->load($_key, 'id_path')->getData('request_path');
        if ($_url_write_a !== $_url_write_b) {
          addSwitch($_a_id, $_b_id, $_url_write_a, $_url_write_b, 'product-'.$_product_id.'-category-'.$_c_id);
        }   
      }
    }
  }
}