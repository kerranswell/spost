<?php
//$_core_mode_ = 'wrapper';
require_once(dirname(__FILE__) . "/../core/core.php");
include_once(dirname(__FILE__) . '/const.php');

Un_magic_quotes();
$dsp->errors;

if (!empty($_POST)) {
    include_once(ADMIN_POST_DIR . 'post.php');
}

parse_str($_SERVER['QUERY_STRING'], $pices);
unset($pices['p_']);
$query_string = http_build_query($pices);

if (!empty($query_string)) 
    $query_string = '?' . $query_string;

$nodes = explode('/', trim($_REQUEST['p_'], '/'));

if (!empty($_REQUEST['t']) && is_file(ADMIN_DIR.$_REQUEST['t'].'.php'))
{
    $path = ADMIN_DIR.$_REQUEST['t'].'.php';
    $_REQUEST['t'] = '';
    require($path);
    exit;
}
else if (!empty($nodes[0])) {
    if (is_file(ADMIN_DIR . $nodes[0] . "/rewrite.php")) {
        $path = ADMIN_DIR . $nodes[0] . "/rewrite.php";
        array_shift($nodes);
        require($path);
    } elseif (is_file(ADMIN_DIR . "/" . $nodes[0] . ".php")) {
        $path = ADMIN_DIR . "/" . $nodes[0] . ".php";
        array_shift($nodes);
        require($path);
    } elseif (isset($nodes[1]) && is_file(ADMIN_DIR . "/" . $nodes[0] . "/" . $nodes[1] . ".php")) {
        $path = ADMIN_DIR . "/" . $nodes[0] . "/" . $nodes[1] . ".php";
        array_shift($nodes);
        array_shift($nodes);
        require($path);
    } elseif (is_file(ADMIN_DIR . $nodes[0] . "/index.php")) {
        $path = ADMIN_DIR . $nodes[0] . "/index.php";
        array_shift($nodes);
        require($path);
    } else {
//        require(ADMIN_DIR . "index.php");
        Redirect(SITE);
    }
    exit();
}

require (ADMIN_DIR . 'index.php');
//Redirect('/'); 404
?>
