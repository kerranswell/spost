<?php

if ($_POST['do_save'])
{
    $op = $_POST['op'];
    $class = $op."_admin";
    if (!empty($dsp->$class->__tablename__)) $dsp->$class->updateItem();
}