<html>
<head><title>nuocw-mdfile-generator</title></head>
<body>

<?php
// phpinfo();
require_once(dirname(__FILE__) . '/vendor/autoload.php');
require_once('config.php');
require_once('library.php');
// require_once('lib/ocw_init.php') ;
require_once('lib/class/OCWDB.class.php');

$nuocw_new_site_directory = '/Users/yamazato/Sites/NUOCW-Project/nuocw-preview/static/' ;

exec('/bin/rm /Users/yamazato/Sites/nuocw-mdfile-generator/src/pages/courses-en/*'  );
exec('/bin/rm /Users/yamazato/Sites/nuocw-mdfile-generator/src/pages/farewell/*' );

// 看板画像フォルダの初期化
exec('/bin/rm /Users/yamazato/Sites/NUOCW-Project/nuocw-preview/static/kanban/*'  );
exec('/bin/rm /Users/yamazato/Sites/NUOCW-Project/nuocw-preview/static/kanban/*' );
// DBに接続
// $ocwpdb = pg_connect(ocwpdb);
// if (!ocwpdb) {
//     die('ocwpdb：接続失敗です。'.pg_last_error());
// }
// print('ocwpdb：接続に成功しました。<br>');

// 出力ソートキー
$course_id = "course_id";
// $course_id = "416" ;
$sort_order = "ASC";
$limit = "LIMIT 0 OFFSET 0" ;
// 全てのファイルを出力する場合
$limit = "" ;

// htmlへ書き出し
exec('/bin/rm ./tmp.html'  );
$html_file_name = "./tmp.html";
$fp_html = fopen($html_file_name, "w");
$check_list = "<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
    <title>新旧OCWデータチェック</title>
</head>
<body>
<table border=\"1\" cellspacing=\"0\" cellpadding=\"5\" bordercolor=\"#C0C0C0\">
<tr><td>番号</td><td><b>現OCW</b></td><td><b>新OCW</b></td><td width=\"200\"><b>ファイル名</b></td></tr>" ;
$kk = 0;

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
    $courselist_sql = "SELECT c.course_id, c.course_name_e as course_name,
                year, term, d.department_id, d.department_name_e as department_name, division,
                array_to_string(array(
                    SELECT i.instructor_name_e FROM course_instructor ci, instructor i
                    WHERE ci.course_id = c.course_id AND ci.instructor_id = i.instructor_id
                    ORDER BY ci.disp_order ASC ), '／') as instructor_name, time
                    FROM course c, department d, term_code_master tcm, course_status cs, event ev,
                    ((SELECT course_id FROM course_status WHERE status='02' AND lang='en')
                    EXCEPT (SELECT course_id FROM course_status WHERE status='09')) AS cs02
                    WHERE c.department_id = d.department_id AND
                    c.term = tcm.term_code AND c.course_id = cs.course_id
                    AND cs.event_id = ev.event_id AND cs02.course_id = c.course_id
                    AND cs.status='02' AND cs.lang ='en'
                ORDER BY c.course_id $sort_order $limit ";

