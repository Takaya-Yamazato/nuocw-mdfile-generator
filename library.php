<?php
/** library.php
 *
 * nuocw-mdfile-generator で利用する library です
 *
 **/

function extractCommonWords($string){
    $stopWords = array('i','a','about','an','and','are','as','at','be','by','com','de','en',
    'for','from','how','in','is','it','la','of','on','or','that','the','this','to','was',
    'what','when','where','who','will','with','und','the','www','students','student','hard',
    'lecture','class','course','school','overview','problem','fundamentals','research',
    'topics','more', 'those', 'have', 'useful', 'your', 'their', 'other', 'does', 'help', 'cases',
    'during', 'each', 'they', 'purpose', 'popular', 'accepted', 'understanding', 'problems', 'contents',
    '- -', 'such', 'issues', 'ability', 'given', 'should', 'important', 'which', 'such','individually',
    'entire', 'results', 'various', 'extensions', 'input', 'main', 'many', 'number', 'different', 'them',
    'held', 'aims','would', 'like', '0', '0 ', 'aims', 'using', 'learning','give','make', 'best','ocwlink','into',
    'way', 'available', 'large', 'certain', 'basic', 'possible', 'appropriate', 'controlled', 'actual',
    '- - ', 'width25', 'used', 'going', 'deschomework', 'universitys', 'because', 'also', 'including',
    'width20', 'were', 'some', 'universities',
);


    $string = preg_replace('/\s\s+/i', '', $string); // replace whitespace
    $string = trim($string); // trim the string
    $string = preg_replace('/[^a-zA-Z0-9 -]/', '', $string); // only take alphanumerical characters, but keep the spaces and dashes too…
    $string = strtolower($string); // make it lowercase

    preg_match_all('/\b.*?\b/i', $string, $matchWords);
    $matchWords = $matchWords[0];

    foreach ( $matchWords as $key=>$item ) {
        if ( $item == '' || in_array(strtolower($item), $stopWords) || strlen($item) <= 3 ) {
            unset($matchWords[$key]);
        }
    }
    $wordCountArr = array();
    if ( is_array($matchWords) ) {
        foreach ( $matchWords as $key => $val ) {
            $val = strtolower($val);
            if ( isset($wordCountArr[$val]) ) {
                $wordCountArr[$val]++;
            } else {
                $wordCountArr[$val] = 1;
            }
        }
    }
    arsort($wordCountArr);
    $wordCountArr = array_slice($wordCountArr, 0, 5);
    return $wordCountArr;
}

function remove_html_special_chars($string) {
    $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
    $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.

    return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
 }

function extractKeyWords($string) {
    mb_internal_encoding('UTF-8');
    $stopwords = array();
    $string = preg_replace('/[\pP]/u', '', trim(preg_replace('/\s\s+/iu', '', mb_strtolower($string))));
    $matchWords = array_filter(explode(' ',$string) , function ($item) use ($stopwords) { return !($item == '' || in_array($item, $stopwords) || mb_strlen($item) <= 2 || is_numeric($item));});
    $wordCountArr = array_count_values($matchWords);
    arsort($wordCountArr);
    return array_keys(array_slice($wordCountArr, 0, 5));
  }

// NOTICEを非表示
 error_reporting(E_ALL & ~E_NOTICE);

function space_trim ($str) {
    // 行頭の半角、全角スペースを、空文字に置き換える
    $str = preg_replace('/^[ 　]+/u', '', $str);

    // 末尾の半角、全角スペースを、空文字に置き換える
    $str = preg_replace('/[ 　]+$/u', '', $str);

    return $str;
}
function break_trim ($str){
$str = preg_replace('/\n{3,}/', "\n\n", $str);
}
function convertEOL($string, $to = "\n")
{
    return preg_replace("/\r\n|\r|\n/", $to, $string);
}

function mbTrim($pString)
{
    // 不要な制御文字を削除
    return preg_replace('/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', '', $pString);
}

//　メモ：
// 改行の削除
// $text = preg_replace('/(?:\n|\r|\r\n)/', '', $text );

