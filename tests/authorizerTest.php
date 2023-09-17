<?php
/**
 * Test for pickles2\px2-px2dthelper
 */

class authorizerTest extends PHPUnit\Framework\TestCase{

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

		require_once(__DIR__.'/testHelper/simple_html_dom.php');
	}

	/**
	 * Autorizer を初期化する
	 */
	public function testInitializeAuthorizer(){

		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/?PX=px2dthelper.authorizer.is_authorized.members',
		] );
		$output = json_decode($output);
		$this->assertTrue( is_object($output) );
		$this->assertFalse( $output->result );
		$this->assertFalse( $output->available );
		$this->assertNull( $output->role );
		$this->assertNull( $output->is_authorized );

		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'--role', 'admin',
			'/?PX=px2dthelper.authorizer.is_authorized.members',
		] );
		$output = json_decode($output);
		$this->assertTrue( is_object($output) );
		$this->assertTrue( $output->result );
		$this->assertTrue( $output->available );
		$this->assertSame( $output->role, "admin" );
		$this->assertTrue( $output->is_authorized );

		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'--role', 'member',
			'/?PX=px2dthelper.authorizer.is_authorized.members',
		] );
		$output = json_decode($output);
		$this->assertTrue( is_object($output) );
		$this->assertTrue( $output->result );
		$this->assertTrue( $output->available );
		$this->assertSame( $output->role, "member" );
		$this->assertFalse( $output->is_authorized );

		// 後始末
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	}

}
