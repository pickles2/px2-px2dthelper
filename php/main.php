<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper;

/**
 * main.php
 */
class main{

	/** Picklesオブジェクト */
	private $px;

	/** PXコマンド名 */
	private $command = array();

	/** px2dtconfig */
	private $px2dtconfig;

	/**
	 * entry
	 *
	 * @param object $px Picklesオブジェクト
	 */
	static public function register($px){
		$px->pxcmd()->register('px2dthelper', function($px){
			(new self( $px ))->kick();
			exit;
		}, true);
	}

	/**
	 * px2-px2dthelper のバージョン情報を取得する。
	 *
	 * px2-px2dthelper のバージョン番号はこのメソッドにハードコーディングされます。
	 *
	 * バージョン番号発行の規則は、 Semantic Versioning 2.0.0 仕様に従います。
	 * - [Semantic Versioning(英語原文)](http://semver.org/)
	 * - [セマンティック バージョニング(日本語)](http://semver.org/lang/ja/)
	 *
	 * *[ナイトリービルド]*<br />
	 * バージョン番号が振られていない、開発途中のリビジョンを、ナイトリービルドと呼びます。<br />
	 * ナイトリービルドの場合、バージョン番号は、次のリリースが予定されているバージョン番号に、
	 * ビルドメタデータ `+nb` を付加します。
	 * 通常は、プレリリース記号 `alpha` または `beta` を伴うようにします。
	 * - 例：1.0.0-beta.12+nb (=1.0.0-beta.12リリース前のナイトリービルド)
	 *
	 * @return string バージョン番号を示す文字列
	 */
	public function get_version(){
		return '2.0.10';
	}


	/**
	 * Constructor
	 *
	 * @param object $px $pxオブジェクト
	 */
	public function __construct( $px ){
		$this->px = $px;
		$this->px2dtconfig = json_decode('{}');
		if( @is_object($this->px->conf()->plugins->px2dt) ){
			$this->px2dtconfig = $this->px->conf()->plugins->px2dt;
		}elseif( is_file( $this->px->get_path_homedir().'px2dtconfig.json' ) ){
			$this->px2dtconfig = json_decode( $this->px->fs()->read_file( $this->px->get_path_homedir().'px2dtconfig.json' ) );
		}
		$this->px2dtconfig = json_decode( json_encode($this->px2dtconfig) ); // 連想配列で設定されている場合を考慮して、オブジェクト形式に変換する

		// broccoliモジュールパッケージのパスを整形
		@$this->px2dtconfig->paths_module_template = @$this->px2dtconfig->paths_module_template;
		if( is_object($this->px2dtconfig->paths_module_template) ){
			foreach( $this->px2dtconfig->paths_module_template as $key=>$val ){
				// ↓ スラッシュで始まり スラッシュで終わる 絶対パスに置き換える。
				// ↓ WindowsでもUNIXスタイルに正規化する。(ボリュームラベルは削除され、バックスラッシュはスラッシュに置き換えられる)
				$this->px2dtconfig->paths_module_template->{$key} = $this->px->fs()->normalize_path( $this->px->fs()->get_realpath( $this->px2dtconfig->paths_module_template->{$key}.'/' ) );
			}
		}

	}

	/**
	 * セットアップ状態をチェックする。
	 * @return object 状態情報
	 */
	public function check_status(){
		$rtn = json_decode('{}');
		$rtn->version = $this->get_version();
		$rtn->is_sitemap_loaded = ($this->px->site() ? true : false);
		return $rtn;
	}

	/**
	 * px2dtconfigを取得する。
	 */
	public function get_px2dtconfig(){
		return $this->px2dtconfig;
	}

