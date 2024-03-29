<?php
/**
 * Test for pickles2\px2-px2dthelper
 */

class getAllTest extends PHPUnit\Framework\TestCase{

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
	}

	/**
	 * PX=px2dthelper.get.all のテスト
	 */
	public function testGetAll(){

		// Pickles 2 実行
		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.get.all' ] );
		// var_dump($output);
		$json = json_decode( $output );
		// var_dump($json);
		$this->assertTrue( is_object($json) );
		$this->assertEquals( $json->path_type, 'normal' );

		// Pickles 2 のバージョン番号
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.version' ] ));
		$this->assertEquals( $json->version->pxfw, $output );

		// px2dthelper のバージョン番号
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.version' ] ));
		$this->assertEquals( $json->version->px2dthelper, $output );

		// config
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.config' ] ));
		$this->assertEquals( $json->config, $output );

		// px2dtconfig
		$this->assertTrue( is_object($json->px2dtconfig) );
		$this->assertTrue( is_object($json->px2dtconfig->paths_module_template) );
			// ↓ スラッシュで始まり スラッシュで終わる 絶対パスで得られる。
			// ↓ WindowsでもUNIXスタイルに正規化される。
		$this->assertEquals( preg_match('/^\\//', $json->px2dtconfig->paths_module_template->Modules1), 1 );
		$this->assertEquals( preg_match('/\\/$/', $json->px2dtconfig->paths_module_template->Modules1), 1 );

		// check_status
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.check_status' ] ));
		$this->assertEquals( $json->check_status->px2dthelper, $output );
		$this->assertEquals( $json->check_status->pxfw_api->version, $json->version->pxfw );

		// realpath_data_dir
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.get.realpath_data_dir' ] ));
		$this->assertEquals( $json->realpath_data_dir, $output );

		// path_resource_dir
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.get.path_resource_dir' ] ));
		$this->assertEquals( $json->path_resource_dir, $output );

		// custom_fields
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.get.custom_fields' ] ));
		$this->assertEquals( $json->custom_fields, $output );

		// realpath_homedir
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.path_homedir' ] ));
		$this->assertEquals( $json->realpath_homedir, $output );

		// path_controot
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.path_controot' ] ));
		$this->assertEquals( $json->path_controot, $output );

		// realpath_docroot
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.path_docroot' ] ));
		$this->assertEquals( $json->realpath_docroot, $output );

		// path_theme_collection_dir
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.get.path_theme_collection_dir' ] ));
		$this->assertEquals( $json->path_theme_collection_dir, $output );

		// realpath_theme_collection_dir
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.get.realpath_theme_collection_dir' ] ));
		$this->assertEquals( $json->realpath_theme_collection_dir, $output );

		// page_info
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.page_info&path=/' ] ));
		$this->assertEquals( $json->page_info, $output );

		// page_originated_csv
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.page_originated_csv&path=/' ] ));
		$this->assertEquals( $json->page_originated_csv, $output );
		$this->assertSame( $json->page_originated_csv->basename, 'sitemap.csv' );
		$this->assertSame( $json->page_originated_csv->row, 1 );

		// path_files
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.path_files&path=/' ] ));
		$this->assertEquals( $json->path_files, $output );

		// realpath_files
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.realpath_files&path=/' ] ));
		$this->assertEquals( $json->realpath_files, $output );

		// navigaton_info
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.get.navigation_info' ] ));
		$this->assertEquals( $json->navigation_info, $output );

		// packages
		$this->assertTrue( is_object($json->packages) );
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.packages.get_path_composer_root_dir' ] ));
		$this->assertEquals( $json->packages->path_composer_root_dir, $output );
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.packages.get_path_npm_root_dir' ] ));
		$this->assertEquals( $json->packages->path_npm_root_dir, $output );
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.packages.get_package_list' ] ));
		$this->assertEquals( $json->packages->package_list, $output );

		// Custom Console Extensions
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.custom_console_extensions' ] ));
		$this->assertEquals( $json->custom_console_extensions, $output->list );

		// 後始末
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	}

	/**
	 * PX=px2dthelper.get.all でページIDをキーに情報を得るテスト
	 */
	public function testGetAllByPageId(){

		// Pickles 2 実行
		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/test1_load.html?PX=px2dthelper.get.all' ] );
		$json1 = json_decode( $output );

		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.get.all&path=/test1_load.html' ] );
		$json2 = json_decode( $output );

		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.get.all&path=test1_load' ] );
		$json3 = json_decode( $output );

		$this->assertEquals( $json1, $json2 );
		$this->assertEquals( $json2, $json3 );


		// 後始末
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/?PX=clearcache',
		] );

	}

	/**
	 * PX=px2dthelper.get.all でページIDをキーにaliasページの情報を得るテスト
	 */
	public function testGetAllOfAliasPageByPageId(){

		// Pickles 2 実行
		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.get.all&path=alias_test' ] );
		$json = json_decode( $output );

		$this->assertEquals( $json->page_info->id, 'alias_test' );
		$this->assertEquals( $json->page_info->title, 'Alias Test' );
		$this->assertEquals( $json->path_type, 'alias' );
		$this->assertFalse( $json->path_files );
		$this->assertFalse( $json->path_resource_dir );
		$this->assertFalse( $json->realpath_files );
		$this->assertFalse( $json->realpath_data_dir );
		$this->assertEquals( $json->navigation_info->page_info->id, 'alias_test' );
		$this->assertEquals( $json->navigation_info->parent, '' );
		$this->assertEquals( count($json->navigation_info->bros), 7 );
		$this->assertEquals( count($json->navigation_info->children), 0 );


		// 後始末
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	}

	/**
	 * PX=px2dthelper.get.all で深いページの情報を得るテスト
	 */
	public function testGetAllDeepPage(){

		// Pickles 2 実行
		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/editsample/?PX=px2dthelper.get.all' ] );
		$json = json_decode( $output );
		$this->assertTrue( is_object($json) );

		// Pickles 2 のバージョン番号
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/editsample/?PX=api.get.version' ] ));
		$this->assertEquals( $json->version->pxfw, $output );

		// px2dthelper のバージョン番号
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/editsample/?PX=px2dthelper.version' ] ));
		$this->assertEquals( $json->version->px2dthelper, $output );

		// path_resource_dir
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/editsample/?PX=px2dthelper.get.path_resource_dir' ] ));
		$this->assertEquals( $json->path_resource_dir, $output );

		// custom_fields
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/editsample/?PX=px2dthelper.get.custom_fields' ] ));
		$this->assertEquals( $json->custom_fields, $output );

		// realpath_homedir
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/editsample/?PX=api.get.path_homedir' ] ));
		$this->assertEquals( $json->realpath_homedir, $output );

		// path_controot
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/editsample/?PX=api.get.path_controot' ] ));
		$this->assertEquals( $json->path_controot, $output );

		// realpath_docroot
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/editsample/?PX=api.get.path_docroot' ] ));
		$this->assertEquals( $json->realpath_docroot, $output );

		// page_info
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/editsample/?PX=api.get.page_info&path=/editsample/' ] ));
		$this->assertEquals( $json->page_info, $output );

		// path_files
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/editsample/?PX=api.get.path_files&path=/editsample/' ] ));
		$this->assertEquals( $json->path_files, $output );

		// realpath_files
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/editsample/?PX=api.get.realpath_files&path=/editsample/' ] ));
		$this->assertEquals( $json->realpath_files, $output );

		// navigaton_info
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/editsample/?PX=px2dthelper.get.navigation_info' ] ));
		$this->assertEquals( $json->navigation_info, $output );


		// 後始末
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	}

	/**
	 * PX=px2dthelper.get.all を、before_sitemap の環境で実行するテスト
	 */
	public function testGetAllBeforeSitemap(){

		// Pickles 2 実行
		$output = $this->px2query->query( [ __DIR__.'/testData/before_sitemap/.px_execute.php', '/?PX=px2dthelper.get.all' ] );
		$json = json_decode( $output );
		$this->assertTrue( is_object($json) );

		// Pickles 2 のバージョン番号
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/before_sitemap/.px_execute.php', '/?PX=api.get.version' ] ));
		$this->assertEquals( $json->version->pxfw, $output );

		// px2dthelper のバージョン番号
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/before_sitemap/.px_execute.php', '/?PX=px2dthelper.version' ] ));
		$this->assertEquals( $json->version->px2dthelper, $output );

		// config
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/before_sitemap/.px_execute.php', '/?PX=api.get.config' ] ));
		$this->assertTrue( is_object($json->config) );

		// px2dtconfig
		$this->assertTrue( is_object($json->px2dtconfig) );
		$this->assertTrue( is_object($json->px2dtconfig->paths_module_template) );
			// ↓ スラッシュで始まり スラッシュで終わる 絶対パスで得られる。
			// ↓ WindowsでもUNIXスタイルに正規化される。
		$this->assertEquals( preg_match('/^\\//', $json->px2dtconfig->paths_module_template->Modules1), 1 );
		$this->assertEquals( preg_match('/\\/$/', $json->px2dtconfig->paths_module_template->Modules1), 1 );

		// check_status
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/before_sitemap/.px_execute.php', '/?PX=px2dthelper.check_status' ] ));
		$this->assertEquals( $json->check_status->px2dthelper, $output );
		$this->assertEquals( $json->check_status->pxfw_api->version, $json->version->pxfw );

		// realpath_data_dir
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/before_sitemap/.px_execute.php', '/?PX=px2dthelper.get.realpath_data_dir' ] ));
		$this->assertEquals( $json->realpath_data_dir, $output );

		// path_resource_dir
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/before_sitemap/.px_execute.php', '/?PX=px2dthelper.get.path_resource_dir' ] ));
		$this->assertEquals( $json->path_resource_dir, $output );

		// custom_fields
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/before_sitemap/.px_execute.php', '/?PX=px2dthelper.get.custom_fields' ] ));
		$this->assertEquals( $json->custom_fields, $output );

		// realpath_homedir
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/before_sitemap/.px_execute.php', '/?PX=api.get.path_homedir' ] ));
		$this->assertEquals( $json->realpath_homedir, $output );

		// path_controot
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/before_sitemap/.px_execute.php', '/?PX=api.get.path_controot' ] ));
		$this->assertEquals( $json->path_controot, $output );

		// realpath_docroot
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/before_sitemap/.px_execute.php', '/?PX=api.get.path_docroot' ] ));
		$this->assertEquals( $json->realpath_docroot, $output );

		// page_info
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/before_sitemap/.px_execute.php', '/?PX=api.get.page_info&path=/' ] ));
		$this->assertEquals( $json->page_info, $output );

		// path_files
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/before_sitemap/.px_execute.php', '/?PX=api.get.path_files&path=/' ] ));
		$this->assertEquals( $json->path_files, $output );

		// realpath_files
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/before_sitemap/.px_execute.php', '/?PX=api.get.realpath_files&path=/' ] ));
		$this->assertEquals( $json->realpath_files, $output );

		// navigaton_info
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/before_sitemap/.px_execute.php', '/?PX=px2dthelper.get.navigation_info' ] ));
		$this->assertEquals( $json->navigation_info, $output );

		// 後始末
		$output = $this->px2query->query( [
			__DIR__.'/testData/before_sitemap/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	}


	/**
	 * $conf->path_files が callable な場合
	 */
	public function testGetAllCallablePathFiles(){

		$this->fs->copy(
			__DIR__.'/testData/standard/px-files/_configs/config_path_files_callable.php',
			__DIR__.'/testData/standard/px-files/config.php'
		);
		$this->fs->copy(
			__DIR__.'/testData/standard/px-files/_configs/px2dtconfig_path_files_callable.json',
			__DIR__.'/testData/standard/px-files/px2dtconfig.json'
		);

		// Pickles 2 実行
		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.get.all' ] );
		$json = json_decode( $output );
		$this->assertTrue( is_object($json) );

		// Pickles 2 のバージョン番号
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.version' ] ));
		$this->assertEquals( $json->version->pxfw, $output );

		// px2dthelper のバージョン番号
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.version' ] ));
		$this->assertEquals( $json->version->px2dthelper, $output );

		// config
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.config' ] ));
		$this->assertTrue( is_object($json->config) );

		// px2dtconfig
		$this->assertTrue( is_object($json->px2dtconfig) );
		$this->assertTrue( is_object($json->px2dtconfig->paths_module_template) );
			// ↓ スラッシュで始まり スラッシュで終わる 絶対パスで得られる。
			// ↓ WindowsでもUNIXスタイルに正規化される。
		$this->assertEquals( preg_match('/^\\//', $json->px2dtconfig->paths_module_template->Modules1), 1 );
		$this->assertEquals( preg_match('/\\/$/', $json->px2dtconfig->paths_module_template->Modules1), 1 );

		// check_status
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.check_status' ] ));
		$this->assertEquals( $json->check_status->px2dthelper, $output );
		$this->assertEquals( $json->check_status->pxfw_api->version, $json->version->pxfw );

		// realpath_data_dir
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.get.realpath_data_dir' ] ));
		$this->assertEquals( $json->realpath_data_dir, $output );

		// path_resource_dir
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.get.path_resource_dir' ] ));
		$this->assertEquals( $json->path_resource_dir, $output );
		$this->assertEquals( '/index_files/resources/', $output );

		// custom_fields
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.get.custom_fields' ] ));
		$this->assertEquals( $json->custom_fields, $output );

		// realpath_homedir
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.path_homedir' ] ));
		$this->assertEquals( $json->realpath_homedir, $output );

		// path_controot
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.path_controot' ] ));
		$this->assertEquals( $json->path_controot, $output );

		// realpath_docroot
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.path_docroot' ] ));
		$this->assertEquals( $json->realpath_docroot, $output );

		// page_info
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.page_info&path=/' ] ));
		$this->assertEquals( $json->page_info, $output );

		// path_files
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.path_files&path=/' ] ));
		$this->assertEquals( $json->path_files, $output );
		$this->assertEquals( '/index_files/', $output );

		// realpath_files
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.realpath_files&path=/' ] ));
		$this->assertEquals( $json->realpath_files, $output );

		// navigaton_info
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.get.navigation_info' ] ));
		$this->assertEquals( $json->navigation_info, $output );

		// 後始末
		$this->fs->copy(
			__DIR__.'/testData/standard/px-files/_configs/config.php',
			__DIR__.'/testData/standard/px-files/config.php'
		);
		$this->fs->copy(
			__DIR__.'/testData/standard/px-files/_configs/px2dtconfig.json',
			__DIR__.'/testData/standard/px-files/px2dtconfig.json'
		);
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	}

	/**
	 * $conf->path_files が function な場合
	 */
	public function testGetAllPathFilesIsFunction(){

		$this->fs->copy(
			__DIR__.'/testData/standard/px-files/_configs/config_path_files_function.php',
			__DIR__.'/testData/standard/px-files/config.php'
		);
		$this->fs->copy(
			__DIR__.'/testData/standard/px-files/_configs/px2dtconfig_path_files_function.json',
			__DIR__.'/testData/standard/px-files/px2dtconfig.json'
		);

		// Pickles 2 実行
		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.get.all' ] );
		$json = json_decode( $output );
		$this->assertTrue( is_object($json) );

		// Pickles 2 のバージョン番号
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.version' ] ));
		$this->assertEquals( $json->version->pxfw, $output );

		// px2dthelper のバージョン番号
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.version' ] ));
		$this->assertEquals( $json->version->px2dthelper, $output );

		// config
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.config' ] ));
		$this->assertTrue( is_object($json->config) );
		$this->assertEquals( $json->config, $output );

		// px2dtconfig
		$this->assertTrue( is_object($json->px2dtconfig) );
		$this->assertTrue( is_object($json->px2dtconfig->paths_module_template) );
			// ↓ スラッシュで始まり スラッシュで終わる 絶対パスで得られる。
			// ↓ WindowsでもUNIXスタイルに正規化される。
		$this->assertEquals( preg_match('/^\\//', $json->px2dtconfig->paths_module_template->Modules1), 1 );
		$this->assertEquals( preg_match('/\\/$/', $json->px2dtconfig->paths_module_template->Modules1), 1 );

		// check_status
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.check_status' ] ));
		$this->assertEquals( $json->check_status->px2dthelper, $output );
		$this->assertEquals( $json->check_status->pxfw_api->version, $json->version->pxfw );

		// realpath_data_dir
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.get.realpath_data_dir' ] ));
		$this->assertEquals( $json->realpath_data_dir, $output );

		// path_resource_dir
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.get.path_resource_dir' ] ));
		$this->assertEquals( $json->path_resource_dir, $output );
		$this->assertEquals( '/index_files/resources/', $output );

		// custom_fields
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.get.custom_fields' ] ));
		$this->assertEquals( $json->custom_fields, $output );

		// path_homedir
		$this->assertEquals( $json->path_homedir, './px-files/' );
			// NOTE: Pickles Framework に `$px->get_path_homedir()` があるが、
			//       このメソッドは `$px->get_realpath_homedir()` の古い名前であり、絶対パスが返される。
			//       過去の挙動を壊さないように、このメソッドの振る舞いは変更しない。
			//       なので、代わりに `$this->get_path_homedir()` を作り、これを使うことにした。

		// realpath_homedir
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.path_homedir' ] ));
		$this->assertEquals( $json->realpath_homedir, $output );

		// path_controot
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.path_controot' ] ));
		$this->assertEquals( $json->path_controot, $output );

		// realpath_docroot
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.path_docroot' ] ));
		$this->assertEquals( $json->realpath_docroot, $output );

		// page_info
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.page_info&path=/' ] ));
		$this->assertEquals( $json->page_info, $output );

		// path_files
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.path_files&path=/' ] ));
		$this->assertEquals( $json->path_files, $output );
		$this->assertEquals( '/index_files/', $output );

		// realpath_files
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.realpath_files&path=/' ] ));
		$this->assertEquals( $json->realpath_files, $output );

		// navigaton_info
		$output = json_decode($this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.get.navigation_info' ] ));
		$this->assertEquals( $json->navigation_info, $output );

		// 後始末
		$this->fs->copy(
			__DIR__.'/testData/standard/px-files/_configs/config.php',
			__DIR__.'/testData/standard/px-files/config.php'
		);
		$this->fs->copy(
			__DIR__.'/testData/standard/px-files/_configs/px2dtconfig.json',
			__DIR__.'/testData/standard/px-files/px2dtconfig.json'
		);
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	}

}
