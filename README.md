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

次の手順でセットアップしてください。

### composer.json に追記する。

```json
{
	"require": {
		"pickles2/px2-px2dthelper": "dev-master"
	}
}
```

### composer を更新する。

```bash
$ composer update
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

#### PX=px2dthelper.get.all

Pickles 2 から複数の情報を一度に取得します。

```bash
$ php .px_execute.php "/?PX=px2dthelper.get.all&filter=false"
```

`filter` オプションは、 `$site->get_bros()` と `$site->get_children()` に渡されます。

#### PX=px2dthelper.check_editor_mode

コンテンツの編集モードを取得します。

```bash
$ php .px_execute.php "/target/path.html?PX=px2dthelper.check_editor_mode"
```

#### PX=px2dthelper.search_sitemap

サイトマップ中のページを検索します。

```bash
$ php .px_execute.php "/?PX=px2dthelper.search_sitemap&keyword=HOME"
```

#### PX=px2dthelper.document_modules.build_css

CSSのソースコードが返されます。

```bash
$ php .px_execute.php /?PX=px2dthelper.document_modules.build_css
```

#### PX=px2dthelper.document_modules.build_js

JavaScriptのソースコードが返されます。

```bash
$ php .px_execute.php /?PX=px2dthelper.document_modules.build_js
```

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

#### PX=px2dthelper.copy_content

コンテンツを複製します。

```bash
$ php .px_execute.php "/path/copy/to.html?PX=px2dthelper.copy_content&from=/path/copy/from.html"
```

または、

```bash
$ php .px_execute.php "/?PX=px2dthelper.copy_content&from=/path/copy/from.html&to=/path/copy/to.html"
```

#### PX=px2dthelper.change_content_editor_mode

コンテンツの編集モードを変更します。

```bash
$ php .px_execute.php "/path/to/target/page_path.html?PX=px2dthelper.change_content_editor_mode&editor_mode=html.gui"
```




## 更新履歴 - Change log

### pickles2/px2-px2dthelper 2.0.5 (2017年5月30日)

- `broccoli-receive-message` スクリプトを挿入する新しい `processor` を追加。
- PXコマンド `PX=px2dthelper.search_sitemap` を追加。
- `PX=px2dthelper.copy_content` で、 `$from` と `$to` が同じコンテンツを指す場合にコンテンツファイルが消えてしまう不具合を修正。

### pickles2/px2-px2dthelper 2.0.4 (2017年4月20日)

- PXコマンド `PX=px2dthelper.get.navigation_info` を追加。
- PXコマンド `PX=px2dthelper.get.all` に `filter` オプションを追加。
- `PX=px2dthelper.get.all` に含まれる `path_files`, `realpath_files` が、不正な値を返すことがある不具合を修正。

### pickles2/px2-px2dthelper 2.0.3 (2017年2月6日)

- PXコマンド `PX=px2dthelper.get.all` を追加。
- `paths_module_template` を絶対パスに整形してから返すようにした。
- `$site` が利用できない場合に異常終了しないようにした。

### pickles2/px2-px2dthelper 2.0.2 (2017年1月18日)

- CSS, JS のビルド結果を整形した。

### pickles2/px2-px2dthelper 2.0.1 (2016年10月17日)

- PXコマンド `PX=px2dthelper.find_page_content` を追加。
- PXコマンド `PX=px2dthelper.get.realpath_data_dir` を追加。
- PXコマンド `PX=px2dthelper.get.path_resource_dir` を追加。
- PXコマンド `PX=px2dthelper.get.custom_fields` を追加。
- PXコマンド `PX=px2dthelper.check_editor_mode` を追加。
- PXコマンド `PX=px2dthelper.init_content` を追加。
- PXコマンド `PX=px2dthelper.change_content_editor_mode` を追加。
- PXコマンド `PX=px2dthelper.check_status` を追加。
- `PX=px2dthelper.copy_content` の コピー先の指定方法を追加。 `/path/copy/to.html?PX=〜〜` のようにも指定できるようになった。

### pickles2/px2-px2dthelper 2.0.0 (2016年9月15日)

- initial release.


## ライセンス - License

MIT License


## 作者 - Author

- Tomoya Koyanagi <tomk79@gmail.com>
- website: <http://www.pxt.jp/>
- Twitter: @tomk79 <http://twitter.com/tomk79/>


## for Developer

### テスト - Test

```
$ ./vendor/phpunit/phpunit/phpunit
```

### ドキュメント出力 - phpDocumentor

```
$ php ./vendor/phpdocumentor/phpdocumentor/bin/phpdoc --title "px2-px2dthelper API Document" -d "./php/" -t "./doc/"
```
