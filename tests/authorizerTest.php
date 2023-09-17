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

		$cd = realpath('.');
		chdir(__DIR__.'/testData/standard/');
		$px = new \picklesFramework2\px('./px-files/');

		$this->assertTrue( is_object($px) );
		$this->assertNull( $px->authorizer );

		$result = \tomk79\pickles2\px2dthelper\authorizer::initialize($px, 'specialist');

		$this->assertTrue( $result );
		$this->assertTrue( is_object($px) );
		$this->assertTrue( is_object($px->authorizer) );
		$this->assertSame( $px->authorizer->get_role(), 'specialist' );
		$this->assertFalse( $px->authorizer->is_authorized('members') );

		$result = \tomk79\pickles2\px2dthelper\authorizer::initialize($px, 'member'); // 2度目は実行されない

		$this->assertFalse( $result );
		$this->assertTrue( is_object($px) );
		$this->assertTrue( is_object($px->authorizer) );
		$this->assertSame( $px->authorizer->get_role(), 'specialist' );
		$this->assertFalse( $px->authorizer->is_authorized('members') );

		chdir($cd);

		// 後始末
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	}

	/**
	 * ロールを指定せずに Autorizer を初期化する
	 */
	public function testInitializeAuthorizerWithEmpty(){

		$cd = realpath('.');
		chdir(__DIR__.'/testData/standard/');
		$px = new \picklesFramework2\px('./px-files/');

		$this->assertTrue( is_object($px) );
		$this->assertNull( $px->authorizer );

		$result = \tomk79\pickles2\px2dthelper\authorizer::initialize($px);

		$this->assertTrue( $result );
		$this->assertTrue( is_object($px) );
		$this->assertTrue( is_bool($px->authorizer) );
		$this->assertFalse( $px->authorizer );

		$result = \tomk79\pickles2\px2dthelper\authorizer::initialize($px, 'member'); // 2度目は実行されない

		$this->assertFalse( $result );
		$this->assertTrue( is_object($px) );
		$this->assertTrue( is_bool($px->authorizer) );
		$this->assertFalse( $px->authorizer );

		chdir($cd);

		// 後始末
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	}

	/**
	 * Autorizer API のテスト
	 */
	public function testAuthorizerApi(){

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

	/**
	 * 定義されていないロールと権限をチェックする
	 */
	public function testUndefinedRole(){

		$cd = realpath('.');
		chdir(__DIR__.'/testData/standard/');
		$px = new \picklesFramework2\px('./px-files/');

		$this->assertTrue( is_object($px) );
		$this->assertNull( $px->authorizer );

		$result = \tomk79\pickles2\px2dthelper\authorizer::initialize($px, 'undefined_role');

		$this->assertTrue( $result );
		$this->assertTrue( is_object($px) );
		$this->assertTrue( is_object($px->authorizer) );
		$this->assertSame( $px->authorizer->get_role(), 'undefined_role' );
		$this->assertFalse( $px->authorizer->is_authorized('members') );
		$this->assertFalse( $px->authorizer->is_authorized('undefined_authority') );

		chdir($cd);

		// 後始末
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	}
}
