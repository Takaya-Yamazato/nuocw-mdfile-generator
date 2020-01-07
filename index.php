<html>
<head><title>nuocw-mdfile-generator</title></head>
<body>

<?php

require_once('./config.php');

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
        WHERE exist_lectnotes='t'
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

    //
// ここから ocwdb　への接続
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
// $sort_key = "course_id";
$sort_key = $courselist_rows['course_id'] ;
$sort_order = "DESC";

// SQL文の作成
// $course_sql = "SELECT contents.contents, course.term, course.course_id 
//         FROM course 
//         INNER JOIN pages ON course.course_id = pages.course_id 
//         INNER JOIN page_contents ON pages.page_id = page_contents.page_id 
//         INNER JOIN contents ON page_contents.contents_id = contents.pid 
//         INNER JOIN page_type_master ON page_type_master.page_type_code = pages.page_type 
//         WHERE course.course_id = $sort_key " ;
$course_sql = "SELECT * FROM course WHERE course.course_id = $sort_key " ;
$course_result = pg_query($course_sql);
if (!$course_result) {
    die('クエリーが失敗しました。'.pg_last_error());
}
$course_array = pg_fetch_all($course_result);
echo "<br>course_array<br>" ;
print_r($course_array);
echo "<br>".$course_array[0]['course_name']."<br>" ;
echo "<br>".$course_array[0]['division']."<br>" ;
echo "<br>".$course_array[0]['term']."<br>" ;
$division_code = $course_array[0]['division'] ;
echo $division_code ;
$term_code = $course_array[0]['term'] ;
echo $term_code ;

// 部局 department
$division_code_master_sql = "SELECT division_name 
                            FROM division_code_master 
                            WHERE division_code = '$division_code' ; " ;
$division_code_master_result = pg_query($division_code_master_sql);
if (!$division_code_master_result) {
    die('クエリーが失敗しました。'.pg_last_error());
}
$division_code_master_array = pg_fetch_all($division_code_master_result);
echo "<br>division_code_master_result<br>" ;
print_r($division_code_master_array);
$division = $division_code_master_array[0]['division_name'] ;
echo "<br>".$division."<br>" ;

// 開講時限　term
$term_code_master_sql = "SELECT name 
                            FROM term_code_master 
                            WHERE term_code = '$term_code' ; " ;
$term_code_master_result = pg_query($term_code_master_sql);
if (!$term_code_master_result) {
    die('クエリーが失敗しました。'.pg_last_error());
}
$term_code_master_array = pg_fetch_all($term_code_master_result);
echo "<br>term_code_master_result<br>" ;
print_r($term_code_master_array);
$term = $term_code_master_array[0]['name'] ;
echo "<br>".$term."<br>" ;

// for ($j = 0 ; $j < pg_num_rows($course_result) ; $j++){
// $course_rows = pg_fetch_array($course_result, NULL, PGSQL_ASSOC);
// print_r($course_rows);
// //    echo $contents_rows['contents'][0];
// //    echo $contents_rows['course_id'][0];
// }

// pdfなどの追加資料　Attachments
//$attachments_sql = "SELECT id, name, description, relation_type, relation_id, del_flg
$attachments_sql = "SELECT name
                    FROM file_group 
                    INNER JOIN course 
                    ON course.course_id = file_group.relation_id 
                    WHERE course.course_id = $sort_key 
                    AND del_flg = 'f' ; " ;
$attachments_result = pg_query($attachments_sql);
if (!$attachments_result) {
    die('クエリーが失敗しました。'.pg_last_error());
}
$attachments_array = pg_fetch_all($attachments_result);
if (!$attachments_array){
    echo "データがありません！" ;
    $attachments = "" ;
}else{
    echo "<br>" ;
    print_r($attachments_array);
    echo "<br>" ;
    $attachments = call_user_func_array('array_merge', $attachments_array); 
    print_r($attachments);
    $attaches = "";
    foreach ($attachments_array as $attachment){
        echo $attachment['name']."<br>" ;
        foreach ($attachment as $attach){
        echo $attach."<br>"  ;
        $attaches .= "\n".$attach ;
        }
    }
    
    echo $attaches."これはAttaches<br>" ;
    //$attachments = strip_tags($attachments_array[0]['name']) ;
}


// 1281               | 対象者
$class_is_for_sql = "SELECT contents.contents 
                    FROM pages, page_contents, contents 
                    WHERE pages.course_id = $sort_key 
                    AND pages.page_id = page_contents.page_id 
                    AND contents.pid = page_contents.contents_id 
                    AND contents.type = '1281' 
                    ORDER BY contents.id DESC LIMIT 1; ";

