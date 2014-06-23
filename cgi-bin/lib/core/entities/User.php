<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author murphyk
 */
class User {
    private $full_name;
    private $first_name;
    private $last_name;
    private $password;

    public function __construct($name, $password) {
        $this->full_name = $name;
        $this->password = $password;
    }

    public function get_name() {
        return $this->full_name;
    }
    
    public function get_first_name() {
        return $this->first_name;
    }
    
    public function set_first_name($first_name) {
        $this->first_name = $first_name;
    }
    
    public function get_last_name() {
        return $this->last_name;
    }
    
    public function set_last_name($last_name) {
        $this->last_name = $last_name;
    }

    public function get_password() {
        return $this->password;
    }

    public function compareUser(User $otherUser) {
        if (strcmp($this->name, $otherUser->get_name()) == 0) {
            if (strcmp($this->password, $otherUser->get_password()) == 0) {
                return true;
            }
        }
        return false;
    }
}
?>
