<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper\fncs\get;
use tomk79\pickles2\px2dthelper\fncs\packages;

/**
 * `PX=px2dthelper.get.list_gui_editor_contents`
 */
class listGuiEditorContents{

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
     * GUIエディターコンテンツの一覧を取得する
     */
    public function get_gui_editor_contents(){
		$rtn = (object) array();
		$rtn->result = true;

		// TODO: 実装する

        return $rtn;
    }
}
