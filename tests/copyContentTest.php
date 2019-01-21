<?php
/**
 * Test for pickles2\px2-px2dthelper
 */

class copyContentTest extends PHPUnit_Framework_TestCase{

	/**
	 * setup
	 */
	public function setup(){
		set_time_limit(60);
		$this->fs = new \tomk79\filesystem();
	}

	/**
	 * コンテンツを複製するテスト
	 */
	public function testCopyContent(){

		// to が存在しないことを確認
		$this->assertFalse( $this->fs->is_file(__DIR__.'/testData/standard/copy/to.html') );
		$this->assertFalse( $this->fs->is_dir(__DIR__.'/testData/standard/copy/to_files/') );
		$this->assertFalse( $this->fs->is_file(__DIR__.'/testData/standard/copy/to_files/test.txt') );

		// PX=px2dthelper.copy_content
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=px2dthelper.copy_content&from='.urlencode('/copy/from.html').'&to='.urlencode('/copy/to.html') ,
		] );
		// var_dump($output);
		$result = json_decode($output);
		// var_dump( $result );
		$this->assertEquals( $result[0], true );
		$this->assertEquals( $result[1], 'ok' );
		clearstatcache();
		$this->assertEquals(
			$this->fs->read_file(__DIR__.'/testData/standard/copy/from.html'),
			$this->fs->read_file(__DIR__.'/testData/standard/copy/to.html')
		);
		$this->assertEquals(
			$this->fs->read_file(__DIR__.'/testData/standard/copy/from_files/test.txt'),
			$this->fs->read_file(__DIR__.'/testData/standard/copy/to_files/test.txt')
		);


		// from を削除
		$this->fs->rm(__DIR__.'/testData/standard/copy/from.html');
		$this->fs->rm(__DIR__.'/testData/standard/copy/from_files/');
		clearstatcache();
		$this->assertFalse( $this->fs->is_file(__DIR__.'/testData/standard/copy/from.html') );
		$this->assertFalse( $this->fs->is_dir(__DIR__.'/testData/standard/copy/from_files/') );


		// PX=px2dthelper.copy_content
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/copy/from.html?PX=px2dthelper.copy_content&from='.urlencode('/copy/to.html') ,
		] );
		// var_dump($output);
		$result = json_decode($output);
		// var_dump( $result );
		$this->assertEquals( $result[0], true );
		$this->assertEquals( $result[1], 'ok' );
		clearstatcache();
		$this->assertEquals(
			$this->fs->read_file(__DIR__.'/testData/standard/copy/from.html'),
			$this->fs->read_file(__DIR__.'/testData/standard/copy/to.html')
		);
		$this->assertEquals(
			$this->fs->read_file(__DIR__.'/testData/standard/copy/from_files/test.txt'),
			$this->fs->read_file(__DIR__.'/testData/standard/copy/to_files/test.txt')
		);

		// to を削除
		$this->fs->rm(__DIR__.'/testData/standard/copy/to.html');
		$this->fs->rm(__DIR__.'/testData/standard/copy/to_files/');
		clearstatcache();
		$this->assertFalse( $this->fs->is_file(__DIR__.'/testData/standard/copy/to.html') );
		$this->assertFalse( $this->fs->is_dir(__DIR__.'/testData/standard/copy/to_files/') );


		// 後始末
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	}//testCopyContent()


	/**
	 * 拡張子が違うコンテンツを複製するテスト
	 */
	public function testCopyExtContent(){

		// 予め to に拡張子違いで存在させておく
		$this->fs->save_file(__DIR__.'/testData/standard/copy/to.html.md', '<p>generated to.html</p>');
		$this->fs->copy_r(__DIR__.'/testData/standard/copy/from_files/', __DIR__.'/testData/standard/copy/to_files/');
		$this->assertTrue( $this->fs->is_file(__DIR__.'/testData/standard/copy/to.html.md') );
		$this->assertTrue( $this->fs->is_dir(__DIR__.'/testData/standard/copy/to_files/') );
		$this->assertTrue( $this->fs->is_file(__DIR__.'/testData/standard/copy/to_files/test.txt') );

		// PX=px2dthelper.copy_content
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=px2dthelper.copy_content&from='.urlencode('/copy/from.html').'&to='.urlencode('/copy/to.html') ,
		] );
		// var_dump($output);
		$result = json_decode($output);
		// var_dump( $result );
		$this->assertEquals( $result[0], false );
		$this->assertEquals( $result[1], 'Contents already exists.' );

		// PX=px2dthelper.copy_content
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=px2dthelper.copy_content&from='.urlencode('/copy/from.html').'&to='.urlencode('/copy/to.html').'&force=1' ,
		] );
		// var_dump($output);
		$result = json_decode($output);
		// var_dump( $result );
		$this->assertEquals( $result[0], true );
		$this->assertEquals( $result[1], 'ok' );
		clearstatcache();
		$this->assertEquals(
			$this->fs->read_file(__DIR__.'/testData/standard/copy/from.html'),
			$this->fs->read_file(__DIR__.'/testData/standard/copy/to.html.md')
		);
		$this->assertEquals(
			$this->fs->read_file(__DIR__.'/testData/standard/copy/from_files/test.txt'),
			$this->fs->read_file(__DIR__.'/testData/standard/copy/to_files/test.txt')
		);
		$this->assertFalse( $this->fs->is_file(__DIR__.'/testData/standard/copy/to.html') );
		$this->assertTrue( $this->fs->is_file(__DIR__.'/testData/standard/copy/to.html.md') );


		// from を削除
		$this->fs->rm(__DIR__.'/testData/standard/copy/from.html');
		$this->fs->rm(__DIR__.'/testData/standard/copy/from_files/');
		clearstatcache();
		$this->assertFalse( $this->fs->is_file(__DIR__.'/testData/standard/copy/from.html') );
		$this->assertFalse( $this->fs->is_dir(__DIR__.'/testData/standard/copy/from_files/') );


		// PX=px2dthelper.copy_content
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/copy/from.html?PX=px2dthelper.copy_content&from='.urlencode('/copy/to.html') ,
		] );
		// var_dump($output);
		$result = json_decode($output);
		// var_dump( $result );
		$this->assertEquals( $result[0], true );
		$this->assertEquals( $result[1], 'ok' );
		clearstatcache();
		$this->assertEquals(
			$this->fs->read_file(__DIR__.'/testData/standard/copy/from.html'),
			$this->fs->read_file(__DIR__.'/testData/standard/copy/to.html.md')
		);
		$this->assertEquals(
			$this->fs->read_file(__DIR__.'/testData/standard/copy/from_files/test.txt'),
			$this->fs->read_file(__DIR__.'/testData/standard/copy/to_files/test.txt')
		);

		// to を削除
		$this->fs->rm(__DIR__.'/testData/standard/copy/to.html.md');
		$this->fs->rm(__DIR__.'/testData/standard/copy/to_files/');
		clearstatcache();
		$this->assertFalse( $this->fs->is_file(__DIR__.'/testData/standard/copy/to.html.md') );
		$this->assertFalse( $this->fs->is_file(__DIR__.'/testData/standard/copy/to.html') );
		$this->assertFalse( $this->fs->is_dir(__DIR__.'/testData/standard/copy/to_files/') );


		// 後始末
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	}//testCopyExtContent()

	/**
	 * $from と $to が同じ場合のテスト
	 */
	public function testCopySameContent(){

		// PX=px2dthelper.copy_content
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=px2dthelper.copy_content&from='.urlencode('/copy/from.html').'&to='.urlencode('/copy/from.html') ,
		] );
		// var_dump($output);
		$result = json_decode($output);
		// var_dump( $result );
		$this->assertEquals( $result[0], false );
		$this->assertEquals( $result[1], 'Same paths was given to `$from` and `$to`.' );
		clearstatcache();
		$this->assertTrue( $this->fs->is_file(__DIR__.'/testData/standard/copy/from.html') );
		$this->assertTrue( $this->fs->is_dir(__DIR__.'/testData/standard/copy/from_files/') );


		// 後始末
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	}//testCopySameContent()



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
