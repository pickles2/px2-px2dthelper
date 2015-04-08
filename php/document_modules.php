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
	 */
	public function __construct( $px, $main ){
		$this->px = $px;
		$this->main = $main;
	}

	/**
	 * ドキュメントモジュール定義をロードする
	 */
	public function load(){
		$rtn = '';
		$rtn .= '<style type="text/css">'.$this->build_css().'</style>';
		$rtn .= '<script type="text/javascript">'.$this->build_js().'</script>';
		return $rtn;
	}

	/**
	 * ドキュメントモジュール定義のスタイルを統合
	 */
	public function build_css(){
		$conf = $this->main->get_px2dtconfig();
		$array_files = array();
		foreach( $conf->paths_module_template as $key=>$row ){
			$array_files[$key] = array();
			$array_files[$key] = array_merge( $array_files[$key], glob($row."**/**/module.css") );
			$array_files[$key] = array_merge( $array_files[$key], glob($row."**/**/module.css.scss") );
		}
		$rtn = '';
		foreach( $array_files as $packageId=>$array_files_row ){
			foreach( $array_files_row as $path ){
				preg_match( '/\/([a-zA-Z0-9\.\-\_]+?)\/([a-zA-Z0-9\.\-\_]+?)\/[a-zA-Z0-9\.\-\_]+?$/i', $path, $matched );
				$rtn .= '/**'."\n";
				$rtn .= ' * module: '.$packageId.':'.$matched[1].'/'.$matched[2]."\n";
				$rtn .= ' */'."\n";
				$tmp_bin = $this->px->fs()->read_file( $path );
				if( $this->px->fs()->get_extension( $path ) == 'scss' ){
					$tmp_current_dir = realpath('./');
					chdir( dirname( $path ) );
					$scss = new \scssc();
					$tmp_bin = $scss->compile( $tmp_bin );
					chdir( $tmp_current_dir );
				}

				$tmp_bin = $this->build_css_resources( $path, $tmp_bin );
					$rtn .= $tmp_bin;
				$rtn .= "\n"."\n";

				unset($tmp_bin);
			}
		}
		return trim($rtn);
	}
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
			if( is_file( dirname($path).'/'.$res ) ){
				$ext = $this->px->fs()->get_extension( dirname($path).'/'.$res );
				$ext = strtolower( $ext );
				$mime = 'image/png';
				switch( $ext ){
					case 'png': $mime = 'image/png'; break;
					case 'gif': $mime = 'image/gif'; break;
					case 'jpg': case 'jpeg': case 'jpe': $mime = 'image/jpeg'; break;
					case 'svg': $mime = 'image/svg+xml'; break;
				}
				$res = 'data:'.$mime.';base64,'.base64_encode($this->px->fs()->read_file(dirname($path).'/'.$res));
			}
			$rtn .= $res;
			$rtn .= '")';
			$bin = $matched[3];
		}
// var_dump($path);
		return $rtn;
	}

	/**
	 * ドキュメントモジュール定義のスクリプトを統合
	 */
	public function build_js(){
		$conf = $this->main->get_px2dtconfig();
		$array_files = array();
		foreach( $conf->paths_module_template as $row ){
			$array_files = array_merge( $array_files, glob($row."**/**/module.js") );
		}
		$rtn = '';
		foreach( $array_files as $path ){
			$rtn .= $this->px->fs()->read_file( $path );
		}
		return trim($rtn);
	}

	/**
	 * スタイルガイドを生成
	 */
	public function build_styleguide(){
		$conf = $this->main->get_px2dtconfig();
		$array_files = array();
		foreach( $conf->paths_module_template as $key=>$row ){
			$array_files[$key] = array();
			$array_files[$key] = array_merge( $array_files[$key], glob($row."**/**/template.html") );
		}
		$rtn = '';
		foreach( $array_files as $packageId=>$array_files_row ){
			$rtn .= '<h2>module: '.$packageId.'</h2>'."\n";
			foreach( $array_files_row as $path ){
				preg_match( '/\/([a-zA-Z0-9\.\-\_]+?)\/([a-zA-Z0-9\.\-\_]+?)\/[a-zA-Z0-9\.\-\_]+?$/i', $path, $matched );
				$rtn .= '<h3>'.$matched[1].'/'.$matched[2].'</h3>'."\n";
				$tmp_bin = $this->px->fs()->read_file( $path );
				$rtn .= $tmp_bin;
				$rtn .= '<pre style="margin:1em 0; overflow:auto; max-height:12em;">'.htmlspecialchars($tmp_bin).'</pre>';
				$rtn .= "\n"."\n";
			}
		}
		return trim($rtn);
	}

}
