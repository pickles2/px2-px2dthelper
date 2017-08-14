<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper;

/**
 * document_modules.php
 */
class document_modules{

	/**
	 * Picklesオブジェクト
	 */
	private $px;

	/**
	 * px2dthelper main
	 */
	private $main;

	/**
	 * constructor
	 *
	 * @param object $px $pxオブジェクト
	 * @param object $main main.php のインスタンス
	 */
	public function __construct( $px, $main ){
		$this->px = $px;
		$this->main = $main;
	}

	/**
	 * ドキュメントモジュール定義をロードする
	 *
	 * モジュール定義の情報から、スタイルシートとJavaScriptコードを生成します。
	 *
	 * HTMLのheadセクション内に、下記のようにコードを記述します。
	 *
	 * ```
	 * <?php
	 * print (new \tomk79\pickles2\px2dthelper\main($px))->document_modules()->load();
	 * ?>
	 * ```
	 *
	 * @return string HTMLコード(styleタグ、およびscriptタグ)
	 */
	public function load(){
		$rtn = '';
		$rtn .= '<style type="text/css">'.$this->build_css().'</style>';
		$rtn .= '<script type="text/javascript">'.$this->build_js().'</script>';
		return $rtn;
	}

	/**
	 * ドキュメントモジュール定義のスタイルシートを統合する
	 *
	 * モジュール定義の情報から、スタイルシートを生成します。
	 * `$conf->plugins->px2dt->paths_module_template` および `$conf->plugins->px2dt->path_module_templates_dir` を参照します。
	 *
	 * スタイルシートファイル(例: `/common/styles/contents.css` など)に、下記のようにコードを記述します。
	 *
	 * ```
	 * <?php
	 * print (new \tomk79\pickles2\px2dthelper\main($px))->document_modules()->build_css();
	 * ?>
	 * ```
	 *
	 * @return string CSSコード
	 */
	public function build_css(){
		$conf = $this->main->get_px2dtconfig();
		$array_files = array();

		// 指定モジュールを検索
		foreach( $conf->paths_module_template as $key=>$row ){
			$array_files[$key] = array();
			$array_files[$key] = array_merge( $array_files[$key], glob($row."**/**/module.css") );
			$array_files[$key] = array_merge( $array_files[$key], glob($row."**/**/module.css.scss") );
		}

		// ディレクトリからモジュールを検索
		$realpath_module_dir = @$conf->path_module_templates_dir;
		if( strlen($realpath_module_dir) && is_dir($realpath_module_dir) ){
			$ls = $this->px->fs()->ls($realpath_module_dir);
			sort($ls);
			foreach( $ls as $key ){
				if( !is_dir( $realpath_module_dir.'/'.$key.'/' ) ){
					continue;
				}
				if( @is_array( $array_files[$key] ) ){
					// 既に定義済みのモジュールパッケージIDは上書きしない。
					continue;
				}
				$array_files[$key] = array();
				$array_files[$key] = array_merge( $array_files[$key], glob($realpath_module_dir.'/'.$key.'/'."**/**/module.css") );
				$array_files[$key] = array_merge( $array_files[$key], glob($realpath_module_dir.'/'.$key.'/'."**/**/module.css.scss") );
			}
		}

		return $this->build_css_src( $array_files );
	}

	/**
	 * テーマが定義するドキュメントモジュール定義のスタイルシートを統合する
	 *
	 * @param string $theme_id テーマID
	 * @return string CSSコード
	 */
	public function build_theme_css( $theme_id ){
		$conf = $this->main->get_px2dtconfig();
		$array_files = array();
		if( !strlen( $theme_id ) ){
			return '';
		}

		$realpath_theme_collection_dir = $this->main->get_realpath_theme_collection_dir();
		if( !is_dir( $realpath_theme_collection_dir ) ){
			return '';
		}

		// ディレクトリからモジュールを検索
		$realpath_module_dir = @$realpath_theme_collection_dir.'/'.$theme_id.'/';
		if( strlen($realpath_module_dir) && is_dir($realpath_module_dir) ){
			$ls = $this->px->fs()->ls($realpath_module_dir);
			sort($ls);
			foreach( $ls as $key ){
				if( !is_dir( $realpath_module_dir.'/'.$key.'/' ) ){
					continue;
				}
				if( @is_array( $array_files[$key] ) ){
					// 既に定義済みのモジュールパッケージIDは上書きしない。
					continue;
				}
				$array_files[$key] = array();
				$array_files[$key] = array_merge( $array_files[$key], glob($realpath_module_dir.'/'.$key.'/'."**/**/module.css") );
				$array_files[$key] = array_merge( $array_files[$key], glob($realpath_module_dir.'/'.$key.'/'."**/**/module.css.scss") );
			}
		}

		return $this->build_css_src( $array_files );
	}

