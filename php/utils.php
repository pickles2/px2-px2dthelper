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
			$protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';
			$host = $_SERVER['HTTP_HOST'];

			// HTTP_HOSTにポート番号が含まれているかチェック
			if( strpos($host, ':') === false ){
				// ポート番号が含まれていない場合、必要に応じて追加
				$port = isset($_SERVER['SERVER_PORT']) ? (int)$_SERVER['SERVER_PORT'] : null;
				$default_port = ($protocol === 'https') ? 443 : 80;

				if( $port && $port !== $default_port ){
					$host .= ':' . $port;
				}
			}

			$origin = $protocol . '://' . $host;
		}
		return $origin;
	}
}
