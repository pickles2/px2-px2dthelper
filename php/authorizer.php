<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper;

/**
 * authorizer.php
 */
class authorizer {

	/** Picklesオブジェクト */
	private $px;

	/** 認可テーブル */
	private $authorization_table;

	/** ロール名 */
	private $role = null;

	/**
	 * $px->authorizer を生成する
	 *
	 * Clover型のCMSでは、このメソッドは外部から `$role` を伴ってコールされます。
	 * 通常は、パラメータ `$role` は省略されます。
	 * babycorn や Burdock のような、コマンドラインで呼び出す種類のCMSでは、
	 * 引数の代わりに、コマンドラインオプション `--role` でロール名を受け取ります。
	 *
	 * この関数は、結果として `$px->authorizer` を生成します。
	 * 一度生成された `$px->authorizer` は、上書きできず、あとから状態を変更することはできません。
	 *
	 * @param object $px Picklesオブジェクト
	 * @param string $role ロール名
	 * @return boolean 成功時に true, 失敗時に false
	 */
	static public function initialize( $px, $role = null ){
		if( !is_null($px->authorizer) ){
			return false;
		}

		if( $px->req()->is_cmd() ){
			$cli_param_role = $px->req()->get_cli_option('--role');
			if( strlen($cli_param_role ?? '') ){
				$role = $cli_param_role;
			}
		}

		if( !strlen($role ?? '') ){
			$px->authorizer = false;
			return true;
		}

		$px->authorizer = new self($px, $role);
		return true;
	}

	/**
	 * Constructor
	 *
	 * @param object $px $pxオブジェクト
	 * @param string $role ロール名
	 */
	private function __construct($px, $role = null){
		if( !is_null($px->authorizer) ){
			return false;
		}

		$this->px = $px;
		$this->role = $role;

		// 認可テーブル
		// NOTE: LangBank を利用した。
		$this->authorization_table = new \tomk79\LangBank(__DIR__.'/../data/authorization.csv');
		$this->authorization_table->setLang( $this->role );
	}

	/**
	 * ロール名を取得する
	 *
	 * @return string ロール名
	 */
	public function get_role(){
		return $this->role;
	}

	/**
	 * カレントユーザーに権限があるか確認する
	 *
	 * @param string $authority_name 権限名
	 * @return boolean 許可される場合に true, 許可されない場合 false
	 */
	public function is_authorized( $authority_name ){
		if( !strlen($this->role ?? '') ){
			return false;
		}
		switch($this->role){
			case "admin":
			case "member":
				break;
			default:
				return false;
		}
		$permission = $this->authorization_table->get($authority_name);
		if( $permission === 1 || $permission === "1" || $permission === 'true' || $permission === 'yes' ){
			return true;
		}
		return false;
	}

}
