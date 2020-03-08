<?php
/** library.php
 *
 * nuocw-mdfile-generator で利用する library です
 *
 **/

// NOTICEを非表示
 error_reporting(E_ALL & ~E_NOTICE);

function space_trim ($str) {
    // 行頭の半角、全角スペースを、空文字に置き換える
    $str = preg_replace('/^[ 　]+/u', '', $str);
 
    // 末尾の半角、全角スペースを、空文字に置き換える
    $str = preg_replace('/[ 　]+$/u', '', $str);

    return $str;
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


function get_contents ($sql) {

    $result = pg_query($sql);
    if (!$result) {
    die('クエリーが失敗しました。'.pg_last_error());
    }
    $array = pg_fetch_all($result);
    if (!mbTrim($array[0]['contents'])){
        // echo "データがありません！" ;
        $contents ="" ;
    }else{
        // echo $array[0]['contents']."array<br>" ;
        // print_r($array);
        $contents = $array[0]['contents'] ;

        // ### タイトル　を抜き出す
        $contents_tag = '/\#+\s(\S+)\s/';
        if ( preg_match_all($contents_tag, $contents, $tag_match) ){
          $ii = 0;
          // print_r($tag_match);
          // echo "<br>";
          foreach ($tag_match[0] as $value){
            $contents = str_replace( $tag_match[0][$ii] , "\n\n".$tag_match[0][$ii]."\n" , $contents ) ;
              $ii++;
              // echo "<br>".$ii." contents: ".$contents."<br>" ;
            }
        }
        // // ###タイトル　を抜き出す（### の後にスペースが無い）        
        // $contents_tag = '/\#+(\S+)\s/';
        // if ( preg_match_all($contents_tag, $contents, $tag_match) ){
        //   $ii = 0;
        //   // print_r($tag_match);
        //   // echo "<br>";
        //   foreach ($tag_match[0] as $value){
        //     $contents = str_replace( $tag_match[0][$ii] , "<br>".$tag_match[0][$ii]."<br>" , $contents ) ;
        //       $ii++;
        //       // echo "<br>".$ii." contents: ".$contents."<br>" ;
        //     }
        // }
            // * タイトル　を抜き出す
            $contents_tag_asterisk = '/\*+\s(\S+)\s/';
            if ( preg_match_all($contents_tag_asterisk, $contents, $tag_match_asterisk) ){
              $ii = 0;
              // print_r($tag_match);
              // echo "<br>";
              foreach ($tag_match_asterisk[0] as $value){
                $contents = str_replace( $tag_match_asterisk[0][$ii] , "<br>".$tag_match_asterisk[0][$ii], $contents ) ;
                  $ii++;
                }
            }
          
          // 改行が連続する場合、ひとつにまとめる
          $contents = preg_replace('/(\n|\r|\r\n)+/us',"\n", $contents );
    }

    
    // {#pdf#} を削除
    if ( $contents = preg_replace('/\{#pdf#\}/', "", $contents) ){
      // echo "<br>{#pdf#}<br>" ;
      // print_r($contents);
        }

    // html タグを markdown へ変換
    // $md = new Markdownify\Converter() ;
    $md = new Markdownify\Converter(Markdownify\Converter::LINK_AFTER_PARAGRAPH, false, true);
    // $md = new Markdownify\Converter($linkPosition = LINK_IN_PARAGRAPH, $bodyWidth = MDFY_BODYWIDTH, $keepHTML = MDFY_KEEPHTML) ;
    $contents = $markdown = entities2text( $md->parseString( text2entities( $contents ) . PHP_EOL) );
    unset($md);

    // 残っている <dd> タグを削除
    // $contents = strip_tags ($contents) ;

    // なぜだかバックスラッシュ「\」が残るので削る
    $contents = str_replace('\\', '' , $contents) ;

    return $contents ;
}



function convert_ocwlink ($resources, $sort_key){

  $file = '/(?<=\{ocwlink file=\").+?(?=\")/';
  preg_match_all($file, $resources, $file_match);
  //print_r($file_match);
  $desc = '/(?<=desc=\").+?(?=\")/';
  preg_match_all($desc, $resources, $desc_match);
  //print_r($desc_match);
    
  $ii = 0;
  foreach ($desc_match[0] as $value){
      $resources .= 
      "- [".$desc_match[0][$ii]."](/files/".$sort_key."/".$file_match[0][$ii].")\n" ;
      $ii++;
    }

  $resources = preg_replace('/(?<={).*?(?=})/', '' , $resources);
  $resources = preg_replace('/\{\}/', '' , $resources);
  $resources = str_replace('\\', '' , $resources) ;

  return $resources ;
}

function convert_ocwimg ($resources, $sort_key){

  $file = '/(?<=\{ocwimg file=\").+?(?=\")/';
  preg_match_all($file, $resources, $file_match);
  //print_r($file_match);
  $desc = '/(?<=alt=\").+?(?=\")/';
  preg_match_all($desc, $resources, $desc_match);
  //print_r($desc_match);
    
  $ii = 0;
  $temp = "";
  foreach ($desc_match[0] as $value){
      $temp .= 
      "\n\n ![".$desc_match[0][$ii]."](/files/".$sort_key."/".$file_match[0][$ii].")\n" ;
      $ii++;
    }

  // {〜} までを削除
  $resources = preg_replace('/(?<={).*?(?=})/', '' , $resources);
  // 上で残った {} を削除
  $resources = preg_replace('/\{\}/', '' , $resources);
  // 半角バックスラッシュを削除
  $resources = str_replace('\\', '' , $resources) ;
  $resources = $temp."\n".$resources ;

  return $resources ;
}


?>