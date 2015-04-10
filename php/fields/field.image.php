<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper;

/**
 * field.image.php
 */
class field_image extends field_base{

	/**
	 * データをバインドする
	 */
	public function bind( $fieldData, $mode = 'finalize' ){
		$rtn = new \stdClass();
		if( is_object($fieldData) ){
			$rtn = $fieldData;
		}
		// $res = _resMgr.getResource( rtn.resKey );
		// if( mode == 'finalize' ){
		// 	rtn.path = _resMgr.getResourcePublicPath( rtn.resKey );
		// }
		// if( mode == 'canvas' ){
		// 	rtn.path = 'data:'+res.type+';base64,' + res.base64;

		// 	if( !res.base64 ){
		// 		// ↓ ダミーの Sample Image
		// 		rtn.path = _imgDummy;
		// 	}
		// }
		return $rtn->path;
	}

}