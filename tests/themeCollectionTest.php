<?php
/**
 * Test for pickles2\px2-px2dthelper
 */

class themeCollectionTest extends PHPUnit\Framework\TestCase{

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
	 * Advanced Config API (PX Command)のテスト
	 */
	public function testAdvancedConfigApi(){

		$output = $this->px2query->query( [
			__DIR__.'/testData/theme_collection/.px_execute.php',
			'/?PX=px2dthelper.get.realpath_theme_collection_dir'
		] );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertTrue( is_string($output) );
		$this->assertEquals( $this->fs->normalize_path($this->fs->get_realpath(__DIR__.'/testData/theme_collection/px-files/themes/')), $output );

		$output = $this->px2query->query( [
			__DIR__.'/testData/theme_collection/.px_execute.php',
			'/?PX=px2dthelper.get.path_theme_collection_dir'
		] );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertTrue( is_string($output) );
		$this->assertEquals( './px-files/themes/', $output );


		$output = $this->px2query->query( [
			__DIR__.'/testData/theme_collection/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	} // testAdvancedConfigApi()

	/**
	 * コンテンツファイルを検索するテスト
	 */
	public function testFindPageContent(){
		// find_page_content()
		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.find_page_content' ] );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( '/index.html', $output );

		// find_page_content()
		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/editor_modes/md.html?PX=px2dthelper.find_page_content' ] );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( '/editor_modes/md.html.md', $output );


		// 後始末
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );
	}

	/**
	 * 編集モードを調べるテスト
	 */
	public function testCheckEditorMode(){

		// check_editor_mode
		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.check_editor_mode' ] );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( 'html', $output );

		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/editor_modes/?PX=px2dthelper.check_editor_mode' ] );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( 'html', $output );

		$outputByPath = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.check_editor_mode&path=/editor_modes/index.html' ] );
		$outputByPath = json_decode($outputByPath);
		// var_dump($outputByPath);
		$this->assertEquals( $outputByPath, $output );

		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/editor_modes/html.html?PX=px2dthelper.check_editor_mode' ] );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( 'html', $output );

		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/editor_modes/htm.htm?PX=px2dthelper.check_editor_mode' ] );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( 'html', $output );

		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/editor_modes/gui.html?PX=px2dthelper.check_editor_mode' ] );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( 'html.gui', $output );

		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/editor_modes/md.html?PX=px2dthelper.check_editor_mode' ] );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( 'md', $output );

		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/editor_modes/not_exists.html?PX=px2dthelper.check_editor_mode' ] );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( '.not_exists', $output );

		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/editor_modes/page_not_exists.html?PX=px2dthelper.check_editor_mode' ] );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( '.page_not_exists', $output );

		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/px-files/?PX=px2dthelper.check_editor_mode' ] );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( '.page_not_exists', $output );


		// 後始末
		$output = $this->px2query->query( [
			__DIR__.'/testData/px2dt_config/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );
		$output = $this->px2query->query( [
			__DIR__.'/testData/px2dt_config/subapp/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	} // testCheckEditorMode()

}