$courselist_sql = "SELECT c.course_id, c.course_name_e as course_name,
                   year, term as course_semester,
                   d.department_id, d.department_name_e as department_name, division,
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
                             c_s.lang = 'en'
                  ) AND


                  NOT EXISTS (
                      SELECT c_s.status
                       FROM  course_status c_s
                       WHERE c_s.course_id = c.course_id AND
                             ((c_s.status = '08' AND lang = 'en') OR
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



for ($i = 0 ; $i < pg_num_rows($courselist_result) ; $i++){
    $courselist_rows = pg_fetch_array($courselist_result, NULL, PGSQL_ASSOC);
    // echo "<br><br>courselist_rows : ";
    // print_r($courselist_rows);
    // echo "<br><br>";
    //    echo $courselist_rows['contents'][0];
    //    echo $courselist_rows['course_id'];

// 出力ソートキー
// $course_id = "course_id";
$course_id = $courselist_rows['course_id'] ;

$course_name = $courselist_rows['course_name']  ;
$course_name = strip_tags( $course_name );
$course_name = space_trim( $course_name ) ;

// $course_name = preg_replace('/\s(?=\s)/', '', $course_name );

$course_name = str_replace('/', '／' , $course_name );
$course_name = str_replace('?', '？' , $course_name );
$course_name = str_replace('!', '！' , $course_name );
$course_name = str_replace(':', '：' , $course_name );



$course_name = str_replace('基礎セミナー-', '基礎セミナー' , $course_name );
$course_name = str_replace('図書館情報リテラシー：-', '図書館情報リテラシー：' , $course_name );
$course_name = str_replace('-—授業分析と教育の科学化—-', 'I-授業分析と教育の科学化-' , $course_name );
$course_name = str_replace('-—産業・組織の心理学—-', '-産業・組織の心理学-' , $course_name );
$course_name = str_replace('情報リテラシー-', '情報リテラシー' , $course_name );
$course_name = str_replace('-—教育方法概論—-', '-教育方法概論-' , $course_name );
$course_name = str_replace('−人間発達と社会の持続的発展の視点から−', '人間発達と社会の持続的発展の視点から' , $course_name );
$course_name = str_replace('—国分寺瓦を題材として—-', '国分寺瓦を題材として-' , $course_name );
$course_name = str_replace('—名大建築３年生が考える附属学校の校舎—-', '名大建築３年生が考える附属学校の校舎-' , $course_name );
$course_name = str_replace('−-食と健康', '−食と健康' , $course_name );
$course_name = str_replace('−内生的貨幣供給論および信用先行説の視点を取り込んで', '内生的貨幣供給論および信用先行説の視点を取り込んで' , $course_name );
$course_name = str_replace('―現代の学力とカリキュラム―', '―現代の学力とカリキュラム' , $course_name );
$course_name = str_replace('−-「法」と紛争解決―', '-「法」と紛争解決―' , $course_name );
$course_name = str_replace('-韓流ドラマから「パッチギ」まで-', '韓流ドラマから「パッチギ」まで' , $course_name );
$course_name = str_replace('-クリープ損傷力学から均質化法へ', 'クリープ損傷力学から均質化法へ' , $course_name );
$course_name = str_replace('教育に魅せられて-', '教育に魅せられて' , $course_name );
$course_name = str_replace('正義と法-', '正義と法' , $course_name );
$course_name = str_replace('-変成岩-', '-変成岩' , $course_name );
$course_name = str_replace('–-ワクワクしながら瞬き３回-–', 'ワクワクしながら瞬き３回-' , $course_name );
$course_name = str_replace('－過去・現在・未来－', '過去・現在・未来' , $course_name );
$course_name = str_replace('—コミュニケーション行為の歴史的考察(1)—-', '—コミュニケーション行為の歴史的考察(1)-' , $course_name );
$course_name = str_replace('-—授業分析と教育の科学化—', '-授業分析と教育の科学化' , $course_name );
$course_name = str_replace('—産業・組織の心理学—', '産業・組織の心理学' , $course_name );
$course_name = str_replace('-：-', '：' , $course_name );
$course_name = str_replace('—名大建築３年生が考える附属学校の校舎—', '—名大建築３年生が考える附属学校の校舎' , $course_name );
$course_name = str_replace('—中津川市加子母地区のムラづくりを体験—', '—中津川市加子母地区のムラづくりを体験' , $course_name );
$course_name = str_replace('生物リズムと行動研究-〜自由な学風と良き隣人に恵まれて〜', '生物リズムと行動研究〜自由な学風と良き隣人に恵まれて〜' , $course_name );
$course_name = str_replace('韓流ドラマから「パッチギ」まで-−日韓関係を考える', '韓流ドラマから「パッチギ」まで−日韓関係を考える' , $course_name );
$course_name = str_replace('—教育方法概論—', '教育方法概論' , $course_name );
$course_name = str_replace('−名大形成外科の道−', '名大形成外科の道-' , $course_name );
$course_name = str_replace('光は光速を超えて-', '光は光速を超えて' , $course_name );
$course_name = str_replace('動機づけ研究の歩みと到達点-〜さて、上がってきたのか、', '動機づけ研究の歩みと到達点〜さて、上がってきたのか、' , $course_name );
$course_name = str_replace('基礎セミナー-「法」と紛争解決', '基礎セミナー「法」と紛争解決' , $course_name );
// $course_name = str_replace('', '' , $course_name );
// $course_name = str_replace('', '' , $course_name );
// $course_name = str_replace('', '' , $course_name );
// $course_name = str_replace('', '' , $course_name );
// $course_name = str_replace('', '' , $course_name );
// $course_name = str_replace('', '' , $course_name );
// $course_name = str_replace('', '' , $course_name );
// $course_name = str_replace('', '' , $course_name );
// $course_name = str_replace('', '' , $course_name );
// $course_name = str_replace('', '' , $course_name );
// $course_name = str_replace('', '' , $course_name );
// $course_name = str_replace('', '' , $course_name );

// $course_name = preg_replace("/(-|---)/", "-", $course_name );
// $course_name = $course_name."-".$courselist_rows['year'] ;
// $course_name = $course_name."-".$course_id."-".$courselist_rows['year'] ;
// $course_name = $course_name."-".$courselist_rows['department_name']."-".$courselist_rows['year'] ;

// echo "<br>".$course_id." ".$course_name."\t: " ;

$file_name = preg_replace("/( |　)/", "-", $course_name );
$file_name = preg_replace('/-+/', '-', $file_name) ;
$file_name = preg_replace("/\(.+?\)/", "", $file_name);
// echo "<br>".$course_name ;
// echo "<br>".$course_id." ".$course_name." ".$file_name."<br>" ;

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
$lecturer_sql = "SELECT instructor_name_e, instructor_position_e
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
$lecturer .= implode ( ", ", $value ).", ";
}
$lecturer = mb_substr($lecturer, 0, -2);

// $lecturer = space_trim($lecturer_array[0]['instructor_name'])." ".space_trim($lecturer_array[0]['instructor_position']) ;
// echo "<br>Lecturer: ".$lecturer ;

// SQL文の作成
// $course_sql = "SELECT * FROM course WHERE course.course_id = $course_id " ;
$course_sql = "SELECT * FROM course
            INNER JOIN course_status ON course.course_id = course_status.course_id
            WHERE course.archive = 'f'
            AND course_status.status='01'
            AND course_status.lang='en'
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

if (!!$course_array){
// echo "<br>course_array<br>" ;
// print_r($course_array);
// echo "<br>".$course_array[0]['course_name_e']."<br>" ;
// echo "<br>".$course_array[0]['division']."<br>" ;
// echo "<br>".$course_array[0]['term']."<br>" ;

$division_code = $course_array[0]['division'] ;
// echo "<br>division code: ".$division_code."<br>" ;
// print_r($course_array);

$term_code = $course_array[0]['term'] ;
// echo $term_code ;
}

// 部局 department
$division_code_master_sql = "SELECT division_name_e
                            FROM division_code_master
                            WHERE division_code = '$division_code' ; " ;
$division_code_master_result = pg_query($division_code_master_sql);
if (!$division_code_master_result) {
    die('クエリーが失敗しました。'.pg_last_error());
}
$division_code_master_array = pg_fetch_all($division_code_master_result);
// echo "<br>division_code_master_result<br>" ;
// print_r($division_code_master_array);

$division = $division_code_master_array[0]['division_name_e'] ;
// $division = str_replace('/', '／' , $division );
// echo "<br>".$division."<br>" ;

str_replace('Graduate School of Infomatics','Graduate School of Informatics',$division);

$category = category_e ($division_code) ;
$tags = category_e ($division_code) ;

// 開講時限　term
$term_code_master_sql = "SELECT name_e
                            FROM term_code_master
                            WHERE term_code = '$term_code' ; " ;
$term_code_master_result = pg_query($term_code_master_sql);
if (!$term_code_master_result) {
    die('クエリーが失敗しました。'.pg_last_error());
}
$term_code_master_array = pg_fetch_all($term_code_master_result);
// echo "<br>term_code_master_result<br>" ;
// print_r($term_code_master_array);
$term = $courselist_rows['year']."\t".$term_code_master_array[0]['name_e'] ;

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

$featuredimage = "/img/common/default_thumbnail.png";

if($division_code  == '1E0') $featuredimage = "/img/department/環境学研究科.png" ;
if($division_code  == '1C0') $featuredimage = "/img/department/多元数理科学研究科.png" ;
if($division_code  == '110') $featuredimage = "/img/department/文学部／人文学研究科.png" ;
if($division_code  == '110') $featuredimage = "/img/department/文学部.png" ;
if($division_code  == '1E1') $featuredimage = "/img/department/創薬科学研究科.png" ;
if($division_code  == '180') $featuredimage = "/img/department/工学部／工学研究科.png" ;
if($division_code  == '151') $featuredimage = "/img/department/情報学部／情報学研究科.png" ;
if($division_code  == '100') $featuredimage = "/img/department/教養教育院.png" ;
if($division_code  == '1B0') $featuredimage = "/img/department/国際開発研究科.png" ;
if($division_code  == '130') $featuredimage = "/img/department/法学部／法学研究科.png" ;
if($division_code  == '140') $featuredimage = "/img/department/経済学部／経済学研究科.png" ;
if($division_code  == '110') $featuredimage = "/img/department/文学研究科.png" ;
if($division_code  == '1A0') $featuredimage = "/img/department/情報科学研究科.png" ;
if($division_code  == '160') $featuredimage = "/img/department/理学部／理学研究科.png" ;
if($division_code  == '190') $featuredimage = "/img/department/農学部／生命農学研究科.png" ;
if($division_code  == '400') $featuredimage = "/img/department/高等研究院.png" ;
if($division_code  == '1F0') $featuredimage = "/img/department/国際言語センター.png" ;
if($division_code  == '170') $featuredimage = "/img/department/医学部／医学系研究科.png" ;
if($division_code  == '120') $featuredimage = "/img/department/教育学部／教育発達科学研究科.png" ;
if($division_code  == '150') $featuredimage = "/img/department/情報文化学部.png" ;
if($division_code  == '1D0') $featuredimage = "/img/department/国際言語文化研究科.png" ;
if($division_code  == '640') $featuredimage = "/img/department/国際教育交流センター.png" ;

if (!$attachments_array){
    // echo "データがありません！" ;
    // $attachments = "" ;
    $attaches = "";
    $attaches .= "  - name: \"NUOCW logo\" \n" ;
    $attaches .= "    path: /img/common/default_thumbnail.png\n" ;
    // $featuredimage = "";

}else{
    // echo "<br>" ;
    // print_r($attachments_array);
    // echo "<br>" ;
    // $attachments = call_user_func_array('array_merge', $attachments_array);
    // print_r($attachments);
    // $ii = 0 ;
    // $featuredimage = "/img/common/default_thumbnail.png";
    // $featuredimage = "";
    foreach ($attachments_array as $attachment){
        if(strpos($attachment['description'],'看板画像') !== false){
        // if ($attachment['description'] == '看板画像'){
            // echo $attachment['name']."    " ;
            // echo $attachment['description']."<br>" ;
            // echo "<br>".$featuredimage ;

            $featuredimage = sprintf('%03d', $course_id)."-".space_trim( $attachment['name'] ) ;
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
                if ( strcasecmp(basename($file_directory_result_name), space_trim( $attachment['name'] )) == 0 ) {
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

// echo "<br> featuredimage ".$featuredimage."<br>" ;

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

// echo "<br>class_is_for_sql: <br>";
// print($class_is_for_sql) ;

$class_is_for_result = pg_query($class_is_for_sql);
if (!$class_is_for_result) {
    die('クエリーが失敗しました。'.pg_last_error());
}
$class_is_for_array = pg_fetch_all($class_is_for_result);

// print($class_is_for_sql_array) ;

if (!($class_is_for_array[0]['contents'])){
    // echo "<br>データがありません！" ;
    $class_is_for = "" ;
}else{
    $class_is_for = space_trim(strip_tags($class_is_for_array[0]['contents'])) ;
    // echo "<br>class_is_for_array: ";
    // print_r($class_is_for_array);
}

// echo "<br>class_is_for: ".$class_is_for;

if(strpos($class_is_for,'単位') !== false){
    //$class_is_for のなかに'単位'が含まれている場合

    if(strpos($class_is_for,'.5') !== false){
        //$class_is_for のなかに'.5'が含まれている場合
        $class_is_for_offset = '4';
    }else{
        $class_is_for_offset = '2';
    }

    $end = mb_strpos($class_is_for,'単位') - $class_is_for_offset ;
    $target = mb_substr($class_is_for, 0, $end);
    $target = space_trim(preg_replace('/(\n|\r|\r\n)+/us',"", $target ));

    // # 単位数
    // credit: "2単位"

    $start = mb_strpos($class_is_for,'単位') - $class_is_for_offset ;
    $credit = mb_substr($class_is_for, $start, $class_is_for_offset + 0);
    $credit = space_trim(preg_replace('/(\n|\r|\r\n)+/us',"", $credit ));

    $start = mb_strpos($class_is_for,'単位')+3;
    $classes = mb_substr($class_is_for, $start);
    $classes = space_trim(preg_replace('/(\n|\r|\r\n)+/us',"", $classes ));


}else{
    $target = $class_is_for ;
    $credit = '' ;
    $classes = '' ;
}

$class_is_for = space_trim( preg_replace('/(\n|\r|\r\n)+/us',"、", $class_is_for ) );
$class_is_for = space_trim( preg_replace('/(?:\n|\r|\r\n)/', "、", $class_is_for ) );

if($course_id == '5'){
    $target = "理学部数理学科3年生 多元数理科学研究科" ;
    $credit = "学部生: 3単位 大学院生: 2単位" ;
    $classes = "週1回 全15回" ;
}
if($course_id == '259'){
    $target = "情報文化学部自然情報学科・社会システム情報学科" ;
    $credit = "必修2単位" ;
    $classes = "週1回 全15回" ;
}
if( $course_id =='297' || $course_id =='352' ){
    $target = "国際言語文化研究科および文学研究科の大学院生" ;
    $credit = "a,bそれぞれ2単位" ;
    $classes = "週1回 全15回" ;
}
if($course_id == '360'){
    $target = "全学部" ;
    $credit = "前期・後期それぞれ2単位" ;
    $classes = "週1回 全15回" ;
}
if($course_id == '406'){
    $target = "国際言語文化研究科" ;
    $credit = "前期・後期それぞれ2単位" ;
    $classes = "週1回 全15回" ;
}
if($course_id == '680'){
    $target = "情報学部" ;
    $credit = "必修1単位" ;
    $classes = "週1回 全7回" ;
}
if($course_id == '703'){
    $target = "基盤創薬学専攻（1年次通年集中博士課程前期課程）" ;
    $credit = "必修・「演習」１単位「実習」２単位" ;
    $classes = "" ;
}
if($course_id == '69'){
    $target = "医学部医学科" ;
    $credit = "" ;
    $classes = "全12回" ;
}
if($course_id == '186'){
    $class_is_for = "文系学部　情報文化学部（自然）、理学部　農学部　工学部（I・II・III系）、文系学部　情報文化学部（自然）、理学部　農学部　工学部（II・III・IV系）";
    $target = "文系学部　情報文化学部（自然）、理学部　農学部　工学部（I・II・III系）、文系学部　情報文化学部（自然）、理学部　農学部　工学部（II・III・IV系）" ;
    $credit = "" ;
    $classes = "" ;
}if($course_id == '193'){
    $target = "教育学部2年生、教育学部以外の学生" ;
    $credit = "" ;
    $classes = "" ;
}if($course_id == '596'){
    $target = "名古屋大学の留学生" ;
    $credit = "" ;
    $classes = "週1回 全15回" ;
}if($course_id == '631'){
    $target = "情・工・理・農学部" ;
    $credit = "" ;
    $classes = "週1回 全15回" ;
}if($course_id == '122'){
    $class_is_for = "文学部２年生以上、文学研究科博士前期課程、他学部３年生以上、2単位、週1回全15回";
    $target = "文学部２年生以上、文学研究科博士前期課程、他学部３年生以上" ;
    $credit = "週1回 全15回" ;
    $classes = "2単位" ;
}
// if($course_id == ''){
//     $target = "" ;
//     $credit = "" ;
//     $classes = "" ;
// }

// echo "<br><br>元　　 : ".$class_is_for ;
// echo "<br>対象者 : ".$target ;
// echo "<br>単位数 : ".$credit ;
// echo "<br>授業回数 : ".$classes."<br>" ;

// 2281               | 対象者（英語）
$class_is_for_sql_e = "SELECT contents.contents
                    FROM pages, page_contents, contents
                    WHERE pages.course_id = $course_id
                    AND pages.page_id = page_contents.page_id
                    AND contents.pid = page_contents.contents_id
                    AND contents.type = '2281'
                    ORDER BY contents.id DESC LIMIT 1; ";

// echo "<br>class_is_for_sql_e: <br>";
// print($class_is_for_sql_e) ;

$class_is_for_result_e = pg_query($class_is_for_sql_e);
if (!$class_is_for_result_e) {
    die('クエリーが失敗しました。'.pg_last_error());
}
$class_is_for_array_e = pg_fetch_all($class_is_for_result_e);

if (!($class_is_for_array_e[0]['contents'])){
    // echo "<br>データがありません！" ;
    $class_is_for_e = "" ;
}else{
    $class_is_for_e = space_trim(strip_tags($class_is_for_array_e[0]['contents'])) ;
    // echo "<br>class_is_for_array_e: ";
    // print_r($class_is_for_array_e);
}

// echo "<br>class_is_for_e: ".$class_is_for_e;

if($class_is_for_e){
    $target = $class_is_for_e;
}

// echo "<br>class_is_for: ".$class_is_for;
// echo "<br>target: ".$target;


// 2282               | Classes
$lectures_sql_e = "SELECT contents.contents
                    FROM pages, page_contents, contents
                    WHERE pages.course_id = $course_id
                    AND pages.page_id = page_contents.page_id
                    AND contents.pid = page_contents.contents_id
                    AND contents.type = '2282'
                    ORDER BY contents.id DESC LIMIT 1; ";

// echo "<br>lectures_sql_e: <br>";
// print($lectures_sql_e) ;

$lectures_result_e = pg_query($lectures_sql_e);
if (!$lectures_result_e) {
    die('クエリーが失敗しました。'.pg_last_error());
}
$lectures_result_array_e = pg_fetch_all($lectures_result_e);

if (!($lectures_result_array_e[0]['contents'])){
    // echo "<br>データがありません！" ;
    $lectures_result_array_e = "" ;
}else{
    $lectures_result_array_e = space_trim(strip_tags($lectures_result_array_e[0]['contents'])) ;
    // echo "<br>lectures_result_array_e: ";
    // print_r($lectures_result_array_e);
}

// echo "<br>lectures_result_array_e: ".$lectures_result_array_e;

if($lectures_result_array_e){
    $classes = $lectures_result_array_e;
}

// 2283               | Credits
$credit_sql_e = "SELECT contents.contents
                    FROM pages, page_contents, contents
                    WHERE pages.course_id = $course_id
                    AND pages.page_id = page_contents.page_id
                    AND contents.pid = page_contents.contents_id
                    AND contents.type = '2283'
                    ORDER BY contents.id DESC LIMIT 1; ";

// echo "<br>credit_sql_e: <br>";
// print($credit_sql_e) ;

$credit_result_e = pg_query($credit_sql_e);
if (!$credit_result_e) {
    die('クエリーが失敗しました。'.pg_last_error());
}
$credit_result_array_e = pg_fetch_all($credit_result_e);

if (!($credit_result_array_e[0]['contents'])){
    // echo "<br>データがありません！" ;
    $credit_result_array_e = "" ;
}else{
    $credit_result_array_e = space_trim(strip_tags($credit_result_array_e[0]['contents'])) ;
    // echo "<br>credit_result_array_e: ";
    // print_r($credit_result_array_e);
}

// echo "<br>credit_result_array_e: ".$credit_result_array_e;

if($credit_result_array_e){
    $credit = $credit_result_array_e;
}

// echo "<br><br>元　　 : ".$class_is_for ;
// echo "<br>対象者 : ".$target ;
// echo "<br>単位数 : ".$credit ;
// echo "<br>授業回数 : ".$classes."<br>" ;

// 51             | 授業ホーム   | Course Home           | index            |        510
$page_id = check_page_status ($course_id, $page_type = '51') ;
// echo "<br>page_type = '51' page_id (print_r): ".print_r($page_id) ;
// $page_id = $page_id;
// echo "<br>page_type = '51' page_id : ".$page_id ;

if(!empty($page_id)){
    // $description_sql = "SELECT contents.type, contents.contents FROM page_contents, contents
    //             WHERE contents.pid = page_contents.contents_id
    //             AND contents.type = '2101'
    //             AND page_contents.page_id = $page_id
    //             ORDER BY contents.id DESC LIMIT 1 ; " ;

    // // echo "<br>description_sql: ".$description_sql ;

    // $description_result = pg_query($description_sql);
    // if (!$description_result) {
    //     die('クエリーが失敗しました。'.pg_last_error());
    // }
    // $description_result_array = pg_fetch_all($description_result);

    $description = get_contents($page_id, $contents_type = '2101');
    // echo "<br>description : ".print_r($description) ;

    // $course_home_sql = "SELECT contents.contents FROM page_contents, contents
    //             WHERE contents.pid = page_contents.contents_id
    //             AND contents.type = '2101'
    //             AND page_contents.page_id = $page_id
    //             ORDER BY contents.id DESC LIMIT 1 ; " ;

    // $course_home_result = pg_query($course_home_sql);
    // if (!$course_home_result) {
    //     die('クエリーが失敗しました。'.pg_last_error());
    // }
    // $course_home_result_array = pg_fetch_all($course_home_result);

    $course_home = get_contents($page_id, $contents_type = '2101');
    // echo "<br>course_home : ".print_r($course_home_result_array) ;

}else{
    $description = '';
    $course_home = '';
}

$description = str_ireplace('###','',$description);
$description = str_ireplace('Course Overview','',$description);
$description = preg_replace('/(?<=\{ocwimg file=\").+?(?=\"\})/', '', $description);
$description = preg_replace('/{ocwimg file=""}/','',$description) ;

    // echo "<br>description : ".$description ;

$description = preg_replace('/(?<=\{ocwimg file =\").+?(?=\"\})/', '', $description);
$description = preg_replace('/{ocwimg file =""}/','',$description) ;

    // echo "<br>description : ".$description ;

$description = preg_replace('/(?<=\{overview header=\").+?(?=\"\})/', '', $description);
$description = preg_replace('/{overview header=""}/','',$description) ;

    // echo "<br>description : ".$description ;

$description = preg_replace('/(?<=\{overview lang=\").+?(?=\"\})/', '', $description);
$description = preg_replace('/{overview lang=""}/','',$description) ;

$description = str_ireplace('{overview header="Course Outline" lang="en"}', '', $description);
$description = str_ireplace('{overview header="Course Aims" lang="en"}', '', $description);
$description = preg_replace('/\#\#\# Course Aims\" lang=\"en/', '\n', $description);
$description = str_ireplace('### Course Aims" lang="en', '', $description);
$description = str_ireplace('{overview lang="en" header="Objectives and aims of the course"}', '', $description);
$description = str_ireplace('{overview lang="en" header="Course Objects"}', '', $description);
$description = str_ireplace('### Course Home" lang="en','', $description);
$description = str_ireplace('{overview lang="en" header= "Course Aims"}','',$description);
$description = str_ireplace('{overview lang="en" header="Course Overview "}','',$description);
$description = str_ireplace('{overview lang="en" header="Course Overview "} ', '',$description);
$description = str_ireplace('{overview lang="en" header= "Course Aims"}','',$description);
$description = str_ireplace('{overview lang="en" header= "Course Aims"}','',$description);
$description = str_ireplace('{overview lang="en" header= "Course Aims"}','',$description);
$description = str_ireplace('{overview lang=“en” header=“Course Objectives”}','',$description);


// echo "<br>description : ".$description ;

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
    $course_home = "### Course Overview\n\n".$description ;
}
if( empty($course_home) && !empty($description) ){
    $course_home = "### Course Overview\n\n".$description ;
}


// 52             | シラバス     | Syllabus              | syllabus         |        520
$page_id = check_page_status ($course_id, $page_type = '52') ;
// echo "<br>page_type = '52' page_id: ".$page_id ;

if(!empty($page_id)){

    // $syllabus_sql = "SELECT contents.contents FROM page_contents, contents
    //                 WHERE contents.pid = page_contents.contents_id
    //                 AND contents.type = '2101'
    //                 AND page_contents.page_id = $page_id
    //                 ORDER BY contents.id DESC LIMIT 1 ; " ;

    // $syllabus_result = pg_query($syllabus_sql);
    // if (!$syllabus_result) {
    //     die('クエリーが失敗しました。'.pg_last_error());
    // }
    // $syllabus_result_array = pg_fetch_all($syllabus_result);

    // echo "<br>syllabus_result_array : ".print_r($syllabus_result_array) ;

    $syllabus = get_contents($page_id, $contents_type = '2101');

}else{
    $syllabus = '' ;
}

// 53             | スケジュール | Calendar              | calendar         |        530
$page_id = check_page_status ($course_id, $page_type = '53') ;
// echo "<br>page_type = '53' page_id: ".$page_id ;

if(!empty($page_id)){

    // $calendar_sql = "SELECT contents.contents FROM page_contents, contents
    //                 WHERE contents.pid = page_contents.contents_id
    //                 AND contents.type = '2101'
    //                 AND page_contents.page_id = $page_id
    //                 ORDER BY contents.id DESC LIMIT 1 ; " ;

    // $calendar_result = pg_query($calendar_sql);
    // if (!$calendar_result) {
    //     die('クエリーが失敗しました。'.pg_last_error());
    // }
    // $calendar_result_array = pg_fetch_all($calendar_result);

    // echo "<br>calendar_result_array : ".print_r($calendar_result_array) ;

    $calendar = get_contents_without_Markdownify ($page_id, $contents_type = '2101'); ;

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
// echo "<br>page_type = '54' page_id: ".$page_id ;

if(!empty($page_id)){

    // $lecture_notes_sql = "SELECT contents.contents FROM page_contents, contents
    //                 WHERE contents.pid = page_contents.contents_id
    //                 AND contents.type = '2101'
    //                 AND page_contents.page_id = $page_id
    //                 ORDER BY contents.id DESC LIMIT 1 ; " ;

    // $lecture_notes_result = pg_query($lecture_notes_sql);
    // if (!$lecture_notes_result) {
    //     die('クエリーが失敗しました。'.pg_last_error());
    // }
    // $lecture_notes_result_array = pg_fetch_all($lecture_notes_result);

    // echo "<br>lecture_notes_result_array : ".print_r($lecture_notes_result_array) ;

    $lecture_notes = get_contents ($page_id, $contents_type = '2101'); ;

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
// echo "<br>page_type = '55' page_id: ".$page_id ;

if(!empty($page_id)){

    $assignment = get_contents_without_Markdownify($page_id, $contents_type = '2101');

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
// echo "<br>page_type = '56' page_id: ".$page_id ;

if(!empty($page_id)){

    $evaluation = get_contents($page_id, $contents_type = '2101');

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
// echo "<br>page_type = '57' page_id: ".$page_id ;

if(!empty($page_id)){

    $achievement = get_contents_without_Markdownify($page_id, $contents_type = '2101');

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
// echo "<br>page_type = '58' page_id: ".$page_id ;

if(!empty($page_id)){

    $related_resources = get_contents_without_Markdownify($page_id, $contents_type = '2101');

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
// echo "<br>page_type = '59' page_id: ".$page_id ;

if(!empty($page_id)){

    $teaching_tips = get_contents($page_id, $contents_type = '2101');

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
// echo "<br>movie: "; print_r($movie);

if(!empty($movie)){
    $movie = $movie[0] ;
}else{
    $movie = '' ;
}


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

if ($course_id == '441'){
    $course_name = "Academic Japanese (Reading-and-Writing)V KANJI 2000" ;
    echo "<br>".$course_name."<br>";
}
// echo "<br><br>";
$key_phrase = space_trim($course_name)." ".$courselist_rows['department_name']." ";
$key_phrase .= $course_home." ".$teaching_tips." ".$syllabus ;
$key_phrase = preg_replace('/(?:\n|\r|\r\n)/', '', $key_phrase ) ;
// $key_phrase = preg_replace('/(?:\n|\r|\r\n)/', '', space_trim(strip_tags(mb_substr($key_phrase,0,500))) ) ;

// $key_phrase = preg_replace('/最終講義/', '' , $key_phrase) ;
// $key_phrase = preg_replace('/\#.*/um', '' , $key_phrase) ;
$key_phrase = str_replace("最終講義-", " ", $key_phrase);
$key_phrase = str_replace("最終講義ー", " ", $key_phrase);
$key_phrase = str_ireplace("####", " ", $key_phrase);
$key_phrase = str_ireplace("###", " ", $key_phrase);

$key_phrase = strip_tags($key_phrase) ;

// extractCommonWords($key_phrase);

// echo "<br><br>key phrase : ".$key_phrase."<br><br>";
// echo "<br><br>key phrase : ";
$text = "This is some text. This is some text. Vending Machines are great.";
$words = extractCommonWords($key_phrase);
echo implode(', ', array_keys($words));
// echo "<br>";
// print_r(array_keys($words));

$tag_array =[];
$ii = 0;
foreach (array_keys($words) as $name) {
    // echo "{$name}<br />";
    $tag_array[$ii] = $name ;
    $ii++;
}
// print_r($tag_array);
// echo $tag_array[0];

// echo "<br><br>key phrase :<br>" ;
// print implode(', ', extractKeyWords("This is some text. This is some text. Vending Machines are great."));
// prints "this,text,some,great,are,vending,machines"
// echo "<br><br>key phrase :<br>" ;
// print implode(', ', extractKeyWords($key_phrase));


// Tags (key_phrase を Yahoo API から取得)
// $key_phrase_title = space_trim($course_name)." ".$courselist_rows['department_name'] ;

// if(preg_match( "/名大トピックス/", $key_phrase ) ){
//     //名大トピックスが含まれている
//     $key_phrase = $key_phrase_title ;
//     }else{
//     //名大トピックスが含まれていない
//     $key_phrase = preg_replace('/(?:\n|\r|\r\n)/', '', space_trim(strip_tags(mb_substr($key_phrase,0,800))) ) ;
//     // $key_phrase = preg_replace('/(?:\n|\r|\r\n)/', '', space_trim(strip_tags($farewell_lecture_home_del_firstline)) ) ;
//     }

// $key_phrase .= preg_replace('/(?:\n|\r|\r\n)/', '', space_trim(strip_tags(mb_substr($description,0,500))) ) ;
// $key_phrase .= preg_replace('/(?:\n|\r|\r\n)/', '', space_trim(strip_tags($description)) ) ;

// Tagsに相応しくない文字を削除
// $key_phrase = preg_replace('/最終講義/', '' , $key_phrase) ;
// // $key_phrase = preg_replace('/\#.*/um', '' , $key_phrase) ;
// $key_phrase_title = str_replace("最終講義-", " ", $key_phrase_title);
// $key_phrase_title = str_replace("最終講義ー", " ", $key_phrase_title);
// $key_phrase_title = str_replace("最終講義―", " ", $key_phrase_title);
// $key_phrase_title = str_replace("II", "", $key_phrase_title);
// $key_phrase_title = str_replace("I", "", $key_phrase_title);

// $key_phrase = str_ireplace("####", " ", $key_phrase);
// $key_phrase = str_ireplace("###", " ", $key_phrase);

// $key_phrase = preg_replace('/\&oslash\;/', 'o', $key_phrase);
// $key_phrase = preg_replace('/\&emsp\;/', '  ' , $key_phrase);
// $key_phrase = preg_replace('/\&/', ' ' , $key_phrase);
// $key_phrase = preg_replace('/\*/', ' ' , $key_phrase);
// $key_phrase = preg_replace('/\"/', ' ' , $key_phrase);
// $key_phrase = str_replace("{", "", $key_phrase);
// $key_phrase = str_replace("}", "", $key_phrase);
// $key_phrase = str_replace("=", "", $key_phrase);
// $key_phrase = str_replace(",", "", $key_phrase);
// $key_phrase = str_replace(".", "", $key_phrase);
// $key_phrase = remove_accent($key_phrase);
// $key_phrase = strip_tags($key_phrase);

// // Strip HTML Tags
// $key_phrase = strip_tags($key_phrase);
// // Clean up things like &amp;
// $key_phrase = html_entity_decode($key_phrase);
// // Strip out any url-encoded stuff
// $key_phrase = urldecode($key_phrase);
// // Replace non-AlNum characters with space
// // $key_phrase = preg_replace('/[^A-Za-z0-9]/', ' ', $key_phrase);
// $key_phrase = preg_replace('/[A-Za-z0-9_]/', '',  $key_phrase) ;
// // Replace Multiple spaces with single space
// $key_phrase = preg_replace('/ +/', ' ', $key_phrase);
// // Trim the string of leading/trailing space
// $key_phrase = space_trim($key_phrase);

// Yahoo API が無効になっている？
// そもそも日本語なので，Keyphrase は取り急ぎ削除
// https://developer.yahoo.co.jp/webapi/jlp/keyphrase/v1/extract.html

// if(preg_match( "/[ぁ-ん]+|[ァ-ヴー]+/u", $key_phrase) ){
//     //日本語文字列が含まれている（キーフレーズは日本語のみに適用）
//     $tags = show_keyphrase($appid, $key_phrase_title." ".$key_phrase );
//     }else{
//     //日本語文字列が含まれていない
//     $tags = show_keyphrase($appid, $key_phrase_title );
//     }

// echo "<br><br> key_phrase = ".$key_phrase_title." ".$key_phrase ;
// echo "<br> tags = ".$tags ;




// 授業のファイル名

$file_name = remove_html_special_chars($course_name)."-".$courselist_rows['year'] ;

// $file_name = "./src/pages/courses/".$course_id."-".$course_name."-".$division."-".$courselist_rows['year'].".md" ;
// $file_name = "./src/pages/courses/".$course_id."-".$course_name."-".$division.".md" ;

if($course_id == '22') $file_name = 'University-Wide-Liberal-Arts--Exploration-of-Japan：From-the-Outside-Looking-In-2014' ;
if($course_id == '44') $file_name = 'Methods-of-Teaching-II--Lesson-Analysis-and-the-Scientification-of-Education-2010' ;
if($course_id == '53') $file_name = 'The-Structure-of-Representation-in-the-Post-Roman-Era--Historical-Reflections-on-Communication-Acts-2006' ;
if($course_id == '57') $file_name = 'University-Wide-Liberal-Arts-Tracing-the-History-of-Mei-Dai-2006' ;
if($course_id == '64') $file_name = 'Behavioristics-Lecture-II--Psychology-of-Industries-and-Organizations-2010' ;
if($course_id == '156') $file_name = 'Methods-of-Teaching-I-Outline-of-Teaching-Methods-2010' ;
if($course_id == '249') $file_name = 'Gender-and-Literature-b-Japanese-and-Chinese-feminism-History-of-gender-studies-and-literary-criticism-2011' ;
if($course_id == '270') $file_name = 'Archaeological-Research-on-the-History-of-Ancient-Handicraft-Industries--about-the-roof-tiles-of-Kokubun-ji-(provincial-temples)--2009' ;
if($course_id == '304') $file_name = 'Second-Language-Acquisition-A-Understanding-Second-Language-Acquisition-Studies-2011' ;
if($course_id == '435') $file_name = 'C-algebraic-methods-in-spectral-theory-2014' ;
if($course_id == '472') $file_name = 'World-and-Image-in-Japanese-Narrative-I-IV-2013' ;

$file_name = "courses-en/".sprintf('%03d', $course_id)."-".$file_name ;

echo "<br>".$course_id." ".$file_name."\t: " ;

$templateKey = "courses-en" ;

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

# 対象者、単位数、授業回数（修正用の元データ）
class_is_for: \"".$class_is_for."\"

# 対象者
target: \"".$target."\"

# 授業回数
classes: \"".$classes."\"

# 単位数
credit: \"".$credit."\"

# pdfなどの追加資料
attachments:
".$attaches."
# 関連するタグ
# （頻度の高い単語を出力）
tags:
    - ".$tag_array[0]."
    - ".$tag_array[1]."
    - ".$tag_array[2]."
    - ".$tag_array[3]."
    - ".$tag_array[4]."
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

// print_r($courselist_text);
$main_text = preg_replace('/\#\#\# Course Aims\" lang=\"en/', '', $main_text);

// テンポラリーファイルに書き込み
$fp_tmp = fopen('tmp.md', 'w');
fwrite($fp_tmp,$main_text);
fclose($fp_tmp);

// echo "<br>ID: ".$course_id."\t".$file_name ;

// 以下は html へ吐き出す内容の表示
// echo htmlspecialchars("<br>".$course_id."-".$course_name."\t&emsp;<a href=\"http://ocw.nagoya-u.jp/index.php?lang=en&mode=c&id=".$course_id."&amp;page_type=index \" target=\"_blank\" rel=\"noopener\"> 現OCW </a>" );
// echo htmlspecialchars("\t&emsp;<a href=\"http://ocw.ilas.nagoya-u.ac.jp/".$file_name."\"target=\"_blank\" rel=\"noopener\"> 新OCW </a>\n") ;

// tmp.html へも出力
$kk = $i+1;
$check_list .="<tr><td width=\"10\">".$kk."</td><td width=\"100\"><a href=\"http://ocw.nagoya-u.jp/index.php?lang=en&mode=c&id=".$course_id."&amp;page_type=index \" target=\"_blank\" rel=\"noopener\"> 現OCW </a></td>" ;
$check_list .="<td width=\"100\"><a href=\"http://ocw.ilas.nagoya-u.ac.jp/".$file_name."\"target=\"_blank\" rel=\"noopener\"> 新OCW </a></td>" ;
$check_list .="<td>".sprintf('%03d', $course_id)."-".$file_name."</td></tr>\n" ;

// echo "<br>ID: ".$course_id."\t".$file_name."\t を出力しました。" ;
// echo "<br>".$file_name."\t を出力しました。" ;

// 一行ずつ読み込んで処理する

$fp_tmp = fopen('tmp.md', 'r');
$fp_tmp2 = fopen('tmp2.md', 'w');
// $fp = fopen($file_name, "w");

$ocwimg_file = '/(?<=\{ocwimg file=\").+?(?=\")/';
$ocwimg_alt  = '/(?<=alt=\").+?(?=\")/';
$ocwimg_link = '/(?<=ocwlink=\").+?(?=\")/';
$ocwimg_all  = '/(?<=\{ocwimg file=\").+?(?=\"\})/';


$ocwlink_file = '/(?<=\{ocwlink file=\").+?(?=\")/';
$ocwlink_desc = '/(?<=desc=\").+?(?=\")/';
// $ocwlink_all = '/(?<=\{ocwlink file=\").+?(?=\")/';
$ocwlink_all  = '/(?<=\{ocwlink file=\").+?(?=\"\})/';

$ocwpagelink_file = '/(?<=\{ocwpagelink type=\").+?(?=\")/';
$ocwpagelink_desc = '/(?<=desc=\").+?(?=\")/';
$ocwpagelink_all  = '/(?<=\{ocwpagelink type=\").+?(?=\"\})/';

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
        // $line = space_trim($line) ;

        // $test = "
        // 第2回
        // : {ocwlink file=\"ファイル名2\" desc=\"タイトル2\"}
        // : {ocwimg file=\"temporary.img\" alt=\"画像の説明\" ocwlink=\"lecture.pdf\"}
        // : {ocwimg file=\"temporary2.img\" alt=\"画像の説明2\" }";

        // $mystring = $test ;
        // $findme   = 'ocwimg file=';
        // $pos = strpos($mystring, $findme);

        // // !== 演算子も使用可能です。ここで != を使っても期待通りに動作しません。
        // // なぜなら 'a' が 0 番目の文字だからです。(0 != false) を評価すると
        // // false になってしまいます。
        // if ($pos !== false) {
        //      echo "文字列 '$findme' が文字列 '$mystring' の中で見つかりました";
        //          echo " 見つかった位置は $pos です";
        // } else {
        //      echo "文字列 '$findme' は、文字列 '$mystring' の中で見つかりませんでした";
        // }

        // echo "<br>";


        $line = str_ireplace('{overview header="Course Outline" lang="en"}', '', $line);
        $line = str_ireplace('{overview header="Course Aims" lang="en"}', '', $line);
        $line = preg_replace('/\#\#\# Course Aims\" lang=\"en/', '', $line);
        $line = preg_replace('/\#\#\# Course Contents\” lang=\“en/', '', $line);

        $line = str_ireplace('### Course Aims" lang="en', '', $line);
        $line = str_ireplace('{overview lang="en" header="Objectives and aims of the course"}', '', $line);
        $line = str_ireplace('{overview lang="en" header="Course Objects"}', '', $line);
        $line = str_ireplace('### Course Home" lang="en','', $line);
        $line = str_ireplace('{overview lang="en" header= "Course Aims"}','',$line);
        $line = str_ireplace('{overview lang="en" header="Course Overview "}','',$line);
        $line = str_ireplace('{overview lang="en" header="Course Overview "} ', '',$line);
        $line = str_ireplace('{overview lang="en" header= "Course Aims"}','',$line);
        $line = str_ireplace('{overview lang="en" header= "Course Aims"}','',$line);
        $line = str_ireplace('{overview lang="en" header="Course Contents"}','',$line);
        $line = str_ireplace('{overview lang="en" header="Course Objectives"}','',$line);

        // print_r($line);
        // echo "<br>";

    // ocwimg with ocwlink
        if(  (strpos($line, 'ocwimg file=') !== FALSE)
          && (strpos($line, 'alt=')         !== FALSE )
          && (strpos($line, 'ocwlink=')     !== FALSE ) ){

                preg_match_all($ocwimg_link, $line, $ocwimg_link_match) ;
                //print_r($file_match);
                // echo "<br> test  : ".htmlspecialchars_decode($line, ENT_NOQUOTES);
                // echo "<br> ocwimg_link_match: " ; var_dump($ocwimg_link_match) ;
                $ocwimg_link_file = "(https://ocw.nagoya-u.jp/files/".$course_id."/".$ocwimg_link_match[0][0].") " ;

                preg_match_all($ocwimg_file, $line, $ocwimg_file_match);
                // echo "<br>   ocwimg_file_match: " ; var_dump($ocwimg_file_match) ;
                $ocwimg_file_embed = "(https://ocw.nagoya-u.jp/files/".$course_id."/".$ocwimg_file_match[0][0].") " ;

                preg_match_all($ocwimg_alt, $line, $ocwimg_alt_match);
                // echo "<br>   ocwimg_alt_match: " ; var_dump($ocwimg_alt_match) ;
                $ocwimg_file_link = "![".$ocwimg_alt_match[0][0]."]".$ocwimg_file_embed ;

                $ocwimg_all_link = "[ ".$ocwimg_file_link." ]".$ocwimg_link_file;
                // $test2 = preg_replace('/\{ocwimg file=\"/', $ocwimg_file_link, $test ) ;
                // echo "<br> ocwimg_all_link : ".htmlspecialchars_decode($ocwimg_all_link, ENT_NOQUOTES);

                preg_match_all($ocwimg_all, $line, $ocwimg_all_match);
                // echo "<br>   ocwimg_all_match: " ; var_dump($ocwimg_all_match) ;

                $ocwimg_match = $ocwimg_all_match[0][0]."\"}" ;
                $test2 = str_replace( $ocwimg_match,"", $line ) ;
                // echo "<br> test2 : ".htmlspecialchars_decode($test2, ENT_NOQUOTES);

                // $test3 = str_replace( $ocwimg_match,"", $test2 ) ;
                // echo "<br> test3 : ".htmlspecialchars_decode($test3, ENT_NOQUOTES);
                $line = preg_replace('/\{ocwimg file=\"/', $ocwimg_all_link, $test2 ) ;
                //print_r($desc_match);
                // echo "<br> ocwimg_all_link: " ; var_dump($ocwimg_link_match) ;
                // echo "<br> ocwimg with link ".htmlspecialchars_decode($line, ENT_NOQUOTES);


             }
    // ocwimg
        if(  (strpos($line, 'ocwimg file=') !== FALSE)
          && (strpos($line, 'alt=')         !== FALSE ) ){
                preg_match_all($ocwimg_file, $line, $ocwimg_file_match) ;
                // echo "<br> test  : ".htmlspecialchars_decode($test, ENT_NOQUOTES);
                // echo "<br> ocwimg_file_match: " ; var_dump($ocwimg_file_match) ;
                $ocwimg_file_link = "(https://ocw.nagoya-u.jp/files/".$course_id."/".$ocwimg_file_match[0][0].") " ;
                preg_match_all($ocwimg_alt, $line, $ocwimg_alt_match);
                if(!empty($ocwimg_alt_match[0][0])){
                    // echo "<br>   ocwimg_alt_match: " ; var_dump($ocwimg_alt_match) ;
                    $ocwimg_file_link = "![".$ocwimg_alt_match[0][0]."]".$ocwimg_file_link ;
                    // $test2 = preg_replace('/\{ocwimg file=\"/', $ocwimg_file_link, $test ) ;
                    // echo "<br> test2 : ".htmlspecialchars_decode($test2, ENT_NOQUOTES);
                }
                preg_match_all($ocwimg_all, $line, $ocwimg_all_match);
                if (!empty($ocwimg_all_match[0][0])){
                    // echo "<br>   if ocwimg_all_match: " ; var_dump($ocwimg_all_match) ;
                    $ocwimg_match = $ocwimg_all_match[0][0]."\"}" ;
                    $test2 = str_replace( $ocwimg_match,"", $line ) ;
                    // echo "<br> test2 : ".htmlspecialchars_decode($test2, ENT_NOQUOTES);
                    $line = preg_replace('/\{ocwimg file=\"/', $ocwimg_file_link, $test2 ) ;
                    //print_r($desc_match);
                }else{
                    // echo "<br>   ocwimg_all_match: " ; var_dump($ocwimg_all_match) ;
                }
                // echo "<br> ocwimg with alt ".htmlspecialchars_decode($line, ENT_NOQUOTES);


             }

    // ocwimg
    if(  strpos($line, 'ocwimg file=') !== FALSE ){

        preg_match_all($ocwimg_file, $line, $ocwimg_file_match) ;
        // echo "<br> test  : ".htmlspecialchars_decode($test, ENT_NOQUOTES);
        // echo "<br> ocwimg_file_match: " ; var_dump($ocwimg_file_match) ;
        $ocwimg_file_link = "(https://ocw.nagoya-u.jp/files/".$course_id."/".$ocwimg_file_match[0][0].") " ;
      //   preg_match_all($ocwimg_alt, $line, $ocwimg_alt_match);
      //   // echo "<br>   ocwimg_alt_match: " ; var_dump($ocwimg_alt_match) ;
        $ocwimg_file_link = "![&nbsp;]".$ocwimg_file_link ;
      //   // $test2 = preg_replace('/\{ocwimg file=\"/', $ocwimg_file_link, $test ) ;
      //   // echo "<br> test2 : ".htmlspecialchars_decode($test2, ENT_NOQUOTES);
        preg_match_all($ocwimg_all, $line, $ocwimg_all_match);
        if(!empty($ocwimg_all_match[0][0])){
            // echo "<br>   ocwimg_all_match: " ; var_dump($ocwimg_all_match) ;
            $ocwimg_match = $ocwimg_all_match[0][0]."\"}" ;
            $test2 = str_replace( $ocwimg_match,"", $line ) ;
            // echo "<br> test2 : ".htmlspecialchars_decode($test2, ENT_NOQUOTES);
            $line = preg_replace('/\{ocwimg file=\"/', $ocwimg_file_link, $test2 ) ;
            //print_r($desc_match);
        }
      //   echo "<br> ocwimg only ".htmlspecialchars_decode($line, ENT_NOQUOTES);

     }

    // ocwimg
    if(  strpos($line, 'ocwimg file=') !== FALSE ){

          preg_match_all($ocwimg_file, $line, $ocwimg_file_match) ;
          // echo "<br> test  : ".htmlspecialchars_decode($test, ENT_NOQUOTES);
          // echo "<br> ocwimg_file_match: " ; var_dump($ocwimg_file_match) ;
          $ocwimg_file_link = "(https://ocw.nagoya-u.jp/files/".$course_id."/".$ocwimg_file_match[0][0].") " ;
        //   preg_match_all($ocwimg_alt, $line, $ocwimg_alt_match);
        //   // echo "<br>   ocwimg_alt_match: " ; var_dump($ocwimg_alt_match) ;
          $ocwimg_file_link = "![&nbsp;]".$ocwimg_file_link ;
        //   // $test2 = preg_replace('/\{ocwimg file=\"/', $ocwimg_file_link, $test ) ;
        //   // echo "<br> test2 : ".htmlspecialchars_decode($test2, ENT_NOQUOTES);
          preg_match_all($ocwimg_all, $line, $ocwimg_all_match);
          if(!empty($ocwimg_all_match[0][0])){
            // echo "<br>   ocwimg_all_match: " ; var_dump($ocwimg_all_match) ;
            $ocwimg_match = $ocwimg_all_match[0][0]."\"}" ;
            $test2 = str_replace( $ocwimg_match,"", $line ) ;
            // echo "<br> test2 : ".htmlspecialchars_decode($test2, ENT_NOQUOTES);
            $line = preg_replace('/\{ocwimg file=\"/', $ocwimg_file_link, $test2 ) ;
            //print_r($desc_match);
          }
        //   echo "<br> ocwimg only ".htmlspecialchars_decode($line, ENT_NOQUOTES);

       }
        // ocwlink
        if( preg_match_all($ocwlink_file, $line, $ocwlink_file_match) ){

            $ocwlink_file_link = "(https://ocw.nagoya-u.jp/files/".$course_id."/".$ocwlink_file_match[0][0].") " ;
            preg_match_all($ocwlink_desc, $line, $ocwlink_desc_match);
            if(!empty($ocwlink_desc_match[0][0])){
                $ocwlink_file_link = "[".$ocwlink_desc_match[0][0]."]".$ocwlink_file_link ;
            }
            preg_match_all($ocwlink_all, $line, $ocwlink_all_match);
            if(!empty($ocwlink_all_match[0][0])){
                $ocwlink_match = $ocwlink_all_match[0][0]."\"}" ;
                $test2 = str_replace( $ocwlink_match,"", $line ) ;

                $line = preg_replace('/\{ocwlink file=\"/', $ocwlink_file_link, $test2 ) ;
            }
            // echo "<br> ocwlink file ".htmlspecialchars_decode($line, ENT_NOQUOTES);

            }


    // ocwpagelink

        if( preg_match_all($ocwpagelink_file, $line, $ocwpagelink_file_match) ){

            $ocwpagelink_file_link = "(#".$ocwpagelink_file_match[0][0].") " ;
            preg_match_all($ocwpagelink_desc, $line, $ocwpagelink_desc_match);

            $ocwpagelink_file_link = "[".$ocwpagelink_desc_match[0][0]."]".$ocwpagelink_file_link ;
            preg_match_all($ocwpagelink_all, $line, $ocwpagelink_all_match);

            $ocwpagelink_match = $ocwpagelink_all_match[0][0]."\"}" ;
            $test2 = str_replace( $ocwpagelink_match,"", $line ) ;

            $line = preg_replace('/\{ocwpagelink type=\"/', $ocwpagelink_file_link, $test2 ) ;

            // echo "<br> ocwpagelink type ".htmlspecialchars_decode($line, ENT_NOQUOTES);

            }

            // echo "<br>" ;


    // stormvideo は削除
    if(preg_match('/stormvideo_link/',$line)){
        $line = "\n" ;
        // echo "<br> stormvideo_link ".htmlspecialchars_decode($line, ENT_NOQUOTES);
      }

    // 行頭の「:」を「- 」へ変換
    if(preg_match('/^:/',$line)){
        $line = str_replace( ":" , "- " , $line) ;
        // echo "<br>行頭の「:」を「- 」へ変換 ".htmlspecialchars_decode($line, ENT_NOQUOTES);
      }

    // スタジオ動画配信サーバ URL の変更
    $line = preg_replace($studio_media, $studio_url, $line);
    // echo "<br>preg_replace : ".$line."<br>";
    $line = str_ireplace("http://studio.media.nagoya-u.ac.jp/videos/watch.php?v=", "https://nuvideo.media.nagoya-u.ac.jp/embed/", $line );
    // echo "<br> str_replace : ".$line."<br>";

    // echo "<br>line : ".$line."<br>";
    $line = preg_replace($studio_url_old, $studio_url, $line);
    $line = str_ireplace("http://nuvideo.media.nagoya-u.ac.jp/embed/", "https://nuvideo.media.nagoya-u.ac.jp/embed/", $line );
    // echo "<br>line : ".$line."<br>";
    $line = preg_replace($studio_thumbs_url_old, $studio_thumbs_url, $line);
    $line = str_ireplace("http://nuvideo.media.nagoya-u.ac.jp/embed/", "https://nuvideo.media.nagoya-u.ac.jp/embed/", $line );

    // 名大トピックス
    $line = preg_replace( $nu_topics_link , $nu_topics_desc ,$line ) ;
    $line = str_ireplace("http://nuvideo.media.nagoya-u.ac.jp/thumbs/", "https://nuvideo.media.nagoya-u.ac.jp/thumbs/", $line );

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
    // if (preg_match('/<a target=\"_blank\" href=/', $line)){
    //     $line = preg_replace('/<a target=\"_blank\" href=/', '<iframe src=', $line);
    //     echo "<br>line : ".$line."<br>";
    // }
    // vsyllabus_direct_link
    // if (preg_match('/<a target=\"blank\" href=/', $line)){
    //     $line = preg_replace('/<a target=\"blank\" href=/', '<iframe src=', $line);
    //     echo "<br>line : ".$line."<br>";
    // }
    $vsyllabus_direct_link_to = ' width="640" height="360" frameborder="0" allowfullscreen></iframe>' ;
    if (preg_match('/(?<=><img).+?(?=<\/a>)/', $line, $vsyllabus_direct_link_match)){
        $vsyllabus_match = "><img".$vsyllabus_direct_link_match[0]."</a>";
        $line = str_replace($vsyllabus_match,$vsyllabus_direct_link_to,$line) ;
        $line = preg_replace('/<a target=\"_blank\" href=/', '<iframe src=', $line);
        $line = preg_replace('/<a target=\"blank\" href=/', '<iframe src=', $line);

            // echo "<br>line : ".$line."<br>";
    }
    if (preg_match('/(?<=>後輩へのメッセージビデオ).+?(?=<\/a>)/', $line, $vsyllabus_direct_link_match)){
        $vsyllabus_match = ">後輩へのメッセージビデオ".$vsyllabus_direct_link_match[0]."</a>";
        $line = str_replace($vsyllabus_match,$vsyllabus_direct_link_to,$line) ;
        $line = preg_replace('/<a target=\"_blank\" href=/', '<iframe src=', $line);
        $line = preg_replace('/<a target=\"blank\" href=/', '<iframe src=', $line);
            // echo "<br>line : ".$line."<br>";
    }

    if (preg_match('/Internet ExplorerまたはMicrosoft Edgeからの閲覧の場合、動画が乱れることがございます。/', $line)){

        $line = "\n\nInternet ExplorerまたはMicrosoft Edgeからの閲覧の場合、動画が乱れることがございます。\n\n" ;
            // echo "<br>line : ".$line."<br>";
    }

    // ocwimg
    if(    (strpos($line, '<a target=' ) !== FALSE)
        && (    (strpos($line, '}</a>' ) !== FALSE)
             || (strpos($line, '} </a>') !== FALSE) )){

        preg_match_all('/\).+?\}/', $line, $ocwimg_thumb_match) ;
        // echo "<br> before  : ".htmlspecialchars_decode($line, ENT_NOQUOTES);
        if(!empty($ocwimg_thumb_match[0][0])){
            $line = str_replace( $ocwimg_thumb_match[0][0],")", $line ) ;
        }
        // echo "<br> after   : ".htmlspecialchars_decode($line, ENT_NOQUOTES);
     }
    // FlashVideo を削除
    $line = preg_replace('/FlashVideo, /', '', $line);

    // 残っている html たとえば <dd> タグを削除
    // $line = strip_tags ($line) ;

    // なぜだか残っている「{tr}」を改行へ変換
    // $line = str_replace('{tr}', "\n" , $line) ;

    //$line = convert_ocwlink ($line) ;
    // echo "<br>".$line;
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

exec('/bin/cp /Users/yamazato/Sites/nuocw-mdfile-generator/tmp.html /Users/yamazato/Sites/NUOCW-Project/nuocw-release-en/static/tmp.html') ;



// 修正済みのファイルは Revised-MD-Files からコピー
exec('/bin/rm　/Users/yamazato/Sites/nuocw-mdfile-generator/src/pages/courses-en/022-University-Wide-Liberal-Arts-*.md');
exec('/bin/rm /Users/yamazato/Sites/nuocw-mdfile-generator/src/pages/courses-en/021-Astrophysics-*.md ');
exec('/bin/rm /Users/yamazato/Sites/nuocw-mdfile-generator/src/pages/courses-en/022-University-Wide-Liberal-Arts-*2014.md');
exec('/bin/rm /Users/yamazato/Sites/nuocw-mdfile-generator/src/pages/courses-en/270-Archaeological-Research-on-the-History-of-Ancient-*2009.md');
exec('/bin/rm /Users/yamazato/Sites/nuocw-mdfile-generator/src/pages/courses-en/044-Methods-of-Teaching-II*2010.md');
exec('/bin/rm /Users/yamazato/Sites/nuocw-mdfile-generator/src/pages/courses-en/057-University-Wide-Liberal-Arts*-2006.md');
exec('/bin/rm /Users/yamazato/Sites/nuocw-mdfile-generator/src/pages/courses-en/249-Gender-and-Literature-*-2011.md');
exec('/bin/rm /Users/yamazato/Sites/nuocw-mdfile-generator/src/pages/courses-en/304-Second-Language-Acquisition-*-2011.md');
exec('/bin/rm /Users/yamazato/Sites/nuocw-mdfile-generator/src/pages/courses-en/053-The-Structure-of-Representation-in-the-Post-Roman-Era*-2006.md');
exec('/bin/rm /Users/yamazato/Sites/nuocw-mdfile-generator/src/pages/courses-en/064-Behavioristics-Lecture-II*-2010.md');
exec('/bin/rm /Users/yamazato/Sites/nuocw-mdfile-generator/src/pages/courses-en/156-Methods-of-Teaching-I*-2010.md');
exec('/bin/rm /Users/yamazato/Sites/nuocw-mdfile-generator/src/pages/courses-en/435-C*-2014.md');
exec('/bin/rm /Users/yamazato/Sites/nuocw-mdfile-generator/src/pages/courses-en/472-World-and-Image-in-Japanese-Narrative*-2013.md');

exec('/bin/rm /Users/yamazato/Sites/NUOCW-Project/nuocw-release-en/src/pages/courses/*.md') ;
exec('/bin/rm /Users/yamazato/Sites/NUOCW-Project/nuocw-release-en/src/pages/courses-en/*.md') ;
// exec('/bin/rm /Users/yamazato/Sites/NUOCW-Project/nuocw-preview/src/pages/farewell/*.md') ;

// 以下、サンプルページ
// exec('/bin/cp /Users/yamazato/Sites/nuocw-mdfile-generator/course-sample/*.md /Users/yamazato/Sites/nuocw-mdfile-generator/src/pages/courses/') ;
// exec('/bin/cp /Users/yamazato/Sites/nuocw-mdfile-generator/farewell-sample/*.md /Users/yamazato/Sites/nuocw-mdfile-generator/src/pages/farewell/') ;

exec('/bin/cp /Users/yamazato/Sites/nuocw-mdfile-generator/src/pages/courses-en/*.md /Users/yamazato/Sites/NUOCW-Project/nuocw-release-en/src/pages/courses-en/') ;
exec('/bin/cp /Users/yamazato/Sites/nuocw-mdfile-generator/Revised-MD-Files/*.md /Users/yamazato/Sites/NUOCW-Project/nuocw-release-en/src/pages/courses-en/') ;
// exec('/bin/cp /Users/yamazato/Sites/NUOCW-Project/nuocw-release/src/pages/courses/*.md /Users/yamazato/Sites/NUOCW-Project/nuocw-release-en/src/pages/courses/') ;
// exec('/bin/cp /Users/yamazato/Sites/nuocw-mdfile-generator/src/pages/farewell/*.md /Users/yamazato/Sites/NUOCW-Project/nuocw-preview/src/pages/farewell/') ;

// adeos
// exec('/bin/rm /Volumes/yamazato/Sites/nuocw-new-site/src/pages/courses/*.md') ;
// exec('/bin/rm /Volumes/yamazato/Sites/nuocw-new-site/src/pages/farewell/*.md') ;

// exec('/bin/cp /Users/yamazato/Sites/NUOCW-Project/nuocw-preview/static/kanban/* /Volumes/yamazato/Sites/nuocw-new-site/static/kanban/'  );
// exec('/bin/cp /Users/yamazato/Sites/nuocw-mdfile-generator/src/pages/courses/*.md /Volumes/yamazato/Sites/nuocw-new-site/src/pages/courses/') ;
// exec('/bin/cp /Users/yamazato/Sites/nuocw-mdfile-generator/src/pages/farewell/*.md /Volumes/yamazato/Sites/nuocw-new-site/src/pages/farewell/') ;



?>
</body>
</html>
