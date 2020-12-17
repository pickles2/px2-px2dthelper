<?php
/**
 * Test for pickles2\px2-px2dthelper
 */

class px2teTest extends PHPUnit_Framework_TestCase{

	/**
	 * setup
	 */
	public function setup(){
		set_time_limit(60);
		$this->fs = new \tomk79\filesystem();
		require_once(__DIR__.'/../php/simple_html_dom.php');
	}

	/**
	 * Pickles 2 Theme Editor GPIのテスト
	 */
	public function testGpi(){
		// GPI
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/broccoli/.px_execute.php' ,
			'/guiedit/index.html?PX=px2dthelper.px2te.gpi&data='.urlencode(base64_encode(json_encode(
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
	 * Pickles 2 Theme Editor GPIのテスト (data_filename でデータを転送する)
	 */
	public function testGpiDataFile(){
		$data = json_encode(
			array(
				'api' => 'getConfig'
			)
		);
		$data_realpath = __DIR__.'/testData/broccoli/px-files/_sys/ram/data/testdata_filename';
		$this->fs->save_file( $data_realpath, $data );
		$this->assertTrue( is_file($data_realpath) );

		// GPI
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/broccoli/.px_execute.php' ,
			'/guiedit/index.html?PX=px2dthelper.px2te.gpi&data_filename=testdata_filename' ,
		] );
		// var_dump($output);
		$json = json_decode($output);
		// var_dump($json);
		$this->assertEquals( $json->appMode, 'web' );

		// GPI (ディレクトリトラバーサル対策により失敗する)
		$output = $this->passthru( [
			'php',
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

		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/broccoli/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );
	}

	/**
	 * Pickles 2 Theme Editor のクライアントリソース取得
	 */
	public function testGetClientResources(){
		// client_resources
		$output = $this->passthru( [
			'php',
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
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/broccoli/.px_execute.php' ,
			'/guiedit/index.html?PX=px2dthelper.px2te.client_resources&dist='.urlencode($realpath_dir) ,
		] );
		// var_dump($output);
		$json = json_decode($output);
		// var_dump($json);
		$this->assertTrue( is_array($json->css) );
		$this->assertTrue( is_array($json->js) );

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
