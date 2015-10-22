<?php
function cdata($str){
	return '<![CDATA['.$str.']]>';
}

function translit($str)
{
    $tr = array(
        "А"=>"A","Б"=>"B","В"=>"V","Г"=>"G",
        "Д"=>"D","Е"=>"E","Ж"=>"J","З"=>"Z","И"=>"I",
        "Й"=>"Y","К"=>"K","Л"=>"L","М"=>"M","Н"=>"N",
        "О"=>"O","П"=>"P","Р"=>"R","С"=>"S","Т"=>"T",
        "У"=>"U","Ф"=>"F","Х"=>"H","Ц"=>"TS","Ч"=>"CH",
        "Ш"=>"SH","Щ"=>"SCH","Ъ"=>"","Ы"=>"YI","Ь"=>"",
        "Э"=>"E","Ю"=>"YU","Я"=>"YA","а"=>"a","б"=>"b",
        "в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"j",
        "з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
        "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
        "с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
        "ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y",
        "ы"=>"yi","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya",
		"ё"=>"yo", "Ё"=>"YO", "ê" => "e", "à" => "a"
    );

	$translit = strtolower(strtr($str, $tr));
	$translit = preg_replace( '/\W/i', '#', $translit );
	$translit = preg_replace( '/#{2,}/i', '#', $translit );
	$translit = str_replace( '#', '-', $translit );
	$translit = preg_replace( '/-$/i', '', $translit );
	$translit = preg_replace( '/^-/i', '', $translit );

    return $translit;
}

function FatalHandler($buffer) {
    if (strpos($buffer, '<b>Fatal error</b>:') === false) {
        return $buffer . Backtrace() . ' NO ERROR';
    } else {
        $backtrace = "<br><br>" . Backtrace();

        return $buffer . $backtrace . " ERROR!!!";
    }
}

function ErrorHandler($errno, $errstr, $errfile, $errline) {
    global $old_error_hundler;

//    print Backtrace();
//    print "<pre>";
//    debug_print_backtrace();
//    print "</pre>";

    if (!empty($old_error_hundler)) {
        $args = func_get_args();
        call_user_func_array($old_error_hundler, $args);
    }
}


function Backtrace() {
    $output = "<div style='text-align: left; font-family: monospace;'>\n";
    $output .= "<pre>\n";
    $output .= "<b>Backtrace:</b><br />\n";
    $backtrace = debug_backtrace();

    foreach ($backtrace as $bt) {
        $args = '';
        foreach ($bt['args'] as $a) {
            if (!empty($args)) {
                $args .= ', ';
            }
            switch (gettype($a)) {
                case 'integer':
                case 'double':
                    $args .= $a;
                    break;
                case 'string':
                    $a = htmlspecialchars(substr($a, 0, 64)) . ((strlen($a) > 64) ? '...':'');
                    $args .= "\"$a\"";
                    break;
                case 'array':
                    $args .= 'Array(' . count($a) . ')';
                    break;
                case 'object':
                    $args .= 'Object(' . get_class($a) . ')';
                    break;
                case 'resource':
                    $args .= 'Resource(' . strstr($a, '#') . ')';
                    break;
                case 'boolean':
                    $args .= $a ? 'True':
                    'False';
                    break;
                case 'NULL':
                    $args .= 'Null';
                    break;
                default:
                    $args .= 'Unknown';
            }
        }
        if (!isset($bt['class'])) {
            $bt['class'] = '';
        }

        if (!isset($bt['type'])) {
            $bt['type'] = '';
        }
        $output .= "<br/>\n";
        if (!empty($bt['file']) && !empty($bt['line'])) $output .= "<b>file:</b> {$bt['file']}:{$bt['line']}<br />\n";
        $output .= "<b>call:</b> {$bt['class']}{$bt['type']}{$bt['function']}($args)<br />\n";
    }
    $output .= "</pre>\n";
    $output .= "</div>\n";


    LogIt($output);

    return $output;
} // Backtrace()


