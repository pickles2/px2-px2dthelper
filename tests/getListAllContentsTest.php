<?php
/**
 * Test for pickles2\px2-px2dthelper
 */

class getListAllContentsTest extends PHPUnit\Framework\TestCase{

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
	 * PX=px2dthelper.get.list_all_contents のテスト
	 */
	public function testGetListAllContents(){

		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.get.list_all_contents' ] );
		$json = json_decode( $output );

		$this->assertTrue( is_object($json) );
		$this->assertTrue( $json->result );
		$this->assertSame( count(get_object_vars($json->all_contents)), 20 );

		// 後始末
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );
	}
}
