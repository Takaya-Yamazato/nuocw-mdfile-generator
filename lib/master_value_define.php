<?php
// マスター値の名前定義
// ソースの中にコード番号を直接含めないで定数を使うの推奨。
// とりあえず適当な接頭辞 ＆ 英語で定義したので気に入らなければ直してください。

// Contents Type Master Codes
// JAPANESE
define('CT_CONTENTS_JA', '1101'); // Contents (ja)
define('CT_INQUIRY_JA', '1221'); // 問合せ先
define('CT_CLASS_IS_FOR_JA', '1281'); // 対象者
define('CT_DATE_JA', '12a1'); // 講義日
define('CT_TIME_JA', '12a2'); // 講義時間
define('CT_PLACE_JA', '12a3'); // 講義場所
define('CT_OVERVIEW_JA', '1301'); // 授業の内容
define('CT_PICKUP_SUMMARY_JA', '1401'); // Pickup (summary) 
define('CT_PICKUP_JA', '1402'); // Pickup
define('CT_TOPICS_JA', '1404'); // Topics
define('CT_PICKUP_INFO_JA', '1483'); // Pickup (info)
define('CT_NEWS_JA', '1484'); // おしらせ
define('CT_PRESSRELEASE_JA', '1485'); // プレスリリース
define('CT_DEPT_INFO_JA', '1405');// 部局紹介
// ENGLISH
define('CT_CONTENTS_EN', '2101'); // Contents (en)
define('CT_CLASS_IS_FOR_EN', '2281'); // Class is for
define('CT_LECTURES_EN', '2282'); // Lectures
define('CT_CREDITS_EN', '2283'); // Credits
define('CT_DATE_EN', '22a1'); // Date
define('CT_TIME_EN', '22a2'); // Time
define('CT_PLACE_EN', '22a3'); // Place
define('CT_OVERVIEW_EN', '2301'); // Overview
define('CT_PICKUP_SUMMARY_EN', '2401'); // Pickup (summary) 
define('CT_PICKUP_EN', '2402'); // Pickup
define('CT_TOPICS_EN', '2404'); // Topics
define('CT_PICKUP_INFO_EN', '2483'); // Pickup (info)
define('CT_NEWS_EN', '2484'); // What's new
define('CT_PRESSRELEASE_EN', '2485'); // Press Release
define('CT_DEPT_INFO_EN', '2405');// Department Info

// Reference ID Type Master Codes
define('RIT_GLOBAL_SCOPE', '00'); // ??
define('RIT_COURSE_ID', '01'); // course_id
define('RIT_PAGE_ID', '02'); // page_id
define('RIT_DEPARTMENT_ID', '03'); // department_id
define('RIT_INSTRUCTOR_ID', '04'); // instructor_id
define('RIT_VSYLLABUS_ID', '05'); // vsyllabus_id
define('RIT_FILE_ID', '06'); // file_id
define('RIT_FILE_GROUP_ID', '07'); //file_group_id
define('RIT_CONTENTS_ID', '08'); // contents_id
define('RIT_EVENT_ID', '09'); // event_id
define('RIT_PICKUP_ID', '10'); // pickup_id
define('RIT_TOPICS_ID', '11'); // topics_id
define('RIT_NEWS_ID', '12'); // news_id
define('RIT_PRESSRELEASE_ID', '13'); // pressrelease_id

// Page Type Master Codes
define('PT_BLANK', '50'); // ??
define('PT_COURSEHOME', '51'); // 授業ホーム
define('PT_SYLLABUS', '52'); // シラバス
define('PT_CALENDAR', '53'); // スケジュール
define('PT_LECTURENOTES', '54'); // 講義ノート
define('PT_ASSIGNMENTS', '55'); // 課題
define('PT_EVALUATION', '56'); // 成績評価
define('PT_ACHIEVEMENT', '57'); // 学習成果
define('PT_RELATEDRESOURCES', '58'); // 参考資料
define('PT_TEACHINGTIPS', '59'); // 授業の工夫
define('PT_F_INDEX', '71'); // 講義ホーム（最終講義）
define('PT_F_INTRO', '72'); // 講師紹介（最終講義）
define('PT_F_RESOURCES', '73'); // 講義資料（最終講義）

// Group Master Codes
define('GT_SYSTEM_ADMINISTRATORS', '01');
define('GT_CONTENTS_ADMINISTRATORS', '11');
define('GT_CONTENTS_EDITORS', '12');
define('GT_CONTENTS_CHECKERS', '13');
define('GT_ADMINISTRATIVE_ASSISTANTS', '14');
define('GT_OCW_WG_MEMBERS', '15');
define('GT_PICKUP_EDITORS', '16');
define('GT_TOPICS_EDITORS', '17');
define('GT_COURSE_PROVIDERS', '21');
define('GT_USERS', '31');
define('GT_GUESTS', '32');
define('GT_DEVELOPERS', '33');
define('GT_DENIED_USERS', '99');

// Term Codes Master Codes
define('TCT_FIRST_SEMESTER', '1'); // 前期
define('TCT_SECOND_SEMESTER', '2'); // 後期
define('TCT_YEAR_ROUND_COURSE', '3'); // 通年
define('TCT_INTENSIVE_COURSE', '4'); // 集中講義
define('TCT_SPECIAL_COURSE', '5'); // 特別講義

