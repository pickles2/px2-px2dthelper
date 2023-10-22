<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper;

/**
 * px2me/apis.php
 */
class px2me_apis{

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
		$px2me = $this->create_px2me();
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
				$rtn = $px2me->gpi( $data );
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
					$realpath_dist = $this->px->fs()->normalize_path($this->px->fs()->get_realpath( $this->px->realpath_plugin_files('/').'../__console_resources/px2me/' ));
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
				$rtn = $px2me->get_client_resources( $realpath_dist, array('appearance' => $appearance,) );

				$rtn->path_base = null;
				if( !$this->px->req()->is_cmd() ){
					$rtn->path_base = $this->px->fs()->normalize_path( $this->px->fs()->get_realpath( $this->px->path_plugin_files('/').'../__console_resources/px2me/', '/' ) );
				}

				return $rtn;
				break;
		}
		return false;
	} // execute_px_command()

	/**
	 * $px2me オブジェクトを生成する
	 * @return object $px2me
	 */
	private function create_px2me(){
		$current_page_info = $this->px->site()->get_current_page_info();
		$px2me = new \pickles2\libs\moduleEditor\main( $this->px );
		$appMode = $this->px->req()->get_param('appMode');
		if( !$appMode ){
			$appMode = 'web';
		}

		$init_options = array(
			'page_path' => $this->px->req()->get_request_file_path(), // <- 編集対象ページのパス
			'appMode' => $appMode, // 'web' or 'desktop'. default to 'web'
			'entryScript' => $_SERVER['SCRIPT_FILENAME'],
			'customFields' => array() ,
			'log' => function($msg){
				$this->px->error($msg);
			},
			'commands' => array(
				'php' => array(),
			),
		);

		$command_php = $this->px->req()->get_cli_option( '--command-php' );
		if( isset($command_php) && is_string($command_php) && strlen(''.$command_php) ){
			$init_options['commands']['php']['bin'] = $command_php;
		}elseif( isset($this->px->conf()->commands->php) && strlen($this->px->conf()->commands->php) ){
			$init_options['commands']['php']['bin'] = $this->px->conf()->commands->php;
		}
		$command_php_ini = $this->px->req()->get_cli_option( '-c' );
		if( isset($command_php_ini) && is_string($command_php_ini) && strlen(''.$command_php_ini) ){
			$init_options['commands']['php']['ini'] = $command_php_ini;
		}

		// --------------------------------------
		// カスタムフィールドを読み込む
		// $init_options['customFields'] = array();
		// // プロジェクトが拡張するフィールド
		// $confCustomFields = @$this->px->conf()->plugins->px2dt->guieditor->custom_fields;
		// if( is_array($confCustomFields) ){
		// 	foreach( $confCustomFields as $fieldName=>$field){
		// 		if( $confCustomFields[$fieldName]->backend->require ){
		// 			$path_backend_field = $this->px->fs()->normalize_path( $this->px->fs()->get_realpath( $confCustomFields[$fieldName]->backend->require ) );
		// 			require_once( $path_backend_field );
		// 		}
		// 		if( $confCustomFields[$fieldName]->backend->class ){
		// 			$init_options['customFields'] = $confCustomFields[$fieldName]->backend->class;
		// 		}
		// 	}
		// }
		// var_dump($init_options['customFields']);

		// var_dump($init_options);
		$px2me->init($init_options);
		return $px2me;
	}

}
