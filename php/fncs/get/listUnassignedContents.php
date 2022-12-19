<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper\fncs\get;

/**
 * fncs/get/listUnassignedContents.php
 */
class listUnassignedContents{

	/** Picklesオブジェクト */
	private $px;

	/** px2dthelperオブジェクト */
	private $px2dthelper;

	/**
	 * constructor
	 *
	 * @param object $px2dthelper $px2dthelperオブジェクト
	 * @param object $px $pxオブジェクト
	 */
	public function __construct( $px2dthelper, $px ){
		$this->px2dthelper = $px2dthelper;
		$this->px = $px;
	}

    /**
     * 未割当のコンテンツファイルの一覧を取得する
     */
    public function get_unassigned_contents(){
        $rtn = array();
        return $rtn;
    }
}
