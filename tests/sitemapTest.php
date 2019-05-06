<?php
/**
 * Test for pickles2\px2-px2dthelper
 */

class sitemapTest extends PHPUnit_Framework_TestCase{

	/**
	 * setup
	 */
	public function setup(){
		set_time_limit(60);
		$this->fs = new \tomk79\filesystem();
		require_once(__DIR__.'/../php/simple_html_dom.php');
	}

	/**
	 * PX=px2dthelper.sitemap.create のテスト
	 */
	public function testSitemapCreate(){

		// ---------------------------
		// 新しいサイトマップファイルを作成
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.sitemap.create&filename=create_new_sitemap' ] );
		clearstatcache();
		// var_dump($output);
		$json = json_decode( $output );
		// var_dump($json);
		$this->assertTrue( is_object($json) );
		$this->assertTrue( $json->result );
		$this->assertTrue( $this->fs->is_file( __DIR__.'/testData/standard/px-files/sitemaps/create_new_sitemap.csv' ) );


		// ---------------------------
		// 同じファイル名でもう一度作成
		// ただし、ファイル名の一部を大文字に。大文字・小文字は区別しないのが正解。
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.sitemap.create&filename=create_NEW_sitemap' ] );
		clearstatcache();
		// var_dump($output);
		$json = json_decode( $output );
		// var_dump($json);
		$this->assertTrue( is_object($json) );
		$this->assertFalse( $json->result );
		$this->assertTrue( $this->fs->is_file( __DIR__.'/testData/standard/px-files/sitemaps/create_new_sitemap.csv' ) );


		// ---------------------------
		// サイトマップファイルを削除
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.sitemap.delete&filename=create_new_sitemap' ] );
		clearstatcache();
		// var_dump($output);
		$json = json_decode( $output );
		// var_dump($json);
		$this->assertTrue( is_object($json) );
		$this->assertTrue( $json->result );
		$this->assertFalse( $this->fs->is_file( __DIR__.'/testData/standard/px-files/sitemaps/create_new_sitemap.csv' ) );


		// 後始末
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	}//testSitemapCreate()




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
