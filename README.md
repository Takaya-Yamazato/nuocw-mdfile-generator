nuocw-mdfile-generate
==========
新日本語版システムのための md ファイルを出力するソフトウェアです。

だいたいメドがつきましたので、github にて公開します。

参照しているデータベース（ocwpdb, ocwdb）は 
2019年11月11日 時点のものです。

Requirements
------------

本ソフトウェアは動作環境として下記を想定しています。

* UNIXシステム
* PHP 7.4.1 (cli) (built: Dec 18 2019 14:47:11) ( NTS )
* postgres (PostgreSQL) 12.1

config-example.php を config.php へコピーし、
ocwpdb および ocwdb の HOSTNAME, USERNAME, PASSWORD を設定してください。

htmlタグ から markdwonタグ への変換に

[pixel418/markdownify](https://packagist.org/packages/pixel418/markdownify)

を使っています。


composer.json

{
    "require": {
        "pixel418/markdownify": "2.*"
    }
}

と、Composer の設定ファイルを作り、

$ php -r "readfile('https://getcomposer.org/installer');" | php
$ php composer.phar install

Composer のインストール＆ライブラリのインストールしてください。



#### 基本動作

ブラウザで index.php へアクセスすると、
ocwpdb と ocwdb へ接続し、
そこからデータを取得、md ファイルを出力します。

#### nuocw-new-site で試す

/src/pages/courses/ と /src/pages/farewell/ に
ここで作成した md-file が入っています。
それぞれ、nuocw-new-site の
/src/pages/courses/ と /src/pages/farewell/ に
コーピーしてご利用ください。

画像ファイルやPDFなどは、dev2 の

/ocw/working_copies/ocw-system-public/production/files

を nuocw-new-site の

/static/

にコピーして参照できるようにしてください。

また、コピーした画像ファイルやPDFを md-file が参照できるように

gatsby-config.js に以下を追加してください。

    {
      // ocw-system の files 
      resolve: "gatsby-source-filesystem",
      options: {
        path: `${__dirname}/static/files`,
        name: "uploads",
      },
    },

ここにも gatsby-config.js をおいておきますので、参考にしてください。
