<html>
<head><title>nuocw-mdfile-generator</title></head>
<body>

<?php
require_once(dirname(__FILE__) . '/vendor/autoload.php');
require_once('config.php');
require_once('library.php');
// require_once('lib/ocw_init.php') ;
require_once('lib/class/OCWDB.class.php');

$nuocw_new_site_directory = '/Users/yamazato/Sites/NUOCW-Project/nuocw-preview/static/' ;

exec('/bin/rm /Users/yamazato/Sites/nuocw-mdfile-generator/src/pages/courses/*'  );
exec('/bin/rm /Users/yamazato/Sites/nuocw-mdfile-generator/src/pages/farewell/*' );

// 看板画像フォルダの初期化
exec('/bin/rm /Users/yamazato/Sites/NUOCW-Project/nuocw-preview/static/kanban/*'  );
// DBに接続
// $ocwpdb = pg_connect(ocwpdb);
// if (!ocwpdb) {
//     die('ocwpdb：接続失敗です。'.pg_last_error());
// }
// print('ocwpdb：接続に成功しました。<br>');

// 出力ソートキー
$course_id = "course_id";
// $course_id = "41" ;
$sort_order = "ASC";
$limit = "LIMIT 50 OFFSET 350" ;
// 全てのファイルを出力する場合
$limit = "" ;

// htmlへ書き出し
exec('/bin/rm /Users/yamazato/Sites/NUOCW-Project/nuocw-preview/static/tmp.html'  );
$html_file_name = "/Users/yamazato/Sites/NUOCW-Project/nuocw-preview/static/tmp.html"; 
$fp_html = fopen($html_file_name, "w");
$check_list = "<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
    <title>新旧OCWデータチェック</title>
</head>
<body>
<table border=\"1\" cellspacing=\"0\" cellpadding=\"5\" bordercolor=\"#C0C0C0\">
<tr><td><b>現OCW</b></td><td><b>新OCW</b></td><td width=\"200\"><b>ファイル名</b></td></tr>" ;

// // SQL文の作成
// $courselist_sql = "SELECT * FROM courselist_by_coursename
//         -- WHERE exist_lectnotes='t'
//         ORDER BY $course_id $sort_order $limit ; ";
// //        WHERE course_id=41

// // print($courselist_sql) ;
// // echo "<br><br>";

// $courselist_result = pg_query($courselist_sql);
//     if (!$courselist_result) {
//         die('クエリーが失敗しました。'.pg_last_error());
//     }

// // DBの切断
// $close_ocwpdb = pg_close($ocwpdb);
// if ($close_ocwpdb){
//     // print('ocwpdb：切断に成功しました。<br><br>');
//     }

    //
// ここから ocwdb　への接続
// DBの接続
$ocwdb = pg_connect(ocwdb);
if (!ocwdb) {
    die('ocwdb：接続失敗です。'.pg_last_error());
}
print('ocwdb：接続に成功しました。<br>');


// SQL文の作成
$courselist_sql = "SELECT c.course_id, c.course_name as course_name, 
            year, term, d.department_id, d.department_name as department_name, division,
            array_to_string(array( 
                SELECT i.instructor_name FROM course_instructor ci, instructor i 
                WHERE ci.course_id = c.course_id AND ci.instructor_id = i.instructor_id 
                ORDER BY ci.disp_order ASC ), '／') as instructor_name, time 
                FROM course c, department d, term_code_master tcm, course_status cs, event ev, 
                ((SELECT course_id FROM course_status WHERE status='02' AND lang='ja') 
                EXCEPT (SELECT course_id FROM course_status WHERE status='09')) AS cs02
                WHERE c.department_id = d.department_id AND 
                c.term = tcm.term_code AND c.course_id = cs.course_id 
                AND cs.event_id = ev.event_id AND cs02.course_id = c.course_id 
                AND cs.status='02' AND cs.lang ='ja'
            ORDER BY c.course_id $sort_order $limit ";

$courselist_sql = "SELECT c.course_id, c.course_name as course_name,
                   year, term as course_semester,
                   d.department_id, d.department_name as department_name, division,
                   array_to_string(array(
                      SELECT
                        i.instructor_id
                      FROM course_instructor ci, instructor i
                      WHERE ci.course_id = c.course_id AND
                            ci.instructor_id = i.instructor_id
                      ORDER BY ci.disp_order ASC
                     ), '／') as instructor_id
            FROM course c, department d, term_code_master tcm
            WHERE c.department_id = d.department_id AND
                  c.term = tcm.term_code AND

                  EXISTS (
                      SELECT c_s.status
                       FROM course_status c_s
                       WHERE c_s.course_id = c.course_id AND
                             c_s.status = '01' AND
                             c_s.lang = 'ja'
                  ) AND


                  NOT EXISTS (
                      SELECT c_s.status
                       FROM  course_status c_s
                       WHERE c_s.course_id = c.course_id AND
                             ((c_s.status = '08' AND lang = 'ja') OR 
                               c_s.status = '09')
                  )
            ORDER BY c.course_id $sort_order $limit ";
// print($courselist_sql) ;
// echo "<br><br>";

// $sth = $ocwdb->prepare($courselist_sql);
// $sth->execute();

// /* Exercise PDOStatement::fetch styles */
// print("PDO::FETCH_ASSOC: ");
// print("Return next row as an array indexed by column name<br>");
// // 一行のみ取り出す
// $result = $sth->fetch(PDO::FETCH_ASSOC);
// // 全て取り出す
// $result = $sth->fetchALL(PDO::FETCH_ASSOC);
// // print_r($sth) ;
// var_dump($result);
// print("\n");

$courselist_result = pg_query($courselist_sql);
    if (!$courselist_result) {
        die('クエリーが失敗しました。'.pg_last_error());
    }

// print_r($courselist_result)    ;

