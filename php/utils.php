<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper;

/**
 * utils.php
 */
class utils{

	/**
	 * サーバーの ORIGIN を取得する
	 */
	static public function get_server_origin(){
		$origin = null;
		if( isset($_SERVER['HTTP_HOST']) ){
			$origin = ( isset($_SERVER['HTTPS']) ? 'https' : 'http' ).'://'.$_SERVER['HTTP_HOST'];
		}
		return $origin;
	}
}
