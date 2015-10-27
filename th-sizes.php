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
        2=>array(100,100,0,0),
    );


    function imgServiceSize($s) {
        return (isset($GLOBALS['isSizes'][$s])?$GLOBALS['isSizes'][$s]:(is_null($s)?$GLOBALS['isSizes']:false));
    }


/*


{
    "media": [
        {
            "type" : "text",
            "text" : "\u0422\u0435\u0441\u0442\u0438\u0440\u0443\u0435\u043c-\u0441 \u0430\u0432\u0442\u043e\u043f\u043e\u0441\u0442\u0438\u043d\u0433-\u0441, \u0412\u0430\u0448\u0435 \u0411\u043b\u0430\u0433\u043e\u0440\u043e\u0434\u0438\u0435!"
        },
        {
            "type" : "photo",
            "list" : {
                "id" : "ULnNGjVGlC0tH7gnOu1VisA5B6noDoM9YLmYfZQjM5RrtM5ZCMthtvr+z0HDI\/ZxLaXQWf+eCSm0Wisf+toChYfE8x+5Ajj5JkEXk3TjunU8ODLPC\/HLWjXE67G+HsIn03Ow+paOPAV+plvc9F67TQ=="
            }
        }
    ]
}












 */