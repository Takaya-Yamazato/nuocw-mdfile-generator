<?php
/** config.php
 *
 * nuocw-mdfile-generator で利用する config です
 * ocwpdb および ocwdb の HOSTNAME, USERNAME, PASSWORD を設定します。
 * 
 **/

mb_internal_encoding('UTF-8');

// コースデータ
// 以下の大文字の HOSTNAME, USERNAME, PASSWORD を設定してください。
define('ocwpdb', 'host=HOSTNAME dbname=ocwpdb-u user=USERNAME password=PASSWORD');
define('ocwdb' , 'host=HOSTNAME dbname=ocwdb-u  user=USWERNAME password=PASSWORD');

function space_trim ($str) {
    // 行頭の半角、全角スペースを、空文字に置き換える
    $str = preg_replace('/^[ 　]+/u', '', $str);
 
    // 末尾の半角、全角スペースを、空文字に置き換える
    $str = preg_replace('/[ 　]+$/u', '', $str);
 
    return $str;
}


function mbTrim($pString)
{
    // 不要な制御文字を削除
    return preg_replace('/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', '', $pString);
}


//　メモ：
// 改行の削除
// $text = preg_replace('/(?:\n|\r|\r\n)/', '', $text );
