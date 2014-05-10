<?php
class Devils_Auth_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_customerSession = null;
    protected $_session = null;

    protected function _getSession()
    {
        return Mage::getSingleton('devils_auth/session');
    }

    protected function _getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }

    public function getCustomerByAttribute($code = '', $value = '')
    {
        $collection = Mage::getResourceModel('customer/customer_collection')
            ->addAttributeToSelect('*')
            ->addAttributeToFilter($code, $value)
            ->setPage(1,1);

        foreach ($collection as $customer) {
            if ($customer->getId()) {
                return $customer;
            }
        }
        return false;
    }

    public function registerCustomer($data)
    {
        echo 'register customer ' . $data['first_name'] . ' ' . $data['last_name'];
    }

    public function loginCustomer($customer)
    {
        try {
            //$customer->
            $this->_getCustomerSession()->login($customer->getEmail(), '123123q');
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
        }
        //$this->_getCustomerSession()->renewSession();

        //var_dump($this->_getCustomerSession()->isLoggedIn());
    }
}