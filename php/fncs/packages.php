<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper;

/**
 * packages.php
 */
class packages{

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
	 * テーマパッケージの一覧を取得する
	 * @return array テーマパッケージの一覧
	 */
	public function get_theme_package_list(){
		return array();
	}

	/**
	 * composer root を取得
	 * `composer.json` が設置されているディレクトリのパスを返します。
	 * @return string `composer.json` が設置されているディレクトリのパス
	 */
	public function get_path_composer_root_dir(){
		$cd = realpath('.');
		if(!is_dir($cd)){
			return false;
		}
		while(1){
			if( is_file($cd.'/composer.json') ){
				// 発見
				return $cd.DIRECTORY_SEPARATOR;
			}
			if( realpath($cd) == realpath('/') ){
				// もうルートディレクトリまで来てしまった
				return false;
			}
			$cd = realpath(dirname($cd));
		}
		return false;
	}

	/**
	 * npm root を取得
	 * `package.json` が設置されているディレクトリのパスを返します。
	 * @return string `package.json` が設置されているディレクトリのパス
	 */
	public function get_path_npm_root_dir(){
		$cd = realpath('.');
		if(!is_dir($cd)){
			return false;
		}
		while(1){
			if( is_file($cd.'/package.json') ){
				// 発見
				return $cd.DIRECTORY_SEPARATOR;
			}
			if( realpath($cd) == realpath('/') ){
				// もうルートディレクトリまで来てしまった
				return false;
			}
			$cd = realpath(dirname($cd));
		}
		return false;
	}

}
