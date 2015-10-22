<?php
class Images {
    
    function GetParams($image_file, $abs_path = false) {
        if (!$abs_path) $image_file = IMAGE_DIR . $image_file;
        
        if (is_file($image_file)) {
            $res = array();
            $params = getimagesize($image_file);
            if ($params === false) return false;
            
            $res['w'] = $params[0];
            $res['h'] = $params[1];
            list($img_sig, $res['ext']) = explode('/', $params['mime']);
        } else {
            return false;
        } // if
        
        return $res;
    } // GetParams()   
    
    function MakeName($filaname, $params = false) {
        if ($params == false) {
            $params = $this->GetParams($filaname);
            if ($params == false) return '';
        }
        
        $name_part = dechex(crc32($filaname));
        list($msec, $sec) = explode(' ', microtime());
        $msec = str_replace('0.', '', $msec);
        $time_parts = dechex($msec) . dechex($sec);
        $result =  $params['w'] . '_' . $params['h'] . '/' . $time_parts . '' . $name_part . '.' . $params['ext'];
        if (is_file(IMAGE_DIR . $result)) $result = $this->MakeName($result, $params);
          
        return $result;
    } // Makename()
    
    function MakeThumbName($filaname, $width, $height) {
        $finfo = pathinfo($filaname);
        $dirname = end(explode('/', $finfo['dirname']));
        $fname = $finfo['basename'];
        $result = $width . '_' . $height . '/' . $fname;
        return $result;
    } // MakeThumbName()

    function GetSizeByName($filaname) {
        $finfo = pathinfo($filaname);
        $dirname = reset(explode('/', $finfo['dirname']));
        list($width, $height) = explode('_', $dirname);
        $width = $width + 0;
        $height = $height + 0;
        return array($width, $height);
    } // GetSizeByName()

