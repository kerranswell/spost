<?php

session_start();

if ($_POST['go'])
{
    $_SESSION['datefilter']['date_from'] = $_REQUEST['date_from'];
    $_SESSION['datefilter']['date_to'] = $_REQUEST['date_to'];
}