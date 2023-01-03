<?php
return call_user_func( function(){

	// initialize
	$conf = new stdClass;

	// project
	$conf->name = 'px2-px2dthelper-test'; // サイト名
	$conf->scheme = 'https'; // スキーマ
	$conf->domain = NULL; // ドメイン
	$conf->copyright = 'Pickles Project'; // 著作権表示
	$conf->path_controot = '/'; // コンテンツルートディレクトリ

	// paths
	$conf->path_top = '/'; // トップページのパス(デフォルト "/")
	$conf->path_publish_dir = null; // パブリッシュ先ディレクトリパス
	$conf->public_cache_dir = '/caches/'; // 公開キャッシュディレクトリ

	/**
	 * リソースディレクトリ(各コンテンツに対して1:1で関連付けられる)のパス
	 *
	 * 次の部品を組み合わせて、書き換え後のパスの構成規則を指定します。
	 * - `{$dirname}` = 変換前のパスの、ディレクトリ部分
	 * - `{$filename}` = 変換前のパスの、拡張子を除いたファイル名部分
	 * - `{$ext}` = 変換前のパスの、拡張子部分
	 *
	 * または次のように、コールバックメソッドを指定します。
	 * ```
	 * $conf->path_files = function($path){
	 * 	$path = preg_replace('/.html?$/s', '_files/', $path);
	 * 	return $path;
	 * };
	 * ```
	 * コールバックメソッドには、 引数 `$path` が渡されます。
	 * これを加工して、書き換え後のパスを返してください。
	 */
	$conf->path_files = '{$dirname}/{$filename}_files/';

	$conf->contents_manifesto = '/common/contents_manifesto.ignore.php'; // Contents Manifesto のパス

	// directory index
	$conf->directory_index = array(
		'index.html'
	);


	// system
	$conf->file_default_permission = '775';
	$conf->dir_default_permission = '775';
	$conf->filesystem_encoding = 'UTF-8';
	$conf->output_encoding = 'UTF-8';
	$conf->output_eol_coding = 'lf';
	$conf->session_name = 'PXSID';
	$conf->session_expire = 1800;
	$conf->allow_pxcommands = 1; // PX Commands のウェブインターフェイスからの実行を許可
	$conf->default_timezone = 'Asia/Tokyo';

	// commands
	$conf->commands = new stdClass;
	$conf->commands->php = 'php';

	// processor
	$conf->paths_proc_type = array(
		// パスのパターン別に処理方法を設定
		//     - ignore = 対象外パス
		//     - direct = 加工せずそのまま出力する(デフォルト)
		//     - その他 = extension 名
		// パターンは先頭から検索され、はじめにマッチした設定を採用する。
		// ワイルドカードとして "*"(アスタリスク) を使用可。
		'/.htaccess' => 'ignore' ,
		'/.px_execute.php' => 'ignore' ,
		'/px-files/*' => 'ignore' ,
		'*.ignore/*' => 'ignore' ,
		'*.ignore.*' => 'ignore' ,
		'/composer.json' => 'ignore' ,
		'/composer.lock' => 'ignore' ,
		'/README.md' => 'ignore' ,
		'/vendor/*' => 'ignore' ,
		'*/.DS_Store' => 'ignore' ,
		'*/Thumbs.db' => 'ignore' ,
		'*/.svn/*' => 'ignore' ,
		'*/.git/*' => 'ignore' ,
		'*/.gitignore' => 'ignore' ,

		'*.html' => 'html' ,
		'*.htm' => 'html' ,
		'*.css' => 'css' ,
		'*.js' => 'js' ,
		'*.png' => 'direct' ,
		'*.jpg' => 'direct' ,
		'*.gif' => 'direct' ,
		'*.svg' => 'direct' ,
	);


	// -------- functions --------

	$conf->funcs = new stdClass;
	require_once(__DIR__.'/plugins/test.php');

	// funcs: Before sitemap
	$conf->funcs->before_sitemap = [
		// PX=clearcache
		'picklesFramework2\commands\clearcache::register' ,

		 // PX=config
		'picklesFramework2\commands\config::register' ,

		 // PX=phpinfo
		'picklesFramework2\commands\phpinfo::register' ,

		// sitemapExcel
		'tomk79\pickles2\sitemap_excel\pickles_sitemap_excel::exec('.json_encode(array(
			'master_format'=>'xlsx',
			'files_master_format'=>array(
				// 'timestamp_sitemap'=>'timestamp',
				// 'csv_master_sitemap'=>'csv',
				// 'xlsx_master_sitemap'=>'xlsx',
				// 'no_convert'=>'pass',
			),
		)).')' ,

		 // プラグイン関連機能のテスト
		'tomk79\plugin_sample\test::exec1' ,
	];

	// funcs: Before content
	$conf->funcs->before_content = [
		// PX=api
		'picklesFramework2\commands\api::register' ,

		// PX=publish
		'picklesFramework2\commands\publish::register' ,

		// PX=px2dthelper
		tomk79\pickles2\px2dthelper\main::register() ,

		 // プラグイン関連機能のテスト
		'tomk79\plugin_sample\test::exec2' ,
	];


	// processor
	$conf->funcs->processor = new stdClass;

	$conf->funcs->processor->html = [
		// ページ内目次を自動生成する
		'picklesFramework2\processors\autoindex\autoindex::exec' ,

		// テーマ
		'theme'=>'tomk79\pickles2\multitheme\theme::exec('.json_encode([
			'param_theme_switch'=>'THEME',
			'cookie_theme_switch'=>'THEME',
			'path_theme_collection'=>'./px-files/themes/',
			'attr_bowl_name_by'=>'data-contents-area',
			'default_theme_id' => 'pickles'
		]).')' ,

		 // プラグイン関連機能のテスト
		'tomk79\plugin_sample\test::exec3('.json_encode(array(
			'ext'=>'html',
		)).')' ,

		// Apache互換のSSIの記述を解決する
		'picklesFramework2\processors\ssi\ssi::exec' ,

		// // output_encoding, output_eol_coding の設定に従ってエンコード変換する。
		'picklesFramework2\processors\encodingconverter\encodingconverter::exec' ,
	];

	$conf->funcs->processor->css = [
		// // output_encoding, output_eol_coding の設定に従ってエンコード変換する。
		'picklesFramework2\processors\encodingconverter\encodingconverter::exec' ,

		 // プラグイン関連機能のテスト
		 // 先頭にバックスラッシュをおいた場合
		 // 連続するバックスラッシュを含んだ場合
		'\\\\\\\\tomk79\\\\\\\\plugin_sample\test::exec3('.json_encode(array(
			'ext'=>'css',
		)).')' ,

	];

	$conf->funcs->processor->js = [
		// // output_encoding, output_eol_coding の設定に従ってエンコード変換する。
		'picklesFramework2\processors\encodingconverter\encodingconverter::exec' ,
	];

	$conf->funcs->processor->md = [
		// Markdown文法を処理する
		'picklesFramework2\processors\md\ext::exec' ,

		// html の処理を追加
		$conf->funcs->processor->html ,

		 // プラグイン関連機能のテスト
		'tomk79\plugin_sample\test::exec3('.json_encode(array(
			'ext'=>'md',
			'test_value'=>'test()',
		)).')' ,

	];

	$conf->funcs->processor->scss = [
		// SCSS文法を処理する
		'picklesFramework2\processors\scss\ext::exec' ,

		// css の処理を追加
		$conf->funcs->processor->css ,
	];


	// funcs: Before output
	$conf->funcs->before_output = [
		 // プラグイン関連機能のテスト
		'tomk79\plugin_sample\test::exec3('.json_encode(array(
		)).')' ,
	];


	// Custom Console Extensions
	require_once(__DIR__.'/customConsoleExtensions/customConsoleExtensionsTest0001/main.php');



	// -------- PHP Setting --------

	/**
	 * `memory_limit`
	 *
	 * PHPのメモリの使用量の上限を設定します。
	 * 正の整数値で上限値(byte)を与えます。
	 *
	 *     例: 1000000 (1,000,000 bytes)
	 *     例: "128K" (128 kilo bytes)
	 *     例: "128M" (128 mega bytes)
	 *
	 * -1 を与えた場合、無限(システムリソースの上限まで)に設定されます。
	 * サイトマップやコンテンツなどで、容量の大きなデータを扱う場合に調整してください。
	 */
	// @ini_set( 'memory_limit' , -1 );

	/**
	 * `display_errors`, `error_reporting`
	 *
	 * エラーを標準出力するための設定です。
	 *
	 * PHPの設定によっては、エラーが発生しても表示されない場合があります。
	 * もしも、「なんか挙動がおかしいな？」と感じたら、
	 * 必要に応じてこれらのコメントを外し、エラー出力を有効にしてみてください。
	 *
	 * エラーメッセージは問題解決の助けになります。
	 */
	@ini_set('display_errors', 1);
	@ini_set('error_reporting', E_ALL);


	return $conf;
} );
