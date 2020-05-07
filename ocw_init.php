<?php

// 依存ライブラリの自動ロード
// require_once(__DIR__.'/../vendor/autoload.php');

// 定数設定
// mb_regex_encoding("EUC-JP");
mb_regex_encoding("UTF-8");
require_once('master_value_define.php');
require_once('ocw_sys_config.php');
require_once('ocw_define.php');

// 環境設定
require_once('init_environmentals.php');

// DB 設定
require_once('init_db.php');

// 共通クラス
require_once LIBDIR . '/class/Smarty_OCW.class.php';
require_once LIBDIR . '/class/OCWVAR.class.php';
require_once LIBDIR . '/class/OUTPUT.class.php';
require_once LIBDIR . '/class/OCWDB.class.php';

// 認証
// require_once('auth.php');

// エラー表示を一時的にON
ini_set("display_errors", "1");
;
