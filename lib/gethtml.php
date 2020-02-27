#!/usr/bin/php
<?php
// コマンドライン専用プログラム
// 指定URLのHTMLをそのまま返す。
require_once('HTTP/Client.php');
if ($argc < 1) {
    echo "no url\n";
    return 1;
}
$url = $argv[1];

$client = new HTTP_Client();
$client->get($url);
$res = $client->currentResponse();
mb_http_output('pass');
echo $res['body'];

return 0;

?>