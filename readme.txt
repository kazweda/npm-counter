=== Npm Counter ===
Tags: counter, access counter, visitor
Requires at least: 4.8
Tested up to: 7.1
Requires PHP: 8.3
Stable tag: 1.1
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

サイトの訪問者数を数えて、ショートコードで表示するシンプルなアクセスカウンターです。

== Description ==

フロントのページが表示されたときに訪問者数を1つ増やし、`[npm_counter]` ショートコードで表示します。

* 同じ訪問者は Cookie により30分間は重複してカウントしません。
* 管理画面・Ajax・cron・REST API へのアクセス、User-Agent が空またはクローラーと判定されるアクセスはカウントしません。
* Cookie を設定できない場合（ヘッダー送信後など）はカウントしません。
* カウントはデータベース側で加算するため、同時アクセスでも抜けません。
* テキストウィジェットでもショートコードを使えます。

== Installation ==

1. `npm-counter` フォルダを `/wp-content/plugins/` にアップロードします。
2. 管理画面の「プラグイン」から有効化します。初回の有効化時にカウントの初期値として 5000 が設定されます。
3. 投稿・固定ページ・テキストウィジェットに `[npm_counter]` と書くと、訪問者数が `5,001` のような形式で表示されます。

テンプレートで表示する場合は `<?php echo do_shortcode( '[npm_counter]' ); ?>` と書きます。

== Frequently Asked Questions ==

= カウントはどこに保存されますか？ =

`wp_options` テーブルの `npm_counter` オプションに保存されます。プラグインを削除するとこのオプションも削除されます。

== Development ==

テストの実行には MySQL が必要です。Docker を使う場合の例です。

    docker run -d --name npm-counter-mysql -e MYSQL_ROOT_PASSWORD=root -e MYSQL_DATABASE=wordpress_test -p 33306:3306 mysql:8.0
    composer install
    bash bin/install-wp-tests.sh wordpress_test root root 127.0.0.1:33306 latest true
    vendor/bin/phpunit

== Changelog ==

= 1.1 =
* カウント処理をフロントの表示時だけ動くようにし、管理画面・Ajax・cron・REST API・クローラーのアクセスを除外。
* Cookie を設定できないときはカウントしないように修正。
* 同時アクセスでカウントが抜けないように修正。

= 1.0 =
* 最初のリリース。
