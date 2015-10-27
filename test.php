<?php
ob_start();

$time = time();

echo 'Date: '.date("d.m.Y H:i", $time).PHP_EOL;

//$sql = "select * from posts where `date` >= ? and `date` <= ? and `published` = 0 and `active` = 1 and `blank` = 0 group by `type`";
//$rows = $dsp->db->Select($sql, $time - 60*2, $time + 60*2);
$sql = "select * from posts where `date` = 1446274800 group by `type`";
$rows = $dsp->db->Select($sql);
$i = 0; $sort_types = array(); foreach ($soc_types as $st => $title) $sort_types[$st] = $i++;

$posts = array();
foreach ($rows as $row)
{
    $posts[$sort_types[$row['type']]] = $row;
}
ksort($posts);

$dsp->socials->prepareAndPublish($posts);

echo 'done.'.PHP_EOL.PHP_EOL;

$output = ob_get_contents();

if (count($dsp->socials->errors) > 0)
{
    $msg = '';
    foreach ($dsp->socials->errors as $e)
    {
        $msg .= print_r($e, true);
    }

    $msg .= PHP_EOL.PHP_EOL."Output: ".PHP_EOL.$output;
    mail('kerranswell@gmail.com', 'Daily History Cron Errors', $msg);
}

file_put_contents(ROOT_DIR.'/logs/spost.log', $output, FILE_APPEND);