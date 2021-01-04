<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper;

/**
 * pxcmd.php
 */
class customConsoleExtensions_pxcmdOperator{

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
	 * Custom Console Extensions 設定を取得する
	 * @return array 抜き出された設定値。設定されていない場合は `false` を返します。
	 */
	private function get_cce_conf(){
		$conf = $this->main->get_px2dtconfig();
		if( !is_object($conf) || !property_exists($conf, 'customConsoleExtensions') ){
			return false;
		}
		$ccEConf = (array) $conf->customConsoleExtensions;
		return $ccEConf;
	}

	/**
	 * 拡張機能の一覧を返す
	 * @return array 拡張機能の一覧
	 */
	public function get_list(){
		$ccEConf = $this->get_cce_conf();
		if( !is_array($ccEConf) && !is_object($ccEConf) ){
			return false;
		}

		$rtn = array();
		foreach($ccEConf as $cce_id=>$ccEInfo){
			$rtn[$cce_id] = array();
			$rtn[$cce_id]['id'] = $cce_id;
			$rtn[$cce_id]['label'] = $cce_id;
			$rtn[$cce_id]['className'] = null;
			if( is_string($ccEInfo) ){
				$rtn[$cce_id]['className'] = $ccEInfo;
			}
			$cceObj = $this->get($cce_id);
			if( !$cceObj ){
				continue;
			}
			$rtn[$cce_id]['label'] = $cceObj->get_label();
		}

		return $rtn;
	}

	/**
	 * 拡張機能を返す
	 * @return object 拡張機能。拡張機能をロードできない場合、 `false` を返します。
	 */
	public function get( $cce_id ){
		$ccEConf = $this->get_cce_conf();
		if( !is_array($ccEConf) && !is_object($ccEConf) ){
			return false;
		}

		$ccEInfo = null;
		if( is_array($ccEConf) ){
			if( !array_key_exists($cce_id, $ccEConf) ){
				return false;
			}
			$ccEInfo = $ccEConf[$cce_id];
		}elseif( is_object($ccEConf) ){
			if( !property_exists($ccEConf, $cce_id) ){
				return false;
			}
			$ccEInfo = $ccEConf->{$cce_id};
		}

		$className = null;
		if( is_string($ccEInfo) ){
			$className = $ccEInfo;
		}
		if( !strlen($className) ){
			return false;
		}
		if( !class_exists($ccEInfo) ){
			return false;
		}

		$rtn = new $ccEInfo( $this->px );

		return $rtn;
	}

}
