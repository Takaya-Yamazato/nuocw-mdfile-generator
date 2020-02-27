<?php
/** ocw_sys_config.php
 *
 * 定数のうち, システムに依存するものを記述します.
 * セットアップには, 以下のディレクトリを apache から書きこめるようにすることも必要です:
 *  - 各種コンパイル済みテンプレート置き場 (各種ディレクトリ内のtemplates_c/)
 *  - 画像変換ツールの画像生成先 (htdocs/compose_image/img/)
 *  - ファイルアップロード先     (FILEDIR)
 *  - 公開用ファイル置き場       ($_ISSUANCE 内で設定する public_{tpl,files}_dir)
 **/

mb_internal_encoding('UTF-8');


// 編集システムの URL 
define('SYSTEM_ROOT_WEB_PATH', '/system/branch-4.2/');

// コースデータ
define('DSN', 'pgsql://ocwuser:passwd@localhost:5432+unix/ocwdb');

// アクセスログ (アクセスランキング生成に使用)
define('LOG_DSN', 'pgsql://ocwanlz:passwd@tcp+ad008.media.nagoya-u.ac.jp:5432/ocwpdb');

// ファイルアップロード先のディレクトリ
define('FILEDIR', '/ocw/sites/edit-system/shared/files/');

// ビデオデータの保管場所
define('VIDEO_UPLOAD_DIR', '/ocw/sites/edit-system/shared/public.stormmaker/STORMMaker_contents/');

// Podcast ファイルの保管ディレクトリ
define('PODCAST_STORAGE_DIR', '/ocw/sites/design/podcast-trunk/');

// 動画情報ファイルの場所
define('MOVIE_INFORMATION_CSV', '/ocw/working_copies/ocw-movie-information/contents.csv');


// 発行先設定
//  description:            説明
//  design_dir:             発行に用いるテンプレート
//  public_tpl_dir:         テンプレートの発行先
//  public_files_dir:       バイナリファイルの発行先
//  podcast_files_dir:      Podcast ファイルの発行先
//  public_files_web_path:  バイナリファイルの発行先ディレクトリの URI
//  public_video_web_path:  ビデオファイルの発行先ディレクトリの URI
//  public_htdocs_web_path: 発行先の URI
//  db_dsn:                 発行先の DB
//  clist_now_showing_only: コースリストを公開中のものに限定するか？
//  clist_show_dept_status: コースリストに表示する部局のステータス
//  clist_table:            コースリスト発行先テーブルの情報
//  issue_ja:               日本語版の発行可能対象
//  issue_en:               英語版の発行可能対象
$_ISSUANCE = array(
  'public_release' => array(
    'description' => '一般公開',
    'design_dir' => '/ocw/sites/design/design-2010/',
    'public_tpl_dir' => '/ocw/sites/public-system/trunk/templates/',
    'public_files_dir' => '/ocw/sites/public-system/trunk/files/',
    'podcast_files_dir' => '/ocw/sites/design/podcast-trunk/',
    'public_files_web_path' => './files/',
    'public_video_web_path' => './resource/',
    'public_htdocs_web_path' => 'http://ocw.nagoya-u.jp/',
    'db_dsn' => 'pgsql://ocwanlz:passwd@tcp+ad008.media.nagoya-u.ac.jp:5432/ocwpdb',
    'clist_now_showing_only' => true,
    'clist_show_dept_status' => SCT_SHOW_OK,
    'clist_table' => array(
      'ja' => array(
        'course' => 'courselist_by_coursename',
        'instructor' => 'courselist_by_instructorname',
        'ranking' => 'ranking',
      ),
      'en' => array(
        'course' => 'courselist_en_by_coursename',
        'instructor' => 'courselist_en_by_instructorname',
        'ranking' => 'ranking_en',
      ),
      'course_tag' => 'course_tag',
      'dept' => 'department',
    ),
    'issue_ja' => array('top', 'clist', 'course', 'etc', 'studiochannel', 'access_rank', 'rssfeed'),
    'issue_en' => array()
    ),
  'public_test' => array(
    'description' => '内部公開',
    'design_dir' => '/ocw/sites/design/design-2010/',
    'public_tpl_dir' => '/ocw/sites/public-system/preview-2010/templates/',
    'public_files_dir' => '/ocw/sites/public-system/preview-2010/files/',
    'podcast_files_dir' => '/ocw/sites/public-system/preview-2012/htdocs/podcast/',
    'public_files_web_path' => './files/',
    'public_htdocs_web_path' => 'http://ocw.media.nagoya-u.ac.jp/preview/',
    'db_dsn' => 'pgsql://ocwanlz:passwd@localhost:5432+unix/ocwpdb',
    'clist_now_showing_only' => false,
    'clist_show_dept_status' => SCT_CHECKING,
    'clist_table' => array(
      'ja' => array(
        'course' => 'courselist_by_coursename',
        'instructor' => 'courselist_by_instructorname',
        'ranking' => 'ranking',
      ),
      'en' => array(
        'course' => 'courselist_en_by_coursename',
        'instructor' => '',
        'ranking' => 'ranking_en',
      ),
    ),
    'course_tag_table' => 'pub_course_tag',
    'issue_ja' => array('top', 'clist', 'course', 'etc', 'studiochannel', 'access_rank', 'rssfeed'),
    'issue_en' => array()
  )
);

// デフォルト発行先設定
// 一般公開
define('PUBLIC_ISSUE', 'public_release');
// 内部公開
define('PRIVATE_ISSUE', 'public_test');


// 公開サーバへの転送設定
// ログファイル名
define('RSYNC_LOG', '/tmp/rsync_to_ad008.log');

// rsync コマンド
define('RSYNC', 'rsync -rltgvzOC --delete --protocol=29 -e "ssh -F /ocw/sites/edit-system/shared/etc/ssh_config -i /ocw/sites/edit-system/shared/etc/ocwscp_id_dsa -l ocwscp" ');

// ファイル転送コマンド
define('RSYNC_FILES', 'date >> '.RSYNC_LOG.'; '.RSYNC.'/ocw/sites/public-system/trunk/files/ 133.6.80.25:/ocw/www.new/files/ 2>&1 | tee -a '. RSYNC_LOG);

// テンプレート転送コマンド
define('RSYNC_TEMPLATES', 'date >> '.RSYNC_LOG.'; '.RSYNC.'/ocw/sites/public-system/trunk/templates/ 133.6.80.25:/ocw/www.new/templates/ 2>&1 | tee -a '. RSYNC_LOG);

// ビデオデータ転送先
define('VIDEO_PUBLIC_DIR', '133.6.80.25:/ocw/www.stormmaker/');
