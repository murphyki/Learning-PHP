<?php
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    include_once(DIR_LIB . "/core/Utils.php");
    include_once(DIR_LIB . "/core/Logger.php");
    include_once(DIR_LIB . "/core/Item.php");
    include_once(DIR_LIB . "/core/entities/User.php");
    include_once(DIR_LIB . "/core/authentication/AuthenticationService.php");
    include_once(DIR_LIB . "/facebook/facebook.php");
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FacebookAuthenticationService
 *
 * @author Kieran
 */
class FacebookAuthenticationService extends AuthenticationService {
    private $fb = null;
    
    public function __construct() {
        $config = array();
        $config['appId'] = FACEBOOK_APP_ID;
        $config['secret'] = FACEBOOK_APP_SECRET;
        $this->fb = new Facebook($config);
    }
    
    public function do_login() {
        $domain = APP_DOMAIN;
        
        if (isset($_SESSION['fb'])) {
            // Set the cached access token
            $this->fb->setAccessToken(strval($_SESSION['fb']));
        } else {
            // If we have a code exchange it for an access token
            if (isset($_REQUEST['code'])) {
                // Get the code
                $code = $_REQUEST['code'];
                
                // Get the access token
                $redirect_uri = "http://{$domain}/admin/login.php";
                $ch = curl_init(); 
                curl_setopt($ch, CURLOPT_URL, 'https://graph.facebook.com/oauth/access_token'); 
                curl_setopt($ch, CURLOPT_POSTFIELDS,'client_id='.urlencode(FACEBOOK_APP_ID).'&client_secret='.urlencode(FACEBOOK_APP_SECRET).'&redirect_uri='.urlencode($redirect_uri).'&code='.urlencode($code));
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
                $data = curl_exec($ch);
                curl_close($ch);

                // Parse for the access token
                $params = null;
                parse_str($data, $params);
                $_SESSION['fb'] = $params['access_token'];
                
                // Set the access token for this instance
                $this->fb->setAccessToken(strval($params['access_token']));
            }
        }
        
        // Set the user
        $this->set_loggedin_user($this->get_user());
        
        Utils::redirect(ADMIN_URL . "/");
    }
    
    public function get_login_url() {
        return $this->fb->getLoginUrl(array(
            "scope"=>"manage_pages,publish_stream,offline_access"
        ));
    }
    
    public function get_logout_url() {
        return $this->fb->getLogoutUrl();
    }
    
    public function get_user() {
        try {
            $user_info = $this->fb->api('/me');
            $user = new User($user_info['username'], "");
            $user->set_first_name($user_info['first_name']);
            $user->set_last_name($user_info['last_name']);
            return $user;
        } catch (FacebookApiException $e) {
            trigger_error($e);
        }
    }
}

?>
