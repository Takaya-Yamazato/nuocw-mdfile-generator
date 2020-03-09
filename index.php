<html>
<head><title>nuocw-mdfile-generator</title></head>
<body>

<?php
require_once(dirname(__FILE__) . '/vendor/autoload.php');
require_once('config.php');
require_once('library.php');
// require_once('lib/ocw_init.php') ;
require_once('lib/class/OCWDB.class.php');

$nuocw_new_site_directory = '/Users/yamazato/Sites/nuocw-new-site/static/' ;

exec('/bin/rm ./src/pages/courses/*'  );
exec('/bin/rm ./src/pages/farewell/*' );

// DBに接続
// $ocwpdb = pg_connect(ocwpdb);
// if (!ocwpdb) {
//     die('ocwpdb：接続失敗です。'.pg_last_error());
// }
// print('ocwpdb：接続に成功しました。<br>');

// 出力ソートキー
$sort_key = "course_id";
// $sort_key = "41" ;
$sort_order = "ASC";
$limit = "LIMIT 2 OFFSET 50" ;
// 全てのファイルを出力する場合
// $limit = "" ;

// // SQL文の作成
// $courselist_sql = "SELECT * FROM courselist_by_coursename
//         -- WHERE exist_lectnotes='t'
//         ORDER BY $sort_key $sort_order $limit ; ";
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
                        i.instructor_name
                      FROM course_instructor ci, instructor i
                      WHERE ci.course_id = c.course_id AND
                            ci.instructor_id = i.instructor_id
                      ORDER BY ci.disp_order ASC
                     ), '／') as instructor_name
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
// $sort_key = "course_id";
$sort_key = $courselist_rows['course_id'] ;

$course_name = $courselist_rows['course_name']  ;
$course_name = strip_tags( $course_name );
$course_name = space_trim( $course_name ) ;
// $course_name = preg_replace('/\s(?=\s)/', '', $course_name );
$course_name = preg_replace("/( |　)/", "-", $course_name );
$course_name = str_replace('/', '／' , $course_name );
$course_name = preg_replace('/-+/', '-', $course_name) ;

// $course_name = preg_replace("/(-|---)/", "-", $course_name );
// $course_name = $course_name."-".$sort_key."-".$courselist_rows['year'] ;
// $course_name = $course_name."-".$courselist_rows['department_name']."-".$courselist_rows['year'] ;

// echo "<br>".$course_name ;

// 記事投稿日
$course_date_sql = "SELECT * FROM event WHERE event_id IN
             (SELECT event_id FROM course_status WHERE  course_id = $sort_key) 
             ORDER BY event_id DESC" ;
$course_date_result = pg_query($course_date_sql);
if (!$course_date_result) {
    die('クエリーが失敗しました。'.pg_last_error());
}

$course_date_array = pg_fetch_all($course_date_result);
// print_r($course_date_array);

$course_date = $course_date_array[0]['time'];

$lecturer = space_trim($courselist_rows['instructor_name']) ;

// SQL文の作成
// $course_sql = "SELECT * FROM course WHERE course.course_id = $sort_key " ;
$course_sql = "SELECT * FROM course 
            INNER JOIN course_status ON course.course_id = course_status.course_id 
            WHERE course.archive = 'f' 
            AND course_status.status='01' 
            AND course_status.lang='ja' 
            AND course.course_id = $sort_key; " ;
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
// echo "<br>".$division."<br>" ;

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
                    WHERE course.course_id = $sort_key 
                    AND del_flg = 'f' ; " ;
$attachments_result = pg_query($attachments_sql);
if (!$attachments_result) {
    die('クエリーが失敗しました。'.pg_last_error());
}
$attachments_array = pg_fetch_all($attachments_result);

$file_directory = $nuocw_new_site_directory."files/".$sort_key."/*" ;
$file_directory_result = glob( $file_directory );
// echo "<br>".dirname($file_directory)."<br>" ; 
// var_dump($file_directory_result);




