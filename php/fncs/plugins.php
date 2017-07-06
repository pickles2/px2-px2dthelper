<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper;

/**
 * plugins.php
 */
class plugins{

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
	 * プラグインオプションを得る
	 * @param  string $plugin_name Plugins Function Name
	 * @param  string $func Function division
	 * @return mixed Plugin Options
	 */
	public function get_plugin_options( $plugin_name, $func ){
		$rtn = array();
		$rtn['plugin_name'] = $plugin_name;
		$rtn['func'] = $func;
		return $rtn;
	}

}
