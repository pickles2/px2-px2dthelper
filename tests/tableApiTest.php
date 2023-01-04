<?php
/**
 * Test for pickles2\px2-px2dthelper
 */

class tableApiTest extends PHPUnit\Framework\TestCase{

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
	 * TableAPI(Px Command)のテスト
	 */
	public function testTableApi(){

		// Excelをtableに変換させる
		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.convert_table_excel2html&path='.urlencode(__DIR__.'/testData/xlsx/default.xlsx') ] );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( gettype(''), gettype($output) );

		$html = str_get_html(
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
		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.convert_table_excel2html&path=' ] );
		// var_dump($output);
		$output = json_decode($output);
		$this->assertFalse( $output );


		// 後始末
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	} // testTableApi()

}
