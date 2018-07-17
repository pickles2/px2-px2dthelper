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
	 * @return string       生成されたHTMLソース
	 */
	public function execute_px_command($px_command_2){
		$px2me = $this->create_px2me();
		switch($px_command_2){
			case 'gpi':
				$rtn = $px2me->gpi(
					json_decode(base64_decode($this->px->req()->get_param('data')), true)
				);
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
		$px2me = new \pickles2\libs\moduleEditor\main();
		$appMode = $this->px->req()->get_param('appMode');
		if( !$appMode ){
			$appMode = 'web';
		}

		$init_options = array(
			'page_path' => $this->px->req()->get_request_file_path(), // <- 編集対象ページのパス
			'appMode' => $appMode, // 'web' or 'desktop'. default to 'web'
			'entryScript' => $_SERVER['SCRIPT_NAME'],
			'customFields' => array() ,
			'log' => function($msg){
				$this->px->error($msg);
			}
		);


		// --------------------------------------
		// カスタムフィールドを読み込む
		$init_options['customFields'] = array();
		// プロジェクトが拡張するフィールド
		$confCustomFields = @$this->px->conf()->plugins->px2dt->guieditor->custom_fields;
		if( is_array($confCustomFields) ){
			foreach( $confCustomFields as $fieldName=>$field){
				if( $confCustomFields[$fieldName]->backend->require ){
					$path_backend_field = $this->px->fs()->normalize_path( $this->px->fs()->get_realpath( $confCustomFields[$fieldName]->backend->require ) );
					require_once( $path_backend_field );
				}
				if( $confCustomFields[$fieldName]->backend->class ){
					$init_options['customFields'] = $confCustomFields[$fieldName]->backend->class;
				}
			}
		}
		// var_dump($init_options['customFields']);

		// var_dump($init_options);
		$px2me->init($init_options);
		return $px2me;
	}

}
