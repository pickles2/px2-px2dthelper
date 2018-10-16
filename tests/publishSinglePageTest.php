<?php
/**
 * Test for pickles2\px2-px2dthelper
 */

class publishSinglePageTest extends PHPUnit_Framework_TestCase{

	private $fs;
	private $path_dist;

	/**
	 * setup
	 */
	public function setup(){
		set_time_limit(60);
		$this->fs = new \tomk79\filesystem();
		$this->path_dist = __DIR__.'/testData/publish/px-files/dist/';
		require_once(__DIR__.'/../php/simple_html_dom.php');
	}

	/**
	 * コンテンツ編集モードを変更するテスト
	 */
	public function testChangeContentEditorMode(){
		$this->clear_dist_dir();
		clearstatcache();
		$this->assertFalse( $this->fs->is_file( $this->path_dist.'index.html' ) );

		// publish /index.html
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/publish/.px_execute.php',
			'/?PX=px2dthelper.publish_single_page'
		] );
		$this->assertTrue( $this->fs->is_file( $this->path_dist.'index.html' ) );
		$this->assertTrue( $this->fs->is_dir( $this->path_dist.'index_files/' ) );
		$this->assertTrue( $this->fs->is_file( $this->path_dist.'index_files/resource.txt' ) );
		$this->assertFalse( $this->fs->is_file( $this->path_dist.'sub_dir_1/index.html' ) );
		$this->assertFalse( $this->fs->is_dir( $this->path_dist.'sub_dir_1/index_files/' ) );

		// publish /sub_dir_1/1-1.html
		$output = $this->passthru( [
			'php',
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
		$output = $this->passthru( [
			'php',
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

	/**
	 * コマンドを実行し、標準出力値を返す
	 * @param array $ary_command コマンドのパラメータを要素として持つ配列
	 * @return string コマンドの標準出力値
	 */
	private function passthru( $ary_command ){
		$cmd = array();
		foreach( $ary_command as $row ){
			$param = '"'.addslashes($row).'"';
			array_push( $cmd, $param );
		}
		$cmd = implode( ' ', $cmd );
		ob_start();
		passthru( $cmd );
		$bin = ob_get_clean();
		return $bin;
	}// passthru()

}
