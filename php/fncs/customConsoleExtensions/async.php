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
	 * Custom Console Extension ID
	 */
	private $cce_id;

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
	public function __construct( $cce_id, $px, $main ){
		$this->cce_id = $cce_id;
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
				$bin_command = json_encode( array(
					'cce_id' => $this->cce_id,
					'command' => $command,
				) );
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

		if( !array_key_exists('type', $command) || !strlen($command['type']) ){
			$command['type'] = 'gpi';
		}
		if( !array_key_exists('command', $command) || !strlen($command['command']) ){
			$command['command'] = null;
		}
		if( !array_key_exists('request', $command) || !is_array($command['request']) ){
			$command['request'] = array();
		}
		if( !array_key_exists('params', $command) || !is_array($command['params']) ){
			$command['params'] = array();
		}

		switch( $command['type'] ){
			case 'gpi':
				$cce_id = $this->cce_id;
				if( !strlen($cce_id) ){
					return false;
				}
				$str_param = http_build_query( $command['params'] );

				$params = 'PX=px2dthelper.custom_console_extensions.'.urlencode($cce_id).'.gpi';
				$params .= '&request='.urlencode( json_encode( $command['request'] ) );
				$params .= (strlen($str_param) ? '&'.$str_param : '');
				$params .= '&appMode='.urlencode( $this->px->req()->get_param('appMode') );
				$params .= '&asyncMethod='.urlencode( $this->px->req()->get_param('asyncMethod') );
				$params .= '&asyncDir='.urlencode( $this->px->req()->get_param('asyncDir') );
				$params .= '&broadcastMethod='.urlencode( $this->px->req()->get_param('broadcastMethod') );
				$params .= '&broadcastDir='.urlencode( $this->px->req()->get_param('broadcastDir') );

				$src_out = $this->px->internal_sub_request(
					'/?'.$params,
					array(
						'output' => 'json',
					),
					$return_value
				);

				return $src_out;
				break;

			case 'pxcmd':
				$str_param = http_build_query( $command['params'] );
				if( !is_string($command['command']) || !strlen($command['command']) ){
					return false;
				}

				$params = 'PX='.urlencode($command['command']);
				$params .= (strlen($str_param) ? '&'.$str_param : '');

				$src_out = $this->px->internal_sub_request(
					'/?'.$params,
					array(
						'output' => 'json',
					),
					$return_value
				);

				return $src_out;
				break;

			case 'cmd':
				break;
		}

		return true;
	}

}
