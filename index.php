<html>
<head><title>nuocw-mdfile-generator</title></head>
<body>

<?php
require_once(dirname(__FILE__) . '/vendor/autoload.php');
require_once('./config.php');

exec('/bin/rm ./src/pages/courses/*'  );
exec('/bin/rm ./src/pages/farewell/*' );


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
$limit = "LIMIT 20 OFFSET 50" ;
// SQL文の作成
$courselist_sql = "SELECT course_id, course_name, instructor_name, year, publish_group_abbr, date, department_id, instructor_id, vsyllabus_id, url_flv 
        FROM courselist_by_coursename
        WHERE exist_lectnotes='t'
        ORDER BY $sort_key $sort_order $limit ; ";
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

// 出力ソートキー
// $sort_key = "course_id";
$sort_key = $courselist_rows['course_id'] ;
$sort_order = "DESC";

// SQL文の作成
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

// pdfなどの追加資料　Attachments
//$attachments_sql = "SELECT id, name, description, relation_type, relation_id, del_flg
$attachments_sql = "SELECT name, description
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
    $featuredimage = "/img/chemex.jpg";
    foreach ($attachments_array as $attachment){
        if ($attachment['description'] == '看板画像'){
            echo $attachment['name']."    " ;
            echo $attachment['description']."<br>" ;
            $featuredimage = "/files/".$sort_key."/".$attachment['name'] ; 
        }else{
            $attaches .= "  - name: \"".$attachment['description'].= "\" \n" ;
            $attaches .= "    path : /files/".$sort_key."/".$attachment['name'].= "\n" ;
        // foreach ($attachment as $attach){
        // echo $attach."<br>"  ;
        // "  - name: ".$attaches .= "\n".$attach ;
        // }
        }
    }
}