function text2entities($text)
{
  return preg_replace_callback('/./u', function($m){
        $s = $m[0];
        $len = strlen($s);
        switch ($len) {
        case 1: return $s;
        case 2: return '&#'.(((ord($s[0])&0x1F)<<6)|(ord($s[1])&0x3F)).';';
        case 3: return '&#'.(((ord($s[0])&0xF)<<12)|((ord($s[1])&0x3F)<<6)|(ord($s[2])&0x3F)).';';
        case 4: return '&#'.(((ord($s[0])&0x7)<<18)|((ord($s[1])&0x3F)<<12)|((ord($s[2])&0x3F)<<6)
                             |(ord($s[3])&0x3F)).';';
        case 5: return '&#'.(((ord($s[0])&0x3)<<24)|((ord($s[1])&0x3F)<<18)|((ord($s[2])&0x3F)<<12)
                            |((ord($s[3])&0x3F)<<6)|(ord($s[4])&0x3F)).';';
        case 6: return '&#'.(((ord($s[0])&0x1)<<30)|((ord($s[1])&0x3F)<<24)|((ord($s[2])&0x3F)<<18)
                            |((ord($s[3])&0x3F)<<12)|((ord($s[4])&0x3F)<<6)|(ord($s[5])&0x3F)).';';
        }
        return $s;
      }, $text);
}

function entities2text($text)
{
  return
    preg_replace_callback('/&#([0-9]+);/u', function($m){
        $u = intval($m[1]);
             if (0x00000000 <= $u && $u <= 0x0000007F) { return chr($u); }
        else if (0x00000080 <= $u && $u <= 0x000007FF) { return chr(0xC0|($u>>6)).chr(0x80|($u&0x3F)); }
        else if (0x00000800 <= $u && $u <= 0x0000FFFF)
             { return chr(0xE0|($u>>12)).chr(0x80|(($u>>6)&0x3F)).chr(0x80|($u&0x3F)); }
        else if (0x00010000 <= $u && $u <= 0x001FFFFF)
             { return chr(0xF0|($u>>18)).chr(0x80|(($u>>12)&0x3F)).chr(0x80|(($u>>6)&0x3F))
                     .chr(0x80|($u&0x3F)); }
        else if (0x00200000 <= $u && $u <= 0x03FFFFFF)
             { return chr(0xF8|($u>>24)).chr(0x80|(($u>>18)&0x3F)).chr(0x80|(($u>>12)&0x3F))
                     .chr(0x80|(($u>>6)&0x3F)).chr(0x80|($u&0x3F)); }
        else if (0x04000000 <= $u && $u <= 0x04000000)
             { return chr(0xFC|($u>>30)).chr(0x80|(($u>>24)&0x3F)).chr(0x80|(($u>>18)&0x3F))
                     .chr(0x80|(($u>>12)&0x3F)).chr(0x80|(($u>>6)&0x3F)).chr(0x80|($u&0x3F)); }
        return $s;
      }, $text);
}

function check_page_status ($course_id, $page_type){

    // echo "<br>course_id : ".$course_id." page_type : ".$page_type ;

        $page_id_sql = "SELECT p.page_id FROM pages p
            WHERE p.course_id = $course_id AND p.page_type = '$page_type'
            AND NOT EXISTS
            ( SELECT p_s.status FROM page_status p_s
            WHERE p_s.page_id = p.page_id AND
            ( p_s.status = '06' OR p_s.status = '07'
                OR p_s.status = '08' OR p_s.status = '09') )
            ORDER BY page_id DESC LIMIT 1 ; " ;
    // echo "<br>page_id_sql : ".$page_id_sql."<br>" ;

    $page_id_result = pg_query($page_id_sql);

    if (!$page_id_result) {
    die('クエリーが失敗しました。'.pg_last_error());
    }
    $page_id_array = pg_fetch_all_columns($page_id_result);

    if (!empty($page_id_array)){

        // var_dump($page_id_array);
        // echo "<br>page_id : ".$page_id_array[0]."<br><br>" ;
        // print_r($page_id_array);

        return $page_id_array[0] ;

        // var_dump($page_id_sql) ;
    }else{
        return '' ;
    }


}

