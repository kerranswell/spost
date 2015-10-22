<?php

$pages = $dsp->pages_admin->getList();

$b = $dsp->_BuilderPatterns->create_block('pages_list', 'pages_list', 'center');
$params = $dsp->pages_admin->getParams('list');

$b_list = $dsp->_Builder->addNode($dsp->_Builder->createNode('list', array()), $b);
foreach ($pages as $page)
{
    $b_item = $dsp->_Builder->addNode($dsp->_Builder->createNode('item', array()), $b_list);

    foreach ($page as $f => $v)
    {
        $p = isset($params[$f]) ? $params[$f] : array();
        $p['name'] = $f;
        $dsp->_Builder->addNode($dsp->_Builder->createNode('field', $p, $v), $b_item);
    }
}

$b_fields = $dsp->_Builder->addNode($dsp->_Builder->createNode('fields', array()), $b);
foreach ($params as $name => $p)
{
    $dsp->_Builder->addNode($dsp->_Builder->createNode('field', $p, $name), $b_fields);
}




