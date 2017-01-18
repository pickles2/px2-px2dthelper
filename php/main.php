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

	/** $module_templates */
	private $obj_module_templates;

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
		return '2.0.3-alpha.1+nb';
	}


	/**
	 * constructor
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

	}

	/**
	 * セットアップ状態をチェックする
	 * @return object 状態情報
	 */
	public function check_status(){
		$rtn = json_decode('{}');
		$rtn->version = $this->get_version();
		$rtn->is_sitemap_loaded = ($this->px->site() ? true : false);
		return $rtn;
	}

	/**
	 * px2dtconfigを取得する
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
	public function path_files( $page_path ){
		if( $this->px->site() ){
			$tmp_page_info = $this->px->site()->get_page_info($page_path);
			$path_content = $tmp_page_info['content'];
			unset($tmp_page_info);
		}
		if( @is_null($path_content) ){
			$path_content = $page_path;
		}

		$rtn = $this->px->conf()->path_files;
		$data = array(
			'dirname'=>$this->px->fs()->normalize_path(dirname($path_content)),
			'filename'=>basename($this->px->fs()->trim_extension($path_content)),
			'ext'=>strtolower($this->px->fs()->get_extension($path_content)),
		);
		$rtn = str_replace( '{$dirname}', $data['dirname'], $rtn );
		$rtn = str_replace( '{$filename}', $data['filename'], $rtn );
		$rtn = str_replace( '{$ext}', $data['ext'], $rtn );
		$rtn = preg_replace( '/^\/*/', '/', $rtn );
		$rtn = preg_replace( '/\/*$/', '', $rtn ).'/';

		if( $this->px->fs()->is_dir('./'.$rtn) ){
			$rtn .= '/';
		}
		$rtn = $this->px->href( $rtn );
		$rtn = $this->px->fs()->normalize_path($rtn);
		$rtn = preg_replace( '/^\/+/', '/', $rtn );
		return $rtn;
	}//path_files()

	/**
	 * realpath_data_dir のパスを得る。
	 *
	 * @param string $page_path 対象のページのパス
	 * @return string ローカルリソースの実際の絶対パス
	 */
	public function get_realpath_data_dir($page_path = null){
		$rtn = @$this->get_px2dtconfig()->guieditor->path_data_dir;
		if( !strlen($rtn) ){
			$rtn = @$this->get_px2dtconfig()->guieditor->realpathDataDir;//古い仕様
		}
		if( !strlen( $rtn ) ){
			$rtn = $this->px->conf()->path_files.'/guieditor.ignore/';
		}
		$rtn = $this->bind_path_files($rtn, $page_path);
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
		$rtn = @$this->get_px2dtconfig()->guieditor->path_resource_dir;
		if( !strlen($rtn) ){
			$rtn = @$this->get_px2dtconfig()->guieditor->pathResourceDir;//古い仕様
		}
		if( !strlen( $rtn ) ){
			$rtn = $this->px->conf()->path_files.'/resources/';
		}
		$rtn = $this->bind_path_files($rtn, $page_path);
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

		$rtn = @$template;
		$data = array(
			'dirname'=>$this->px->fs()->normalize_path(dirname($path_content)),
			'filename'=>basename($this->px->fs()->trim_extension($path_content)),
			'ext'=>strtolower($this->px->fs()->get_extension($path_content)),
		);
		$rtn = str_replace( '{$dirname}', $data['dirname'], $rtn );
		$rtn = str_replace( '{$filename}', $data['filename'], $rtn );
		$rtn = str_replace( '{$ext}', $data['ext'], $rtn );
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
				$realpath_data_dir = $this->get_realpath_data_dir();
				if( $this->px->fs()->is_file( $realpath_data_dir.'/data.json' ) ){
					$rtn = 'html.gui';
				}
			}
		}

		return $rtn;
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
		$rtn = '';
		$rtn = new document_modules($this->px, $this);
		return $rtn;
	}

	/**
	 * ドキュメントモジュール定義をロードする
	 */
	public function module_templates(){
		return $this->obj_module_templates;
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
				}
				break;

			case 'find_page_content':
				// コンテンツのパスを調べる
				print $std_output->data_convert( $this->find_page_content() );
				exit;
				break;

			case 'check_editor_mode':
				// コンテンツの編集モードを調べる
				print $std_output->data_convert( $this->check_editor_mode() );
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

			case 'document_modules':
				$data_type = $this->px->req()->get_param('type');
				$val = null;
				switch( @$this->command[2] ){
					case 'build_css':
						if( !is_string($data_type) || !strlen($data_type) ){
							header('Content-type: text/css; charset=UTF-8');
							$this->px->req()->set_param('type', 'css');
						}
						$val = $this->document_modules()->build_css();
						break;
					case 'build_js':
						if( !is_string($data_type) || !strlen($data_type) ){
							header('Content-type: text/javascript; charset=UTF-8');
							$this->px->req()->set_param('type', 'js');
						}
						$val = $this->document_modules()->build_js();
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

			case 'convert_table_excel2html':
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

		}

		print $this->px->pxcmd()->get_cli_header();
		print 'Pickles 2 Desktop Tool Helper plugin.'."\n";
		print $this->px->pxcmd()->get_cli_footer();
		exit;
	}

}
