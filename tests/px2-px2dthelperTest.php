<?php
/**
 * Test for tomk79\px2-px2dthelper
 * 
 * $ cd (project dir)
 * $ ./vendor/phpunit/phpunit/phpunit tests/px2-px2dthelperTest.php px2px2dthelper
 */

class px2px2dthelperTest extends PHPUnit_Framework_TestCase{

	/**
	 * ファイルシステムユーティリティ
	 */
	// private $fs;

	/**
	 * setup
	 */
	public function setup(){
		// $this->fs = new \tomk79\filesystem();
		require_once(__DIR__.'/../php/simple_html_dom.php');
	}

	/**
	 * PxCommandの疎通確認テスト
	 */
	public function testPing(){

		// ping打ってみる
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.ping' ] );
		// var_dump($output);
		$this->assertEquals( 'ok', trim($output) );

	}//testPing()

	/**
	 * ビルドのテスト
	 */
	public function testBuild(){


		// ビルドCSS
		$outputCss = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/test1_build_css.html' ,
		] );
		// var_dump($outputCss);
		$this->assertTrue( strpos( $outputCss, '.bar{' ) !== false );//←素のCSSがそのまま出ている。
		$this->assertTrue( strpos( $outputCss, '.hoge_fuga .hoge_fuga-child {' ) !== false );//←SCSSが機能している。
		$this->assertTrue( strpos( $outputCss, 'data:image/png;base64,' ) !== false );//←SCSSが機能している。


		// ビルドJavaScript
		$outputJs = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/test1_build_js.html' ,
		] );
		$this->assertEquals( 'alert(\'foo/bar\');', trim($outputJs) );


		// ビルドLoader
		$outputLoader = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/test1_load.html' ,
		] );
		// var_dump($output);
		$this->assertEquals( '<style type="text/css">'.$outputCss.'</style><script type="text/javascript">'.$outputJs.'</script>', $outputLoader );


		// 後始末
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	}//testBuild()

	/**
	 * TableAPIのテスト
	 */
	public function testTableApi(){

		// Excelをtableに変換させる
		// 存在しないファイルパスを渡したら、falseが返る。
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.convert_table_excel2html&path='.urlencode(__DIR__.'/testData/xlsx/default.xlsx') ] );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( gettype(''), gettype($output) );

		$html = \tomk79\pickles2\px2dthelper\str_get_html(
			$output ,
			true, // $lowercase
			true, // $forceTagsClosed
			DEFAULT_TARGET_CHARSET, // $target_charset
			false, // $stripRN
			DEFAULT_BR_TEXT, // $defaultBRText
			DEFAULT_SPAN_TEXT // $defaultSpanText
		);

		$this->assertEquals( 18, count($html->find('tr')) );
		$this->assertEquals( 5, count($html->find('tr',0)->find('td')) );
		$this->assertEquals( 'E18', $html->find('tr',17)->childNodes(4)->innertext );
		$this->assertNull( $html->find('tr',18) );
		$this->assertNull( $html->find('tr',17)->childNodes(5) );



		// Excelをtableに変換させる
		// 存在しないファイルパスを渡したら、falseが返る。
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.convert_table_excel2html&path=' ] );
		// var_dump($output);
		$output = json_decode($output);
		$this->assertFalse( $output );

	}//testPing()







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
