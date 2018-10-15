<?php
/**
 * Test for pickles2\px2-px2dthelper
 */

class beforeSitemapTest extends PHPUnit_Framework_TestCase{

	/**
	 * setup
	 */
	public function setup(){
		$this->fs = new \tomk79\filesystem();
	}

	/**
	 * before_sitemap に設定されている場合の挙動
	 */
	public function testBeforeSitemap(){

		// PX=px2dthelper.version
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/before_sitemap/.px_execute.php' ,
			'/?PX=px2dthelper.version' ,
		] );
		// var_dump($output);
		$version = json_decode($output);
		// var_dump( $version );
		$this->assertEquals( preg_match('/^\d+\.\d+\.\d+(?:\-.+)?(?:\+.+)?$/', $version), 1 );


		// PX=px2dthelper.check_status
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/before_sitemap/.px_execute.php' ,
			'/?PX=px2dthelper.check_status' ,
		] );
		// var_dump($output);
		$output = json_decode($output);
		// var_dump( $output );
		$this->assertEquals( $version, $output->version );
		$this->assertFalse( $output->is_sitemap_loaded );


		// PX=px2dthelper.check_status
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=px2dthelper.check_status' ,
		] );
		// var_dump($output);
		$output = json_decode($output);
		// var_dump( $output );
		$this->assertEquals( $version, $output->version );
		$this->assertTrue( $output->is_sitemap_loaded );


	}//testBeforeSitemap()


	/**
	 * 後片付け
	 */
	public function testCleanup(){

		// 後始末
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/before_sitemap/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	}//testCleanup()



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