	/**
	 * CSSソースをビルドする
	 * @param array $array_files パッケージIDをキーにCSSファイルを格納した連想配列
	 * @return string ビルドされたCSSソース
	 */
	private function build_css_src( $array_files ){
		$rtn = '';
		foreach( $array_files as $packageId=>$array_files_row ){
			foreach( $array_files_row as $path ){
				preg_match( '/\/([a-zA-Z0-9\.\-\_]+?)\/([a-zA-Z0-9\.\-\_]+?)\/[a-zA-Z0-9\.\-\_]+?$/i', $path, $matched );
				$tmp_bin = $this->px->fs()->read_file( $path );
				if( $this->px->fs()->get_extension( $path ) == 'scss' ){
					$tmp_current_dir = realpath('./');
					chdir( dirname( $path ) );
					$scss = new \Leafo\ScssPhp\Compiler();
					$tmp_bin = $scss->compile( $tmp_bin );
					chdir( $tmp_current_dir );
				}

				$tmp_bin = $this->build_css_resources( $path, $tmp_bin );

				$tmp_bin = trim($tmp_bin);
				if(!strlen($tmp_bin)){
					unset($tmp_bin);
					continue;
				}

				$rtn .= '/**'."\n";
				$rtn .= ' * module: '.$packageId.':'.$matched[1].'/'.$matched[2]."\n";
				$rtn .= ' */'."\n";
				$rtn .= trim($tmp_bin)."\n"."\n";

				unset($tmp_bin);
			}
		}
		return trim($rtn)."\n";
	}

	/**
	 * CSSリソースをビルドする
	 * @param string $path CSSファイルのパス
	 * @param string $bin ビルド前のCSSコード
	 * @return string CSSコード
	 */
	private function build_css_resources( $path, $bin ){
		$rtn = '';
		while( 1 ){
			if( !preg_match( '/^(.*?)url\s*\\((.*?)\\)(.*)$/si', $bin, $matched ) ){
				$rtn .= $bin;
				break;
			}
			$rtn .= $matched[1];
			$rtn .= 'url("';
			$res = trim( $matched[2] );
			if( preg_match( '/^(\"|\')(.*)\1$/si', $res, $matched2 ) ){
				$res = trim( $matched2[2] );
			}
			$res = preg_replace('/#.*$/si', '', $res);
			$res = preg_replace('/\\?.*$/si', '', $res);
			if( is_file( dirname($path).'/'.$res ) ){
				$ext = $this->px->fs()->get_extension( dirname($path).'/'.$res );
				$ext = strtolower( $ext );
				$mime = 'image/png';
				switch( $ext ){
					// styles
					case 'css': $mime = 'text/css'; break;
					// images
					case 'png': $mime = 'image/png'; break;
					case 'gif': $mime = 'image/gif'; break;
					case 'jpg': case 'jpeg': case 'jpe': $mime = 'image/jpeg'; break;
					case 'svg': $mime = 'image/svg+xml'; break;
					// fonts
					case 'eot': $mime = 'application/vnd.ms-fontobject'; break;
					case 'woff': $mime = 'application/x-woff'; break;
					case 'otf': $mime = 'application/x-font-opentype'; break;
					case 'ttf': $mime = 'application/x-font-truetype'; break;
				}
				$res = 'data:'.$mime.';base64,'.base64_encode($this->px->fs()->read_file(dirname($path).'/'.$res));
			}
			$rtn .= $res;
			$rtn .= '")';
			$bin = $matched[3];
		}

		return $rtn;
	}

