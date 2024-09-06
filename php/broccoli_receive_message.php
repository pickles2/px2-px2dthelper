<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper;

/**
 * broccoli/receive_message.php
 */
class broccoli_receive_message{

	/**
	 * entry
	 *
	 * @param object $px Picklesオブジェクト
	 * @param object $plugin_conf プラグイン設定オブジェクト
	 * @return boolean 常に `true` を返します。
	 */
	static public function apply($px, $plugin_conf){
		if( $px->is_publish_tool() ){
			// パブリッシュ時には何もしない。
			return true;
		}

		$me = new self($px, $plugin_conf);
		return true;
	}

	/**
	 * constructor
	 * @param object $px Picklesオブジェクト
	 * @param object $plugin_conf プラグイン設定オブジェクト
	 */
	public function __construct($px, $plugin_conf){
		$main_src = $px->bowl()->pull('main');

		$px2ce_edit_mode = $px->req()->get_param('PICKLES2_CONTENTS_EDITOR');
		if( strlen( $px2ce_edit_mode ?? '' ) ){
			// Broccoli編集画面の実行を妨げるスクリプトを無害化
			$main_src = self::detoxify_sabotage_script($px, $main_src);

			// HTMLからコンテンツエリアを除去する
			if( $px2ce_edit_mode == 'broccoli' || $px2ce_edit_mode == 'broccoli.layout' ){
				if( strlen($px->conf()->plugins->px2dt->contents_area_selector ?? '') ){
					$main_src = self::remove_contents_area( $main_src, $px->conf()->plugins->px2dt->contents_area_selector ?? '' );
				}
			}

			// レイアウト編集への対応のための変換処理
			if( $px2ce_edit_mode == 'broccoli.layout' ){
				$main_src = self::remake_for_edit_theme_layout($px, $main_src);
			}

			// RecieveMessageScriptの生成と挿入
			$main_src .= self::generate_receive_message_script($plugin_conf);
		}

		$main_src .= self::generate_error_message($px);

		$px->bowl()->replace( $main_src, 'main' );
	}

	/**
	 * Broccoli編集画面の実行を妨げるスクリプトを無害化する
	 *
	 * @param object $px Pickles Object
	 * @param string $src HTMLソース
	 * @return string 変換されたHTMLソース
	 */
	private static function detoxify_sabotage_script($px, $src){
		// なぜかBroccoliをフリーズさせる外部のJS。
		// 無効化したら動くようになった。 (2019/4/22)
		$src = preg_replace( '/'.preg_quote('//platform.twitter.com/','/').'/', '//platform.twitter.com__/', $src );
		$src = preg_replace( '/'.preg_quote('//connect.facebook.net/','/').'/', '//connect.facebook.net__/', $src );
		return $src;
	}

	/**
	 * レイアウト編集への対応のための変換処理
	 *
	 * @param object $px Pickles Object
	 * @param string $src HTMLソース
	 * @return string 変換されたHTMLソース
	 */
	private static function remake_for_edit_theme_layout($px, $src){
		// bodyセクション全体を bowl で囲む
		$src = preg_replace('/(\<body.*?\>)/', '$1<div data-pickles2-theme-editor-contents-area="main">', $src);
		$src = preg_replace('/(\<\/body.*?\>)/', '</div>$1', $src);
		return $src;
	}

	/**
	 * RecieveMessageScript を生成する
	 * @param object $plugin_conf プラグイン設定オブジェクト
	 * @return string       生成されたHTMLソース
	 */
	private static function generate_receive_message_script($plugin_conf){
		$enabled_origin = $plugin_conf->enabled_origin ?? null;
		if( !is_array( $enabled_origin ) ){
			$enabled_origin = array();
		}
		ob_start();
?>
<script data-broccoli-receive-message="yes">
window.addEventListener('message',(function() {
return function f(event) {
if(!event.data.scriptUrl){return;}
<?php
if( count($enabled_origin) ){
	$tmp_origin = array();
	foreach($enabled_origin as $origin){
		if( !is_string($origin) || !strlen($origin) ){ continue; }
		array_push( $tmp_origin, 'event.origin!=\''.$origin.'\'' );
	}
	if( count($tmp_origin) ){
		print 'if(';
		print implode(' && ', $tmp_origin);
		print '){ console.error(\'Unauthorized access.\');return;}';
	}
}
?>

var s=document.createElement('script');
document.querySelector('body').appendChild(s);s.src=event.data.scriptUrl;
window.removeEventListener('message', f, false);
}
})(),false);
</script>
<?php
		$receive_message_script = ob_get_clean();

		return $receive_message_script;
	}

	/**
	 * エラーメッセージを生成する
	 * @param  object $px Pickles Framework インスタンス
	 * @return string       生成されたHTMLソース
	 */
	private static function generate_error_message($px){

		$errorHtml = '';
		$status = $px->get_status();
		if( $status != 200 ){
			$errorHtml .= '<ul style="background-color: #fee; border: 3px solid #f33; padding: 10px; margin: 0.5em; border-radius: 5px;">';
			$errorHtml .= '<li style="color: #f00; list-style-type: none;">STATUS: '.htmlspecialchars($status).' '.htmlspecialchars($px->get_status_message()).'</li>';
			$errorHtml .= '</ul>';
		}
		$errors = $px->get_errors();
		if( count($errors) ){
			$errorHtml .= '<ul style="background-color: #fee; border: 3px solid #f33; padding: 10px; margin: 0.5em; border-radius: 5px;">';
			$printed = array();
			foreach( $errors as $idx=>$error ){
				if( isset($printed[$error]) ){
					// 重複したエラーは、出力を省略する。
					continue;
				}
				$errorHtml .= '<li style="color: #f00; list-style-type: none;">'.htmlspecialchars($error).'</li>';
				$printed[$error] = true;
			}
			$errorHtml .= '</ul>';
		}

		$rtn = '';
		if( strlen( $errorHtml ) ){
			$rtn .= '<div style="position: fixed; top: 10px; left: 5%; width: 90%; font-size: 14px; opacity: 0.8; z-index: 2147483000;" onclick="this.style.display=\'none\';">';
			$rtn .= $errorHtml;
			$rtn .= '</div>';
		}
		return $rtn;
	}

	/**
	 * HTMLからコンテンツエリアを除去する
	 * 
	 * @param string $main_src HTMLソース
	 * @return string 変換されたHTMLソース
	 */
	private static function remove_contents_area($main_src, $contents_area_selector){

		$html = \tomk79\pickles2\px2dthelper\str_get_html(
			$main_src,
			false, // $lowercase
			false, // $forceTagsClosed
			DEFAULT_TARGET_CHARSET, // $target_charset
			false, // $stripRN
			DEFAULT_BR_TEXT, // $defaultBRText
			DEFAULT_SPAN_TEXT // $defaultSpanText
		);
		if( $html !== false ){
			$ret = $html->find($contents_area_selector.' script, '.$contents_area_selector.' style, '.$contents_area_selector.' iframe, '.$contents_area_selector.' frameset');
			foreach( $ret as $retRow ){
				$retRow->outertext = '';
			}
			$main_src = $html->outertext;
		}
		return $main_src;
	}

}