$class_is_for_result = pg_query($class_is_for_sql);
if (!$class_is_for_result) {
    die('クエリーが失敗しました。'.pg_last_error());
}
$class_is_for_array = pg_fetch_all($class_is_for_result);
if (!$class_is_for_array){
    echo "データがありません！" ;
    $class_is_for = "" ;
}else{
    echo "class_is_for_array<br>" ;
    print_r($class_is_for_array);
    $class_is_for = strip_tags($class_is_for_array[0]['contents']) ;
}


// 51             | 授業ホーム   | Course Home           | index            |        510
$course_home_sql = "SELECT contents.contents 
                    FROM pages, page_contents, contents 
                    WHERE pages.course_id = $sort_key 
                    AND pages.page_type = '51' 
                    AND pages.page_id = page_contents.page_id 
                    AND contents.pid = page_contents.contents_id 
                    AND contents.type = '1301' 
                    ORDER BY contents.id DESC LIMIT 1; ";

$course_home_result = pg_query($course_home_sql);
if (!$course_home_result) {
die('クエリーが失敗しました。'.pg_last_error());
}
$course_home_array = pg_fetch_all($course_home_result);
if (!$course_home_array){
    echo "データがありません！" ;
    $course_home ="" ;
}else{
    echo "course_home_array<br>" ;
    print_r($course_home_array);
    $course_home ="### 授業ホーム
    ".strip_tags($course_home_array[0]['contents']) ;
}

// 52             | シラバス     | Syllabus              | syllabus         |        520
$syllabus_sql = "SELECT contents.contents 
                    FROM pages, page_contents, contents 
                    WHERE pages.course_id = $sort_key 
                    AND pages.page_type = '52' 
                    AND pages.page_id = page_contents.page_id 
                    AND contents.pid = page_contents.contents_id 
                    AND contents.type = '1101' 
                    ORDER BY contents.id DESC LIMIT 1; ";

$syllabus_result = pg_query($syllabus_sql);
if (!$syllabus_result) {
die('クエリーが失敗しました。'.pg_last_error());
}
$syllabus_array = pg_fetch_all($syllabus_result);
if(!$syllabus_array){
    echo "データがありません！" ;
    $syllabus ="" ;
}else{
    echo "syllabus_array<br>" ;
    print_r($syllabus_array);
    $syllabus ="### ".strip_tags($syllabus_array[0]['contents']) ;
}


// 53             | スケジュール | Calendar              | calendar         |        530
$calendar_sql = "SELECT contents.contents 
                    FROM pages, page_contents, contents 
                    WHERE pages.course_id = $sort_key 
                    AND pages.page_type = '53' 
                    AND pages.page_id = page_contents.page_id 
                    AND contents.pid = page_contents.contents_id 
                    AND contents.type = '1301' 
                    ORDER BY contents.id DESC LIMIT 1; ";

$calendar_result = pg_query($calendar_sql);
if (!$calendar_result) {
die('クエリーが失敗しました。'.pg_last_error());
}
$calendar_array = pg_fetch_all($calendar_result);
if(!$calendar_array){
    echo "データがありません！" ;
    $calendar = "" ;
}else{
    echo "calendar_array<br>" ;
    print_r($calendar_array);
    $calendar ="### ".strip_tags($calendar_array[0]['contents']) ;
}

// 54             | 講義ノート   | Lecture Notes         | lecturenotes     |        540
 
$lecture_notes_sql = "SELECT contents.contents 
                    FROM pages, page_contents, contents 
                    WHERE pages.course_id = $sort_key 
                    AND pages.page_type = '54' 
                    AND pages.page_id = page_contents.page_id 
                    AND contents.pid = page_contents.contents_id 
                    AND contents.type = '1101' 
                    ORDER BY contents.id DESC LIMIT 1; ";

$lecture_notes_result = pg_query($lecture_notes_sql);
if (!$lecture_notes_result) {
die('クエリーが失敗しました。'.pg_last_error());
}
$lecture_notes_array = pg_fetch_all($lecture_notes_result);
if(!$lecture_notes_array){
    echo "データがありません！" ;
    $lecture_notes = "" ;
}else{
    echo "lecture_notes_array<br>" ;
    print_r($lecture_notes_array);
    $lecture_notes = "### ".strip_tags($lecture_notes_array[0]['contents']) ;
}



// 55             | 課題         | Assignments           | assignments      |        550
 
$assignments_sql = "SELECT contents.contents 
                    FROM pages, page_contents, contents 
                    WHERE pages.course_id = $sort_key 
                    AND pages.page_type = '55' 
                    AND pages.page_id = page_contents.page_id 
                    AND contents.pid = page_contents.contents_id 
                    AND contents.type = '1101' 
                    ORDER BY contents.id DESC LIMIT 1; ";