function LogIt($data) {
    if ($fp = fopen(LOGS_DIR . 'errors.log', 'a+')) {
        fputs($fp, "\r\n");
        fputs($fp, "======= " . date('d-m-Y H:i:s') . " =======\r\n");
        fputs($fp, strip_tags($data));
        fputs($fp, "\r\n");

        fclose($fp);
    }
} // LogIt()


function MessageIt($data, $file = false) {
    if ($file === false) {
        $file = 'php://output';
    } elseif ($file == true) {
        $file = LOGS_DIR . 'message.log';
    } else {
        $file = LOGS_DIR . $file . '.log';
    } // if

    if ($fp = fopen($file, 'a+')) {
        fputs($fp, date('d-m-Y H:i:s') . " == ");
        fputs($fp, $data . "\r\n");

        fclose($fp);
    } // if
} // MessageIt()


function ExclosureArray(&$arr) {
    $exclosed = array();
    $new_vals = array();

    foreach ($arr as $idx => $value) {
        if (gettype($value) == "array") {
            ExclosureArray($arr[$idx]);
        }

        if (strpos($idx, "-") !== false) {
            list($main_idx, $second_idx) = explode("-", $idx, 2);
            if (isset($second_idx)) {
                $exclosed[] = $idx;
                if (!isset($new_vals[$main_idx])) {
                    $new_vals[$main_idx] = array();
                }

                $new_vals[$main_idx][$second_idx] = $value;
            } // if
        } // if
    } // foreach

    foreach ($exclosed as $idx) {
        unset($arr[$idx]);
    }

    foreach ($new_vals as $idx => $value) {
        ExclosureArray($new_vals[$idx]);
        $arr[$idx] = $new_vals[$idx];
    }
} // ExclosureArray()


if (!function_exists('array_fill_keys')) {
    function array_fill_keys($keys, $value) {
        $result = array();

        foreach ($keys as $key) {
            $result[$key] = $value;
        }

        return $result;
    } // array_fill_keys()
} // if


function array_append($array1, $array2) {
    if (gettype($array1) != "array" || gettype($array2) != "array") {
        return $array1;
    }

    $result = array();

    foreach ($array1 as $idx => $value) {
        if (!isset($array2[$idx])) {
            $result[$idx] = $value;
        } else {
            $result[$idx] = array_append($value, $array2[$idx]);
        }
    } // foreach

    foreach ($array2 as $idx => $value) {
        if (!isset($array1[$idx])) {
            $result[$idx] = $value;
        }
    } // foreach

    return $result;
} // array_append()


function array_template($array, $tpl, $additive = false) {
    if ((gettype($array) != "array") || (gettype($tpl) != "array") || is_list($array)) {
        return $array;
    }
    if ($additive) {
        $result = $tpl;
    } else {
        $result = array();
    }
    foreach ($tpl as $idx => $value) {
        if (isset($array[$idx])) {
            $result[$idx] = array_template($array[$idx], $value);
        }
    } // foreach

    return $result;
} // array_append()


function is_list($array) {
    $keys = array_keys($array);

    foreach ($keys as $key) {
        if (!is_integer($key)) return false;
    }

    return true;
} // is_list()


function array2list($array, $field_name = '') {
    $result = array();

    if ($field_name == '') {
        foreach ($array as $record) {
            $record[] = reset($record);
        }
    } else {
        foreach ($array as $record) {
            $record[] = $record[$field_name];
        }
    }

    return $result;
}


function Redirect($url, $code = 302) {
    if (headers_sent()) {
        print "<META HTTP-EQUIV='REFRESH' CONTENT='0; URL=$url'>";
    } else {
        header('Location: ' . $url, true, $code);
    }
    die(0);
} // Redirect()


function word($c, $str1, $str2, $str5) {
    $c = abs($c) % 100;
    if ($c > 10 && $c < 20) return $str5;
    $c %= 10;
    if ($c > 1 && $c < 5) return $str2;
    if ($c == 1) return $str1;
    return $str5;
} // word()


