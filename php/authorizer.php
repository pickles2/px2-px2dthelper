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
	 * @param object $px Picklesオブジェクト
	 * @param string $role ロール名
	 */
	static public function initialize( $px, $role = null ){
		if( $px->authorizer ){
			return false;
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
		if( $px->authorizer ){
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
	 * カレントユーザーに権限があるか確認する
	 *
	 * @param string $division 権限区分名
	 * @return boolean 許可される場合に true, 許可されない場合 false
	 */
	public function is_authorized( $division ){
		if( !strlen($this->role ?? '') ){
			return false;
		}
		switch($this->role){
			case "admin":
			case "specialist":
			case "member":
				break;
			default:
				return false;
		}
		$permission = $this->authorization_table->get($division);
		if( $permission === 1 || $permission === "1" || $permission === 'true' || $permission === 'yes' ){
			return true;
		}
		return false;
	}

}
