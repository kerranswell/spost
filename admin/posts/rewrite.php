<?php

$act = empty($_REQUEST['act']) ? 'list' : $_REQUEST['act'];

$f = ADMIN_DIR . "/" . $op . "/".$act.".php";
if (is_file($f)) require ($f);