    function ResizeImage($filename, $width, $height, $canresize = false, $byshort = true, $arrange = 1, $bgcolor = 'FFFFFF', $make = true, $force = false) {
        list($old_width, $old_height) = $this->GetSizeByName($filename);
        $dx = $width / $old_width;
        $dy = $height / $old_height;
        if ($byshort) { // crop
            $scale = min($dx, $dy);
        } else {
            $scale = max($dx, $dy);
        }
        $new_width = round($old_width * $scale);
        $new_height = round($old_height * $scale);
        
        if ($canresize) {
            if ($height != $new_height) $height = $new_height;
            if ($width != $new_width) $width = $new_width;
        }
         
        if ($height != $new_height) {
            list($src_y, $src_h, $dst_y, $dst_h) = $this->_calc_copy_params($old_height, $height, $scale, $arrange);
        } else {
            $src_y = 0;
            $src_h = $old_height;
            $dst_y = 0;
            $dst_h = $height;
        }
        
        if ($width != $new_width) {
            list($src_x, $src_w, $dst_x, $dst_w) = $this->_calc_copy_params($old_width, $width, $scale, $arrange);
        } else {
            $src_x = 0;
            $src_w = $old_width;
            $dst_x = 0;
            $dst_w = $width;
        }
        
        $finfo = pathinfo($filename);
        $ext = $finfo['extension'];
        
        $new_filename = $width . '_' . $height . '/' . $finfo['basename'];
        if (!is_file(IMAGE_DIR . $filename)) {
            return '1_1/1.gif';
        }

        if ($make && (!is_file(IMAGE_DIR . $new_filename) || $force)) {
            switch ($ext) {
                case 'jpeg' : 
                    $old_image = imagecreatefromjpeg(IMAGE_DIR . $filename);
                    break;
    
                case 'gif' : 
                    $old_image = imagecreatefromgif(IMAGE_DIR . $filename);
                    break;
    
                case 'png' : 
                    $old_image = imagecreatefrompng(IMAGE_DIR . $filename);
                    break;
                
                default:
           } // switch
            
            
            // Make new image
            $image = imagecreatetruecolor($width, $height);
            $r = hexdec(substr($bgcolor, 0, 2));
            $g = hexdec(substr($bgcolor, 2, 2));
            $b = hexdec(substr($bgcolor, 4, 2));
            $bgColor = imagecolorallocate($image, $r, $g, $b);
            imagefilledrectangle($image, 0, 0, $width - 1, $height - 1, $bgColor);    
    
            imagecopyresized($image, $old_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
            
            $finfo = pathinfo($new_filename);
            if (!is_dir(IMAGE_DIR . $finfo['dirname'])) {
                mkdir(IMAGE_DIR . $finfo['dirname'], 0644, true);
                chmod(IMAGE_DIR . $finfo['dirname'], 0777);
            }
            
            switch ($ext) {
                case 'jpeg' : 
                    $old_image = imagejpeg($image, IMAGE_DIR . $new_filename);
                    break;
    
                case 'gif' : 
                    $old_image = imagegif($image, IMAGE_DIR . $new_filename);
                    break;
    
                case 'png' : 
                    $old_image = imagepng($image, IMAGE_DIR . $new_filename);
                    break;
            } // switch
        } // if (make))
            
        return $new_filename;
    } // ResizeImage()
    
    
    function _calc_copy_params($actual_size, $new_size, $scale, $arrange) {
        $required_size = round($new_size / $scale);
        
        $src_copy_size = min($actual_size, $required_size);
        $dst_copy_size = round($src_copy_size * $scale);
        
        switch ($arrange) {
            case 0 : // left bound
                $src_copy_from = 0;
                $dst_copy_to   = 0;

                break;
                
            case 1 : // centered
                $ds = round(($required_size - $actual_size) / 2);
                if ($required_size > $actual_size) { // $ds - positive
                    $src_copy_from = 0;
                    $dst_copy_to = round($ds * $scale);
                } else { // $ds - negative
                    $src_copy_from = -$ds;
                    $dst_copy_to = 0;
                }
                
                break;
                
            case 2 : // right bound
                if ($required_size > $actual_size) {
                    $src_copy_from = 0;
                    $dst_copy_to = round(($required_size - $actual_size) * $scale);
                } else {
                    $src_copy_from = ($actual_size - $required_size);
                    $dst_copy_to = 0;
                }
        } // switch
        
        return array($src_copy_from, $src_copy_size, $dst_copy_to, $dst_copy_size);
    } // _calc
   
    function ResizeImagePercent($filename, $percent) {
        list($old_width, $old_height) = $this->GetSizeByName($filename);
        $width = round($old_width * $percent / 100);
        $height = round($old_width * $percent / 100);
        
        $finfo = pathinfo($filename);
        $ext = $finfo['extension'];
        $new_filename = $this->MakeThumbName($filename, $width, $height);
        $finfo = pathinfo($new_filename);
        if (!is_dir(IMAGE_DIR . $finfo['dirname'])) {
            mkdir(IMAGE_DIR . $finfo['dirname'], 0777, true);
            chmod(IMAGE_DIR . $finfo['dirname'], 0777);
        }
        
        switch ($ext) {
            case 'jpeg' : 
                $old_image = imagecreatefromjpeg(IMAGE_DIR . $filename);
                break;

            case 'gif' : 
                $old_image = imagecreatefromgif(IMAGE_DIR . $filename);
                break;

            case 'png' : 
                $old_image = imagecreatefrompng(IMAGE_DIR . $filename);
                break;
        } // switch
        
        $image = imagecreatetruecolor($width, $height);

        imagecopyresized($image, $old_image, 0, 0, 0, 0, $width, $height, $old_width, $old_height);
        
        switch ($ext) {
            case 'jpeg' : 
                $old_image = imagejpeg($image, IMAGE_DIR . $new_filename);
                break;

            case 'gif' : 
                $old_image = imagegif($image, IMAGE_DIR . $new_filename);
                break;

            case 'png' : 
                $old_image = imagecreatefrompng($image, IMAGE_DIR . $new_filename);
                break;
        } // switch
        
        return $new_filename;
    } // ResizeImagePercent()
    
    
    function PlaceUploadedFile($name) {
       if (!empty($_FILES[$name]) && is_uploaded_file($_FILES[$name]['tmp_name']) && ($_FILES[$name]['error'] == UPLOAD_ERR_OK)) {
            $params = $this->GetParams($_FILES[$name]['tmp_name'], true);
            if ($params == false) {
                return false;
            }
            $fname = $this->MakeName($_FILES[$name]['name'], $params);
            $finfo = pathinfo($fname);
            if (!is_dir(IMAGE_DIR . $finfo['dirname'])) {
                mkdir(IMAGE_DIR . $finfo['dirname'], 0777, true);
                chmod(IMAGE_DIR . $finfo['dirname'], 0777);
            }
            move_uploaded_file($_FILES[$name]['tmp_name'], IMAGE_DIR . $fname);
            return $fname;
        } else {
            return false;
        }
    } // PlaceUploadedFile()
    
} // class Images