function Transform($xslfile, $xml, $return = false) {
    if (Param('_Debug')) {
        header("Content-type: text/xml");
        print $xml;
        exit;
    }

    if (5 == 5) {

        if (class_exists('xsltCache')) {
            $xslt = new xsltCache;
            $xslt->importStyleSheet(TPL_DIR . $xslfile);
        } else {
            $xslt = new xsltProcessor;
            $xsltDoc = DomDocument::load(TPL_DIR . $xslfile);
            $xslt->importStyleSheet($xsltDoc);
        }
        //    $result = $xslt->transformToXML(DomDocument::loadXML($xml));
        $doc = new DOMDocument();
        $load_succesfull = @$doc->loadXML($xml);
        if (!$load_succesfull) {
            $result = $xslt->transformToXML(iceERROR('XML', $xml));
        } else {
            $result = $xslt->transformToXML($doc);
        }

    } else {
        //    ob_end_clean();
        $xslt = domxml_xslt_stylesheet_file(TPL_DIR . $xslfile);
        $dom = @domxml_open_mem($xml);

        if (!$dom) $dom = domxml_open_mem(iceERROR('XML', $xml));

        $final = $xslt->process($dom);
        print serialize($xslt);
        exit;
        $load_end_time = microtime(true);
        $result = ($xslt->result_dump_mem($final));
        unset($dom);
        unset($xslt);
    }
    if (!$return) {
        header("Content-type: text/html");
        print $result;
    } else {
        //print $b; // ajax "<sajax>" string
        return $result;
    }
} // Transform()


function iceERROR($format, $data) {
    LogIt($data);

//    $doc = new DOMDocument();

//    if (!$doc->loadHTML($data)) {
        echo file_get_contents(ROOT_DIR . '/problem.html');
//    }

//    return $doc;
}


function inTag($value, $tag, $attrs = array(), $needquote = false) {
    $tag_array = array($tag);
    foreach ($attrs as $name => $val) {
        $tag_array[] = $name . '="' . $val . '"';
    }
    if ($needquote && (strpos($value, '<![CDATA[') === false)) $value = htmlspecialchars($value);
    return '<' . join(' ', $tag_array) . '>' . $value . '</' . $tag . '>';
}



function Param($name) {
    if (isset($_REQUEST[$name])) {
        return $_REQUEST[$name];
    } elseif (isset($_GET[$name])) {
        return $_GET[$name];
    }

    return '';
} // Param()


function MakeXML($array, $needquote = false) {
    $result = '';

    if (empty($array)) return $result;

    foreach($array as $node => $value) {
        if (is_int($node)) {
            $params = array('id' => $node);
            $node = 'item';
        } else {
            $params = array();
        }

        if (is_array($node) && !empty($node['id'])) {
            $params['id'] = $node['id'];
        }

        if (is_array($value)) {
            $result .= inTag(MakeXML($value, $needquote), $node, $params);
        } else {
            $result .= inTag($value, $node, $params, $needquote);
        }
    }

    /*
    if (strpos($result, '<![CDATA') !== false) {
//        $result = preg_replace('#<!\[CDATA\[(.*)\]\]>#', '<![CDATA[' . html_entity_decode("$1") . ']]>', $result);
        preg_match('#<!\[CDATA\[(.*)\]\]>#', $result, $matche);
        print_r($matche);
    }
    */
    return $result;
} // MakeXML


function MakeIdXML($array, $needquote = false) {
    $result = '';

    if (empty($array)) return $result;

    foreach($array as $node => $value) {
        if (is_int($node)) {
            $params = array('id' => $node);
            $node = 'item';
        } else {
            $params = array();
        }

        if (is_array($value) && !empty($value['id'])) {
            $params['id'] = $value['id'];
            if (sizeof($value) == 2) {
                 unset($value['id']);
                 $value = reset($value);
            }
        }

        if (is_array($value)) {
            $result .= inTag(MakeXML($value, $needquote), $node, $params);
        } else {
            $result .= inTag($value, $node, $params, $needquote);
        }
    }

    /*
    if (strpos($result, '<![CDATA') !== false) {
//        $result = preg_replace('#<!\[CDATA\[(.*)\]\]>#', '<![CDATA[' . html_entity_decode("$1") . ']]>', $result);
        preg_match('#<!\[CDATA\[(.*)\]\]>#', $result, $matche);
        print_r($matche);
    }
    */
    return $result;
} // MakeXML


