<?php

class blocks extends Record {

    private $placement_table = 'placement';

    public function setPlacementTable($table)
    {
        $this->placement_table = $table;
    }

    function getStaticsArr( $layout=1, $typeservice=1, $itemid=0 /*id пункта кокого-либо сервиса*/, $vars = array() ){
//        if ($layout == 101) $layout = ' ( p.layout_id = 101 ) and '; // для мобильной версии не выводим весь мусор сайта, а выводим только
//        if ($layout >= 100) $layout = ' ( p.layout_id = '.$layout.' ) and '; // для мобильной версии не выводим весь мусор сайта, а выводим только
//        else $layout = $layout ? ' ( p.layout_id = '.$layout.' or p.layout_id = 0 ) and ' : '';
//        $layout = $layout ? ' ( p.layout_id = '.$layout.' or p.layout_id = '.(_isMobile() ? '100' : '0').' ) and ' : '';
//
		$sql = 'select
				s.id,
				s.file,
				s.wrap,
				s.description,
                                s.async,
				s.status,
				p.layout_id,
				p.place_id,
                                p.exclusion,
				p.order
			from statics s
				join '.$this->placement_table.' p on ( p.id = s.id and p.type = 2 )
			where
				'.
				($layout ? ' ( p.layout_id = '.$layout.' or p.layout_id = 0 ) and ' : '').
				($typeservice ? ' ( p.typeservice = '.$typeservice.' or p.typeservice = 29 ) and ' : '').
				($itemid ? ' ( p.item_id = '.$itemid.' or p.item_id = 0 ) and ' : '').
				's.status = 1
			order by p.exclusion desc, p.order, file';

		return $this->dsp->db->select($sql);
	}

	function runStatics( $layout=1, $typeservice=1, $itemid=0 /*id пункта кокого-либо сервиса*/, $vars = array() ){
		$arr = $this->getStaticsArr( $layout, $typeservice, $itemid, $vars );
		$starr = array();
        $exclusions = array();
        $stats_node = $this->dsp->_Builder->createNode('statics');
		foreach( $arr as $i => $s ){
            if ($s['exclusion']) $exclusions[$s['id']] = 1;
            if (isset($exclusions[$s['id']])) {
                continue;
            }
			$p = explode('.', $s['file'] );
			$ext = end( $p );
			$wrap = empty($s['wrap']) ? array('','') : explode('?', $s['wrap']);

            $p = explode('/', $s['file']);

            if (!empty($p) && ($p[1] == 'static')) {
                //$p[0] = $p[1];
                //$p[1] = STATIC_VERSION;
                unset($p[0]);
                $s['file'] = '/' . join('/', $p) . '?v='.STATIC_VERSION;
            }

			//<link rel="stylesheet" href="/static/css/main.css" />
			if( $ext == 'css' ){
				$f = $wrap[0].'<link rel="stylesheet" href="'.$s['file'].'" />'.$wrap[1];
			}
			//<script src="/source/js/main/20-widthwatch.js"></script>
			if( $ext == 'js' ){
				$f = $wrap[0] . '<script src="' . $s['file'] . '"' .
                    (!empty($s['async']) ? ' async="async"' : '') .
                    '></script>' . $wrap[1];
			}
//			$starr []= $f;
            $xnode = $this->dsp->_Builder->createNode('item', array('place' => $s['place_id'], 'ext' => $ext, '_key' => $i), $f);
            $stats_node->appendChild($xnode);
		}

//		$this->dsp->_Builder->addArray( $starr, $tag = 'statics', $extra_attrs = array(/* 'id'=>'statics' */), $node = '', $asItems = false, $attrs = array() );
		$this->dsp->_Builder->addNode($stats_node);
	}

	//массив блоков в соответствии с развеской
	function getBlocksArr( $layout=1, $typeservice=1, $itemid=0 /*id пункта кокого-либо сервиса*/, $vars = array() ){

//        $layout = $layout ? ' ( p.layout_id = '.$layout.' or p.layout_id = '.(_isMobile() ? '100' : '0').' ) and ' : '';
//        $layout = $layout ? ' ( p.layout_id = '.$layout.' or p.layout_id = '.(_isMobile() ? '0' : '0').' ) and ' : '';

        $cat_id = 0;
if (isset($vars['catalogue_cat']))
{
    $cat_id = mysql_real_escape_string($vars['catalogue_cat']['id']);
}
		$sql = 'select
				b.blocks_id id,
				b.name title,
				b.name_admin,
				b.type,
				b.function params,
				b.cached cached,
				b.cache_timeout timeout,
				b.class class_name,
				p.layout_id,
				p.place_id,
                                p.exclusion,
				p.order, 
                                p.idx AS `placement`,
                                p.cat_id
			from blocks b
				join '.$this->placement_table.' p on ( p.id = b.blocks_id and p.type = 1 )
			where
				'.
				($layout ? ' ( p.layout_id = '.$layout.' or p.layout_id = 0 ) and ' : '').
				($typeservice ? ' ( p.typeservice = '.$typeservice.' or p.typeservice = 29 ) and ' : '').
				($itemid ? ' ( p.item_id = '.$itemid.' or p.item_id = 0 ) and ' : '').
				'b.status = 1 and ((p.cat_id > 0 and p.cat_id = '.$cat_id.') or p.cat_id = 0)
			order by p.exclusion desc, p.order
			';

		return $this->dsp->db->select($sql);
	}

