<?php
/** ocw_define.php
 *
 * 定数のうち, システムに依存しないものや,
 * ocw_sys_config.php にある定数を利用して定まるものを記述する.
 *
 */

// ライブラリディレクトリ
define('LIBDIR', dirname(__FILE__));

// ホームディレクトリ
define('OCWHOME', dirname(dirname(__FILE__)));

// 通信プロトコル
define('PROTOCOL', isset($_SERVER['HTTPS']) ? 'https' : 'http');

// システムのURLのベース
define('URL_BASE', PROTOCOL.'://'.$_SERVER['SERVER_NAME'].SYSTEM_ROOT_WEB_PATH);

// 設定ファイル、ビデオデータへのシンボリックリンクを置く作業場所
define('VIDEO_WORK_DIR', OCWHOME.'/htdocs/resource/');
// ビデオの公開状態の保存ファイル
define('VIDEO_STATE_FILE', VIDEO_WORK_DIR.'state.data');

// ログインスクリプト
define('LOGIN_SCRIPT_WEB_PATH', SYSTEM_ROOT_WEB_PATH . 'login.php');

// 確認表示時のファイル表示スクリプト
define('FILE_WEB_PATH', SYSTEM_ROOT_WEB_PATH . 'fileview.php');

// テスト発行・表示スクリプトの URI
define('ISSUE_PREVIEW_WEB_PATH', SYSTEM_ROOT_WEB_PATH . 'issuance/preview.php');

// 公開 URI
define('PUBLIC_WEB_PATH', $_ISSUANCE[PUBLIC_ISSUE]['public_htdocs_web_path']);
define('PREVIEW_WEB_PATH', $_ISSUANCE[PRIVATE_ISSUE]['public_htdocs_web_path']);

// パンくずリストの部局部分の長さの最大
//  - パンくずリスト生成時にチェックする
define('MAX_TOPIC_PATH_LENGTH', 10);

// コースページの発行単位と掲載順序
$_COURSE_PAGES_FORMAT = array(
    array('tpl_name' => 'index',
          'page_name' => '授業ホーム',
          'page_name_en' => 'Course Home',
          'order' => array(PT_COURSEHOME => 'index',
                           PT_F_INDEX => 'f_index',
                           PT_ACHIEVEMENT => 'achievement',
                           PT_TEACHINGTIPS => 'teachingtips')),
    array('tpl_name' => 'syllabus',
          'page_name' => 'シラバス',
          'page_name_en' => 'Syllabus',
          'order' => array(PT_SYLLABUS => 'syllabus',
                           PT_CALENDAR => 'calendar',
                           PT_EVALUATION => 'evaluation')),
    array('tpl_name' => 'f_intro',
          'page_name' => '講師紹介',
          'page_name_en' => 'Introduction',
          'order' => array(PT_F_INTRO => 'f_intro')),
    array('tpl_name' => 'materials',
          'page_name' => '講義資料',
          'page_name_en' => 'Resources',
          'order' => array(PT_LECTURENOTES => 'lecturenotes',
                           PT_ASSIGNMENTS => 'assignments',
                           PT_RELATEDRESOURCES => 'relatedresources',
                           PT_F_RESOURCES => 'f_resources'))
);

// 上の逆引き
$_COURSE_PAGES_FORMAT_INV = array(
    'index' => 'index',
    'f_index' => 'index',
    'achievement' => 'index',
    'teachingtips' => 'index',
    'syllabus' => 'syllabus',
    'calendar' => 'syllabus',
    'evaluation' => 'syllabus',
    'f_intro' => 'f_intro',
    'lecturenotes' => 'materials',
    'assignments' => 'materials',
    'relatedresources' => 'materials',
    'f_resources' => 'materials'
);

// 言語のリスト
$LANG_LIST = array(
    'ja' => '日本語',
    'en' => '英語'
);

// meeting_time テーブルで利用する曜日のリスト
$DAY_LIST = array(
    'MON' => '月曜',
    'TUE' => '火曜',
    'WED' => '水曜',
    'THU' => '木曜',
    'FRI' => '金曜'
  );
$DAY_LIST_E = array(
    'MON' => 'Monday',
    'TUE' => 'Tuesday',
    'WED' => 'Wednesday',
    'THU' => 'Thursday',
    'FRI' => 'Friday'
  );

// meeting_time テーブルで利用する講義時間.
$TIME_LIST = array(
    '00' => '',
    '01' => '1',
    '02' => '2',
    '03' => '3',
    '04' => '4',
    '05' => '5',
    '06' => '6'
  );

