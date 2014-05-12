<?php
$eavInstaller = Mage::getModel('customer/entity_setup','core_setup');
$eavInstaller->startSetup();

$eavInstaller->removeAttribute('customer','soundcloud_id');
$eavInstaller->removeAttribute('customer','soundcloud_oa_access_token');
$eavInstaller->removeAttribute('customer','soundcloud_oa_valid_till');
$attributesData = array(
    'soundcloud_id' => array(
        'type'     => 'varchar',
        'input'    => 'hidden',
        'visible'  => false,
        'required' => false
    ),
    'soundcloud_oa_access_token' => array(
        'type'     => 'varchar',
        'input'    => 'hidden',
        'visible'  => false,
        'required' => false
    ),
    'soundcloud_oa_valid_till' => array(
        'type'     => 'int',
        'input'    => 'hidden',
        'visible'  => false,
        'required' => false
    ),
);

foreach($attributesData as $attrCode=>$attrOptions) {
    $eavInstaller->addAttribute('customer', $attrCode, $attrOptions);
}

$eavInstaller->endSetup();