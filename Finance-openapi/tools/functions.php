<?php

function utf8_to_unicode_str($utf8){
    $return = '';
    for ($i = 0; $i < mb_strlen($utf8); $i++) {
        $char = mb_substr($utf8, $i, 1);
        // 3字节是汉字，不转换，4字节才是emoji
        if (strlen($char) > 3) {
            $char = trim(json_encode($char), '"');
        } else{
        	$char = unicode_encode($char);
        }
        $return .= $char;
    }
    return $return;
}

function unicode_encode($contents){
    $contents = iconv('UTF-8', 'UCS-2BE', $contents);
    $len = strlen($contents);
    $str = '';
    for($i=0;$i<$len-1;$i=$i+2){
        $c = $contents[$i];
        $c2 = $contents[$i + 1];
        if(ord($c) > 0){
            // 两个字节的文字
            // echo strlen(base_convert(ord($c), 10, 16));
            // echo strlen(base_convert(ord($c2), 10, 16));exit;
            $a = base_convert(ord($c), 10, 16);
            $b = base_convert(ord($c2), 10, 16);
            if(strlen($a) == '1'){
                $a = '0' . base_convert(ord($c), 10, 16);
            }
            if(strlen($b) == '1'){
                $b = '0' . base_convert(ord($c2), 10, 16);
            }
            $str .= '\u'.$a.$b;
        } else{
            $str .= $c2;
        }
    }
    return $str;
}
