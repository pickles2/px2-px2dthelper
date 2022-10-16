<?php
/**
 * Test for pickles2\px2-px2dthelper
 */

class initContentTest extends PHPUnit\Framework\TestCase{

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
	 * コンテンツファイル初期化のテスト
	 */
	public function testInitializeContent(){

		// PX=px2dthelper.init_content
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/init_content/html/test.html?PX=px2dthelper.init_content'
		] );
		// var_dump($output);
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( gettype(array()), gettype($output) );
		$this->assertEquals( $output[0], true );
		$this->assertTrue( $this->fs->is_file( __DIR__.'/testData/standard/init_content/html/test.html' ) );

		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/init_content/html/?PX=px2dthelper.init_content'
		] );
		// var_dump($output);
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( gettype(array()), gettype($output) );
		$this->assertEquals( $output[0], true );
		$this->assertTrue( $this->fs->is_file( __DIR__.'/testData/standard/init_content/html/index.html' ) );

		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/init_content/html.gui/?PX=px2dthelper.init_content&editor_mode=html.gui'
		] );
		// var_dump($output);
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( gettype(array()), gettype($output) );
		$this->assertEquals( $output[0], true );
		$this->assertTrue( $this->fs->is_file( __DIR__.'/testData/standard/init_content/html.gui/index.html' ) );
		$this->assertTrue( $this->fs->is_file( __DIR__.'/testData/standard/init_content/html.gui/index_files/guieditor.ignore/data.json' ) );

		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/init_content/md/?PX=px2dthelper.init_content&editor_mode=md'
		] );
		// var_dump($output);
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( gettype(array()), gettype($output) );
		$this->assertEquals( $output[0], true );
		$this->assertEquals( $output[1], 'ok' );
		$this->assertTrue( $this->fs->is_file( __DIR__.'/testData/standard/init_content/md/index.html.md' ) );


		// forceフラグのテスト
		$this->assertSame( 0, filesize( __DIR__.'/testData/standard/init_content/md/index.html.md' ) );

		file_put_contents(__DIR__.'/testData/standard/init_content/md/index.html.md', 'teststring');
		clearstatcache();

		$this->assertSame( 10, filesize( __DIR__.'/testData/standard/init_content/md/index.html.md' ) );

		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/init_content/md/?PX=px2dthelper.init_content&editor_mode=html'
		] );
		// var_dump($output);
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( gettype(array()), gettype($output) );
		$this->assertEquals( $output[0], false );
		$this->assertEquals( $output[1], 'Contents already exists.' );
		$this->assertFalse( is_file( __DIR__.'/testData/standard/init_content/md/index.html' ) );
		$this->assertSame( 10, filesize( __DIR__.'/testData/standard/init_content/md/index.html.md' ) );

		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/init_content/md/?PX=px2dthelper.init_content&editor_mode=html&force=1'
		] );
		// var_dump($output);
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( gettype(array()), gettype($output) );
		$this->assertEquals( $output[0], true );
		$this->assertEquals( $output[1], 'ok' );
		$this->assertTrue( is_file( __DIR__.'/testData/standard/init_content/md/index.html' ) );
		$this->assertFalse( is_file( __DIR__.'/testData/standard/init_content/md/index.html.md' ) );
		$this->assertSame( 0, filesize( __DIR__.'/testData/standard/init_content/md/index.html' ) );



		// 後始末
		$this->fs->rm(__DIR__.'/testData/standard/init_content/');
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );
	} // testInitializeContent()

}
