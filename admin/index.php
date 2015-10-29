<?php
    define('WDEBUG', true);
    define('DS', DIRECTORY_SEPARATOR);

    set_time_limit(0);
    un_magic_quotes();
    // JS
    $bp = $dsp->_BuilderPatterns;
    $root = $bp->root();
    $dom_head = $bp->append_simple_node($root, 'head');
    $dom_js = $bp->append_simple_node($dom_head, 'js');

    // Append javascripts
//    $bp->append_simple_node($dom_js, 'item', 'admin/static/js/custom/list_common');

    // Append notify
    $notify = $dsp->authadmin->RetriveParam("message");
    $notify = empty($notify) ? ( Param('e-notify') ? base64_decode(Param('e-notify')):'' ) : $notify;
    if (!empty($notify)) {
        $param = array();
        if ( Param('e-notify') ) $param = array('mode' => 'error');
        $bp->append_simple_node($dom_head, 'notify', $notify, $param);
    }

    $dsp->authadmin->Init();

    if (!$dsp->authadmin->IsLogged()) {
        $dsp->_Builder->addNode($dsp->_Builder->createNode('block', array('align' => 'center', 'id' => 'login', 'name' => 'login')));
        $dsp->_Builder->Transform('admin' . DS . 'login.xsl');
        exit();
    }
    
    $template = 'main';

    // Navigation Block
    $dom_block_nav = $dsp->_BuilderPatterns->create_block('nav', 'nav', 'left');
    $dsp->_Builder->addArray($dsp->admin_menu->getAdminMenu(), '', array(), $dom_block_nav, false);

    $op = $_REQUEST['op'];

    $common = array('op' => $op, '_get' => $_GET, 'socials' => $dsp->socials->public_settings);
    $b_common = $dsp->_Builder->addNode($dsp->_Builder->createNode('common', array()));
    $dsp->_Builder->addArray($common, '', array(), $b_common, false);

//    $dsp->_Builder->addNode($dsp->_Builder->createNode('block', array('align' => 'center', 'id' => 'main_menu', 'name' => 'main_menu')));
    if ($op != '') {
        if (is_file(ADMIN_DIR . $op . "/rewrite.php")) {
            $path = ADMIN_DIR . $op . "/rewrite.php";
            require($path);
        } elseif (is_file(ADMIN_DIR . "/" . $op . ".php")) {
            $path = ADMIN_DIR . "/" . $op . ".php";
            require($path);
        }

//        exit();
    }

    $dsp->_Builder->Transform('admin/' . $template . '.xsl');

?>