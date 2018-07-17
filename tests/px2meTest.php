<?php
/**
 * Test for pickles2\px2-px2dthelper
 */

class px2meTest extends PHPUnit_Framework_TestCase{

	/**
	 * setup
	 */
	public function setup(){
		$this->fs = new \tomk79\filesystem();
		require_once(__DIR__.'/../php/simple_html_dom.php');
	}

	/**
	 * Pickles 2 Module Editor GPIのテスト
	 */
	public function testGpi(){
		// GPI
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/broccoli/.px_execute.php' ,
			'/guiedit/index.html?PX=px2dthelper.px2me.gpi&data='.urlencode(base64_encode(json_encode(
				array(
					'api' => 'getConfig'
				)
			))) ,
		] );
		// var_dump($output);
		$json = json_decode($output);
		// var_dump($json);
		$this->assertEquals( $json->appMode, 'web' );

		// 後始末
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/broccoli/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );
	}


	/**
	 * コマンドを実行し、標準出力値を返す
	 * @param array $ary_command コマンドのパラメータを要素として持つ配列
	 * @return string コマンドの標準出力値
	 */
	private function passthru( $ary_command ){
		set_time_limit(60*10);
		$cmd = array();
		foreach( $ary_command as $row ){
			$param = escapeshellcmd($row);
			array_push( $cmd, $param );
		}
		$cmd = implode( ' ', $cmd );
		ob_start();
		passthru( $cmd );
		$bin = ob_get_clean();
		set_time_limit(30);
		return $bin;
	}// passthru()

}