$assignments_result = pg_query($assignments_sql);
if (!$assignments_result) {
die('クエリーが失敗しました。'.pg_last_error());
}
$assignments_array = pg_fetch_all($assignments_result);
if (!$assignments_array){ 
    echo "データがありません！" ;
    $assignment = "";
}else{
    echo "assignments_array<br>" ;
    print_r($assignments_array);
    $assignment = "### ".strip_tags($assignments_array[0]['contents']) ; }

// 56             | 成績評価     | Evaluation            | evaluation       |        560
$evaluation_sql = "SELECT contents.contents 
                    FROM pages, page_contents, contents 
                    WHERE pages.course_id = $sort_key 
                    AND pages.page_type = '56' 
                    AND pages.page_id = page_contents.page_id 
                    AND contents.pid = page_contents.contents_id 
                    AND contents.type = '1101' 
                    ORDER BY contents.id DESC LIMIT 1; ";

$evaluation_result = pg_query($evaluation_sql);
if (!$evaluation_result) {
die('クエリーが失敗しました。'.pg_last_error());
}
$evaluation_array = pg_fetch_all($evaluation_result);
if($evaluation_array){
    echo "データがありません！" ;
    $evaluation = "";
}else{
    echo "evaluation_array<br>" ;
    print_r($evaluation_array);
    $evaluation = "### ".strip_tags($evaluation_array[0]['contents']) ;    
}

// 57             | 学習成果     | Achievement           | achievement      |        570
 $achievement_sql = "SELECT contents.contents 
                    FROM pages, page_contents, contents 
                    WHERE pages.course_id = $sort_key 
                    AND pages.page_type = '57' 
                    AND pages.page_id = page_contents.page_id 
                    AND contents.pid = page_contents.contents_id 
                    AND contents.type = '1101' 
                    ORDER BY contents.id DESC LIMIT 1; ";

$achievement_result = pg_query($achievement_sql);
if (!$achievement_result) {
die('クエリーが失敗しました。'.pg_last_error());
}
$achievement_array = pg_fetch_all($achievement_result);
if(!$achievement_array){
    echo "データがありません！" ;
    $achievement = "";
}else{
    echo "achievement_array<br>" ;
    print_r($achievement_array);
    $achievement = "### ".strip_tags($achievement_array[0]['contents']) ; 
    
}

// 58             | 参考資料     | Related Resources     | relatedresources |        580
 $related_resources_sql = "SELECT contents.contents 
                        FROM pages, page_contents, contents 
                        WHERE pages.course_id = $sort_key 
                        AND pages.page_type = '58' 
                        AND pages.page_id = page_contents.page_id 
                        AND contents.pid = page_contents.contents_id 
                        AND contents.type = '1101' 
                        ORDER BY contents.id DESC LIMIT 1; ";

$related_resources_result = pg_query($related_resources_sql);
if (!$related_resources_result) {
die('クエリーが失敗しました。'.pg_last_error());
}
$related_resources_array = pg_fetch_all($related_resources_result);
if(!$related_resources_array){
    echo "データがありません！" ;
    $related_resources = "" ;
}else{
    echo "related_resources_array<br>" ;
    print_r($related_resources_array);
    $related_resources = "### ".strip_tags($related_resources_array[0]['contents']) ;    
}

// 59             | 授業の工夫   | Teaching Tips         | teachingtips     |        590
$teaching_tips_sql = "SELECT contents.contents 
                    FROM pages, page_contents, contents 
                    WHERE pages.course_id = $sort_key 
                    AND pages.page_type = '59' 
                    AND pages.page_id = page_contents.page_id 
                    AND contents.pid = page_contents.contents_id 
                    AND contents.type = '1101' 
                    ORDER BY contents.id DESC LIMIT 1; ";

$teaching_tips_result = pg_query($teaching_tips_sql);
if (!$teaching_tips_result) {
die('クエリーが失敗しました。'.pg_last_error());
}
$teaching_tips_array = pg_fetch_all($teaching_tips_result);
if (!$teaching_tips_array){
    echo "データがありません！" ;
    $teaching_tips = "" ;
}else{
    echo "teaching_tips_array<br>" ;
    print_r($teaching_tips_array);
    $teaching_tips = "### ".strip_tags($teaching_tips_array[0]['contents']) ;    
}


// 71             | 最終講義・講義ホーム   | Farewell Lecture Home | f_index          |        515

$farewell_lecture_home_sql = "SELECT contents.contents 
                    FROM pages, page_contents, contents 
                    WHERE pages.course_id = $sort_key 
                    AND pages.page_type = '71' 
                    AND pages.page_id = page_contents.page_id 
                    AND contents.pid = page_contents.contents_id 
                    AND contents.type = '1101' 
                    ORDER BY contents.id DESC LIMIT 1; ";

