pickles2/px2-px2dthelper
======================

<table class="def">
  <thead>
	<tr>
	  <th></th>
	  <th>Linux</th>
	  <th>Windows</th>
	</tr>
  </thead>
  <tbody>
	<tr>
	  <th>master</th>
	  <td align="center">
		<a href="https://travis-ci.org/pickles2/px2-px2dthelper"><img src="https://secure.travis-ci.org/pickles2/px2-px2dthelper.svg?branch=master"></a>
	  </td>
	  <td align="center">
		<a href="https://ci.appveyor.com/project/tomk79/px2-px2dthelper"><img src="https://ci.appveyor.com/api/projects/status/70winlbbg8sway58/branch/master?svg=true"></a>
	  </td>
	</tr>
	<tr>
	  <th>develop</th>
	  <td align="center">
		<a href="https://travis-ci.org/pickles2/px2-px2dthelper"><img src="https://secure.travis-ci.org/pickles2/px2-px2dthelper.svg?branch=develop"></a>
	  </td>
	  <td align="center">
		<a href="https://ci.appveyor.com/project/tomk79/px2-px2dthelper"><img src="https://ci.appveyor.com/api/projects/status/70winlbbg8sway58/branch/develop?svg=true"></a>
	  </td>
	</tr>
  </tbody>
</table>


Pickles 2 用のプラグインです。Pickles 2 Desktop Tool と連携させるためのAPIを提供します。

## インストール - Install

次の手順でインストールしてください。

### Composer パッケージを読み込む。

```bash
$ composer require pickles2/px2-px2dthelper
```

### Pickles2 のコンフィグに追記する。
```php
<?php
return call_user_func( function(){

	/* 中略 */

	// funcs: Before content
	$conf->funcs->before_content = array(
		// PX=px2dthelper
		'tomk79\pickles2\px2dthelper\main::register'
	);

	/* 中略 */

	// processor
	$conf->funcs->processor->html = array(
		// broccoli-receive-message スクリプトを挿入
		// (Optional)
		'tomk79\pickles2\px2dthelper\broccoli_receive_message::apply('.json_encode( array(
			// 許可する接続元を指定
			'enabled_origin'=>array(
				'http://127.0.0.1:8080',
				'http://127.0.0.1:8081',
				'http://127.0.0.1:8082',
			)
		) ).')'
	);

	/* 中略 */

	return $conf;
} );
```

## API

### モジュールの CSS をビルドする

```php
/* CSSファイルに下記を記述 */
<?php
// CSSのソースコードが返されます。
// このコードには、画像などのリソースもdataスキーマ化した状態で含められます。
print (new \tomk79\pickles2\px2dthelper\main($px))->document_modules()->build_css();
?>
```

### モジュールの JavaScript をビルドする

```php
// JavaScriptファイルに下記を記述
<?php
// JavaScriptのソースコードが返されます。
// スクリプト内から画像などのリソースを呼び出している場合、
// このメソッドには、リンクを解決するなどの機能はありません。
// 予め、出力後のパスを起点にパスが解決できるように作成してください。
print (new \tomk79\pickles2\px2dthelper\main($px))->document_modules()->build_js();
?>
```

### HTMLにCSSとJavaScriptをロードする

```php
<!DOCTYPE html>
<html>
<head>
<title>Page Title</title>
<?php
// style要素、および script要素 が出力されます。
print (new \tomk79\pickles2\px2dthelper\main($px))->document_modules()->load();
?>
</head>
<body>
	<h1>Page Title</h1>
	<!-- コンテンツ -->
</body>
</html>
```

### PXコマンド

#### PX=px2dthelper.version

`px2-px2dthelper` のバージョン番号を取得します。

```bash
$ php .px_execute.php /?PX=px2dthelper.version
```

#### PX=px2dthelper.check_status

`px2-px2dthelper` の状態情報を取得します。

```bash
$ php .px_execute.php /?PX=px2dthelper.check_status
```

