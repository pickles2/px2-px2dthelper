<?php
/**
 * Test for pickles2\px2-px2dthelper
 */

class configParserTest extends PHPUnit\Framework\TestCase{

	/**
	 * setup
	 */
	public function setup() : void{
		set_time_limit(60);
		$this->fs = new \tomk79\filesystem();
		require_once(__DIR__.'/testHelper/pickles2query.php');
		$this->px2query = new testHelper_pickles2query();
	}

	/**
	 * PX=px2dthelper.config.parse のテスト
	 */
	public function testConfigParser(){

		// ---------------------------
		// 設定値を取得
		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.config.parse' ] );
		$json = json_decode( $output );
		$this->assertTrue( is_object($json) );
		$this->assertTrue( $json->result );
		$this->assertSame( $json->values->name, 'px2-px2dthelper-test' );
		$this->assertSame( $json->values->domain, null );
		$this->assertSame( $json->values->copyright, 'Pickles Project' );
		$this->assertSame( $json->symbols->theme_id, 'pickles' );

		// ---------------------------
		// 上書きする
		$json = json_encode(array(
			'values'=>array(
				'name' => 'new site name',
				'scheme' => 'http',
				'domain' => 'example.com',
				'copyright' => 'new copyright',
			),
			'symbols'=>array(
				'theme_id' => 'update_test',
			),
		));
		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.config.update&base64_json='.urlencode(base64_encode($json)) ] );
		$json = json_decode( $output );
		$this->assertTrue( is_object($json) );
		$this->assertTrue( $json->result );
		$this->assertSame( $json->values->name, 'new site name' );
		$this->assertSame( $json->values->scheme, 'http' );
		$this->assertSame( $json->values->domain, 'example.com' );
		$this->assertSame( $json->values->copyright, 'new copyright' );
		$this->assertSame( $json->symbols->theme_id, 'update_test' );

		// ---------------------------
		// 戻す
		$json = json_encode(array(
			'values'=>array(
				'name' => 'px2-px2dthelper-test',
				'scheme' => 'https',
				'domain' => null,
				'copyright' => 'Pickles Project',
			),
			'symbols'=>array(
				'theme_id' => 'pickles',
			),
		));
		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.config.update&base64_json='.urlencode(base64_encode($json)) ] );
		// var_dump($output);
		$json = json_decode( $output );
		// var_dump($json);
		$this->assertTrue( is_object($json) );
		$this->assertTrue( $json->result );
		$this->assertSame( $json->values->name, 'px2-px2dthelper-test' );
		$this->assertSame( $json->values->scheme, 'https' );
		$this->assertSame( $json->values->domain, null );
		$this->assertSame( $json->values->copyright, 'Pickles Project' );
		$this->assertSame( $json->symbols->theme_id, 'pickles' );


		// ---------------------------
		// 設定値を再び取得
		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.config.parse' ] );
		// var_dump($output);
		$json = json_decode( $output );
		// var_dump($json);
		$this->assertTrue( is_object($json) );
		$this->assertTrue( $json->result );
		$this->assertSame( $json->values->name, 'px2-px2dthelper-test' );
		$this->assertSame( $json->values->scheme, 'https' );
		$this->assertSame( $json->values->domain, null );
		$this->assertSame( $json->values->copyright, 'Pickles Project' );
		$this->assertSame( $json->symbols->theme_id, 'pickles' );


		// 後始末
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	} // testConfigParser()

}