$farewell_lecture_home_result = pg_query($farewell_lecture_home_sql);
if (!$farewell_lecture_home_result) {
die('クエリーが失敗しました。'.pg_last_error());
}
$farewell_lecture_home_array = pg_fetch_all($farewell_lecture_home_result);
if ($farewell_lecture_home_array){
    echo "データがありません！" ;
    $farewell_lecture_home = "";
}else{
    echo "farewell_lecture_home_array<br>" ;
    print_r($farewell_lecture_home_array);
    $farewell_lecture_home = "### ".strip_tags($farewell_lecture_home_array[0]['contents']) ;
    
}

// 72             | 最終講義・講師紹介     | Introduction          | f_intro          |        525
$farewell_lecture_introduction_sql = "SELECT contents.contents 
                    FROM pages, page_contents, contents 
                    WHERE pages.course_id = $sort_key 
                    AND pages.page_type = '72' 
                    AND pages.page_id = page_contents.page_id 
                    AND contents.pid = page_contents.contents_id 
                    AND contents.type = '1101' 
                    ORDER BY contents.id DESC LIMIT 1; ";

$farewell_lecture_introduction_result = pg_query($farewell_lecture_introduction_sql);
if (!$farewell_lecture_introduction_result) {
die('クエリーが失敗しました。'.pg_last_error());
}
$farewell_lecture_introduction_array = pg_fetch_all($farewell_lecture_introduction_result);
if ($farewell_lecture_introduction_array){
    echo "データがありません！" ;
    $farewell_lecture_introduction = "" ;
}else{
    echo "farewell_lecture_introduction_array<br>" ;
    print_r($farewell_lecture_introduction_array);
    $farewell_lecture_introduction = "### ".strip_tags($farewell_lecture_introduction_array[0]['contents']) ;
        
}

// 73             | 最終講義・講義資料     | Resources             | f_resources      |        585
$farewell_lecture_resources_sql = "SELECT contents.contents 
                    FROM pages, page_contents, contents 
                    WHERE pages.course_id = $sort_key 
                    AND pages.page_type = '73' 
                    AND pages.page_id = page_contents.page_id 
                    AND contents.pid = page_contents.contents_id 
                    AND contents.type = '1101' 
                    ORDER BY contents.id DESC LIMIT 1; ";

$farewell_lecture_resources_result = pg_query($farewell_lecture_resources_sql);
if (!$farewell_lecture_resources_result) {
die('クエリーが失敗しました。'.pg_last_error());
}
$farewell_lecture_resources_array = pg_fetch_all($farewell_lecture_resources_result);
if ($farewell_lecture_resources_array){
    echo "データがありません！" ;
    $farewell_lecture_resources = "" ;
}else{
    echo "farewell_lecture_resources_array<br>" ;
    print_r($farewell_lecture_resources_array);
    $farewell_lecture_resources = "### ".strip_tags($farewell_lecture_resources_array[0]['contents']) ;    
}

  
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

    // echo "<br><br>";
    // print('course.course_id='.$contents_rows['course_id'].'<br>');
    // print('contents.contents='.$contents_rows['contents'].'<br>');
    // print(mb_substr($contents_rows['contents'],0,100).'<br>');
    // echo "<br><br>";
 
    $courselist_text =
"---
# テンプレート指定
templateKey: \"courses\"

# コースID
course_id: \"".$sort_key."\"

# タイトル
title: \"".$courselist_rows['course_name']."\"

# 簡単な説明
description: >-
".strip_tags(mb_substr($course_home_array[0]['contents'],0,100))." ...

# 講師名
lecturer: \"".$courselist_rows['instructor_name']."\"

# 部局名
department: \"".$division."\"

# 開講時限
term: \"".$term."\"

# 対象者、単位数、授業回数
target: \"".$class_is_for."\"

# 授業回数
classes: 

# 単位数
credit: 

# pdfなどの追加資料
attachments: \"".$attaches."\"

# 関連するタグ
tags:

# 色付けのロールにするか
featuredpost: true

# ロールに表示する画像
featuredimage: /img/chemex.jpg

# 記事投稿日
date: ".$courselist_rows['date']."

---

".$course_home."

".$teaching_tips."

".$achievement."

".$syllabus."

".$calendar."

".$lecture_notes."

".$assignment."

".$evaluation."

".$related_resources."

    " ;
    // ファイルに書き込む
    fwrite($fp,$courselist_text);
 
    // ファイルを閉じる
    fclose($fp);

//}



}

 // DBの切断        
$close_ocwdb  = pg_close($ocwdb);
if ($close_ocwdb){
    print('ocwdb：切断に成功しました。<br>');
    }

?>
</body>
</html>