#### PX=px2dthelper.find_page_content

ページのコンテンツファイルを探します。

```bash
$ php .px_execute.php /path/find/content.html?PX=px2dthelper.find_page_content
```

#### PX=px2dthelper.get.realpath_data_dir

`$conf->plugins->px2dt->guieditor->path_data_dir` の解決された内部絶対パスを取得します。

#### PX=px2dthelper.get.path_resource_dir

`$conf->plugins->px2dt->guieditor->path_resource_dir` の解決されたパスを取得します。

#### PX=px2dthelper.get.custom_fields

`$conf->plugins->px2dt->guieditor->custom_fields` の値を取得します。

#### PX=px2dthelper.get.navigation_info

ナビゲーションシステムを生成するために必要な情報をまとめて取得します。

```bash
$ php .px_execute.php "/?PX=px2dthelper.get.navigation_info&filter=false"
```

`filter` オプションは、 `$site->get_bros()` と `$site->get_children()` に渡されます。

#### PX=px2dthelper.get.realpath_theme_collection_dir

テーマコレクションディレクトリのパスを取得します。。

```bash
$ php .px_execute.php "/?PX=px2dthelper.get.realpath_theme_collection_dir"
```

#### PX=px2dthelper.get.all

Pickles 2 から複数の情報を一度に取得します。

```bash
$ php .px_execute.php "/?PX=px2dthelper.get.all&filter=false&path=/index.html"
```

`filter` オプションは、 `$site->get_bros()` と `$site->get_children()` に渡されます。

#### PX=px2dthelper.check_editor_mode

コンテンツの編集モードを取得します。

```bash
$ php .px_execute.php "/?PX=px2dthelper.check_editor_mode&path=/target/path.html"
```

#### PX=px2dthelper.search_sitemap

サイトマップ中のページを検索します。

`limit` オプションは、検索結果の最大件数を指定します。デフォルトは 200件です。

```bash
$ php .px_execute.php "/?PX=px2dthelper.search_sitemap&keyword=HOME&limit=10"
```

#### PX=px2dthelper.sitemap.create

新規サイトマップファイルを作成します。 パラメータ `filename` に、作成するサイトマップのファイル名(拡張子は含まない)を指定します。

```bash
$ php .px_execute.php "/?PX=px2dthelper.sitemap.create&filename=foobar"
```

#### PX=px2dthelper.sitemap.delete

サイトマップファイルを削除します。 パラメータ `filename` に、削除するサイトマップのファイル名(拡張子は含まない)を指定します。

```bash
$ php .px_execute.php "/?PX=px2dthelper.sitemap.delete&filename=foobar"
```

#### PX=px2dthelper.document_modules.build_css

CSSのソースコードが返されます。

```bash
$ php .px_execute.php /?PX=px2dthelper.document_modules.build_css
```

加えて、パラメータ `theme_id` を付与すると、テーマが定義するCSSのコードを返します。

#### PX=px2dthelper.document_modules.build_js

JavaScriptのソースコードが返されます。

```bash
$ php .px_execute.php /?PX=px2dthelper.document_modules.build_js
```

加えて、パラメータ `theme_id` を付与すると、テーマが定義するJavaScriptのコードを返します。

#### PX=px2dthelper.document_modules.load

CSSとJavaScriptをロードするHTMLソースコードが返されます。

```bash
$ php .px_execute.php /?PX=px2dthelper.document_modules.load
```

#### PX=px2dthelper.convert_table_excel2html

CSV や Excel形式 で作られた表を元に、HTMLのテーブル要素を生成して出力します。
パラメータ `path` には、 `*.csv`, `*.xls`, `*.xlsx` を指定できます。

```bash
$ php .px_execute.php "/?PX=px2dthelper.convert_table_excel2html&path=/path/to/sourcedata.xlsx"
```

#### PX=px2dthelper.init_content

コンテンツを初期化します。

```bash
$ php .px_execute.php "/path/init/content.html?PX=px2dthelper.init_content&editor_mode=html.gui"
```

