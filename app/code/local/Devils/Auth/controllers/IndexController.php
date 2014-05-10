<?php
class Devils_Auth_IndexController extends Mage_Core_Controller_Front_Action
{
    protected $_handler = null;

    protected function _getHelper()
    {
        return Mage::helper('devils_auth');
    }

    protected function _getSession()
    {
        return Mage::getSingleton('devils_auth/session');
    }

    protected function _getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }

    protected function _getHandler($handlerId = false)
    {
        if (!$this->_handler) {
            $this->_handler = Mage::helper('devils_auth/oauth_' . $handlerId);
        }
        return $this->_handler;
    }

    public function indexAction()
    {
        echo 'hello!';
        $customerForm = Mage::getModel('customer/form');
        $customerForm->setFormCode('customer_account_create');
        $customerAttributes = $customerForm->getAttributes();

        $requiredAttributes = array();
        foreach($customerAttributes as $attrCode => $attribute) {
            if ($attribute->getIsRequired()) {
                $requiredAttributes[$attrCode] = $attribute;
                echo $attrCode . '<br>';
            }
        }

        $customerData = array(
            'firstname' => 'Vasia',
            'lastname' => 'Pupkin',
            'email' => 'pupkin@randix.info'
        );

        var_dump($this->_getCustomerSession()->getCustomer()->getData());
        exit();
    }

    public function step1Action()
    {
        $handlerId = $this->getRequest()->getParam('handler', false);
        $reason = $this->getRequest()->getParam('reason', false);
        $code = $this->getRequest()->getParam('code', null);

        if ($this->_getCustomerSession()->isLoggedIn()) {
            setcookie('sl', 1, 0 , '/');
            echo '<script type="text/javascript">window.close();</script>';
            return;
        }

        $response = $this->_getHandler($handlerId)->getAccessToken($code, $reason);
        if ($response) {
            $oauthResponse = array(
                'oa_access_token' => $this->_handler->oa_access_token,
                'oa_valid_till' => $this->_handler->oa_valid_till,
                'oa_user_id' => (string)$this->_handler->oa_user_id,
            );

            $this->_getSession()->setHandlerData($handlerId, $oauthResponse);

            setcookie('sl', 1, 0 , '/');
            echo '<script type="text/javascript">window.close();</script>';
        }
        //$response =
        //echo 'step1' . $handlerId . 'code = ' . $code;
    }

    public function step2Action()
    {
        $result = array('status' => 0);
        $handlerId = $this->getRequest()->getParam('handler', false);
        $oauthResponse = $this->_getSession()->getHandlerData($handlerId);
        $session = $this->_getCustomerSession();

        $oauthData = $this->_getHandler($handlerId)
            ->setOauthData($oauthResponse)
            ->getUserData();

        $customer = $this->_getHelper()->getCustomerByAttribute($handlerId . '_id', $oauthResponse['oa_user_id']);

        if ($customer) {
            $session->setCustomerAsLoggedIn($customer);
            $this->_getSession()->setData('customer_id', $customer->getId());
            $result['status'] = 1;
        } else {
            $oauthEmail = false;
            if (isset($oauthData['email']) && !empty($oauthData['email'])) {
                $oauthEmail = $oauthData['email'];
            }
            $customer = $this->_getHelper()->getCustomerByAttribute('email', $oauthEmail);
            if ($oauthEmail && $customer) {
                $customerData = array(
                    $handlerId . '_id' => $oauthResponse['oa_user_id'],
                    $handlerId . '_oa_access_token' => $oauthResponse['oa_access_token'],
                    $handlerId . '_oa_valid_till' => $oauthResponse['oa_valid_till']
                );
                $customer->addData($customerData);
                $customer->save();
                $session->setCustomerAsLoggedIn($customer);
                $result['status'] = 1;
            } else {
                $customerData = array(
                    'firstname' => $oauthData['first_name'],
                    'lastname' => $oauthData['last_name'],
                    'email' => $oauthData['email'],
                    $handlerId . '_id' => $oauthResponse['oa_user_id'],
                    $handlerId . '_oa_access_token' => $oauthResponse['oa_access_token'],
                    $handlerId . '_oa_valid_till' => $oauthResponse['oa_valid_till']
                );

                try {
                    $customer = Mage::getModel('customer/customer');
                    $customer->setData($customerData);
                    $customer->save();
                    $session->setCustomerAsLoggedIn($customer);
                    $result['status'] = 1;
                } catch (Mage_Core_Exception $e) {
                    Mage::logException($e);
                    $result['status'] = 0;
                }
            }

            //$customer = Mage::getModel('customer/customer')->loadByEmail($oauthData['email']);
            //echo 'Register as ' . $oauthData['first_name'] . ' ' . $oauthData['last_name'];
            //$this->_getHelper()->registerCustomer($oauthData);
        }
        echo json_encode($result);
    }
}