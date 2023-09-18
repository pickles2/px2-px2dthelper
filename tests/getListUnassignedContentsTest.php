<?php
/**
 * Test for pickles2\px2-px2dthelper
 */

class getListUnassignedContentsTest extends PHPUnit\Framework\TestCase{

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
	 * PX=px2dthelper.get.list_unassigned_contents のテスト
	 */
	public function testGetListUnassignedContents(){

		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.get.list_unassigned_contents' ] );
		$json = json_decode( $output );

		$this->assertTrue( is_object($json) );
		$this->assertTrue( $json->result );
		$this->assertTrue( is_array($json->unassigned_contents) );
		$this->assertSame( count($json->unassigned_contents), 7 );
		$this->assertTrue( array_search('/copy/from.html', $json->unassigned_contents) !== false );
		$this->assertTrue( array_search('/editor_modes/md.html.md', $json->unassigned_contents) !== false );
		$this->assertTrue( array_search('/unassigned/content.html.md', $json->unassigned_contents) !== false );
		$this->assertFalse( array_search('/unassigned/content_files/test.html', $json->unassigned_contents) );
		$this->assertFalse( array_search('/unassigned/ignored_test/ignore/ignoretest.html', $json->unassigned_contents) );
		$this->assertFalse( array_search('/unassigned/ignored_test/test2.ignore/ignoredcontents.html', $json->unassigned_contents) );
		$this->assertFalse( array_search('/unassigned/ignored_test/test.ignore.html.md', $json->unassigned_contents) );

		// 後始末
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	}

}
