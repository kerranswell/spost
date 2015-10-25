<?

$b = $dsp->_BuilderPatterns->create_block('main', 'main', 'center');

$time = time();
$n = date("n", $time);
global $months_rod_pad;
$date = date("j", $time)." ".$months_rod_pad[$n]." ".date("Y", $time);

$b_date = $dsp->_Builder->addNode($dsp->_Builder->createNode('date', array(), $date), $b);

$dsp->_Builder->Transform('main.xsl');