v2.0.12 以降、このAPIは、すでにコンテンツが存在する場合には、上書きせずにエラーを返すようになりました。
`force` オプションが付加された場合は、 強制的に上書きします。

v2.0.11 とそれ以前のバージョンでは、既存のコンテンツを上書きして初期化するのがデフォルトの挙動でした。

```bash
$ # forceオプションを付加した例
$ php .px_execute.php "/path/init/content.html?PX=px2dthelper.init_content&editor_mode=html.gui&force=1"
```

#### PX=px2dthelper.config.parse

設定ファイル(`config.php`)を解析し、解析できた値の一覧を返します。

#### PX=px2dthelper.config.update

設定ファイル(`config.php`)を解析し、解析できた値のうち任意の値を変更して上書きします。

```bash
$ php .px_execute.php "/path/to/target/page_path.html?PX=px2dthelper.config.update&base64_json=xxxxxxxxxxxx"
```

変更したい値は、 `json` (JSON形式の文字列) または `base64_json` (JSON形式の文字列を Base64に変換した文字列) のいずれかにセットして渡します。

```php
$base64_json = base64_encode( json_encode( array(
	// config の項目で、上書きしたい情報をセットします。
	'values' => array(
		'name' => 'New Site Name',
	),

	// config の物理構造に合致しない設定項目は、`symbols` の中に分類されます。
	'symbols' => array(
		'theme_id' => 'new_theme_id', 
	),
) ) );
```

#### PX=px2dthelper.copy_content

コンテンツを複製します。

```bash
$ php .px_execute.php "/path/copy/to.html?PX=px2dthelper.copy_content&from=/path/copy/from.html"
```

または、

```bash
$ php .px_execute.php "/?PX=px2dthelper.copy_content&from=/path/copy/from.html&to=/path/copy/to.html"
```

v2.0.12 以降、このAPIは、すでにコンテンツが存在する場合には、上書きせずにエラーを返すようになりました。
`force` オプションが付加された場合は、 強制的に上書きします。

v2.0.11 とそれ以前のバージョンでは、既存のコンテンツを上書きして初期化するのがデフォルトの挙動でした。

```bash
$ # forceオプションを付加した例
$ php .px_execute.php "/?PX=px2dthelper.copy_content&from=/path/copy/from.html&to=/path/copy/to.html&force=1"
```


#### PX=px2dthelper.change_content_editor_mode

コンテンツの編集モードを変更します。

```bash
$ php .px_execute.php "/path/to/target/page_path.html?PX=px2dthelper.change_content_editor_mode&editor_mode=html.gui"
```

#### PX=px2dthelper.plugins.get_plugin_options

プラグインオプションを取得します。

```bash
$ php .px_execute.php "/path/to/target/page_path.html?PX=px2dthelper.plugins.get_plugin_options&func_div=processor.html&plugin_name=namespace\\classname::funcname"
```

#### PX=px2dthelper.publish_single_page

指定されたページを単体でパブリッシュします。
ページ固有のリソースディレクトリが同時にパブリッシュされます。

```bash
$ php .px_execute.php "/path/to/target/page_path.html?PX=px2dthelper.publish_single_page"
```

#### PX=px2dthelper.packages.get_path_composer_root_dir

`composer.json` が置かれているディレクトリのパスを取得する。

#### PX=px2dthelper.packages.get_path_npm_root_dir

`package.json` が置かれているディレクトリのパスを取得する。

#### PX=px2dthelper.packages.get_package_list

パッケージの一覧を取得する。

```bash
$ php .px_execute.php "/path/to/target/page_path.html?PX=px2dthelper.packages.get_package_list"
```

#### PX=px2dthelper.px2ce.gpi
#### PX=px2dthelper.px2me.gpi

それぞれ、Pickles 2 Contents Editor、Pickles 2 Module Editor のGPIを呼び出します。

次のオプションを指定できます。

