<?php
/**
 * processor test
 */
namespace tomk79\plugin_sample;

/**
 * processor test
 */
class test{

	/**
	 * 変換処理の実行
	 * @param object $px Picklesオブジェクト
	 * @param object $json プラグインオプション
	 */
	public static function exec1( $px, $json ){
		return true;
	}

	/**
	 * 変換処理の実行
	 * @param object $px Picklesオブジェクト
	 * @param object $json プラグインオプション
	 */
	public static function exec2( $px, $json ){
		return true;
	}

	/**
	 * 変換処理の実行
	 * @param object $px Picklesオブジェクト
	 * @param object $json プラグインオプション
	 */
	public static function exec3( $px, $json ){
		return true;
	}
}
