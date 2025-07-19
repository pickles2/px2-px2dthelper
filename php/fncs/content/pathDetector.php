<?php
/**
 * px2-px2dthelper pathDetector
 */
namespace tomk79\pickles2\px2dthelper\fncs\content;

/**
 * px2-px2dthelper pathDetector
 */
class pathDetector {

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
	 * Markdownファイル中のパスを解決
	 * 
	 * @param string $src 変換対象のMarkdownコード
	 * @param callback $get_new_path 変換前のパスを受け取り、変換後のパスを返すコールバック関数
	 * @return string 変換後のMarkdownコード
	 */
	public function path_detect_in_md( $src, $get_new_path ){

		// リンクとイメージを処理
		$tmp_src = $src;
		$src = '';

		while(1){
			if(!preg_match('/^(.*?)\[(.*?)\]\((.*?)(\s+".*?")?\)(.*)$/s', $tmp_src, $matched)){
				$src .= $tmp_src;
				break;
			}
			$src .= $matched[1];
			$label = $matched[2];
			$path = $get_new_path($matched[3]);
			$title = $matched[4];
			$tmp_src = $matched[5];

			$src .= '['.$label.']('.$path.$title.')';

			continue;
		}

		$src = $this->path_detect_in_html($src, $get_new_path);

		return $src;
	}

	/**
	 * HTMLファイル中のパスを解決
	 * 
	 * @param string $src 変換対象のHTMLコード
	 * @param callback $get_new_path 変換前のパスを受け取り、変換後のパスを返すコールバック関数
	 * @return string 変換後のHTMLコード
	 */
	public function path_detect_in_html( $src, $get_new_path ){

		// コメントブロックとPHPブロックを一時的に退避
		$placeholders = array();
		$idx = 0;

		// コメントブロック
		$src = preg_replace_callback('/<!--.*?-->/s', function($matches) use (&$placeholders, &$idx) {
			$key = "___COMMENT_BLOCK_PLACEHOLDER_{$idx}___";
			$placeholders[$key] = $matches[0];
			$idx++;
			return $key;
		}, $src);

		// PHPブロック
		$src = preg_replace_callback('/<\?(php|=)?[\s\S]*?\?>/i', function($matches) use (&$placeholders, &$idx) {
			$key = "___PHP_BLOCK_PLACEHOLDER_{$idx}___";
			$placeholders[$key] = $matches[0];
			$idx++;
			return $key;
		}, $src);

		// HTMLをパース
		$html = \tomk79\pickles2\px2dthelper\str_get_html(
			$src ,
			false, // $lowercase
			false, // $forceTagsClosed
			DEFAULT_TARGET_CHARSET, // $target_charset
			false, // $stripRN
			DEFAULT_BR_TEXT, // $defaultBRText
			DEFAULT_SPAN_TEXT // $defaultSpanText
		);

		if($html === false){
			// HTMLパースに失敗した場合、無加工のまま返す。
			return $src;
		}

		$conf_dom_selectors = array(
			'*[href]'=>'href',
			'*[src]'=>'src',
			'form[action]'=>'action',
		);

		foreach( $conf_dom_selectors as $selector=>$attr_name ){
			$ret = $html->find($selector);
			foreach( $ret as $retRow ){
				$val = $retRow->getAttribute($attr_name);
				$val = $get_new_path($val);
				$retRow->setAttribute($attr_name, $val);
			}
		}

		$ret = $html->find('*[style]');
		foreach( $ret as $retRow ){
			$val = $retRow->getAttribute('style');
			$val = str_replace('&quot;', '"', $val);
			$val = str_replace('&lt;', '<', $val);
			$val = str_replace('&gt;', '>', $val);
			$val = $this->path_detect_in_css($val, $get_new_path);
			$val = str_replace('"', '&quot;', $val);
			$val = str_replace('<', '&lt;', $val);
			$val = str_replace('>', '&gt;', $val);
			$retRow->setAttribute('style', $val);
		}

		$ret = $html->find('style');
		foreach( $ret as $retRow ){
			$val = $retRow->innertext;
			$val = $this->path_detect_in_css($val, $get_new_path);
			$retRow->innertext = $val;
		}

		$src = $html->outertext;

		// 一時的に退避したコメントブロックとPHPブロックを復元
		foreach( $placeholders as $key => $val ){
			$src = str_replace($key, $val, $src);
		}

		return $src;
	}

	/**
	 * CSSファイル中のパスを解決
	 * 
	 * @param string $src 変換対象のCSS(またはSCSS)コード
	 * @param callback $get_new_path 変換前のパスを受け取り、変換後のパスを返すコールバック関数
	 * @return string 変換後のCSS(またはSCSS)コード
	 */
	private function path_detect_in_css( $bin, $get_new_path ){

		$rtn = '';

		// url()
		while( 1 ){
			if( !preg_match( '/^(.*?)url\s*\\((.*?)\\)(.*)$/si', $bin, $matched ) ){
				$rtn .= $bin;
				break;
			}
			$rtn .= $matched[1];
			$rtn .= 'url("';
			$res = trim( $matched[2] );
			if( preg_match( '/^(\"|\')(.*)\1$/si', $res, $matched2 ) ){
				$res = trim( $matched2[2] );
			}
			$res = $get_new_path( $res );
			$rtn .= $res;
			$rtn .= '")';
			$bin = $matched[3];
		}

		// @import
		$bin = $rtn;
		$rtn = '';
		while( 1 ){
			if( !preg_match( '/^(.*?)@import\s*([^\s\;]*)(.*)$/si', $bin, $matched ) ){
				$rtn .= $bin;
				break;
			}
			$rtn .= $matched[1];
			$rtn .= '@import ';
			$res = trim( $matched[2] );
			if( !preg_match('/^url\s*\(/', $res) ){
				$rtn .= '"';
				if( preg_match( '/^(\"|\')(.*)\1$/si', $res, $matched2 ) ){
					$res = trim( $matched2[2] );
				}
				$res = $get_new_path( $res );
				$rtn .= $res;
				$rtn .= '"';
			}else{
				$rtn .= $res;
			}
			$bin = $matched[3];
		}

		return $rtn;
	}

}