// 

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
if (!($class_is_for_array[0]['contents'])){
    echo "データがありません！" ;
    $class_is_for = "" ;
}else{
    echo "class_is_for_array<br>" ;
    print_r($class_is_for_array);
    $class_is_for = space_trim(strip_tags($class_is_for_array[0]['contents'])) ;
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

$course_home = get_contents($course_home_sql);

// $course_home_result = pg_query($course_home_sql);
// if (!$course_home_result) {
// die('クエリーが失敗しました。'.pg_last_error());
// }
// $course_home_array = pg_fetch_all($course_home_result);
// if (!mbTrim(space_trim($course_home_array[0]['contents']))){
//     echo "データがありません！" ;
//     $course_home ="" ;
// }else{
//     echo "course_home_array<br>" ;
//     print_r($course_home_array);
//     $course_home ="### 授業ホーム
//     ".space_trim(strip_tags($course_home_array[0]['contents'])) ;
// }

// 52             | シラバス     | Syllabus              | syllabus         |        520
$syllabus_sql = "SELECT contents.contents 
                    FROM pages, page_contents, contents 
                    WHERE pages.course_id = $sort_key 
                    AND pages.page_type = '52' 
                    AND pages.page_id = page_contents.page_id 
                    AND contents.pid = page_contents.contents_id 
                    AND contents.type = '1101' 
                    ORDER BY contents.id DESC LIMIT 1; ";

$syllabus = get_contents ($syllabus_sql) ;
// $syllabus_result = pg_query($syllabus_sql);
// if (!$syllabus_result) {
// die('クエリーが失敗しました。'.pg_last_error());
// }
// $syllabus_array = pg_fetch_all($syllabus_result);
// if(!mbTrim(space_trim($syllabus_array[0]['contents']))){
//     echo "データがありません！" ;
//     $syllabus ="" ;
// }else{
//     echo "syllabus_array<br>" ;
//     print_r($syllabus_array);
//     $syllabus ="### ".space_trim(strip_tags($syllabus_array[0]['contents'])) ;
// }


// 53             | スケジュール | Calendar              | calendar         |        530
$calendar_sql = "SELECT contents.contents 
                    FROM pages, page_contents, contents 
                    WHERE pages.course_id = $sort_key 
                    AND pages.page_type = '53' 
                    AND pages.page_id = page_contents.page_id 
                    AND contents.pid = page_contents.contents_id 
                    AND contents.type = '1301' 
                    ORDER BY contents.id DESC LIMIT 1; ";

$syllabus = get_contents ($calendar_sql) ;

// 54             | 講義ノート   | Lecture Notes         | lecturenotes     |        540
 
$lecture_notes_sql = "SELECT contents.contents 
                    FROM pages, page_contents, contents 
                    WHERE pages.course_id = $sort_key 
                    AND pages.page_type = '54' 
                    AND pages.page_id = page_contents.page_id 
                    AND contents.pid = page_contents.contents_id 
                    AND contents.type = '1101' 
                    ORDER BY contents.id DESC LIMIT 1; ";

$lecture_notes = get_contents ($lecture_notes_sql) ;

// 55             | 課題         | Assignments           | assignments      |        550
 
$assignments_sql = "SELECT contents.contents 
                    FROM pages, page_contents, contents 
                    WHERE pages.course_id = $sort_key 
                    AND pages.page_type = '55' 
                    AND pages.page_id = page_contents.page_id 
                    AND contents.pid = page_contents.contents_id 
                    AND contents.type = '1101' 
                    ORDER BY contents.id DESC LIMIT 1; ";

$assignment = get_contents($assignments_sql) ;

// 56             | 成績評価     | Evaluation            | evaluation       |        560
$evaluation_sql = "SELECT contents.contents 
                    FROM pages, page_contents, contents 
                    WHERE pages.course_id = $sort_key 
                    AND pages.page_type = '56' 
                    AND pages.page_id = page_contents.page_id 
                    AND contents.pid = page_contents.contents_id 
                    AND contents.type = '1101' 
                    ORDER BY contents.id DESC LIMIT 1; ";

$evaluation = get_contents($evaluation_sql);

// 57             | 学習成果     | Achievement           | achievement      |        570
 $achievement_sql = "SELECT contents.contents 
                    FROM pages, page_contents, contents 
                    WHERE pages.course_id = $sort_key 
                    AND pages.page_type = '57' 
                    AND pages.page_id = page_contents.page_id 
                    AND contents.pid = page_contents.contents_id 
                    AND contents.type = '1101' 
                    ORDER BY contents.id DESC LIMIT 1; ";

$achievement = get_contents($achievement_sql);

// 58             | 参考資料     | Related Resources     | relatedresources |        580
 $related_resources_sql = "SELECT contents.contents 
                        FROM pages, page_contents, contents 
                        WHERE pages.course_id = $sort_key 
                        AND pages.page_type = '58' 
                        AND pages.page_id = page_contents.page_id 
                        AND contents.pid = page_contents.contents_id 
                        AND contents.type = '1101' 
                        ORDER BY contents.id DESC LIMIT 1; ";

$related_resources = get_contents($related_resources_sql);

// 59             | 授業の工夫   | Teaching Tips         | teachingtips     |        590
$teaching_tips_sql = "SELECT contents.contents 
                    FROM pages, page_contents, contents 
                    WHERE pages.course_id = $sort_key 
                    AND pages.page_type = '59' 
                    AND pages.page_id = page_contents.page_id 
                    AND contents.pid = page_contents.contents_id 
                    AND contents.type = '1101' 
                    ORDER BY contents.id DESC LIMIT 1; ";

$teaching_tips = get_contents($teaching_tips_sql);

// 71             | 最終講義・講義ホーム   | Farewell Lecture Home | f_index          |        515

$farewell_lecture_home_sql = "SELECT contents.contents 
                    FROM pages, page_contents, contents 
                    WHERE pages.course_id = $sort_key 
                    AND pages.page_type = '71' 
                    AND pages.page_id = page_contents.page_id 
                    AND contents.pid = page_contents.contents_id 
                    AND contents.type = '1101' 
                    ORDER BY contents.id DESC LIMIT 1; ";

$farewell_lecture_home = get_contents($farewell_lecture_home_sql);

// 72             | 最終講義・講師紹介     | Introduction          | f_intro          |        525
$farewell_lecture_introduction_sql = "SELECT contents.contents 
                    FROM pages, page_contents, contents 
                    WHERE pages.course_id = $sort_key 
                    AND pages.page_type = '72' 
                    AND pages.page_id = page_contents.page_id 
                    AND contents.pid = page_contents.contents_id 
                    AND contents.type = '1101' 
                    ORDER BY contents.id DESC LIMIT 1; ";

$farewell_lecture_introduction = get_contents($farewell_lecture_introduction_sql);

// 73             | 最終講義・講義資料     | Resources             | f_resources      |        585
$farewell_lecture_resources_sql = "SELECT contents.contents 
                    FROM pages, page_contents, contents 
                    WHERE pages.course_id = $sort_key 
                    AND pages.page_type = '73' 
                    AND pages.page_id = page_contents.page_id 
                    AND contents.pid = page_contents.contents_id 
                    AND contents.type = '1101' 
                    ORDER BY contents.id DESC LIMIT 1; ";

$farewell_lecture_resources = get_contents($farewell_lecture_resources_sql);
$output = "](" ;
$destination = "[/files/".$sort_key."/" ;

$farewell_lecture_resources = preg_replace('/\{ocwlink file="/', $destination , $farewell_lecture_resources);
$farewell_lecture_resources = preg_replace('/" desc="/', $output , $farewell_lecture_resources);
$farewell_lecture_resources = preg_replace('/"\}/', ")", $farewell_lecture_resources);     
$destination = "![/files/".$sort_key."/" ;

$farewell_lecture_resources = preg_replace('/\{ocwimg file="/', $destination , $farewell_lecture_resources);
$farewell_lecture_resources = preg_replace('/" align="right" alt="/', $output , $farewell_lecture_resources);
$farewell_lecture_resources = preg_replace('/"\}/', ")", $farewell_lecture_resources); 

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

if(strpos($courselist_rows['course_name'],'最終講義') !== false){
    // 最終講義のファイル名
    $file_name = "./src/pages/farewell/".$courselist_rows['course_name']."-".$courselist_rows['year'].".md" ;
    $templateKey = "farewell" ;
  }else{
    // 授業のファイル名
    $file_name = "./src/pages/courses/".$courselist_rows['course_name']."-".$courselist_rows['year'].".md" ;
    $templateKey = "courses" ;
  }
// 書き込みモードでファイルを開く
echo "<br>".$file_name."<br>" ;

$fp = fopen($file_name, "w");
 
$courselist_text =
"---
# テンプレート指定
templateKey: \"".$templateKey."\"

# コースID
course_id: \"".$sort_key."\"

# タイトル
title: \"".$courselist_rows['course_name']."\"

# 簡単な説明
description: >-
  ".preg_replace('/(?:\n|\r|\r\n)/', '', space_trim(strip_tags(mb_substr($course_home,0,100))) )."...

# 講師名
lecturer: \"".space_trim($courselist_rows['instructor_name'])."\"

# 部局名
department: \"".$division."\"

# 開講時限
term: \"".$term."\"

# 対象者、単位数、授業回数
target: \"".preg_replace('/(?:\n|\r|\r\n)/', '\t', $class_is_for )."\"

# 授業回数
classes: 

# 単位数
credit: 

# pdfなどの追加資料
attachments: 
".$attaches."

# 関連するタグ
tags:

# 色付けのロールにするか
featuredpost: true

# ロールに表示する画像
featuredimage: ".$featuredimage."

# 記事投稿日
date: ".$courselist_rows['date']."

---

".$course_home.$farewell_lecture_home."
".$teaching_tips.$farewell_lecture_introduction."
".$achievement.$farewell_lecture_resources."
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





}

 // DBの切断        
$close_ocwdb  = pg_close($ocwdb);
if ($close_ocwdb){
    print('ocwdb：切断に成功しました。<br>');
    }

?>
</body>
</html>