pickles2/px2-px2dthelper
======================

Pickles 2 のプラグインです。Pickles 2 babycorn(Desktop Tool) やその他のCMSと連携させるためのAPIを提供します。


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

### Authorizer `$px->authorizer` の初期化

```php
\tomk79\pickles2\px2dthelper\authorizer::initialize($px, 'member');
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

	// config の構造に合致しない設定項目は、`symbols` の中に分類されます。
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
#### PX=px2dthelper.px2te.gpi

それぞれ、Pickles 2 Contents Editor、Pickles 2 Module Editor、Pickles 2 Theme Editor  のGPIを呼び出します。

次のオプションを指定できます。

- `appMode` : `web` (デフォルト) または `desktop`
- `data` : GPIに渡される引数。 JSON形式のテキストを base64エンコードして指定します。
- `data_filename` : `data` の代わりに、JSONをファイルに保存して受け渡しします。ファイルは `px-files/_sys/ram/data/` に置き、そのファイル名を指定してください。

#### PX=px2dthelper.px2ce.client_resources
#### PX=px2dthelper.px2me.client_resources
#### PX=px2dthelper.px2te.client_resources

それぞれ、 Pickles 2 Contents Editor、Pickles 2 Module Editor、Pickles 2 Theme Editor の、クライアントサイドで読み込むべきリソースの一覧を返します。

デフォルトでは、サーバー内部の絶対パスを返します。 `dist` パラメータにサーバー内部のディレクトリパスを渡すと、指定のディレクトリ内にコピーを作成し、そこからの相対パスを返すようになります。

#### PX=px2dthelper.custom_console_extensions
#### PX=px2dthelper.custom_console_extensions.XXXX.client_resources
#### PX=px2dthelper.custom_console_extensions.XXXX.gpi
#### PX=px2dthelper.custom_console_extensions_async_run

`$conf->plugins->px2dt->customConsoleExtensions` に登録された拡張機能へアクセスするAPIを提供します。
第3引数の `XXXX` には、 Custom Console Extensions に登録したID(添字)を指定します。省略時には、登録されている拡張機能の一覧を返します。

#### PX=px2dthelper.get.path_theme_collection_dir

#### PX=px2dthelper.get.list_unassigned_contents

#### PX=px2dthelper.get.list_gui_editor_contents

#### PX=px2dthelper.get.list_all_contents

#### PX=px2dthelper.page.add_page_info_raw

#### PX=px2dthelper.page.get_page_info_raw

#### PX=px2dthelper.page.update_page_info_raw

#### PX=px2dthelper.page.move_page_info_raw

#### PX=px2dthelper.page.delete_page_info_raw

#### PX=px2dthelper.content.delete

#### PX=px2dthelper.contents_template.get_list

#### PX=px2dthelper.sitemap.filelist

#### PX=px2dthelper.sitemap.download

#### PX=px2dthelper.sitemap.upload

#### PX=px2dthelper.sitemap.csv2xlsx

#### PX=px2dthelper.sitemap.xlsx2csv

#### PX=px2dthelper.authorizer.is_authorized.XXXX

### コマンドラインオプション

#### --role

ロール名。


## 更新履歴 - Change log

### pickles2/px2-px2dthelper v2.2.5 (リリース日未定)

- `LANG`、`lang` パラメータで、言語切替ができるようになった。
- コンテンツテンプレートが多言語化に対応できるようになった。
- コンテンツテンプレートに、ディレクトリではないファイルが直接配置された場合に、無視するようになった。

### pickles2/px2-px2dthelper v2.2.4 (2024年9月10日)

- Broccoli(ブロックエディタ)の編集時に生成するプレビューから、scriptなどいくつかの要素を除去するようになった。

### pickles2/px2-px2dthelper v2.2.3 (2024年7月21日)

- `PX=px2dthelper.get.list_gui_editor_contents` を追加。
- `PX=px2dthelper.get.list_all_contents` を追加。
- クライアントサイド `cceAgent` に `pxCmd()` を追加。

### pickles2/px2-px2dthelper v2.2.2 (2024年2月18日)

- コンテンツテンプレートのデフォルトのパスを `px-files/contents_templates/` とした。
- コンテンツテンプレートにサムネイルを含められるようになった。

### pickles2/px2-px2dthelper v2.2.1 (2023年11月13日)

- px2ce, px2me, px2te の `appearance` オプションに対応した。

### pickles2/px2-px2dthelper v2.2.0 (2023年9月24日)

- 認可機能 `$px->authorizer` を追加。
- `PX=px2dthelper.authorizer.is_authorized.XXXX` を追加。
- `PX=px2dthelper.get.list_unassigned_contents` が、ブログマップに対応した。
- `PX=px2dthelper.get.list_unassigned_contents` が、除外(ignore)されたパスをリストしないようになった。
- カスタムコンソール機能拡張に `capability` の設定を追加。

### pickles2/px2-px2dthelper v2.1.11 (2023年8月29日)

- `PX=px2dthelper.content.move` が失敗することがある不具合を修正。
- `PX=px2dthelper.content.move` によるコンテンツ移動時、被リンクの張替え処理を、除外されたディレクトリで実行しないようになった。
- ページ情報編集のバリデーション機能を改善した。
- `PX=px2dthelper.contents_template.get_list` を追加。
- `PX=px2dthelper.init_content` が、コンテンツテンプレートからの初期化に対応した。
- その他、内部コードの修正など。

### pickles2/px2-px2dthelper v2.1.10 (2023年5月1日)

- `configParser` の解析と置換で、エスケープ処理を改善し、使える文字が増えた。
- `configParser` で、 `$conf->tagline` の解析と置換ができるようになった。

### pickles2/px2-px2dthelper v2.1.9 (2023年4月22日)

- クライアントサイド `cceAgent` に `editContent()`、 `editThemeLayout()`、 `openInBrowser()` を追加。

### pickles2/px2-px2dthelper v2.1.8 (2023年3月11日)

- 重複するエラー表示をまとめるようになった。
- `data-broccoli-receive-message` は、broccoli編集時にのみ挿入されるようになった。
- Broccoli でのテーマ編集モードへの対応処理を改善した。
- その他、内部コードの修正など。

### pickles2/px2-px2dthelper v2.1.7 (2023年2月11日)

- `PX=px2dthelper.config.parse`, `PX=px2dthelper.config.update` で、 `$conf->copyright` を扱えるようになった。
- その他、内部コードの修正など。

### pickles2/px2-px2dthelper v2.1.6 (2022年12月29日)

- `PX=px2dthelper.get.list_unassigned_contents` を追加。
- px2ce, px2me, px2te, CCE で、Web SAPI からクライアント資材のパスの指定をできないようにした。
- その他、内部コードの修正など。

### pickles2/px2-px2dthelper v2.1.5 (2022年11月3日)

- `PX=px2dthelper.page.move_page_info_raw` を追加。
- `PX=px2dthelper.content.move` を追加。
- `PX=px2dthelper.page.add_page_info_raw` のバリデーションを改善。
- `PX=px2dthelper.page.update_page_info_raw` で、`path`、`content`、`logical_path` が変更された場合、影響範囲へ反映するようになった。
- `PX=px2dthelper.page.delete_page_info_raw` で、削除対象のページに子ページがある場合に、パンくずを繰り上げるようになった。
- `tomk79\pickles2\px2dthelper\utils::get_server_origin()` を追加。
- 内部コードの修正など。

### pickles2/px2-px2dthelper v2.1.4 (2022年7月20日)

- `module.js` の安定性に関する修正。
- 内部コードの修正、ファイルの整理など。

### pickles2/px2-px2dthelper v2.1.3 (2022年6月5日)

- サイトマップ変換に関する不具合の修正。

### pickles2/px2-px2dthelper v2.1.2 (2022年5月6日)

- `PX=px2dthelper.sitemap.upload` が、ファイルの保存に失敗する場合がある不具合を修正。
- `PX=px2dthelper.page.add_page_info_raw` を追加。
- `PX=px2dthelper.page.get_page_info_raw` を追加。
- `PX=px2dthelper.page.update_page_info_raw` を追加。
- `PX=px2dthelper.page.delete_page_info_raw` を追加。
- `PX=px2dthelper.content.delete` を追加。
- その他いくつかの細かい修正。

### pickles2/px2-px2dthelper v2.1.1 (2022年5月2日)

- `PX=px2dthelper.sitemap.filelist` を追加。
- `PX=px2dthelper.sitemap.download` を追加。
- `PX=px2dthelper.sitemap.upload` を追加。
- `PX=px2dthelper.sitemap.csv2xlsx` を追加。
- `PX=px2dthelper.sitemap.xlsx2csv` を追加。
- Px2CE, Px2TE, Px2ME に、 `$conf->commands->php` の設定が伝播されない不具合を修正。

### pickles2/px2-px2dthelper v2.1.0 (2022年1月8日)

- サポートするPHPのバージョンを `>=7.3.0` に変更。
- PHP 8.1 に対応した。

### pickles2/px2-px2dthelper v2.0.22 (2022年1月4日)

- `$conf->plugins->px2dt->enable_document_modules_cache` を追加。Broccoli関連リソースのビルドをキャッシュするか設定できるようになった。デフォルトは無効。
- Pickles 2 Contents Editor、 Pickles 2 Module Editor、 Pickles 2 Theme Editor の初期化に関する不具合を修正。

### pickles2/px2-px2dthelper v2.0.21 (2021年8月21日)

- 同梱のプラグインが、より直接的な表現で設定できるようになった。
- パフォーマンスに関する改善。

### pickles2/px2-px2dthelper v2.0.20 (2021年7月10日)

- 拡張field設定のフロントエンドリソースが複数ある場合に処理できない不具合を修正。
- その他の細かい内部コード修正。

### pickles2/px2-px2dthelper v2.0.19 (2021年4月24日)

- `scssphp/scssphp` への対応を追加。
- その他の細かい内部コード修正。

### pickles2/px2-px2dthelper v2.0.18 (2021年2月21日)

- ホームディレクトリ、およびテーマコレクションディレクトリが、プレビューのドキュメントルート外に置かれている場合に、正しいパスを返せない不具合を修正。
- APIが返すパスの、Windowsパスに関する環境依存を修正。

### pickles2/px2-px2dthelper v2.0.17 (2021年2月21日)

- Update: Broccoli v0.4.x
- `PX=px2dthelper.px2te.gpi`, `PX=px2dthelper.px2te.client_resources` を追加。
- カスタムコンソール機能拡張を追加。 `PX=px2dthelper.custom_console_extensions` を追加。
- `PX=px2dthelper.get.all` の返却値に `custom_console_extensions` を追加。
- `PX=px2dthelper.get.path_theme_collection_dir` を追加。
- `PX=px2dthelper.get.all` の返却値に `path_theme_collection_dir` を追加。
- `PX=px2dthelper.get.all` の返却値に `path_homedir` を追加。

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
$ wget https://phpdoc.org/phpDocumentor.phar;
$ composer run-script documentation;
```


## ライセンス - License

Copyright (c)2001-2024 Tomoya Koyanagi, and Pickles Project<br />
MIT License https://opensource.org/licenses/mit-license.php


## 作者 - Author

- Tomoya Koyanagi <tomk79@gmail.com>
- website: <https://www.pxt.jp/>
- Twitter: @tomk79 <https://twitter.com/tomk79/>
