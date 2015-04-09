<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper;

/**
 * field.markdown.php
 */
class field_markdown extends field_base{

	/**
	 * データをバインドする
	 */
	public function bind( $fieldData, $mode = 'finalize' ){
		$rtn = '';
		$rtn .= \Michelf\MarkdownExtra::defaultTransform( $fieldData );

		if( $mode == 'canvas' && !strlen($rtn) ){
			$rtn = '<span style="color:#999; background-color:#ddd; font-size:10px; padding:0 1em;">(ダブルクリックしてHTMLコードを編集してください)</span>';
		}
		return $rtn;
	}

}

