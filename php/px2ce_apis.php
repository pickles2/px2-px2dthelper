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
				$rtn = $px2ce->gpi( $data );
				return $rtn;
				break;
			case 'client_resources':
				$rtn = $px2ce->get_client_resources( $this->px->req()->get_param('dist') );
				return $rtn;
				break;
		}
		return false;
	} // execute_px_command()

	/**
	 * $px2ce オブジェクトを生成する
	 * @return object $px2ce
	 */
	private function create_px2ce(){
		$current_page_info = $this->px->site()->get_current_page_info();
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
			'page_path' => $this->px->req()->get_request_file_path(), // <- 編集対象ページのパス
			'appMode' => $appMode, // 'web' or 'desktop'. default to 'web'
			'entryScript' => $_SERVER['SCRIPT_FILENAME'],
			'customFields' => array() ,
			'log' => function($msg){
				$this->px->error($msg);
			}
		);

		// --------------------------------------
		// カスタムフィールドを読み込む
		// プロジェクトが拡張するフィールド
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

		// var_dump($init_options);
		$px2ce->init($init_options);
		return $px2ce;
	}

}
