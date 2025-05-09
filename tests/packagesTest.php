<?php
/**
 * Test for pickles2\px2-px2dthelper
 */

class packagesTest extends PHPUnit\Framework\TestCase{

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
	 * compser root
	 */
	public function testGettingComposerRootDir(){
		// get_path_composer_root_dir
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=px2dthelper.packages.get_path_composer_root_dir' ,
		] );
		$output = json_decode($output);
		$this->assertTrue( is_dir($output) );

		// 後始末
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );
	}

	/**
	 * npm root
	 */
	public function testGettingNpmRootDir(){
		// get_path_npm_root_dir
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=px2dthelper.packages.get_path_npm_root_dir' ,
		] );
		$output = json_decode($output);
		$this->assertFalse( $output );

		// 後始末
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );
	}

	/**
	 * テーマパッケージの一覧を取得するテスト
	 */
	public function testGettingThemePackageList(){

		// 単一の結果を得られるテスト
		$result = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=px2dthelper.packages.get_package_list' ,
		] );
		$result = json_decode($result);
		$this->assertTrue( is_object( $result ) );
		$this->assertEquals( count( $result->themes ), 0 );
		$this->assertEquals( count( $result->broccoliModules ), 4 );
		$this->assertEquals( count( $result->broccoliFields ), 4 );
		$this->assertEquals( count( $result->processors ), 2 );
		$this->assertEquals( count( $result->plugin ), 0 );
		$this->assertEquals( count( $result->projects ), 0 );

		// 後始末
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	}

}
