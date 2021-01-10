<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper;

/**
 * async.php
 */
class customConsoleExtensions_async{

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
	 * 非同期処理方法の設定を取得する
	 * @return object 設定オブジェクト
	 */
	public function get_config(){
		$config = new \stdClass();

		$realpath_config = trim($this->px->req()->get_param('asyncConfFile'));
		$param_asyncMethod = trim($this->px->req()->get_param('asyncMethod'));
		$param_asyncDir = trim($this->px->req()->get_param('asyncDir'));

		// JSONファイルに与えられている場合
		// 読み込んでデコードする
		if( strlen($realpath_config) && is_file($realpath_config) && is_readable($realpath_config) ){
			$config = json_decode(file_get_contents( $realpath_config ));
		}

		// --------------------
		// 連携方法
		// `file` => 指定されたディレクトリに、命令をファイルとして保存する。
		// `sync` => 非同期せず、直接同期実行する。 (default)
		if( !property_exists($config, 'method') || !strlen($config->method) ){
			$config->method = 'sync';
		}
		if( strlen($param_asyncMethod) ){
			$config->method = $param_asyncMethod;
		}

		// --------------------
		// 出力先ファイル
		// `method`=`file` の場合に、命令ファイルを出力する先のディレクトリパス。
		if( !property_exists($config, 'dir') || !strlen($config->dir) ){
			$config->dir = null;
		}
		if( strlen($param_asyncDir) ){
			$config->dir = $param_asyncDir;
		}

		return $config;
	}

	/**
	 * 非同期処理を呼び出す
	 */
	public function call( $command ){
		$command = json_decode(json_encode($command), true);
		if( !is_array($command) ){
			return false;
		}

		$config = $this->get_config();
		switch( $config->method ){
			case 'file':
				// ファイルで命令を伝える
				$realpath_dir = $config->dir;
				if( !strlen($realpath_dir) || !is_dir($realpath_dir) || !is_writable($realpath_dir) ){
					return false;
				}
				$realpath_dir = $this->px->fs()->get_realpath($realpath_dir.'/');

				$filename = '__async_command_'.date('Y-m-d-His').'_'.microtime(true).'_'.rand().'.json';
				$bin_command = json_encode( $command );
				$this->px->fs()->save_file($realpath_dir.$filename, $bin_command);

				return true;
				break;
			case 'sync':
			default:
				// 同期処理する
				// 非同期の処理にはならないが、外部の機能に依存せずに実行できる。
				return $this->run($command);
				break;
		}
		return true;
	}

	/**
	 * 非同期処理を実行する
	 */
	public function run($command){
		$command = json_decode(json_encode($command), true);
		if( !is_array($command) ){
			return false;
		}

		return true;
	}

}