	/**
	 * ページのコンテンツファイルを探す
	 *
	 * @param string $page_path ページのパス。
	 */
	public function find_page_content( $page_path = null ){
		// execute Content
		$path_content = $this->px->req()->get_request_file_path();
		if( strlen($page_path) ){
			$path_content = $page_path;
		}
		$ext = $this->px->get_path_proc_type( $this->px->req()->get_request_file_path() );
		if($ext !== 'direct' && $ext !== 'pass'){
			if( $this->px->site() ){
				$current_page_info = $this->px->site()->get_page_info($page_path);
				$tmp_path_content = @$current_page_info['content'];
				if( strlen( $tmp_path_content ) ){
					$path_content = $tmp_path_content;
				}
				unset($current_page_info, $tmp_path_content);
			}
		}

		foreach( array_keys( get_object_vars( $this->px->conf()->funcs->processor ) ) as $tmp_ext ){
			if( $this->px->fs()->is_file( './'.$path_content.'.'.$tmp_ext ) ){
				$ext = $tmp_ext;
				$path_content .= '.'.$tmp_ext;
				break;
			}
		}

		return $path_content;
	}

	/**
	 * ローカルリソースディレクトリのパスを得る。
	 *
	 * @param string $page_path 対象のページのパス
	 * @return string ローカルリソースの実際の絶対パス
	 */
	public function path_files( $page_path = null ){
		if( !is_string( $page_path ) ){
			$page_path = $this->px->req()->get_request_file_path();
		}
		$rtn = $this->px->conf()->path_files;
		$rtn = $this->bind_path_files($rtn, $page_path);
		$rtn = $this->px->href( $rtn );
		$rtn = $this->px->fs()->normalize_path($rtn);
		$rtn = preg_replace( '/^\/+/', '/', $rtn );
		return $rtn;
	}//path_files()


	/**
	 * ローカルリソースディレクトリのサーバー内部パスを得る。
	 *
	 * @param string $page_path 対象のページのパス
	 * @return string ローカルリソースのサーバー内部パス
	 */
	public function realpath_files( $page_path = null ){
		$rtn = $this->path_files( $page_path );
		$rtn = $this->px->fs()->get_realpath( $rtn );
		$rtn = $this->px->fs()->get_realpath( $this->px->get_realpath_docroot().$rtn );
		return $rtn;
	}//realpath_files()

	/**
	 * テーマコレクションディレクトリのパスを得る。
	 *
	 * @return string テーマコレクションディレクトリの絶対パス, 失敗した場合 `false`
	 */
	public function get_realpath_theme_collection_dir(){
		$theme_plugin_name = 'tomk79\\pickles2\\multitheme\\theme::exec';
		$val = $this->plugins()->get_plugin_options($theme_plugin_name, 'processor.html');
		if( @$val[0]->options->path_theme_collection ){
			return $this->px->fs()->get_realpath($val[0]->options->path_theme_collection.'/');
		}
		return false;
	}

	/**
	 * realpath_data_dir のパスを得る。
	 *
	 * @param string $page_path 対象のページのパス
	 * @return string ローカルリソースの実際の絶対パス
	 */
	public function get_realpath_data_dir($page_path = null){
		$path_template = false;
		if( property_exists( $this->get_px2dtconfig(), 'guieditor' ) ){
			if( property_exists( $this->get_px2dtconfig()->guieditor, 'path_data_dir' ) ){
				$path_template = $this->get_px2dtconfig()->guieditor->path_data_dir;
			}elseif( property_exists( $this->get_px2dtconfig()->guieditor, 'realpathDataDir' ) ){
				$path_template = $this->get_px2dtconfig()->guieditor->realpathDataDir; // ← こちらは古い名前。後にスネークケース↑に変更されたため、現在は推奨されない。
			}
		}
		if( $path_template ){
			$rtn = $this->bind_path_files($path_template, $page_path);
		}else{
			$rtn = $this->bind_path_files($this->px->conf()->path_files, $page_path);
			$rtn = preg_replace( '/[\/\\\\]*$/s', '/', $rtn );
			$rtn .= 'guieditor.ignore/';
		}

		$rtn = $this->px->fs()->get_realpath( './'.$rtn );
		$rtn = $this->px->fs()->normalize_path($rtn);
		$rtn = preg_replace( '/^\/+/', '/', $rtn );
		return $rtn;
	}//get_realpath_data_dir()

