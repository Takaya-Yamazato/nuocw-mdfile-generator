<html>
<head><title>PHP TEST</title></head>
<body>

<?php

$conn = "host=localhost dbname=ocwpdb-u user=ocwuser password=passwd";
$link = pg_connect($conn);
if (!$link) {
    die('接続失敗です。'.pg_last_error());
}

print('接続に成功しました。<br>');

// PostgreSQLに対する処理

$close_flag = pg_close($link);

if ($close_flag){
    print('切断に成功しました。<br>');
}

?>
</body>
</html>