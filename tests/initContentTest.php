<?php
/**
 * Test for pickles2\px2-px2dthelper
 */

class initContentTest extends PHPUnit_Framework_TestCase{

	/**
	 * setup
	 */
	public function setup(){
		set_time_limit(60);
		$this->fs = new \tomk79\filesystem();
		require_once(__DIR__.'/../php/simple_html_dom.php');
	}

	/**
	 * コンテンツファイル初期化のテスト
	 */
	public function testInitializeContent(){

		// PX=px2dthelper.init_content
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php',
			'/init_content/html/test.html?PX=px2dthelper.init_content'
		] );
		// var_dump($output);
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( gettype(array()), gettype($output) );
		$this->assertTrue( $this->fs->is_file( __DIR__.'/testData/standard/init_content/html/test.html' ) );

		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php',
			'/init_content/html/?PX=px2dthelper.init_content'
		] );
		// var_dump($output);
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( gettype(array()), gettype($output) );
		$this->assertTrue( $this->fs->is_file( __DIR__.'/testData/standard/init_content/html/index.html' ) );

		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php',
			'/init_content/html.gui/?PX=px2dthelper.init_content&editor_mode=html.gui'
		] );
		// var_dump($output);
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( gettype(array()), gettype($output) );
		$this->assertTrue( $this->fs->is_file( __DIR__.'/testData/standard/init_content/html.gui/index.html' ) );
		$this->assertTrue( $this->fs->is_file( __DIR__.'/testData/standard/init_content/html.gui/index_files/guieditor.ignore/data.json' ) );

		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php',
			'/init_content/md/?PX=px2dthelper.init_content&editor_mode=md'
		] );
		// var_dump($output);
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( gettype(array()), gettype($output) );
		$this->assertTrue( $this->fs->is_file( __DIR__.'/testData/standard/init_content/md/index.html.md' ) );



		// 後始末
		$this->fs->rm(__DIR__.'/testData/standard/init_content/');
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );
	} // testInitializeContent()




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