	/**
	 * Get path_resource_dir
	 * @param string $page_path 対象のページのパス
	 */
	public function get_path_resource_dir($page_path = null){
		if( !is_object($this->px->site()) ){
			return false;
		}
		$path_template = false;
		if( property_exists( $this->get_px2dtconfig(), 'guieditor' ) ){
			if( property_exists( $this->get_px2dtconfig()->guieditor, 'path_resource_dir' ) ){
				$path_template = $this->get_px2dtconfig()->guieditor->path_resource_dir;
			}elseif( property_exists( $this->get_px2dtconfig()->guieditor, 'pathResourceDir' ) ){
				$path_template = $this->get_px2dtconfig()->guieditor->pathResourceDir; // ← こちらは古い名前。後にスネークケース↑に変更されたため、現在は推奨されない。
			}
		}
		if( $path_template ){
			$rtn = $this->bind_path_files($path_template, $page_path);
		}else{
			$rtn = $this->bind_path_files($this->px->conf()->path_files, $page_path);
			$rtn = preg_replace( '/[\/\\\\]*$/s', '/', $rtn );
			$rtn .= 'resources/';
		}
		$rtn = $this->px->href( $rtn );
		$rtn = $this->px->fs()->normalize_path($rtn);
		$rtn = preg_replace( '/^\/+/', '/', $rtn );
		return $rtn;
	}//get_path_resource_dir()

	/**
	 * realpath_data_dir のパスを得る。
	 *
	 * @param string $template テンプレート
	 * @param string $page_path ページのパス
	 * @return string バインド後のパス文字列
	 */
	private function bind_path_files( $template, $page_path = null ){
		if( $this->px->site() ){
			$tmp_page_info = $this->px->site()->get_page_info($page_path);
			$path_content = $tmp_page_info['content'];
			unset($tmp_page_info);
		}
		if( @is_null($path_content) ){
			$path_content = $page_path;
		}
		if( @is_null($path_content) ){
			$path_content = $this->px->req()->get_request_file_path();
		}

		$rtn = false;

		if( is_callable($template) ){
			// コールバック関数が設定された場合
			$rtn = call_user_func($template, $this->px->fs()->normalize_path($path_content) );
		}elseif( is_string($template) && strpos(trim($template), 'function') === 0 ){
			// function で始まる文字列が設定された場合
			$rtn = call_user_func(eval('return '.$template.';'), $this->px->fs()->normalize_path($path_content) );
		}else{
			$rtn = $template;
			$data = array(
				'dirname'=>$this->px->fs()->normalize_path(dirname($path_content)),
				'filename'=>basename($this->px->fs()->trim_extension($path_content)),
				'ext'=>strtolower($this->px->fs()->get_extension($path_content)),
			);
			$rtn = str_replace( '{$dirname}', $data['dirname'], $rtn );
			$rtn = str_replace( '{$filename}', $data['filename'], $rtn );
			$rtn = str_replace( '{$ext}', $data['ext'], $rtn );
		}

		$rtn = preg_replace( '/^\/*/', '/', $rtn );
		$rtn = preg_replace( '/\/*$/', '', $rtn ).'/';

		if( $this->px->fs()->is_dir('./'.$rtn) ){
			$rtn .= '/';
		}
		return $rtn;
	}//bind_path_files()

	/**
	 * Get custom_fields
	 */
	public function get_custom_fields(){
		$rtn = @$this->get_px2dtconfig()->guieditor->custom_fields;
		if( gettype($rtn) != gettype(new \stdClass) ){
			$rtn = new \stdClass;
		}

		foreach( $rtn as $field ){
			$field->backend->require = $this->px->fs()->normalize_path( $this->px->fs()->get_realpath( './'.$field->backend->require ) );
			$field->frontend->file = $this->px->fs()->normalize_path( $this->px->fs()->get_realpath( './'.$field->frontend->file ) );
		}

		return $rtn;
	}