for ($i = 0 ; $i < pg_num_rows($courselist_result) ; $i++){
    $courselist_rows = pg_fetch_array($courselist_result, NULL, PGSQL_ASSOC);
    // echo "<br><br>";
    // print_r($courselist_rows);
    //    echo $courselist_rows['contents'][0];
    //    echo $courselist_rows['course_id'][0];

// 出力ソートキー
// $course_id = "course_id";
$course_id = $courselist_rows['course_id'] ;

$course_name = $courselist_rows['course_name']  ;
$course_name = strip_tags( $course_name );
$course_name = space_trim( $course_name ) ;
// $course_name = preg_replace('/\s(?=\s)/', '', $course_name );
$course_name = preg_replace("/( |　)/", "-", $course_name );
$course_name = str_replace('/', '／' , $course_name );
$course_name = str_replace('?', '？' , $course_name );
$course_name = str_replace('!', '！' , $course_name );
$course_name = str_replace(':', '：' , $course_name );
$course_name = preg_replace('/-+/', '-', $course_name) ;

// $course_name = preg_replace("/(-|---)/", "-", $course_name );
// $course_name = $course_name."-".$courselist_rows['year'] ;
// $course_name = $course_name."-".$course_id."-".$courselist_rows['year'] ;
// $course_name = $course_name."-".$courselist_rows['department_name']."-".$courselist_rows['year'] ;

// echo "<br>".$course_name ;

// 記事投稿日
$course_date_sql = "SELECT * FROM event WHERE event_id IN
             (SELECT event_id FROM course_status WHERE  course_id = $course_id) 
             ORDER BY event_id DESC" ;
$course_date_result = pg_query($course_date_sql);
if (!$course_date_result) {
    die('クエリーが失敗しました。'.pg_last_error());
}

$course_date_array = pg_fetch_all($course_date_result);
// print_r($course_date_array);

$course_date = $course_date_array[0]['time'];

// 講師　
$lecturer_sql = "SELECT instructor_name, instructor_position 
            FROM instructor WHERE instructor_id IN 
            (SELECT instructor_id FROM course_instructor 
            WHERE course_id = $course_id) ";
$lecturer_result = pg_query($lecturer_sql);
if (!$lecturer_result) {
    die('クエリーが失敗しました。'.pg_last_error());
}

$lecturer_array = pg_fetch_all_columns ($lecturer_result);
$lecturer_array = pg_fetch_all ($lecturer_result);

// var_dump($lecturer_array) ;
// $lecturer_array = call_user_func_array("array_merge", $lecturer_array); 
$lecturer = "";

foreach($lecturer_array as $value){
$lecturer .= implode ( " ", $value ).", ";
}
$lecturer = mb_substr($lecturer, 0, -2);

// $lecturer = space_trim($lecturer_array[0]['instructor_name'])." ".space_trim($lecturer_array[0]['instructor_position']) ;
// echo "<br>".$lecturer ;

// SQL文の作成
// $course_sql = "SELECT * FROM course WHERE course.course_id = $course_id " ;
$course_sql = "SELECT * FROM course 
            INNER JOIN course_status ON course.course_id = course_status.course_id 
            WHERE course.archive = 'f' 
            AND course_status.status='01' 
            AND course_status.lang='ja' 
            AND course.course_id = $course_id; " ;
$course_result = pg_query($course_sql);
if (!$course_result) {
    die('クエリーが失敗しました。'.pg_last_error());
}

$course_array = pg_fetch_all($course_result);
// $course_array = $courselist_rows;
// if (!$course_array){
//     die('course_array NULL');
// }

// echo "<br>course_array<br>" ;
// print_r($course_array);
// echo "<br>".$course_array[0]['course_name']."<br>" ;
// echo "<br>".$course_array[0]['division']."<br>" ;
// echo "<br>".$course_array[0]['term']."<br>" ;

$division_code = $course_array[0]['division'] ;
// echo "<br>division code: ".$division_code."<br>" ;

$term_code = $course_array[0]['term'] ;
// echo $term_code ;

// 部局 department
$division_code_master_sql = "SELECT division_name 
                            FROM division_code_master 
                            WHERE division_code = '$division_code' ; " ;
$division_code_master_result = pg_query($division_code_master_sql);
if (!$division_code_master_result) {
    die('クエリーが失敗しました。'.pg_last_error());
}
$division_code_master_array = pg_fetch_all($division_code_master_result);
// echo "<br>division_code_master_result<br>" ;
// print_r($division_code_master_array);
$division = $division_code_master_array[0]['division_name'] ;
$division = str_replace('/', '／' , $division );
// echo "<br>".$division."<br>" ;

$category = category ($division_code) ;
$tags = category ($division_code) ;

// 開講時限　term
$term_code_master_sql = "SELECT name 
                            FROM term_code_master 
                            WHERE term_code = '$term_code' ; " ;
$term_code_master_result = pg_query($term_code_master_sql);
if (!$term_code_master_result) {
    die('クエリーが失敗しました。'.pg_last_error());
}
$term_code_master_array = pg_fetch_all($term_code_master_result);
// echo "<br>term_code_master_result<br>" ;
// print_r($term_code_master_array);
$term = $courselist_rows['year']."年度\t".$term_code_master_array[0]['name'] ;

// echo "<br>".$term."<br>" ;

// pdfなどの追加資料　Attachments
//$attachments_sql = "SELECT id, name, description, relation_type, relation_id, del_flg
$attachments_sql = "SELECT name, description
                    FROM file_group 
                    INNER JOIN course 
                    ON course.course_id = file_group.relation_id 
                    WHERE course.course_id = $course_id 
                    AND del_flg = 'f' ; " ;
$attachments_result = pg_query($attachments_sql);
if (!$attachments_result) {
    die('クエリーが失敗しました。'.pg_last_error());
}
$attachments_array = pg_fetch_all($attachments_result);

$file_directory = $nuocw_new_site_directory."files/".$course_id."/*" ;
$file_directory_result = glob( $file_directory );
// echo "<br>".dirname($file_directory)."<br>" ; 
// var_dump($file_directory_result);

// echo "<br>"; var_dump($attachments_array);
$attaches = "";
$featuredimage = "/img/common/thumbnail.png";

if (!$attachments_array){
    // echo "データがありません！" ;
    // $attachments = "" ;
    $attaches = "";
    $attaches .= "  - name: \"NUOCW logo\" \n" ;
    $attaches .= "    path: /img/common/thumbnail.png\n" ;

}else{
    // echo "<br>" ;
    // print_r($attachments_array);
    // echo "<br>" ;
    // $attachments = call_user_func_array('array_merge', $attachments_array); 
    // print_r($attachments);
    // $ii = 0 ;
    $featuredimage = "/img/common/thumbnail.png";
    foreach ($attachments_array as $attachment){
        if(strpos($attachment['description'],'看板画像') !== false){
        // if ($attachment['description'] == '看板画像'){
            // echo $attachment['name']."    " ;
            // echo $attachment['description']."<br>" ;
            // echo "<br>".$featuredimage ;

            $featuredimage = sprintf('%03d', $course_id)."-".trim( $attachment['name'] ) ; 
            $image_transfer  = "/bin/cp " ;
            $image_transfer .= "/Users/yamazato/Sites/NUOCW-Project/files/".$course_id."/".$attachment['name'] ;
            $image_transfer .= " /Users/yamazato/Sites/NUOCW-Project/nuocw-preview/static/kanban/".$featuredimage ;
            exec($image_transfer);
            $featuredimage = "/kanban/".$featuredimage ; 
            // echo "<br>".$featuredimage ;

            // foreach ( $file_directory_result as $file_directory_result_name) {
            //     if ( strcasecmp(basename($file_directory_result_name), trim( $attachment['name'] )) == 0 ) {

            //         $featuredimage = $course_id."-".trim( $attachment['name'] ) ; 
            //         $image_transfer  = "/bin/cp " ;
            //         $image_transfer .= "/Users/yamazato/Sites/files/".$featuredimage ;
            //         $image_transfer .= " /Users/yamazato/Sites/NUOCW-Project/nuocw-preview/static/kanban/".$featuredimage ;
            //         exec('$image_transfer');
            //         echo "<br>".$image_transfer ;
            //     }
            // }
        }else{
            $attaches .= "  - name: \"".$attachment['description'].= "\" \n" ;
            $attaches .= "    path: https://ocw.nagoya-u.jp/files/".$course_id."/".$attachment['name'].= "\n" ;
            // $attache_file_name = trim ( $attachment['name'] ) ;
            
            foreach ( $file_directory_result as $file_directory_result_name) {
                if ( strcasecmp(basename($file_directory_result_name), trim( $attachment['name'] )) == 0 ) {
                    // echo "A match was found.  ". basename($file_directory_result_name). " = ". trim ( $attachment['name'] ) . "<br>";
                    $attaches .= "  - name: \"".$attachment['description'].= "\"\n" ;
                    $attaches .= "    path: /files/".$course_id."/".$attachment['name'].= "\n" ;
                    // echo "<br>".$attaches."<br>" ;
                } 
            }
        // echo "<br>" ;
        // foreach ($attachment as $attach){
        // echo $attach."<br>"  ;
        // "  - name: ".$attaches .= "\n".$attach ;
        // }
        }
        // $ii ++ ;
    }
}


// $jj = 0;
// echo "<br><br>" ; 
// foreach ( $file_directory_result as $filename) {
//     echo basename($filename). "<br>";
//     $file_directory_result[$jj] = basename($filename) ;
//     if ( preg_match( $attache_file_name , $file_directory_result[$jj] )) {
//         echo "A match was found.". "<br>";
//         echo $attache_file_name. "<br>";
//     } else {
//         echo "A match was not found.". "<br>";
//     }
//     $jj++ ;
// }
// echo "<br> file_directory_result <br>" ; 
// var_dump($file_directory_result);

// echo "<br> attache_file_name <br>"; 
// var_dump($attache_file_name);

// $attache_intersect = array_intersect( $file_directory_result , $attache_file_name ) ;
// echo "<br>array_intersect(file_directory_result, attache_file_name)<br>"; 
// var_dump( $attache_intersect );

// echo "<br>array_diff(file_directory_result, attache_file_name)<br>" ; 
// var_dump( array_diff( $attache_file_name , $file_directory_result ) );



// // 比較元の配列を変数に格納
// $arr = array('b'=>'sano', 'd'=>'aoyama', 'momozono');
 
// // 比較対象の配列を変数に格納
// $arr2 = array('a'=>'izumi', 'd'=>'aoyama', 'sano', 'momozono');
 
// // 「キー => 値」のペアで比較して重複していない要素のみ出力
// print_r(array_diff_assoc($arr, $arr2));

// 1281               | 対象者
$class_is_for_sql = "SELECT contents.contents 
                    FROM pages, page_contents, contents 
                    WHERE pages.course_id = $course_id 
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
    // echo "データがありません！" ;
    $class_is_for = "" ;
}else{
    $class_is_for = space_trim(strip_tags($class_is_for_array[0]['contents'])) ;
    echo "<br>class_is_for_array : ".$class_is_for."<br>" ;
    // print_r($class_is_for_array);
}

