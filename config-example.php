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


