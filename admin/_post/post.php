<?php

    if (!$dsp->authadmin->IsLogged() && $_POST['opcode'] != 'login') exit;

    if (!empty($_POST['opcode'])) {
//        print ADMIN_POST_DIR . $_POST['opcode'] . '.php';
        if (!empty($_POST['tablename']) && is_file(ADMIN_POST_DIR . $_POST['tablename'] . '_' . $_POST['opcode'] . '.php')) {
            require(ADMIN_POST_DIR . $_POST['tablename'] . '_' . $_POST['opcode'] . '.php');
        } else if (is_file(ADMIN_POST_DIR . $_POST['opcode'] . '.php')) {
            require(ADMIN_POST_DIR . $_POST['opcode'] . '.php');
        } else {
            
        } // if
    } // if

    if (isset($_POST['no_redirect']) &&  $_POST['no_redirect'])
    {

    } else {
        Redirect($_SERVER['REQUEST_URI']);
    }
?>