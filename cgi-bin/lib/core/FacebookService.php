<?php
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    include_once(DIR_LIB . "/core/Logger.php");
    include_once(DIR_LIB . "/core/Item.php");
    include_once(DIR_LIB . "/facebook/facebook.php");
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FacebookService
 *
 * @author Kieran
 */
class FacebookService {
    private $fb = null;
    
    public function __construct() {
        $config = array();
        $config['appId'] = FACEBOOK_APP_ID;
        $config['secret'] = FACEBOOK_APP_SECRET;
        $this->fb = new Facebook($config);
        
        if (isset($_SESSION['fb'])) {
            // Set the cached access token
            $this->fb->setAccessToken(strval($_SESSION['fb']));
        } else {
            //Logger::log_error("Facebook not initialised correctly.", __FILE__, __LINE__);
        }
    }
    
    public function publish_item(Item $item, $alternative_title = null) {
        if ($this->fb !== null && $this->fb->getUser()) {
            $title = $item->getTitle();
            if ($alternative_title !== null && strlen($alternative_title) > 0 ) {
                $title = $alternative_title;
            }
            $data = array(
                'message'=>$item->getDescription(),
                'link'=>$item->getLink(),
                'name'=>$title,
                'caption'=>APP_DOMAIN,
                'description'=>"For full details follow this link." //$item->getDescription()
            );

            $this->publish_data($data);
        }
    }
    
    private function publish_data(array $data) {
        if ($this->fb !== null && $this->fb->getUser()) {
            // We're logged in so try and post...
            try {
                $ret_obj = $this->fb->api('me/accounts', 'GET');
                $id = null;
                $access_token = null;
                foreach ($ret_obj['data'] as $value) {
                    if (strcasecmp($value['name'], FACEBOOK_PAGE_NAME) == 0) {
                        $id = $value['id'];
                        $access_token = $value['access_token'];
                        break;
                    }
                }
                if ($id !== null && $access_token !== null) {
                    $this->fb->setAccessToken($access_token);
                    $ret_obj = $this->fb->api("/{$id}/feed", 'POST', $data);
                } else {
                    Logger::log_error('Failed to post to facebook.', __FILE__, __LINE__);
                }
            } catch (Exception $e) {
                Logger::log_error($e->getMessage(), __FILE__, __LINE__);
            }
        }
    }
}

?>
