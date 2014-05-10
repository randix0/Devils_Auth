<?php
class Devils_Auth_Model_Session extends Mage_Core_Model_Session_Abstract
{
    public function __construct()
    {
        $namespace = 'devils_auth';
        $this->init($namespace);
    }

    public function setHandlerData($handlerId = '', $data = array())
    {
        $this->setData('oauth_response_' . $handlerId, $data);
    }

    public function getHandlerData($handlerId = '')
    {
        $data = $this->getData('oauth_response_' . $handlerId);
        return $data;
    }
}