function MakePagerXML($first, $last, $current, $pre_url, $post_url, $step = 2) {
    //print_r(func_get_args());
    if ($first >= $last) {
        return '';
    }

    $result  = inTag($current, 'current');
    $result .= inTag($pre_url, 'pre_url');
    $result .= inTag($post_url, 'post_url');

    $result .= inTag($post_url, 'post_url');

    $left_bound  = $current - $step;
    $right_bound = $current + $step;

    if ($left_bound < $first + 1) $left_bound = $first + 1;
    if ($right_bound > $last - 1) $right_bound = $last - 1;

    $result .= inTag(1, 'page');
    if ($left_bound != $first + 1)
        $result .= inTag(0, 'page');

    for ($i = $left_bound; $i <= $right_bound; $i++) {
        $result .= inTag($i, 'page');
    }

    if ($right_bound != $last - 1)
        $result .= inTag(0, 'page');

    $result .= inTag($last, 'page');

    return inTag($result, 'pager');
} // MakePagerXML()


function MakeSelectXML($values, $tag) {
//    $result = '';
//    foreach ($values as $id => $value) {
//        $result .= inTag($value, 'item', array('id' => $id));
//    }
    return inTag(MakeXML($values), $tag);
} // MakeSelectXML


function ExtractId($data) {
    if (is_array($data)) {
        if (isset($data['id'])) {
            return $data['id'];
        } else {
            return false;
        }
    } else {
        return $data;
    }
} // ExtractId()


function xml_join(&$root, &$append) {
    if (is_object($append) && (get_class($append) == 'SimpleXMLElement')) {
        if (strlen(trim((string) $append))==0) {
            $xml = $root->addChild($append->getName());
            foreach($append->children() as $child) {
                xml_join($xml, $child);
            }
        } else {
            $value = (string)$append;
            MakeCdata($value);
            $xml = $root->addChild($append->getName(), $value);
        }
        foreach($append->attributes() as $n => $v) {
            $xml->addAttribute($n, $v);
        }
    } else {

    }
}


    function MakeCData(&$data, $times = 1) {
        //var_dump($data);
        if (is_array($data)) {
            foreach ($data as &$item) {
                MakeCData($item, $times);
            }
        } else {
            for ($i = $times; $i > 0; $i--)
                $data = htmlspecialchars($data);
        }
        //var_dump($data);
    }





   function stripslashes_deep($value) {
       $value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);

       return $value;
   }

    function Un_magic_quotes() {
        if (get_magic_quotes_gpc()) {
           $_POST = array_map('stripslashes_deep', $_POST);
           $_GET = array_map('stripslashes_deep', $_GET);
           $_COOKIE = array_map('stripslashes_deep', $_COOKIE);
           $_REQUEST = array_map('stripslashes_deep', $_REQUEST);
        }
    } // Un_magic_quotes()


    function ToUTF8($value) {
       $value = is_array($value) ? array_map('ToUTF8', $value) : mb_convert_encoding($value, 'utf-8', 'windows-1251');

       return $value;
    }

    function ToUTF8Deep() {
           $_POST = array_map('ToUTF8', $_POST);
           $_GET = array_map('ToUTF8', $_GET);
           $_COOKIE = array_map('ToUTF8', $_COOKIE);
           $_REQUEST = array_map('ToUTF8', $_REQUEST);
    }

    function Escape_Text(&$text) {
        $text = '<![CDATA[' . nl2br($text) . ']]>';
    }

    function Escape_Record(&$record, $field_name) {
        $record[$field_name] = '<![CDATA[' . nl2br($record[$field_name]) . ']]>';
    }

    function br2nl($text)
    {
        return  preg_replace('/<br\\\\s*?\\/??>/i', "\\n", $text);
    }

    function CalcLevels(&$list) {
        $uncounted = sizeof($list);

        while ($uncounted > 0) {
            foreach ($list as $idx => $item) {
                if (isset($item['level'])) {

                } elseif ($item['parent_id'] == 0) {
                    $list[$idx]['level'] = 0;
                    $uncounted--;
                } elseif (isset($list[$item['parent_id']]['level'])) {
                    $list[$idx]['level'] = $list[$item['parent_id']]['level'] + 1;
                    $uncounted--;
                } // if
            } // foreach
        } // while
    } // CalcLevels()


    function MakeTree($list) {
        $tree = array();

        $max_level = 0;
        foreach ($list as $idx => $item) {
            $max_level = max($max_level, $item['level']);
            $list[$idx]['_childs'] = array();
        }

        for ($level = $max_level; $level > 0; $level--) {
            foreach ($list as $idx => $item) {
                if ($item['level'] == $level) {
                    $list[$item['parent_id']]['_childs'][$idx] = $item;
                    unset($list[$idx]);
                }
            }
        }

        return $list;
    }


    function MakeList($tree) {
        $result = array();

        foreach($tree as $idx => $node) {
            $childs = $node['_childs'];
            $node['id'] = $idx;
            unset($node['_childs']);
            $result[] = $node;

            if (!empty($childs)) {
                $result = array_merge($result, MakeList($childs));
            }
        }

        return $result;
    }