$class_is_for = preg_replace('/(\n|\r|\r\n)+/us',"\n", $class_is_for );

// echo "<br>".$class_is_for;

// 51             | 授業ホーム   | Course Home           | index            |        510
$page_id = check_page_status ($course_id, $page_type = '51') ;
if(!empty($page_id)){
    $description_sql = "SELECT contents.contents FROM page_contents, contents 
                WHERE contents.pid = page_contents.contents_id 
                -- AND (contents.type = '1101' OR contents.type = '1301') 
                AND contents.type = '1301'
                AND page_contents.page_id = $page_id 
                ORDER BY contents.id DESC LIMIT 1 ; " ;

    $description = get_contents($page_id, $contents_type = '1301');
    // echo "<br>" ;
    // var_dump($description) ;

    $course_home_sql = "SELECT contents.contents FROM page_contents, contents 
                WHERE contents.pid = page_contents.contents_id 
                -- AND (contents.type = '1101' OR contents.type = '1301') 
                AND contents.type = '1101'
                AND page_contents.page_id = $page_id 
                ORDER BY contents.id DESC LIMIT 1 ; " ;

    $course_home = get_contents($page_id, $contents_type = '1101');
    // echo "<br>" ;
    // var_dump($course_home) ;
}else{
    $description = '';
    $course_home = '';
}
// $course_home = convert_ocwlink ($course_home , $course_id) ;
$overview_header = '/(?<=\{overview header=\").+?(?=\"\})/';

if( preg_match_all('/(?<=\{overview header=\").+?(?=\"\})/', $course_home, $overview_header_match) ){
    // var_dump($overview_header_match);
    // echo "<br>overview_header : ".$overview_header." overview_header_match : ".$overview_header_match[0][0]."<br>" ;
    $course_home = "### ".$overview_header_match[0][0]."\n\n".$description ;
}
if( preg_match_all('/(?<=\{overview\})/', $course_home, $overview_header_match) ){
    // var_dump($overview_header_match);
    // echo "<br>overview_header : ".$overview_header." overview_header_match : ".$overview_header_match[0][0]."<br>" ;
    $course_home = "### 授業の内容\n\n".$description ;
}
if( empty($course_home) && !empty($description) ){
    $course_home = "### 授業の内容\n\n".$description ;
}

// 52             | シラバス     | Syllabus              | syllabus         |        520
$page_id = check_page_status ($course_id, $page_type = '52') ;
if(!empty($page_id)){

    $syllabus_sql = "SELECT contents.contents FROM page_contents, contents 
                    WHERE contents.pid = page_contents.contents_id 
                    AND contents.type = '1101'
                    AND page_contents.page_id = $page_id 
                    ORDER BY contents.id DESC LIMIT 1 ; " ;

    $syllabus = get_contents($page_id, $contents_type = '1101');

}else{
    $syllabus = '' ;
}

// 53             | スケジュール | Calendar              | calendar         |        530
$page_id = check_page_status ($course_id, $page_type = '53') ;
if(!empty($page_id)){

    $calendar_sql = "SELECT contents.contents FROM page_contents, contents 
                    WHERE contents.pid = page_contents.contents_id 
                    AND contents.type = '1101'
                    AND page_contents.page_id = $page_id 
                    ORDER BY contents.id DESC LIMIT 1 ; " ;

    $calendar = get_contents_without_Markdownify ($page_id, $contents_type = '1101'); ;

}else{
    $calendar = '' ;
}
// $calendar_sql = "SELECT contents.contents 
//                     FROM pages, page_contents, contents, page_status
//                     WHERE pages.course_id = $course_id 
//                     AND pages.page_type = '53' 
//                     AND pages.page_id = page_contents.page_id 
//                     AND contents.pid = page_contents.contents_id 
//                     AND (contents.type = '1101' OR contents.type = '1301')
//                     AND (page_status.status = '01' OR page_status.status = '02' OR page_status.status = '03' OR page_status.status = '04' OR page_status.status = '05' )
//                     ORDER BY contents.id DESC LIMIT 1; ";

// // $calendar = get_contents ($calendar_sql) ;



// $calendar = convert_ocwlink ($calendar, $course_id) ;

// 54             | 講義ノート   | Lecture Notes         | lecturenotes     |        540
$page_id = check_page_status ($course_id, $page_type = '54') ;
if(!empty($page_id)){

    $lecture_notes_sql = "SELECT contents.contents FROM page_contents, contents 
                    WHERE contents.pid = page_contents.contents_id 
                    AND contents.type = '1101'
                    AND page_contents.page_id = $page_id 
                    ORDER BY contents.id DESC LIMIT 1 ; " ;

    $lecture_notes = get_contents ($page_id, $contents_type = '1101'); ;

}else{
    $lecture_notes = '' ;
} 
// $lecture_notes_sql = "SELECT contents.contents 
//                     FROM pages, page_contents, contents, page_status 
//                     WHERE pages.course_id = $course_id 
//                     AND pages.page_type = '54' 
//                     AND pages.page_id = page_contents.page_id 
//                     AND contents.pid = page_contents.contents_id 
//                     AND (contents.type = '1101' OR contents.type = '1301')
//                     AND (page_status.status = '01' OR page_status.status = '02' OR page_status.status = '03' OR page_status.status = '04' OR page_status.status = '05' )
//                     ORDER BY contents.id DESC LIMIT 1; ";


