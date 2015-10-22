<?php

//HTTP consts
if (!empty($_SERVER['HTTP_HOST'])) {
	define("HOST", $_SERVER['HTTP_HOST']);
}

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
define("IMAGE_FOLDER", "/img/");
define("IMAGE_DIR", $DOCUMENT_ROOT . IMAGE_FOLDER);
define("SHOWS_DIR", "shows/") ;
define("TPL_DIR", ROOT_DIR . "templates/");
define("CLASS_DIR", ROOT_DIR . "classes/");
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

define('STREETSTYLE_CAT_ID', 45);

// diagnoses logics ids for tests service
define('TESTS_LOGIC_SUMM_SCORES', 5);
define('TESTS_LOGIC_GROUP_ANSWERS', 4);


define('ARTICLE_TITLE_DELIMITER', "<br>"); // this value must be preg_match pattern valid string! (see admin/art_editor.php)

if (!defined('HOST')) define('HOST', 'tsi.elle.www.hsmdev.ru');

/* THUMBNAILS CODES */
define('TH_BG_IMAGE_ADMIN', 1);

?>