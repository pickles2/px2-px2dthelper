<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper;

/**
 * customConsoleExtensions.php
 */
class customConsoleExtensions{

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
	public function __construct( $px, $main ){
		$this->px = $px;
		$this->main = $main;
	}

	/**
	 * 拡張機能を返す
     * @return object 拡張機能。拡張機能をロードできない場合、 `false` を返します。
	 */
    public function get( $cce_id ){
        // TODO: 開発中
        return false;
    }

	/**
	 * 拡張機能の一覧を返す
     * @return array 拡張機能の一覧
	 */
    public function get_list(){
        // TODO: 開発中
        return array();
    }

}
