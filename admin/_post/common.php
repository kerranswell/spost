<?php

$act = $_POST['act'];

switch ($act)
{
    case 'tree_node_list_sort' :

        $ids = @explode(",", $_POST['ids']);
        $table = trim($_POST['table']);

        $index_start = (int)$_POST['index_start'];
        if (is_array($ids) && count($ids) > 0)
        {
            foreach($ids as $i => $id)
            {
                $dsp->db->Execute("update `".mysql_real_escape_string($table)."` set `pos` = ? where `id` = ?", $i + $index_start, $id);
            }
        }

        break;
}