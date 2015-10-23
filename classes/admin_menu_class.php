<?php

class admin_menu extends Record {

    public function getAdminMenu()
    {
        $menu = array();

        $menu[] = array('title' => 'Посты', 'op' => 'posts', 'link' => '/admin/?op=posts');

        return $menu;
    }

} // class admin_menu

?>