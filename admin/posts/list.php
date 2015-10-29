<?php

$filter = array();
$filter['date_from'] = empty($_SESSION['datefilter']['date_from']) ? strtotime(date("d.m.Y", time())) : strtotime($_SESSION['datefilter']['date_from']);
$filter['date_to'] = empty($_SESSION['datefilter']['date_to']) ? strtotime(date("d.m.Y", time()+ 60*60*24*7))  : strtotime($_SESSION['datefilter']['date_to']);

$sql = "select * from `posts` where `date` <= ? and `date` >= ? order by `date` desc";
$rows = $dsp->db->Select($sql, $filter['date_to'], $filter['date_from']);

$posts = array();
$i = 0; $sort_types = array(); foreach ($soc_types as $st => $title) $sort_types[$st] = $i++;
foreach ($rows as $row)
{
    $posts[$row['date']][$sort_types[$row['type']]] = $row;
}

$b = $dsp->_BuilderPatterns->create_block('posts_list', 'posts_list', 'center');
$b_list = $dsp->_Builder->addNode($dsp->_Builder->createNode('list', array()), $b);
$dsp->_Builder->addNode($dsp->_Builder->createNode('date_from', array(), date("d.m.Y", $filter['date_from'])), $b);
$dsp->_Builder->addNode($dsp->_Builder->createNode('date_to', array(), date("d.m.Y", $filter['date_to'])), $b);

foreach ($posts as $date => $post)
{
    ksort($post);
    $b_item = $dsp->_Builder->addNode($dsp->_Builder->createNode('item', array()), $b_list);
    $content = array('types' => array());
    $blank = 0;
    foreach ($post as $p)
    {
        if (!$p['active']) continue;

        if ($p['text'] != '' && !isset($content['text'])) $content['text'] = $p['text'];
        if ($p['image'] != 0 && !isset($content['image'])) $content['image'] = $p['image'];
        $content['types'][] = $soc_types[$p['type']];
        if ($p['blank']) $blank = 1;
    }
    if (count($content['types']) > 0) $content['types'] = implode(', ', $content['types']);

    $content['date'] = $date;
    $content['blank'] = $blank;
    $content['date_title'] = date('d.m.Y H:i', $date);
    if (!isset($content['image'])) $content['image'] = 0;
    if ($content['image'] > 0)
    {
        $content['image_th'] = $dsp->i->default_path.$dsp->i->resize($content['image'], TH_IMAGE_EDIT_ADMIN);
        $content['image'] = SITE.IMAGE_FOLDER.$dsp->i->getOriginal($content['image']);
    }

    foreach ($content as $f => $v)
    {
        $dsp->_Builder->addNode($dsp->_Builder->createNode($f, array(), $v), $b_item);
    }
}





