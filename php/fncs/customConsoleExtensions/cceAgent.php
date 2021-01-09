<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper;

/**
 * cceAgent.php
 */
class customConsoleExtensions_cceAgent{

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
     * アプリケーションのモードを調べる
     * @return string `web` または `desktop`
     */
    public function get_app_mode(){
		$rtn = 'desktop';
		$appMode = $this->px->req()->get_param('appMode');
		if( strlen($appMode) ){
			$rtn = $appMode;
		}

        return $rtn;
    }

}
