<?php
/**
 * Test for pickles2\px2-px2dthelper
 */

class getAllTest extends PHPUnit_Framework_TestCase{

	/**
	 * setup
	 */
	public function setup(){
		$this->fs = new \tomk79\filesystem();
		require_once(__DIR__.'/../php/simple_html_dom.php');
	}

	/**
	 * PX=px2dthelper.get.all のテスト
	 */
	public function testGetAll(){

		// Pickles 2 実行
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.get.all' ] );
		var_dump($output);
		$json = json_decode( $output );
		$this->assertTrue( is_object($json) );

		// Pickles 2 のバージョン番号を取得
		$output = json_decode($this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.version' ] ));
		$this->assertEquals( $json->version->pxfw, $output );

		// px2dthelper のバージョン番号を取得
		$output = json_decode($this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.version' ] ));
		$this->assertEquals( $json->version->px2dthelper, $output );

		// config を取得
		$output = json_decode($this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.config' ] ));
		$this->assertEquals( $json->config, $output );


		// 後始末
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	}//testGetAll()



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
