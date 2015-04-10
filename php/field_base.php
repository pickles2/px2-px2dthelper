<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper;

/**
 * field_base.php
 */
class field_base{

	/**
	 * データをバインドする
	 */
	public function bind( $fieldData, $mode = 'finalize' ){
		$rtn = '';
		if( is_object($fieldData) ){
			foreach( $fieldData as $row ){
				$rtn .= $row;
			}
		}else{
			$rtn .= $fieldData;
		}
		if( $mode == 'canvas' && !strlen($rtn) ){
			$rtn = '<span style="color:#999; background-color:#ddd; font-size:10px; padding:0 1em;">(ダブルクリックしてHTMLコードを編集してください)</span>';
		}
		return $rtn;
	}

}