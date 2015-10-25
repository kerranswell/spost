<?php

class admin_menu extends Record {

    public function getAdminMenu()
    {
        $menu = array();

        $menu[] = array('title' => 'Посты', 'op' => 'posts', 'link' => '/admin/?op=posts');
        $menu[] = array('title' => 'Токены', 'op' => 'posts', 'link' => '/admin/?op=posts&act=tokens');

        return $menu;
    }

} // class admin_menu

?>