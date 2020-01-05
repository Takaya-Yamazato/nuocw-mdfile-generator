<html>
<head><title>PHP TEST</title></head>
<body>

<?php

require_once('./lib/ocw_init.php');

// DBに接続
$ocwpdb = pg_connect(ocwpdb);
if (!ocwpdb) {
    die('ocwpdb：接続失敗です。'.pg_last_error());
}
print('ocwpdb：接続に成功しました。<br>');

// 出力ソートキー
$sort_key = "course_id";
// $sort_key = "41" ;
$sort_order = "ASC";

// SQL文の作成
$courselist_sql = "SELECT course_id, course_name, instructor_name, year, publish_group_abbr, date, department_id, instructor_id, vsyllabus_id, url_flv 
        FROM courselist_by_coursename
        ORDER BY $sort_key $sort_order
        LIMIT 5 ";
//        WHERE course_id=41

print($courselist_sql) ;
echo "<br><br>";

$courselist_result = pg_query($courselist_sql);
    if (!$courselist_result) {
        die('クエリーが失敗しました。'.pg_last_error());
    }

// DBの切断
$close_ocwpdb = pg_close($ocwpdb);
if ($close_ocwpdb){
    print('ocwpdb：切断に成功しました。<br><br>');
    }
 
// DBの接続
$ocwdb = pg_connect(ocwdb);
if (!ocwdb) {
    die('ocwdb：接続失敗です。'.pg_last_error());
}
print('ocwdb：接続に成功しました。<br>');


for ($i = 0 ; $i < pg_num_rows($courselist_result) ; $i++){
    $courselist_rows = pg_fetch_array($courselist_result, NULL, PGSQL_ASSOC);
    print_r($courselist_rows);
    //    echo $courselist_rows['contents'][0];
    //    echo $courselist_rows['course_id'][0];

    // 書き込みモードでファイルを開く
    $file_name = $courselist_rows['course_name'].".md" ;
    echo "<br>".$file_name."<br>" ;
    $fp = fopen($file_name, "w");

// 出力ソートキー
$sort_key = "course_id";
$sort_key = $courselist_rows['course_id'] ;
$sort_order = "DESC";

// SQL文の作成
$course_sql = "SELECT contents.contents, course.term, course.course_id 
        FROM course 
        INNER JOIN pages ON course.course_id = pages.course_id 
        INNER JOIN page_contents ON pages.page_id = page_contents.page_id 
        INNER JOIN contents ON page_contents.contents_id = contents.pid 
        INNER JOIN page_type_master ON page_type_master.page_type_code = pages.page_type 
        WHERE course.course_id = $sort_key " ;

$course_result = pg_query($course_sql);
if (!$course_result) {
    die('クエリーが失敗しました。'.pg_last_error());
}
    
for ($j = 0 ; $j < pg_num_rows($course_result) ; $j++){
$course_rows = pg_fetch_array($course_result, NULL, PGSQL_ASSOC);
print_r($course_rows);
//    echo $contents_rows['contents'][0];
//    echo $contents_rows['course_id'][0];
}

print("page_type_code |     name     |        name_e         |     filename     | disp_order<br> 
  51             | 授業ホーム   | Course Home           | index            |        510 <br><br>");
 
$contents_sql = "SELECT contents.id, pages.course_id, contents.contents
                FROM pages, page_contents, contents 
                WHERE pages.course_id = $sort_key 
                AND pages.page_type = '51' AND pages.page_id = page_contents.page_id 
                AND contents.pid = page_contents.contents_id AND contents.type = '1301' 
                ORDER BY contents.id DESC LIMIT 1 ";


print($contents_sql) ;
echo "<br><br>";

$contents_result = pg_query($contents_sql);
    if (!$contents_result) {
        die('クエリーが失敗しました。'.pg_last_error());
    }
        
for ($k = 0 ; $k < pg_num_rows($contents_result) ; $k++){
    $contents_rows = pg_fetch_array($contents_result, NULL, PGSQL_ASSOC);
    print_r($contents_rows);
//    echo $contents_rows['contents'][0];
//    echo $contents_rows['course_id'][0];
  
echo "<br><br>";
    
print('course_id='.$courselist_rows['course_id'].'<br>');
print('course_name='.$courselist_rows['course_name'].'<br>');
print('year='.$courselist_rows['year'].'<br>');
print('publish_group_abbr='.$courselist_rows['publish_group_abbr'].'<br>');
print('date='.$courselist_rows['date'].'<br>');
print('department_id='.$courselist_rows['department_id'].'<br>');
print('instructor_id='.$courselist_rows['instructor_id'].'<br>');
print('vsyllabus_id='.$courselist_rows['vsyllabus_id'].'<br>');
print('url_flv='.$courselist_rows['url_flv'].'<br>');
    
echo "<br><br>";

    echo "<br><br>";
    print('course.course_id='.$contents_rows['course_id'].'<br>');
    print('contents.contents='.$contents_rows['contents'].'<br>');
    print(mb_substr($contents_rows['contents'],0,100).'<br>');
    echo "<br><br>";
 
    $courselist_text =
"---
# テンプレート指定
templateKey: \"courses\"

# タイトル
title: \"".$courselist_rows['course_name']."\"

# 簡単な説明
description: >-
".mb_substr($contents_rows['contents'],0,100)."...

# 講師名
lecturer: \"".$courselist_rows['instructor_name']."\"

# 部局名
department: \"".$courselist_rows['department_id']."\"

# 開講時限
term: \"".$courselist_rows['department_id']."\"

# 対象者
target: 

# 授業回数
classes: 

# 単位数
credit: 

# pdfなどの追加資料
attachments:


# 関連するタグ
tags:

# 色付けのロールにするか
featuredpost: true

# ロールに表示する画像
featuredimage: /img/chemex.jpg

# 記事投稿日
date: ".$courselist_rows['date']."

---

### 授業ホーム
".$contents_rows['contents']."

    " ;
    // ファイルに書き込む
    fwrite($fp,$courselist_text);
 
    // ファイルを閉じる
    fclose($fp);

}



}

 // DBの切断        
$close_ocwdb  = pg_close($ocwdb);
if ($close_ocwdb){
    print('ocwdb：切断に成功しました。<br>');
    }

?>
</body>
</html>