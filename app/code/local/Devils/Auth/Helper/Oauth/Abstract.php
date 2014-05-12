<?php
abstract class Devils_Auth_Helper_Oauth_Abstract extends Mage_Core_Helper_Abstract {
    const XML_PATH_DEVILS_AUTH = 'default/devils_auth/';
    protected $app_id			= '';
    protected $app_secret		= '';

    public $oa_access_token		= '';
    public $oa_valid_till		= 0;
    public $oa_user_id			= 0;

    protected $user_data_response	= NULL;

    abstract protected function &_connectAndGrabUserData();

    abstract public function getAccessToken($code, $reason = '');

    abstract public function getUserID();
    abstract public function getUserData();
    abstract public function getUserPhoto();

    protected function _getSession()
    {
        return Mage::getSingleton('devils_auth/session');
    }

    protected function _curlResponse($url, $data = '', $post = false)
    {
        $query = '';
        if ($data) {
            if (is_array($data)) {
                $query = http_build_query($data);
            } else {
                $query = $data;
            }

            if (!$post) {
                $url .= '?' . $query;
            }
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        }
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public function setOauthData($data)
    {
        $this->oa_access_token = $data['oa_access_token'];
        $this->oa_valid_till = $data['oa_valid_till'];
        $this->oa_user_id = $data['oa_user_id'];
        return $this;
    }
}