function reloadUser(&$dsp, $userLogin) { // call when login and social autologin
	$user = $dsp->users->GetByEmail($userLogin);

	if (empty($user)) { // No such user in base
		// Add user to our base
		$ssouser = $dsp->auth->sso->getData($userLogin);

		if (!empty($ssouser)) {
			$ssouser = formatSsoUserForCRUD($dsp, $ssouser);
            $ssouser = importSsoUser($dsp, $ssouser);
            //var_dump($ssouser);   die();
            // Phone is hidden by default in user profile
            $ssouser['hidden_fields'] = $dsp->users_settings->setHiddenFields(array('phone'));
			$user = $dsp->users->AddItem($ssouser); //TODO var_dump($user)
		}
	} else {
		// if revision of user data has been updated - renew all user data
		$rev = $dsp->auth->sso->getRevision($user['email']);
                    //print $rev; print $user['_rev'];

		if ($user['_rev'] != $rev['user']) {
			$ssouser = $dsp->auth->sso->getData($user['email']);
			$ssouser = formatSsoUserForCRUD($dsp, $ssouser);
			//if (isset($user['avatar'])) $ssouser['avatar'] = $user['avatar'];
			$user = $dsp->users->EditItem($user['id'], $ssouser);
		}
	} // if (empty($user))
	return $user;
}

function formatSsoUserForCRUD(&$dsp, $ssouser) {
    if (!empty($ssouser['birthday']) && is_array($ssouser['birthday'])) {
        $ssouser['birthday'] =  join('-', array_reverse($ssouser['birthday']));
    } else {
        $ssouser['birthday'] = '0000-00-00';
    }
    if (!empty($ssouser['meta']['elle']['nickname'])) {
        $ssouser['nickname'] = $ssouser['meta']['elle']['nickname'];
    }
    if ((empty($ssouser['nickname']) || trim($ssouser['nickname']) == '') && isset($ssouser['oauth1']) && isset($ssouser['oauth1']['twitter'])) {
        $ssouser['nickname'] = $ssouser['oauth1']['twitter']['name'];
    }
    return $ssouser;
}

function importSsoUser(&$dsp, $ssouser) {
    switch ($ssouser['from_site']) {
        case 'marieclaire_test':
        case 'marieclaire':
            if (!empty($ssouser['residence'])) {
                $ssouser['city'] = $ssouser['residence'];
            }
            if (empty($ssouser['nickname']) && !empty($ssouser['nick_name'])
                    && $dsp->users->isUniqueNickname($ssouser['nick_name'], $ssouser['email'])) {
                $ssouser['nickname'] = $ssouser['nick_name'];
            }
            if (empty($ssouser['meta']['elle']['aboutme']) && !empty($ssouser['meta']['about'])) {
                $ssouser['meta']['elle']['aboutme'] = $ssouser['meta']['about'];
                $dsp->auth->sso->edit($ssouser['email'], array('meta' => array('elle' => array('aboutme' => $ssouser['meta']['about']))));
            }
            break;
        case 'ellegirl_test':
        case 'ellegirl':
            if (!empty($ssouser['nickname'])
                    && !$dsp->users->isUniqueNickname($ssouser['nickname'], $ssouser['email'])) {
                $ssouser['nickname'] = '';
            }
            if (empty($ssouser['meta']['elle']['aboutme']) && !empty($ssouser['meta']['about'])) {
                $ssouser['meta']['elle']['aboutme'] = $ssouser['meta']['about'];
                $dsp->auth->sso->edit($ssouser['email'], array('meta' => array('elle' => array('aboutme' => $ssouser['meta']['about']))));
            }
            break;
        default:
            break;
    }
    return $ssouser;
}

