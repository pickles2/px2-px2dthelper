<?php
/**
 * Test for pickles2\px2-px2dthelper
 */

class mainTest extends PHPUnit\Framework\TestCase{

	/**
	 * setup
	 */
	public function setup() : void{
		set_time_limit(60);
		$this->fs = new \tomk79\filesystem();
		require_once(__DIR__.'/../php/simple_html_dom.php');
	}

	/**
	 * PxCommandの疎通確認テスト
	 */
	public function testPing(){

		// ping打ってみる
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.ping' ] );
		// var_dump($output);
		$this->assertEquals( '"ok"', $output );

		// ping打ってみる
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.ping&type=jsonp' ] );
		// var_dump($output);
		$this->assertEquals( 'callback("ok");', $output );

		// ping打ってみる
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.ping&type=jsonp&callback=hoge' ] );
		// var_dump($output);
		$this->assertEquals( 'hoge("ok");', $output );

		// ping打ってみる
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.ping&type=xml' ] );
		// var_dump($output);
		$this->assertEquals( '<api><value type="string">ok</value></api>', $output );

	}//testPing()

	/**
	 * バージョン番号の取得テスト
	 */
	public function testVersion(){


		// Pickles 2 実行
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=px2dthelper.version' ,
		] );
		// var_dump($output);
		$version = json_decode($output);
		// var_dump( $version );
		$this->assertEquals( preg_match('/^\d+\.\d+\.\d+(?:\-.+)?(?:\+.+)?$/', $version), 1 );


		// 後始末
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	}//testVersion()

	/**
	 * Advanced Config API (PX Command)のテスト
	 */
	public function testAdvancedConfigApi(){

		// guieditor.realpath_data_dir
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/px2dt_config/.px_execute.php',
			'/subdirectory/test.html?PX=px2dthelper.get.realpath_data_dir'
		] );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( gettype(''), gettype($output) );
		$this->assertEquals( $this->fs->normalize_path($this->fs->get_realpath(__DIR__.'/testData/px2dt_config/data_dir/subdirectory/test.files/guieditor.ignore/')), $output );

		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/px2dt_config/subapp/.px_execute.php',
			'/subdirectory/test.html?PX=px2dthelper.get.realpath_data_dir'
		] );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( gettype(''), gettype($output) );
		$this->assertEquals( $this->fs->normalize_path($this->fs->get_realpath(__DIR__.'/testData/px2dt_config/subapp/data_dir/subdirectory/test.files/guieditor.ignore/')), $output );

		// guieditor.path_resource_dir
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/px2dt_config/.px_execute.php',
			'/subdirectory/test.html?PX=px2dthelper.get.path_resource_dir'
		] );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( gettype(''), gettype($output) );
		$this->assertEquals( '/resources/subdirectory/test.files/resources/', $output );

		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/px2dt_config/subapp/.px_execute.php',
			'/subdirectory/test.html?PX=px2dthelper.get.path_resource_dir'
		] );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( gettype(''), gettype($output) );
		$this->assertEquals( '/subapp/resources/subdirectory/test.files/resources/', $output );

		// guieditor.custom_fields
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/px2dt_config/.px_execute.php',
			'/?PX=px2dthelper.get.custom_fields'
		] );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( gettype(new stdClass), gettype($output) );
		$this->assertEquals( gettype(new stdClass), gettype($output->projectCustom1) );
		$this->assertEquals( gettype(new stdClass), gettype($output->projectCustom2) );
		$this->assertEquals( $this->fs->normalize_path($this->fs->get_realpath(__DIR__.'/testData/px2dt_config/px-files/broccoli-fields/projectCustom2/backend.js')), $output->projectCustom2->backend->require );
		$this->assertEquals( $this->fs->normalize_path($this->fs->get_realpath(__DIR__.'/testData/px2dt_config/px-files/broccoli-fields/projectCustom2/frontend.js')), $output->projectCustom2->frontend->file[0] );
		$this->assertEquals( 'window.broccoliFieldProjectCustom2', $output->projectCustom2->frontend->function );

		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/px2dt_config/subapp/.px_execute.php',
			'/?PX=px2dthelper.get.custom_fields'
		] );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( gettype(new stdClass), gettype($output) );
		$this->assertEquals( gettype(new stdClass), gettype($output->projectCustom1) );
		$this->assertEquals( gettype(new stdClass), gettype($output->projectCustom2) );
		$this->assertEquals( $this->fs->normalize_path($this->fs->get_realpath(__DIR__.'/testData/px2dt_config/subapp/px-files/broccoli-fields/projectCustom2/backend.js')), $output->projectCustom2->backend->require );
		$this->assertEquals( $this->fs->normalize_path($this->fs->get_realpath(__DIR__.'/testData/px2dt_config/subapp/px-files/broccoli-fields/projectCustom2/frontend.js')), $output->projectCustom2->frontend->file[0] );
		$this->assertEquals( 'window.broccoliFieldProjectCustom2', $output->projectCustom2->frontend->function );


		// 後始末
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/px2dt_config/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/px2dt_config/subapp/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	} // testAdvancedConfigApi()

	/**
	 * コンテンツファイルを検索するテスト
	 */
	public function testFindPageContent(){
		// find_page_content()
		$output = $this->passthru( [ 'php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.find_page_content' ] );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( '/index.html', $output );

		// find_page_content()
		$output = $this->passthru( [ 'php', __DIR__.'/testData/standard/.px_execute.php', '/editor_modes/md.html?PX=px2dthelper.find_page_content' ] );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( '/editor_modes/md.html.md', $output );


		// 後始末
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );
	}

	/**
	 * 編集モードを調べるテスト
	 */
	public function testCheckEditorMode(){

		// check_editor_mode
		$output = $this->passthru( [ 'php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.check_editor_mode' ] );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( 'html', $output );

		$output = $this->passthru( [ 'php', __DIR__.'/testData/standard/.px_execute.php', '/editor_modes/?PX=px2dthelper.check_editor_mode' ] );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( 'html', $output );

		$outputByPath = $this->passthru( [ 'php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.check_editor_mode&path=/editor_modes/index.html' ] );
		$outputByPath = json_decode($outputByPath);
		// var_dump($outputByPath);
		$this->assertEquals( $outputByPath, $output );

		$output = $this->passthru( [ 'php', __DIR__.'/testData/standard/.px_execute.php', '/editor_modes/html.html?PX=px2dthelper.check_editor_mode' ] );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( 'html', $output );

		$output = $this->passthru( [ 'php', __DIR__.'/testData/standard/.px_execute.php', '/editor_modes/htm.htm?PX=px2dthelper.check_editor_mode' ] );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( 'html', $output );

		$output = $this->passthru( [ 'php', __DIR__.'/testData/standard/.px_execute.php', '/editor_modes/gui.html?PX=px2dthelper.check_editor_mode' ] );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( 'html.gui', $output );

		$output = $this->passthru( [ 'php', __DIR__.'/testData/standard/.px_execute.php', '/editor_modes/md.html?PX=px2dthelper.check_editor_mode' ] );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( 'md', $output );

		$output = $this->passthru( [ 'php', __DIR__.'/testData/standard/.px_execute.php', '/editor_modes/not_exists.html?PX=px2dthelper.check_editor_mode' ] );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( '.not_exists', $output );

		$output = $this->passthru( [ 'php', __DIR__.'/testData/standard/.px_execute.php', '/editor_modes/page_not_exists.html?PX=px2dthelper.check_editor_mode' ] );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( '.page_not_exists', $output );

		$output = $this->passthru( [ 'php', __DIR__.'/testData/standard/.px_execute.php', '/px-files/?PX=px2dthelper.check_editor_mode' ] );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( '.page_not_exists', $output );


		// 後始末
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/px2dt_config/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/px2dt_config/subapp/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	} // testCheckEditorMode()




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
