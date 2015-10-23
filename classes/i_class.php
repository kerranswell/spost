<?php
    ini_set('display_errors',1);
    /*
    Система картинок.
    Общая идея:

    1) Хранение картинок без привязки к ID, а общим списком
    2) Создание имени картинки каким-нибудь образом (предлагаю MD + CRC(timestamp))
    3) Имя картинки сразу хранит размерность.Типа x-y-afd46bc...
    4) Какая-то часть имени является еще и путем для уменьшения кол-ва картинок в папке
    5) Ресайзы хранить так же, как и сейчас -service_id/size_id/...

    Реализовать класс работы с картинками:
    - создать имя
    - положить картинку
    - сделать ресайз
    - удалить (со всеми ресайзами)
    - заменить картинку (удалить все старые ресайзы)
    */

    if (!defined('IMAGE_TH_PATTERN')) define ('IMAGE_TH_PATTERN', '%s/%s/%s.%s');

    /**
    * @author Kharchuk A. S.
    * @date 02.2012
    */
    class i{
        private $gITCache = array();
        private $secure = '@wdwfghd0=0w\$_4gj*0-=\12kdfdfb7xcvxcv789!7455-*A*+*';
        public $doNotExec = false;
        public $default_path;

        private $max_width = '5000'; //px
        private $max_height = '5000'; //px
        private $max_weight = 5242880; //bytes - 5mb
        private $min_width = '200'; //px
        private $min_height = '200'; //px
        /*
        set allowed extention for images $allow_ext
        1 = IMAGETYPE_GIF,	2 = IMAGETYPE_JPG,	3 = IMAGETYPE_PNG,	4 = IMAGETYPE_SWF,	5 = IMAGETYPE_PSD,
        6 = IMAGETYPE_BMP,	7 = IMAGETYPE_TIFF_II (intel byte order),	8 = IMAGETYPE_TIFF_MM (motorola byte order),
        9 = IMAGETYPE_JPC,	10 = IMAGETYPE_JP2,	11 = IMAGETYPE_JPX.
        */
        private $allow_types = array( 1, 2, 3 );
        private $image_dir = 'images/';
        private $th_dir = 'images/c/';
        private $th_whitemargin = false;
        private $th_jpeg_quality = 85;
        private $folder_length = 4; // число символов в имени подпапки
        private $images_cache_lifetime = 2592000; //image cache lifetime
        private $useResizeImage_gd = true;

        // old settings
        //  private $path_to_imagemagick_convert = '/usr/local/bin/convert';
        private $path_to_imagemagick_convert = 'convert';

        function __set($name, $value){$this->$name = $value;}

        function __get($name){return $this->$name;}

        function getImageDir(){
            return $this->image_dir;
        }

        function __construct(){
            global $dsp;
            global $error_text;

            if ( !defined('ROOT_DIR') ) { 
                define( 'ROOT_DIR', $_SERVER['DOCUMENT_ROOT'].'/' );
            }
            require_once(ROOT_DIR."th-sizes.php");
            if(!isset($this->dsp)){
                $this->dsp = $dsp;
            }

            $this->default_path = SITE.'/'.$this->th_dir;
        }

        //add image to db layer
        function addImageToDB( $name, $orig_name, $s /* = getimagesize()*/, $file_size, $th = 0 ){
            list( $width, $height, $type ) = $s;
            //$full_key можно использовть для выборки по одному полю, если это критично по времени (для документориентированных субд)
            $full_key = md5( $name . $type . $width . $height . (int)$th );
            $sql = 'insert into `images` (`name`,`type`,`width`,`height`,`ts`,`th`,`weight`,`full_key`,`orig_name`) values ( ?, ?, ?, ?, CURRENT_TIMESTAMP, ?, ?, ?, ?)--';
            $r = $this->dsp->db->Execute( $sql, $name, $type, $width, $height, $th, $file_size, $full_key, $orig_name );

            if($this->dsp->db->last_errno) { 
                return 'db error';
            }
            else {
                return $this->dsp->db->SelectValue( 'select idx from images where full_key = ?--', $full_key );
            }
        }

        //add thumbnail to db layer
        function addThToDB( $dst, $name, $size_code ){
            $image_size = getimagesize( $dst );//array
            //$full_key можно использовть для выборки по одному полю, если это критично по времени (для документориентированных субд)
            $full_key = md5( $name . $image_size[2] . $image_size[0] . $image_size[1] . (int)$size_code );
            
            if (!($idx = $this->dsp->db->SelectValue( 'select idx from images where full_key = ?--', $full_key ))) {
                $this->dsp->db->Execute( 'insert into `images` (`name`,`type`,`width`,`height`,`ts`,`th`,`weight`,`full_key`,`orig_name`) values (?, ?, ?, ?, CURRENT_TIMESTAMP, ?, ?, ?, "")--', $name, $image_size[2], $image_size[0], $image_size[1], $size_code, filesize( $dst ), $full_key );
            }
            if($this->dsp->db->last_errno) { 
                return 'db error';
            } 
            else { 
                return $this->dsp->db->SelectValue( 'select idx from images where full_key = ?--', $full_key );
            }
        }

        /**
        * 
        * Enter description here ...
        * @param unknown_type $file
        * @param unknown_type $is_validate_full
        */
        function checkImage( $file /* = $_FILES['userfile'] */, $is_validate_full = false){
            if( $file["size"] > $this->max_weight ) { 
                return 1;
            }

            $is = getimagesize( $file['tmp_name'] );
            $width = $is[0];
            $height = $is[1];
            $type = $is[2];

            if( $width > $this->max_width ) {
                return 2;
            }
            if( $height > $this->max_height ) { 
                return 3;
            }
            if( !in_array( $type, $this->allow_types ) ) {
                return 4;
            }
            if( $is_validate_full && $width < $this->min_width  ) {
                return 5;
            }
            if( $is_validate_full && $height < $this->min_height ) {
                return 6;
            }

            return $is;
        }

        function createName( $tmp_name, $s ){
            return $s[0] . '@' . $s[1] . '@' . md5( $tmp_name . $s[2]/*type*/ . microtime( true ) );
        }

        //работает с файлом уже лежащем на вервере в произвольном месте
        function reputToPlace( $file /* fullpath */ ){
            $weight = filesize($file);
            if( $weight > $this->max_weight ) {
                return false;
            }

            $is = getimagesize( $file );
            if( !$is ) {
                // die('bad file');
                return false;
            }

            $width = $is[0];
            $height = $is[1];
            $type = $is[2];

            if( $width > $this->max_width ) { 
                return false;
            }

            if( $height > $this->max_height ) return false;
            if( !in_array( $type, $this->allow_types ) ) return false;


            $name = $this->createName( $file, $is );
            $upload_file = $this->makePath( $is, $name );

            if (copy($file,$upload_file)) {
                unlink($file);
            } else {
                // die('copy file error ['.$file.' to '.$upload_file.']');
                return false;
            }

            $idx = $this->addImageToDB( $name, $file /*orig name*/, $is /* = getimagesize()*/, $weight, $th = 0 );

            if( is_file( $file['tmp_name'] ) ) unlink( $file['tmp_name'] );

            return array( $idx, $name, $upload_file );
        }

        //только для загруженных по http файлов
        function putToPlace( $file /* = $_FILES['userfile'] */ ){
            $file_size = $file['size'];//weight
            $orig_name = $file['name'];
            $image_size = $this->checkImage( $file );//array
            if( !is_array($image_size) ){
                global $error_text;
                $this->_returnError($error_text['image_validate_images']['error_' . (int)$image_size]);
                return false;
            }

            $name = $this->createName( $file['tmp_name'], $image_size );
            $upload_file = $this->makePath( $image_size, $name );

            if ( !move_uploaded_file( $file['tmp_name'], $upload_file ) ){
                $this->_returnError('move uploaded file error ['.$file['tmp_name'].' to '.$upload_file.']');
                return false;
            }else{
                $idx = $this->addImageToDB( $name, $orig_name, $image_size /* = getimagesize()*/, $file_size, $th = 0 );
            }

            if( is_file( $file['tmp_name'] ) ) unlink( $file['tmp_name'] );

            return array( $idx, $name );
        }

        function subfolder( $name ){
            $sf = explode( '@', $name );
            $sf = $sf[2];
            return substr( $sf, 0, $this->folder_length ) . '/' . substr( $sf, $this->folder_length, $this->folder_length );
        }

        /*
        * get path to image by image ID
        * 
        * @param integer $idx
        *   image ID from DB 
        * @param integer $size_code
        *   index of assoc array $GLOBALS["sizes"] from th-sizes.php. If it's 0 then
        *   return nominal size 
        */
        function resize( $idx, $size_code = 0 ){
            if(empty($idx)) {
                // * return empty string if idx is empty
                return "";
            }

            $image = $this->dsp->db->SelectRow( 'select * from images where idx = ?--', (int)$idx );

            if(empty($image)) {
                // * if we dont find eny records related with incoming idx we return empty string
                return "";
            }
            $ext = $this->image_type_to_extension( (int)$image['type'] );
            //$path = $this->th_dir . $size_code . '/' . $this->subfolder( $image['name'] ) . '/' . $image['name'] . '-' . $this->hashme($image['name']) . '.' . $ext;
            $path = (!empty($size_code) ? ($size_code. '/') :  "0/") . $this->subfolder( $image['name'] ) . '/' . $image['name'] . '-' . $this->hashme($image['name']) . '.' . $ext;
            return $path;
        }

        public function getOriginal( $idx )
        {
            if(empty($idx)) {
                // * return empty string if idx is empty
                return "";
            }

            $image = $this->dsp->db->SelectRow( 'select * from images where idx = ?--', (int)$idx );

            if(empty($image)) {
                // * if we dont find eny records related with incoming idx we return empty string
                return "";
            }
            $ext = $this->image_type_to_extension( (int)$image['type'] );
            $path = $image['width'] .'/'. $image['height'] .'/'. $image['name'] . '.' . $ext;

            return $path;
        }

        /*
        * get path to image by image Name
        * 
        * @param integer $name
        *   image name from DB 
        * @param integer $size_code
        *   index of assoc array $GLOBALS["sizes"] from th-sizes.php. If it's 0 then
        *   return nominal size 
        */
        function resizeByName( $name, $size_code = 0 ){
            if(empty($name)) {
                // * return empty string if name is empty
                return "";
            }
            $image = $this->dsp->db->SelectRow( 'select * from images where name = ?--', $name );
            if(empty($image)) {
                // * if we dont find eny records related with incoming idx we return empty string
                return "";
            }
            $ext = $this->image_type_to_extension( (int)$image['type'] );
            //$path = $this->th_dir . $size_code . '/' . $this->subfolder( $image['name'] ) . '/' . $image['name'] . '-' . $this->hashme($image['name']) . '.' . $ext;
            $path = (!empty($size_code) ? ($size_code. '/') :  "") . $this->subfolder( $image['name'] ) . '/' . $image['name'] . '-' . $this->hashme($image['name']) . '.' . $ext;
            return $path;
        }

        function clearByName( $name ){

            $all = $this->dsp->db->Select( 'select * from images where `name` = ?--', $name );
            foreach( $all as $c ){
                $dir = '';
                if( $c['th'] ) {
                    $path = $this->imagePattern( $c['th'], $c['name'], str_replace( '.', '', $this->image_type_to_extension( $c['type'] ) ), $this->hashme($c['name']) );
                    $dir = $this->th_dir . $c['th'] .'/' . $this->subfolder( $name );
                }
                else {
                    $path = $this->image_dir . $c['width'] . '/' . $c['height'] . '/' . $c['name'] . '.' . $this->image_type_to_extension( $c['type'] );
                    $dir = $this->image_dir . $c['width'] . '/' . $c['height'];
                }

                $my_del = unlink( ROOT_DIR . $path );
                $this->removeDirs(ROOT_DIR . $dir);
            }
            $this->dsp->db->Execute( 'delete from images where `name` = ?--', $name );
            return $this->dsp->db->last_errno ? 'false' : 'true';
        }

        function removeDirs($dir)
        {
            $t = explode('/', $dir);
            $last = array_pop($t);
            if ($last == 'c') return;

            $files = scandir($dir);
            $del = 1;
            foreach ($files as $f) if ($f != '.' && $f != '..') {$del = 0; break;}
            if ($del) rmdir($dir);
            else return;
            $dir = implode("/", $t);
            $this->removeDirs($dir);
        }

        function clearByIDX( $idx, $user_id = 0 ){
            $img  = $this->dsp->db->SelectRow( 'select `name`, `user_id` from images where idx = ?--', $idx );
            if ( $user_id == 0 || $user_id == $img['user_id'] ) $this->clearByName( $img['name'] );
            return $this->dsp->db->last_errno ? 'false' : 'true';
        }

        function replaceByName( $old_name, $file /* = $_FILES['userfile'] */ ){
            $this->putToPlace( $file );
            $this->dsp->db->Execute( 'delete from images where name = ?--', $old_name );
        }

        function replaceByIDX( $old_idx, $file /* = $_FILES['userfile'] */, $user_id = 0 ){
            $result = $this->putToPlace( $file );
            list($fid, $fpath) = $result;
            $this->clearByIDX( $old_idx, $user_id );
            return $result;
        }

        /**
        * Открывает файл-изображение $src.
        * По ходу задаются тип $type и массив $s (результат getimagesize($src)), переданные по ссылке.
        *
        * @param string $src - image file path
        * @param string $type - [returns] image type constant
        * @return resource - GD image
        */
        function getImageType($src, &$s) {
            if (isset($this->gITCache[$src])) {
                $s = $this->gITCache[$src][1];
                return $this->gITCache[$src][0];
            }
            if (!file_exists($src)) return false;
            $s = @getimagesize($src);
            if (!$s) return false;
            if (!function_exists('exif_imagetype')) {
                if (false !== strpos($s['mime'],"image/jpeg")) $type = IMAGETYPE_JPEG;
                if (false !== strpos($s['mime'],"image/gif")) $type = IMAGETYPE_GIF;
                if (false !== strpos($s['mime'],"image/png")) $type = IMAGETYPE_PNG;
            }
            else {
                $type = exif_imagetype($src);
            }
            $this->gITCache[$src] = array($type, $s);
            return $type;
        }

        function imagePattern( $size, $name, $ext, $hash='' ) {
            return sprintf( $this->th_dir . IMAGE_TH_PATTERN, $size, $this->subfolder( $name ) , $name . (strlen($hash)?('-'.$hash):''), $ext);
        }

        function imageValidatePath($path) {
            if ( !preg_match("~([0-9]+)\/([a-zA-Z0-9]+\/[a-zA-Z0-9]+)\/([@a-zA-Z0-9]+)-([a-zA-Z0-9]+)\.([a-z]+)$~", $path, $matches) ) {
                return false;
            }
            list( $path, $size_code, $subfolder, $name, $hash, $ext ) = $matches;
            $ow = $oh = 0;
            if (preg_match("~^([0-9]+)@([0-9]+)@~", $name, $m2))
            {
                $ow = $m2[1];
                $oh = $m2[2];
            }
            $sizes = $GLOBALS['isSizes'];
            if(!isset($sizes[$size_code])) {
                return false;
            }
            $h=$this->imagePattern( $size_code, $name, $ext );
            $h=substr(base64_encode(substr(sha1($name.$this->secure),30)),0,-2);
            return (0===strcmp($h,$hash) ? array($size_code,$name,$ext,$hash,$ow,$oh) : false);
        }
        
        function hashme(&$s){
            return substr(base64_encode(substr(sha1($s.$this->secure),30)),0,-2);
        }

        /**
        * Изменяет размеры файла $src до высоты $new_width и ширины $new_height,
        * осуществляет жесткую подгонку размера ($cut=true)
        * сохраняет результат в файл $dst
        * в соответствии с типом файла $src, если не указан особо тип $force_type
        *
        * @param string $src - source image file path
        * @param string $dst - destination image file path
        * @param int $new_width - width
        * @param int $new_height - height
        * @param boolean $cut - force use $new_width, $new_height even $src sizes is smaller
        * @param int $force_type - image type constant
        * @param boolean $whitemargin - Большая картинка уменьшится, чтобы полностью влезть в новые размеры. Добавляются поля.
        * @return void | false on error
        */
        function resizeImage($src, $dst = '', $new_width = 150, $new_height = 150, $cut = false, $whitemargin=null, $params=array())
        {
            $type   = $this->getImageType($src, $size);
            $is_gif = (IMAGETYPE_GIF  == $type);

            if (is_null($whitemargin)) $whitemargin = $this->th_whitemargin;
            if (empty($dst)) return false;
            //return $this->resizeImage_imagick($src, $dst, $new_width, $new_height, $cut, $whitemargin, $params);
            if ($this->useResizeImage_gd && !$is_gif) {
                return $this->resizeImage_gd($src, $dst, $new_width, $new_height, $cut, $whitemargin, $params);
            } else {
                return $this->resizeImage_imagick($src, $dst, $new_width, $new_height, $cut, $whitemargin, $params);
            }
        }

        function resizeImage_imagick($src, $dst, $new_width, $new_height, $cut, $whitemargin, $params=array())
        {
            $type = $this->getImageType($src, $size);
            if (!$type) return false;
            $filename_without_ext = substr($dst, 0, strrpos($dst, '.')).'.';
            $ext = $this->image_type_to_extension($type);
            $dst = $filename_without_ext . $ext;
            $is_jpg = (IMAGETYPE_JPEG == $type);
            $is_png = (IMAGETYPE_PNG  == $type);
            $is_gif = (IMAGETYPE_GIF  == $type);

            if ($cut) {
                $_new_width = $new_width; $_new_height = $new_height;	 // размеры выходного файла ($cut=true)
                $this->calcImageSize($size, $new_width, $new_height, $whitemargin);
            }
            else {
                $this->calcImageSize($size, $new_width, $new_height, 1);
                $_new_width = $new_width; $_new_height = $new_height;
            }

            //$com = '/usr/local/bin/convert -fill "rgba(255,255,255,1)" '.$src; 
            $com = $this->path_to_imagemagick_convert . (!$is_gif ? ' -fill "rgba(255,255,255,0)" ':' ').$src;
            if (!$is_gif) $com .= " -type TrueColorMatte";
            else $com .= " -coalesce";
            $com .= " -resize ".$new_width."x".$new_height;
            $com .= ' -size '.$_new_width."x".$_new_height;
            $com .= ($is_jpg) ? ' xc:#fff' : (!$is_gif?' xc:"rgba(255,255,255,0)"':' '); // цвет фона (белый = 1 | прозрачный = 0) "rgba(255,255,255,1)"
            $geo = (isset($params['gravity'])) ? $params['gravity'] : 'center';
            if (!$is_gif) $com .= ' +swap -gravity '.$geo.' -composite'; // центрируем
            if (isset($params['rotate'])) $com .= ' -rotate '.$params['rotate'];
            if (!$is_gif) $com .= ' -quality '.$this->th_jpeg_quality." -strip";
            $com .= " ".$dst;

            if ($this->doNotExec) return $com;
            else exec($com);


            if (!is_file($dst)) exit($com);
            return array($ext, $size['mime'], false);
        }

        function resizeImage_gd($src, $dst, $new_width, $new_height, $cut, $whitemargin, $params=array())
        {
            $type = $this->getImageType($src, $size);
            if (!$type) return false;
            $filename_without_ext = substr($dst, 0, strrpos($dst, '.')).'.';
            $ext = $this->image_type_to_extension($type);
            $dst = $filename_without_ext . $ext;
            $is_jpg = (IMAGETYPE_JPEG == $type);
            $is_png = (IMAGETYPE_PNG  == $type);
            $is_gif = (IMAGETYPE_GIF  == $type);

            if ($cut) {
                $_new_width = $new_width; $_new_height = $new_height;         // размеры выходного файла ($cut=true)
                $this->calcImageSize($size, $new_width, $new_height, $whitemargin);
            }
            else {
                $this->calcImageSize($size, $new_width, $new_height, 1);
                $_new_width = $new_width; $_new_height = $new_height;
            }

            /*
            //$com = '/usr/local/bin/convert -fill "rgba(255,255,255,1)" '.$src; 
            $com = $this->path_to_imagemagick_convert . ' -fill "rgba(255,255,255,0)" '.$src; 
            if (!$is_gif) $com .= " -type TrueColorMatte";
            // @todo: здесь задается обрезка (вычисляются размеры, до которых изменяется изображение
            $com .= " -resize ".$new_width."x".$new_height;
            $com .= ' -size '.$_new_width."x".$_new_height;
            $com .= ($is_jpg) ? ' xc:#fff' : ' xc:"rgba(255,255,255,0)"'; // цвет фона (белый = 1 | прозрачный = 0) "rgba(255,255,255,1)"
            $geo = (isset($params['gravity'])) ? $params['gravity'] : 'center';
            $com .= ' +swap -gravity '.$geo.' -composite'; // центрируем
            if (isset($params['rotate'])) $com .= ' -rotate '.$params['rotate'];
            if (!$is_gif) $com .= ' -quality '.$this->th_jpeg_quality." -strip";
            $com .= " ".$dst;

            if ($this->doNotExec) return $com;
            else exec($com);


            if (!is_file($dst)) exit($com);
            */
                                
            $gd_src = false;

            if ($is_jpg) {
                $gd_src = imagecreatefromjpeg($src);
            } else if ($is_png) {
                $gd_src = imagecreatefrompng($src);
            } else if ($is_gif) {
                $gd_src = imagecreatefromgif($src);
            } else {
                if (($file_src = file_get_contents($src))) {
                    $gd_src = imagecreatefromstring($file_src);
                }
            }
                        
            $cantent = false;
            
            if (!empty($gd_src)) {

                $gd_dst = false;
                    
                if (!$is_gif) {
                    $gd_dst = imagecreatetruecolor($_new_width, $_new_height);
                } else {
                    $gd_dst = imagecreate($_new_width, $_new_height);
                }
                
                if (!empty($gd_dst)) {
                    
                    $color = 0;

                    if (!$is_jpg) {
                        $color = imagecolorallocatealpha($gd_dst, 255, 255, 255, 0);
                    } else {
                        $color = imagecolorallocate($gd_dst, 255, 255, 255);
                    }
                    
                    imagefill($gd_dst, 0, 0, $color);
                    
                    $x = 0; 
                    $y = 0; 
                    
                    $geo = (isset($params['gravity'])) ? $params['gravity'] : 'center';
                    
                    switch($geo) {
                        case 'north':
                            $x = ceil(($_new_width - $new_width) / 2);
                            break;
                        default:
                            $x = ceil(($_new_width - $new_width) / 2);
                            $y = ceil(($_new_height - $new_height) / 2);
                    }
                    
                    imagecopyresampled ($gd_dst, $gd_src, $x, $y, 0, 0, $new_width, $new_height, $size[0], $size[1]);
                    
//                    header("Content-type: image/jpeg");
//                    echo imagejpeg($gd_dst, NULL, $this->th_jpeg_quality);
                    
                    ob_start();
                    
                    if ($is_jpg) {
                        imagejpeg($gd_dst, NULL, $this->th_jpeg_quality);
                    } else if ($is_png) {
                        imagepng($gd_dst);
                    } else if ($is_gif) {
                        imagegif($gd_dst);
                    } else {
                        imagejpeg($gd_dst, NULL, $this->th_jpeg_quality);
                    }
                    $cantent = ob_get_contents();
                    file_put_contents($dst, $cantent);

                    ob_end_clean();
                                        
                    imagedestroy($gd_dst);
                }
                imagedestroy($gd_src);
            }
            
            return array($ext, $size['mime'], $cantent);
        }
                
        /**
        * Вычисляются размеры изображения, до которых оно само должно быть уменьшено с учетом пропорций
        *
        * @param array $size - result of getimagesize()
        * @param int &$new_width
        * @param int &$new_height
        */
        function calcImageSize($size, &$new_width, &$new_height, $byLessSide) {
            $small_w = $size[0] <= $new_width;
            $small_h = $size[1] <= $new_height;

            $k = $size[0]/$size[1];

            if ($k > ($new_width/$new_height)) {
                if ($byLessSide) {
                    $new_height = ($small_w) ? $size[1] : ceil($new_width/$k);
                    $new_width  = ($small_w) ? $size[0] : $new_width;
                }
                else {
                    $new_width  = ($small_h) ? $size[0] : ceil($new_height*$k);
                    $new_height = ($small_h) ? $size[1] : $new_height;
                }
            }
            else {
                if ($byLessSide) {
                    $new_width  = ($small_h) ? $size[0] : ceil($new_height*$k);
                    $new_height = ($small_h) ? $size[1] : $new_height;
                }
                else {
                    $new_height = ($small_w) ? $size[1] : ceil($new_width/$k);
                    $new_width  = ($small_w) ? $size[0] : $new_width;
                }
            }
        }

        function image_type_to_extension($imagetype)
        {
            if(empty($imagetype)) return false;
            switch($imagetype)
            {
                case IMAGETYPE_GIF    : return 'gif';
                case IMAGETYPE_JPEG   : return 'jpg';
                case IMAGETYPE_PNG    : return 'png';
                case IMAGETYPE_SWF    : return 'swf';
                case IMAGETYPE_PSD    : return 'psd';
                case IMAGETYPE_BMP    : return 'bmp';
                case IMAGETYPE_TIFF_II : return 'tiff';
                case IMAGETYPE_TIFF_MM : return 'tiff';
                case IMAGETYPE_JPC    : return 'jpc';
                case IMAGETYPE_JP2    : return 'jp2';
                case IMAGETYPE_JPX    : return 'jpf';
                case IMAGETYPE_JB2    : return 'jb2';
                case IMAGETYPE_SWC    : return 'swc';
                case IMAGETYPE_IFF    : return 'aiff';
                case IMAGETYPE_WBMP   : return 'wbmp';
                case IMAGETYPE_XBM    : return 'xbm';
                default               : return false;
            }
        }

        function makePath($s /* = getimagesize()*/, $name ) {
            $folder1 = ROOT_DIR . $this->image_dir . (int)$s[0] . '/';
            $folder2 = ROOT_DIR . $this->image_dir . (int)$s[0] . '/' . (int)$s[1] . '/';
            if( !is_dir( $folder1 ) ) mkdir( $folder1, 0777 );
            if( !is_dir( $folder2 ) ) mkdir( $folder2, 0777 );
            return $folder2 . $name . '.' . $this->image_type_to_extension( $s[2] );
            //return ROOT_DIR . $this->image_dir . $name . '.' . $this->image_type_to_extension( $s[2] );
        }

        function clearTHByURL($url)
        {
            $file = ROOT_DIR.$this->th_dir.$url;
            if (file_exists($file)) unlink($file);
        }

        function thumbnailGenerator(){
            $requestedUrl = ( isset( $_REQUEST['url'] ) ) ? $_REQUEST['url'] : $_SERVER['REDIRECT_URL'];
            $image = $this->imageValidatePath( $requestedUrl );
            if (!$image) $this->nf("Malformed request");
            $sizes = imgServiceSize($image[0]);
            $generator = true;
            list( $x_size, $y_size ) = explode( '@', $image[1] );//get sizes from name
            $src = ROOT_DIR.$this->getImageDir().$x_size.'/'.$y_size.'/'.$image[1].'.'.$image[2];

            if (!is_file($src)) {//file source not found
                if (is_file(ROOT_DIR.$this->getImageDir().'null-'.$image[0].'.jpg')) {
                    $src = ROOT_DIR.$this->getImageDir().'null-'.$image[0].'.jpg';
                } else {
                    $src = ROOT_DIR.$this->getImageDir()."null.jpg";
                }
                $generated = array('jpg','image/jpeg');

                //проверить существует ли null.jpg и если его нет сгенерировать программно
                if (!is_file($src)) {
                    $dst = ROOT_DIR.$this->imagePattern( $image[0], 'null', 'jpg' );                    
                    $this->nullGenerate( $sizes, ROOT_DIR.$this->getImageDir().'null-'.$image[0].'.jpg' );	
                } else {
                    Redirect(sprintf('%s/%s', SITE_PUBLIC_PATH, $this->getImageDir().'null-'.$image[0].'.jpg'));
                    //                    header("Content-type: ".$generated[1]);
                    //                    header("Content-length: ".filesize($src));                    
                    //                    $fp=fopen($src,'rb');
                    //                    fpassthru($fp);
                    //                    fclose($fp);
                    //                    die;
                }
            }
            else {
                $dst = ROOT_DIR.$this->imagePattern($image[0],$image[1],$image[2], $image[3]);
                // если картинка уже существует, то отдаем правильный content-type
                $generated = array($image[2],'image/'.str_replace('jpg','jpeg',$image[2]));
            }

            if ($generator && !file_exists($dst)){

                $e = explode('/',$dst);
                if( !is_dir( $folder = ROOT_DIR . $this->th_dir ) ) mkdir( $folder , 0777 );
                if( !is_dir( $folder = $folder . $e[count($e)-4] . '/' ) ) mkdir( $folder, 0777 );
                if( !is_dir( $folder = $folder . $e[count($e)-3] . '/' ) ) mkdir( $folder, 0777 );
                if( !is_dir( $folder = $folder . $e[count($e)-2] ) ) mkdir( $folder, 0777 );

                // проверка кривых картинок, у которых расширение не соответствует внутреннему типу
                $origtype = $this->getImageType($src, $git);
                $origext = $this->image_type_to_extension($origtype);

                if ($origext != $image[2]) {
                    $checkpath = $this->imagePattern( $image[0], $image[1], $origext , $image[3]);
                    if (file_exists(ROOT_DIR.$checkpath)) { header( "Location: /$checkpath" ); exit; }
                }
                // @todo чтобы избежать паралелльного ресайза, надо положить в $dst пустой файл
                // ::не надо иначе кто-то увидит пустой файл вместо картинки

                $params = (isset($sizes[4]) && is_array($sizes[4])) ? $sizes[4] : array();

                // get size (width, height) for original image
                if ($image[0] == 0 && is_file($src)){
                    list($widthOriginal, $heightOriginal) = getimagesize($src);
                    $sizes[0] = $widthOriginal;
                    $sizes[1] = $heightOriginal;
                }

                $generated = $this->resizeImage($src, $dst, $sizes[0], $sizes[1], $sizes[2], $sizes[3], $params );

                if (!$generated) { die('file generating error'); }
                else{
                    //пишем в бд информацию о тумбочке
                    $this->addThToDB( $dst, $image[1], $image[0] );
                }

                if ($origext != $image[2]) {
                    if (file_exists(ROOT_DIR.$dst)) { header("Location: /$dst"); exit; }
                }
            }

            if (!empty($this->images_cache_lifetime)) {
                header(sprintf('Cache-Control: max-age=%s', $this->images_cache_lifetime), true);
                header(sprintf('Expires: %s GMT', gmdate("D, d M Y H:i:s", time() + $this->images_cache_lifetime)), true);
                header(sprintf('Last-Modified: %s GMT', gmdate("D, d M Y H:i:s")));
                header('Pragma: cache');
            }

            header("Content-type: ".$generated[1]);
            if (empty($generated[2])) {
                header("Content-length: ".filesize($dst));
                $fp=fopen($dst,'rb');
                fpassthru($fp);
                fclose($fp);
            } else {
                echo $generated[2];
            }
            exit;
        }

        function nf($reason) { header("HTTP/1.1 400 Bad Request"); exit($reason); }

        //генерируем "нулевое" изображение нужных размеров
        function nullGenerate( $sizes/*array*/ , $save_file = NULL){
            if (empty($sizes[0]) || empty($sizes[1])) {
                $sizes[0] = 1;
                $sizes[1] = 1;
            }
            $im = @imagecreate( $sizes[0], $sizes[1] ) or die("Cannot Initialize new GD image stream");
            $background_color = imagecolorallocate( $im, 255, 255, 255 );
            $border_color = imagecolorallocate( $im, 100, 100, 100 );

            for($i=0; $i<(sqrt($sizes[0]*$sizes[1])/5); $i++){
                $text_color = imagecolorallocate( $im, rand(0,255), rand(0,255), rand(0,255) );
                imagestring( $im, $font_size = 5, rand(-100,$sizes[0]+100), rand(-20,$sizes[1]+20), "Sorry. No image...", $text_color );
            }

            imageRectangle($im, 0, 0, $sizes[0]-1, $sizes[1]-1, $border_color);

            header("Content-type: image/png");
            imagepng($im, $save_file);
            imagedestroy($im);
            die;
        }

        /*
        * add image to garbage table by ID
        */
        function setImageToGarbageById($idx = 0){
            if(!empty($idx)){
                $r = $this->dsp->db->Execute( 'insert into images_garbage(id, createdate) values (?, NOW())', $idx);
            }

            if($this->dsp->db->last_errno) { 
                return 'db error';
            }
            else {
                return false;
            }
        }

        /**
        * 
        * Clear data from image garbage(images_garbage) table from DB
        * 
        * @param integer $idx
        *   id of image from 'image' table to delete it from garbage table
        * @param boolean $is_date
        *   if TRUE then add cause with checking to delete old records by image,
        *   if false then delete by id
        */
        function clearImageToGarbageByCause($idx = 0, $is_date = false){

            $sql = 'delete from images_garbage where ';
            $images = array();
            // set first cause = delete images from garbage table and clean images from 
            // server
            if($is_date) {
                // set cause for select
                $cause = " TIMESTAMPADD(HOUR, " . ADMIN_GARBAGE_IMAGE_TIME_LIVE . ", createdate)<'" .date("Y-m-d H:i:s")."'";

                // get images to delete from DB garbage table
                $sql_all = 'SELECT id FROM images_garbage WHERE ' . $cause;
                $images = $this->dsp->db->Select( $sql_all );

                // clean images from table 'images'
                foreach ($images as $image) {
                    $this->clearByIDX($image['id']);
                }
                $sql .= $cause;

                // delete images from 'images_garbage' table
                $r = $this->dsp->db->Execute( $sql );
            }
            // simply clean images from garbage table
            else if(!empty($idx) && IsInt($idx)>0) {

                $sql .= " id=? ";
                $variable = (int)$idx;
                $r = $this->dsp->db->Execute( $sql, $idx);
            }

            if($this->dsp->db->last_errno) { 
                return 'db error';
            }
            else {
                return false;
            }
        }

        // small news garbage
        function getSizesForRedaktor(){
            $sizes = array();
            foreach( $GLOBALS['isSizes'] as $i=>$s ){
                if((int)$i >= 2000 && (int)$i < 2999) $sizes[$i] = $s;
            }
            return $sizes;
        }

        /**
        * 
        * Set error message in session admin_error,
        * 
        * @param string $errorText
        */
        private function _returnError($errorText = ''){
            $this->dsp->session->SetParam('admin_error' , array($errorText));
        }

        public function getImageSize($idx = 0)
        {
            $sizes = false;
            $match = array();

            if ( $idx )
            {
                $image = $this->dsp->db->SelectRow( 'select `width`,`height` from images where idx = ?', (int)$idx );

                if ( $image['width'] && $image['height'] )
                {
                    $sizes = array('width' => $image['width'], 'height' => $image['height']);
                }
            }

            return $sizes;
        }

        public function setUser ( $idx, $user_id )
        {
            $r = $this->dsp->db->Execute( 'UPDATE `images` SET `user_id` = ? WHERE `idx` = ?', $user_id, $idx );
        }

        public function getFileFromArray($_f, $name)
        {
            $f = array();
            $f['name'] = $_f['name'][$name];
            $f['type'] = $_f['type'][$name];
            $f['tmp_name'] = $_f['tmp_name'][$name];
            $f['error'] = $_f['error'][$name];
            $f['size'] = $_f['size'][$name];
            return $f;
        }

        public function getFileFromArray2($_f, $name, $st)
        {
            $f = array();
            $f['name'] = $_f['name'][$st][$name];
            $f['type'] = $_f['type'][$st][$name];
            $f['tmp_name'] = $_f['tmp_name'][$st][$name];
            $f['error'] = $_f['error'][$st][$name];
            $f['size'] = $_f['size'][$st][$name];
            return $f;
        }
    }

    /*
    -- таблица с картинками для мускула
    SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

    CREATE TABLE IF NOT EXISTS `images` (
    `idx` int(11) NOT NULL auto_increment,
    `name` varchar(255) NOT NULL,
    `type` int(2) unsigned NOT NULL,
    `width` int(5) unsigned NOT NULL,
    `height` int(5) unsigned NOT NULL,
    `ts` timestamp NOT NULL default CURRENT_TIMESTAMP,
    `th` tinyint(1) NOT NULL,
    `weight` bigint(20) NOT NULL,
    `full_key` varchar(255) NOT NULL,
    `orig_name` text NOT NULL,
    PRIMARY KEY  (`idx`),
    UNIQUE KEY `name` (`name`,`type`,`width`,`height`,`th`),
    UNIQUE KEY `full_key` (`full_key`),
    KEY `idx` (`idx`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=82 ;
    */


