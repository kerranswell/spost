<?

/*$b = $dsp->_BuilderPatterns->create_block('posts_edit', 'posts_edit', 'center');

$b_date = $dsp->_Builder->addNode($dsp->_Builder->createNode('date', array(), date($date_format, $date)), $b);*/

$dsp->_Builder->Transform('main.xsl');
