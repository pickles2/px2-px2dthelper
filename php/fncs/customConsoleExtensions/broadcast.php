<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper;

/**
 * broadcast.php
 */
class customConsoleExtensions_broadcast{

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
	 * 配信方法の設定を取得する
	 * @return object 設定オブジェクト
	 */
	public function get_config(){
		$config = new \stdClass();

		$realpath_config = trim($this->px->req()->get_param('broadcastConfFile'));
		$param_broadcastMethod = trim($this->px->req()->get_param('broadcastMethod'));
		$param_broadcastDir = trim($this->px->req()->get_param('broadcastDir'));

		// JSONファイルに与えられている場合
		// 読み込んでデコードする
		if( strlen($realpath_config) && is_file($realpath_config) && is_readable($realpath_config) ){
			$config = json_decode(file_get_contents( $realpath_config ));
		}

		// --------------------
		// 連携方法
		// `file` => 指定されたディレクトリに、命令をファイルとして保存する。(default)
		if( !property_exists($config, 'method') || !strlen($config->method) ){
			$config->method = 'file';
		}
		if( strlen($param_broadcastMethod) ){
			$config->method = $param_broadcastMethod;
		}

		// --------------------
		// 出力先ファイル
		// `method`=`file` の場合に、命令ファイルを出力する先のディレクトリパス。
		if( !property_exists($config, 'dir') || !strlen($config->dir) ){
			$config->dir = null;
		}
		if( strlen($param_broadcastDir) ){
			$config->dir = $param_broadcastDir;
		}

		return $config;
	}

	/**
	 * 非同期処理を呼び出す
	 */
	public function call($command){
		$command = json_decode(json_encode($command), true);
		if( !is_array($command) ){
			return false;
		}

		$config = $this->get_config();
		switch( $config->method ){
			case 'file':
				break;
		}
		return true;
	}
}