// $lecture_notes = get_contents_without_Markdownify ($lecture_notes_sql) ;
// $lecture_notes = convert_ocwlink ($lecture_notes, $course_id) ;

// 55             | 課題         | Assignments           | assignments      |        550
$page_id = check_page_status ($course_id, $page_type = '55') ;
if(!empty($page_id)){

    $assignment = get_contents_without_Markdownify($page_id, $contents_type = '1101');

}else{
    $assignment = '' ;
}  
// $assignments_sql = "SELECT contents.contents 
//                     FROM pages, page_contents, contents, page_status 
//                     WHERE pages.course_id = $course_id 
//                     AND pages.page_type = '55' 
//                     AND pages.page_id = page_contents.page_id 
//                     AND contents.pid = page_contents.contents_id 
//                     AND (contents.type = '1101' OR contents.type = '1301')
//                     AND (page_status.status = '01' OR page_status.status = '02' OR page_status.status = '03' OR page_status.status = '04' OR page_status.status = '05' )
//                     ORDER BY contents.id DESC LIMIT 1; ";


// $assignment = convert_ocwlink ($assignments, $course_id) ;

// 56             | 成績評価     | Evaluation            | evaluation       |        560
$page_id = check_page_status ($course_id, $page_type = '56') ;
if(!empty($page_id)){

    $evaluation = get_contents($page_id, $contents_type = '1101');

}else{
    $evaluation = '' ;
}  
// $evaluation_sql = "SELECT contents.contents 
//                     FROM pages, page_contents, contents, page_status 
//                     WHERE pages.course_id = $course_id 
//                     AND pages.page_type = '56' 
//                     AND pages.page_id = page_contents.page_id 
//                     AND contents.pid = page_contents.contents_id 
//                     AND (contents.type = '1101' OR contents.type = '1301')
//                     AND (page_status.status = '01' OR page_status.status = '02' OR page_status.status = '03' OR page_status.status = '04' OR page_status.status = '05' ) 
//                     ORDER BY contents.id DESC LIMIT 1; ";
// $evaluation = get_contents_without($evaluation_sql);
// $evaluation = get_contents_without_Markdownify($evaluation_sql);


// 57             | 学習成果     | Achievement           | achievement      |        570
$page_id = check_page_status ($course_id, $page_type = '57') ;
if(!empty($page_id)){

    $achievement = get_contents_without_Markdownify($page_id, $contents_type = '1101');

}else{
    $achievement = '' ;
} 
// $achievement_sql = "SELECT contents.contents 
//                     FROM pages, page_contents, contents, page_status 
//                     WHERE pages.course_id = $course_id 
//                     AND pages.page_type = '57' 
//                     AND pages.page_id = page_contents.page_id 
//                     AND contents.pid = page_contents.contents_id 
//                     AND (contents.type = '1101' OR contents.type = '1301')
//                     AND (page_status.status = '01' OR page_status.status = '02' OR page_status.status = '03' OR page_status.status = '04' OR page_status.status = '05' )
//                     ORDER BY contents.id DESC LIMIT 1; ";

// $achievement = get_contents_without_Markdownify($achievement_sql);
// $achievement = convert_ocwlink ($achievement, $course_id) ;

// 58             | 参考資料     | Related Resources     | relatedresources |        580
$page_id = check_page_status ($course_id, $page_type = '58') ;
if(!empty($page_id)){

    $related_resources = get_contents_without_Markdownify($page_id, $contents_type = '1101');

}else{
    $related_resources = '' ;
} 
//  $related_resources_sql = "SELECT contents.contents 
//                         FROM pages, page_contents, contents, page_status 
//                         WHERE pages.course_id = $course_id 
//                         AND pages.page_type = '58' 
//                         AND pages.page_id = page_contents.page_id 
//                         AND contents.pid = page_contents.contents_id 
//                         AND (contents.type = '1101' OR contents.type = '1301')
//                         AND (page_status.status = '01' OR page_status.status = '02' OR page_status.status = '03' OR page_status.status = '04' OR page_status.status = '05' )
//                         ORDER BY contents.id DESC LIMIT 1; ";

// $related_resources = get_contents_without_Markdownify($related_resources_sql);
// $related_resources = convert_ocwlink ($related_resources, $course_id) ;

// 59             | 授業の工夫   | Teaching Tips         | teachingtips     |        590
$page_id = check_page_status ($course_id, $page_type = '59') ;
if(!empty($page_id)){

    $teaching_tips = get_contents($page_id, $contents_type = '1101');

}else{
    $teaching_tips = '' ;
} 
// $teaching_tips_sql = "SELECT contents.contents 
//                     FROM pages, page_contents, contents, page_status 
//                     WHERE pages.course_id = $course_id 
//                     AND pages.page_type = '59' 
//                     AND pages.page_id = page_contents.page_id 
//                     AND contents.pid = page_contents.contents_id 
//                     AND (contents.type = '1101' OR contents.type = '1301')
//                     AND (page_status.status = '01' OR page_status.status = '02' OR page_status.status = '03' OR page_status.status = '04' OR page_status.status = '05' )
//                     ORDER BY contents.id DESC LIMIT 1; ";

// $teaching_tips = get_contents($teaching_tips_sql);
// $teaching_tips = convert_ocwlink ($teaching_tips, $course_id) ;

// 12a1               | 最終講義　日付

$farewell_date_sql = "SELECT contents.contents 
                    FROM pages, page_contents, contents 
                    WHERE pages.course_id = $course_id 
                    AND pages.page_id = page_contents.page_id 
                    AND contents.pid = page_contents.contents_id 
                    AND contents.type = '12a1' 
                    ORDER BY contents.id DESC LIMIT 1; ";

$farewell_date_result = pg_query($farewell_date_sql);
if (!$farewell_date_result) {
    die('クエリーが失敗しました。'.pg_last_error());
}
$farewell_date_array = pg_fetch_all($farewell_date_result);
if (!($farewell_date_array[0]['contents'])){
    // echo "データがありません！" ;
    $farewell_date = "" ;
}else{
    // echo "class_is_for_array<br>" ;
    // print_r($class_is_for_array);
    $farewell_date = space_trim(strip_tags($farewell_date_array[0]['contents'])) ;
}

// echo "<br> farewell_date ".$farewell_date ;

// 12a2              |　最終講義　時間

$farewell_time_sql = "SELECT contents.contents 
                    FROM pages, page_contents, contents 
                    WHERE pages.course_id = $course_id 
                    AND pages.page_id = page_contents.page_id 
                    AND contents.pid = page_contents.contents_id 
                    AND contents.type = '12a2' 
                    ORDER BY contents.id DESC LIMIT 1; ";

$farewell_time_result = pg_query($farewell_time_sql);
if (!$farewell_time_result) {
    die('クエリーが失敗しました。'.pg_last_error());
}
$farewell_time_array = pg_fetch_all($farewell_time_result);
if (!($farewell_time_array[0]['contents'])){
    // echo "データがありません！" ;
    $farewell_time = "" ;
}else{
    // echo "class_is_for_array<br>" ;
    // print_r($class_is_for_array);
    $farewell_time = space_trim(strip_tags($farewell_time_array[0]['contents'])) ;
}
$farewell_date = "| 日時 | ".$farewell_date." 　".$farewell_time." |" ;
// echo "<br> farewell_time ".$farewell_date ;

// 12a3               | 最終講義　場所

