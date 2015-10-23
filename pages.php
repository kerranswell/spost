<?php

require_once(LIB_DIR.'Vkontakte.php');
echo SITE; exit;
$pic = IMAGE_DIR.'638/472/638@472@562a266641a4f6bf170782239c7d970f.jpg';
$text == 'Playing music so fun!';

$vk = new Vk(array(
    'client_id' => VK_APP_ID,
    'client_secret' => VK_SECRET,
    'redirect_uri' => SITE,
));

//$vkAPI = new Vkontakte(array('access_token' => VK_ACCESS_TOKEN));
//$vkAPI->postToPublic(VK_ACCOUNT_ID, $text, $pic, array('fun', 'checkcheck'));
