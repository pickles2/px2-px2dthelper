<?php
/**
 * Test for pickles2\px2-px2dthelper
 */

class searchSitemapTest extends PHPUnit_Framework_TestCase{

	/**
	 * setup
	 */
	public function setup(){
		$this->fs = new \tomk79\filesystem();
	}

	/**
	 * ページを検索するテスト
	 */
	public function testSearchSitemap(){

		// PX=px2dthelper.init_content
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php',
			'/index.html?PX=px2dthelper.search_sitemap&keyword='.urlencode('Build by')
		] );
		// var_dump($output);
		$output = json_decode($output, true);
		// var_dump($output);
		$this->assertEquals( gettype(array()), gettype($output) );

		$this->assertEquals( count($output), 1 );
		$this->assertTrue( is_array($output[0]) );
		$this->assertEquals( $output[0]['title'], 'Build by PHP' );

		// 後始末
		$this->fs->rm(__DIR__.'/testData/standard/init_content/');
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );
	} // testSearchSitemap()




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