- `appMode` : `web` (デフォルト) または `desktop`
- `data` : GPIに渡される引数。 JSON形式のテキストを base64エンコードして指定します。
- `data_filename` : `data` の代わりに、JSONをファイルに保存して受け渡しします。ファイルは `px-files/_sys/ram/data/` に置き、そのファイル名を指定してください。

#### PX=px2dthelper.px2ce.client_resources
#### PX=px2dthelper.px2me.client_resources

それぞれ、 Pickles 2 Contents Editor、Pickles 2 Module Editor の、クライアントサイドで読み込むべきリソースの一覧を返します。

デフォルトでは、サーバー内部の絶対パスを返します。 `dist` パラメータにサーバー内部のディレクトリパスを渡すと、指定のディレクトリ内にコピーを作成し、そこからの相対パスを返すようになります。


## 更新履歴 - Change log

### pickles2/px2-px2dthelper v2.0.16 (2020年10月17日)

- Firefox で、 Broccoliエディタの初期化が完了できない問題に対する修正。
- その他の細かい内部コード修正。

### pickles2/px2-px2dthelper v2.0.15 (2020年6月21日)

- 外部依存パッケージのバージョンを更新。

### pickles2/px2-px2dthelper v2.0.14 (2020年1月2日)

- PHP 7.4 に対応した。

### pickles2/px2-px2dthelper v2.0.13 (2019年9月4日)

- Broccoli編集画面を停止させる外部のスクリプトを無効化するようにした。

### pickles2/px2-px2dthelper v2.0.12 (2019年6月8日)

- PXコマンド `PX=px2dthelper.config.parse` と `PX=px2dthelper.config.update` を追加。
- PXコマンド `PX=px2dthelper.sitemap.create` と `PX=px2dthelper.sitemap.delete` を追加。
- `PX=px2dthelper.init_content`、`PX=px2dthelper.copy_content` は、コンテンツがすでに存在する場合には、上書きせずエラーを出すように変更された。そのかわり、 `force` オプションを追加し、強制的に上書きできるようにした。
- `PX=px2dthelper.get.all` の結果に `page_originated_csv` が追加された。(`pickles2/px-fw-2.x v2.0.40` 以上が必要)
- `PX=px2dthelper.search_sitemap` に `limit` オプションを追加。 デフォルトは 200件が上限となるようになった。
- `document_modules` のビルド結果をキャッシュするようになった。

### pickles2/px2-px2dthelper v2.0.11 (2018年11月8日)

- リソースパスに関する設定(`$conf->path_files`, `$conf->plugins->px2dt->guieditor->path_resource_dir`, `$conf->plugins->px2dt->guieditor->path_data_dir`)にコールバック関数を使用できない問題を修正。

### pickles2/px2-px2dthelper v2.0.10 (2018年10月26日)

- Windows環境で、 `PX=px2dthelper.publish_single_page` を実行時に、リソースディレクトリがパブリッシュ対象に含まれない不具合を修正。

### pickles2/px2-px2dthelper v2.0.9 (2018年9月25日)

- PXコマンド `PX=px2dthelper.publish_single_page` を追加。
- Pickles 2 Contents Editor, および Pickles 2 Module Editor のAPIで、APIを正常に呼び出せない場合がある問題を修正。

### pickles2/px2-px2dthelper v2.0.8 (2018年8月16日)

- `broccoli-html-editor` のAPIを追加。
- Pickles 2 Contents Editor のAPIを追加。
- Pickles 2 Module Editor のAPIを追加。

### pickles2/px2-px2dthelper v2.0.7 (2018年2月28日)

- 依存ライブラリ `michelf/php-markdown`, `leafo/scssphp` のバージョン制約を緩和。 (`pickles2/px-fw-2.x` の更新に合わせられるように)
- `$conf->px2dtconfig` が連想配列で設定されている場合に、正規化処理が適切に反映されない不具合を修正。

### pickles2/px2-px2dthelper v2.0.6 (2017年9月14日)

