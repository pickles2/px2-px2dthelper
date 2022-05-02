<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper;

/**
 * broadcast.php
 *
 * `$cceAgent` の機能の一部として呼び出され、非同期のブロードキャストを仲介します。
 * ブロードキャストは、WebSocketのような実装が期待され、呼び出し元アプリに依存します。
 * このオブジェクトは、メッセージをファイル等に保存し、呼び出し元へ伝達します。
 */
class customConsoleExtensions_broadcast{

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
	 * 配信方法の設定を取得する
	 * @return object 設定オブジェクト
	 */
	public function get_config(){
		$config = new \stdClass();

		$realpath_config = trim(''.$this->px->req()->get_param('broadcastConfFile'));
		$param_broadcastMethod = trim(''.$this->px->req()->get_param('broadcastMethod'));
		$param_broadcastDir = trim(''.$this->px->req()->get_param('broadcastDir'));

		// JSONファイルに与えられている場合
		// 読み込んでデコードする
		if( strlen($realpath_config) && is_file($realpath_config) && is_readable($realpath_config) ){
			$config = json_decode(file_get_contents( $realpath_config ));
		}

		// --------------------
		// 連携方法
		// `file` => 指定されたディレクトリに、命令をファイルとして保存する。(default)
		if( !property_exists($config, 'method') || !strlen($config->method) ){
			$config->method = 'file';
		}
		if( strlen($param_broadcastMethod) ){
			$config->method = $param_broadcastMethod;
		}

		// --------------------
		// 出力先ファイル
		// `method`=`file` の場合に、命令ファイルを出力する先のディレクトリパス。
		if( !property_exists($config, 'dir') || !strlen($config->dir) ){
			$config->dir = null;
		}
		if( strlen($param_broadcastDir) ){
			$config->dir = $param_broadcastDir;
		}

		return $config;
	}

	/**
	 * メッセージの配信処理を要求する
	 *
	 * @param array|object $message 配信する情報。
	 * @param string $scope 配信する範囲。
	 * - `self` = 実行者のみ (default)
	 * - `branch` = 同じブランチの編集者
	 * - `project` = 同じプロジェクトの編集者
	 * - `all` = 全編集者
	 *
	 * @return boolean 結果
	 */
	public function call( $message, $scope = null ){
		$message = json_decode(json_encode($message), true);
		if( !is_array($message) ){
			return false;
		}

		$config = $this->get_config();
		switch( $config->method ){
			case 'file':
			default:
				// ファイルで命令を伝える
				$realpath_dir = $config->dir;
				if( !strlen($realpath_dir) || !is_dir($realpath_dir) || !is_writable($realpath_dir) ){
					return false;
				}
				$realpath_dir = $this->px->fs()->get_realpath($realpath_dir.'/');

				$filename = '__broadcast_message_'.date('Y-m-d-His').'_'.microtime(true).'_'.rand().'.json';
				$bin_command = json_encode( $message );
				$this->px->fs()->save_file($realpath_dir.$filename, $bin_command);

				return true;
				break;
		}
		return true;
	}
}
