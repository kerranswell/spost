<?php

switch ($_REQUEST['type'])
{
    case 'fb' :
        $dsp->socials->updateFBAccessToken();
        break;

    case 'ok' :
        $dsp->socials->updateOKAccessToken();
        break;

    case 'tw' :
        $dsp->socials->updateTWConfiguration();
        break;

    default :

        $b = $dsp->_BuilderPatterns->create_block('tokens', 'tokens', 'center');
        if (isset($_GET['ok']))
        {
            $dsp->_Builder->addNode($dsp->_Builder->createNode('status', array(), 'ok'), $b);
        }

        $dsp->_Builder->addNode($dsp->_Builder->createNode('fb_login_url', array(), $dsp->socials->getFBLoginUrl()), $b);
//        $dsp->_Builder->addNode($dsp->_Builder->createNode('tw_update_url', array(), $dsp->socials->getOKLoginUrl()), $b);
}