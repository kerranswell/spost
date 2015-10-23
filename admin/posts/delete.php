<?php

$date = $_REQUEST['date'];

$posts = array();

if ($date > 0)
{
    $sql = "select * from `posts` where `date` = ?";
    $rows = $dsp->db->Select($sql, $date);
    foreach ($rows as $row) {
        if ($row['image'] > 0) $dsp->i->clearByIDX($row['image']);
    }

    $sql = "delete from `posts` where `date` = ?";
    $dsp->db->Execute($sql, $date);
}

Redirect('/admin/?op=posts');