	/**
	 * Get Navigation Info
	 * @param string $page_path ページのパス (省略時: カレントページ)
	 * @param array $opt オプション(省略可)
	 * <dl>
	 *   <dt>$opt['filter'] (初期値: `true`)</dt>
	 *     <dd>フィルターの有効/無効を切り替えます。`true` のとき有効、`false`のとき無効となります。フィルターが有効な場合、サイトマップで `list_flg` が `0` のページが一覧から除外されます。</dd>
	 * </dl>
	 * @return array ナビゲーション情報。 サイトマップがロードできない場合は `false` を返します。
	 */
	public function get_navigation_info( $page_path = null, $opt = array() ){
		if( !is_object($this->px->site()) ){
			return false;
		}
		$rtn = json_decode('{}');
		$rtn->page_info = $this->px->site()->get_page_info($page_path);

		$rtn->breadcrumb = $this->px->site()->get_breadcrumb_array($page_path);
		$rtn->breadcrumb_info = array();
		foreach($rtn->breadcrumb as $page_id){
			array_push($rtn->breadcrumb_info, $this->px->site()->get_page_info($page_id));
		}

		$rtn->parent = $this->px->site()->get_parent($page_path);
		$rtn->parent_info = false;
		if( $rtn->parent !== false ){
			$rtn->parent_info = $this->px->site()->get_page_info($rtn->parent);
		}

		$rtn->bros = $this->px->site()->get_bros($page_path, $opt);
		$rtn->bros_info = array();
		foreach($rtn->bros as $page_id){
			array_push($rtn->bros_info, $this->px->site()->get_page_info($page_id));
		}

		$rtn->children = $this->px->site()->get_children($page_path, $opt);
		$rtn->children_info = array();
		foreach($rtn->children as $page_id){
			array_push($rtn->children_info, $this->px->site()->get_page_info($page_id));
		}

		// var_dump($rtn);
		return $rtn;
	}

	/**
	 * 編集モードを取得する
	 * @param string $page_path 対象のページのパス
	 */
	public function check_editor_mode( $page_path = null ){
		if(!strlen($page_path)){
			$page_path = $this->px->req()->get_request_file_path();
		}
		$page_info = null;
		if( $this->px->site() ){
			$page_info = $this->px->site()->get_page_info($page_path);
		}

		$realpath_controot = $this->px->get_path_docroot().$this->px->get_path_controot();
		$preg_exts = implode( '|', array_keys( get_object_vars( $this->px->conf()->funcs->processor ) ) );

		$path_proc_type = $this->px->get_path_proc_type($page_path);
		$path_content = $this->find_page_content($page_path);

		$rtn = '.not_exists';
		if( !is_file( $realpath_controot.$path_content ) ){
			if( is_null($page_info) ){
				return '.page_not_exists';
			}
			return '.not_exists';
		}

		@preg_match( '/\\.('.$preg_exts.')\\.('.$preg_exts.')$/', $path_content, $matched );

		if( $path_proc_type == 'html' ){
			$rtn = 'html';
			if( @is_string( $matched[2] ) ){
				switch( $matched[2] ){
					case 'md':
						$rtn = $matched[2];
						break;
				}
			}else{
				$realpath_data_dir = $this->get_realpath_data_dir($page_path);
				if( $this->px->fs()->is_file( $realpath_data_dir.'/data.json' ) ){
					$rtn = 'html.gui';
				}
			}
		}

		return $rtn;
	}

	/**
	 * サイトマップ中のページを検索する
	 *
	 * @param  string $keyword キーワード
	 * @param  array $options オプション
	 * @return array 検出されたページの一覧
	 */
	public function search_sitemap( $keyword, $options = array() ){
		require_once(__DIR__.'/fncs/search_sitemap.php');
		$obj = new fncs_search_sitemap( $this, $this->px );
		$result = $obj->find( $keyword, $options = array() );
		return $result;
	}

