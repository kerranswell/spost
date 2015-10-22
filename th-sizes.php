<?php
    /**
    * size: (width, height, crop, whitemargin [, array(gravity=>north, rotate=2 [,?bw?=>1])])
    *
    *  crop  whitemargin  result
    *    0            0  ресайз без изменения соотношения сторон исходной картинки
    *    0            1  дополнение предыдущего варианта белыми полями до указанных размеров
    *    1            0  обрезка до указанных размеров с выравниванием по центру
    */
    $GLOBALS['isSizes'] = array(
        0=>array('original'), // original
        1=>array(400,400,0,0),
    );


    function imgServiceSize($s) {
        return (isset($GLOBALS['isSizes'][$s])?$GLOBALS['isSizes'][$s]:(is_null($s)?$GLOBALS['isSizes']:false));
    }
