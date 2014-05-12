<?php
class Devils_Auth_Block_Auth extends Mage_Core_Block_Template
{
    protected function _getSession()
    {
        return Mage::getSingleton('devils_auth/session');
    }

    protected function _getHelper()
    {
        return Mage::helper('devils_auth');
    }

    public function getAppId($handlerId = false)
    {
        return Mage::getConfig()->getNode(Devils_Auth_Helper_Oauth_Abstract::XML_PATH_DEVILS_AUTH . $handlerId . '/app_id');
    }

    public function getState()
    {
        return md5(time());
    }

    public function getUserData()
    {
        return $this->_getSession()->getTempUserData();
    }

    public function getPostActionUrl()
    {
        return $this->_getHelper()->getLoginPostUrl();
    }

    public function getFormData()
    {
        $data = $this->getData('form_data');
        if (is_null($data)) {
            $formData = Mage::getSingleton('customer/session')->getCustomerFormData(true);
            $data = new Varien_Object();
            if ($formData) {
                $data->addData($formData);
                $data->setCustomerData(1);
            }
            if (isset($data['region_id'])) {
                $data['region_id'] = (int)$data['region_id'];
            }
            $this->setData('form_data', $data);
        }
        return $data;
    }
}