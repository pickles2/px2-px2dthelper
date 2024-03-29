<?php
/**
 * Test for pickles2\px2-px2dthelper
 */

class changeContentEditorModeTest extends PHPUnit\Framework\TestCase{

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
	 * コンテンツ編集モードを変更するテスト
	 */
	public function testChangeContentEditorMode(){

		// init content
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/init_content/html/test.html?PX=px2dthelper.init_content&editor_mode=html'
		] );
		$this->assertTrue( $this->fs->is_file( __DIR__.'/testData/standard/init_content/html/test.html' ) );
		$this->fs->save_file(
			__DIR__.'/testData/standard/init_content/html/test.html',
			'<p>HTML Content.</p>'
		);

		// PX=px2dthelper.change_content_editor_mode
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/init_content/html/test.html?PX=px2dthelper.change_content_editor_mode&editor_mode=md'
		] );
		// var_dump($output);
		$output = json_decode($output);
		// var_dump($output);
		clearstatcache();
		$this->assertEquals( gettype(array()), gettype($output) );
		$this->assertFalse( $this->fs->is_file( __DIR__.'/testData/standard/init_content/html/test.html' ) );
		$this->assertTrue( $this->fs->is_file( __DIR__.'/testData/standard/init_content/html/test.html.md' ) );

		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/init_content/html/test.html?PX=px2dthelper.change_content_editor_mode&editor_mode=html.gui'
		] );
		// var_dump($output);
		$output = json_decode($output);
		// var_dump($output);
		clearstatcache();
		$this->assertEquals( gettype(array()), gettype($output) );
		$this->assertTrue( $this->fs->is_file( __DIR__.'/testData/standard/init_content/html/test.html' ) );
		$this->assertFalse( $this->fs->is_file( __DIR__.'/testData/standard/init_content/html/test.html.md' ) );
		$this->assertTrue( $this->fs->is_file( __DIR__.'/testData/standard/init_content/html/test_files/guieditor.ignore/data.json' ) );
		$dataJson = $this->fs->read_file( __DIR__.'/testData/standard/init_content/html/test_files/guieditor.ignore/data.json' );
		// var_dump( $dataJson );
		$json = json_decode( $dataJson );
		// var_dump( $json );
		$this->assertEquals( '_sys/root', $json->bowl->main->modId );
		$this->assertEquals( '_sys/html', $json->bowl->main->fields->main[0]->modId );
		$this->assertEquals( '<p>HTML Content.</p>', $json->bowl->main->fields->main[0]->fields->main );

		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/init_content/html/test.html?PX=px2dthelper.change_content_editor_mode&editor_mode=html'
		] );
		// var_dump($output);
		$output = json_decode($output);
		// var_dump($output);
		clearstatcache();
		$this->assertEquals( gettype(array()), gettype($output) );
		$this->assertTrue( $this->fs->is_file( __DIR__.'/testData/standard/init_content/html/test.html' ) );
		$this->assertFalse( $this->fs->is_file( __DIR__.'/testData/standard/init_content/html/test.html.md' ) );
		$this->assertFalse( $this->fs->is_file( __DIR__.'/testData/standard/init_content/html/test_files/guieditor.ignore/data.json' ) );



		// 後始末
		$this->fs->rm(__DIR__.'/testData/standard/init_content/');
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );
	} // testInitializeContent()

	/**
	 * 変更対象のコンテンツが存在しない場合のテスト
	 */
	public function testChangeContentEditorModeContNotExists(){

		// PX=px2dthelper.change_content_editor_mode
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/init_content/content/not/exists.html?PX=px2dthelper.change_content_editor_mode&editor_mode=md'
		] );
		// var_dump($output);
		$output = json_decode($output);
		// var_dump($output);
		clearstatcache();
		$this->assertEquals( gettype(array()), gettype($output) );
		$this->assertFalse( $output[0] );
		$this->assertFalse( $this->fs->is_file( __DIR__.'/testData/standard/init_content/content/not/exists.html' ) );
		$this->assertFalse( $this->fs->is_file( __DIR__.'/testData/standard/init_content/content/not/exists.html.md' ) );



		// 後始末
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );
	} // testChangeContentEditorModeContNotExists()

	/**
	 * 変更前と変更後に同じモードをセットするテスト
	 */
	public function testChangeContentEditorModeNoChange(){

		// init content
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/init_content/html/test.html?PX=px2dthelper.init_content&editor_mode=html'
		] );

		// PX=px2dthelper.change_content_editor_mode
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/init_content/html/test.html?PX=px2dthelper.change_content_editor_mode&editor_mode=html'
		] );
		// var_dump($output);
		$output = json_decode($output);
		// var_dump($output);
		clearstatcache();
		$this->assertEquals( gettype(array()), gettype($output) );
		$this->assertFalse( $output[0] );
		$this->assertTrue( $this->fs->is_file( __DIR__.'/testData/standard/init_content/html/test.html' ) );



		// 後始末
		$this->fs->rm(__DIR__.'/testData/standard/init_content/');
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );
	} // testChangeContentEditorModeNoChange()

	/**
	 * editor_modeオプションを省略した場合のテスト
	 */
	public function testChangeContentEditorModeNoEditorModeOption(){

		// init content
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/init_content/html/test.html?PX=px2dthelper.init_content&editor_mode=html'
		] );

		// PX=px2dthelper.change_content_editor_mode
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/init_content/html/test.html?PX=px2dthelper.change_content_editor_mode'
		] );
		// var_dump($output);
		$output = json_decode($output);
		// var_dump($output);
		clearstatcache();
		$this->assertEquals( gettype(array()), gettype($output) );
		$this->assertFalse( $output[0] );
		$this->assertTrue( $this->fs->is_file( __DIR__.'/testData/standard/init_content/html/test.html' ) );



		// 後始末
		$this->fs->rm(__DIR__.'/testData/standard/init_content/');
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );
	} // testChangeContentEditorModeNoEditorModeOption()

}