$farewell_place_sql = "SELECT contents.contents 
                    FROM pages, page_contents, contents 
                    WHERE pages.course_id = $course_id 
                    AND pages.page_id = page_contents.page_id 
                    AND contents.pid = page_contents.contents_id 
                    AND contents.type = '12a3' 
                    ORDER BY contents.id DESC LIMIT 1; ";

$farewell_place_result = pg_query($farewell_place_sql);
if (!$farewell_place_result) {
    die('クエリーが失敗しました。'.pg_last_error());
}
$farewell_place_array = pg_fetch_all($farewell_place_result);
if (!($farewell_place_array[0]['contents'])){
    // echo "データがありません！" ;
    $farewell_place = "" ;
}else{
    // echo "class_is_for_array<br>" ;
    // print_r($class_is_for_array);
    $farewell_place = space_trim(strip_tags($farewell_place_array[0]['contents'])) ;
}
$farewell_place = "| 場所 | ".$farewell_place." |" ;
// echo "<br> farewell_place ".$farewell_place ;

// 71             | 最終講義・講義ホーム   | Farewell Lecture Home | f_index          |        515
$page_id = check_page_status ($course_id, $page_type = '71') ;
if(!empty($page_id)){

    $farewell_lecture_home = get_contents_without_Markdownify($page_id, $contents_type = '1101');
    $farewell_lecture_home = get_contents($page_id, $contents_type = '1101');    
    $farewell_lecture_home_del_firstline = preg_replace('/\###.*/um', '' , $farewell_lecture_home);

}else{
    $farewell_lecture_home = '' ;
    $farewell_lecture_home_del_firstline = '';
} 


// $farewell_lecture_home_sql = "SELECT contents.contents 
//                     FROM pages, page_contents, contents, page_status 
//                     WHERE pages.course_id = $course_id 
//                     AND pages.page_type = '71' 
//                     AND pages.page_id = page_contents.page_id 
//                     AND contents.pid = page_contents.contents_id 
//                     AND (contents.type = '1101' OR contents.type = '1301')
//                     AND (page_status.status = '01' OR page_status.status = '02' OR page_status.status = '03' OR page_status.status = '04' OR page_status.status = '05' ) 
//                     ORDER BY contents.id DESC LIMIT 1; ";

// // echo "<br>farewell_lecture_home_sql ".$farewell_lecture_home_sql."<br>" ;
// $farewell_lecture_home = get_contents($farewell_lecture_home_sql);
// $farewell_lecture_home_del_firstline = preg_replace('/\###.*/um', '' , $farewell_lecture_home);
// print_r($farewell_lecture_home);

// 72             | 最終講義・講師紹介     | Introduction          | f_intro          |        525
$page_id = check_page_status ($course_id, $page_type = '72') ;
if(!empty($page_id)){

    $farewell_lecture_introduction = get_contents($page_id, $contents_type = '1101');

}else{
    $farewell_lecture_introduction = '' ;
} 
// $farewell_lecture_introduction_sql = "SELECT contents.contents 
//                     FROM pages, page_contents, contents, page_status 
//                     WHERE pages.course_id = $course_id 
//                     AND pages.page_type = '72' 
//                     AND pages.page_id = page_contents.page_id 
//                     AND contents.pid = page_contents.contents_id 
//                     AND (contents.type = '1101' OR contents.type = '1301')
//                     AND (page_status.status = '01' OR page_status.status = '02' OR page_status.status = '03' OR page_status.status = '04' OR page_status.status = '05' )
//                     ORDER BY contents.id DESC LIMIT 1; ";

// $farewell_lecture_introduction = get_contents($farewell_lecture_introduction_sql);
// $farewell_lecture_introduction = convert_ocwimg ($farewell_lecture_introduction, $course_id);

// 73             | 最終講義・講義資料     | Resources             | f_resources      |        585
$page_id = check_page_status ($course_id, $page_type = '73') ;
if(!empty($page_id)){

    $farewell_lecture_resources = get_contents($page_id, $contents_type = '1101');

}else{
    $farewell_lecture_resources = '' ;
} 
// $farewell_lecture_resources_sql = "SELECT contents.contents 
//                     FROM pages, page_contents, contents, page_status 
//                     WHERE pages.course_id = $course_id 
//                     AND pages.page_type = '73' 
//                     AND pages.page_id = page_contents.page_id 
//                     AND contents.pid = page_contents.contents_id 
//                     AND (contents.type = '1101' OR contents.type = '1301')
//                     AND (page_status.status = '01' OR page_status.status = '02' OR page_status.status = '03' OR page_status.status = '04' OR page_status.status = '05' )
//                     ORDER BY contents.id DESC LIMIT 1; ";

// // $farewell_lecture_resources = "\n" ;
// $farewell_lecture_resources = get_contents_without_Markdownify($farewell_lecture_resources_sql);
// $farewell_lecture_resources = convert_ocwlink ($farewell_lecture_resources, $course_id) ;

// 講義映像
$movie_sql = "SELECT url_flv FROM visual_syllabus 
            WHERE vsyllabus_id = 
                (SELECT vsyllabus_id FROM course 
                 WHERE course_id = $course_id ) ; " ;
$movie_result = pg_query($movie_sql);
if (!$movie_result) {
    die('クエリーが失敗しました。'.pg_last_error());
}
$movie = pg_fetch_row($movie_result);
$movie = $movie[0] ;
// echo "<br>"; print_r($movie);
$movie = str_ireplace("http://studio.media.nagoya-u.ac.jp/videos/watch.php?v=", "https://nuvideo.media.nagoya-u.ac.jp/embed/", $movie);
$movie = str_ireplace("http://nuvideo.media.nagoya-u.ac.jp/embed/", "https://nuvideo.media.nagoya-u.ac.jp/embed/", $movie);

if(preg_match('/FlvPlayer/',$movie)){
    $movie = '' ;
    echo "\n".$movie ;
  }


// $movie_description = "SELECT description FROM visual_syllabus 
//             WHERE vsyllabus_id = 
//                 (SELECT vsyllabus_id FROM course 
//                 WHERE course_id = $course_id ) ; " ;

// $movie_duration = "SELECT time FROM visual_syllabus 
//             WHERE vsyllabus_id = 
//                 (SELECT vsyllabus_id FROM course 
//                 WHERE course_id = $course_id ) ; " ;


// $file = '/(?<=\{ocwlink file=\").+?(?=\")/';
// preg_match_all($file, $farewell_lecture_resources, $file_match);
// //print_r($file_match);
// $desc = '/(?<=desc=\").+?(?=\")/';
// preg_match_all($desc, $farewell_lecture_resources, $desc_match);
// //print_r($desc_match);

// $ii = 0;
// foreach ($desc_match[0] as $value){
//     $farewell_lecture_resources .= 
//     "[".$desc_match[0][$ii]."](/files/".$course_id."/".$file_match[0][$ii].")\n" ;
//     $ii++;
// }
//$farewell_lecture_resources .= "\n[".$desc_match[0][0]."](/files/".$course_id."/".$file_match[0][0].")" ;
//print_r($farewell_lecture_resources);

//$farewell_lecture_resources = preg_replace($pattern, '' , $farewell_lecture_resources);

//print_r($farewell_lecture_resources);


//  $str='(東京都){神奈川県}(千葉県)';
//  $pattern = '/\(.+?\)/';
//  $pattern = '/(?<={).*?(?=})/';
//  preg_match_all($pattern, $str, $match);
//  print_r($match);

