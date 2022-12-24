<?php
/**
 * Test for pickles2\px2-px2dthelper
 */

class getListUnassignedContentsTest extends PHPUnit\Framework\TestCase{

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
	 * PX=px2dthelper.get.list_unassigned_contents のテスト
	 */
	public function testGetListUnassignedContents(){

		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.get.list_unassigned_contents' ] );
		$json = json_decode( $output );

		$this->assertTrue( is_object($json) );
		$this->assertTrue( $json->result );
		$this->assertTrue( is_array($json->unassigned_contents) );
		$this->assertSame( count($json->unassigned_contents), 6 );

		// 後始末
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	}

}
