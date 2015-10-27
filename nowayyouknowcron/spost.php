<?php
ob_start();

$time = time();

echo 'Date: '.date("d.m.Y H:i", $time).PHP_EOL;

$sql = "select * from posts where `date` >= ? and `date` <= ? and `published` = 0 and `active` = 1 group by `type`";
$rows = $dsp->db->Select($sql, $time - 60*2, $time + 60*2);
//$sql = "select * from posts where `date` = 1445868000 group by `type`";
//$rows = $dsp->db->Select($sql);
$i = 0; $sort_types = array(); foreach ($soc_types as $st => $title) $sort_types[$st] = $i++;

$posts = array();
foreach ($rows as $row)
{
    $posts[$sort_types[$row['type']]] = $row;
}
ksort($posts);

$content = [];
foreach ($posts as &$post)
{
    if ($post['text'] != '')
    {
        if (empty($content['text'])) $content['text'] = $post['text'];
    } else if (!empty($content['text'])) {
        $post['text'] = $content['text'];
        if ($post['type'] == 'tw')
        {
            $post['text'] = mb_substr($post['text'], 0, 137, 'utf-8').'...';
        }
    }

    if ($post['image'] > 0) $content['image'] = $post['image'];
    else if (!empty($content['image'])) $post['image'] = $content['image'];

    if ($post['url'] > 0) $content['url'] = $post['url'];
    else if (!empty($content['url'])) $post['url'] = $content['url'];

    if ($post['image'] > 0)
    {
        $post['image_url'] = SITE.IMAGE_FOLDER.$dsp->i->getOriginal($post['image']);
        $post['image_file'] = IMAGE_DIR.$dsp->i->getOriginal($post['image']);
    }

    $dsp->socials->post($post);
}

echo 'done.'.PHP_EOL.PHP_EOL;

file_put_contents(ROOT_DIR.'/logs/spost.log', ob_get_contents(), FILE_APPEND);