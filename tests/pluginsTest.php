<?php
/**
 * Test for pickles2\px2-px2dthelper
 */

class pluginsTest extends PHPUnit_Framework_TestCase{

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
	 * broccoli-html-editor ビルドのテスト
	 */
	public function testGettingPluginOptions(){

		$result = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=px2dthelper.plugins.get_plugin_options&func_div=processor.html&plugin_name='.urlencode('tomk79\pickles2\px2dthelper\themes\pickles\theme::exec') ,
		] );
		// var_dump($result);
		$result = json_decode($result);
		var_dump($result);
		$this->assertTrue( is_object( $result ) );

		// 後始末
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	} // testGettingPluginOptions()

}
