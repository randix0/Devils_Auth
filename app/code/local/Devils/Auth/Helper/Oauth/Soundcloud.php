<?php
class Devils_Auth_Helper_Oauth_Soundcloud extends Devils_Auth_Helper_Oauth_Abstract
{
    /**
     * Get segments file name
     *
     * @param integer $generation
     * @return string
     */
    public function __construct()
    {
        $config = Mage::getConfig()->getNode(self::XML_PATH_DEVILS_AUTH . 'facebook')->asArray();
        $this->app_id		= $config['app_id'];
        $this->app_secret	= $config['app_secret'];

        if(!$this->app_id || !$this->app_secret) {
            throw new Exception('Application ID not set', APP_ID_OR_SECRET_NOT_SET);
        }

    }

    /**
     * Get segments file name
     *
     * @param integer $generation
     * @return string
     */
    protected function &_connectAndGrabUserData() {
        if(!$this->user_data_response) {

            if(!$this->oa_access_token) {
                throw new Exception('Token not valid');
            }

            if($this->oa_valid_till < time()) {
                throw new Exception('Token expired');
            }

            $response = NULL;

            $url = 'https://api.soundcloud.com/me.json?access_token='.$this->oa_access_token;

            $contents = $this->_curlResponse($url);

            if($contents) {

                $response = json_decode($contents, true);

                if(isset($response['id']) && $response['id']) {
                    $this->user_data_response = $response;
                } else {
                    throw new Exception('Get user data failed', CONNECTION_ERROR);
                }
            } else {
                throw new Exception('Connection error', CONNECTION_ERROR);
            }

        }

        return $this->user_data_response;
    }

    /**
     * Get segments file name
     *
     * @param integer $generation
     * @return string
     */
    public function getAccessToken($code, $reason = '') {
        $redirectUrn = 'auth/index/step1/handler/soundcloud/';
        if ($reason) {
            $redirectUrn .= 'reason/' . $reason;
        }
        $redirectUri = Mage::getUrl($redirectUrn);

        $tokenUrl	= 'https://api.soundcloud.com/oauth2/token';
        $data = array(
            'client_id' => $this->app_id,
            'client_secret' => $this->app_secret,
            'redirect_uri' => $redirectUri,
            'code' => $code,
            'grant_type' => 'authorization_code'
        );

        $response = NULL;

        $contents = $this->_curlResponse($tokenUrl, $data, true);
        if($contents) {
            $response = json_decode($contents, true);
        } else {
            throw new Exception('Connection error', CONNECTION_ERROR);
        }

        if($response && isset($response['access_token']) && $response['access_token']) {
            $this->oa_access_token	= $response['access_token'];
            $this->oa_valid_till	= time() + 3600;
            $this->oa_user_id		= $this->getUserID();

            return true;
        }

        return false;
    }

    /**
     * Get segments file name
     *
     * @param integer $generation
     * @return string
     */
    public function getUserID() {
        if($this->oa_user_id)
            return $this->oa_user_id;

        $response = $this->_connectAndGrabUserData();

        if($response) {
            $this->oa_user_id = $response['id'];

            return $this->oa_user_id;
        }

        return false;
    }

    /**
     * Get segments file name
     *
     * @param integer $generation
     * @return string
     */
    public function getUserData() {
        $response = $this->_connectAndGrabUserData();

        if($response) {
            $this->oa_user_id = $response['id'];
            $fullName = $response['full_name'];
            $name = exclude(' ', $fullName);

            return array(
                'first_name'		=> $name[0],
                'last_name'			=> $name[1],
                'url_friendly_name'	=> (isset($response['permalink'])?$response['permalink']:''),
                'email'				=> (isset($response['email']) && $response['email'] && isset($response['verified']) && $response['verified'])?$response['email']:'',
                'timezone'			=> 0,
                'gender'			=> 0,
                'soundcloud_id'		=> $response['id']
            );
        }

        return false;
    }

    /**
     * Get segments file name
     *
     * @param integer $generation
     * @return string
     */
    public function getUserPhoto() {
        $response = $this->_connectAndGrabUserData();

        if($response && isset($response['avatar_url']) && $response['avatar_url']) {
            return $response['avatar_url'];
        }

        return false;
    }
}