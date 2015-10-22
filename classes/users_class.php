<?php

class users extends Record {

    function GetByEmail($email) {
        $result = $this->GetByCause(array('email' => $email));
        
        if (!empty($result)) {
            return reset($result);
        } else {
            return false;
        }
    } // GetByEmail()
    

    function beforeAdd($item) {
        if (empty($item['avatar'])) {
            $item['avatar'] = $this->_grabAvatar($item);
        }
        
        return $item;
    }
    
    
    function beforeEdit($key, $item) {
        //if (empty($item['avatar'])) $item['avatar'] = $this->_grabAvatar($item);
        
        return $item;
    }
    
    
    function afterGet($item) {
        $item['avatar_small'] = null;
        if (!empty($item['avatar'])) {
            if ($this->dsp->eis->isEisUrl($item['avatar'])) {
                list($w, $h) = _isMobile() ? array(40, 40) : array(49, 49);
                $item['avatar'] = $this->dsp->eis->Resize($item['avatar'], $w, $h, 'crop');
                $item['avatar_small'] = $this->dsp->eis->Resize($item['avatar'], 21, 21, 'crop');
            } else {
                $item['avatar_small'] = $item['avatar'];
            }
        }
        
        return $item;
    }
    
    function GetItem($key) {
    	$item = parent::GetItem($key);
        if (!empty($item['email']) && !empty($item['from_site'])) {
            $item['link'] = $this->getLink($item['email'], $item['from_site']);
        }
    	
    	return $item;
    }
    
    function _grabAvatar($user) {
        $data = $this->auth->sso->getData($user['email']);
        
        $avatar = '';
        if (isset($data['oauth2']) && isset($data['oauth2']['facebook']))
            $avatar = 'http://graph.facebook.com/'. $data['oauth2']['facebook']['id'] . '/picture';
        elseif (isset($data['oauth1']) && isset($data['oauth1']['twitter']))
            $avatar = $data['oauth1']['twitter']['profile_image_url'];
    //    elseif (isset($data['oauth2']) && isset($data['oauth2']['odnoklassniki']))
    //        $avatar=$data['oauth2']['odnoklassniki']['pic_1'];
        elseif (isset($data['oauth2']) && isset($data['oauth2']['vkontakte']))
            $avatar = $data['oauth2']['vkontakte']['photo'];
        elseif (isset($data['oauth2']) && isset($data['oauth2']['google']))
            $avatar = str_replace('photo.jpg','s48-c-k/photo.jpg',$data['oauth2']['google']['picture']);
        elseif (isset($data['oauth2']) && isset($data['oauth2']['mailru']) && $data['oauth2']['mailru']['has_pic'])
            $avatar = $data['oauth2']['mailru']['pic_small'];

        return $avatar;
        
        /*
        $ch = curl_init($avatar);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_PROXY, 'proxy:80');
        curl_setopt($ch, CURLOPT_PROXYPORT, 80);
        curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, false);

        // grab URL
        $result = curl_exec($ch);
        if ($result) {
            $this->dsp->im
        }
        */
    } // _grabAvatar()

	function getLink($email, $type) {
		$data = $this->dsp->auth->sso->getData($email);
		
		$link = '';
    	switch ($type) {
     		case "facebook"  :
            case "mailru"  :
                $link = isset($data['oauth2']) && isset($data['oauth2'][$type]) ? $data['oauth2'][$type]['link'] : '';
                break;    
    	 	
    	 	case "twitter"  :
                $link = isset($data['oauth1']) && isset($data['oauth1'][$type]) ? 'https://twitter.com/' . $data['oauth1'][$type]['screen_name'] : '';
                break;
    
    		case "vkontakte"  :
                $link = isset($data['oauth2']) && isset($data['oauth2'][$type]) ? 'http://vk.com/id' . $data['oauth2'][$type]['id'] : '';
                break;
            
            case "odnoklassniki"  :
                $link = isset($data['oauth2']) && isset($data['oauth2'][$type]) ? 'http://www.odnoklassniki.ru/profile/' . $data['oauth2'][$type]['uid'] : '';
                break;
        }
        return  $link;
    }
    
    public function getSex($user)
    {
        if (!empty($user['sex']) && ($user['sex'] == 'w' || $user['sex'] == 'm')) {
            return $user['sex'];
        } else {
            return false;
        }
    }
    
    public function getDisplayName($user, $private = false) 
    {
        if (!$private) {
            $user = $this->dsp->users_settings->removeHiddenFields($user);
        }
    
        if (!empty($user['nickname']) && trim($user['nickname']) != '') {
            return $user['nickname'];
        }
        
        $hasFirstName = !empty($user['first_name']) && trim($user['first_name']) != '';
        $hasLastName = !empty($user['last_name']) && trim($user['last_name']) != '';

        if ($hasFirstName && $hasLastName) {
            return $user['first_name'] . ' ' . $user['last_name'];
        } elseif ($hasFirstName) {
            return $user['first_name'];
        } elseif ($hasLastName) {
            return $user['last_name'];
        } elseif ($private && !empty($user['email']) && trim($user['email']) != '') {
            return $user['email'];
        } elseif (!empty($user['user_id'])) {
            return $user['user_id'];
        } elseif (!empty($user['id'])) {
            return $user['id'];
        }
    }
    
    public function isUniqueNickname($nickname, $email)
    {
        $ssoemail = $this->auth->sso->getEmailByNickname($nickname);
        if (!empty($ssoemail) && $ssoemail != $email) {
            return false;
        }        
        $sql = "SELECT 1 FROM `" . $this->__tablename__ . "` WHERE `nickname` = ? AND `email` != ?";
        return !$this->dsp->db->SelectValue($sql, $nickname, $email);
    }
    
} // class users
