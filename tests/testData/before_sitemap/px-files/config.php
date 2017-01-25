<?php
return call_user_func( function(){

	// initialize
	$conf = new stdClass;

	// project
	$conf->name = 'px2-px2dthelper-test'; // サイト名
	$conf->domain = null; // ドメイン
	$conf->path_controot = '/'; // コンテンツルートディレクトリ

	// paths
	$conf->path_top = '/'; // トップページのパス(デフォルト "/")
	$conf->path_publish_dir = null; // パブリッシュ先ディレクトリパス
	$conf->public_cache_dir = '/caches/'; // 公開キャッシュディレクトリ
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
		//	 - ignore = 対象外パス
		//	 - direct = 加工せずそのまま出力する(デフォルト)
		//	 - その他 = extension 名
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

	// funcs: Before sitemap
	$conf->funcs->before_sitemap = [
		// PX=clearcache
		'picklesFramework2\commands\clearcache::register' ,

		 // PX=config
		'picklesFramework2\commands\config::register' ,

		 // PX=phpinfo
		'picklesFramework2\commands\phpinfo::register' ,

		// PX=api
		'picklesFramework2\commands\api::register' ,

		// PX=px2dthelper
		'tomk79\pickles2\px2dthelper\main::register'
	];

	// funcs: Before content
	$conf->funcs->before_content = [
		// PX=publish
		'picklesFramework2\commands\publish::register' ,
	];


	// processor
	$conf->funcs->processor = new stdClass;

	$conf->funcs->processor->html = [
		// ページ内目次を自動生成する
		'picklesFramework2\processors\autoindex\autoindex::exec' ,

		// Apache互換のSSIの記述を解決する
		'picklesFramework2\processors\ssi\ssi::exec' ,
	];

	$conf->funcs->processor->css = [
	];

	$conf->funcs->processor->js = [
	];

	$conf->funcs->processor->md = [
		// Markdown文法を処理する
		'picklesFramework2\processors\md\ext::exec' ,

		// html の処理を追加
		$conf->funcs->processor->html ,
	];

	$conf->funcs->processor->scss = [
		// SCSS文法を処理する
		'picklesFramework2\processors\scss\ext::exec' ,

		// css の処理を追加
		$conf->funcs->processor->css ,
	];


	// funcs: Before output
	$conf->funcs->before_output = [
	];



	// -------- config for Plugins. --------
	// その他のプラグインに対する設定を行います。
	$conf->plugins = new stdClass;

	/** config for Pickles 2 Desktop Tool. */
	$conf->plugins->px2dt = new stdClass;

	/** broccoliモジュールセットの登録 */
	$conf->plugins->px2dt->paths_module_template = [
		"PlainHTMLElements" => "../../../vendor/tomk79/px2-mod-plain-html-elements/modules/",
		"Modules1" => "./px-files/resources/module_templates_1/",
		"Modules2" => "./px-files/resources/module_templates_2/",
		"FESS" => "../../../vendor/tomk79/px2-fess/modules/"
	];

	/** コンテンツエリアを識別するセレクタ(複数の要素がマッチしてもよい) */
	$conf->plugins->px2dt->contents_area_selector = '[data-contents-area]';

	/** コンテンツエリアのbowl名を指定する属性名 */
	$conf->plugins->px2dt->contents_bowl_name_by = 'data-contents-area';

	/** パブリッシュのパターンを登録 */
	$conf->plugins->px2dt->publish_patterns = array(
		array(
			'label'=>'すべて',
			'paths_region'=> array('/'),
			'paths_ignore'=> array(),
			'keep_cache'=>false
		),
		array(
			'label'=>'リソース類',
			'paths_region'=> array('/caches/','/common/'),
			'paths_ignore'=> array(),
			'keep_cache'=>true
		),
		array(
			'label'=>'すべて(commonを除く)',
			'paths_region'=> array('/'),
			'paths_ignore'=> array('/common/'),
			'keep_cache'=>false
		),
	);

	/** config for GUI Editor. */
	$conf->plugins->px2dt->guieditor = new stdClass;

	/** GUI編集データディレクトリ */
	$conf->plugins->px2dt->guieditor->path_data_dir = '/data_dir/{$dirname}/{$filename}.files/guieditor.ignore/';

	/** GUI編集リソース出力先ディレクトリ */
	$conf->plugins->px2dt->guieditor->path_resource_dir = '/resources/{$dirname}/{$filename}.files/resources/';

	/** カスタムフィールド */
	$conf->plugins->px2dt->guieditor->custom_fields = array(
		'projectCustom1'=>array(
			'backend'=>array(
				'require' => './px-files/broccoli-fields/projectCustom1/backend.js'
			),
			'frontend'=>array(
				'file' => './px-files/broccoli-fields/projectCustom1/frontend.js',
				'function' => 'window.broccoliFieldProjectCustom1'
			),
		),
		'projectCustom2'=>array(
			'backend'=>array(
				'require' => './px-files/broccoli-fields/projectCustom2/backend.js'
			),
			'frontend'=>array(
				'file' => './px-files/broccoli-fields/projectCustom2/frontend.js',
				'function' => 'window.broccoliFieldProjectCustom2'
			),
		),
	);



	// -------- PHP Setting --------

	/**
	 * `memory_limit`
	 *
	 * PHPのメモリの使用量の上限を設定します。
	 * 正の整数値で上限値(byte)を与えます。
	 *
	 *	 例: 1000000 (1,000,000 bytes)
	 *	 例: "128K" (128 kilo bytes)
	 *	 例: "128M" (128 mega bytes)
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
