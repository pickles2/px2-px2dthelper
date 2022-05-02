<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper;

/**
 * cceAgent.php
 *
 * 拡張機能のバックエンドスクリプトへ、引数として提供されるユーティリティオブジェクトです。
 * このオブジェクトのインターフェイスは、拡張機能開発者に対して公開されます。
 */
class customConsoleExtensions_cceAgent{

	/**
	 * Custom Console Extension ID
	 */
	private $cce_id;

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
	public function __construct( $cce_id, $px, $main ){
		$this->cce_id = $cce_id;
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
		if( strlen(''.$appMode) ){
			$rtn = $appMode;
		}

        return $rtn;
    }

	/**
	 * 非同期の処理を実行する
	 * 
	 * 呼び出し元のPHPプロセスとは別の、非同期のプロセスを実行します。
	 * 実際には、px2dthelper自身には、非同期プロセス実行の能力はなく、
	 * 仲介するアプリケーション(babycorn や burdock)に、非同期処理のキックを依存します。
	 */
	public function async($command){
		require_once(__DIR__.'/async.php');
		$async = new customConsoleExtensions_async($this->cce_id, $this->px, $this->main);
		return $async->call( $command );
	}

	/**
	 * ブラウザへ非同期的なメッセージを配信する
	 * 
	 * `async()` と同様、px2dthelper自身には、メッセージを送信する能力はなく、
	 * 仲介するアプリケーション(babycorn や burdock)に、配信処理を依存します。
	 */
	public function broadcast($message, $scope = null){
		require_once(__DIR__.'/broadcast.php');
		$broadcast = new customConsoleExtensions_broadcast($this->cce_id, $this->px, $this->main);
		return $broadcast->call( $message, $scope );
	}

}
