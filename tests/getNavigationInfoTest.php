<?php
/**
 * Test for pickles2\px2-px2dthelper
 */

class getNavigationInfo extends PHPUnit\Framework\TestCase{

	/**
	 * setup
	 */
	public function setup() : void{
		set_time_limit(60);
		$this->fs = new \tomk79\filesystem();
		require_once(__DIR__.'/../php/simple_html_dom.php');
	}

	/**
	 * PX=px2dthelper.get.navigation_info のテスト
	 */
	public function testNavigationInfo(){

		// トップページ取得
		$output_toppage = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.get.navigation_info' ] );
		// var_dump($output_toppage);
		$json_toppage = json_decode( $output_toppage );
		// var_dump($json_toppage);
		$this->assertTrue( is_object($json_toppage) );
		$this->assertTrue( is_object($json_toppage->page_info) );
		$this->assertEquals( $json_toppage->page_info->path, '/index.html' );
		$this->assertTrue( $json_toppage->parent === false );
		$this->assertTrue( $json_toppage->parent_info === false );
		$this->assertTrue( is_array($json_toppage->breadcrumb) );
		$this->assertTrue( !count($json_toppage->breadcrumb) );
		$this->assertTrue( is_array($json_toppage->breadcrumb_info) );
		$this->assertTrue( !count($json_toppage->breadcrumb_info) );
		$this->assertTrue( is_array($json_toppage->bros) );
		$this->assertEquals( count($json_toppage->bros), 1 );
		$this->assertTrue( is_array($json_toppage->bros_info) );
		$this->assertEquals( count($json_toppage->bros_info), 1 );
		$this->assertTrue( is_array($json_toppage->children) );
		$this->assertEquals( count($json_toppage->children), 7 );
		$this->assertTrue( is_array($json_toppage->children_info) );
		$this->assertEquals( count($json_toppage->children_info), 7 );


		// 下層ページ取得
		$output_test1_load = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/test1_load.html?PX=px2dthelper.get.navigation_info' ] );
		// var_dump($output_test1_load);
		$json_test1_load = json_decode( $output_test1_load );
		// var_dump($json_test1_load);
		$this->assertTrue( is_array($json_test1_load->breadcrumb) );
		$this->assertEquals( count($json_test1_load->breadcrumb), 1 );
		$this->assertEquals( $json_test1_load->breadcrumb_info[0], $json_toppage->page_info );
		$this->assertEquals( $json_test1_load->parent_info, $json_toppage->page_info );
		$this->assertEquals( $json_test1_load->bros, $json_toppage->children );
		$this->assertEquals( $json_test1_load->bros_info, $json_toppage->children_info );
		$this->assertEquals( count($json_test1_load->children), 2 );
		$this->assertEquals( count($json_test1_load->children_info), 2 );


		// 下層ページ取得(filter: false)
		$output_test1_load = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/test1_load.html?PX=px2dthelper.get.navigation_info&filter=false' ] );
		// var_dump($output_test1_load);
		$json_test1_load = json_decode( $output_test1_load );
		// var_dump($json_test1_load);
		$this->assertTrue( is_array($json_test1_load->breadcrumb) );
		$this->assertEquals( count($json_test1_load->breadcrumb), 1 );
		$this->assertEquals( $json_test1_load->breadcrumb_info[0], $json_toppage->page_info );
		$this->assertEquals( $json_test1_load->parent_info, $json_toppage->page_info );
		$this->assertEquals( count($json_test1_load->bros), count($json_toppage->children)+1 );
		$this->assertEquals( count($json_test1_load->bros_info), count($json_toppage->children_info)+1 );
		$this->assertEquals( count($json_test1_load->children), 3 );
		$this->assertEquals( count($json_test1_load->children_info), 3 );


		// 後始末
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	}//testNavigationInfo()

	/**
	 * PX=px2dthelper.get.navigation_info を、before_sitemap の環境で実行するテスト
	 */
	public function testNavigationInfoBeforeSitemap(){

		// Pickles 2 実行
		$output = $this->passthru( ['php', __DIR__.'/testData/before_sitemap/.px_execute.php', '/?PX=px2dthelper.get.navigation_info' ] );
		// var_dump($output);
		$json = json_decode( $output );
		// var_dump($json);
		$this->assertTrue( $json === false );


		// 後始末
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/before_sitemap/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	}//testNavigationInfoBeforeSitemap()



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