if (!$attachments_array){
    // echo "データがありません！" ;
    $attachments = "" ;
}else{
    // echo "<br>" ;
    // print_r($attachments_array);
    // echo "<br>" ;
    $attachments = call_user_func_array('array_merge', $attachments_array); 
    // print_r($attachments);
    $attaches = "";
    $ii = 0 ;
    $featuredimage = "/img/common/thumbnail.png";
    foreach ($attachments_array as $attachment){
        if ($attachment['description'] == '看板画像'){
            // echo $attachment['name']."    " ;
            // echo $attachment['description']."<br>" ;

            foreach ( $file_directory_result as $filename) {
                if ( strcasecmp(basename($filename), trim( $attachment['name'] )) == 0 ) {
                    $featuredimage = "/files/".$sort_key."/".trim( $attachment['name'] ) ; 
                    
                }
            }
        }else{
            // $attaches .= "  - name: \"".$attachment['description'].= "\" \n" ;
            // $attaches .= "    path: /files/".$sort_key."/".$attachment['name'].= "\n" ;
            // $attache_file_name = trim ( $attachment['name'] ) ;
            
            foreach ( $file_directory_result as $filename) {
                if ( strcasecmp(basename($filename), trim( $attachment['name'] )) == 0 ) {
                    // echo "A match was found.  ". basename($filename). " = ". trim ( $attachment['name'] ) . "<br>";
                    $attaches .= "  - name: \"".$attachment['description'].= "\" \n" ;
                    $attaches .= "    path: /files/".$sort_key."/".$attachment['name'].= "\n" ;
                    
                } else {
                    // echo "A match was not found. ". basename($filename). " != ". trim ( $attachment['name'] ) . "<br>";
                    
                }
                
            }
        // foreach ($attachment as $attach){
        // echo $attach."<br>"  ;
        // "  - name: ".$attaches .= "\n".$attach ;
        // }
        }
        $ii ++ ;
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
    // echo "データがありません！" ;
    $class_is_for = "" ;
}else{
    // echo "class_is_for_array<br>" ;
    // print_r($class_is_for_array);
    $class_is_for = space_trim(strip_tags($class_is_for_array[0]['contents'])) ;
}

$class_is_for = preg_replace('/(\n|\r|\r\n)+/us',"\n", $class_is_for );

// echo "<br>".$class_is_for;

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
// $course_home = convert_ocwlink ($course_home , $sort_key) ;

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
// $syllabus = convert_ocwlink ($syllabus, $sort_key) ;

// 53             | スケジュール | Calendar              | calendar         |        530
$calendar_sql = "SELECT contents.contents 
                    FROM pages, page_contents, contents 
                    WHERE pages.course_id = $sort_key 
                    AND pages.page_type = '53' 
                    AND pages.page_id = page_contents.page_id 
                    AND contents.pid = page_contents.contents_id 
                    AND contents.type = '1301' 
                    ORDER BY contents.id DESC LIMIT 1; ";

$calendar = get_contents ($calendar_sql) ;
$calendar = convert_ocwlink ($calendar, $sort_key) ;

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
// $lecture_notes = convert_ocwlink ($lecture_notes, $sort_key) ;

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
// $assignment = convert_ocwlink ($assignments, $sort_key) ;

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
// $evaluation = convert_ocwlink ($evaluation, $sort_key) ;

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
// $achievement = convert_ocwlink ($achievement, $sort_key) ;

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
// $related_resources = convert_ocwlink ($related_resources, $sort_key) ;

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
// $teaching_tips = convert_ocwlink ($teaching_tips, $sort_key) ;

// 71             | 最終講義・講義ホーム   | Farewell Lecture Home | f_index          |        515
$farewell_lecture_home_sql = "SELECT contents.contents 
                    FROM pages, page_contents, contents 
                    WHERE pages.course_id = $sort_key 
                    AND pages.page_type = '71' 
                    AND pages.page_id = page_contents.page_id 
                    AND contents.pid = page_contents.contents_id 
                    AND contents.type = '1101' 
                    ORDER BY contents.id DESC LIMIT 1; ";

// echo "<br>farewell_lecture_home_sql ".$farewell_lecture_home_sql."<br>" ;
$farewell_lecture_home = get_contents($farewell_lecture_home_sql);
$farewell_lecture_home_del_firstline = preg_replace('/\###.*/um', '' , $farewell_lecture_home);
// print_r($farewell_lecture_home);

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
// $farewell_lecture_introduction = convert_ocwimg ($farewell_lecture_introduction, $sort_key);

// 73             | 最終講義・講義資料     | Resources             | f_resources      |        585
$farewell_lecture_resources_sql = "SELECT contents.contents 
                    FROM pages, page_contents, contents 
                    WHERE pages.course_id = $sort_key 
                    AND pages.page_type = '73' 
                    AND pages.page_id = page_contents.page_id 
                    AND contents.pid = page_contents.contents_id 
                    AND contents.type = '1101' 
                    ORDER BY contents.id DESC LIMIT 1; ";

// $farewell_lecture_resources = "\n" ;
$farewell_lecture_resources = get_contents($farewell_lecture_resources_sql);
// $farewell_lecture_resources = convert_ocwlink ($farewell_lecture_resources, $sort_key) ;

// 講義映像
$movie_sql = "SELECT url_flv FROM visual_syllabus 
            WHERE vsyllabus_id = 
                (SELECT vsyllabus_id FROM course 
                 WHERE course_id = $sort_key ) ; " ;
$movie_result = pg_query($movie_sql);
if (!$movie_result) {
    die('クエリーが失敗しました。'.pg_last_error());
}
$movie = pg_fetch_row($movie_result);
$movie = $movie[0] ;
// echo "<br>"; print_r($movie);
$movie = str_ireplace("http://studio.media.nagoya-u.ac.jp/videos/watch.php?v=", "https://nuvideo.media.nagoya-u.ac.jp/embed/", $movie);
if(preg_match('/FlvPlayer/',$movie)){
    $movie = '' ;
    echo "<br>".$movie ;
  }


// $movie_description = "SELECT description FROM visual_syllabus 
//             WHERE vsyllabus_id = 
//                 (SELECT vsyllabus_id FROM course 
//                 WHERE course_id = $sort_key ) ; " ;

// $movie_duration = "SELECT time FROM visual_syllabus 
//             WHERE vsyllabus_id = 
//                 (SELECT vsyllabus_id FROM course 
//                 WHERE course_id = $sort_key ) ; " ;


// $file = '/(?<=\{ocwlink file=\").+?(?=\")/';
// preg_match_all($file, $farewell_lecture_resources, $file_match);
// //print_r($file_match);
// $desc = '/(?<=desc=\").+?(?=\")/';
// preg_match_all($desc, $farewell_lecture_resources, $desc_match);
// //print_r($desc_match);

// $ii = 0;
// foreach ($desc_match[0] as $value){
//     $farewell_lecture_resources .= 
//     "[".$desc_match[0][$ii]."](/files/".$sort_key."/".$file_match[0][$ii].")\n" ;
//     $ii++;
// }
//$farewell_lecture_resources .= "\n[".$desc_match[0][0]."](/files/".$sort_key."/".$file_match[0][0].")" ;
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
// $destination = "[/files/".$sort_key."/" ;

// $farewell_lecture_resources = preg_replace('/\{ocwlink file="/', $destination , $farewell_lecture_resources);
// $farewell_lecture_resources = preg_replace('/" desc="/', $output , $farewell_lecture_resources);
// $farewell_lecture_resources = preg_replace('/"\}/', ")", $farewell_lecture_resources);     
// $destination = "![/files/".$sort_key."/" ;

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

if(strpos($courselist_rows['course_name'],'最終講義') !== false){

// 最終講義のファイル名
$file_name = "./src/pages/farewell/".$sort_key."-".$course_name."-".$courselist_rows['department_name'].".md" ;
$templateKey = "farewell" ;

$main_text = "
".$farewell_lecture_home."


".$farewell_lecture_introduction."


".$farewell_lecture_resources ;

// 改行が連続する場合、ひとつにまとめる
// $main_text = preg_replace('/(\n|\r|\r\n)+/us',"\n", $main_text );    

$courselist_text =
"---
# テンプレート指定
templateKey: \"".$templateKey."\"

# コースID
course_id: \"".$sort_key."\"

# タイトル
title: \"".$course_name."\"

# 簡単な説明
description: >-
  ".preg_replace('/(?:\n|\r|\r\n)/', '', space_trim(strip_tags(mb_substr($farewell_lecture_home_del_firstline,0,100))) )."...

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
".$attaches."
# 関連するタグ
tags:

# 色付けのロールにするか
featuredpost: true

# 画像
## rootフォルダはstaticになっている
## なにも指定がない場合はデフォルトの画像が表示される
## 映像がある場合は映像優先で表示する
featuredimage: ".$featuredimage."

# 映像のURL
## なにも指定がない場合は画像が表示される
featuredmovie: ".$movie."

# 記事投稿日
date: ".$course_date."
---

" ;

  }else{

// 授業のファイル名
$file_name = "./src/pages/courses/".$sort_key."-".$course_name."-".$courselist_rows['department_name']."-".$courselist_rows['year'].".md" ;

$templateKey = "courses" ;

$main_text = "
".$course_home."


".$teaching_tips."


".$achievement."


".$syllabus."


".$calendar."


".$lecture_notes."


".$assignment."


".$evaluation."


".$related_resources ;

// 改行が連続する場合、ひとつにまとめる
// $main_text = preg_replace('/(\n|\r|\r\n)+/us',"\n", $main_text );

$courselist_text =
"---
# テンプレート指定
templateKey: \"".$templateKey."\"

# コースID
course_id: \"".$sort_key."\"

# タイトル
title: \"".$course_name."\"

# 簡単な説明
description: >-
  ".preg_replace('/(?:\n|\r|\r\n)/', '', space_trim(strip_tags(mb_substr($course_home,0,100))) )."...

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
tags:

# カテゴリ
category:
  - culture

# 色付けのロールにするか
featuredpost: true

# 画像
## rootフォルダはstaticになっている
## なにも指定がない場合はデフォルトの画像が表示される
## 映像がある場合は映像優先で表示する
featuredimage: ".$featuredimage."

# 映像のURL
## なにも指定がない場合は画像が表示される
featuredmovie: ".$movie."

# 記事投稿日
date: ".$course_date."
---

" ;

  }

// $courselist_text .= ltrim( $main_text ) ;

// テンポラリーファイルに書き込み
$fp_tmp = fopen('tmp.md', 'w');
fwrite($fp_tmp,$main_text);
fclose($fp_tmp);

echo "<br>ID: ".$sort_key."\t".$file_name."\t を出力しました。" ;
// echo "<br>".$file_name."\t を出力しました。" ;

// 一行ずつ読み込んで処理する

$fp_tmp = fopen('tmp.md', 'r');
$fp_tmp2 = fopen('tmp2.md', 'w');
// $fp = fopen($file_name, "w");

$ocwlink = '/(?<=\{ocwlink file=\").+?(?=\")/';
$ocwlink_desc = '/(?<=desc=\").+?(?=\")/';

$ocwimg = '/(?<=\{ocwimg file=\").+?(?=\")/';
$ocwimg_desc = '/(?<=alt=\").+?(?=\")/';

$contents_tag = '/\#+\s(\S+)\s/';
$contents_desc = '/\#+\s(\S+)\s/';

while ($line = fgets($fp_tmp)) {

    // -----
    // ここに$lineに対して何かしらの処理を書く
    // -----
 
    // 改行コードを LF(\n) に統一
        $line = preg_replace("/\r\n|\r/","\n",$line);
        $line = str_replace("\r\n","\n",$line);
        $line = str_replace("\r","\n",$line);
    // print_r($contents);

    // 全角スペースを半角へ変換
        $line = mb_convert_kana($line, 's');
       
    // 文字列の先頭、末尾の半角全角スペース削除
        $line = space_trim($line) ;
    
    if( preg_match_all($ocwlink, $line, $ocwlink_match) ){
        // ocwlink
        // echo "<br>file_match<br>" ;
        // echo $file_match[0][0];
        // echo "<br>desc_match<br>" ;
        preg_match_all($ocwlink_desc, $line, $desc_match);
        // print_r($desc_match);
        $line = "[".$desc_match[0][0]."](/files/".$sort_key."/".$ocwlink_match[0][0].") \n" ;
    }

        // ocwimg
    if( preg_match_all($ocwimg, $line, $ocwimg_match) ){

        //print_r($file_match);

        preg_match_all($ocwimg_desc, $line, $desc_match);
            //print_r($desc_match);
        $line = "![".$desc_match[0][0]."](/files/".$sort_key."/".$ocwimg_match[0][0].") " ;
    }    
    
        // $ii = 0;
        // foreach ($desc_match[0] as $value){
        //     $resources .= 
        //     "- [".$desc_match[0][$ii]."](/files/".$sort_key."/".$file_match[0][$ii].")\n" ;
        //     $ii++;
        //   }
      
        // $resources = preg_replace('/(?<={).*?(?=})/', '' , $resources);
        // $resources = preg_replace('/\{\}/', '' , $resources);
        // $resources = str_replace('\\', '' , $resources) ;
    
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
$courselist_text .= ltrim( $main_text ) ;
// echo "<br><br>" ; var_dump($courselist_text) ;

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

exec('/bin/rm tmp.md'  );
exec('/bin/rm tmp2.md'  );

exec('/bin/rm /Users/yamazato/Sites/nuocw-new-site/src/pages/courses/*.md') ;
exec('/bin/rm /Users/yamazato/Sites/nuocw-new-site/src/pages/farewell/*.md') ;

exec('/bin/cp /Users/yamazato/Sites/nuocw-mdfile-generator/course-sample/*.md /Users/yamazato/Sites/nuocw-mdfile-generator/src/pages/courses/') ;
exec('/bin/cp /Users/yamazato/Sites/nuocw-mdfile-generator/farewell-sample/*.md /Users/yamazato/Sites/nuocw-mdfile-generator/src/pages/farewell/') ;

exec('/bin/cp /Users/yamazato/Sites/nuocw-mdfile-generator/src/pages/courses/*.md /Users/yamazato/Sites/nuocw-new-site/src/pages/courses/') ;
exec('/bin/cp /Users/yamazato/Sites/nuocw-mdfile-generator/src/pages/farewell/*.md /Users/yamazato/Sites/nuocw-new-site/src/pages/farewell/') ;

exec('/bin/rm /Users/yamazato/Sites/nuocw-new-site/src/pages/farewell/*.md') ;
exec('/bin/cp /Users/yamazato/Sites/nuocw-mdfile-generator/farewell-sample/*.md /Users/yamazato/Sites/nuocw-new-site/src/pages/farewell/') ;

?>
</body>
</html>