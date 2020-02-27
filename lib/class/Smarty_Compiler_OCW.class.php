<?php

require_once(__DIR__.'/../../vendor/autoload.php');
require_once(__DIR__.'/Smarty_OCW_Exception.class.php');

/**
 * Class Smarty_Compiler_OCW.
 * Smarty内部の処理で発生したエラー(コンパイルエラー, パースエラー)等をSmarty_OCW_Exceptionとして投げるコンパイラクラス
 */
class Smarty_Compiler_OCW extends Smarty_Compiler
{
    /**
     * @param string $error_msg
     * @param int $error_type
     * @throws Smarty_OCW_Exception
     */
    public function trigger_error($error_msg, $error_type = E_USER_WARNING)
    {
        throw new Smarty_OCW_Exception($error_msg, $error_type, $this->_current_file);
    }
}
