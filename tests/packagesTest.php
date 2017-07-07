<?php
/**
 * Test for pickles2\px2-px2dthelper
 */

class packagesTest extends PHPUnit_Framework_TestCase{

	private $fs;
	private $px2query;

	/**
	 * setup
	 */
	public function setup(){
		$this->fs = new \tomk79\filesystem();
		require_once(__DIR__.'/testHelper/pickles2query.php');
		$this->px2query = new testHelper_pickles2query();
	}

	/**
	 * テーマパッケージの一覧を取得するテスト
	 */
	public function testGettingThemePackageList(){

		// 単一の結果を得られるテスト
		$result = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=px2dthelper.packages.get_theme_package_list' ,
		] );
		// var_dump($result);
		$result = json_decode($result);
		// var_dump($result);
		$this->assertTrue( is_array( $result ) );
		$this->assertEquals( count( $result ), 0 );

		// 後始末
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	} // testGettingThemePackageList()

}
