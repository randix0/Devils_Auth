<?php
/*
$installer = $this;
$installer->startSetup();
$table = $installer->run("
DROP TABLE IF EXISTS {$this->getTable('devils_auth_customer_entity')};
CREATE TABLE {$this->getTable('devils_auth_customer_entity')} (
  `entity_id` int(10) unsigned NOT NULL,
  `facebook_id` bigint(20) NOT NULL,
  `facebook_oa_access_token` text NOT NULL,
  `facebook_oa_valid_till` int(11) NOT NULL,
  `google_id` bigint(20) NOT NULL,
  `google_oa_access_token` text NOT NULL,
  `google_oa_valid_till` int(11) NOT NULL,
  `vkontakte_id` bigint(20) NOT NULL,
  `vkontakte_oa_access_token` text NOT NULL,
  `vkontakte_oa_valid_till` int(11) NOT NULL,
  PRIMARY KEY (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
$installer->endSetup();
*/

$eavInstaller = Mage::getModel('customer/entity_setup','core_setup');
$eavInstaller->startSetup();

$eavInstaller->removeAttribute('customer','facebook_id');
$eavInstaller->removeAttribute('customer','facebook_oa_access_token');
$eavInstaller->removeAttribute('customer','facebook_oa_valid_till');
$eavInstaller->removeAttribute('customer','google_id');
$eavInstaller->removeAttribute('customer','google_oa_access_token');
$eavInstaller->removeAttribute('customer','google_oa_valid_till');
$eavInstaller->removeAttribute('customer','vkontakte_id');
$eavInstaller->removeAttribute('customer','vkontakte_oa_access_token');
$eavInstaller->removeAttribute('customer','vkontakte_oa_valid_till');

$attributesData = array(
    'facebook_id' => array(
        'type'     => 'varchar',
        'input'    => 'hidden',
        'visible'  => false,
        'required' => false
    ),
    'facebook_oa_access_token' => array(
        'type'     => 'varchar',
        'input'    => 'hidden',
        'visible'  => false,
        'required' => false
    ),
    'facebook_oa_valid_till' => array(
        'type'     => 'int',
        'input'    => 'hidden',
        'visible'  => false,
        'required' => false
    ),
    'google_id' => array(
        'type'     => 'varchar',
        'input'    => 'hidden',
        'visible'  => false,
        'required' => false
    ),
    'google_oa_access_token' => array(
        'type'     => 'varchar',
        'input'    => 'hidden',
        'visible'  => false,
        'required' => false
    ),
    'google_oa_valid_till' => array(
        'type'     => 'int',
        'input'    => 'hidden',
        'visible'  => false,
        'required' => false
    ),
    'vkontakte_id' => array(
        'type'     => 'varchar',
        'input'    => 'hidden',
        'visible'  => false,
        'required' => false
    ),
    'vkontakte_oa_access_token' => array(
        'type'     => 'varchar',
        'input'    => 'hidden',
        'visible'  => false,
        'required' => false
    ),
    'vkontakte_oa_valid_till' => array(
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