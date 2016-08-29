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

	// funcs: Before sitemap
	$conf->funcs->before_sitemap = [
		// PX=px2dthelper
		'tomk79\pickles2\px2dthelper\main::register'
	];

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

CSVやExcel形式で作られた表を元に、HTMLのテーブル要素を生成して出力します。

```bash
$ php .px_execute.php /?PX=px2dthelper.convert_table_excel2html
```

#### PX=px2dthelper.version

px2-px2dthelper のバージョン番号を取得します。

```bash
$ php .px_execute.php /?PX=px2dthelper.version
```




## 更新履歴 - Change log

### pickles2/px2-px2dthelper 2.0.0 (2016年??月??日)

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
