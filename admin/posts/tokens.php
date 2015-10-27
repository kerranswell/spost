<?php

switch ($_REQUEST['type'])
{
    case 'fb' :
        $dsp->socials->updateFBAccessToken();
        break;

    case 'ok' :
        $dsp->socials->updateOKAccessToken();
        break;

    default :

        $b = $dsp->_BuilderPatterns->create_block('tokens', 'tokens', 'center');
        $dsp->_Builder->addNode($dsp->_Builder->createNode('fb_login_url', array(), $dsp->socials->getFBLoginUrl()), $b);
//        $dsp->_Builder->addNode($dsp->_Builder->createNode('ok_login_url', array(), $dsp->socials->getOKLoginUrl()), $b);
}