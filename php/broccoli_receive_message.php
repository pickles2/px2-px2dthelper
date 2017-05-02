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
	 */
	static public function apply($px, $json){
		if( $px->is_publish_tool() ){
			// パブリッシュ時には何もしない。
			return true;
		}

		$enabled_origin = @$json->enabled_origin;
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

		$main_src = $px->bowl()->pull('main');
		$main_src .= $receive_message_script;
		$px->bowl()->replace( $main_src, 'main' );
		return true;
	}

}
