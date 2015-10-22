<?php

class admin_menu extends Record {

    public function getAdminMenu()
    {
        $menu = array();

        $menu[] = array('title' => 'Страницы', 'op' => 'pages', 'link' => '/admin/?op=pages&pid=0');

        return $menu;
    }

} // class admin_menu

?>