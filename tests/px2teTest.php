<?php
/**
 * Test for pickles2\px2-px2dthelper
 */

class px2teTest extends PHPUnit\Framework\TestCase{

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
	 * Pickles 2 Theme Editor GPIのテスト
	 */
	public function testGpi(){
		// GPI
		$output = $this->px2query->query( [
			__DIR__.'/testData/broccoli/.px_execute.php' ,
			'/guiedit/index.html?PX=px2dthelper.px2te.gpi&data='.urlencode(base64_encode(json_encode(
				array(
					'api' => 'getBootupInformations'
				)
			))) ,
		] );
		// var_dump($output);
		$json = json_decode($output);
		// var_dump($json);
		$this->assertEquals( $json->conf->appMode, 'web' );

		// 後始末
		$output = $this->px2query->query( [
			__DIR__.'/testData/broccoli/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );
	}

	/**
	 * Pickles 2 Theme Editor GPIのテスト (data_filename でデータを転送する)
	 */
	public function testGpiDataFile(){
		$data = json_encode(
			array(
				'api' => 'getBootupInformations'
			)
		);
		$data_realpath = __DIR__.'/testData/broccoli/px-files/_sys/ram/data/testdata_filename';
		$this->fs->save_file( $data_realpath, $data );
		$this->assertTrue( is_file($data_realpath) );

		// GPI
		$output = $this->px2query->query( [
			__DIR__.'/testData/broccoli/.px_execute.php' ,
			'/guiedit/index.html?PX=px2dthelper.px2te.gpi&data_filename=testdata_filename' ,
		] );
		// var_dump($output);
		$json = json_decode($output);
		// var_dump($json);
		$this->assertEquals( $json->conf->appMode, 'web' );

		// GPI (ディレクトリトラバーサル対策により失敗する)
		$output = $this->px2query->query( [
			__DIR__.'/testData/broccoli/.px_execute.php' ,
			'/guiedit/index.html?PX=px2dthelper.px2te.gpi&data_filename=a/../testdata_filename' ,
		] );
		// var_dump($output);
		$json = json_decode($output);
		// var_dump($json);
		$this->assertFalse( $json );


		// 後始末
		unlink($data_realpath);
		clearstatcache();
		$this->assertFalse( is_file($data_realpath) );

		$output = $this->px2query->query( [
			__DIR__.'/testData/broccoli/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );
	}

	/**
	 * Pickles 2 Theme Editor のクライアントリソース取得
	 */
	public function testGetClientResources(){
		// client_resources
		$output = $this->px2query->query( [
			__DIR__.'/testData/broccoli/.px_execute.php' ,
			'/guiedit/index.html?PX=px2dthelper.px2te.client_resources' ,
		] );
		// var_dump($output);
		$json = json_decode($output);
		// var_dump($json);
		$this->assertTrue( is_array($json->css) );
		$this->assertTrue( is_array($json->js) );

		// client_resources
		$realpath_dir = __DIR__.'/testData/broccoli/caches/client_resources/';
		mkdir($realpath_dir);
		$output = $this->px2query->query( [
			__DIR__.'/testData/broccoli/.px_execute.php' ,
			'/guiedit/index.html?PX=px2dthelper.px2te.client_resources&dist='.urlencode($realpath_dir) ,
		] );
		// var_dump($output);
		$json = json_decode($output);
		// var_dump($json);
		$this->assertTrue( is_array($json->css) );
		$this->assertTrue( is_array($json->js) );

		// 後始末
		$output = $this->px2query->query( [
			__DIR__.'/testData/broccoli/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );
	}

}
