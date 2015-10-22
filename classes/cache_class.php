<?php

/**
 * @author Kharchuk A. S.
 * @date 04.2012
 * класс для кэширования любой функции
 */


class cache{

	private $cache_table_name = 'cache_functions';
	private $cache_folder = '/_cache/functions/';
	private $cache_path_pattern = '%s/%s/%s/%s/%s'; //папка/папка/папка/файл - тройная вложенность
	private $cache_folder_length = 4; //длина имени подпапок

	function __construct(){
		global $dsp;
		$this->dsp = $dsp;
	}
	
	function __destruct(){;}
	
        function __set($name, $value){$this->$name = $value;}

        function __get($name){return $this->$name;}

	//полный путь файла кэша
	function getFullPath( $f, $atrs, $related = false, $key = '' ){
		return ( $related ? '' : $_SERVER['DOCUMENT_ROOT'] ) . $this->cache_folder . $this->getPartOfPath( $f, $atrs, $key );
	}
	
	function getPartOfPath( $f, $atrs, $key = '' ){
		$key = !empty($key) ? $key : $this->makeKey( $f, $atrs );
		return sprintf( $this->cache_path_pattern,
			get_class($f[0]),
			$this->getFunctionName( $f ),
			substr( $key, 0, $this->cache_folder_length ),
			substr( $key, $this->cache_folder_length, $this->cache_folder_length ),
			substr( $key, 2*$this->cache_folder_length, $this->cache_folder_length )
		);
	}
	
	function makeKey( $f, $atrs ){
		return md5( get_class( $f[0] ) . $f[1] . serialize( $atrs ) );
	}
	
	function getFunctionName( $f ){
		return $f[1];
	}

	function makePath( $path ){
		$dir_struct = explode('/', $path);
		$dir_struct = array_filter( $dir_struct, function($el){ return !empty($el); } ); //kill empty elements
		//make folders
		$p = $_SERVER['DOCUMENT_ROOT'];
		foreach( $dir_struct as $dir){
			$p .= '/'.$dir;
			if(!is_dir($p)) mkdir($p, 0777);
		}
	}

	function go( $f /*array*/, $atrs /*array*/, $time = 1 /*minutes*/ ){

		$key = $this->makeKey( $f, $atrs );
		$file = $this->getFullPath( $f, $atrs ) . '/' . $key;
		$result = array('key' => $key, 'data' => '');
		//checking cache
		$c = $this->dsp->db->select('select 1 from `'.$this->cache_table_name.'` where cache_id = ? and date_sub(now(), interval ? minute) < caching_start', $key, $time);
		
                $result['data'] = @unserialize(file_get_contents($file));
                if($c && is_file($file)) return $result;
                
		$class = get_class($f[0]);
		$func = $f[1];
		$result['data'] = call_user_func_array( $f, $atrs );
		$this->makePath( $this->getFullPath( $f, $atrs, true /*related*/ ) );

		//$r = $this->dsp->db->Execute('replace `'.$this->cache_table_name.'` values (?,?,?,?,?,now(),?,?,?)', $key, $class, $func, $cid=0, $caching=0, serialize($atrs), ''/*serialize($_REQUEST)*/, $time);
		$r = $this->dsp->db->Execute('INSERT INTO `'.$this->cache_table_name.'`
		    (`cache_id`,`class_name`,`cid`,`function`,`caching`,`caching_start`,`params`,`request_params`,`fullkey`)
		    VALUES (?,?,?,?,?,now(),?,?,?)
		    on duplicate key update
			class_name = ?,
			function = ?,
			caching_start = now(),
			params = ?,
			request_params = ?;
			', $key, $class, $cid=0, $func, $caching=0, serialize($atrs), serialize($_REQUEST), $time,
			$class, $func, serialize($atrs), serialize($_REQUEST)
		);
		$bytes = file_put_contents($file, serialize($result['data']));

		return $result;
	}
	
	 public function drop($f, $atrs) 
    {
		$key = $this->makeKey( $f, $atrs );
		$file = $this->getFullPath( $f, $atrs ) . '/' . $key;
        
        $this->dsp->db->execute('DELETE FROM `'.$this->cache_table_name.'` WHERE cache_id = ?', $key);
        if (is_file($file)) {
			@unlink($file);
        }
    }    
	
}
?>