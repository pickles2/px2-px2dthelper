<?php
/**
 * Test for pickles2\px2-px2dthelper
 */

class beforeSitemapTest extends PHPUnit\Framework\TestCase{

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
	 * before_sitemap に設定されている場合の挙動
	 */
	public function testBeforeSitemap(){

		// PX=px2dthelper.version
		$output = $this->px2query->query( [
			__DIR__.'/testData/before_sitemap/.px_execute.php' ,
			'/?PX=px2dthelper.version' ,
		] );
		// var_dump($output);
		$version = json_decode($output);
		// var_dump( $version );
		$this->assertEquals( preg_match('/^\d+\.\d+\.\d+(?:\-.+)?(?:\+.+)?$/', $version), 1 );


		// PX=px2dthelper.check_status
		$output = $this->px2query->query( [
			__DIR__.'/testData/before_sitemap/.px_execute.php' ,
			'/?PX=px2dthelper.check_status' ,
		] );
		// var_dump($output);
		$output = json_decode($output);
		// var_dump( $output );
		$this->assertEquals( $version, $output->version );
		$this->assertFalse( $output->is_sitemap_loaded );


		// PX=px2dthelper.check_status
		$output = $this->px2query->query( [
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
		$output = $this->px2query->query( [
			__DIR__.'/testData/before_sitemap/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

		$this->assertFalse( is_dir(__DIR__.'/testData/before_sitemap/px-files/_sys/ram/caches/sitemaps/') );
		$this->assertFalse( is_dir(__DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/') );

	} // testCleanup()

}
