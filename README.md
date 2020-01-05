[![build status](https://gitlab.ocw.media.nagoya-u.ac.jp/ci/projects/3/status.png?ref=master)](https://gitlab.ocw.media.nagoya-u.ac.jp/ci/projects/3?ref=master)
[![build status](https://gitlab.ocw.media.nagoya-u.ac.jp/ci/projects/3/status.png?ref=staging)](https://gitlab.ocw.media.nagoya-u.ac.jp/ci/projects/3?ref=staging)
[![build status](https://gitlab.ocw.media.nagoya-u.ac.jp/ci/projects/3/status.png?ref=development)](https://gitlab.ocw.media.nagoya-u.ac.jp/ci/projects/3?ref=development)

ocw-system
==========

本ソフトウェアは[名古屋大学OCW](http://ocw.nagoya-u.jp/)に於いて, コンテンツを編集するために開発されたPHPによるWebアプリケーションです。

Requirements
------------

本ソフトウェアは動作環境として下記を想定しています。

* UNIXシステム
* PHP ~ 5.6 (>=5.6 <7.0.0)
* [Composer](https://getcomposer.org/)
* PostgreSQL ~ 9.3


Installation
------------

### Resolving Dependencies

本ソフトウェアは依存性解決にComposerを利用しています。
ソースコードディレクトリのルートにて下記コマンドを実行することで必要なパッケージがインストールされます。

    $ composer install --no-dev

必要に応じて`$ composer update`を実行し, 依存関係を最新のものに更新してください。


#### Database Initialization

本ソフトウェアで使用するデータベースを作成, 初期化します。
PostgreSQL上で本ソフトウェアが使用するユーザを作成し, そのユーザが所有するデータベースを作成してください。
データベースの定義ファイルが`sql`ディレクトリにあるので, それをPostgreSQLで実行し, データベースを初期化してください。

#### Application Configuration

`lib/ocw_sys_config.example.php`を`lib/ocw_sys_config.php`にコピーし, 実行環境に合わせて適切に設定してください。

#### Web Server Configuraion

Webサーバの設定は, 本ソフトウェアの`htdocs`ディレクトリ以下のみを公開するように設定してください。
また本ソフトウェアに含まれる`templates_c`という名称のディレクトリは, 全てWebサーバが読み書き可能となるように権限(パーミッション, セキュリティコンテキスト)を設定する必要があります。

#### DB 
