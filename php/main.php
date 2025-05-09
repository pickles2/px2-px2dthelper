<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper;

/**
 * main.php
 */
class main {

	/** Picklesオブジェクト */
	private $px;

	/** langbank */
	private $lb;

	/** PXコマンド名 */
	private $command = array();

	/** px2dtconfig */
	private $px2dtconfig;

	/**
	 * entry
	 *
	 * @param object $px Picklesオブジェクト
	 * @param object $options プラグイン設定
	 */
	static public function register( $px = null, $options = null ){
		if( count(func_get_args()) <= 1 ){
			return __CLASS__.'::'.__FUNCTION__.'('.( is_array($px) ? json_encode($px) : '' ).')';
		}

		$px->pxcmd()->register('px2dthelper', function($px){
			(new self( $px ))->route();
			exit;
		}, true);

		return;
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
	 * *[開発版]*<br />
	 * バージョン番号が振られていない、開発途中のリビジョンを、開発版と呼びます。<br />
	 * 開発版の場合、バージョン番号は、次のリリースが予定されているバージョン番号に、
	 * ビルドメタデータ `+dev` を付加します。
	 * 通常は、プレリリース記号 `alpha` または `beta` を伴うようにします。
	 * - 例：1.0.0-beta.12+dev (=1.0.0-beta.12リリース前の開発版)
	 *
	 * @return string バージョン番号を示す文字列
	 */
	public function get_version(){
		return '2.2.8';
	}


	/**
	 * Constructor
	 *
	 * @param object $px $pxオブジェクト
	 */
	public function __construct( $px ){
		$this->px = $px;
		$this->px2dtconfig = json_decode('{}');
		if( is_object($this->px->conf()->plugins->px2dt ?? null) ){
			$this->px2dtconfig = $this->px->conf()->plugins->px2dt;
		}elseif( is_file( $this->px->get_realpath_homedir().'px2dtconfig.json' ) ){
			$this->px2dtconfig = json_decode( $this->px->fs()->read_file( $this->px->get_realpath_homedir().'px2dtconfig.json' ) );
		}
		$this->px2dtconfig = json_decode( json_encode($this->px2dtconfig) ); // 連想配列で設定されている場合を考慮して、オブジェクト形式に変換する

		// broccoliモジュールパッケージのパスを整形
		$this->px2dtconfig->paths_module_template = $this->px2dtconfig->paths_module_template ?? null;
		if( is_object($this->px2dtconfig->paths_module_template) ){
			foreach( $this->px2dtconfig->paths_module_template as $key=>$val ){
				// ↓ スラッシュで始まり スラッシュで終わる 絶対パスに置き換える。
				// ↓ WindowsでもUNIXスタイルに正規化する。(ボリュームラベルは削除され、バックスラッシュはスラッシュに置き換えられる)
				$this->px2dtconfig->paths_module_template->{$key} = $this->px->fs()->normalize_path( $this->px->fs()->get_realpath( $this->px2dtconfig->paths_module_template->{$key}.'/' ) );
			}
		}

		// LangBank
		$this->lb = new \tomk79\LangBank(__DIR__.'/../data/language.csv');
		$this->lb->setLang( $px->lang() ?? 'ja' );

		// $px->authorizer を初期化する
		authorizer::initialize($px);
	}

	/**
	 * $lb
	 */
	public function lb(){
		return $this->lb;
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
		if( strlen($page_path ?? '') ){
			$path_content = $page_path;
		}
		$ext = $this->px->get_path_proc_type( $this->px->req()->get_request_file_path() );
		if($ext !== 'direct' && $ext !== 'pass'){
			if( $this->px->site() ){
				$current_page_info = $this->px->site()->get_page_info($page_path);
				$tmp_path_content = $current_page_info['content'] ?? null;
				if( strlen( ''.$tmp_path_content ) ){
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
	 * @param string $page_path 対象のページのパス または ページID
	 * @return string ローカルリソースの実際の絶対パス
	 */
	public function path_files( $page_path = null ){
		$path_content = null;
		if( $this->px->site() && is_string($page_path) ){
			$tmp_page_info = $this->px->site()->get_page_info($page_path);
			if( is_array($tmp_page_info) && ($tmp_page_info['path'] == $page_path || $tmp_page_info['id'] == $page_path) ){
				$path_content = $tmp_page_info['content'];
			}
			unset($tmp_page_info);
		}
		if( is_null($path_content) ){
			$path_content = $page_path;
		}
		if( is_null($path_content) ){
			$path_content = $this->px->req()->get_request_file_path();
		}

		$rtn = $this->bind_path_files($this->px->conf()->path_files ?? '', $path_content);
		$rtn = $this->px->href( $rtn );
		$rtn = $this->px->fs()->normalize_path($rtn);
		$rtn = preg_replace( '/^\/+/', '/', $rtn );
		return $rtn;
	}


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
		$rtn = $this->px->fs()->normalize_path($rtn);
		return $rtn;
	}

	/**
	 * ホームディレクトリのパスを得る。
	 *
	 * NOTE: Pickles Framework に `$px->get_path_homedir()` があるが、
	 *       このメソッドは `$px->get_realpath_homedir()` の古い名前であり、絶対パスが返される。
	 *       過去の挙動を壊さないように、このメソッドの振る舞いは変更しない。
	 *       なので、代わりに `$this->get_path_homedir()` を作り、これを使うことにした。
	 *
	 * @return string ホームディレクトリのパス, 失敗した場合 `false`
	 */
	public function get_path_homedir(){
		$realpath_homedir = $this->px->get_realpath_homedir();
		$path_homedir = $this->px->fs()->get_relatedpath($realpath_homedir);
		$path_homedir = $this->px->fs()->normalize_path($path_homedir);
		return $path_homedir;
	}

	/**
	 * テーマコレクションディレクトリのパスを得る。
	 *
	 * @return string テーマコレクションディレクトリの絶対パス, 失敗した場合 `false`
	 */
	public function get_path_theme_collection_dir(){
		$theme_plugin_name = 'tomk79\\pickles2\\multitheme\\theme::exec';
		$val = $this->plugins()->get_plugin_options($theme_plugin_name, 'processor.html');
		if(
			is_array($val)
			&& array_key_exists(0, $val)
			&& is_object($val[0])
			&& property_exists($val[0], 'options')
			&& is_object($val[0]->options)
			&& property_exists($val[0]->options, 'path_theme_collection')
			&& ($val[0]->options->path_theme_collection ?? null) ){
			$relatedpath = $this->px->fs()->get_relatedpath($val[0]->options->path_theme_collection);
			$relatedpath = $this->px->fs()->normalize_path($relatedpath);
			return $relatedpath;
		}
		return false;
	}

	/**
	 * テーマコレクションディレクトリのパスを得る。
	 *
	 * @return string テーマコレクションディレクトリの絶対パス, 失敗した場合 `false`
	 */
	public function get_realpath_theme_collection_dir(){
		$path_theme_collection_dir = $this->get_path_theme_collection_dir();
		if( $path_theme_collection_dir ){
			return $this->px->fs()->normalize_path( $this->px->fs()->get_realpath('./'.$path_theme_collection_dir) );
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
		$path_content = null;
		if( $this->px->site() && is_string($page_path) ){
			$tmp_page_info = $this->px->site()->get_page_info($page_path);
			if( is_array($tmp_page_info) && ($tmp_page_info['path'] == $page_path || $tmp_page_info['id'] == $page_path) ){
				$path_content = $tmp_page_info['content'];
			}
			unset($tmp_page_info);
		}
		if( is_null($path_content) ){
			$path_content = $page_path;
		}
		if( is_null($path_content) ){
			$path_content = $this->px->req()->get_request_file_path();
		}

		$path_template = false;
		if( property_exists( $this->get_px2dtconfig(), 'guieditor' ) ){
			if( property_exists( $this->get_px2dtconfig()->guieditor, 'path_data_dir' ) ){
				$path_template = $this->get_px2dtconfig()->guieditor->path_data_dir;
			}elseif( property_exists( $this->get_px2dtconfig()->guieditor, 'realpathDataDir' ) ){
				$path_template = $this->get_px2dtconfig()->guieditor->realpathDataDir; // ← こちらは古い名前。後にスネークケース↑に変更されたため、現在は推奨されない。
			}
		}
		if( $path_template ){
			$rtn = $this->bind_path_files($path_template, $path_content);
		}else{
			$rtn = $this->bind_path_files($this->px->conf()->path_files, $path_content);
			$rtn = preg_replace( '/[\/\\\\]*$/s', '/', $rtn );
			$rtn .= 'guieditor.ignore/';
		}

		$rtn = $this->px->fs()->get_realpath( './'.$rtn );
		$rtn = $this->px->fs()->normalize_path($rtn);
		$rtn = preg_replace( '/^\/+/', '/', $rtn );
		return $rtn;
	}

	/**
	 * Get path_resource_dir
	 * @param string $page_path 対象のページのパス
	 */
	public function get_path_resource_dir($page_path = null){
		if( !is_object($this->px->site()) ){
			return false;
		}

		$path_content = null;
		if( $this->px->site() && is_string($page_path) ){
			$tmp_page_info = $this->px->site()->get_page_info($page_path);
			if( is_array($tmp_page_info) && ($tmp_page_info['path'] == $page_path || $tmp_page_info['id'] == $page_path) ){
				$path_content = $tmp_page_info['content'];
			}
			unset($tmp_page_info);
		}
		if( is_null($path_content) ){
			$path_content = $page_path;
		}
		if( is_null($path_content) ){
			$path_content = $this->px->req()->get_request_file_path();
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
			$rtn = $this->bind_path_files($path_template, $path_content);
		}else{
			$rtn = $this->bind_path_files($this->px->conf()->path_files, $path_content);
			$rtn = preg_replace( '/[\/\\\\]*$/s', '/', $rtn );
			$rtn .= 'resources/';
		}
		$rtn = $this->px->href( $rtn );
		$rtn = $this->px->fs()->normalize_path($rtn);
		$rtn = preg_replace( '/^\/+/', '/', $rtn );
		return $rtn;
	}

	/**
	 * リソースパステンプレートに実際の値を当てはめる。
	 *
	 * @param string $template テンプレート
	 * @param string $path_content コンテンツのパス
	 * @return string バインド後のパス文字列
	 */
	public function bind_path_files( $template, $path_content = null ){
		if( is_null($path_content) ){
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
				'filename'=>basename(
					''.$this->px->fs()->trim_extension(
						$this->px->fs()->trim_extension($path_content) // 二重拡張子を想定し、拡張子を2つ削除する (2022/8/31)
					)
				),
				'ext'=>strtolower(''.$this->px->fs()->get_extension($path_content)),
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
	}

	/**
	 * Get custom_fields
	 */
	public function get_custom_fields(){
		$rtn = $this->get_px2dtconfig()->guieditor->custom_fields ?? null;
		if( gettype($rtn) != gettype(new \stdClass) ){
			$rtn = new \stdClass;
		}

		foreach( $rtn as $field ){
			if( is_string($field) ){
				// $field 情報が、フィールドIDを文字列でエイリアスとして指定されている場合
				continue;
			}

			$field->backend->require = $this->px->fs()->normalize_path( $this->px->fs()->get_realpath( './'.$field->backend->require ) );

			if( is_string($field->frontend->file) ){
				$field->frontend->file = array( $field->frontend->file );
			}
			if( is_array($field->frontend->file) ){
				foreach( $field->frontend->file as $key => $row ){
					$field->frontend->file[$key] = $this->px->fs()->normalize_path( $this->px->fs()->get_realpath( './'.$field->frontend->file[$key] ) );
				}
			}
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
		$rtn->top_page_info = $this->px->site()->get_page_info('');

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

		$rtn->global_menu = $this->px->site()->get_global_menu();
		$rtn->global_menu_info = array();
		foreach($rtn->global_menu as $page_id){
			array_push($rtn->global_menu_info, $this->px->site()->get_page_info($page_id));
		}

		$rtn->shoulder_menu = $this->px->site()->get_shoulder_menu();
		$rtn->shoulder_menu_info = array();
		foreach($rtn->shoulder_menu as $page_id){
			array_push($rtn->shoulder_menu_info, $this->px->site()->get_page_info($page_id));
		}

		$rtn->category_top = $this->px->site()->get_category_top($page_path);
		$rtn->category_top_info = false;
		if( $rtn->category_top !== false ){
			$rtn->category_top_info = $this->px->site()->get_page_info($rtn->category_top);
		}

		$rtn->category_sub_menu = $this->px->site()->get_children($rtn->category_top, $opt);
		$rtn->category_sub_menu_info = array();
		foreach($rtn->category_sub_menu as $page_id){
			array_push($rtn->category_sub_menu_info, $this->px->site()->get_page_info($page_id));
		}

		return $rtn;
	}

	/**
	 * 編集モードを取得する
	 * @param string $page_path 対象のページのパス
	 */
	public function check_editor_mode( $page_path = null ){
		if( !strlen($page_path ?? '') ){
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

		preg_match( '/\\.('.$preg_exts.')\\.('.$preg_exts.')$/', $path_content ?? '', $matched );

		if( $path_proc_type == 'html' ){
			$rtn = 'html';
			if( is_string( $matched[2] ?? null) ){
				$rtn = $matched[2] ?? null;
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
		$obj = new fncs\search_sitemap( $this, $this->px );
		$result = $obj->find( $keyword, $options );
		return $result;
	}

	/**
	 * コンテンツを初期化する
	 * @param string $editor_mode 編集モード名
	 * @param  array  $options   オプション (詳しくは `fncs_init_content::init_content()` の説明を参照)
	 */
	public function init_content( $editor_mode, $options = array() ){
		$obj = new fncs\init_content( $this, $this->px );
		$result = $obj->init_content( $editor_mode, $options );
		return $result;
	}

	/**
	 * コンテンツを複製する
	 * @param string $path_from 複製元ページのパス
	 * @param string $path_to 複製先ページのパス
	 * @param  array  $options   オプション (詳しくは `fncs_copy_content::copy()` の説明を参照)
	 * @return array 実行結果
	 */
	public function copy_content( $path_from, $path_to, $options = array() ){
		$copyCont = new fncs\copy_content($this, $this->px);
		$result = $copyCont->copy( $path_from, $path_to, $options );
		return $result;
	}

	/**
	 * コンテンツ編集モードを変更する
	 * @param string $editor_mode 変更後の編集モード名
	 */
	public function change_content_editor_mode( $editor_mode ){
		$obj = new fncs\change_content_editor_mode( $this, $this->px );
		$page_path = $this->px->req()->get_request_file_path();
		$result = $obj->change_content_editor_mode( $editor_mode, $page_path );
		return $result;
	}

	/**
	 * ドキュメントモジュール定義をロードする
	 */
	public function document_modules(){
		$rtn = new fncs\document_modules($this->px, $this);
		return $rtn;
	}

	/**
	 * プラグイン操作オブジェクトをロードする
	 */
	public function plugins(){
		$rtn = new fncs\plugins($this->px, $this);
		return $rtn;
	}

	/**
	 * パッケージ操作オブジェクトをロードする
	 */
	public function packages(){
		$rtn = new fncs\packages($this->px, $this);
		return $rtn;
	}

	/**
	 * ファイルの MIME Content-type を検出する
	 */
	public function mime_content_type($filename){
		$ext = $this->px->fs()->get_extension($filename ?? '');
		$ext = strtolower($ext ?? '');

		switch( $ext ){
			// texts
			case 'html': case 'htm': return 'text/html';
			case 'css': return 'text/css';
			case 'js': return 'text/javascript';

			// images
			case 'png': return 'image/png';
			case 'gif': return 'image/gif';
			case 'jpg': case 'jpeg': case 'jpe': return 'image/jpeg';
			case 'webp': return 'image/webp';
			case 'svg': return 'image/svg+xml';

			// fonts
			case 'eot': return 'application/vnd.ms-fontobject';
			case 'woff': return 'application/x-woff';
			case 'otf': return 'application/x-font-opentype';
			case 'ttf': return 'application/x-font-truetype';
		}
		return mime_content_type($filename);
	}

	/**
	 * route as PX Command
	 *
	 * @return void
	 */
	private function route(){
		$this->command = $this->px->get_px_command();
		$std_output = new std_output($this->px);

		if( strlen($this->px->req()->get_param('lang') ?? '') ){
			$this->lb->setLang( $this->px->req()->get_param('lang') );
		}

		$sitemap_filter_options = function($px, $cmd=null){
			$options = array();
			$options['filter'] = $px->req()->get_param('filter');
			if( strlen(''.$options['filter']) ){
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

		switch( $this->command[1] ?? '' ){
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
				switch( $this->command[2] ?? '' ){
					case 'path_theme_collection_dir':
						$request_path = $this->px->req()->get_request_file_path();
						print $std_output->data_convert( $this->get_path_theme_collection_dir() );
						exit;
						break;

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
						$rtn = (object) array();
						$request_path = $this->px->req()->get_param('path');
						if( !strlen( $request_path ?? '' ) ){
							$request_path = $this->px->req()->get_request_file_path();
						}

						$rtn->config = $this->px->conf();
						$rtn->version = (object) array();
						$rtn->version->pxfw = $this->px->get_version();
						$rtn->version->px2dthelper = $this->get_version();
						$rtn->px2dtconfig = $this->get_px2dtconfig();
						$rtn->check_status = (object) array();
						$rtn->check_status->px2dthelper = $this->check_status();
						$rtn->check_status->pxfw_api = (object) array();
						$rtn->check_status->pxfw_api->version = $rtn->version->pxfw;
						$rtn->check_status->pxfw_api->is_sitemap_loaded = (is_object($this->px->site()) ? true : false);
						$rtn->custom_fields = $this->get_custom_fields();
						$rtn->path_homedir = $this->get_path_homedir();
							// NOTE: Pickles Framework に `$px->get_path_homedir()` があるが、
							//       このメソッドは `$px->get_realpath_homedir()` の古い名前であり、絶対パスが返される。
							//       過去の挙動を壊さないように、このメソッドの振る舞いは変更しない。
							//       なので、代わりに `$this->get_path_homedir()` を作り、これを使うことにした。
						$rtn->realpath_homedir = $this->px->get_realpath_homedir();
						$rtn->path_controot = $this->px->get_path_controot();
						$rtn->realpath_docroot = $this->px->get_path_docroot();
						$rtn->path_theme_collection_dir = $this->get_path_theme_collection_dir();
						$rtn->realpath_theme_collection_dir = $this->get_realpath_theme_collection_dir();
						$rtn->realpath_data_dir = $this->get_realpath_data_dir( $request_path );

						$rtn->page_info = false;
						$rtn->path_type = false;
						$rtn->path_files = false;
						$rtn->path_resource_dir = false;
						$rtn->realpath_files = false;
						$rtn->navigation_info = false;

						if( is_object($this->px->site()) ){
							$rtn->page_info = $this->px->site()->get_page_info( $request_path );
							if( is_array($rtn->page_info) && array_key_exists('path', $rtn->page_info) ){
								$rtn->path_type = $this->px->get_path_type( $rtn->page_info['path'] );
							}
							if( $rtn->path_type && $rtn->path_type != 'alias' ){
								$rtn->path_files = $this->path_files( $request_path );
								$rtn->path_resource_dir = $this->get_path_resource_dir( $request_path );
								$rtn->realpath_files = $this->realpath_files( $request_path );
							}else{
								$rtn->realpath_data_dir = false;
							}
							$rtn->navigation_info = $this->get_navigation_info( $request_path, $sitemap_filter_options($this->px, $this->command[2]) );
							if( is_callable(array($this->px->site(), 'get_page_originated_csv')) ){
								$rtn->page_originated_csv = $this->px->site()->get_page_originated_csv( $request_path );
							}
						}

						$rtn->packages = (object) array();
						$rtn->packages->path_composer_root_dir = $this->packages()->get_path_composer_root_dir();
						$rtn->packages->path_npm_root_dir = $this->packages()->get_path_npm_root_dir();
						$rtn->packages->package_list = $this->packages()->get_package_list();

						$ccExtMgr = new fncs\customConsoleExtensions\pxcmdOperator($this->px, $this);
						$rtn->custom_console_extensions = $ccExtMgr->get_list();

						print $std_output->data_convert( $rtn );
						exit;
						break;

					case 'list_all_contents':
						// NOTE: px2-px2dthelper v2.2.3 で追加
						$listAllContents = new fncs\get\listAllContents( $this, $this->px );
						$rtn = $listAllContents->get_all_contents();
						print $std_output->data_convert( $rtn );
						exit;
						break;

					case 'list_unassigned_contents':
						// NOTE: px2-px2dthelper v2.1.5 で追加
						$listUnassignedContents = new fncs\get\listUnassignedContents( $this, $this->px );
						$rtn = $listUnassignedContents->get_unassigned_contents();
						print $std_output->data_convert( $rtn );
						exit;
						break;

					case 'list_gui_editor_contents':
						// NOTE: px2-px2dthelper v2.2.3 で追加
						$listGuiEditorContents = new fncs\get\listGuiEditorContents( $this, $this->px );
						$rtn = $listGuiEditorContents->get_gui_editor_contents();
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
				if( !strlen( $request_path ?? '' ) ){
					$request_path = $this->px->req()->get_request_file_path();
				}
				print $std_output->data_convert( $this->check_editor_mode( $request_path ) );
				exit;
				break;

			case 'search_sitemap':
				// サイトマップ中のページを検索する
				$result = $this->search_sitemap(
					$this->px->req()->get_param('keyword'),
					array(
						'limit' => $this->px->req()->get_param('limit'),
					)
				);
				print $std_output->data_convert( $result );
				exit;
				break;

			case 'sitemap':
				// サイトマップ操作
				$sitemap_editor = new fncs\sitemap\sitemapEditor( $this, $this->px );
				switch( $this->command[2] ?? '' ){
					case 'filelist':
						$filename = $this->px->req()->get_param('filename');
						$result = $sitemap_editor->filelist($filename);
						print $std_output->data_convert( $result );
						exit;
						break;

					case 'create':
						$this->route_only_post_and_cli();
						$filename = $this->px->req()->get_param('filename');
						$result = $sitemap_editor->create($filename);
						print $std_output->data_convert( $result );
						exit;
						break;

					case 'download':
						$filefullname = $this->px->req()->get_param('filefullname');
						$result = $sitemap_editor->read($filefullname);
						if( !$this->px->req()->is_cmd() ){
							$this->px->header('Content-type: application/octet-stream');
							print $result['bin'];
							exit;
						}
						$result['base64'] = base64_encode($result['bin']);
						unset($result['bin']);
						print $std_output->data_convert( $result );
						exit;
						break;

					case 'upload':
						$this->route_only_post_and_cli();
						$filefullname = $this->px->req()->get_param('filefullname');
						$file = $this->px->req()->get_param('file');
						if( !$this->px->req()->is_cmd() ){
							if( !strlen($filefullname) ){
								$filefullname = $file['name'];
							}
							$bin = $this->px->fs()->read_file( $file['tmp_name'] );
						}else{
							$bin = $this->px->fs()->read_file( $file );
						}
						$result = $sitemap_editor->save($filefullname, $bin);
						print $std_output->data_convert( $result );
						exit;
						break;

					case 'xlsx2csv':
						$this->route_only_post_and_cli();
						$filename = $this->px->req()->get_param('filename');
						$result = $sitemap_editor->xlsx2csv($filename);
						print $std_output->data_convert( $result );
						exit;
						break;

					case 'csv2xlsx':
						$this->route_only_post_and_cli();
						$filename = $this->px->req()->get_param('filename');
						$result = $sitemap_editor->csv2xlsx($filename);
						print $std_output->data_convert( $result );
						exit;
						break;

					case 'delete':
						$this->route_only_post_and_cli();
						$filename = $this->px->req()->get_param('filename');
						$result = $sitemap_editor->delete($filename);
						print $std_output->data_convert( $result );
						exit;
						break;

				}
				break;

			case 'page':
				// サイトマップ/ページ操作
				$sitemap_page_editor = new fncs\page\pageEditor( $this, $this->px );
				switch( $this->command[2] ?? '' ){
					case 'get_page_info_raw':
						$filename = $this->px->req()->get_param('filefullname');
						$row = $this->px->req()->get_param('row');
						$result = $sitemap_page_editor->get_page_info_raw($filename, $row);
						print $std_output->data_convert( $result );
						exit;
						break;

					case 'add_page_info_raw':
						$this->route_only_post_and_cli();
						$filename = $this->px->req()->get_param('filefullname');
						$row = $this->px->req()->get_param('row');
						$page_info = $this->px->req()->get_param('page_info');
						$result = $sitemap_page_editor->add_page_info_raw($filename, $row, $page_info);
						print $std_output->data_convert( $result );
						exit;
						break;

					case 'move_page_info_raw':
						$this->route_only_post_and_cli();
						$from_filename = $this->px->req()->get_param('from_filefullname');
						$from_row = $this->px->req()->get_param('from_row');
						$to_filename = $this->px->req()->get_param('to_filefullname');
						$to_row = $this->px->req()->get_param('to_row');
						$result = $sitemap_page_editor->move_page_info_raw($from_filename, $from_row, $to_filename, $to_row);
						print $std_output->data_convert( $result );
						exit;
						break;

					case 'update_page_info_raw':
						$this->route_only_post_and_cli();
						$filename = $this->px->req()->get_param('filefullname');
						$row = $this->px->req()->get_param('row');
						$page_info = $this->px->req()->get_param('page_info');
						$result = $sitemap_page_editor->update_page_info_raw($filename, $row, $page_info);
						print $std_output->data_convert( $result );
						exit;
						break;

					case 'delete_page_info_raw':
						$this->route_only_post_and_cli();
						$filename = $this->px->req()->get_param('filefullname');
						$row = $this->px->req()->get_param('row');
						$result = $sitemap_page_editor->delete_page_info_raw($filename, $row);
						print $std_output->data_convert( $result );
						exit;
						break;

				}
				break;

			case 'content':
				// コンテンツ操作
				$content_editor = new fncs\content\contentEditor( $this, $this->px );
				switch( $this->command[2] ?? '' ){
					case 'move':
						$path_from = $this->px->req()->get_param('from');
						$path_to = $this->px->req()->get_param('to');
						$result = $content_editor->move($path_from, $path_to);
						print $std_output->data_convert( $result );
						exit;
						break;

					case 'delete':
						$result = $content_editor->delete();
						print $std_output->data_convert( $result );
						exit;
						break;
				}

				break;

			case 'contents_template':
				$contents_template = new \tomk79\pickles2\px2dthelper\fncs\contentsTemplate\contentsTemplate($this, $this->px);
				switch( $this->command[2] ?? '' ){
					case 'get_list':
						$result = $contents_template->get_list();
						print $std_output->data_convert( $result );
						exit;
						break;

				}
				break;

			case 'init_content':
				// コンテンツを初期化する
				$this->route_only_post_and_cli();
				$flg_force = $this->px->req()->get_param('force');
				$result = $this->init_content(
					$this->px->req()->get_param('editor_mode'),
					array(
						'force'=>$flg_force,
					)
				);
				print $std_output->data_convert( $result );
				exit;
				break;

			case 'copy_content':
				// コンテンツを複製する
				$this->route_only_post_and_cli();
				$flg_force = $this->px->req()->get_param('force');
				$path_to = $this->px->req()->get_request_file_path();
				$param_path_to = $this->px->req()->get_param('to');
				if( strlen( $param_path_to ?? '' ) ){
					$path_to = $param_path_to;
				}
				$result = $this->copy_content(
					$this->px->req()->get_param('from'),
					$path_to,
					array(
						'force'=>$flg_force,
					)
				);
				print $std_output->data_convert( $result );
				exit;
				break;

			case 'change_content_editor_mode':
				// コンテンツを初期化する
				$this->route_only_post_and_cli();
				$this->authorize_required('server_side_scripting');
					// TODO: HTML中に動的コードを含まないコンテンツならば、'server_side_scripting' 権限は要らないかもしれない。
				$result = $this->change_content_editor_mode( $this->px->req()->get_param('editor_mode') );
				print $std_output->data_convert( $result );
				exit;
				break;

			case 'publish_single_page':
				// 指定ページを単体でパブリッシュする
				$this->route_only_post_and_cli();
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
				switch( $this->command[2] ?? '' ){
					case 'build_css':
						if( !is_string($data_type) || !strlen($data_type) ){
							header('Content-type: text/css; charset=UTF-8');
							$this->px->req()->set_param('type', 'css');
						}
						if( strlen(''.$theme_id) ){
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
						if( strlen(''.$theme_id) ){
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

			case 'config':
				switch( $this->command[2] ?? '' ){
					case 'parse':
						$config_parser = new fncs\config\configParser( $this, $this->px );
						$result = $config_parser->parse();
						print $std_output->data_convert( $result );
						exit;
						break;

					case 'update':
						$this->route_only_post_and_cli();
						$this->authorize_required('config');
						$config_parser = new fncs\config\configParser( $this, $this->px );
						$set_vars = array();
						$base64_json = $this->px->req()->get_param('base64_json');
						$json = $this->px->req()->get_param('json');
						if( strlen($base64_json ?? '') ){
							$json = base64_decode($base64_json);
						}
						if( strlen($json ?? '') ){
							$set_vars = json_decode($json, true);
						}
						$result = $config_parser->update($set_vars);
						print $std_output->data_convert( $result );
						exit;
						break;
				}
				break;

			case 'plugins':
				// プラグインの操作
				$val = null;
				switch( $this->command[2] ?? '' ){
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
				switch( $this->command[2] ?? '' ){
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

			case 'authorizer':
				switch( $this->command[2] ?? '' ){
					case 'is_authorized':
						$result = (object) array(
							"result" => false,
							"message" => "Authorizer is not ready.",
							"available" => false,
							"role" => null,
							"is_authorized" => null,
						);
						if( is_object($this->px->authorizer) ){
							$result->result = true;
							$result->message = 'OK';
							$result->available = true;
							$result->role = $this->px->authorizer->get_role();
							$result->is_authorized = $this->px->authorizer->is_authorized($this->command[3] ?? '');
						}
						print $std_output->data_convert( $result );
						exit;
						break;
				}
				break;

			case 'convert_table_excel2html':
				// Excelで書かれた表をHTMLに変換する
				$this->route_only_post_and_cli();
				$path_xlsx = $this->px->req()->get_param('path');
				if( !is_file($path_xlsx) || !is_readable($path_xlsx) ){
					print $std_output->data_convert( false );
					exit;
					break;
				}
				$excel2html = new \tomk79\excel2html\main($path_xlsx);
				$val = $excel2html->get_html(array(
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
				$this->route_only_post_and_cli();
				$apis = new px2ce_apis($this->px, $this);
				$result = $apis->execute_px_command($this->command[2]);
				print $std_output->data_convert( $result );
				exit;
				break;

			case 'px2me':
				// Pickles 2 Module Editor
				$this->route_only_post_and_cli();
				$this->authorize_required('server_side_scripting');
				$apis = new px2me_apis($this->px, $this);
				$result = $apis->execute_px_command($this->command[2]);
				print $std_output->data_convert( $result );
				exit;
				break;

			case 'px2te':
				// Pickles 2 Theme Editor
				$this->route_only_post_and_cli();
				$apis = new px2te_apis($this->px, $this);
				$result = $apis->execute_px_command($this->command[2]);
				print $std_output->data_convert( $result );
				exit;
				break;

			case 'custom_console_extensions':
				// Custom Console Extensions
				// 管理画面を拡張するインターフェイス
				$ccExtMgr = new fncs\customConsoleExtensions\pxcmdOperator($this->px, $this);
				$ary_px_command = $this->command;
				array_shift($ary_px_command); // <- `px2dthelper` をトル
				array_shift($ary_px_command); // <- `custom_console_extensions` をトル
				$result = $ccExtMgr->execute_px_command($ary_px_command);
				print $std_output->data_convert( $result );
				exit;
				break;

			case 'custom_console_extensions_async_run':
				$this->route_only_post_and_cli();
				$tmpAsync = new fncs\customConsoleExtensions\async(null, $this->px, $this);
				$config = $tmpAsync->get_config();
				$rtn = array(
					'result' => true,
					'message' => 'OK',
				);

				switch( $config->method ){
					case 'file':
						$realpath_dir = $config->dir;
						if( !strlen(''.$realpath_dir) || !is_dir($realpath_dir) || !is_writable($realpath_dir) ){
							return false;
						}
						$realpath_dir = $this->px->fs()->get_realpath($realpath_dir.'/');
						$cmdList = $this->px->fs()->ls($realpath_dir);
						$rtn['responses'] = array();

						foreach($cmdList as $filename){
							$command = false;
							$file_content = file_get_contents( $realpath_dir.$filename );
							try{
								$command = json_decode( $file_content );
								unlink($realpath_dir.$filename);
							}catch(Exception $e){
							}
							$async = new fncs\customConsoleExtensions\async($command->cce_id, $this->px, $this);
							$result = $async->run( $command->command );
							array_push($rtn['responses'], $result);
						}
						print $std_output->data_convert( $rtn );
						exit;
						break;
					case 'sync':
						print $std_output->data_convert( array(
							'result' => false,
							'message' => 'Method "sync" is unable to call by outside.',
						) );
						exit;
						break;
				}

				print $std_output->data_convert( false );
				exit;
				break;
		}

		print $this->px->pxcmd()->get_cli_header();
		print 'Pickles 2 Desktop Tool Helper plugin.'."\n";
		print 'Command not found.'."\n";
		print $this->px->pxcmd()->get_cli_footer();
		exit;
	}

	/**
	 * POSTメソッド、CLIのみ許容
	 */
	private function route_only_post_and_cli(){
		$method = $this->px->req()->get_method();
		switch( $method ){
			case 'command':
			case 'post':
				break;
			default:
				$std_output = new std_output($this->px);
				$this->px->set_status(405);
				print $std_output->data_convert( array(
					'result' => false,
					'message' => "405 Method Not Allowed.",
				) );
				exit;
				break;
		}
		return;
	}

	/**
	 * Authorizeが必要
	 */
	private function authorize_required( $authority_name ){
		$is_authorized = true;
		if( is_object($this->px->authorizer) ){
			$is_authorized = $this->px->authorizer->is_authorized($authority_name);
		}
		if($is_authorized){
			return;
		}

		$std_output = new std_output($this->px);
		$this->px->set_status(401);
		print $std_output->data_convert( array(
			'result' => false,
			'message' => "401 Unauthorized.",
		) );
		exit;
	}
}
