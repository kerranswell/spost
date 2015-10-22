<?php

class pages_admin extends record {

    public $__tablename__  = 'pages';
    public $pid = 0;
    public $act = 'list';

    protected $table_structure = array(
        'id' => array('type' => 'int', 'params' => array(
            'edit' => array('showtype' => 'none'),
            'list' => array('showtype' => 'none'),
        ), 'title' => 'ID'),
        'pid' => array('type' => 'int', 'params' => array(
            'edit' => array('showtype' => 'none'),
            'list' => array('showtype' => 'none'),
        ), 'title' => 'PID'),
        'title' => array('type' => 'string', 'params' => array(
            'edit' => array('showtype' => 'string'),
            'list' => array('showtype' => 'link_children'),
        ), 'title' => 'Заголовок'),
        'translit' => array('type' => 'string', 'params' => array(
            'edit' => array('showtype' => 'string'),
            'list' => array('showtype' => 'none'),
        ), 'title' => 'Translit'),
        'text' => array('type' => 'text', 'params' => array(
            'edit' => array('showtype' => 'editor'),
            'list' => array('showtype' => 'none'),
        ), 'title' => 'Текст'),
        'pos' => array('type' => 'int', 'params' => array(
            'edit' => array('showtype' => 'none'),
            'list' => array('showtype' => 'none'),
        ), 'title' => 'Позиция'),
        'bg_image' => array('type' => 'int', 'params' => array(
            'edit' => array('showtype' => 'image'),
            'list' => array('showtype' => 'none'),
        ), 'title' => 'Фон'),
        'status' => array('type' => 'int', 'params' => array(
            'edit' => array('showtype' => 'checkbox'),
            'list' => array('showtype' => 'none'),
        ), 'title' => 'Статус'),
    );

    protected function init()
    {
        $this->pid = empty($_REQUEST['pid']) ? 0 : (int)$_REQUEST['pid'];
        if (empty($_SESSION[$this->__tablename__]['pid'])) $_SESSION[$this->__tablename__]['pid'] = $this->pid;

        if (isset($_REQUEST['pid'])) $_SESSION[$this->__tablename__]['pid'] = $this->pid;
        else $this->pid = $_SESSION[$this->__tablename__]['pid'];

        if ($this->pid > 0)
        {
            $this->table_structure['title']['params']['list']['showtype'] = 'label';
        }

        $this->act = empty($_REQUEST['act']) ? 'list' : $_REQUEST['act'];
    }

    public function getPath($pid = -1)
    {
        if ($pid < 0) $pid = $this->pid;

        $rows = array();

        if ($pid > 0)
        {
            $row = $this->GetItem($pid, '`id`, `pid`, `title`');
            $rows[] = $row;
            $rows = array_merge($this->getPath($row['pid']), $rows);
        } else {
            $rows = array_merge(array(array('id' => 0, 'pid' => -1, 'title' => 'Корень')), $rows);
        }

        return $rows;
    }

    public function getList()
    {
        $pid = $this->pid;

        $sql = "select `id`, `pid`, `title`, `pos`, `status` from `pages` p
                where p.`pid` = ? order by `pos` asc".'';

        $rows = $this->dsp->db->Select($sql, $pid);

        return $rows;
    }

    public function getParams($place)
    {
        $ar = array();
        foreach ($this->table_structure as $f => $d)
        {
            $ar[$f] = $d['params'][$place];
            $ar[$f]['title'] = $d['title'];
        }

        return $ar;
    }

    public function blankItem()
    {
        $ar = array();
        foreach ($this->table_structure as $f => $d)
        {
            switch ($d['type'])
            {
                case 'int' : $ar[$f] = 0;
                default : $ar[$f] = '';
            }
        }

        return $ar;
    }

    public function updateItem()
    {
        $id = $_REQUEST['id'];
        if ($id > 0)
        {
            $item = $this->GetItem($id, 'bg_image');
        }

        $save = $_POST['record'];
        $save['id'] = $id;
        $pid = $this->pid;
        if (trim($save['translit']) == '') $save['translit'] = translit($save['title']);

        # delete background image
        if ((isset($_POST['bg_image_delete']) || !empty($_FILES['record']['tmp_name']['bg_image'])) && $item['bg_image'] > 0)
        {
//            $this->dsp->i->clearTHByURL($this->dsp->i->resize($item['bg_image'], TH_BG_IMAGE_ADMIN));
            $this->dsp->i->clearByIDX($item['bg_image']);
            $save['bg_image'] = 0;
        }

        if (!empty($_FILES['record']['tmp_name']['bg_image']))
        {
            $f = $this->dsp->i->getFileFromArray($_FILES['record'], 'bg_image');
            list($save['bg_image'],) = $this->dsp->i->putToPlace($f);
        }

        $this->errors = $this->checkUpdate($save);

        if (count($this->errors) > 0)
        {
            return;
        }

        if ($id > 0)
        {
            if (!isset($save['bg_image'])) $save['bg_image'] = $item['bg_image'];
            $sql = "update `".$this->__tablename__."` set
                `title` = ?,
                `translit` = ?,
                `text` = ?,
                `status` = ?,
                `bg_image` = ?
                where `id` = ?
            ".'';
            $this->dsp->db->Execute($sql, $save['title'], $save['translit'], $save['text'], !empty($save['status']) ? 1 : 0, $save['bg_image'], $id);
            Redirect('/admin/?op=pages&act=edit&id='.$id);
        } else {
            $pos = $this->dsp->db->SelectValue("select `pos` from `pages` where `pid` = ? order by `pos` desc limit 1".'', $pid);
            if (!$pos) $pos = 0; else $pos++;
            $sql = "insert into `pages` (`id`, `pid`, `title`, `translit`, `text`, `status`, `pos`) values (0, ?, ?, ?, ?, ?, ?, ?)".'';
            $this->dsp->db->Execute($sql, $pid, $save['title'], $save['translit'], $save['text'], !empty($save['status']) ? 1 : 0, $pos, $save['bg_image']);

            Redirect('/admin/?op=pages&act=edit&id='.$this->dsp->db->LastInsertId());
        }
    }

    protected function checkUpdate($item)
    {
        $errors = array();
        if (trim($item['title']) == '') $errors['title'] = 'Необходимо заполнить это поле';
        $sql = "select count(*) from `".$this->__tablename__."` where `translit` = ? and `id` != ?".'';
        $v = $this->dsp->db->SelectValue($sql, $item['translit'], $item['id']);
        if ($v > 0) $errors['translit'] = 'Такой url уже существует';

        return $errors;
    }

}

?>