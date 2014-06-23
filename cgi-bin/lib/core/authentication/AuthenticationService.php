<?php
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    include_once(DIR_LIB . "/core/Utils.php");
    include_once(DIR_LIB . "/core/Logger.php");
    include_once(DIR_LIB . "/core/authentication/UserDAO.php");
    include_once(DIR_LIB . "/core/authentication/GoogleAuthenticationService.php");
    include_once(DIR_LIB . "/core/authentication/FacebookAuthenticationService.php");
    include_once(DIR_LIB . "/core/entities/User.php");
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SecurityService
 *
 * @author murphyk
 */
class AuthenticationService {
    private $login_page;
    
    // private constructor
    public function __construct($login_page = "login.php") {
        $this->login_page = $login_page;
    }
    
    public function is_admin_logged_in() {
        if ($this->is_logged_in()) {
            $username = $this->get_loggedin_user();
            
            foreach(Config::get_admins() as $adminUser) {
                if ($adminUser[1] == $username) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    public function is_logged_in() {
        if ($this->has_session_timed_out()) {
            return false;
        }
        
        if (isset($_SESSION['pinky']) && $_SESSION['pinky'] == 'the brain' &&
            isset($_SESSION['brain']) && $_SESSION['brain'] !== null) {
            return true;
        }
        
        // check did the user authenticate through the browser
        //if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
        //    $username = $_SERVER['PHP_AUTH_USER'];
        //    $password = $_SERVER['PHP_AUTH_PW'];
        //    return $this->validate_user($username, $password);
        //}
        
        return false;
    }
    
    public function is_member_logged_in() {
        return ($this->is_logged_in() && $this->is_member());
    }
    
    public function is_member() {
        if ($this->is_logged_in()) {
            $username = $this->get_loggedin_user();
            $userDAO = new UserDAO();
            return $userDAO->is_member($username);
        }
        return false;
    }
    
    public function add_member() {
        if ($this->is_admin_logged_in()) {
            $username = Utils::get_user_input("username");
            $member = trim($username);
            if (strlen($member) == 0) {
                Logger::log_error("User name not specified.", __FILE__, __LINE__);
            }
            
            $userDAO = new UserDAO();
            if ($userDAO->is_member($member)) {
                Logger::log_error("User is already a member!", __FILE__, __LINE__);
            }
            
            return $userDAO->add_member($member);
        }
        return false;
    }
    
    public function remove_member() {
        if ($this->is_admin_logged_in()) {
            $username = Utils::get_user_input("username");
            $userDAO = new UserDAO();
            $member = trim($username);
            if (strlen($member) == 0) {
                Logger::log_error("User name not specified.", __FILE__, __LINE__);
            }
            
            $userDAO = new UserDAO();
            if (!$userDAO->is_member($member)) {
                Logger::log_error("User is not a member!", __FILE__, __LINE__);
            }
            
            return $userDAO->remove_member($member);
        }
        return false;
    }

    public function login($provider) {
        //if ($this->is_logged_in()) {
        //    return true;
        //}
        
        $service = null;
        if (strcasecmp("google", $provider) == 0) {
            $service = new GoogleAuthenticationService();
        } else if (strcasecmp("facebook", $provider) == 0) {
            $service = new FacebookAuthenticationService();
        } else {
            Logger::log_error("Unknown provider specified for login.", __FILE__, __LINE__);
        }
        return $service->do_login();
    }
    
    public function show_login_page() {
        Utils::redirect(ADMIN_URL . "/" . $this->login_page);
    }
    
    public function logout($refer = true) {
        session_unset();

        if (isset($_SERVER['fb'])){
            unset($_SERVER['fb']);
        }
        
        if (isset($_SERVER['PHP_AUTH_USER'])){
            unset($_SERVER['PHP_AUTH_USER']);
        }

        if (isset($_SERVER['PHP_AUTH_PW'])){
            unset($_SERVER['PHP_AUTH_PW']);
        }
        
        if ($refer) {
            return $this->redirect_after_logout();
        }
        
        return true;
    }
    
    public function redirect_after_logout() {
        Utils::redirect("/");
        return true;
    }

    public function get_loggedin_user() {
        if ($this->is_logged_in()) {
            $user = unserialize($_SESSION['brain']);
            return $user->get_name();
        }

        return "";
    }
    
    public function get_loggedin_user_display_name() {
        if ($this->is_logged_in()) {
            $user = unserialize($_SESSION['brain']);
            return $user->get_first_name();
        }

        return "";
    }
    
    protected function set_loggedin_user(User $user) {
        unset($_SESSION['pinky']);
        $_SESSION['errorMsg'] = "";
        $_SESSION['infoMsg'] = "";
        $_SESSION['pinky'] = "the brain";
        $_SESSION['brain'] = serialize($user);
        $_SESSION["timeout"] = time();
        $_SERVER['PHP_AUTH_USER'] = $user->get_name();
        $_SERVER['PHP_AUTH_PW'] = $user->get_password();

        return true;
    }
    
    protected function has_session_timed_out() {
        if (isset($_SESSION["timeout"])) {
            $session_time = intval($_SESSION["timeout"]) + (30*60);// 30 min session
            if ($session_time < time()) {
                return $this->logout(false);
            }
        }
        return false;
    }
}
?>