function makePreview($txt, $l = 100)
{
	if (mb_strlen($txt, 'utf-8') <= $l) return $txt;

	$txt2 = mb_substr($txt, 0, $l-3, 'utf-8');
	$pos = mb_strrpos($txt2, ' ', 'utf-8');

	$txt2 = trim(mb_substr($txt2, 0, $pos+1, 'utf-8'));
	if (mb_strlen($txt2, 'utf-8') < mb_strlen($txt, 'utf-8')) $txt2 = $txt2.( mb_substr($txt2, mb_strlen($txt2)-2, 1) == '.' ? '..' : '...');

	return $txt2;
}

function makePreviewStrong($txt, $l = 100)
{
	if (mb_strlen($txt, 'utf-8') <= $l + 3) return $txt;

	$txt2 = mb_substr($txt, 0, $l, 'utf-8');

	if (mb_strlen($txt2, 'utf-8') < mb_strlen($txt, 'utf-8')) $txt2 = trim($txt2).'...';

	return $txt2;
}

function getPictureResized($url, $w, $h = 0, $crop = false)
{
    global $dsp;
	if (!$h) $h = $w;

	if ($url != '')
	{
		$pw = $w;
		$ph = $h;

		if ($crop)
		{
			$url = $dsp->eis->Resize($url, (int) $w, (int) $h, 'crop-gravity-north');
		} else {

			$sz = $dsp->eis->GetSizeByURL($url);
			if ($sz[0] >= $sz[1])
			{
				if ($sz[0] > $pw)
				{
					$h_ = round(($sz[1] / $sz[0]) * $pw);
					$url = $dsp->eis->Resize($url, (int) $pw, (int) $h_, 'resize');
				}
			} else {
				if ($sz[1] > $ph)
				{
					$w_ = round(($sz[0] / $sz[1]) * $pw);
					$url = $dsp->eis->Resize($url, (int) $w_, (int) $ph, 'resize');
				}
			}
		}
	}

	return $url;
}

function dateFormatted($t)
{
	$months = array(
		1 => 'янв',
		2 => 'фев',
		3 => 'мар',
		4 => 'апр',
		5 => 'май',
		6 => 'июн',
		7 => 'июл',
		8 => 'авг',
		9 => 'сен',
		10 => 'окт',
		11 => 'ноя',
		12 => 'дек',
	);

	return date("d", $t)." ".$months[date("n", $t)].", ".date("H:i", $t);
}

function xml_escape($s)
{
    return str_replace(
        array("&",     "<",    ">",    '"',      "'"),
        array("&amp;", "&lt;", "&gt;", "&quot;", "&apos;"),
        $s
    );
}