	/**
	 * コンテンツを初期化する
	 * @param string $editor_mode 編集モード名
	 */
	public function init_content( $editor_mode ){
		require_once(__DIR__.'/fncs/init_content.php');
		$obj = new fncs_init_content( $this, $this->px );
		$result = $obj->init_content( $editor_mode );
		return $result;
	}

	/**
	 * コンテンツを複製する
	 * @param string $path_from 複製元ページのパス
	 * @param string $path_to 複製先ページのパス
	 * @return array 実行結果
	 */
	public function copy_content( $path_from, $path_to ){
		require_once(__DIR__.'/fncs/copy_content.php');
		$copyCont = new fncs_copy_content($this, $this->px);
		$result = $copyCont->copy( $path_from, $path_to );
		return $result;
	}

	/**
	 * コンテンツ編集モードを変更する
	 * @param string $editor_mode 変更後の編集モード名
	 */
	public function change_content_editor_mode( $editor_mode ){
		require_once(__DIR__.'/fncs/change_content_editor_mode.php');
		$obj = new fncs_change_content_editor_mode( $this, $this->px );
		$page_path = $this->px->req()->get_request_file_path();
		$result = $obj->change_content_editor_mode( $editor_mode, $page_path );
		return $result;
	}

	/**
	 * ドキュメントモジュール定義をロードする
	 */
	public function document_modules(){
		require_once( __DIR__.'/fncs/document_modules.php' );
		$rtn = new document_modules($this->px, $this);
		return $rtn;
	}

	/**
	 * プラグイン操作オブジェクトをロードする
	 */
	public function plugins(){
		require_once( __DIR__.'/fncs/plugins.php' );
		$rtn = new plugins($this->px, $this);
		return $rtn;
	}

	/**
	 * パッケージ操作オブジェクトをロードする
	 */
	public function packages(){
		require_once( __DIR__.'/fncs/packages.php' );
		$rtn = new packages($this->px, $this);
		return $rtn;
	}