// Event Result Master Codes
define('ERT_OK', '1'); // イベント成功
define('ERT_ERROR', '2'); // イベントエラー
define('ERT_WARNING', '3'); // イベント警告
define('ERT_NOTICE', '4'); //
define('ERT_INFORMATION', '5'); // 
define('ERT_DEBUG', '6'); // 

// Event Type Master Codes
define('ET_ACCOUNT_CREATED', '101'); // アカウント作成
define('ET_ACCOUNT_INFO_MODIFIED', '102'); // アカウント修正
define('ET_ACCOUNT_DELETED', '103'); // アカウント削除
define('ET_LOGIN', '104'); // ログイン
define('ET_LOGOUT', '105'); // ログアウト

define('ET_COURSE_CREATED', '201'); // コース作成
define('ET_COURSE_INFO_MODIFIED', '202'); // コース修正
define('ET_COURSE_DELETED', '203'); // コース削除
define('ET_COURSE_STATUS_CHANGED', '204'); // コースステータス変更

define('ET_PAGE_CREATED', '301'); //ページ作成
define('ET_PAGE_MODIFIED', '302'); // ページ修正
define('ET_PAGE_DELETED', '303'); // ページ削除
define('ET_PAGE_STATUS_CHANGED', '304'); // ページステータス変更

define('ET_CONTENTS_CREATED', '351'); // コンテンツ作成
define('ET_CONTENTS_MODIFIED', '352'); // コンテンツ修正
define('ET_CONTENTS_DELETED', '353'); // コンテンツ削除
define('ET_CONTENTS_STATUS_CHANGED', '354'); // コンテンツステータス変更
define('ET_CONTENTS_ROLLBACKED', '355'); // コンテンツロールバック

define('ET_INSTRUCTOR_ADDED', '401'); // 教員追加
define('ET_INSTRUCTOR_INFO_MODIFIED', '402'); // 教員情報修正
define('ET_INSTRUCTOR_DELETED', '403'); // 教員削除

define('ET_DEPARTMENT_ADDED', '501'); // 部局追加
define('ET_DEPARTMENT_INFO_MODIFIED', '502'); // 部局情報修正
define('ET_DEPARTMENT_DELETED', '503'); // 部局削除
define('ET_DEPARTMENT_STATUS_CHANGED', '504'); // 部局ステータス変更

define('ET_VSYLLABUS_ADDED', '601'); // ビジュアルシラバス追加
define('ET_VSYLLABUS_INFO_MODIFIED', '602'); // ビジュアルシラバス修正
define('ET_VSYLLABUS_DELETED', '603'); // ビジュアルシラバス削除

define('ET_FILE_UPLOADED', '701'); // ファイルアップロード
define('ET_FILE_INFO_MODIFIED', '702'); // ファイル情報修正
define('ET_FILE_DELETED', '703'); // ファイル削除
define('ET_FILE_OVERWRITTEN', '704'); //ファイル上書アップロード
define('ET_FILE_ROLLBACKED', '705'); // ファイルロールバック

define('ET_COMMENT', '801'); // コメント

define('ET_PICKUP_CREATED', '851'); // Pickup作成
define('ET_PICKUP_MODIFIED', '852'); // Pickup修正
define('ET_PICKUP_DELETED', '853'); // Pickup削除

define('ET_TOPICS_CREATED', '871'); // TOPICS作成
define('ET_TOPICS_MODIFIED', '872'); // TOPICS修正
define('ET_TOPICS_DELETED', '873'); // TOPICS削除
define('ET_TOPICS_AUTO_CREATED', '874'); // TOPICS自動生成

define('ET_PAGE_BROWSED', '901'); // ページ閲覧

// Status Code Master Codes
// この辺英語が分からないので使う場合うまい言葉に直してください。
define('SCT_NOW_SHOWING', '01'); // 公開中
define('SCT_SHOW_OK', '02'); // 公開可能
define('SCT_CHECKING', '03'); // チェック中
define('SCT_EDITING', '04'); // 編集中
define('SCT_EDITABLE', '05'); // 編集可能
define('SCT_INSUFFICIENT_DATA', '06'); // データ不足
define('SCT_NEED_TO_MODIFY', '07'); // 要修正
define('SCT_CLOSED', '08'); // 非公開
define('SCT_DELETED', '09'); // 削除

// Mime Type Master Codes
define('MT_JPG', '441'); // .jpg
define('MT_PNG', '450'); // .png
define('MT_GIF', '420'); // .gif
define('MT_BMP', '410'); // .bmp

define('MT_HTML', '620'); // .html
define('MT_HTM', '621'); // .htm
define('MT_TXT', '631'); // .txt
define('MT_CSS', '610'); // .css
define('MT_TEX', '260'); // .tex

define('MT_PDF', '130'); // .pdf
define('MT_RTF', '650'); // .rtf
define('MT_XLS', '160'); // .xls
define('MT_PPT', '170'); // .xls

define('MT_LZH', '123'); // .lzh
define('MT_ZIP', '270'); // .zip

define('MT_MP4', '323'); // .mp4
