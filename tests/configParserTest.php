<?php
/**
 * Test for pickles2\px2-px2dthelper
 */

class configParserTest extends PHPUnit\Framework\TestCase{

	private $fs;
	private $px2query;

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
				'tagline' => 'new tagline',
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
		$this->assertSame( $json->values->tagline, 'new tagline' );
		$this->assertSame( $json->values->scheme, 'http' );
		$this->assertSame( $json->values->domain, 'example.com' );
		$this->assertSame( $json->values->copyright, 'new copyright' );
		$this->assertSame( $json->symbols->theme_id, 'update_test' );

		// ---------------------------
		// 戻す
		$json = json_encode(array(
			'values'=>array(
				'name' => 'px2-px2dthelper-test',
				'tagline' => 'Pickles 2 desktop tool helper',
				'scheme' => 'https',
				'domain' => null,
				'copyright' => 'Pickles Project',
			),
			'symbols'=>array(
				'theme_id' => 'pickles',
			),
		));
		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.config.update&base64_json='.urlencode(base64_encode($json)) ] );
		$json = json_decode( $output );
		$this->assertTrue( is_object($json) );
		$this->assertTrue( $json->result );
		$this->assertSame( $json->values->name, 'px2-px2dthelper-test' );
		$this->assertSame( $json->values->tagline, 'Pickles 2 desktop tool helper' );
		$this->assertSame( $json->values->scheme, 'https' );
		$this->assertSame( $json->values->domain, null );
		$this->assertSame( $json->values->copyright, 'Pickles Project' );
		$this->assertSame( $json->symbols->theme_id, 'pickles' );


		// ---------------------------
		// 設定値を再び取得
		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.config.parse' ] );
		$json = json_decode( $output );
		$this->assertTrue( is_object($json) );
		$this->assertTrue( $json->result );
		$this->assertSame( $json->values->name, 'px2-px2dthelper-test' );
		$this->assertSame( $json->values->scheme, 'https' );
		$this->assertSame( $json->values->domain, null );
		$this->assertSame( $json->values->copyright, 'Pickles Project' );
		$this->assertSame( $json->symbols->theme_id, 'pickles' );


		// ---------------------------
		// エラーパターン
		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.config.update&base64_json='.urlencode(base64_encode(json_encode(array(
			'values'=>array(
				'name' => "\r\n",
			),
		)))) ] );
		$json = json_decode( $output );
		$this->assertTrue( is_object($json) );
		$this->assertFalse( $json->result );

		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.config.update&base64_json='.urlencode(base64_encode(json_encode(array(
			'values'=>array(
				'copyright' => "\r\n",
			),
		)))) ] );
		$json = json_decode( $output );
		$this->assertTrue( is_object($json) );
		$this->assertFalse( $json->result );

		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.config.update&base64_json='.urlencode(base64_encode(json_encode(array(
			'values'=>array(
				'scheme' => 'ftp',
			),
		)))) ] );
		$json = json_decode( $output );
		$this->assertTrue( is_object($json) );
		$this->assertFalse( $json->result );


		// ---------------------------
		// 後始末
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );
	}

}