	/**
	 * kick as PX Command
	 *
	 * @return void
	 */
	private function kick(){
		$this->command = $this->px->get_px_command();
		require_once(__DIR__.'/std_output.php');
		$std_output = new std_output($this->px);

		$sitemap_filter_options = function($px, $cmd=null){
			$options = array();
			$options['filter'] = $px->req()->get_param('filter');
			if( strlen($options['filter']) ){
				switch( $options['filter'] ){
					case 'true':
					case '1':
						$options['filter'] = true;
						break;
					case 'false':
					case '0':
						$options['filter'] = false;
						break;
				}
			}
			return $options;
		};

		switch( @$this->command[1] ){
			case 'ping':
				// 疎通確認応答
				print $std_output->data_convert( 'ok' );
				exit;
				break;

			case 'version':
				// バージョン番号
				print $std_output->data_convert( $this->get_version() );
				exit;
				break;

			case 'check_status':
				// 状態をチェックする
				print $std_output->data_convert( $this->check_status() );
				exit;
				break;

			case 'get':
				switch( @$this->command[2] ){
					case 'realpath_theme_collection_dir':
						$request_path = $this->px->req()->get_request_file_path();
						print $std_output->data_convert( $this->get_realpath_theme_collection_dir() );
						exit;
						break;
					case 'realpath_data_dir':
						print $std_output->data_convert( $this->get_realpath_data_dir() );
						exit;
						break;
					case 'path_resource_dir':
						print $std_output->data_convert( $this->get_path_resource_dir() );
						exit;
						break;
					case 'custom_fields':
						print $std_output->data_convert( $this->get_custom_fields() );
						exit;
						break;
					case 'navigation_info':
						$request_path = $this->px->req()->get_request_file_path();
						print $std_output->data_convert( $this->get_navigation_info( $request_path, $sitemap_filter_options($this->px, $this->command[2]) ) );
						exit;
						break;
					case 'all':
						$rtn = json_decode('{}');
						$request_path = $this->px->req()->get_param('path');
						if( !strlen( $request_path ) ){
							$request_path = $this->px->req()->get_request_file_path();
						}

						@$rtn->config = $this->px->conf();
						@$rtn->version->pxfw = $this->px->get_version();
						@$rtn->version->px2dthelper = $this->get_version();
						@$rtn->px2dtconfig = $this->get_px2dtconfig();
						@$rtn->check_status->px2dthelper = $this->check_status();
						@$rtn->check_status->pxfw_api->version = $rtn->version->pxfw;
						@$rtn->check_status->pxfw_api->is_sitemap_loaded = (is_object($this->px->site()) ? true : false);
						@$rtn->custom_fields = $this->get_custom_fields();
						@$rtn->realpath_homedir = $this->px->get_path_homedir();
						@$rtn->path_controot = $this->px->get_path_controot();
						@$rtn->realpath_docroot = $this->px->get_path_docroot();
						@$rtn->realpath_theme_collection_dir = $this->get_realpath_theme_collection_dir();
						@$rtn->realpath_data_dir = $this->get_realpath_data_dir( $request_path );

						@$rtn->page_info = false;
						@$rtn->path_type = false;
						@$rtn->path_files = false;
						@$rtn->path_resource_dir = false;
						@$rtn->realpath_files = false;
						@$rtn->navigation_info = false;

						if( is_object($this->px->site()) ){
							@$rtn->page_info = $this->px->site()->get_page_info( $request_path );
							@$rtn->path_type = $this->px->get_path_type( $rtn->page_info['path'] );
							if( $rtn->path_type != 'alias' ){
								@$rtn->path_files = $this->path_files( $request_path );
								@$rtn->path_resource_dir = $this->get_path_resource_dir( $request_path );
								@$rtn->realpath_files = $this->realpath_files( $request_path );
							}else{
								@$rtn->realpath_data_dir = false;
							}
							@$rtn->navigation_info = $this->get_navigation_info( $request_path, $sitemap_filter_options($this->px, $this->command[2]) );
						}

						@$rtn->packages->path_composer_root_dir = $this->packages()->get_path_composer_root_dir();
						@$rtn->packages->path_npm_root_dir = $this->packages()->get_path_npm_root_dir();
						@$rtn->packages->package_list = $this->packages()->get_package_list();

						print $std_output->data_convert( $rtn );
						exit;
						break;
				}
				break;

			case 'find_page_content':
				// コンテンツのパスを調べる
				print $std_output->data_convert( $this->find_page_content() );
				exit;
				break;

			case 'check_editor_mode':
				// コンテンツの編集モードを調べる
				$request_path = $this->px->req()->get_param('path');
				if( !strlen( $request_path ) ){
					$request_path = $this->px->req()->get_request_file_path();
				}
				print $std_output->data_convert( $this->check_editor_mode( $request_path ) );
				exit;
				break;

			case 'search_sitemap':
				// サイトマップ中のページを検索する
				$result = $this->search_sitemap( $this->px->req()->get_param('keyword'), array() );
				print $std_output->data_convert( $result );
				exit;
				break;

			case 'init_content':
				// コンテンツを初期化する
				$result = $this->init_content( $this->px->req()->get_param('editor_mode') );
				print $std_output->data_convert( $result );
				exit;
				break;

			case 'copy_content':
				// コンテンツを複製する
				$path_to = $this->px->req()->get_request_file_path();
				$param_to = $this->px->req()->get_param('to');
				if( strlen( $param_to ) ){
					$path_to = $param_to;
				}
				$result = $this->copy_content(
					$this->px->req()->get_param('from'),
					$path_to
				);
				print $std_output->data_convert( $result );
				exit;
				break;

			case 'change_content_editor_mode':
				// コンテンツを初期化する
				$result = $this->change_content_editor_mode( $this->px->req()->get_param('editor_mode') );
				print $std_output->data_convert( $result );
				exit;
				break;

			case 'publish_single_page':
				// 指定ページを単体でパブリッシュする
				$path_target_page = $this->px->req()->get_request_file_path();
				$path_files = $this->px->path_files();
				$result = $this->px->internal_sub_request(
					$path_target_page.'?PX=publish.run&path_region='.$path_target_page.'&paths_region[]='.$path_files.'&keep_cache=1'
				);
				print $std_output->data_convert( $result );
				exit;
				break;

			case 'document_modules':
				// モジュール(Broccoli)の操作
				$data_type = $this->px->req()->get_param('type');
				$theme_id = $this->px->req()->get_param('theme_id');
				$val = null;
				switch( @$this->command[2] ){
					case 'build_css':
						if( !is_string($data_type) || !strlen($data_type) ){
							header('Content-type: text/css; charset=UTF-8');
							$this->px->req()->set_param('type', 'css');
						}
						if( strlen($theme_id) ){
							$val = $this->document_modules()->build_theme_css( $theme_id );
						}else{
							$val = $this->document_modules()->build_css();
						}
						break;
					case 'build_js':
						if( !is_string($data_type) || !strlen($data_type) ){
							header('Content-type: text/javascript; charset=UTF-8');
							$this->px->req()->set_param('type', 'js');
						}
						if( strlen($theme_id) ){
							$val = $this->document_modules()->build_theme_js( $theme_id );
						}else{
							$val = $this->document_modules()->build_js();
						}
						break;
					case 'load':
						if( !is_string($data_type) || !strlen($data_type) ){
							header('Content-type: text/html; charset=UTF-8');
							$this->px->req()->set_param('type', 'html');
						}
						$val = $this->document_modules()->load();
						break;
				}
				print $std_output->data_convert( $val );
				exit;
				break;

			case 'plugins':
				// プラグインの操作
				$val = null;
				switch( @$this->command[2] ){
					case 'get_plugin_options':
						// プラグインのオプション情報を取得する
						$plugin_name = $this->px->req()->get_param('plugin_name');
						$func_div = $this->px->req()->get_param('func_div');
						$val = $this->plugins()->get_plugin_options($plugin_name, $func_div);
						break;
				}
				print $std_output->data_convert( $val );
				exit;
				break;

			case 'packages':
				// composer パッケージの操作
				$val = null;
				switch( @$this->command[2] ){
					case 'get_path_composer_root_dir':
						$val = $this->packages()->get_path_composer_root_dir();
						break;
					case 'get_path_npm_root_dir':
						$val = $this->packages()->get_path_npm_root_dir();
						break;
					case 'get_package_list':
						$val = $this->packages()->get_package_list();
						break;
				}
				print $std_output->data_convert( $val );
				exit;
				break;

			case 'convert_table_excel2html':
				// Excelで書かれた表をHTMLに変換する
				$path_xlsx = $this->px->req()->get_param('path');
				if( !is_file($path_xlsx) || !is_readable($path_xlsx) ){
					print $std_output->data_convert( false );
					exit;
					break;
				}
				$excel2html = new \tomk79\excel2html\main($path_xlsx);
				$val = @$excel2html->get_html(array(
					'header_row' => $this->px->req()->get_param('header_row') ,
					'header_col' => $this->px->req()->get_param('header_col') ,
					'renderer' => $this->px->req()->get_param('renderer') ,
					'cell_renderer' => $this->px->req()->get_param('cell_renderer') ,
					'render_cell_width' => true ,
					'strip_table_tag' => true
				));
				print $std_output->data_convert( $val );
				exit;
				break;

			case 'px2ce':
				// Pickles 2 Contents Editor
				$apis = new px2ce_apis($this->px, $this);
				$result = $apis->execute_px_command($this->command[2]);
				print $std_output->data_convert( $result );
				exit;
				break;

			case 'px2me':
				// Pickles 2 Module Editor
				$apis = new px2me_apis($this->px, $this);
				$result = $apis->execute_px_command($this->command[2]);
				print $std_output->data_convert( $result );
				exit;
				break;
		}

		print $this->px->pxcmd()->get_cli_header();
		print 'Pickles 2 Desktop Tool Helper plugin.'."\n";
		print $this->px->pxcmd()->get_cli_footer();
		exit;
	}

}