	/**
	 * ドキュメントモジュール定義のJavaScriptコードを統合する
	 *
	 * モジュール定義の情報から、JavaScriptコードを生成します。
	 * `$conf->plugins->px2dt->paths_module_template` および `$conf->plugins->px2dt->path_module_templates_dir` を参照します。
	 *
	 * スクリプトファイル(例: `/common/scripts/contents.js` など)に、下記のようにコードを記述します。
	 *
	 * ```
	 * <?php
	 * print (new \tomk79\pickles2\px2dthelper\main($px))->document_modules()->build_js();
	 * ?>
	 * ```
	 *
	 * @return string JavaScriptコード
	 */
	public function build_js(){
		$conf = $this->main->get_px2dtconfig();
		$array_files = array();

		// 指定モジュールを検索
		foreach( $conf->paths_module_template as $packageId=>$row ){
			$array_files[$packageId] = glob($row.'**/**/module.js');
		}
		// ディレクトリからモジュールを検索
		$realpath_module_dir = @$conf->path_module_templates_dir;
		if( strlen($realpath_module_dir) && is_dir($realpath_module_dir) ){
			$ls = $this->px->fs()->ls($realpath_module_dir);
			sort($ls);
			foreach( $ls as $packageId ){
				if( !is_dir( $realpath_module_dir.'/'.$packageId.'/' ) ){
					continue;
				}
				if( @is_array( $array_files[$packageId] ) ){
					// 既に定義済みのモジュールパッケージIDは上書きしない。
					continue;
				}
				$array_files[$packageId] = glob($realpath_module_dir.'/'.$packageId.'/**/**/module.js');
			}
		}

		return $this->build_js_src( $array_files );
	}

	/**
	 * テーマが定義するドキュメントモジュール定義のJavaScriptコードを統合する
	 *
	 * @param string $theme_id テーマID
	 * @return string JavaScriptコード
	 */
	public function build_theme_js( $theme_id ){
		$conf = $this->main->get_px2dtconfig();
		$array_files = array();
		if( !strlen( $theme_id ) ){
			return '';
		}

		$realpath_theme_collection_dir = $this->main->get_realpath_theme_collection_dir();
		if( !is_dir( $realpath_theme_collection_dir ) ){
			return '';
		}

		// ディレクトリからモジュールを検索
		$realpath_module_dir = @$realpath_theme_collection_dir.'/'.$theme_id.'/';
		if( strlen($realpath_module_dir) && is_dir($realpath_module_dir) ){
			$ls = $this->px->fs()->ls($realpath_module_dir);
			sort($ls);
			foreach( $ls as $packageId ){
				if( !is_dir( $realpath_module_dir.'/'.$packageId.'/' ) ){
					continue;
				}
				if( @is_array( $array_files[$packageId] ) ){
					// 既に定義済みのモジュールパッケージIDは上書きしない。
					continue;
				}
				$array_files[$packageId] = glob($realpath_module_dir.'/'.$packageId.'/**/**/module.js');
			}
		}

		return $this->build_js_src( $array_files );
	}

	/**
	 * JSソースをビルドする
	 * @param array $array_files パッケージIDをキーにJSファイルを格納した連想配列
	 * @return string ビルドされたJSソース
	 */
	private function build_js_src( $array_files ){
		$rtn = '';
		foreach( $array_files as $packageId=>$array_files_row ){
			foreach( $array_files_row as $path ){
				preg_match( '/\/([a-zA-Z0-9\.\-\_]+?)\/([a-zA-Z0-9\.\-\_]+?)\/[a-zA-Z0-9\.\-\_]+?$/i', $path, $matched );

				$tmp_bin = $this->px->fs()->read_file( $path );

				$tmp_bin = trim($tmp_bin);
				if(!strlen($tmp_bin)){
					unset($tmp_bin);
					continue;
				}

				$rtn .= '/**'."\n";
				$rtn .= ' * module: '.$packageId.':'.$matched[1].'/'.$matched[2]."\n";
				$rtn .= ' */'."\n";
				$rtn .= trim($tmp_bin)."\n"."\n";

				unset($tmp_bin);
			}
		}

		return trim($rtn)."\n";
	}
}