// $farewell_lecture_resources = preg_replace('/(?<={).*?(?=})/', '' , $farewell_lecture_resources);
// $farewell_lecture_resources = preg_replace('/\{\}/', '' , $farewell_lecture_resources);

// echo "<br> farewell_lecture_resources <br>".$farewell_lecture_resources ;


// preg_match_all('/\{ocwlink file=["|\'][.+?]"/', $farewell_lecture_resources, $res,PREG_SET_ORDER);
// print_r($res);

// $output = "](" ;
// $destination = "[/files/".$course_id."/" ;

// $farewell_lecture_resources = preg_replace('/\{ocwlink file="/', $destination , $farewell_lecture_resources);
// $farewell_lecture_resources = preg_replace('/" desc="/', $output , $farewell_lecture_resources);
// $farewell_lecture_resources = preg_replace('/"\}/', ")", $farewell_lecture_resources);     
// $destination = "![/files/".$course_id."/" ;

// $farewell_lecture_resources = preg_replace('/\{ocwimg file="/', $destination , $farewell_lecture_resources);
// $farewell_lecture_resources = preg_replace('/" align="right" alt="/', $output , $farewell_lecture_resources);
// $farewell_lecture_resources = preg_replace('/"\}/', ")", $farewell_lecture_resources); 

// echo "<br><br>";
    
// print('course_id='.$courselist_rows['course_id'].'<br>');
// print('course_name='.$courselist_rows['course_name'].'<br>');
// print('year='.$courselist_rows['year'].'<br>');
// print('publish_group_abbr='.$courselist_rows['publish_group_abbr'].'<br>');
// print('date='.$courselist_rows['date'].'<br>');
// print('department_id='.$courselist_rows['department_id'].'<br>');
// print('instructor_id='.$courselist_rows['instructor_id'].'<br>');
// print('vsyllabus_id='.$courselist_rows['vsyllabus_id'].'<br>');
// print('url_flv='.$courselist_rows['url_flv'].'<br>');
    
// echo "<br><br>";
$key_pharase = space_trim($course_name)." ".$courselist_rows['department_name']." ";
$key_pharase .= preg_replace('/(?:\n|\r|\r\n)/', '', space_trim(strip_tags(mb_substr($course_home,0,500))) ) ;
$key_pharase .= preg_replace('/(?:\n|\r|\r\n)/', '', space_trim(strip_tags(mb_substr($farewell_lecture_home_del_firstline,0,500))) ) ;

// $key_pharase = preg_replace('/最終講義/', '' , $key_pharase) ;
$key_pharase = preg_replace('/\#.*/um', '' , $key_pharase) ;
$key_pharase = str_replace("最終講義-", " ", $key_pharase);
$key_pharase = str_replace("最終講義ー", " ", $key_pharase);
// $key_pharase = str_ireplace("####", " ", $key_pharase);
// $key_pharase = str_ireplace("###", " ", $key_pharase);

// echo "<br> key_phrase = ".$key_pharase ;

$tags = show_keyphrase($appid, $key_pharase);

echo "<br> tags = ".$tags ;

// Tags (key_phrase を Yahoo API から取得)
$key_pharase_title = space_trim($course_name)." ".$courselist_rows['department_name'] ;

if(preg_match( "/名大トピックス/", $farewell_lecture_home_del_firstline ) ){
    //名大トピックスが含まれている
    $key_pharase = $key_pharase_title ;
    }else{
    //名大トピックスが含まれていない
    $key_pharase = preg_replace('/(?:\n|\r|\r\n)/', '', space_trim(strip_tags(mb_substr($farewell_lecture_home_del_firstline,0,500))) ) ;
    // $key_pharase = preg_replace('/(?:\n|\r|\r\n)/', '', space_trim(strip_tags($farewell_lecture_home_del_firstline)) ) ;
    }

$key_pharase .= preg_replace('/(?:\n|\r|\r\n)/', '', space_trim(strip_tags(mb_substr($description,0,500))) ) ;
// $key_pharase .= preg_replace('/(?:\n|\r|\r\n)/', '', space_trim(strip_tags($description)) ) ;

// Tagsに相応しくない文字を削除
// $key_pharase = preg_replace('/最終講義/', '' , $key_pharase) ;
// $key_pharase = preg_replace('/\#.*/um', '' , $key_pharase) ;
$key_pharase_title = str_replace("最終講義-", "", $key_pharase_title);
$key_pharase_title = str_replace("最終講義ー", "", $key_pharase_title);
$key_pharase_title = str_replace("最終講義―", "", $key_pharase_title);
$key_pharase_title = str_replace("II", "", $key_pharase_title);
$key_pharase_titel = str_replace("I", "", $key_pharase_title);

$key_pharase = str_ireplace("####", " ", $key_pharase);
$key_pharase = str_ireplace("###", " ", $key_pharase);

$key_pharase = preg_replace('/\&oslash\;/', 'o', $key_pharase);

// echo "<br><br> key_phrase = ".$key_pharase_title." ".$key_pharase ;

if(preg_match( "/[ぁ-ん]+|[ァ-ヴー]+/u", $key_pharase) ){
    //日本語文字列が含まれている
    $tags = show_keyphrase($appid, $key_pharase_title." ".$key_pharase );
    }else{
    //日本語文字列が含まれていない
    $tags = show_keyphrase($appid, $key_pharase_title );
    }

// echo "<br> tags = ".$tags ;

