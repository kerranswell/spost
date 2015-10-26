<?php

require_once(LIB_DIR."facebook-v4/autoload.php");

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookSDKException;

if (!defined('CRON')) if (!session_id()) @session_start();

FacebookSession::setDefaultApplication(FB_APP_ID, FB_APP_SECRET);

class socials {

    public $fb_redirect_url;

    public function socials()
    {
        $this->fb_redirect_url = SITE.'/admin/?op=posts&act=tokens&type=fb';
    }

    public function getFBLoginUrl()
    {
        $helper = new FacebookRedirectLoginHelper($this->fb_redirect_url);
        return $helper->getLoginUrl(['manage_pages','publish_pages']);
    }

    public function updateFBAccessToken()
    {
        $helper = new FacebookRedirectLoginHelper($this->fb_redirect_url);
        try {
            $session = $helper->getSessionFromRedirect();
        } catch(FacebookRequestException $ex) {
            // When Facebook returns an error
        } catch(\Exception $ex) {
            // When validation fails or other local issues
        }
        if ($session) {

            $accessToken = $session->getAccessToken();
            try {
                // Exchange the short-lived token for a long-lived token.
                $longLivedAccessToken = $accessToken->extend();
            } catch(FacebookSDKException $e) {
                echo 'Error extending short-lived access token: ' . $e->getMessage();
                exit;
            }

            $session = new FacebookSession($longLivedAccessToken);
            $request = new FacebookRequest($session, 'GET', '/me/accounts?fields=name,access_token,perms');
            $pageList = $request->execute()
                ->getGraphObject()
                ->asArray();
            foreach ($pageList['data'] as $page)
            {
                if ($page->id = FB_ACCOUNT_ID)
                {
                    $access_token = $page->access_token;
                    $this->dsp->db->Execute("update `social_tokens` set `value` = ? where `type` = 'fb_access_token'", $access_token);

                    $b = $this->dsp->_BuilderPatterns->create_block('tokens', 'tokens', 'center');
                    $this->dsp->_Builder->addNode($this->dsp->_Builder->createNode('status', array(), 'ok'), $b);
                    return;
                }
            }
        } else {
            echo 'Ошибка'; exit;
        }
    }

    public function post($post)
    {
        if ($post['type'] != 'tw')
        {
            $time = time();
            $n = date("n", $time);
            global $months_rod_pad;
            $title = "Сегодня ".date("j")." ".$months_rod_pad[$n].PHP_EOL.PHP_EOL;
            $post['text'] = $title.$post['text'];
        }

        switch ($post['type'])
        {
            case 'vk' : $this->postVK($post); break;
            case 'fb' : $this->postFB($post); break;
            case 'tw' : $this->postTW($post); break;
        }
    }

    public function postVK($post)
    {
        if ($post['text'] == '' && $post['image'] == 0 && $post['url'] == '') return;

        require_once(LIB_DIR."vkpublic.php");
        $vk = new VKPublic(VK_ACCOUNT_ID, VK_APP_ID, VK_APP_SECRET);
        $vk->setAccessData(VK_ACCESS_TOKEN, VK_ACCESS_SECRET);

        $attachments = [];
        if ($post['image'] > 0)
        {
            $attachments[] = $vk->createPhotoAttachment($post['image_file']);
        }

        if ($post['url'] != '')
        {
            $attachments[] = $post['url'];
        }

        if (count($attachments) > 0) $attachments = implode(',', $attachments);
        else $attachments = null;

        $r = $vk->wallPostAttachment($attachments, $post['text']);
        if (!empty($r->response->post_id) && $r->response->post_id > 0)
        {
            $sql = "update posts set published = 1, social_id = ? where `date` = ? and `type` = ?";
            $this->dsp->db->Execute($sql, $r->response->post_id, $post['date'], $post['type']);

            echo 'VK success: post id '.$r->response->post_id.PHP_EOL;
        }

    }

    public function postFB($post)
    {
        if ($post['text'] == '' && $post['image'] == 0 && $post['url'] == '') return;

        $sql = "select `value` from social_tokens where `type` = 'fb_access_token'";
        $access_token = $this->dsp->db->SelectValue($sql);

        $session = new FacebookSession($access_token);

        $params = [];
        $params['message'] = $post['text'];
        if ($post['url'] != '') $params['link'] = $post['url'];

/*        if ($post['image'] > 0)
        {
            $params['source'] = new FacebookFile($post['image_file']);
            $params['caption'] = $post['text'];
        }*/

        if ($session)
        {
            if ($post['image'] > 0)
            {
                $params['url'] = $post['image_url'];
            }

            try {
                $response = (new FacebookRequest($session, 'POST', '/'.FB_ACCOUNT_ID.'/'.($post['image'] > 0 ? 'photos' : 'feed'), $params))->execute()->getGraphObject();
                $sql = "update posts set published = 1, social_id = ? where `date` = ? and `type` = ?";
                $this->dsp->db->Execute($sql, $response->getProperty('id'), $post['date'], $post['type']);
                echo 'Facebook success: post id '.$response->getProperty('id').PHP_EOL;
            } catch (FacebookRequestException $e) {
                echo "Exception occured, code: " . $e->getCode();
                echo " with message: " . $e->getMessage();
            }
        }

    }

    public function postTW($post)
    {
        if ($post['image'] == 0 && $post['text'] == '' && $post['url'] == '') return;

        // require codebird
        require_once(LIB_DIR.'codebird-php-develop/src/codebird.php');

        \Codebird\Codebird::setConsumerKey(TW_API_KEY, TW_API_SECRET);
        $cb = \Codebird\Codebird::getInstance();
        $cb->setToken(TW_ACCESS_TOKEN, TW_ACCESS_TOKEN_SECRET);

        $params = [];
        $params['status'] = $post['text'];
//        if ($post['url'] != '') $params['status'] .= ($params['status'] == '' ? '' : ' ').$post['url'];
        if ($post['image'] > 0) $params['media[]'] = $post['image_file'];

/*        if(!Normalizer::isNormalized($params['status'],Normalizer::FORM_C)){
            $params['status'] = Normalizer::normalize($params['status'],Normalizer::FORM_C);
        }*/

/*        $params['status'] = iconv('Windows-1251', 'UTF-8', $params['status']);
        echo $params['status']; exit;*/

        if ($post['image'] > 0)
            $reply = $cb->statuses_updateWithMedia($params);
        else
            $reply = $cb->statuses_update($params);


        $status = $reply->httpstatus;
        if ($status == 200)
        {
            $sql = "update posts set published = 1, social_id = ? where `date` = ? and `type` = ?";
            $this->dsp->db->Execute($sql, $reply->id, $post['date'], $post['type']);
            echo 'Twitter success: post id '.$reply->id.PHP_EOL;
        } else {
            echo 'Twitter failed: '.print_r($reply, true).PHP_EOL;
        }
    }

    protected function curl($url)
    {
        $ch = curl_init();

        // set url
        curl_setopt($ch, CURLOPT_URL, $url);
        // return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        // disable SSL verifying
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        // $output contains the output string
        $result = curl_exec($ch);

        if (!$result) {
            $errno = curl_errno($ch);
            $error = curl_error($ch);
        }

        curl_close($ch);

        if (isset($errno) && isset($error)) {
            throw new \Exception($error, $errno);
        }

        return $result;
    }


}