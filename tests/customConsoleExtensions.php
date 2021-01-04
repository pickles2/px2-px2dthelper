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

		// Pickles 2 実行
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.custom_console_extensions' ] );
		// var_dump($output);
		$json = json_decode( $output );
		// var_dump($json);
		$this->assertTrue( is_object($json) );
		$this->assertSame( $json->customConsoleExtensionsTest0001->id, 'customConsoleExtensionsTest0001' );
		$this->assertSame( $json->customConsoleExtensionsTest0001->label, '拡張機能0001' );

		// Pickles 2 実行
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.custom_console_extensions.customConsoleExtensionsTest0001.client_resources' ] );
		// var_dump($output);
		$json = json_decode( $output );
		// var_dump($json);
		$this->assertTrue( is_object($json) );
		$this->assertSame( $json->css[0], realpath(__DIR__.'/testData/standard/px-files/customConsoleExtensions/customConsoleExtensionsTest0001/resources/styles/cce0001.css') );
		$this->assertSame( $json->js[0], realpath(__DIR__.'/testData/standard/px-files/customConsoleExtensions/customConsoleExtensionsTest0001/resources/scripts/cce0001.js') );

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
