<?php
class customConsoleExtensionsTest0001{

	/** $px */
	private $px;

	/** $json */
	private $json;

	/** $cceAgent */
	private $cceAgent;

	/**
	 * Constructor
	 */
	public function __construct($px, $json, $cceAgent){
		$this->px = $px;
		$this->json = $json;
		$this->cceAgent = $cceAgent;
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
			case 'get-app-mode':
				return array(
					'result' => true,
					'message' => 'OK',
					'appMode' => $this->cceAgent->get_app_mode(),
				);
				break;
			case 'test-async':
				$this->cceAgent->async(array(
					'type' => 'gpi',
					'command' => 'test-async-run',
				));
				return array(
					'result' => true,
					'message' => 'OK',
				);
				break;
			case 'test-broadcast':
				$this->cceAgent->broadcast(array(
					'message' => 'This is a boroadcast message.',
				));
				return array(
					'result' => true,
					'message' => 'OK',
				);
				break;
		}
		return false;
	}
}