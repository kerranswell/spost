<?php

class posts_admin extends record {

    public $__tablename__  = 'posts';
    public $act = 'list';

    protected function init()
    {
        $this->act = empty($_REQUEST['act']) ? 'list' : $_REQUEST['act'];
    }

    public function getList()
    {
        $pid = $this->pid;

        $sql = "select `id`, `pid`, `title`, `pos`, `status` from `pages` p
                where p.`pid` = ? order by `pos` asc".'';

        $rows = $this->dsp->db->Select($sql, $pid);

        return $rows;
    }

    public function GetPosts($date, $fields = '*')
    {
        $sql = "Select ".$fields." from `posts` where `date` = ? group by `type`";
        $rows = $this->dsp->db->Select($sql, $date);
        $posts = array();
        foreach ($rows as $row) $posts[$row['type']] = $row;
        return $posts;
    }

    public function updateItem()
    {
        global $soc_types;

        $date = strtotime($_POST['date']);
        $blank = empty($_POST['blank']) ? 0 : 1;
        $req_date = $_GET['date'];

        $posts = array();
        if ($req_date > 0)
        {
            $posts = $this->GetPosts($req_date, 'image, type, published');
        }

        $save = $_POST['record'];
        foreach ($soc_types as $st => $t)
        {
            $save[$st]['text'] = trim($save[$st]['text']);
            $save[$st]['date'] = $date;
        }

        # delete images
        foreach ($soc_types as $st => $t)
        if ((isset($_POST[$st.'_image_delete']) || !empty($_FILES['record']['tmp_name'][$st]['image'])) && $posts[$st]['image'] > 0)
        {
            $this->dsp->i->clearByIDX($posts[$st]['image']);
            $save[$st]['image'] = 0;
        }

        foreach ($soc_types as $st => $t)
        if (!empty($_FILES['record']['tmp_name'][$st]['image']))
        {
            $f = $this->dsp->i->getFileFromArray2($_FILES['record'], 'image', $st);
            list($save[$st]['image'],) = $this->dsp->i->putToPlace($f);
        }

        $this->errors = $this->checkUpdate($save);

        if (count($this->errors) > 0)
        {
            return;
        }

        if ($_POST['publish'])
        {
            foreach ($soc_types as $st => $t)
            {
                if (!isset($save[$st]['image'])) $save[$st]['image'] = $posts[$st]['image'];
                $save[$st]['published'] = $posts[$st]['published'];
                $save[$st]['type'] = $st;
                $save[$st]['date'] = $req_date;
            }

            $date = time();

            ob_start();
            $ps = array();
            foreach ($save as $s) if ($s['active']) $ps[] = $s;
            $this->dsp->socials->prepareAndPublish($ps);
            if (count($this->dsp->socials->errors) > 0)
            {
                echo '<pre>';
                print_r($this->dsp->socials->errors);
                echo '</pre>'; exit;
            }
        }

        foreach ($soc_types as $st => $t)
        {
            if ($req_date > 0)
            {
                if (!isset($save[$st]['image'])) $save[$st]['image'] = $posts[$st]['image'];
                $sql = "update `posts` set `text` = ?, `image` = ?, `active` = ?, `date` = ?, `url` = ?, `blank` = ?, `tags` = ? where `date` = ? and `type` = ?";
                $this->dsp->db->Execute($sql, $save[$st]['text'], $save[$st]['image'], !empty($save[$st]['active']) ? 1 : 0, $date, $save[$st]['url'], $blank, $save[$st]['tags'], $req_date, $st);
            } else {
                $sql = "insert into `posts` (`type`, `text`, `image`, `active`, `date`, `url`, `blank`, `tags`) values (?, ?, ?, ?, ?, ?, ?, ?)".'';
                $this->dsp->db->Execute($sql, $st, $save[$st]['text'], $save[$st]['image'], !empty($save[$st]['active']) ? 1 : 0, $date, $save[$st]['url'], $blank, $save[$st]['tags']);
            }
        }

        Redirect('/admin/?op=posts&act=edit&date='.$date);
    }

    protected function checkUpdate($item)
    {
        $errors = array();

        return $errors;
    }

}

?>