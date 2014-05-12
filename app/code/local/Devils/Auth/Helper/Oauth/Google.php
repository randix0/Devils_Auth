<?php
class Devils_Auth_Helper_Oauth_Google extends Devils_Auth_Helper_Oauth_Abstract
{
    private $config = NULL;
    /**
     * Get segments file name
     *
     * @param integer $generation
     * @return string
     */
    public function __construct() {
        $config = Mage::getConfig()->getNode(self::XML_PATH_DEVILS_AUTH . 'google')->asArray();
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
    public function getAccessToken($code, $reason = '')
    {
        $redirectUrn = 'auth/index/step1/handler/google/';
        if ($reason) {
            $redirectUrn .= 'reason/' . $reason;
        }
        $redirectUri = Mage::getUrl($redirectUrn);

        $tokenUrl = 'https://accounts.google.com/o/oauth2/token';
        $data = array(
            'client_id' => $this->app_id,
            'client_secret' => $this->app_secret,
            'redirect_uri' => $redirectUri,
            'code' => $code,
            'grant_type' => authorization_code
        );
        /*
        $token_url	= 'https://accounts.google.com/o/oauth2/token';
        $data		= 'code='.$code.
            '&client_id='.$this->app_id.
            '&client_secret='.$this->app_secret.
            '&redirect_uri='. urlencode($redirectUri).
            '&grant_type=authorization_code';
        */


        $response = NULL;

        $contents = $this->_curlResponse($tokenUrl, $data, true);

        if($contents/* = @file_get_contents($token_url, false, stream_context_create(array('http'=>array(
            'method'	=>'POST',
            'header'	=> "Content-type: application/x-www-form-urlencoded\r\n".
                "Content-Length: " . strlen($data) . "\r\n",
            'content'	=>$data
        ))))*/) {

            $response = json_decode($contents, true);

        } else {
            throw new Exception('Connection error', CONNECTION_ERROR);
        }

        if($response && isset($response['access_token']) && $response['access_token']) {
            $this->oa_access_token	= $response['access_token'];
            $this->oa_valid_till	= time() + $response['expires_in'];
            $this->oa_user_id		= 0;

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
    protected function &_connectAndGrabUserData() {
        if(!$this->user_data_response) {

            if(!$this->oa_access_token) {
                throw new Exception('Token not valid', TOKEN_NOT_VALID);
            }

            if($this->oa_valid_till < time()) {
                throw new Exception('Token expired', TOKEN_EXPIRED);
            }

            $response = NULL;

            if($contents = @file_get_contents('https://www.googleapis.com/oauth2/v2/userinfo?access_token='.$this->oa_access_token)) {

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

            return array(
                'first_name'		=> $response['given_name'],
                'last_name'			=> $response['family_name'],
                //'url_friendly_name'	=> '',
                'email'				=> (isset($response['verified_email']) && $response['verified_email'])?$response['email']:'',
                //'timezone'			=> '',
                'gender'			=> $response['gender']?(($response['gender']=='male')?2:1):0,
                'google_id'			=> $response['id']
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

        if($response && isset($response['picture']) && $response['picture']) {
            return $response['picture'];
        }

        return false;
    }
}