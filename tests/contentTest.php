<?php
/**
 * Test for pickles2\px2-px2dthelper
 */

class contentTest extends PHPUnit\Framework\TestCase{

	/**
	 * setup
	 */
	public function setup() : void{
		set_time_limit(60);
		$this->fs = new \tomk79\filesystem();
	}

	/**
	 * コンテンツを複製するテスト
	 */
	public function testDeleteContent(){

		$this->fs->mkdir_r(__DIR__.'/testData/standard/delete/test_files/');
		$this->fs->save_file(__DIR__.'/testData/standard/delete/test.html.md', '<p>test</p>');
		$this->fs->save_file(__DIR__.'/testData/standard/delete/test_files/test.txt', 'test');

		// 削除対象ファイルが存在することを確認
		$this->assertTrue( $this->fs->is_file(__DIR__.'/testData/standard/delete/test.html.md') );
		$this->assertTrue( $this->fs->is_dir(__DIR__.'/testData/standard/delete/test_files/') );

		// PX=px2dthelper.copy_content
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/delete/test.html?PX=px2dthelper.content.delete' ,
		] );
		// var_dump($output);
		$result = json_decode($output);
		// var_dump( $result );
		$this->assertEquals( $result->result, true );
		$this->assertEquals( $result->message, 'OK' );

		// 削除対象ファイルが削除されたことを確認
		clearstatcache();
		$this->assertFalse( $this->fs->is_file(__DIR__.'/testData/standard/delete/test.html.md') );
		$this->assertFalse( $this->fs->is_dir(__DIR__.'/testData/standard/delete/test_files/') );

		// 後始末
		$this->fs->rm(__DIR__.'/testData/standard/delete/');
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	} // testDeleteContent()




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
