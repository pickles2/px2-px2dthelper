<?php
/**
 * px2-px2dthelper.php
 */
namespace tomk79\pickles2\px2dthelper;

/**
 * entry.php
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

					$rtn .= $tmp_bin;
					unset($tmp_bin);
				}else{
					$rtn .= $tmp_bin;
				}
				$rtn .= "\n"."\n";
			}
		}
		return trim($rtn);
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

}
