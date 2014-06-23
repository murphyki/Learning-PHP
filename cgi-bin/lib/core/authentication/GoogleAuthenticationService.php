<?php
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    include_once(DIR_LIB . "/core/Utils.php");
    include_once(DIR_LIB . "/core/Logger.php");
    include_once(DIR_LIB . "/core/entities/User.php");
    include_once(DIR_LIB . "/core/authentication/AuthenticationService.php");
    include_once(DIR_LIB . "/core/authentication/openid.php");
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of GoogleAuthenticationService
 *
 * @author murphyk
 */
class GoogleAuthenticationService extends AuthenticationService {
    
    public function __construct() {
    }
    
    public function do_login() {
        $host = APP_DOMAIN;
        $openid = new LightOpenID($host);
        if(!$openid->mode) {
            $openid->returnUrl = "http://{$host}/admin/login.php?provider=google";
            $openid->identity = 'https://www.google.com/accounts/o8/id';
            $openid->required = array('contact/email', 'namePerson/first', 'namePerson/last');
            header('Location: ' . $openid->authUrl());
        } else if($openid->mode == 'cancel') {
            // Login cancelled - nothing else to do i guess...
        } else {
            if ($openid->validate()) {
                session_regenerate_id();
                $attribs = $openid->getAttributes();
                $user = new User($attribs["contact/email"], "");
                $user->set_first_name($attribs["namePerson/first"]);
                $user->set_last_name($attribs["namePerson/last"]);
                $this->set_loggedin_user($user);
                Utils::redirect(ADMIN_URL . "/");
            }
        }
        
        return false;
    }
}
?>
