<?php
class customConsoleExtensionsTest0001{

	/** $px */
	private $px;

	/**
	 * Constructor
	 */
	public function __construct($px){
		$this->px = $px;
	}

	/**
	 * 機能拡張の名前を取得する
	 */
	public function get_label(){
		return '拡張機能0001';
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
		$rtn['js'] = array('styles/cce0001.js');
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
		return false;
	}
}