if(strpos($courselist_rows['course_name'],'最終講義') !== false){

    $farewell_delete_name = array("最終講義-", "最終講義ー", "最終講義－", "最終講義―");
    $course_name = str_replace($farewell_delete_name, "", $course_name);

// 最終講義のファイル名
// $file_name = "./src/pages/farewell/".$course_id."-".$course_name."-".$courselist_rows['department_name'].".md" ;
$file_name = "farewell/".sprintf('%03d', $course_id)."-".$course_name ;
$templateKey = "farewell" ;

$main_text = "
|   |   |
|---|---|
".$farewell_date."
".$farewell_place."
|   |   |


".$farewell_lecture_home."


".$farewell_lecture_introduction."


".$farewell_lecture_resources."
-----" ;

// 改行が連続する場合、ひとつにまとめる
// $main_text = preg_replace('/(\n|\r|\r\n)+/us',"\n", $main_text );    

$courselist_text =
"---
# テンプレート指定
templateKey: \"".$templateKey."\"

# コースID
course_id: \"".sprintf('%03d', $course_id)."\"

# タイトル
title: \"".$course_name."\"

# 簡単な説明
description: >-
  ".preg_replace('/(?:\n|\r|\r\n)/', '', space_trim(strip_tags(mb_substr($farewell_lecture_home_del_firstline,0,200))) )." ....

# 講師名
lecturer: \"".$lecturer."\"

# 部局名
department: \"".$division."\"

# 開講時限
term: \"".$term."\"

# 対象者、単位数、授業回数
target: \"".preg_replace('/(?:\n|\r|\r\n)/', "\n", $class_is_for )."\"

# 授業回数
classes: \"\"

# 単位数
credit: \"\"

# pdfなどの追加資料
## rootフォルダはstaticになっている
attachments:


# 関連するタグ
# （Yahoo API Key-Phase により取得。入力はタイトル、部局名と授業ホーム、出力はキーフレーズ（tags））
tags:".$tags."

# 色付けのロールにするか
featuredpost: true

# 画像
## rootフォルダはstaticになっている
## なにも指定がない場合はデフォルトの画像が表示される
## 映像がある場合は映像優先で表示する
featuredimage: ".$featuredimage."

# 映像のURL
## なにも指定がない場合は画像が表示される
movie: ".$movie."

# 記事投稿日
date: ".$course_date."
---
" ;

  }else{

$course_name = $course_name."-".$courselist_rows['year'] ;
// 授業のファイル名
// $file_name = "./src/pages/courses/".$course_id."-".$course_name."-".$division."-".$courselist_rows['year'].".md" ;
// $file_name = "./src/pages/courses/".$course_id."-".$course_name."-".$division.".md" ;
$file_name = "courses/".sprintf('%03d', $course_id)."-".$course_name ;

$templateKey = "courses" ;

// 以下、フルに出力する場合
$main_text = "
".$course_home."


".$teaching_tips."


".$achievement."


".$syllabus."


".$calendar."


".$lecture_notes."


".$assignment."


".$evaluation."


".$related_resources."
-----" ;

// 以下、授業ホーム、授業の工夫、シラバス、スケジュール、講義ノート、成績評価のみを出力
// $main_text = "
// ".$course_home."

// ".$teaching_tips."

// ".$syllabus."

// ".$calendar."

// ".$lecture_notes."

// ".$evaluation ;

// 改行が連続する場合、ひとつにまとめる
// $main_text = preg_replace('/(\n|\r|\r\n)+/us',"\n", $main_text );

$courselist_text =
"---
# テンプレート指定
templateKey: \"".$templateKey."\"

# コースID
course_id: \"".sprintf('%03d', $course_id)."\"

# タイトル
title: \"".$course_name."\"

# 簡単な説明
description: >-
  ".preg_replace('/(?:\n|\r|\r\n)/', '', space_trim(strip_tags(mb_substr($description,0,200))) )." ....
# 講師名
lecturer: \"".$lecturer."\"

# 部局名
department: \"".$division."\"

# 開講時限
term: \"".$term."\"

# 対象者、単位数、授業回数
target: \"".preg_replace('/(?:\n|\r|\r\n)/', "\n", $class_is_for )."\"

# 授業回数
classes: \"\"

# 単位数
credit: \"\"

# pdfなどの追加資料
attachments:
".$attaches."
# 関連するタグ
# （Yahoo API Key-Phase により取得。入力はタイトル、部局名と授業ホーム、出力はキーフレーズ（tags））
tags:".$tags."

# カテゴリ
category:
".$category."

# 色付けのロールにするか
featuredpost: true

# 画像
## rootフォルダはstaticになっている
## なにも指定がない場合はデフォルトの画像が表示される
## 映像がある場合は映像優先で表示する
featuredimage: ".$featuredimage."

# 映像のURL
## なにも指定がない場合は画像が表示される
movie: ".$movie."

# 記事投稿日
date: ".$course_date."
---
" ;

  }


// テンポラリーファイルに書き込み
$fp_tmp = fopen('tmp.md', 'w');
fwrite($fp_tmp,$main_text);
fclose($fp_tmp);

echo "<br>ID: ".$course_id."\t".$file_name ;

// 以下は html へ吐き出す内容の表示
// echo htmlspecialchars("<br>".$course_id."-".$course_name."\t&emsp;<a href=\"http://ocw.nagoya-u.jp/index.php?lang=ja&mode=c&id=".$course_id."&amp;page_type=index \" target=\"_blank\" rel=\"noopener\"> 現OCW </a>" );
// echo htmlspecialchars("\t&emsp;<a href=\"http://ocw.ilas.nagoya-u.ac.jp/".$file_name."\"target=\"_blank\" rel=\"noopener\"> 新OCW </a>\n") ;

// tmp.html へも出力
$check_list .="<tr><td><a href=\"http://ocw.nagoya-u.jp/index.php?lang=ja&mode=c&id=".$course_id."&amp;page_type=index \" target=\"_blank\" rel=\"noopener\"> 現OCW </a></td>" ;
$check_list .="<td><a href=\"https://nuocw-preview.netlify.app/".$file_name."\"target=\"_blank\" rel=\"noopener\"> 新OCW </a></td>" ;
$check_list .="<td>".$course_id."-".$course_name."</td></tr>\n" ;

// echo "<br>ID: ".$course_id."\t".$file_name."\t を出力しました。" ;
// echo "<br>".$file_name."\t を出力しました。" ;

// 一行ずつ読み込んで処理する

$fp_tmp = fopen('tmp.md', 'r');
$fp_tmp2 = fopen('tmp2.md', 'w');
// $fp = fopen($file_name, "w");

$ocwlink = '/(?<=\{ocwlink file=\").+?(?=\")/';
$ocwlink_desc = '/(?<=desc=\").+?(?=\")/';

$ocwpagelink = '/(?<=\{ocwpagelink type=\").+?(?=\")/';
$ocwpagelink_desc = '/(?<=desc=\").+?(?=\")/';

$ocwimg = '/(?<=\{ocwimg file=\").+?(?=\")/';
$ocwimg_desc = '/(?<=alt=\").+?(?=\")/';

$contents_tag = '/\#+\s(\S+)\s/';
$contents_desc = '/\#+\s(\S+)\s/';

$studio_url = 'https://nuvideo.media.nagoya-u.ac.jp/embed/' ;
$studio_url_old = '/http:\/\/nuvideo.media.nagoya-u.ac.jp\/embed\//' ;
$studio_media = '/http:\/\/studio.media.nagoya-u.ac.jp\/videos\/watch.php\?\v\=/' ;

$studio_thumbs_url = 'https://nuvideo.media.nagoya-u.ac.jp/thumbs/' ;
$studio_thumbs_url_old = '/http:\/\/nuvideo.media.nagoya-u.ac.jp\/thumbs\//' ;



// 名大トピックス
$nu_topics_link = '/名大トピックス/';
$nu_topics_desc = '[名大トピックス](http://www.nagoya-u.ac.jp/about-nu/public-relations/publication/topics-archive.html)' ;


