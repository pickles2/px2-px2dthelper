<?php
/**
 * Test for pickles2\px2-px2dthelper
 */

class searchSitemapTest extends PHPUnit\Framework\TestCase{

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
	 * ページを検索するテスト
	 */
	public function testSearchSitemap(){

		// PX=px2dthelper.init_content
		$output = $this->px2query->query( [
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
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );
	} // testSearchSitemap()

	/**
	 * 件数制限付きでページを検索するテスト
	 */
	public function testSearchSitemapLimited(){

		// PX=px2dthelper.init_content
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/index.html?PX=px2dthelper.search_sitemap&keyword='.urlencode('/').'&limit=2'
		] );
		// var_dump($output);
		$output = json_decode($output, true);
		// var_dump($output);
		$this->assertEquals( gettype(array()), gettype($output) );

		$this->assertEquals( count($output), 2 );
		$this->assertTrue( is_array($output[0]) );

		// 後始末
		$this->fs->rm(__DIR__.'/testData/standard/init_content/');
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );
	} // testSearchSitemapLimited()

}
