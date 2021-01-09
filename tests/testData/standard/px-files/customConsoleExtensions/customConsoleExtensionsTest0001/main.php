<?php
class customConsoleExtensionsTest0001{

	/** $px */
	private $px;

	/** $json */
	private $json;

	/**
	 * Constructor
	 */
	public function __construct($px, $json){
		$this->px = $px;
		$this->json = $json;
	}

	/**
	 * 機能拡張の名前を取得する
	 */
	public function get_label(){
		return $this->json->label;
	}

	/**
	 * 機能拡張のクライアントサイド資材のベースディレクトリパスを取得する
	 */
	public function get_client_resource_base_dir(){
		return __DIR__.'/resources/';
	}

	/**
	 * 機能拡張のクライアントサイド資材一覧を取得する
	 */
	public function get_client_resource_list(){
		$rtn = array();
		$rtn['css'] = array('styles/cce0001.css');
		$rtn['js'] = array('scripts/cce0001.js');
		return $rtn;
	}

	/**
	 * クライアントサイドの初期化関数名を取得する
	 */
	public function get_client_initialize_function(){
		return 'window.customConsoleExtensionsTest0001';
	}

	/**
	 * General Purpose Interface
	 */
	public function gpi( $request ){
		switch( $request->command ){
			case 'test-command':
				return array(
					'result' => true,
					'message' => 'OK',
				);
				break;
		}
		return false;
	}
}