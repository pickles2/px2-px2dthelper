<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper\fncs\customConsoleExtensions;

/**
 * async.php
 *
 * `$cceAgent` の機能の一部として呼び出され、非同期処理の実行を仲介します。
 * 通常、PHPで実行されたプロセスは非同期処理に対応しておらず、非常駐です。
 * このため、非同期処理の実際の実行は、呼び出し元アプリが別で呼び出すプロセスに委ねられることになります。
 * このオブジェクトは、命令をファイル等に保存し、呼び出し元へ伝達します。
 */
class async{

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
		$config->method = null;
		$config->dir = null;

		$realpath_config = null;
		$param_asyncMethod = null;
		$param_asyncDir = null;
		if( $this->px->req()->is_cmd() ){
			// CLI
			$realpath_config = trim(''.$this->px->req()->get_param('asyncConfFile'));
			$param_asyncMethod = trim(''.$this->px->req()->get_param('asyncMethod'));
			$param_asyncDir = trim(''.$this->px->req()->get_param('asyncDir'));
		}

		// JSONファイルに与えられている場合
		// 読み込んでデコードする
		if( strlen(''.$realpath_config) && is_file($realpath_config) && is_readable($realpath_config) ){
			$config = json_decode(file_get_contents( $realpath_config ));
		}

		// --------------------
		// 連携方法
		// `file` => 指定されたディレクトリに、命令をファイルとして保存する。
		// `sync` => 非同期せず、直接同期実行する。 (default)
		if( !isset($config->method) || !strlen(''.$config->method) ){
			$config->method = 'sync';
		}
		if( strlen(''.$param_asyncMethod) ){
			$config->method = $param_asyncMethod;
		}

		// --------------------
		// 出力先ファイル
		// `method`=`file` の場合に、命令ファイルを出力する先のディレクトリパス。
		if( !isset($config->dir) || !strlen(''.$config->dir) ){
			if( $config->method == 'file' ){
				$config->dir = $this->px->get_realpath_homedir().'_sys/ram/data/px2-px2dthelper/__cce/async/';
				$this->px->fs()->mkdir_r($config->dir);
			}
		}
		if( strlen(''.$param_asyncDir) ){
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
				if( !strlen(''.$realpath_dir) || !is_dir($realpath_dir) || !is_writable($realpath_dir) ){
					return false;
				}
				$realpath_dir = $this->px->fs()->get_realpath($realpath_dir.'/');
				$this->px->fs()->mkdir_r($realpath_dir);

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

		if( !array_key_exists('type', $command) || !strlen(''.$command['type']) ){
			$command['type'] = 'gpi';
		}
		if( !array_key_exists('command', $command) || !strlen(''.$command['command']) ){
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
				if( !strlen(''.$cce_id) ){
					return false;
				}
				$str_param = http_build_query( $command['params'] );

				$params = 'PX=px2dthelper.custom_console_extensions.'.urlencode(''.$cce_id).'.gpi';
				$params .= '&request='.urlencode( json_encode( $command['request'] ) );
				$params .= (strlen(''.$str_param) ? '&'.$str_param : '');
				if( $this->px->req()->is_cmd() ){
					// CLI
					$params .= '&appMode='.urlencode( ''.$this->px->req()->get_param('appMode') );
					$params .= '&asyncMethod='.urlencode( ''.$this->px->req()->get_param('asyncMethod') );
					$params .= '&asyncDir='.urlencode( ''.$this->px->req()->get_param('asyncDir') );
					$params .= '&broadcastMethod='.urlencode( ''.$this->px->req()->get_param('broadcastMethod') );
					$params .= '&broadcastDir='.urlencode( ''.$this->px->req()->get_param('broadcastDir') );
				}

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
				if( !is_string($command['command']) || !strlen(''.$command['command']) ){
					return false;
				}

				$params = 'PX='.urlencode($command['command']);
				$params .= (strlen(''.$str_param) ? '&'.$str_param : '');

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
