<?php

$date_format = "d.m.Y H:i";
$date = $_REQUEST['date'];

$posts = array();
$blank = 0;

if ($date > 0)
{
    $sql = "select * from `posts` where `date` = ? group by `type`";
    $rows = $dsp->db->Select($sql, $date);
    $i = 0; $sort_types = array(); foreach ($soc_types as $st => $title) $sort_types[$st] = $i++;
    foreach ($rows as $row) {
        $row['soc_type_title'] = $soc_types[$row['type']];
        if ($row['image'] > 0)
        {
            $row['image_th'] = $dsp->i->default_path.$dsp->i->resize($row['image'], TH_IMAGE_EDIT_ADMIN);
            $row['image'] = SITE.IMAGE_FOLDER.$dsp->i->getOriginal($row['image']);
        }
        if ($row['blank']) $blank = 1;
        $posts[$sort_types[$row['type']]] = $row;
    }
    ksort($posts);

} else {

    $sql = "Select max(`date`) from `posts` where `blank` = 0";
    $max = $dsp->db->SelectValue($sql);
    $date = 0;
    if ($max > 0)
    {
        $max_date = date('d.m.Y', $max);
        foreach ($post_times as $time)
        {
            $t = $max_date.' '.$time;
            if (strtotime($t) > $max)
            {
                $date = strtotime($t);
                break;
            }
        }

        if (!$date) {
            $date = strtotime(date("d.m.Y", $max + 60*60*24)." ".$post_times[0]);
        }
    }
    if (!$date) $date = strtotime(date("d.m.Y", time() + 60*60*24)." ".$post_times[0]);

    foreach ($soc_types as $st => $title)
    {
        $posts[] = array(
            'id' => 0,
            'type' => $st,
            'text' => '',
            'image' => '0',
            'active' => 1,
            'date' => date($date_format, $date),
            'url' => '',
            'tags' => '',
            'blank' => 0,
            'published' => 0,
            'soc_type_title' => $title
        );
    }
}

$b = $dsp->_BuilderPatterns->create_block('posts_edit', 'posts_edit', 'center');

$b_date = $dsp->_Builder->addNode($dsp->_Builder->createNode('date', array(), date($date_format, $date)), $b);
$b_blank = $dsp->_Builder->addNode($dsp->_Builder->createNode('blank', array(), $blank), $b);

$b_posts = $dsp->_Builder->addNode($dsp->_Builder->createNode('posts', array()), $b);

foreach ($posts as $post)
{
    $b_item = $dsp->_Builder->addNode($dsp->_Builder->createNode('item', array()), $b_posts);
    foreach ($post as $f => $v)
    {
        if (isset($_POST['record'][$post['type']][$f])) $v = $_POST['record'][$post['type']][$f];
        $dsp->_Builder->addNode($dsp->_Builder->createNode($f, array(), $v), $b_item);
    }
}



