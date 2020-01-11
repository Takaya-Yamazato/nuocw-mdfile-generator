nuocw-mdfile-generate
==========
新日本語版システムのための md ファイルを出力するソフトウェアです。

だいたいメドがつきましたので、github にて公開します。

試行錯誤をやりながら作ってますので、コードが汚いです。

データ確認のために、余計な出力がたくさん出ますが、気にしないで下さい。

Requirements
------------

本ソフトウェアは動作環境として下記を想定しています。

* UNIXシステム
* PHP 7.4.1 (cli) (built: Dec 18 2019 14:47:11) ( NTS )
* postgres (PostgreSQL) 12.1

config-example.php を config.php へコピーし、
ocwpdb および ocwdb の HOSTNAME, USERNAME, PASSWORD を設定してください。

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
\
gatsby-config.js に以下を追加してください。

    {
      // ocw-system の files 
      resolve: "gatsby-source-filesystem",
      options: {
        path: `${__dirname}/static/files`,
        name: "uploads",
      },
    },


