<?php

require_once(LIB_DIR."facebook-v4/autoload.php");
require_once(LIB_DIR."okphp_v1.php");

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookSDKException;

if (!defined('CRON')) if (!session_id()) @session_start();

FacebookSession::setDefaultApplication(FB_APP_ID, FB_APP_SECRET);

class socials {

    public $fb_redirect_url;
    public $ok_redirect_url;
    public $errors = [];
    public $settings = [];
    public $public_settings = [];

    public function Init()
    {
        $sql = "select * from `social_tokens`";
        $rows = $this->dsp->db->Select($sql);
        foreach ($rows as $row)
        {
            $this->settings[$row['type']] = $row['value'];
            if ($row['public'] == 1) $this->public_settings[$row['type']] = $row['value'];
        }

        $this->fb_redirect_url = SITE.'/admin/?op=posts&act=tokens&type=fb';
        $this->ok_redirect_url = SITE.'/admin/?op=posts&act=tokens&type=ok';
    }

    public function getOKLoginUrl()
    {
        $ok = new Social_APIClient_Odnoklassniki(
            array(
                'client_id' => OK_APP_ID,
                'application_key' => OK_PUBLIC_KEY,
                'client_secret' => OK_SECRET_KEY
            )
        );
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
/*        if ($post['type'] != 'tw')
        {
            $time = time();
            $n = date("n", $time);
            global $months_rod_pad;
            $title = "Сегодня ".date("j")." ".$months_rod_pad[$n].PHP_EOL.PHP_EOL;
            $post['text'] = $title.$post['text'];
        }*/

        switch ($post['type'])
        {
            case 'vk' : $this->postVK($post); break;
            case 'fb' : $this->postFB($post); break;
            case 'tw' : $this->postTW($post); break;
            case 'ok' : $this->postOK($post); break;
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
            $p = $vk->createPhotoAttachment($post['image_file']);
            if ($p) $attachments[] = $p;
            else $this->errors[] = ['type' => 'vk', 'message' => 'error loading photo'];
        }

        if ($post['url'] != '')
        {
            $attachments[] = $post['url'];
        }

        if (count($attachments) > 0) $attachments = implode(',', $attachments);
        else $attachments = null;

        $text = $post['text'];
        if (!empty($post['tags'])) $text .= PHP_EOL.$post['tags'];

        $r = $vk->wallPostAttachment($attachments, $text);
        if (!empty($r->response->post_id) && $r->response->post_id > 0)
        {
            $sql = "update posts set published = 1, social_id = ? where `date` = ? and `type` = ?";
            $this->dsp->db->Execute($sql, $r->response->post_id, $post['date'], $post['type']);

            echo 'VK success: post id '.$r->response->post_id.PHP_EOL;
        } else {
            echo 'VK failed'.print_r($r, true).PHP_EOL;
            $this->errors[] = ['type' => 'vk', 'message' => 'error posting', 'reply' => print_r($r, true)];
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
        if (!empty($post['tags'])) $params['message'] .= PHP_EOL.$post['tags'];
        if ($post['url'] != '')
        {
            if ($post['image'] > 0)
            {
                $params['message']  .= PHP_EOL.$post['url'];
            } else {
                $params['link'] = $post['url'];
            }
        }

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
                $this->errors[] = ['type' => 'fb', 'message' => 'failed posting', 'reply' => print_r($response, true), 'error' => print_r($e, true)];
            }
        } else {
            $this->errors[] = ['type' => 'fb', 'message' => 'Couldn\'t initialize session', 'reply' => print_r($session, true)];
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

        if(!Normalizer::isNormalized($params['status'],Normalizer::FORM_C)){
            $params['status'] = Normalizer::normalize($params['status'],Normalizer::FORM_C);
        }
//        if ($post['url'] != '') $params['status'] .= ($params['status'] == '' ? '' : ' ').$post['url'];
        if ($post['image'] > 0) $params['media[]'] = $post['image_file'];

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
            $this->errors[] = ['type' => 'tw', 'message' => 'failed posting', 'reply' => print_r($reply, true)];
        }
    }

    public function updateTWConfiguration()
    {
        require_once(LIB_DIR.'codebird-php-develop/src/codebird.php');

        \Codebird\Codebird::setConsumerKey(TW_API_KEY, TW_API_SECRET);
        $cb = \Codebird\Codebird::getInstance();
        $cb->setToken(TW_ACCESS_TOKEN, TW_ACCESS_TOKEN_SECRET);

        $reply = $cb->help_configuration([]);
        if ($reply->httpstatus == 200)
        {
            $this->dsp->db->Execute("update `social_tokens` set `value` = ? where `type` = 'tw_characters_per_media'", $reply->characters_reserved_per_media);
            $this->dsp->db->Execute("update `social_tokens` set `value` = ? where `type` = 'tw_short_url_length'", $reply->short_url_length);
            $this->dsp->db->Execute("update `social_tokens` set `value` = ? where `type` = 'tw_short_url_length_https'", $reply->short_url_length_https);

            Redirect('/admin/?op=posts&act=tokens&ok');
        } else {
            echo 'Failed.<br /><pre>';
            print_r($reply);
            echo '</pre>';
            exit;
        }
    }

    public function twitterTest()
    {
        // require codebird
        require_once(LIB_DIR.'codebird-php-develop/src/codebird.php');

        \Codebird\Codebird::setConsumerKey(TW_API_KEY, TW_API_SECRET);
        $cb = \Codebird\Codebird::getInstance();
        $cb->setToken(TW_ACCESS_TOKEN, TW_ACCESS_TOKEN_SECRET);

        $params = [];
        $params['status'] = '31 октября (19 октября) 1811 года открылся Императорский Царскосельский лицей. 31 октября (19 октября) 1811 года открылся Императорский Цафф';
//        $params['status'] = 'Chinese ships and aircraft warned and tracked a U.S. Navy warship Tuesday as it came close to reefs claimed by China in contested waters ina';
        $reply = $cb->statuses_update($params);
        print_r($reply); exit;
    }

    public function prepareAndPublish($posts)
    {
        $content = [];
        foreach ($posts as &$post)
        {
            if ($post['image'] > 0) $content['image'] = $post['image'];
            else if (!empty($content['image'])) $post['image'] = $content['image'];

            if ($post['text'] != '')
            {
                if (empty($content['text'])) $content['text'] = $post['text'];
            } else if (!empty($content['text'])) {
                $post['text'] = $content['text'];
            }

            if ($post['url'] != '') $content['url'] = $post['url'];
            else if (!empty($content['url'])) $post['url'] = $content['url'];

            if ($post['tags'] != '') $content['tags'] = $post['tags'];
            else if (!empty($content['tags'])) $post['tags'] = $content['tags'];

            if ($post['image'] > 0)
            {
                $post['image_url'] = SITE.IMAGE_FOLDER.$this->dsp->i->getOriginal($post['image']);
                $post['image_file'] = IMAGE_DIR.$this->dsp->i->getOriginal($post['image']);
            }

            $this->dsp->socials->post($post);
        }
    }

    public function postOK($post)
    {
        if ($post['image'] == 0 && $post['text'] == '' && $post['url'] == '') return;

        $ok = new Social_APIClient_Odnoklassniki(
            array(
                'client_id' => OK_APP_ID,
                'application_key' => OK_PUBLIC_KEY,
                'client_secret' => OK_SECRET_KEY
            )
        );

        $ok->setToken(OK_ACCESS_TOKEN);

        $attachment = ['media' => []];
        if ($post['text'] != '')
        {
            $attachment['media'][] = ['type' => 'text', 'text' => $post['text']];
        }

        if ($post['url'] != '')
        {
            $attachment['media'][] = ['type' => 'link', 'url' => $post['url']];
        }

        if ($post['image'] > 0)
        {
            $r = $ok->api("photosV2.getUploadUrl", ['gid' => OK_ACCOUNT_ID]);
            if (!empty($r['upload_url'])) $upload_url = $r['upload_url'];

            if ($upload_url)
            {
                $photo_id = $r['photo_ids'][0];

                $ch = curl_init($upload_url);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, array(
                    'photo' => '@' . $post['image_file']
                ));

                if (($upload = curl_exec($ch)) === false) {
                    throw new Exception(curl_error($ch));
                    $this->errors[] = ['type' => 'ok', 'message' => 'photo upload failed'];
                }

                curl_close($ch);
                $upload = json_decode($upload);
                if (!empty($upload->photos->$photo_id->token))
                    $attachment['media'][] = ['type' => 'photo', 'list' => [['id' => strval($upload->photos->$photo_id->token)]]];
            } else {
                $this->errors[] = ['type' => 'ok', 'message' => 'photo upload failed', 'reply' => print_r($r, true)];
            }

        }

        $attachment = json_encode($attachment);

        $post_id = $ok->api('mediatopic.post', array('type' => 'GROUP_THEME', 'gid' => OK_ACCOUNT_ID, 'attachment' => $attachment));
        if ($post_id > 0)
        {
            $sql = "update posts set published = 1, social_id = ? where `date` = ? and `type` = ?";
            $this->dsp->db->Execute($sql, $post_id, $post['date'], $post['type']);
            echo 'OKs success: post id '.$post_id.PHP_EOL;
        } else {
            echo 'OKs failed'.PHP_EOL;
            $this->errors[] = ['type' => 'ok', 'message' => 'failed posting'];
        }
    }

}