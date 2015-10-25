<?php

//HTTP consts
if (!empty($_SERVER['HTTP_HOST'])) {
	define("HOST", $_SERVER['HTTP_HOST']);
} else define('CRON', 1);
if (!defined('HOST')) define('HOST', 'dailyhistory.ru');


define("HTTP_REL_PATH", '');
define("SITE", 'http://' . HOST . HTTP_REL_PATH);
define("AJAX_PATH", HOST . "/ajax/");
define("IMAGE_PATH", HOST . "/img/");

if (empty($_SERVER['DOCUMENT_ROOT'])) {
    $DOCUMENT_ROOT = dirname(dirname(__FILE__));
} else {
    $DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
}

// SERVER consts
define("SERVER_PROTOCOL", "http://");
define("SERVER_REL_PATH", '');
define("ROOT_DIR", $DOCUMENT_ROOT . SERVER_REL_PATH . "/");
define("IMAGE_FOLDER", "/images/");
define("IMAGE_DIR", $DOCUMENT_ROOT . IMAGE_FOLDER);
define("SHOWS_DIR", "shows/") ;
define("TPL_DIR", ROOT_DIR . "templates/");
define("CLASS_DIR", ROOT_DIR . "classes/");
define("LIB_DIR", ROOT_DIR . "lib/");
define("POST_DIR", ROOT_DIR . "_post/");
define("TABLE_DIR", CLASS_DIR . "tables/");
define("LOGS_DIR", ROOT_DIR . "logs/");
define("CFG_DIR", ROOT_DIR . "core/");
define("MAIL_TPL_DIR", ROOT_DIR . "mail_templates/");

define("ADMIN_DIR", ROOT_DIR . "admin/");
define("ADMIN_TABLE_DIR", CLASS_DIR . "admin_tables/");
define("ADMIN_TPL_DIR", ADMIN_DIR . "templates/");
define("ADMIN_POST_DIR", ADMIN_DIR . '_post/');

define("LOG_ACTION", true);


$months         = array('', 'январь', 'февраль', 'март', 'апрель', 'май', 'июнь', 'июль', 'август', 'сентябрь', 'октябрь', 'ноябрь', 'декабрь');
$months_rod_pad = array('', 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря');
$months_short   = array('', 'янв', 'фев', 'марта', 'апр', 'мая', 'июн', 'июл', 'авг', 'сен', 'окт', 'ноя', 'дек');

define('AUTHOR_TYPE_ARTICLES', 1);
define('AUTHOR_TYPE_POSTS', 2);
define('AUTHOR_TYPE_ALL', 3);

// diagnoses logics ids for tests service
define('TESTS_LOGIC_SUMM_SCORES', 5);
define('TESTS_LOGIC_GROUP_ANSWERS', 4);


define('ARTICLE_TITLE_DELIMITER', "<br>"); // this value must be preg_match pattern valid string! (see admin/art_editor.php)

/* SOCIAL CODES */
define('VK_APP_ID', '5117128');
define('VK_MY_ID', '3403879');
define('VK_APP_SECRET', 'UaZ0bqrz69ZlEyBPlt2A');
define('VK_ACCESS_TOKEN', '1d8bb4d2531f9884f417e99c0c37bb243181da629c67098023f08aafcbdc9a0e73ed9dd2200e8d4e549b9');
define('VK_ACCESS_SECRET', '39cdffa884202998f0');
define('VK_ACCOUNT_ID', '105226635');
define('VK_API_VERSION', '5.37');

define('FB_APP_ID', '1646293928976787');
define('FB_APP_SECRET', '1cc0f285ed8af6c5c31ce48ab3c60916');
define('FB_ACCOUNT_ID', '692946360842537');

define('TW_API_KEY', 'XxcXEKNoXWkKCltwSinb7KLZ6');
define('TW_API_SECRET', 'SETUy2quzELpTH2k48K5EezfPjFOlIzw04WOtvXWHaqfGdXUCn');
define('TW_ACCESS_TOKEN', '4040835677-UyOPdrS4zxBAWtVZWrws56BqD4s9lts6KQ4TWND');
define('TW_ACCESS_TOKEN_SECRET', 'cdmHkElH8kn0sjzMl9UUeKE1LDxhcBBP9mTl8aJlMVpTd');
define('TW_OWNER_ID', '4040835677');

/* THUMBNAILS CODES */
define('TH_BG_IMAGE_ADMIN', 1);
define('TH_IMAGE_EDIT_ADMIN', 2);

/* DB TABLES */
//define('TABLE_PREFIX', 'spost_');
define('TABLE_PREFIX', '');
define('DB_REPLACE_TABLES', 0);

$tables = array();
foreach (
    array(
             'blocks',
             'images',
             'pages',
             'placement',
             'services',
             'usersadmin',
         )
    as $table => $t
) $tables[$t] = TABLE_PREFIX.$t;

$soc_types = array('vk' => 'VK', 'fb' => 'Facebook', 'tw' => 'Twitter', 'ok' => 'Одноклассники');

$post_times = array(
    '10:00', '17:00', '21:00',
);


define('DB_LOG', 0);

?>