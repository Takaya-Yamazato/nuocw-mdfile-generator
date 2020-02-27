<?php

// URLを与えてそのページのhtmlをtidyに渡し、tidyの出力を文字列として返す。
// $chartype_option: euc-jp => 不要, shift_jis => -shiftjis, utf8 => -utf8
// $chartype_option: euc-jp => -euc-jp, shift_jis => -shiftjis, utf8 => 不要 (2014/10/17 変更)
function tidyCheckUrl($url, $chartype_option = '')
{
    $tidy = dirname(__FILE__) . '/tidy';
    $gethtml = dirname(__FILE__) . '/gethtml.php';
    error_reporting(E_ALL);
    mb_http_output('pass');
    
    $command = "php -q $gethtml $url | $tidy $chartype_option -e";
    $descriptorspec = array(
        //0 => array("pipe", "r"), // stdin
        //1 => array("pipe", "w"), // stdout
        2 => array("pipe", "w")  // stderr
    );
    $process = proc_open($command, $descriptorspec, $pipes);
    $html = "";
    
    if (is_resource($process)) {
        $html = $html . "<pre>\n";
        // 標準エラー出力 (tidy はhtml整形ツールであり、stdoutに整形後html, stderrにerror等を出力する)
        while ($line = fgets($pipes[2])) {
            $html = $html . htmlspecialchars($line);
            if (preg_match('/were found/', $line)) {
                break;
            }
        }
        $html = $html . "</pre>\n";
        $html = preg_replace('/\\n\\n/', "\n", $html);
        fclose($pipes[2]);
        
        proc_close($process);
    }
    return $html;
}

// htmlのソース文字列をtidyに渡し、tidyの出力を文字列として返す。
function tidyCheckHtml($html_source, $chartype_option = '')
{
    $tidy = dirname(__FILE__) . '/tidy';
    error_reporting(E_ALL);
    mb_http_output('pass');
    
    $command = "$tidy $chartype_option -e";
    $descriptorspec = array(
        0 => array("pipe", "r"), // stdin
        //1 => array("pipe", "w"), // stdout
        2 => array("pipe", "w")  // stderr
    );
    $process = proc_open($command, $descriptorspec, $pipes);
    $html = "";
    
    if (is_resource($process)) {
        //標準入力として html のソースを与える
        fputs($pipes[0], $html_source);
        fclose($pipes[0]);

        $html = $html . "<pre>\n";
        // 標準エラー出力
        while ($line = fgets($pipes[2])) {
            $html = $html . htmlspecialchars($line);
            if (preg_match('/were found/', $line)) {
                break;
            }
        }
        $html = $html . "</pre>\n";
        $html = preg_replace('/\\n\\n/', "\n", $html);
        fclose($pipes[2]);
        
        proc_close($process);
    }
    return $html;
}

// TidyのチェックにはHTMLとして成立している必要があるので
// HTMLの一部のソース(body内)のチェックが出来るようヘッダとフッタを付加して適用
// HTML 4.01のヘッダの付け足しなので注意。
function tidyCheckHtmlPart($html_part_source, $chartype_option = '')
{
    $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">'
    . '<html lang="ja"><head><title>test</title></head><body>'
    . $html_part_source    . "</body></html>";
    return tidyCheckHtml($html, $chartype_option);
}

?> 
