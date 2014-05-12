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
            if (isset($oauthData['email']) && !empty($oauthData['email'])) {
                $oauthEmail = $oauthData['email'];
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
            } else {
                $customerData = array(
                    'firstname' => $oauthData['first_name'],
                    'lastname' => $oauthData['last_name'],
                    'handler_id' => $handlerId,
                    'oa_id' => $oauthResponse['oa_user_id'],
                    'oa_access_token' => $oauthResponse['oa_access_token'],
                    'oa_valid_till' => $oauthResponse['oa_valid_till']
                );

                $this->_getCustomerSession()->setCustomerFormData($customerData);
                $result['url'] = Mage::getUrl('auth/index/register');
                $result['status'] = 2;
            }
        }
        echo json_encode($result);
    }

    public function registerAction()
    {
        if ($this->_getCustomerSession()->isLoggedIn()) {
            $this->_redirect('*/*');
            return;
        }
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }

    public function registerPostAction()
    {
        /** @var $session Mage_Customer_Model_Session */
        $session = $this->_getCustomerSession();
        if ($session->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }

        if (!$this->_validateFormKey()) {
            return $this->_redirect('*/*/register');
        }

        $session->setEscapeMessages(true); // prevent XSS injection in user input
        if (!$this->getRequest()->isPost()) {
            $errUrl = Mage::getUrl('*/*/register', array('_secure' => true));
            $this->_redirectError($errUrl);
            return;
        }

        if ($this->getRequest()->isPost()) {
            $formData = $this->getRequest()->getPost();
            $customerData = array(
                'firstname' => $formData['firstname'],
                'lastname' => $formData['lastname'],
                'email' => $formData['email'],
                $formData['handler_id'] . '_id' => $formData['oa_id'],
                $formData['handler_id'] . '_oa_access_token' => $formData['oa_access_token'],
                $formData['handler_id'] . '_oa_valid_till' => $formData['oa_valid_till'],
            );

            try {
                $customer = Mage::getModel('customer/customer');
                $customer->setData($customerData);
                $customer->save();
                if ($customer->getId() > 0) {
                    $session->setCustomerAsLoggedIn($customer);
                }
                $this->_redirect('customer/accou nt/');
            } catch (Mage_Core_Exception $e) {
                $session->setCustomerFormData($this->getRequest()->getPost());
                if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
                    $url = Mage::getUrl('customer/account/forgotpassword');
                    $message = $this->__('There is already an account with this email address. If you are sure that it is your email address, <a href="%s">click here</a> to get your password and access your account.', $url);
                    $session->setEscapeMessages(false);
                } else {
                    $message = $e->getMessage();
                }
                $session->addError($message);
            } catch (Exception $e) {
                $session->setCustomerFormData($this->getRequest()->getPost())
                    ->addException($e, $this->__('Cannot save the customer.'));
            }

            $errUrl = Mage::getUrl('*/*/register', array('_secure' => true));
            $this->_redirectError($errUrl);
        }
    }
}