<?php
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
        
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UserDAO
 *
 * @author murphyk
 */
class UserDAO {
    private $membersfile = "";

    public function __construct() {
        $this->membersfile = DIR_LIB . "/core/authentication/.htpasswd";
    }
    
    public function is_member($member) {
        $members = $this->load_members();
        $mbr = trim($member);
        if (in_array($mbr, $members)) {
            return true;
        }
        
        return false;
    }
    
    public function add_member($member) {
        $members = $this->load_members();
        $mbr = trim($member);
        $existing_members = "";
        for ($i = 0; $i < count($members); $i++) {
            $existing_members .= $members[$i] . "\n";
        }

        $existing_members .= $mbr;

        return file_put_contents($this->membersfile, $existing_members);
    }
    
    public function remove_member($member) {
        $members = $this->load_members();
        $mbr = trim($member);
        $mbrs = array();
        for ($i = 0; $i < count($members); $i++) {
            if (strcasecmp($mbr, $members[$i])!= 0) {
                $mbrs[] = $members[$i];
            }
        }

        $existing_members = "";
        for ($i = 0; $i < count($mbrs); $i++) {
            $existing_members .= $mbrs[$i];
            if ($i < (count($mbrs) - 1)) {
                $existing_members .= "\n";
            }
        }

        return file_put_contents($this->membersfile, $existing_members);
    }
    
    public static function list_members() {
        $userDAO = new UserDAO();
        return $userDAO->load_members();
    }

    private function load_members() {
        $mbrs = file($this->membersfile);
        $members = array();
        forEach($mbrs as $m) {
            $m1 = rtrim($m);
            if (strlen($m1) > 0) { 
                $members[] = $m1;
            }
        }
        return $members;
    }
}
?>
