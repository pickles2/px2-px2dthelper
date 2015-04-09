<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper;

/**
 * field.text.php
 */
class field_text extends field_base{

	/**
	 * データをバインドする
	 */
	public function bind( $fieldData, $mode = 'finalize' ){
		$rtn = $fieldData;
		$rtn = htmlspecialchars( $rtn );
		$rtn = preg_replace( '/\r\n|\r|\n/', '<br />', $rtn );

		return $rtn;
	}

}
