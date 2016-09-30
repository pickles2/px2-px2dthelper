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
		return '2.0.1-alpha.1+nb';
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
	 * px2dtconfigを取得する
	 */
	public function get_px2dtconfig(){
		return $this->px2dtconfig;
	}

	/**
	 * realpath_data_dir のパスを得る。
	 *
	 * @param string $localpath_resource ローカルリソースのパス
	 * @return string ローカルリソースの実際の絶対パス
	 */
	public function get_realpath_data_dir(){
		$rtn = @$this->get_px2dtconfig()->guieditor->path_data_dir;
		if( !strlen( $rtn ) ){
			$rtn = $this->px->conf()->path_files.'/guieditor.ignore/';
		}
		$rtn = $this->bind_path_files($rtn);
		$rtn = $this->px->fs()->get_realpath( './'.$rtn );
		$rtn = $this->px->fs()->normalize_path($rtn);
		$rtn = preg_replace( '/^\/+/', '/', $rtn );
		return $rtn;
	}//get_realpath_data_dir()

	/**
	 * Get path_resource_dir
	 */
	public function get_path_resource_dir(){
		$rtn = @$this->get_px2dtconfig()->guieditor->path_resource_dir;
		if( !strlen( $rtn ) ){
			$rtn = $this->px->conf()->path_files.'/resources/';
		}
		$rtn = $this->bind_path_files($rtn);
		$rtn = $this->px->href( $rtn );
		$rtn = $this->px->fs()->normalize_path($rtn);
		$rtn = preg_replace( '/^\/+/', '/', $rtn );
		return $rtn;
	}//get_path_resource_dir()

	/**
	 * realpath_data_dir のパスを得る。
	 *
	 * @param string $template テンプレート
	 * @return string バインド後のパス文字列
	 */
	private function bind_path_files( $template ){
		if( $this->px->site() !== false ){
			$tmp_page_info = $this->px->site()->get_current_page_info();
			$path_content = $tmp_page_info['content'];
			unset($tmp_page_info);
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
	 */
	public function check_editor_mode(){
		$page_info = null;
		if( $this->px->site() ){
			$page_info = $this->px->site()->get_current_page_info();
		}
		// var_dump($page_info);
		// var_dump($realpath_data_dir);

		$preg_exts = implode( '|', array_keys( get_object_vars( $this->px->conf()->funcs->processor ) ) );

		$path_proc_type = $this->px->get_path_proc_type();
		// var_dump( $path_proc_type );
		$path_content = $this->px->get_path_content();
		// var_dump( $path_content );

		$rtn = '.not_exists';
		if( !is_file( './'.$path_content ) ){
			if( is_null($page_info) ){
				return '.page_not_exists';
			}
			return '.not_exists';
		}

		preg_match( '/\\.('.$preg_exts.')\\.('.$preg_exts.')$/', $path_content, $matched );
		// var_dump($matched);

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

		// var_dump( $rtn );
		return $rtn;
	}

	/**
	 * コンテンツを複製する
	 */
	public function copy_content($path_from, $path_to){
		require_once(__DIR__.'/fncs/copy_content.php');
		$copyCont = new fncs_copy_content($this->px);
		$result = $copyCont->copy( $path_from, $path_to );
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
	 * フィールド定義オブジェクトを取得
	 */
	public function get_field_definition( $field_type ){
		require_once( __DIR__.'/field_base.php' );
		$rtn = null;
		if( is_file( __DIR__.'/fields/field.'.$field_type.'.php' ) ){
			require_once( __DIR__.'/fields/field.'.$field_type.'.php' );
			$class_name = '\\tomk79\\pickles2\\px2dthelper\\field_'.$field_type;
			$rtn = new $class_name();
		}else{
			$rtn = new field_base();
		}
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

			case 'check_editor_mode':
				print $std_output->data_convert( $this->check_editor_mode() );
				exit;
				break;
				break;

			case 'copy_content':
				// コンテンツを複製する
				$result = $this->copy_content(
					$this->px->req()->get_param('from'),
					$this->px->req()->get_param('to')
				);
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
