<?php

// OCWVAR クラス
//  - 変数にかかわるクラス
//
//  - isId($id)

class OCWVAR
{

    // bool isId(mixed $id)
    //  - ID かどうか.
    public static function isId($id)
    {
        // 空の文字列ではない, 数字からなる文字列の場合 true を返す.
       return (ctype_digit("$id") && "$id" != '') ? true : false;
    }

    // string timeZoneOfDay()
    //  - 時間帯により、01, 02, 03 を返す。
    public static function timeZoneOfDay()
    {
        $hour = date('G');

        if (4 <= $hour && $hour <= 14) {
            return '01';
        } elseif (15 <= $hour && $hour <= 18) {
            return '02';
        } else {
            return '03';
        }
    }
}