function generatePass() {
	$arr = array('a','b','c','d','e','f',
                 'g','h','i','j','k','l',
                 'm','n','o','p','r','s',
                 't','u','v','x','y','z',
                 'A','B','C','D','E','F',
                 'G','H','I','J','K','L',
                 'M','N','O','P','R','S',
                 'T','U','V','X','Y','Z',
                 '1','2','3','4','5','6',
                 '7','8','9','0');
    $pass = "";
    for($i = 0; $i < 9; $i++)
    {
      $index = rand(0, count($arr) - 1);
      $pass .= $arr[$index];
    }
    return $pass;
}

    function getip()
    {
        if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"),"unknown"))
            $ip = getenv("HTTP_CLIENT_IP");
        elseif (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        elseif (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
            $ip = getenv("REMOTE_ADDR");
        elseif (!empty($_SERVER['REMOTE_ADDR']) && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
            $ip = $_SERVER['REMOTE_ADDR'];
        else
            $ip = "unknown";

        return($ip);
    }
//ToUTF8Deep();

function digitWords($number, $words = array('rubl', 'rublya', 'rubley'))
{
	$d100 = $number % 100;
	if ($d100 >= 11 && $d100 <= 19) return $words[2];
	$d10 = $number % 10;
	if ($d10 == 1) return $words[0];
	if ($d10 >= 2 && $d10 <= 4) return $words[1];
	return $words[2];
}

function replaceOriginPath($path)
{
    global $dsp;
    $doc = $dsp->_Builder->doc;
    $xp = new DOMXPath($doc);
    $t = $xp->query('/root/path_origin/path');
    $t->item(0)->nodeValue = $path;
}

function toRelativeURL($url) {
    $p = parse_url($url);
    if (empty($p['path'])) {
        return false;
    }
    return $p['path'] . (!empty($p['query']) ? '?' . $p['query'] : '') . (!empty($p['fragment']) ? '#' . $p['fragment'] : '');
}

function _isAjax() {
	return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));
}


// Check for mobile version
function _isMobile() {
    global $_COOKIE;
//    if (!isset($_COOKIE['mobile_test'])) return false;
    return defined('MOBILE') && MOBILE && !defined('MOBILE_404') && !isset($_REQUEST['nomobile']);
}

// Check if no mobile cookie at all
function _isMobileGlobal() {
    global $_COOKIE;

//    if (!isset($_COOKIE['mobile_test'])) return false;

   	if(isset($_COOKIE['mobile_version'])) return true;

    return false;

}

function redirectUpperCased()
{
    if (preg_match('/[A-Z]/', $_REQUEST['p_']))
    {
        $uri = $_SERVER['REQUEST_URI'];
        $q = strpos($uri, '?');
        $u = $p = '';
        if ($q > 0)
        {
            $u = substr($uri, 0, $q);
            $p = substr($uri, $q);
        } else {
            $u = $uri; $p = '';
        }

        $uri = strtolower($u).$p;
        Redirect($uri, 301);
    }
}

include_once (dirname(__FILE__) . "/const.php");

require_once (CLASS_DIR . "dispatcher.php");
$dsp = Dispatcher::getInstance();

require_once (CLASS_DIR . 'record_class.php');
include_once (dirname(__FILE__) . "/const_services.php");
//$dsp->Init('auth');
ExclosureArray($_REQUEST);

// ------------------------------------------------------------
// filter for post queries
if (!empty($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST')
{
    $ajx_dir = $_SERVER['DOCUMENT_ROOT'].'/ajx/';
    $admin_dir = $_SERVER['DOCUMENT_ROOT'].'/admin/';

    $p_ajx = strpos('1'.$_SERVER['SCRIPT_FILENAME'], $ajx_dir);
    $p_admin = strpos('1'.$_SERVER['SCRIPT_FILENAME'], $admin_dir);

    if (($p_ajx == 1) || ($p_admin == 1) || !empty($_REQUEST['opcode']))
    {
        // скрипт лежит в папке ajx или admin или же присутствует параметр opcode - валидно
    } else {
        // не валидно
        $ip = getip();
        $logfile = $_SERVER['DOCUMENT_ROOT'].'/logs/no_opcode_ip.log';
        // проверяем, есть ли этот адрес уже в списке
        $found = false;
        $handle = @fopen($logfile, "r");
        if ($handle)
        {
            while (!feof($handle))
            {
                $buffer = fgets($handle);
                if(!strcmp($buffer, $ip."\r\n"))
                {
                    $found = true;
                    break;
                }
            }
            fclose($handle);
        }

        if (!$found)
            file_put_contents($logfile, $ip."\r\n", FILE_APPEND);
    }
}
// filter for post queries
// ------------------------------------------------------------

?>
