<?php

// OUTPUT クラス
//  - 画面表示にかかわるクラス

class OUTPUT
{

    public static function printErrorMessage($message)
    {
        require_once('Smarty_OCW.class.php');
        $tpl = new Smarty_OCW('lib');

        $tpl->assign("message", $message);
        $tpl->display("error_message.tpl");
    }
}
