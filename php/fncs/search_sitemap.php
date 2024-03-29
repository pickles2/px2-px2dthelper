<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper\fncs;

/**
 * fncs/search_sitemap.php
 */
class search_sitemap{

	/** Picklesオブジェクト */
	private $px;

	/** px2dthelperオブジェクト */
	private $px2dthelper;

	/**
	 * constructor
	 *
	 * @param object $px2dthelper $px2dthelperオブジェクト
	 * @param object $px $pxオブジェクト
	 */
	public function __construct( $px2dthelper, $px ){
		$this->px2dthelper = $px2dthelper;
		$this->px = $px;
	}

	/**
	 * サイトマップ中のページを検索する
	 *
	 * @param  string $keyword キーワード
	 * @param  array $options オプション
	 * @return array 検出されたページの一覧
	 */
	public function find( $keyword, $options = array() ){
		$rtn = array();
		if( !$this->px->site() ){
			return array();
		}

		$keyword = ''.$keyword;
		if( !strlen(''.$keyword) ){
			return array();
		}


		$options = json_decode(json_encode($options), true);
		if(!is_array($options)){
			$options = array();
		}
		if( !array_key_exists('limit', $options) ){
			$options['limit'] = 200;
		}
		if( !is_null($options['limit']) ){
			$options['limit'] = intval($options['limit']);
		}


		$sitemap = $this->px->site()->get_sitemap();
		foreach($sitemap as $page){
			if( !is_null($options['limit']) && count($rtn) >= $options['limit'] ){
				break;
			}
			foreach($page as $key=>$val){
				if(
					$key == 'logical_path'
					|| $key == 'layout'
					|| $key == 'orderby'
					|| $key == 'keywords'
					|| $key == 'description'
					|| $key == 'role'
					|| $key == 'proc_type'
					|| preg_match( '/^.*(?:\_|\-|\.)(?:flg|flag)$/si', $key)
					|| preg_match( '/^(?:flg|flag)(?:\_|\-|\.).*$/si', $key)
				){
					// 検索対象のカラムを制限する。
					// 例えば、 ページパスやページIDで検索したとき、
					// logical_path に含まれるので、下層ページ全部がヒットしてしまう。
					// そうした意図しない検索対象を除外するための処理。
					// ただし、ユーザーが拡張した任意の名称のカラムは検索対象としたいので、
					// ブラックリスト処理とした。
					continue;
				}
				if( @preg_match('/'.preg_quote($keyword, '/').'/s', $val) ){
					array_push($rtn, $page);
					continue 2;
				}
			}
		}

		return $rtn;
	}

}
