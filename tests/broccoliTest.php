<?php
/**
 * Test for pickles2\px2-px2dthelper
 */

class broccoliTest extends PHPUnit_Framework_TestCase{

	/**
	 * setup
	 */
	public function setup(){
		$this->fs = new \tomk79\filesystem();
		require_once(__DIR__.'/../php/simple_html_dom.php');
	}

	/**
	 * broccoli-html-editor ビルドのテスト
	 */
	public function testBuild(){

		// build "CSS"
		$outputCss = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/test1_build_css.html' ,
		] );
		// var_dump($outputCss);
		$this->assertTrue( strpos( $outputCss, '.bar{' ) !== false );//←素のCSSがそのまま出ている。
		$this->assertTrue( strpos( $outputCss, '.hoge_fuga .hoge_fuga-child {' ) !== false );//←SCSSが機能している。
		$this->assertTrue( strpos( $outputCss, 'data:image/png;base64,' ) !== false );//←SCSSが機能している。
		$outputCssApi = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=px2dthelper.document_modules.build_css' ,
		] );
		// var_dump($outputCssApi);
		$this->assertEquals( $outputCss, $outputCssApi );


		// build "JavaScript"
		$outputJs = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/test1_build_js.html' ,
		] );

		$expected = '/**'."\n"
		.' * module: Modules1:foo/bar'."\n"
		.' */'."\n"
		.'alert(\'foo/bar\');'."\n";
		$this->assertEquals( $expected, $outputJs );
		$outputJsApi = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=px2dthelper.document_modules.build_js' ,
		] );
		$this->assertEquals( $outputJs, $outputJsApi );


		// build "Loader"
		$outputLoader = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/test1_load.html' ,
		] );
		// var_dump($output);
		$this->assertEquals( '<style type="text/css">'.$outputCss.'</style><script type="text/javascript">'.$outputJs.'</script>', $outputLoader );
		$outputLoaderApi = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=px2dthelper.document_modules.load' ,
		] );
		$this->assertEquals( $outputLoader, $outputLoaderApi );


		// 後始末
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	}//testBuild()


	/**
	 * broccoli_receive_message のテスト
	 */
	public function testBroccoliReceiveMessage(){

		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/broccoli_receive_message/.px_execute.php' ,
			'-u', 'Mozilla/5.0',
			'/index.html' ,
		] );
		// var_dump($output);
		$this->assertTrue( !!preg_match('/broccoli\-receive\-message\=/s', $output) );

		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/broccoli_receive_message/.px_execute.php' ,
			'-u', 'PicklesCrawler',
			'/index.html' ,
		] );
		// var_dump($output);
		$this->assertFalse( !!preg_match('/broccoli\-receive\-message\=/s', $output) );



		// 後始末
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/broccoli_receive_message/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	}//testBroccoliReceiveMessage()




	/**
	 * コマンドを実行し、標準出力値を返す
	 * @param array $ary_command コマンドのパラメータを要素として持つ配列
	 * @return string コマンドの標準出力値
	 */
	private function passthru( $ary_command ){
		set_time_limit(60*10);
		$cmd = array();
		foreach( $ary_command as $row ){
			$param = escapeshellcmd($row);
			array_push( $cmd, $param );
		}
		$cmd = implode( ' ', $cmd );
		ob_start();
		passthru( $cmd );
		$bin = ob_get_clean();
		set_time_limit(30);
		return $bin;
	}// passthru()

}
