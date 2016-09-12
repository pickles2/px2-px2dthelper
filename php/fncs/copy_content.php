<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper;

/**
 * fncs/copy_content.php
 */
class fncs_copy_content{

	/** Picklesオブジェクト */
	private $px;

	/**
	 * constructor
	 *
	 * @param object $px $pxオブジェクト
	 */
	public function __construct( $px ){
		$this->px = $px;
	}

	/**
	 * コンテンツを複製する
	 * 
	 * @param  string $path_from コピー元のページパス (サイトマップの path 値)
	 * @param  string $path_to   コピー先のページパス (サイトマップの path 値)
	 * @return array `array(boolean $result, string $error_msg)`
	 */
	public function copy( $path_from, $path_to ){
		return array(true, 'ok');
	}

}
