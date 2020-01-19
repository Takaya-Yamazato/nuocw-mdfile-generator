<?php
// Smarty_OCW class

require_once(__DIR__.'/../../vendor/autoload.php');
require_once(__DIR__.'/Smarty_OCW_Exception.class.php');
require_once(dirname(__FILE__) . '/../ocw_define.php');

// 各デフォルトディレクトリ定義
// define(SMARTY_OCW_TEMPLATES, OCWHOME . '/templates');
// define(SMARTY_OCW_TEMPLATES_C, OCWHOME . '/templates_c');
define('SMARTY_OCW_CONFIGS', OCWHOME . '/configs');
define('SMARTY_OCW_CACHE', OCWHOME . '/cache');
define('SMARTY_OCW_PLUGINS', OCWHOME. '/plugins');

class Smarty_OCW extends Smarty
{
    public function __construct($subdir = '')
    {
        parent::__construct();    // Smartyのコンストラクタ実行

        // 相対パスの時は, OCWHOME が基準.
        if (substr($subdir, 0, 1) != '/') {
            $subdir = OCWHOME . '/' . $subdir;
        }
        
        // 最後の / は削除.
        if (substr($subdir, -1) == '/') {
            $subdir = substr($subdir, 0, -1);
        }

        // 各種ディレクトリ設定 (templates は引数で与えたサブディレクトリの下に設定)
        // テンプレート置き場
        $this->template_dir = $subdir . '/templates';
        // コンパイル済みテンプレート置き場
        $this->compile_dir = $subdir . '/templates_c';
        // コンフィグファイル置き場
        $this->config_dir = SMARTY_OCW_CONFIGS;
        // キャッシュ置き場
        $this->cache_dir = SMARTY_OCW_CACHE;
        // プラグイン置き場
        $this->plugins_dir[] = SMARTY_OCW_PLUGINS;
        
        // $this->caching = 1; // キャッシュを有効にする 1 or 2 (default 0)
        // $this->compile_check = false; // コンパイル済みテンプレートを更新しない(運営段階等で使う)

        // 内部エラー発生時に例外を投げるコンパイラクラスの指定
        $this->compiler_class = 'Smarty_Compiler_OCW';
        $this->compiler_file = __DIR__.'/Smarty_Compiler_OCW.class.php';

        // 他にも初期設定が必要なら追加
        // 例えばページタイトルを引数で与えるようにするなど $this->assign('title', $title); 

        // 定数の読み込み
        $this->config_load('const.conf');
    }

    /**
     * 常に例外Smarty_OCW_Exception を投げる
     *
     * @param string $error_msg
     * @param integer $level
     * @throws Smarty_OCW_Exception
     * @see Smarty::trigger_error
     */
    public function trigger_error($error_msg, $level = E_USER_WARNING)
    {
        throw new Smarty_OCW_Exception($error_msg, $level);
    }
}
