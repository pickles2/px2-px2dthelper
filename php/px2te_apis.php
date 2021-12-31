<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper;

/**
 * px2te/apis.php
 */
class px2te_apis{

	/** $px object */
	private $px;

	/** px2dthelper object */
	private $main;

	/**
	 * constructor
	 * @param object $px Picklesオブジェクト
	 * @param object $main px2dthelper オブジェクト
	 */
	public function __construct($px, $main){
		$this->px = $px;
		$this->main = $main;
	}

	/**
	 * PXコマンドを実行する
	 * @param string $px_command_2 PXコマンドの第3引数(添字 2 に該当)
	 * @return mixed PXコマンドの実行結果
	 */
	public function execute_px_command($px_command_2){
		$px2te = $this->create_px2te();
		switch($px_command_2){
			case 'gpi':
				$data = $this->px->req()->get_param('data');
				$data_filename = $this->px->req()->get_param('data_filename');
				if( strlen($data_filename) ){
					if( strpos( $data_filename, '/' ) !== false || strpos( $data_filename, '\\' ) !== false || $data_filename == '.' || $data_filename == '.' ){
						// ディレクトリトラバーサル対策
						return false;
						break;
					}
				}
				$realpath_data_file = $this->px->get_realpath_homedir().'/_sys/ram/data/'.$data_filename;
				if( !strlen( $data ) && strlen($data_filename) && is_file( $realpath_data_file ) ){
					$data = file_get_contents( $realpath_data_file );
					$data = json_decode($data, true);
				}else{
					$data = json_decode(base64_decode($data), true);
				}
				$rtn = $px2te->gpi( $data );
				return $rtn;
				break;
			case 'client_resources':
				$rtn = $px2te->get_client_resources( $this->px->req()->get_param('dist') );
				return $rtn;
				break;
		}
		return false;
	} // execute_px_command()

	/**
	 * $px2te オブジェクトを生成する
	 * @return object $px2te
	 */
	private function create_px2te(){
		$current_page_info = $this->px->site()->get_current_page_info();
		$px2te = new \pickles2\libs\themeEditor\main( $this->px );
		$appMode = $this->px->req()->get_param('appMode');
		if( !$appMode ){
			$appMode = 'web';
		}

		$init_options = array(
			'appMode' => $appMode, // 'web' or 'desktop'. default to 'web'
			'entryScript' => $_SERVER['SCRIPT_FILENAME'],
			'commands' => array(
				'php' => array(),
			),
		);

		$command_php = $this->px->req()->get_cli_option( '--command-php' );
		if( isset($command_php) && is_string($command_php) && strlen($command_php) ){
			$init_options['commands']['php']['bin'] = $command_php;
		}
		$command_php_ini = $this->px->req()->get_cli_option( '-c' );
		if( isset($command_php_ini) && is_string($command_php_ini) && strlen($command_php_ini) ){
			$init_options['commands']['php']['ini'] = $command_php_ini;
		}

		// var_dump($init_options);
		$px2te->init($init_options);
		return $px2te;
	}

}
