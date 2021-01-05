<?php
/**
 * Test for pickles2\px2-px2dthelper
 */

class customConsoleExtensionsTest extends PHPUnit_Framework_TestCase{

	/**
	 * setup
	 */
	public function setup(){
		set_time_limit(60);
		$this->fs = new \tomk79\filesystem();
		require_once(__DIR__.'/../php/simple_html_dom.php');
	}

	/**
	 * PX=px2dthelper.custom_console_extensions のテスト
	 */
	public function testCustomConsoleExtensions(){

		// ----------------------------------------
		// PX Command: Custom Console Extensions の一覧取得
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.custom_console_extensions' ] );
		// var_dump($output);
		$json = json_decode( $output );
		// var_dump($json);
		$this->assertTrue( is_object($json) );
		$this->assertSame( $json->customConsoleExtensionsTest0001->id, 'customConsoleExtensionsTest0001' );
		$this->assertSame( $json->customConsoleExtensionsTest0001->label, '拡張機能0001' );

		// ----------------------------------------
		// PX Command: Custom Console Extensions 存在しない拡張機能の情報取得
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.custom_console_extensions.undefinedCustomConsoleExtension' ] );
		// var_dump($output);
		$json = json_decode( $output );
		// var_dump($json);
		$this->assertTrue( is_object($json) );
		$this->assertFalse( $json->result );
		$this->assertSame( $json->message, 'Custom Console Extension "undefinedCustomConsoleExtension" is NOT available.' );

		// ----------------------------------------
		// PX Command: Custom Console Extensions 拡張機能0001の情報取得
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.custom_console_extensions.customConsoleExtensionsTest0001' ] );
		// var_dump($output);
		$json = json_decode( $output );
		// var_dump($json);
		$this->assertTrue( is_object($json) );
		$this->assertSame( $json->id, 'customConsoleExtensionsTest0001' );
		$this->assertSame( $json->label, '拡張機能0001' );

		// ----------------------------------------
		// PX Command: Custom Console Extensions のクライアント資材一覧取得
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.custom_console_extensions.customConsoleExtensionsTest0001.client_resources' ] );
		// var_dump($output);
		$json = json_decode( $output );
		// var_dump($json);
		$this->assertTrue( is_object($json) );
		$this->assertSame( $json->css[0], realpath(__DIR__.'/testData/standard/px-files/customConsoleExtensions/customConsoleExtensionsTest0001/resources/styles/cce0001.css') );
		$this->assertSame( $json->js[0], realpath(__DIR__.'/testData/standard/px-files/customConsoleExtensions/customConsoleExtensionsTest0001/resources/scripts/cce0001.js') );
		$this->assertSame( false, is_file(__DIR__.'/testData/standard/px-files/_sys/ram/caches/tmpResTest/'.$json->css[0]) );
		$this->assertSame( false, is_file(__DIR__.'/testData/standard/px-files/_sys/ram/caches/tmpResTest/'.$json->js[0]) );

		// ----------------------------------------
		// PX Command: Custom Console Extensions のクライアント資材一覧取得 (出力先を指定した場合)
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.custom_console_extensions.customConsoleExtensionsTest0001.client_resources&dist='.__DIR__.'/testData/standard/px-files/_sys/ram/caches/tmpResTest/' ] );
		// var_dump($output);
		$json = json_decode( $output );
		// var_dump($json);
		$this->assertTrue( is_object($json) );
		$this->assertSame( $json->css[0], 'styles/cce0001.css' );
		$this->assertSame( $json->js[0], 'scripts/cce0001.js' );
		$this->assertSame( true, is_file(__DIR__.'/testData/standard/px-files/_sys/ram/caches/tmpResTest/'.$json->css[0]) );
		$this->assertSame( true, is_file(__DIR__.'/testData/standard/px-files/_sys/ram/caches/tmpResTest/'.$json->js[0]) );


		// ----------------------------------------
		// PX Command: Custom Console Extensions のGPIを呼び出す
		$this->fs->save_file(__DIR__.'/testData/standard/px-files/_sys/ram/data/tmp_request.txt', 'PX=px2dthelper.custom_console_extensions.customConsoleExtensionsTest0001.gpi&request='.urlencode(json_encode(array('command'=>'test-command'))));
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '--method', 'post', '--body-file', 'tmp_request.txt', '/' ] );
		// var_dump($output);
		$json = json_decode( $output );
		// var_dump($json);
		$this->assertTrue( is_object($json) );
		$this->assertSame( true, $json->result );
		$this->assertSame( 'OK', $json->message );
		$this->fs->rm(__DIR__.'/testData/standard/px-files/_sys/ram/data/tmp_request.txt');


		// 後始末
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	} // testCustomConsoleExtensions()




	/**
	 * コマンドを実行し、標準出力値を返す
	 * @param array $ary_command コマンドのパラメータを要素として持つ配列
	 * @return string コマンドの標準出力値
	 */
	private function passthru( $ary_command ){
		$cmd = array();
		foreach( $ary_command as $row ){
			$param = '"'.addslashes($row).'"';
			array_push( $cmd, $param );
		}
		$cmd = implode( ' ', $cmd );
		ob_start();
		passthru( $cmd );
		$bin = ob_get_clean();
		return $bin;
	}// passthru()

}