function get_contents ($page_id, $contents_type) {
    $sql = "SELECT contents.contents FROM page_contents, contents
    WHERE contents.pid = page_contents.contents_id
    AND contents.type = '$contents_type'
    AND page_contents.page_id = $page_id
    ORDER BY contents.id DESC LIMIT 1 ; " ;

// echo "<br>get_contents_sql : ".$sql."<br>" ;

    $result = pg_query($sql);
    if (!$result) {
    die('クエリーが失敗しました。'.pg_last_error());
    }
    $array = pg_fetch_all($result);
    // echo "<br> get_contents array: " ;
    // print_r($array);

    $contents = '';

    if(!empty($array)){
        if (!mbTrim($array[0]['contents'])){
            // echo "データがありません！" ;
            $contents ="" ;
        }else{
            // echo $array[0]['contents']."array<br>" ;
            // print_r($array);
            $contents = $array[0]['contents'] ;

            // 改行コードを LF(\n) に統一
            $contents = preg_replace("/\r\n|\r/s","\n",$contents);
            // $line = str_replace("\r\n","\n",$line);
            // $line = str_replace("\r","\n",$line);

            // {#pdf#} を削除
            $contents = preg_replace('/\{#pdf#\}/', "", $contents) ;
            // {#pdf_e#} を削除
            $contents = preg_replace('/\{#pdf_e#\}/', "", $contents) ;
            // {overview lang="en"} を削除
            $contents = preg_replace('/\{overview lang=\"en\"\}/', "", $contents) ;

           // {overview lang="en" header="Course Aims"} を削除
           $contents = preg_replace('/\{overview lang=\"en\" header=\"Course Aims\"\}/', "", $contents) ;

            // コメントアウト（<!-- ...  -->）を削除
            $contents = preg_replace('/<!--[\s\S]*?-->/s', '', $contents);


            // ### タイトル　が　NULL なら html タグを markdown へ変換
            $contents_tag = $contents_tag = '/\#+\s(\S+)/';
            if ( preg_match_all($contents_tag, $contents) == NULL ){
                // $md = new Markdownify\Converter() ;
                $md = new Markdownify\Converter(Markdownify\Converter::LINK_IN_PARAGRAPH, false, true);
                // $md = new Markdownify\Converter($linkPosition = LINK_IN_PARAGRAPH, $bodyWidth = MDFY_BODYWIDTH, $keepHTML = MDFY_KEEPHTML) ;
                $contents = $markdown = entities2text( $md->parseString( text2entities( $contents ) . PHP_EOL) );
                unset($md);
            }

        // stormvideo は削除
        if(preg_match('/stormvideo_link/s',$contents)){

            // echo "<br>" ;
            // echo "<br> stormvideo_link ".htmlspecialchars_decode($contents, ENT_NOQUOTES);
            // ### 講義ビデオ　を削除
            $contents = preg_replace('/### 講義ビデオ/', '', $contents);
            $contents = preg_replace('/^.*stormvideo_link.*$/um','',$contents);
            $contents = preg_replace('/^.*Internet Explorer.*$/um','',$contents);
            $contents = preg_replace('/^.*Google Chrome.*$/um','',$contents);

            // echo "<br> stormvideo_del ".htmlspecialchars_decode($contents, ENT_NOQUOTES)."<br>";
        }

            // // #で改行
            // $contents_tag = $contents_tag = '/\#+(\S+)/';
            // if ( preg_match_all($contents_tag, $contents, $tag_match) ){
            //   $ii = 0;
            //   // print_r($tag_match);
            //   // echo "<br>";
            //   foreach ($tag_match[0] as $value){
            //     $contents = str_replace( $tag_match[0][$ii] , "\n\n".$tag_match[0][$ii] , $contents ) ;
            //       $ii++;
            //       // echo "<br>".$ii." contents: ".$contents."<br>" ;
            //     }
            // }
            // ### タイトル　を抜き出す
            // $contents_tag = '/\#+\s(\S+)\s/';
            // if ( preg_match_all($contents_tag, $contents, $tag_match) ){
            //   $ii = 0;
            //   // print_r($tag_match);
            //   // echo "<br>";
            //   foreach ($tag_match[0] as $value){
            //     $contents = str_replace( $tag_match[0][$ii] , "\n".$tag_match[0][$ii]."\n" , $contents ) ;
            //       $ii++;
            //       // echo "<br>".$ii." contents: ".$contents."<br>" ;
            //     }
            // }
            // // ###タイトル　を抜き出す（### の後にスペースが無い）
            // $contents_tag = '/\#+(\S+)\s/';
            // if ( preg_match_all($contents_tag, $contents, $tag_match) ){
            //   $ii = 0;
            //   // print_r($tag_match);
            //   // echo "<br>";
            //   foreach ($tag_match[0] as $value){
            //     $contents = str_replace( $tag_match[0][$ii] , "\n\n".$tag_match[0][$ii] , $contents ) ;
            //       $ii++;
            //       // echo "<br>".$ii." contents: ".$contents."<br>" ;
            //     }
            // }
                // // * タイトル　を抜き出す
                // $contents_tag_asterisk = '/\*+\s(\S+)\s/';
                // if ( preg_match_all($contents_tag_asterisk, $contents, $tag_match_asterisk) ){
                //   $ii = 0;
                //   // print_r($tag_match);
                //   // echo "<br>";
                //   foreach ($tag_match_asterisk[0] as $value){
                //     $contents = str_replace( $tag_match_asterisk[0][$ii] , "\n".$tag_match_asterisk[0][$ii] , $contents ) ;
                //       $ii++;
                //     }
                // }

            }


        // なぜだかバックスラッシュ「\」が残るので削る
        $contents = str_replace('\\', '' , $contents) ;

        // なぜだか残っている「{tr}」を「<tr>」へ変換
        $contents = str_replace('{tr}', "<tr>" , $contents) ;

        // dl要素で定義リストを表し、dt要素、dd要素でリストの内容を構成します。
        // 語句を説明するdd要素は、語句を表すdt要素の後ろに記述します。
        // <dl> タグを削除
        $contents = str_replace('<dl>', '' , $contents) ;
        // </dl> タグを削除
        $contents = str_replace('</dl>', '' , $contents) ;

        // // <dt> タグを「- 」へ変換
        $contents = str_replace('<dt>', '' , $contents) ;
        // // </dt> タグを削除
        $contents = str_replace('</dt>', '' , $contents) ;

        // <dd> タグを「- 」へ変換
        // $contents = str_replace('<dd>', "- " , $contents) ;
        // </dd> タグを削除
        // $contents = str_replace('</dd>', '' , $contents) ;

        // {#hr#} タグを「---」へ変換
        $contents = str_replace('{#hr#}', '
        ---' , $contents) ;

        // 残っている html タグを削除
        // $contents = strip_tags ($contents) ;

        $dd_tag = '/(?<=\<dd\>).+?(?=\<\/dd\>)/s';
        if( preg_match_all($dd_tag, $contents, $dd_tag_match) ){

            // $dd_tag_match = ltrim( $dd_tag_match ) ;
            // echo "<br><br> dd_tag_match : " ; var_dump($dd_tag_match) ; echo "<br>" ;
            // echo "<br><br> dd_tag_match : ".$dd_tag_match[0][0] ;

            $dd_tag2 = filter_var($dd_tag_match, FILTER_CALLBACK,
            ['options' => function ($value) {
                return "- ".trim($value) ;
            }]);
            $ii = 0;
            foreach ($dd_tag2[0] as $value) {
                // echo "<br> key: " ; var_dump(trim($value)) ;
                // echo "<br> ii: ".$ii;
                $value = str_replace("
    ","",$value);
                $contents = str_replace($dd_tag_match[0][$ii],trim($value),$contents);
                // echo "<br> value: ".$value;
                $contents = str_replace('<dd>','',$contents);
                // echo "<br> no-dd: ".$contents;
                $contents = str_replace("</dd>","",$contents);
                $ii ++ ;
            }
            unset($value);
            // $contents = str_replace('    ','',$contents) ;

            $array = explode("\n", $contents); // とりあえず行に分割
            $array = array_map('space_trim', $array); // 各行にspace_trim()をかける
            $array = array_filter($array, 'strlen'); // 文字数が0の行を取り除く
            // $array = array_map('break_trim', $array); // 2行以上を取り除く
            $array = array_values($array); // これはキーを連番に振りなおしてるだけ
            // echo implode("<br><br>", $array) ;
            $contents = implode("\n\n", $array) ;
            // $contents = $array ;
            // echo "<br> dd_tag_match : " ; var_dump($array) ;
            // $contents2 = array_map('ltrim', $contents);
            // $contents2 = str_replace($dd_tag_match,$dd_tag2,$contents) ;
            // $contents = array_map('ddcalc', $dd_tag_match);

            // echo "<br><br> dd_tag_match: ".preg_replace("/\r\n|\r/s","\n",$contents) ;

        }

        $dt_tag = '/(?<=\<dt\>).+?(?=\<\/dt\>)/s';
        if( preg_match_all($dt_tag, $contents, $dt_tag_match) ){

            // echo "<br> dd_tag_match : " ; var_dump($dd_tag_match) ;

            $dt_tag2 = filter_var($dt_tag_match, FILTER_CALLBACK,
            ['options' => function ($value) {
                return "- ".$value ;
            }]);
            $ii = 0;
            foreach ($dt_tag2[0] as $value) {
                // echo "<br> key: " ; var_dump($value);
                // echo "<br> ii: ".$ii;
                $value = str_replace("
    ","",$value);
                $contents = str_replace($dt_tag_match[0][$ii],trim($value),$contents);
                $contents = str_replace("<dt>","",$contents);
                $contents = str_replace("</dt>","",$contents);
                $ii ++ ;
            }
            unset($value);
            $contents = str_replace('    ','',$contents) ;

            $array = explode("\n", $contents); // とりあえず行に分割
            $array = array_map('space_trim', $array); // 各行にspace_trim()をかける
            $array = array_filter($array, 'strlen'); // 文字数が0の行を取り除く
            // $array = array_map('break_trim', $array); // 2行以上を取り除く
            $array = array_values($array); // これはキーを連番に振りなおしてるだけ
            // echo implode("<br><br>", $array) ;
            $contents = implode("\n", $array) ;
            //  $contents = $array ;
        }

        if( preg_match('/\<table/', $contents) ){

            // echo "<br> dd_tag_match : " ; var_dump($dd_tag_match) ;

        $array = explode("\n", $contents); // とりあえず行に分割
        $array = array_map('rtrim', $array); // 各行にspace_trim()をかける
        $array = array_filter($array, 'strlen'); // 文字数が0の行を取り除く
        // $array = array_map('break_trim', $array); // 2行以上を取り除く
        $array = array_values($array); // これはキーを連番に振りなおしてるだけ
        // echo implode("<br><br>", $array) ;
        $contents = implode("\n", $array) ;

        }
    }
    return $contents ;
}

function ddcalc($n){
    //コールバック関数
    return( ":  ".$n);
}

function dtcalc($n){
    //コールバック関数
    return( "- ".$n);
}

function array_map_recursive(callable $func, array $arr) {
    array_walk_recursive($arr, function(&$v) use ($func) {
        $v = $func($v);
    });
    return $arr;
}

function get_contents_without_Markdownify ($page_id, $contents_type) {
    $sql = "SELECT contents.contents FROM page_contents, contents
                    WHERE contents.pid = page_contents.contents_id
                    AND contents.type = '$contents_type'
                    AND page_contents.page_id = $page_id
                    ORDER BY contents.id DESC LIMIT 1 ; " ;

  $result = pg_query($sql);
  if (!$result) {
  die('クエリーが失敗しました。'.pg_last_error());
  }
  $array = pg_fetch_all($result);

  if(!empty($array)){

    if (!mbTrim($array[0]['contents'])){
        // echo "データがありません！" ;
        $contents ="" ;
    }else{
        // echo $array[0]['contents']."array<br>" ;
        // print_r($array);
        $contents = $array[0]['contents'] ;

        // 改行コードを LF(\n) に統一
        $contents = preg_replace("/\r\n|\r/","\n",$contents);
        // $line = str_replace("\r\n","\n",$line);
        // $line = str_replace("\r","\n",$line);

        // {#pdf#} を削除
        $contents = preg_replace('/\{#pdf#\}/', "", $contents) ;
        // {#pdf_e#} を削除
        $contents = preg_replace('/\{#pdf_e#\}/', "", $contents) ;
        // {overview lang="en"} を削除
        $contents = preg_replace('/\{overview lang=\"en\"\}/', "", $contents) ;

        // {overview lang="en" header="Course Aims"} を削除
        $contents = preg_replace('/\{overview lang=\"en\" header=\"Course Aims\"\}/', "", $contents) ;

        // コメントアウト（<!-- ...  -->）を削除
        $contents = preg_replace('/<!--[\s\S]*?-->/s', '', $contents);

        // なぜだかバックスラッシュ「\」が残るので削る
        $contents = str_replace('\\', '' , $contents) ;

        // なぜだか残っている「{tr}」を「<tr>」へ変換
        $contents = str_replace('{tr}', "<tr>" , $contents) ;



        // dl要素で定義リストを表し、dt要素、dd要素でリストの内容を構成します。
        // 語句を説明するdd要素は、語句を表すdt要素の後ろに記述します。
        // <dl> タグを削除
        $contents = str_replace('<dl>', '' , $contents) ;
        // </dl> タグを削除
        $contents = str_replace('</dl>', '' , $contents) ;

        // <dt> タグを「####」へ変換
        $contents = str_replace('<dt>', "" , $contents) ;
        // </dt> タグを削除
        $contents = str_replace('</dt>', '' , $contents) ;

        // <dd> タグを「- 」へ変換
        //   $contents = str_replace('<dd>', "" , $contents) ;
        // </dd> タグを削除
        //   $contents = str_replace('</dd>', '' , $contents) ;

        // {#hr#} タグを「---」へ変換
        $contents = str_replace('{#hr#}', '---' , $contents) ;

        $dd_tag = '/(?<=\<dd\>).+?(?=\<\/dd\>)/s';
        if( preg_match_all($dd_tag, $contents, $dd_tag_match) ){

            // echo "<br> dd_tag_match : " ; var_dump($dd_tag_match) ;

            $dd_tag2 = filter_var($dd_tag_match, FILTER_CALLBACK,
            ['options' => function ($value) {
                return "- ".$value ;
            }]);
            $ii = 0;
            foreach ($dd_tag2[0] as $value) {
                // echo "<br> key: " ; var_dump($value);
                // echo "<br> ii: ".$ii;
                $value = str_replace("
    ","",$value);
                $contents = str_replace($dd_tag_match[0][$ii],trim($value),$contents);
                $contents = str_replace("<dd>","",$contents);
                $contents = str_replace("</dd>","",$contents);
                $ii ++ ;
            }
            unset($value);

            $array = explode("\n", $contents); // とりあえず行に分割
            $array = array_map('space_trim', $array); // 各行にspace_trim()をかける
            $array = array_filter($array, 'strlen'); // 文字数が0の行を取り除く
            // $array = array_map('break_trim', $array); // 2行以上を取り除く
            $array = array_values($array); // これはキーを連番に振りなおしてるだけ
            // echo implode("<br><br>", $array) ;
            $contents = implode("\n\n", $array) ;
            // echo "<br><br> dd_tag_match: ".preg_replace("/\r\n|\r/s","\n",$contents) ;
        }

        if( preg_match('/\<table/', $contents) ){

            // echo "<br> dd_tag_match : " ; var_dump($dd_tag_match) ;

            $array = explode("\n", $contents); // とりあえず行に分割
            $array = array_map('rtrim', $array); // 各行にspace_trim()をかける
            $array = array_filter($array, 'strlen'); // 文字数が0の行を取り除く
            // $array = array_map('break_trim', $array); // 2行以上を取り除く
            $array = array_values($array); // これはキーを連番に振りなおしてるだけ
            // echo implode("<br><br>", $array) ;
            $contents = implode("\n", $array) ;

            }
    }

    return $contents ;

  }else{
    return '' ;
  }
}

// function convert_ocwlink ($resources, $course_id){

//   $file = '/(?<=\{ocwlink file=\").+?(?=\")/';
//   preg_match_all($file, $resources, $file_match);
//   //print_r($file_match);
//   $desc = '/(?<=desc=\").+?(?=\")/';
//   preg_match_all($desc, $resources, $desc_match);
//   //print_r($desc_match);

//   $ii = 0;
//   foreach ($desc_match[0] as $value){
//       $resources .=
//       "- [".$desc_match[0][$ii]."](/files/".$course_id."/".$file_match[0][$ii].")\n" ;
//       $ii++;
//     }

//   $resources = preg_replace('/(?<={).*?(?=})/', '' , $resources);
//   $resources = preg_replace('/\{\}/', '' , $resources);
//   $resources = str_replace('\\', '' , $resources) ;

//   return $resources ;
// }

// function convert_ocwimg ($resources, $course_id){

//   $file = '/(?<=\{ocwimg file=\").+?(?=\")/';
//   preg_match_all($file, $resources, $file_match);
//   //print_r($file_match);
//   $desc = '/(?<=alt=\").+?(?=\")/';
//   preg_match_all($desc, $resources, $desc_match);
//   //print_r($desc_match);

//   $ii = 0;
//   $temp = "";
//   foreach ($desc_match[0] as $value){
//       $temp .=
//       "\n\n ![".$desc_match[0][$ii]."](/files/".$course_id."/".$file_match[0][$ii].")\n" ;
//       $ii++;
//     }

//   // {〜} までを削除
//   $resources = preg_replace('/(?<={).*?(?=})/', '' , $resources);
//   // 上で残った {} を削除
//   $resources = preg_replace('/\{\}/', '' , $resources);
//   // 半角バックスラッシュを削除
//   $resources = str_replace('\\', '' , $resources) ;
//   $resources = $temp."\n".$resources ;

//   return $resources ;
// }

function category ($division_code){

  switch ($division_code) {
    case 100:
        $category = " - \"教養\"" ;
        break;
    case 110:
        $category = " - \"文学\"" ;
        break;
    case 111:
        $category = " - \"文学\"" ;
        break;
    case 120:
        $category = " - \"教育学\"" ;
        break;
    case 121:
        $category = " - \"教育学\"" ;
        break;
    case 130:
        $category = " - \"法学\"" ;
        break;
    case 140:
		    $category = " - \"経済学\"" ;
        break;
    case 150:
        $category = " - \"情報と文化\"" ;
        break;
    case 151:
        $category = " - \"情報と文化\"" ;
        break;
    case 160:
		    $category = " - \"理学\"" ;
        break;
    case 170:
		    $category = " - \"医学\"" ;
        break;
    case 180:
		    $category = " - \"工学\"" ;
        break;
    case 190:
		    $category = " - \"農学\"" ;
        break;
    case 151:
      $category = " - \"情報学\"" ;
          break;
    case 110:
      $category = " - \"文学\"" ;
          break;
    case 61:
      $category = " - \"情報学\"" ;
          break;
    case "1A0":
        $category = " - \"情報と科学\"" ;
        break;
    case "1B0":
        $category = " - \"国際開発\"" ;
        break;
    case "1C0":
        $category = " - \"数学\"" ;
        break;
    case "1D0":
        $category = " - \"言語と文化\"" ;
        break;
    case "1E0":
        $category = " - \"環境学\"" ;
        break;
    case "1E1":
        $category = " - \"薬学\"" ;
        break;
    case "1F0":
        $category = " - \"言語\"" ;
        break;
    case 200:
        $category = " - \"医学\"" ;
        break;
    case 210:
        $category = " - \"宇宙と地球環境\"" ;
        break;
    case 220:
        $category = " - \"科学\"" ;
        break;
    case 300:
        $category = " - \"地球水循環\"" ;
        break;
    case 310:
        $category = " - \"情報学\"" ;
        break;
    case 400:
        $category = " - \"高等研究\"" ;
        break;
    case 410:
        $category = " - \"保健体育\"" ;
        break;
    case 420:
        $category = " - \"図書\"" ;
        break;
    case 430:
        $category = " - \"アイソトープ\"" ;
        break;
    case 440:
        $category = " - \"遺伝子\"" ;
        break;
    case 450:
        $category = " - \"物質科学\"" ;
        break;
    case 460:
        $category = " - \"高等教育\"" ;
        break;
    case 470:
        $category = " - \"農学\"" ;
        break;
    case 480:
        $category = " - \"年代測定\"" ;
        break;
    case 490:
        $category = " - \"博物館\"" ;
        break;
    case 500:
        $category = " - \"心理学\"" ;
        break;
    case 510:
        $category = " - \"法学\"" ;
        break;
    case 520:
        $category = " - \"生物学\"" ;
        break;
    case 530:
        $category = " - \"情報学\"" ;
        break;
    case 540:
        $category = " - \"小型シンクロトロン\"" ;
        break;
    case 550:
        $category = " - \"図書\"" ;
        break;
    case 570:
        $category = " - \"国際交流\"" ;
        break;
    case 580:
        $category = " - \"電子\"" ;
        break;
    case 590:
        $category = " - \"医学\"" ;
        break;
    case 635:
        $category = " - \"国際交流\"" ;
        break;
    case 640:
        $category = " - \"国際交流\"" ;
        break;
  }

  return $category;
}

function category_e ($division_code){

    switch ($division_code) {
      case 100:
          $category = " - \"Liberal Arts\"" ;
          break;
      case 110:
          $category = " - \"literature\"" ;
          break;
      case 111:
          $category = " - \"Literature\"" ;
          break;
      case 120:
          $category = " - \"Education\"" ;
          break;
      case 121:
          $category = " - \"Education\"" ;
          break;
      case 130:
          $category = " - \"Law\"" ;
          break;
      case 140:
              $category = " - \"Economics\"" ;
          break;
      case 150:
          $category = " - \"Information science and culture\"" ;
          break;
      case 151:
          $category = " - \"Information science and culture\"" ;
          break;
      case 160:
              $category = " - \"Science\"" ;
          break;
      case 170:
              $category = " - \"Medical science\"" ;
          break;
      case 180:
              $category = " - \"Engineering\"" ;
          break;
      case 190:
              $category = " - \"Agriculture\"" ;
          break;
      case 151:
        $category = " - \"Infomatics\"" ;
            break;
      case 110:
        $category = " - \"Literature\"" ;
            break;
      case 61:
        $category = " - \"Infomatics\"" ;
            break;
      case "1A0":
          $category = " - \"Information science and culture\"" ;
          break;
      case "1B0":
          $category = " - \"International development\"" ;
          break;
      case "1C0":
          $category = " - \"Mathmatics\"" ;
          break;
      case "1D0":
          $category = " - \"Linguistics and culture\"" ;
          break;
      case "1E0":
          $category = " - \"Environmental science\"" ;
          break;
      case "1E1":
          $category = " - \"Pharmacology\"" ;
          break;
      case "1F0":
          $category = " - \"Linguistics\"" ;
          break;
      case 200:
          $category = " - \"Medical science\"" ;
          break;
      case 210:
          $category = " - \"Space–Earth environmental research\"" ;
          break;
      case 220:
          $category = " - \"Science\"" ;
          break;
      case 300:
          $category = " - \"Hydrospheric atmospheric research\"" ;
          break;
      case 310:
          $category = " - \"Informatics\"" ;
          break;
      case 400:
          $category = " - \"Higher education\"" ;
          break;
      case 410:
          $category = " - \"Physical education\"" ;
          break;
      case 420:
          $category = " - \"Library science\"" ;
          break;
      case 430:
          $category = " - \"Isotope\"" ;
          break;
      case 440:
          $category = " - \"Gene science\"" ;
          break;
      case 450:
          $category = " - \"Material science\"" ;
          break;
      case 460:
          $category = " - \"Higher study\"" ;
          break;
      case 470:
          $category = " - \"Agriculture\"" ;
          break;
      case 480:
          $category = " - \"Chronological dating\"" ;
          break;
      case 490:
          $category = " - \"Museum\"" ;
          break;
      case 500:
          $category = " - \"Psychology\"" ;
          break;
      case 510:
          $category = " - \"Law\"" ;
          break;
      case 520:
          $category = " - \"Biology\"" ;
          break;
      case 530:
          $category = " - \"Infomatics\"" ;
          break;
      case 540:
          $category = " - \"Synchrotron\"" ;
          break;
      case 550:
          $category = " - \"Library\"" ;
          break;
      case 570:
          $category = " - \"International education &amp; exchange\"" ;
          break;
      case 580:
          $category = " - \"Electronics\"" ;
          break;
      case 590:
          $category = " - \"Medical science\"" ;
          break;
      case 635:
          $category = " - \"International education &amp; exchange\"" ;
          break;
      case 640:
          $category = " - \"International education &amp; exchange\"" ;
          break;
    }

    return $category;
  }
/**
 * Yahoo! JAPAN Web APIのご利用には、アプリケーションIDの登録が必要です。
 * あなたが登録したアプリケーションIDを $appid に設定してお使いください。
 * アプリケーションIDの登録URLは、こちらです↓
 * http://e.developer.yahoo.co.jp/webservices/register_application
 */
$appid = 'dj00aiZpPTN4TVpRVXRKUjZzNiZzPWNvbnN1bWVyc2VjcmV0Jng9MWU-'; // <-- ここにあなたのアプリケーションIDを設定してください。

function show_keyphrase($appid, $sentence){
//   $sentence = escapestring($sentence) ;
//   $sentence = htmlspecialchars_decode($sentence);

//   echo "<br><br>".$sentence ;

  $output = "xml";
  $request  = "http://jlp.yahooapis.jp/KeyphraseService/V1/extract?";
  $request .= "appid=".$appid."&sentence=".urlencode($sentence)."&output=".$output;

  $responsexml = simplexml_load_file($request);

  $result_num = count($responsexml->Result);

  if($result_num > 0){
    // echo "<table>";
    // echo "<tr><td><b>キーフレーズ</b></td><td><b>スコア</b></td></tr>";

    // for($i = 0; $i < $result_num; $i++){
    $num = $result_num ;
    if ( $result_num > 10){
      $num = 10;
    }else{
      $num = $result_num ;
    }
     for($i = 0; $i < $result_num; $i++){
      $result = $responsexml->Result[$i];
      // var_dump($result);
      if ( $result->Score >= 50){
      echo "<tr><td>".escapestring($result->Keyphrase)."</td><td>".escapestring($result->Score)."</td></tr>";
$tags .= "
  - \"".($result->Keyphrase)."\"" ;
    }}
    // echo "</table>";
  }
  return $tags;
}

function mb_wordwrap( $str, $width=20, $break=PHP_EOL )
{
    $c = mb_strlen($str);
    $arr = [];
    for ($i=0; $i<=$c; $i+=$width) {
        $arr[] = mb_substr($str, $i, $width);
    }
    return implode($break, $arr);
}

function remove_accent($str)
{
  $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
  $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
  return str_replace($a, $b, $str);
}

?>
