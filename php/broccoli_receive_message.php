<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper;

/**
 * broccoli_receive_message.php
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

		$main_src .= self::generate_receive_message_script($plugin_conf);
		$main_src .= self::generate_error_message($px);

		$px->bowl()->replace( $main_src, 'main' );
	}

	/**
	 * RecieveMessageScript を生成する
	 * @param object $plugin_conf プラグイン設定オブジェクト
	 * @return string       生成されたHTMLソース
	 */
	private function generate_receive_message_script($plugin_conf){
		$enabled_origin = @$plugin_conf->enabled_origin;
		if( !is_array( $enabled_origin ) ){
			$enabled_origin = array();
		}
		ob_start();
?>
<script data-broccoli-receive-message="yes">
window.addEventListener('message',(function() {
return function f(event) {
<?php
if( count($enabled_origin) ){
	print 'if(';
	$tmp_origin = array();
	foreach($enabled_origin as $origin){
		array_push( $tmp_origin, 'event.origin!=\''.$origin.'\'' );
	}
	print implode($tmp_origin, ' && ');
	print '){ console.error(\'Unauthorized access.\');return;}';
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
	} // generate_receive_message_script()

	/**
	 * エラーメッセージを生成する
	 * @param  object $px Pickles Framework インスタンス
	 * @return string       生成されたHTMLソース
	 */
	private function generate_error_message($px){

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
			foreach( $errors as $idx=>$error ){
				$errorHtml .= '<li style="color: #f00; list-style-type: none;">'.htmlspecialchars($error).'</li>';
			}
			$errorHtml .= '</ul>';
		}

		$rtn = '';
		if( @strlen($errorHtml) ){
			$rtn .= '<div style="position: fixed; top: 10px; left: 5%; width: 90%; font-size: 14px; opacity: 0.8; z-index: 2147483000;" onclick="this.style.display=\'none\';">';
			$rtn .= $errorHtml;
			$rtn .= '</div>';
		}
		return $rtn;
	} // generate_receive_message_script()

}