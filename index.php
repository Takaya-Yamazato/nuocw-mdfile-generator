<html>
<head><title>PHP TEST</title></head>
<body>

<?php

require_once('./lib/ocw_init.php');

$conn = "host=localhost dbname=ocwdb-u user=ocwuser password=passwd";
$link = pg_connect($conn);
if (!$link) {
    die('接続失敗です。'.pg_last_error());
}

print('接続に成功しました。<br>');

// SQL文の作成
$sql = "SELECT contents.contents, course.course_id 
        FROM course 
        INNER JOIN pages ON course.course_id = pages.course_id 
        INNER JOIN page_contents ON pages.page_id = page_contents.page_id 
        INNER JOIN contents ON page_contents.contents_id = contents.pid 
        INNER JOIN page_type_master ON page_type_master.page_type_code = pages.page_type 
        WHERE course.course_id = '41' " ;
print($sql) ;
echo "<br><br>";

$result = pg_query($sql);
    if (!$result) {
        die('クエリーが失敗しました。'.pg_last_error());
    }
        
for ($i = 0 ; $i < pg_num_rows($result) ; $i++){
    $rows = pg_fetch_array($result, NULL, PGSQL_ASSOC);
    print_r($rows);
    echo "<br><br>";
    print('course.course_id='.$rows[0]);
    print(',contents.contents='.$rows[1].'<br>');
        }
        
$close_flag = pg_close($link);
        
if ($close_flag){
    print('切断に成功しました。<br>');
    }

?>
</body>
</html>