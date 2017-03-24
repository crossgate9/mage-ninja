<?php

date_default_timezone_set('europe/amsterdam');
$_date = date('c');
$_filename = $argv[1];

$_debug_prefix = "[$_date] process.vlog2unitload.back.php: $_filename,";

error_reporting(E_ALL | E_STRICT);

$compilerConfig = 'includes/config.php';
if (file_exists($compilerConfig)) {
    include $compilerConfig;
}

$mageFilename = '../../../../app/Mage.php';
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

$_products = [];

# read files
$_fin = fopen($_filename, 'r');

if (! $_fin) {
    echo "$_debug_prefix cannot open file.\n";
}

while (($_line = fgetcsv($_fin)) !== FALSE) {
    $_sku = $_line[0];
    $_products[$_sku] = [];
}

fclose($_fin);

$_store_views = [1,2,3,4,5,6,7,8];
foreach ($_store_views as $_store_view) {
    $_base_url = Mage::app()->getStore($_store_view)->getBaseUrl();
    
    foreach ($_products as $_sku => $_data) {
        $_product = Mage::getModel('catalog/product')->setStore($_store_view)->loadByAttribute('sku', $_sku);
        if ($_product === FALSE) { continue; }
        $_products[$_sku][$_store_view] = $_base_url . $_product->getData('url_key') . '.html';
    }
}

$_fout = fopen('output.csv', 'w');
foreach ($_products as $_sku => $_data) {
    $_line = [];
    $_line[] = $_sku;
    foreach ($_store_views as $_store_view) {
        if (isset($_data[$_store_view])) {
            $_line[] = $_data[$_store_view];
        }
    }
    fputcsv($_fout, $_line);
}
fclose($_fout);