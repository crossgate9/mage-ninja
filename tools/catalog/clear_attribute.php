<?php

# Thanks to Mufaddai
# http://magento.stackexchange.com/questions/3701/clearing-magento-after-testing

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

$resource = Mage::getSingleton('core/resource');
$db_read = $resource->getConnection('core_read');

$attribute_sets = $db_read->fetchCol("SELECT attribute_set_id FROM " . $resource->getTableName("eav_attribute_set") . " WHERE attribute_set_id<> 4 AND entity_type_id=4");
foreach ($attribute_sets as $attribute_set_id) {
    try {
        Mage::getModel("eav/entity_attribute_set")->load($attribute_set_id)->delete();
    } catch (Exception $e) {
        echo $e->getMessage() . "\n";
    }
}
