<?php
    $dsp->authadmin->Init();
    $notify = '';
    if (isset($_POST['username']) && isset($_POST['password'])) {
    	$uname = $_POST['username'];
        $upass = $_POST['password'];
        $dsp->authadmin->Login($uname, $upass);

        if (!$dsp->authadmin->IsLogged()) {
            $dsp->authadmin->SetParam('invalid_login', '1');
            $notify = 'неверный пароль или логин';
        } else {
            // Logs
            $user = $dsp->usersadmin->GetByLoginPass($uname, $upass);
        }

        if ( strpos( $_SERVER['REQUEST_URI'], 'e-notify=' ) !== FALSE )
            $_SERVER['REQUEST_URI'] = substr( $_SERVER['REQUEST_URI'], 0, strpos( $_SERVER['REQUEST_URI'], 'e-notify=' ) - 1 );

        if ( !empty( $notify ) )
        {
            $_SERVER['REQUEST_URI'] .= ( strpos( $_SERVER['REQUEST_URI'], '?' ) !== FALSE ? '&':'?' ).'e-notify=' . base64_encode($notify);
        }
    }


?>