// DBアクセス不要で，for_xxx.tplを読み込んで書き出すだけで良いページの名前
// テンプレート生成時に利用
$NONDB_PAGE_NAME_LIST = array(
  'ja' => array(
    'news' => 'おしらせ',
    'faq' => 'ヘルプ (FAQ)',
    'sitemap' => 'サイトマップ',
    'about' => '名大の授業について',
    'glossary' => '用語解説',
    'special' => '特別企画',
    'welcome' => 'ごあいさつ',
    'inquiry' => 'お問合せ',
    'link' => 'リンク',
    'notfound' => 'ページが見つかりません',
    'symp08' => '名古屋大学OCWシンポジウム2008',
    'symp08_mob' => '名古屋大学OCWシンポジウム2008(携帯)',
    'topics' => 'TOPICS',
    'topics2016chem' => 'TOPICS 2016 有機化学実験第2',
    'topics2016engi' => 'TOPICS 2016 電気・電子工学実験 (大実験)',
    'topics2016agri' => 'TOPICS 2016 生物環境科学実験実習',
    'topics2016' => 'TOPICS 2016',
    'topics2015sec' => 'TOPICS 2015 second (No.8)',
    'topics2015' => 'TOPICS 2015',
    'topics2014' => 'TOPICS 2014',
    'topics2013' => 'TOPICS 2013',
    'topics_branches' => 'TOPICS 2011',
    'topics2010' => 'TOPICS 2010',
    'app' => 'OCWサポートスタッフ募集',
    'student_testimonials' => '留学生の声',
    'summercamp' => 'トピックス特別篇',
    'nobel_interview' => '2014年ノーベル物理学賞受賞記念特別インタビュー',
    'open_campus_2015' => 'オープンキャンパス2015',
    'open_campus_2016' => 'オープンキャンパス2016',
    'open_campus_2017' => 'オープンキャンパス2017',
    'open_campus_2018' => 'オープンキャンパス2018',
    'romeclub' => 'ローマクラブ共同会長 名古屋大学名誉博士称号授与記念イベント',
    'repository' => '川邉先生（リポジトリページ）特集',
    'specialtopics' => '過去の特集ページ',
    'teacher' => '教員の方へ',
    'research_work' => '名大の研究指導',
    'research_work1-1' => '名大の研究指導・小川先生インタビュー',
    'research_work1-2' => '名大の研究指導・古村さんインタビュー',
    'research_work2' => '名大の研究指導・増田先生・三宅先生インタビュー',
    'highschool' => '高校生向けページ',
    'lab_intro' => '名大の研究室紹介',
    'research_work3-1' => '名大の研究指導・五島先生インタビュー',
    'research_work3-2' => '名大の研究指導・伊藤さんインタビュー',
    'research_work4' => '名大の研究指導・藤吉先生インタビュー',
    'topics2017' => 'TOPICS 2017',
    'topics2017_goto_1' => 'TOPICS 2017 後藤先生 OBによる講演会と番組製作に向けた話し合い',
    'topics2017_goto_2' => 'TOPICS 2017 後藤先生 番組製作に向けた話し合い',
    'topics2017_goto_3' => 'TOPICS 2017 後藤先生 番組収録',
    'topics2017_goto_4' => 'TOPICS 2017 後藤先生 学生へのインタビュー',
    'topics2017_goto_5' => 'TOPICS 2017 後藤先生 先生へのインタビュー',
    'topics2017_kajiwara_1' => 'TOPICS 2017 梶原先生 測量実習、名古屋大学の遺跡を知る、報告会',
    'topics2017_kajiwara_2' => 'TOPICS 2017 梶原先生 学生へのインタビュー',
    'topics2017_kajiwara_3' => 'TOPICS 2017 梶原先生 先生へのインタビュー',
    'topics2017_yanagihara_1' => 'TOPICS 2017 柳原先生 授業内容',
    'topics2017_yanagihara_2' => 'TOPICS 2017 柳原先生 学生へのインタビュー',
    'topics2017_yanagihara_3' => 'TOPICS 2017 柳原先生 先生へのインタビュー',
    'topics2017_miyata_1' => 'TOPICS 2017 宮田先生 コオロギの組織観察',
    'topics2017_miyata_2' => 'TOPICS 2017 宮田先生 マウス胎仔の培養と観察',
    'topics2017_miyata_3' => 'TOPICS 2017 宮田先生 学生へのインタビュー',
    'topics2017_miyata_4' => 'TOPICS 2017 宮田先生 先生へのインタビュー',
    'topics2017_sugioka_1' => 'TOPICS 2017 杉岡先生 授業内容',
    'topics2017_sugioka_2' => 'TOPICS 2017 杉岡先生 学生へのインタビュー',
    'topics2018' => 'TOPICS 2018',
    'topics2018_kukita' => 'TOPICS 2018 久木田先生インタビュー',
    'topics2018_kukita_student' => 'TOPICS 2018 学生インタビュー',
    'topics2018_takeda' => 'TOPICS 2018 武田先生インタビュー',
    'topics2018_ishiguro' => 'TOPICS 2018 石黒先生インタビュー',
    'topics2018_takeuchi' => 'TOPICS 2018 竹内先生インタビュー',
    'research_work5' => '名大の研究指導・横溝先生インタビュー',
    'open_campus' => 'オープンキャンパスまとめページ'
  ),

  'en' => array(
    'about' => 'About NU OCW',
    'welcome' => 'Welcome to NU OCW',
    'notfound' => 'Not Found',
    'search' => 'Keyword Search',
    'voice' => "International Student's Voice",
    'inquiry' => "Inquiries",
    'summercamp' => 'Special Topics',
    'mei_writing' => 'Mei-Writing',
    'voice_g30graduation' => 'G30 Graduation Ceremony',
    'nobel_interview' => '2014 Nobel Prize Winners Dr. Amano\'s Special Interview',
    'coffee_hour' => 'Coffee Hour',
    'specialtopics' => "Topics Back Numbers"
    )
);