while ($line = fgets($fp_tmp)) {

    // -----
    // ここに$lineに対して何かしらの処理を書く
    // -----
 
    // 改行コードを LF(\n) に統一
        $line = preg_replace("/\r\n|\r/","\n",$line);
        // $line = str_replace("\r\n","\n",$line);
        // $line = str_replace("\r","\n",$line);
        // echo "<br>line : ".$line."<br>";

    // 全角スペースを半角へ変換
        $line = mb_convert_kana($line, 's');
       
    // 文字列の先頭、末尾の半角全角スペース削除
        $line = space_trim($line) ;

        
    // ocwimg
    if( preg_match_all($ocwimg, $line, $ocwimg_match) ){
        //print_r($file_match);
          preg_match_all($ocwimg_desc, $line, $desc_match);
        //print_r($desc_match);
        $line = "![".$desc_match[0][0]."](https://ocw.nagoya-u.jp/files/".$course_id."/".$ocwimg_match[0][0].") " ;
        // $line = "\n![".$desc_match[0][0]."](http://ocw.ilas.nagoya-u.ac.jp/files/".$course_id."/".$ocwimg_match[0][0].") " ;
     }    

     // ocwlink
    if( preg_match_all($ocwlink, $line, $ocwlink_match) ){
        // echo "<br>ocwlink : ".$ocwlink." ocwlink_match : ".$ocwlink_match[0][0]."<br>" ;
        preg_match_all($ocwlink_desc, $line, $desc_match);
        // print_r($desc_match);
        $line = "[".$desc_match[0][0]."](https://ocw.nagoya-u.jp/files/".$course_id."/".$ocwlink_match[0][0].") \n" ;
        // $line = "[".$desc_match[0][0]."](http://ocw.ilas.nagoya-u.ac.jp/files/".$course_id."/".$ocwlink_match[0][0].") \n\n" ;
        // echo "<br>line : ".$line."<br>";
    }

    // ocwpagelink
    if( preg_match_all($ocwpagelink, $line, $ocwpagelink_match) ){
        // echo "<br>ocwpagelink : ".$ocwpagelink." ocwpagelink_match : ".$ocwpagelink_match[0][0]."<br>" ;
        preg_match_all($ocwpagelink_desc, $line, $desc_match);
        // print_r($desc_match);
        $line = "[".$desc_match[0][0]."](#".$desc_match[0][0].") \n" ;
        // echo "<br>line : ".$line."<br>";
    }    

    // stormvideo は削除
    if(preg_match('/stormvideo_link/',$line)){
        $line = "\n" ;
      }

    // スタジオ動画配信サーバ URL の変更
    $line = preg_replace($studio_media, $studio_url, $line);
    // echo "<br>line : ".$line."<br>";
    $line = preg_replace($studio_url_old, $studio_url, $line);
    // echo "<br>line : ".$line."<br>";
    $line = preg_replace($studio_thumbs_url_old, $studio_thumbs_url, $line);
    
    // 名大トピックス
    $line = preg_replace( $nu_topics_link , $nu_topics_desc ,$line ) ;

        // $ii = 0;
        // foreach ($desc_match[0] as $value){
        //     $resources .= 
        //     "- [".$desc_match[0][$ii]."](/files/".$course_id."/".$file_match[0][$ii].")\n" ;
        //     $ii++;
        //   }
      
        // $resources = preg_replace('/(?<={).*?(?=})/', '' , $resources);
        // $resources = preg_replace('/\{\}/', '' , $resources);
        // $resources = str_replace('\\', '' , $resources) ;
        
    // vsyllabus_direct_link
    if (preg_match('/<a target=\"_blank\" href=/', $line)){
        $line = preg_replace('/<a target=\"_blank\" href=/', '<iframe src=', $line);    
    }
    $vsyllabus_direct_link_to = ' width="640" height="360" frameborder="0" allowfullscreen></iframe>' ;
    if (preg_match('/(?<=><img).+?(?=<\/a>)/', $line, $vsyllabus_direct_link_match)){        
        $vsyllabus_match = "><img".$vsyllabus_direct_link_match[0]."</a>";
        $line = str_replace($vsyllabus_match,$vsyllabus_direct_link_to,$line) ;  
            // echo "<br>line : ".$line."<br>";       
    }
    if (preg_match('/(?<=>後輩へのメッセージビデオ).+?(?=<\/a>)/', $line, $vsyllabus_direct_link_match)){        
        $vsyllabus_match = ">後輩へのメッセージビデオ".$vsyllabus_direct_link_match[0]."</a>";
        $line = str_replace($vsyllabus_match,$vsyllabus_direct_link_to,$line) ;  
            // echo "<br>line : ".$line."<br>";       
    }    

    // 残っている html たとえば <dd> タグを削除
    // $line = strip_tags ($line) ;

    // なぜだか残っている「{tr}」を改行へ変換
    // $line = str_replace('{tr}', "\n" , $line) ;

    //$line = convert_ocwlink ($line) ;
    // echo "<br>line : ".$line."<br>";
    fwrite($fp_tmp2, $line);
}

// ファイルを閉じる
fclose($fp_tmp);
fclose($fp_tmp2);

// $fp_tmp2 = fopen('tmp2.md', 'r');
// $main_text = fread($handle, filesize($tmp_filename));
// fclose($fp_tmp2);

$main_text = file_get_contents('tmp2.md');

// 改行が3連続する場合、ひとつにまとめる
// $main_text = preg_replace('/(\n\n\n)+/us',"\n", $main_text );

// 最後に、よく分からない タグを「# 」で協調
// $main_text = str_replace('{', '# ' , $main_text) ;  

// $courselist_text .= ltrim( $main_text ) ;
$courselist_text .= $main_text ;


// echo "<br><br>" ; var_dump($courselist_text) ;

$file_name = "./src/pages/".$file_name.".md"; 
$fp = fopen($file_name, "w");
fwrite($fp,$courselist_text);
fclose($fp);

}


// ファイルの中身を読んで文字列に格納する
// $fp_tmp = fopen('tmp.md', 'r');
// $main_text = fread('tmp.md', filesize('tmp.md'));
// fclose($fp_tmp);


// $handle = fopen("tmp2.md", "r");
// $main_text = fread($handle, filesize("tmp2.md"));
// fclose($handle);

// $courselist_text .= ltrim( $main_text ) ;
// echo "<br><br>" ; var_dump($courselist_text) ;

// DBの切断        
$close_ocwdb  = pg_close($ocwdb);
if ($close_ocwdb){
    print('<br><br>ocwdb：切断に成功しました。<br>');
    }

$check_list .="</table><br></body></html>" ;
fwrite($fp_html,$check_list);
fclose($fp_html);
    
exec('/bin/rm tmp.md'  );
exec('/bin/rm tmp2.md'  );

exec('/bin/rm /Users/yamazato/Sites/NUOCW-Project/nuocw-preview/src/pages/courses/*.md') ;
exec('/bin/rm /Users/yamazato/Sites/NUOCW-Project/nuocw-preview/src/pages/farewell/*.md') ;

// 以下、サンプルページ
// exec('/bin/cp /Users/yamazato/Sites/nuocw-mdfile-generator/course-sample/*.md /Users/yamazato/Sites/nuocw-mdfile-generator/src/pages/courses/') ;
// exec('/bin/cp /Users/yamazato/Sites/nuocw-mdfile-generator/farewell-sample/*.md /Users/yamazato/Sites/nuocw-mdfile-generator/src/pages/farewell/') ;

exec('/bin/cp /Users/yamazato/Sites/nuocw-mdfile-generator/src/pages/courses/*.md /Users/yamazato/Sites/NUOCW-Project/nuocw-preview/src/pages/courses/') ;
exec('/bin/cp /Users/yamazato/Sites/nuocw-mdfile-generator/src/pages/farewell/*.md /Users/yamazato/Sites/NUOCW-Project/nuocw-preview/src/pages/farewell/') ;

// adeos
// exec('/bin/rm /Volumes/yamazato/Sites/nuocw-new-site/src/pages/courses/*.md') ;
// exec('/bin/rm /Volumes/yamazato/Sites/nuocw-new-site/src/pages/farewell/*.md') ;

// exec('/bin/cp /Users/yamazato/Sites/NUOCW-Project/nuocw-preview/static/kanban/* /Volumes/yamazato/Sites/nuocw-new-site/static/kanban/'  );
// exec('/bin/cp /Users/yamazato/Sites/nuocw-mdfile-generator/src/pages/courses/*.md /Volumes/yamazato/Sites/nuocw-new-site/src/pages/courses/') ;
// exec('/bin/cp /Users/yamazato/Sites/nuocw-mdfile-generator/src/pages/farewell/*.md /Volumes/yamazato/Sites/nuocw-new-site/src/pages/farewell/') ;



?>
</body>
</html>