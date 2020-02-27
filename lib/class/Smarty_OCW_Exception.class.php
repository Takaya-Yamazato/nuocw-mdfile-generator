<?php

/**
 * Smarty_OCW_Exception class.
 * Smarty_OCWクラスおよびSmarty_Compiler_OCWクラスのtrigger_errorで投げられる例外クラス
 */
class Smarty_OCW_Exception extends Exception
{
    private $current_file;

    /**
     * Smarty_OCW_Exception constructor.
     * @param string $message
     * @param int $code
     * @param string $current_file
     * @param Exception $previous
     */
    public function __construct($message = null, $code = 0, $current_file = null, Exception $previous = null)
    {
        $this->current_file = $current_file;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return null|string
     */
    public function getCurrentFile()
    {
        return $this->current_file;
    }
}
