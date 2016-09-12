<?php
/**
 * Test for pickles2\px2-px2dthelper
 */

class copyContentTest extends PHPUnit_Framework_TestCase{

	/**
	 * コンテンツを複製するテスト
	 */
	public function testCopyContent(){

		// Pickles 2 実行
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=px2dthelper.copy_content&from=/copy/from.html&to=/copy/to.html' ,
		] );
		// var_dump($output);
		$result = json_decode($output);
		// var_dump( $result );
		$this->assertEquals( $result[0], true );
		$this->assertEquals( $result[1], 'ok' );


		// 後始末
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	}//testCopyContent()



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
