<?php
/**
 * Test for pickles2\px2-px2dthelper
 */

class configParserTest extends PHPUnit_Framework_TestCase{

	/**
	 * setup
	 */
	public function setup(){
		set_time_limit(60);
		$this->fs = new \tomk79\filesystem();
		require_once(__DIR__.'/../php/simple_html_dom.php');
	}

	/**
	 * PX=px2dthelper.config.parse のテスト
	 */
	public function testConfigParser(){

		// ---------------------------
		// 設定値を取得
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.config.parse' ] );
		// var_dump($output);
		$json = json_decode( $output );
		// var_dump($json);
		$this->assertTrue( is_object($json) );
		$this->assertTrue( $json->result );
		$this->assertSame( $json->values->name, 'px2-px2dthelper-test' );
		$this->assertSame( $json->values->domain, null );
		$this->assertSame( $json->symbols->theme_id, 'pickles' );

		// ---------------------------
		// 上書きする
		$json = json_encode(array(
			'values'=>array(
				'name' => 'new site name',
				'domain' => 'example.com',
			),
			'symbols'=>array(
				'theme_id' => 'update_test',
			),
		));
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.config.update&base64_json='.urlencode(base64_encode($json)) ] );
		// var_dump($output);
		$json = json_decode( $output );
		// var_dump($json);
		$this->assertTrue( is_object($json) );
		$this->assertTrue( $json->result );
		$this->assertSame( $json->values->name, 'new site name' );
		$this->assertSame( $json->values->domain, 'example.com' );
		$this->assertSame( $json->symbols->theme_id, 'update_test' );

		// ---------------------------
		// 戻す
		$json = json_encode(array(
			'values'=>array(
				'name' => 'px2-px2dthelper-test',
				'domain' => null,
			),
			'symbols'=>array(
				'theme_id' => 'pickles',
			),
		));
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.config.update&base64_json='.urlencode(base64_encode($json)) ] );
		// var_dump($output);
		$json = json_decode( $output );
		// var_dump($json);
		$this->assertTrue( is_object($json) );
		$this->assertTrue( $json->result );
		$this->assertSame( $json->values->name, 'px2-px2dthelper-test' );
		$this->assertSame( $json->values->domain, null );
		$this->assertSame( $json->symbols->theme_id, 'pickles' );


		// ---------------------------
		// 設定値を再び取得
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.config.parse' ] );
		// var_dump($output);
		$json = json_decode( $output );
		// var_dump($json);
		$this->assertTrue( is_object($json) );
		$this->assertTrue( $json->result );
		$this->assertSame( $json->symbols->theme_id, 'pickles' );


		// 後始末
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	}//testConfigParser()




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