	//формируем сами блоки
	//в классе блока должен быть метод addToBuilder()
	function runBlocks( $layout=1, $typeservice=1, $itemid=0 /*id пункта кокого-либо сервиса*/, $vars = array() ){
            $arr = $this->getBlocksArr( $layout, $typeservice, $itemid, $vars );
            $exclusions = array();

            foreach( $arr as $i => $block ){
                    if ($block['exclusion'] == 1 && ($block['layout_id'] == $layout || $block['layout_id'] == 0)) {
                        $exclusions[$block['id']] = true;
                    }
                    if (isset($exclusions[$block['id']])) {
                        continue;
                    }

                    $bid		=	$block['id'];
                    $name		=	$block['title'];
                    $name_admin	        =	$block['name_admin'];
                    $class		=	$block['class_name'];
                    $method		=	$block['params'];
                    $cached		=	$block['cached'];
                    $timeout	        =	$block['timeout'];
                    $place		=	$block['place_id'];
                    $layout		=	$block['layout_id'];
                    $order		=	$block['order'];
                    $type		=	!empty($block['type']) ? $block['type'] : 1;
                    $cache_key          =       '';

                    $st = microtime( true );

                    if( $type == 1 ){
                            if( empty( $class ) ) continue;
                            if( gettype($this->dsp->$class) != 'object' ) continue;
                            if( !method_exists( $this->dsp->$class, $method ) ) continue;
                            $articles_ids = array();
                            $r            = array();

                            // хак для уникальности кеша у каждого блока
                            $vars['temp_block_id'] = $bid;
                            $vars['blocks_ids']    = array();
                            
                            // mannuals
                            $manuals = array();
							$multi_service = FALSE;
							//проверка есть ли ручной вывод в блоке и добавленные в него элементы
							$sql = 'SELECT
                                                a.id AS id, a.service, b.multi_service, a.block_manual_id
                                        FROM blocks_manual AS b, blocks_manual_items AS a, blocks AS bl
                                        WHERE
                                                '.
                                                ($layout ? ' ( b.layout_id = '.$layout.' or b.layout_id = 0 ) AND ' : '').
                                                ($typeservice ? ' ( b.service_id = '.$typeservice.' or b.service_id = 29 ) AND ' : '').
                                                ($itemid ? ' ( b.item_id = '.$itemid.' or b.item_id = 0 ) AND ' : '').
                                                'b.block_id = ? AND
                                                 b.id = a.block_manual_id AND
                                                 b.block_id = bl.blocks_id
                                        ORDER BY a.position ASC, a.date DESC limit 10';
							$items = $this->dsp->db->Select($sql,$bid);

							foreach ( $items AS $item )
							{
								if ( !isset( $manuals[$item['service']] ) ) $manuals[$item['service']] = array();
								$manuals[$item['service']][] = $item['id'];
								if ( $item['multi_service'] ) $multi_service = TRUE;

                                $vars['blocks_ids'][$item['service']] = $item['block_manual_id'];
							}

							$vars['manual'] = (is_array( $manuals ) && count($manuals) > 0 ) ? ( ($multi_service) ? $manuals : reset($manuals) ) : array();

                            // end manuals
                            $no_block_cache = 0;
                            if (!empty($_REQUEST['no_cache_id']) && $_REQUEST['no_cache_id'] == $bid) $no_block_cache = 1;
                            if ($cached && empty($vars['go_dynamic']) && !$no_block_cache)
                            {
                                if (_isMobile()) $vars['mobile_version'] = 1;
                                $r = $this->dsp->cache->go( array( $this->dsp->$class, $method ), array( $vars ), $timeout );
                                $cache_key = $r['key'];
                                $r = $r['data'];
                            }
                            else
                            {
                                //выполнение метода класса, указанного в развеске
                                $result = $this->dsp->$class->$method( $vars );
                                
                                //слияние полученных массивов
                                if ( count($r) > 0 && count($result) > 0 ) $r = array_merge($r,$result);
                                elseif ( count($result) > 0 ) $r = $result;

                                unset($vars['temp_block_id']);

                                if ( isset( $vars['count'] ) ) unset($vars['count']);
                                if ( isset( $vars['manual'] ) ) unset($vars['manual']);
                            }

                            if ($r === false) continue;
                    }
                    elseif( $type == 2 )
                    {
                            // Add Google Analytics (GA) attribute
                            $method = preg_replace('~^<div~i', '<div data-ga="' . $bid . '"', trim($method));
                            $r = array( 'data'=>$method );
                    }

                    $et = microtime( true );

                    $time = number_format( $et - $st, 6 );
                    //list($int, $dec) = explode('.', $time);
                    //$time = number_format($time, strlen($dec));
                    //$dec = round( $dec );
                    //$time = $int . '.' . $dec;

                    $this->dsp->_Builder->addArray(
                            (array) $r,
                            $tag = 'block',				
                            $extra_attrs = array(
                                    'id'		=>	$bid,
                                    'type'		=>	$type,
                                    'name'		=>	$name,
                                    'name_admin'	=>	$name_admin,
                                    'place'		=>	$place,
                                    'layout'		=>	$layout,
                                    'order'		=>	$order,
                                    'debug'		=>	( Param('_blocks') ? 'on' : ( Param('_sblocks') ? 'short' : 'off' ) ),
                                    'cached'		=>	$cached,
                                    'cahe_timeout'	=>	$timeout,
                                    'time'		=>	$time,
                                    'cashed'		=>	$cached,
                                    'cache_key'         =>      $cache_key
                            ),
                            $node = '',
                            $asItems = false, 
                            $attrs = array()
                    );

            }
	}


} // class blocks
