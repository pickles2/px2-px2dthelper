<?php
/**
 * Test for pickles2\px2-px2dthelper
 */

class publishSinglePageTest extends PHPUnit\Framework\TestCase{

	private $fs;
	private $path_dist;

	/**
	 * setup
	 */
	public function setup() : void{
		set_time_limit(60);
		$this->fs = new \tomk79\filesystem();
		require_once(__DIR__.'/testHelper/pickles2query.php');
		$this->px2query = new testHelper_pickles2query();

		$this->path_dist = __DIR__.'/testData/publish/px-files/dist/';
	}

	/**
	 * コンテンツ編集モードを変更するテスト
	 */
	public function testChangeContentEditorMode(){
		$this->clear_dist_dir();
		clearstatcache();
		$this->assertFalse( $this->fs->is_file( $this->path_dist.'index.html' ) );

		// publish /index.html
		$output = $this->px2query->query( [
			__DIR__.'/testData/publish/.px_execute.php',
			'/?PX=px2dthelper.publish_single_page'
		] );
		$this->assertTrue( $this->fs->is_file( $this->path_dist.'index.html' ) );
		$this->assertTrue( $this->fs->is_dir( $this->path_dist.'index_files/' ) );
		$this->assertTrue( $this->fs->is_file( $this->path_dist.'index_files/resource.txt' ) );
		$this->assertFalse( $this->fs->is_file( $this->path_dist.'sub_dir_1/index.html' ) );
		$this->assertFalse( $this->fs->is_dir( $this->path_dist.'sub_dir_1/index_files/' ) );

		// publish /sub_dir_1/1-1.html
		$output = $this->px2query->query( [
			__DIR__.'/testData/publish/.px_execute.php',
			'/sub_dir_1/1-1.html?PX=px2dthelper.publish_single_page'
		] );
		$this->assertTrue( $this->fs->is_file( $this->path_dist.'index.html' ) );
		$this->assertTrue( $this->fs->is_dir( $this->path_dist.'index_files/' ) );
		$this->assertTrue( $this->fs->is_file( $this->path_dist.'index_files/resource.txt' ) );
		$this->assertTrue( $this->fs->is_file( $this->path_dist.'sub_dir_1/1-1.html' ) );
		$this->assertTrue( $this->fs->is_dir( $this->path_dist.'sub_dir_1/1-1_files/' ) );
		$this->assertTrue( $this->fs->is_file( $this->path_dist.'sub_dir_1/1-1_files/res.txt' ) );
		$this->assertFalse( $this->fs->is_file( $this->path_dist.'sub_dir_1/index.html' ) );
		$this->assertFalse( $this->fs->is_dir( $this->path_dist.'sub_dir_1/index_files/' ) );



		// 後始末
		$output = $this->px2query->query( [
			__DIR__.'/testData/publish/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );
	} // testInitializeContent()




	/**
	 * dist フォルダを空にする
	 * @param array $ary_command コマンドのパラメータを要素として持つ配列
	 * @return string コマンドの標準出力値
	 */
	private function clear_dist_dir(){
		$this->fs->rm($this->path_dist);
		$this->fs->mkdir($this->path_dist);
		return true;
	}// clear_dist_dir()

}
