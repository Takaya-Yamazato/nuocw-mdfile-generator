﻿nuocw-mdfile-generate
==========
新日本語版システムのための md ファイルを出力するソフトウェアです。
だいたいメドがつきましたので、github にて公開します。

試行錯誤をやりながら作ってますので、コードが汚いです。
データ確認のために、余計な出力がたくさん出ますが、
まだ気にしないで下さい。

Requirements
------------

本ソフトウェアは動作環境として下記を想定しています。

* UNIXシステム
* PHP 7.4.1 (cli) (built: Dec 18 2019 14:47:11) ( NTS )
* postgres (PostgreSQL) 12.1

config-example.php を config.php へコピーし、
ocwpdb および ocwdb の HOSTNAME, USERNAME, PASSWORD を設定します。

#### 基本動作

ブラウザで index.php へアクセスすると、
ocwpdb と ocwdb へ接続し、
そこからデータを取得、md ファイルを出力します。

