<?php
/**
 * Test for pickles2\px2-px2dthelper
 */

class getAllTest extends PHPUnit_Framework_TestCase{

	/**
	 * setup
	 */
	public function setup(){
		$this->fs = new \tomk79\filesystem();
		require_once(__DIR__.'/../php/simple_html_dom.php');
	}

	/**
	 * PX=px2dthelper.get.all のテスト
	 */
	public function testGetAll(){

		// Pickles 2 実行
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.get.all' ] );
		// var_dump($output);
		$json = json_decode( $output );
		// var_dump($json);
		$this->assertTrue( is_object($json) );

		// Pickles 2 のバージョン番号
		$output = json_decode($this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.version' ] ));
		$this->assertEquals( $json->version->pxfw, $output );

		// px2dthelper のバージョン番号
		$output = json_decode($this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.version' ] ));
		$this->assertEquals( $json->version->px2dthelper, $output );

		// config
		$output = json_decode($this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.config' ] ));
		$this->assertEquals( $json->config, $output );

		// px2dtconfig
		$this->assertTrue( is_object($json->px2dtconfig) );
		$this->assertTrue( is_object($json->px2dtconfig->paths_module_template) );
			// ↓ スラッシュで始まり スラッシュで終わる 絶対パスで得られる。
			// ↓ WindowsでもUNIXスタイルに正規化される。
		$this->assertEquals( preg_match('/^\\//', $json->px2dtconfig->paths_module_template->Modules1), 1 );
		$this->assertEquals( preg_match('/\\/$/', $json->px2dtconfig->paths_module_template->Modules1), 1 );

		// check_status
		$output = json_decode($this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.check_status' ] ));
		$this->assertEquals( $json->check_status->px2dthelper, $output );
		$this->assertEquals( $json->check_status->pxfw_api->version, $json->version->pxfw );

		// realpath_data_dir
		$output = json_decode($this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.get.realpath_data_dir' ] ));
		$this->assertEquals( $json->realpath_data_dir, $output );

		// path_resource_dir
		$output = json_decode($this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.get.path_resource_dir' ] ));
		$this->assertEquals( $json->path_resource_dir, $output );

		// custom_fields
		$output = json_decode($this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.get.custom_fields' ] ));
		$this->assertEquals( $json->custom_fields, $output );

		// realpath_homedir
		$output = json_decode($this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.path_homedir' ] ));
		$this->assertEquals( $json->realpath_homedir, $output );

		// path_controot
		$output = json_decode($this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.path_controot' ] ));
		$this->assertEquals( $json->path_controot, $output );

		// realpath_docroot
		$output = json_decode($this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.path_docroot' ] ));
		$this->assertEquals( $json->realpath_docroot, $output );

		// page_info
		$output = json_decode($this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.page_info&path=/' ] ));
		$this->assertEquals( $json->page_info, $output );

		// path_files
		$output = json_decode($this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.path_files&path=/' ] ));
		$this->assertEquals( $json->path_files, $output );

		// realpath_files
		$output = json_decode($this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.realpath_files&path=/' ] ));
		$this->assertEquals( $json->realpath_files, $output );


		// 後始末
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	}//testGetAll()



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