- PXコマンド `PX=px2dthelper.plugins.get_plugin_options` を追加。
- PXコマンド `PX=px2dthelper.packages.get_package_list` を追加。
- PXコマンド `PX=px2dthelper.packages.get_path_composer_root_dir` を追加。
- PXコマンド `PX=px2dthelper.packages.get_path_npm_root_dir` を追加。
- PXコマンド `PX=px2dthelper.get.realpath_theme_collection_dir` を追加。
- PXコマンド `PX=px2dthelper.get.all`, `PX=px2dthelper.check_editor_mode` に `path` オプションを追加。
- PXコマンド `PX=px2dthelper.get.all` の結果に `path_type`, `realpath_theme_collection_dir`, `packages->path_composer_root_dir`, `packages->path_npm_root_dir`, `packages->package_list` を追加。
- PXコマンド `PX=px2dthelper.get.all` で、`path` オプションに id を指定してエイリアスページの情報を取得できるようになった。
- `$conf->plugins->px2dt->path_module_templates_dir` 設定に対応。
- PXコマンド `PX=px2dthelper.document_modules.build_css` と `PX=px2dthelper.document_modules.build_js` が、テーマのモジュールに対応。

### pickles2/px2-px2dthelper v2.0.5 (2017年5月30日)

- `broccoli-receive-message` スクリプトを挿入する新しい `processor` を追加。
- PXコマンド `PX=px2dthelper.search_sitemap` を追加。
- `PX=px2dthelper.copy_content` で、 `$from` と `$to` が同じコンテンツを指す場合にコンテンツファイルが消えてしまう不具合を修正。

### pickles2/px2-px2dthelper v2.0.4 (2017年4月20日)

- PXコマンド `PX=px2dthelper.get.navigation_info` を追加。
- PXコマンド `PX=px2dthelper.get.all` に `filter` オプションを追加。
- `PX=px2dthelper.get.all` に含まれる `path_files`, `realpath_files` が、不正な値を返すことがある不具合を修正。

### pickles2/px2-px2dthelper v2.0.3 (2017年2月6日)

- PXコマンド `PX=px2dthelper.get.all` を追加。
- `paths_module_template` を絶対パスに整形してから返すようにした。
- `$site` が利用できない場合に異常終了しないようにした。

### pickles2/px2-px2dthelper v2.0.2 (2017年1月18日)

- CSS, JS のビルド結果を整形した。

### pickles2/px2-px2dthelper v2.0.1 (2016年10月17日)

- PXコマンド `PX=px2dthelper.find_page_content` を追加。
- PXコマンド `PX=px2dthelper.get.realpath_data_dir` を追加。
- PXコマンド `PX=px2dthelper.get.path_resource_dir` を追加。
- PXコマンド `PX=px2dthelper.get.custom_fields` を追加。
- PXコマンド `PX=px2dthelper.check_editor_mode` を追加。
- PXコマンド `PX=px2dthelper.init_content` を追加。
- PXコマンド `PX=px2dthelper.change_content_editor_mode` を追加。
- PXコマンド `PX=px2dthelper.check_status` を追加。
- `PX=px2dthelper.copy_content` の コピー先の指定方法を追加。 `/path/copy/to.html?PX=〜〜` のようにも指定できるようになった。

### pickles2/px2-px2dthelper v2.0.0 (2016年9月15日)

- initial release.


## for Developer

### テスト - Test

```
$ ./vendor/phpunit/phpunit/phpunit
```

### ドキュメント出力 - phpDocumentor

```
$ composer run-script documentation
```


## ライセンス - License

Copyright (c)2001-2020 Tomoya Koyanagi, and Pickles 2 Project<br />
MIT License https://opensource.org/licenses/mit-license.php


## 作者 - Author

- Tomoya Koyanagi <tomk79@gmail.com>
- website: <https://www.pxt.jp/>
- Twitter: @tomk79 <https://twitter.com/tomk79/>
