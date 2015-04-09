<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper;

/**
 * field.href.php
 */
class field_href extends field_base{

	/**
	 * データをバインドする
	 */
	public function bind( $fieldData, $mode = 'finalize' ){
		$rtn = $fieldData;
		$rtn = htmlspecialchars($rtn);
		return $rtn;
	}

}
