<?php
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

$_filename = $argv[1];
$_data = json_decode(file_get_contents($_filename));
$_pages = $_data['page'];
$_blocks = $_data['block'];

foreach ($_pages as $_page) {
    $_id = $_page['page_id'];
    $_page = Mage::getModel('cms/page')->load($_id);
    foreach ($_page as $_key=>$_val) {
        $_page->setData($_key, $_val);
    }
    $_page->save();
}

foreach ($_blocks as $_block) {
    $_id = $_block['block_id'];
    $_block = Mage::getModel('cms/block')->load($_id);
    foreach ($_block as $_key=>$_val) {
        $_block->setData($_key, $_val);
    }
    $_block->save();
}