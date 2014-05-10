<?php
class Devils_Auth_Block_Auth extends Mage_Core_Block_Template
{
    public function getAppId($handlerId = false)
    {
        return Mage::getConfig()->getNode(Devils_Auth_Helper_Oauth_Abstract::XML_PATH_DEVILS_AUTH . $handlerId . '/app_id');
    }
}