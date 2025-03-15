<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper;

/**
 * px2ce/apis.php
 */
class px2ce_apis{

	/** $px object */
	private $px;

	/** px2dthelper object */
	private $main;

	/**
	 * 編集対象のモード
	 * 'page_content' (default) or 'theme_layout'
	 */
	private $target_mode = 'page_content';

	/**
	 * 編集対象のテーマID
	 * $target_mode が 'theme_layout' の場合にセットされる。
	 */
	private $theme_id = null;

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
		$px2ce = $this->create_px2ce();
		switch($px_command_2){
			case 'gpi':
				$data = $this->px->req()->get_param('data');
				$data_filename = $this->px->req()->get_param('data_filename');
				if( strlen(''.$data_filename) ){
					if( strpos( $data_filename, '/' ) !== false || strpos( $data_filename, '\\' ) !== false || $data_filename == '.' || $data_filename == '.' ){
						// ディレクトリトラバーサル対策
						return false;
						break;
					}
				}
				$realpath_data_file = $this->px->get_realpath_homedir().'/_sys/ram/data/'.$data_filename;
				if( !strlen( ''.$data ) && strlen(''.$data_filename) && is_file( $realpath_data_file ) ){
					$data = file_get_contents( $realpath_data_file );
					$data = json_decode($data, true);
				}else{
					$data = json_decode(base64_decode($data), true);
				}
				$rtn = $px2ce->gpi( $data );
				return $rtn;
				break;
			case 'client_resources':
				$realpath_dist = null;
				if( $this->px->req()->is_cmd() ){
					// CLI
					if( $this->px->req()->get_param('dist') ){
						$realpath_dist = $this->px->req()->get_param('dist');
					}
				}else{
					$realpath_dist = $this->px->fs()->normalize_path($this->px->fs()->get_realpath( $this->px->realpath_plugin_files('/').'../__console_resources/px2ce/' ));
					$this->px->fs()->mkdir_r($realpath_dist);
				}

				$appearance = "auto";
				switch($this->px->req()->get_param('appearance')){
					case "auto":
					case "light":
					case "dark":
						$appearance = $this->px->req()->get_param('appearance');
						break;
				}
				$rtn = $px2ce->get_client_resources( $realpath_dist, array('appearance' => $appearance,) );

				$rtn->path_base = null;
				if( !$this->px->req()->is_cmd() ){
					$rtn->path_base = $this->px->fs()->normalize_path( $this->px->fs()->get_realpath( $this->px->path_plugin_files('/').'../__console_resources/px2ce/', '/' ) );
				}

				return $rtn;
				break;
		}
		return false;
	}

	/**
	 * $px2ce オブジェクトを生成する
	 * @return object $px2ce
	 */
	private function create_px2ce(){
		$px2ce = new \pickles2\libs\contentsEditor\main( $this->px );
		$appMode = $this->px->req()->get_param('appMode');
		if( !$appMode ){
			$appMode = 'web';
		}
		$target_mode = $this->px->req()->get_param('target_mode');
		if( !$target_mode ){
			$target_mode = 'page_content';
		}

		$init_options = array(
			'target_mode' => $target_mode,
			'page_path' => $this->px->req()->get_request_file_path(),
			'theme_id' => $this->px->req()->get_param('theme_id') ?? null,
			'layout_id' => $this->px->req()->get_param('layout_id') ?? null,
			'appMode' => $appMode,
			'entryScript' => $_SERVER['SCRIPT_FILENAME'],
			'customFields' => array() ,
			'log' => function($msg){
				$this->px->error($msg);
			},
		);

		$command_php = $this->px->req()->get_cli_option( '--command-php' );
		if( isset($command_php) && is_string($command_php) && strlen(''.$command_php) ){
			$init_options['php'] = $command_php;
		}elseif( isset($this->px->conf()->commands->php) && strlen($this->px->conf()->commands->php) ){
			$init_options['php'] = $this->px->conf()->commands->php;
		}
		$command_php_ini = $this->px->req()->get_cli_option( '-c' );
		if( isset($command_php_ini) && is_string($command_php_ini) && strlen(''.$command_php_ini) ){
			$init_options['php_ini'] = $command_php_ini;
		}

		$px2ce->init($init_options);
		return $px2ce;
	}

}
