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
		set_time_limit(60);
		$this->fs = new \tomk79\filesystem();
		require_once(__DIR__.'/testHelper/pickles2query.php');
		$this->px2query = new testHelper_pickles2query();
	}

	/**
	 * プラグインオプションを取得するテスト
	 */
	public function testGettingPluginOptions(){

		// 単一の結果を得られるテスト
		$result = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=px2dthelper.plugins.get_plugin_options&func_div=processor.html&plugin_name='.urlencode('tomk79\pickles2\multitheme\theme::exec') ,
		] );
		$result = json_decode($result);
		$this->assertTrue( is_array( $result ) );
		$this->assertEquals( count( $result ), 1 );
		$this->assertEquals( $result[0]->options->default_theme_id, 'pickles' );

		// 複数の結果が得られるテスト
		// (func_divオプションをつけない = $conf->funcs 内を横断的に検索する)
		$result = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=px2dthelper.plugins.get_plugin_options&plugin_name='.urlencode('tomk79\plugin_sample\test::exec3') ,
		] );
		$result = json_decode($result);
		$this->assertTrue( is_array( $result ) );
		$this->assertEquals( count( $result ), 6 );
		$this->assertEquals( $result[0]->options->ext, 'html' );
		$this->assertEquals( $result[3]->options->test_value, 'test()' );
		$this->assertTrue( is_array($result[5]->options) );

		// 後始末
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	} // testGettingPluginOptions()

}
