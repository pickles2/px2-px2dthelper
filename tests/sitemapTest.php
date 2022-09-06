<?php
/**
 * Test for pickles2\px2-px2dthelper
 */

class sitemapTest extends PHPUnit\Framework\TestCase{

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
	 * PX=px2dthelper.sitemap.create のテスト
	 */
	public function testSitemapCreate(){

		// ---------------------------
		// 新しいサイトマップファイルを作成
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/?PX=px2dthelper.sitemap.create&filename=create_new_sitemap'
		] );
		clearstatcache();
		$json = json_decode( $output );
		$this->assertTrue( is_object($json) );
		$this->assertTrue( $json->result );
		$this->assertTrue( $this->fs->is_file( __DIR__.'/testData/standard/px-files/sitemaps/create_new_sitemap.csv' ) );
		$this->assertTrue( $this->fs->is_file( __DIR__.'/testData/standard/px-files/sitemaps/create_new_sitemap.xlsx' ) );


		// ---------------------------
		// 同じファイル名でもう一度作成
		// ただし、ファイル名の一部を大文字に。大文字・小文字は区別しないのが正解。
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/?PX=px2dthelper.sitemap.create&filename=create_NEW_sitemap'
		] );
		clearstatcache();
		$json = json_decode( $output );
		$this->assertTrue( is_object($json) );
		$this->assertFalse( $json->result ); // 失敗 `false` が得られる
		$this->assertTrue( $this->fs->is_file( __DIR__.'/testData/standard/px-files/sitemaps/create_new_sitemap.csv' ) );
		$this->assertTrue( $this->fs->is_file( __DIR__.'/testData/standard/px-files/sitemaps/create_new_sitemap.xlsx' ) );

	} // testSitemapCreate()

	/**
	 * PX=px2dthelper.sitemap.csv2xlsx のテスト
	 */
	public function testSitemapCsv2Xlsx(){

		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/?PX=px2dthelper.sitemap.csv2xlsx&filename=create_new_sitemap'
		] );
		clearstatcache();
		$json = json_decode( $output );
		$this->assertTrue( is_object($json) );
		$this->assertTrue( $json->result );
		$this->assertTrue( $this->fs->is_file( __DIR__.'/testData/standard/px-files/sitemaps/create_new_sitemap.csv' ) );
		$this->assertTrue( $this->fs->is_file( __DIR__.'/testData/standard/px-files/sitemaps/create_new_sitemap.xlsx' ) );

	} // testSitemapCsv2Xlsx()

	/**
	 * PX=px2dthelper.sitemap.xlsx2csv のテスト
	 */
	public function testSitemapXlsx2Csv(){

		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/?PX=px2dthelper.sitemap.xlsx2csv&filename=create_new_sitemap'
		] );
		clearstatcache();
		$json = json_decode( $output );
		$this->assertTrue( is_object($json) );
		$this->assertTrue( $json->result );
		$this->assertTrue( $this->fs->is_file( __DIR__.'/testData/standard/px-files/sitemaps/create_new_sitemap.csv' ) );
		$this->assertTrue( $this->fs->is_file( __DIR__.'/testData/standard/px-files/sitemaps/create_new_sitemap.xlsx' ) );

	} // testSitemapXlsx2Csv()

	/**
	 * PX=px2dthelper.sitemap.filelist のテスト
	 */
	public function testSitemapFileList(){

		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/?PX=px2dthelper.sitemap.filelist'
		] );
		clearstatcache();
		$json = json_decode( $output );
		$this->assertTrue( is_object($json) );
		$this->assertTrue( $json->result );
		$this->assertIsString( $json->message );
		$this->assertIsObject( $json->list );
		$this->assertIsObject( $json->list_origcase );
		$this->assertIsArray( $json->fullname_list );
		$this->assertIsArray( $json->fullname_list_origcase );

	} // testSitemapFileList()

	/**
	 * PX=px2dthelper.sitemap.download のテスト
	 */
	public function testSitemapFileDownload(){

		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/?PX=px2dthelper.sitemap.download&filefullname=create_new_sitemap.xlsx'
		] );
		clearstatcache();
		$json = json_decode( $output );
		$this->assertTrue( is_object($json) );
		$this->assertTrue( $json->result );
		$this->assertIsString( $json->message );
		$this->assertSame( $json->filename, 'create_new_sitemap.xlsx' );
		$this->assertIsString( $json->base64 );
		$this->assertFalse( isset($json->bin) );

	} // testSitemapFileDownload()

	/**
	 * PX=px2dthelper.page.add_page_info_raw のテスト
	 */
	public function testPageAddPageInfoRaw(){

		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/?PX=clearcache'
		] );
		$page_info = array(
			'page_info' => array(
				'path'=>'/added_page_sample/index.html',
				'title'=>'Page Title',
			),
		);
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/?PX=px2dthelper.page.add_page_info_raw&filefullname=create_new_sitemap.csv&row=1&'.http_build_query($page_info)
		] );
		clearstatcache();
		$json = json_decode( $output );
		$this->assertTrue( is_object($json) );
		$this->assertTrue( $json->result );

		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/?PX=clearcache'
		] );
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/added_page_sample/?PX=api.get.page_info'
		] );


		// --------------------------------------
		// パスが重複しているために失敗するテスト
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/?PX=px2dthelper.page.add_page_info_raw&filefullname=create_new_sitemap.csv&row=1&'.http_build_query($page_info)
		] );
		clearstatcache();
		$json = json_decode( $output );
		$this->assertTrue( is_object($json) );
		$this->assertFalse( $json->result ); // 失敗 `false` が得られる

	} // testPageAddPageInfoRaw()

	/**
	 * PX=px2dthelper.page.get_page_info_raw のテスト
	 */
	public function testPageGetPageInfoRaw(){

		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/?PX=clearcache'
		] );
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/?PX=px2dthelper.page.get_page_info_raw&filefullname=create_new_sitemap.csv&row=1'
		] );
		clearstatcache();
		$json = json_decode( $output );
		$this->assertTrue( is_object($json) );
		$this->assertTrue( $json->result );
		$this->assertIsArray( $json->sitemap_definition );
		$this->assertIsArray( $json->page_info );
		$this->assertSame( $json->page_info[0], '/added_page_sample/index.html' );
		$this->assertSame( $json->page_info[3], 'Page Title' );

	} // testPageGetPageInfoRaw()

	/**
	 * PX=px2dthelper.page.move_page_info_raw のテスト
	 */
	public function testPageMovePageInfoRaw(){

		// 準備
		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.sitemap.create&filename=create_new_sitemap_2' ] );

		for( $i = 2; $i <= 11; $i ++ ){
			$output = $this->px2query->query( [
				__DIR__.'/testData/standard/.px_execute.php',
				'/?PX=px2dthelper.page.add_page_info_raw&filefullname=create_new_sitemap.csv&row='.$i.'&'.http_build_query(array(
					'page_info' => array(
						'path'=>'/added_page_sample/sitemap/'.$i.'.html',
						'title'=>'Sitemap 1 - Page Title '.$i,
						'description'=>'description - Sitemap 1 - '.$i,
					),
				))
			] );
		}
		$tmp_sitemap = array(array(
			'* id',
			'* path',
			'* title',
		));
		for( $i = 1; $i <= 10; $i ++ ){
			array_push($tmp_sitemap, array(
				'',
				'/added_page_sample/sitemap_2/'.$i.'.html',
				'Sitemap 2 - Page Title '.$i,
			));
		}
		$this->fs->save_file(__DIR__.'/testData/standard/px-files/sitemaps/create_new_sitemap_2.csv', $this->fs->mk_csv($tmp_sitemap));

		clearstatcache();

		// --------------------------------------
		// ページの行を移動させる (同じファイル内)
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/?PX=px2dthelper.page.move_page_info_raw'
				.'&from_filefullname=create_new_sitemap.csv'
				.'&from_row=9'
				.'&to_filefullname=create_new_sitemap.csv'
				.'&to_row=5'
		] );
		$json = json_decode( $output );
		$this->assertTrue( $json->result );

		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.page.get_page_info_raw&filefullname=create_new_sitemap.csv&row=5' ] );
		$json = json_decode( $output );
		$this->assertSame( $json->page_info[0], '/added_page_sample/sitemap/9.html' );
		$this->assertSame( $json->page_info[3], 'Sitemap 1 - Page Title 9' );
		$this->assertSame( $json->page_info[13], 'description - Sitemap 1 - 9' );

		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/?PX=px2dthelper.page.move_page_info_raw'
				.'&from_filefullname=create_new_sitemap.csv'
				.'&from_row=5'
				.'&to_filefullname=create_new_sitemap.csv'
				.'&to_row=10'
		] );
		$json = json_decode( $output );
		$this->assertTrue( $json->result );
		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.page.get_page_info_raw&filefullname=create_new_sitemap.csv&row=9' ] );
		$json = json_decode( $output );
		$this->assertSame( $json->page_info[0], '/added_page_sample/sitemap/9.html' );
		$this->assertSame( $json->page_info[3], 'Sitemap 1 - Page Title 9' );
		$this->assertSame( $json->page_info[13], 'description - Sitemap 1 - 9' );


		// --------------------------------------
		// ページの行を移動させる (別のファイル内)
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/?PX=px2dthelper.page.move_page_info_raw'
				.'&from_filefullname=create_new_sitemap.csv'
				.'&from_row=9'
				.'&to_filefullname=create_new_sitemap_2.csv'
				.'&to_row=5'
		] );
		$json = json_decode( $output );
		$this->assertTrue( $json->result );
		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.page.get_page_info_raw&filefullname=create_new_sitemap_2.csv&row=5' ] );
		$json = json_decode( $output );
		$this->assertSame( count($json->page_info), 3 );
		$this->assertSame( $json->page_info[1], '/added_page_sample/sitemap/9.html' );
		$this->assertSame( $json->page_info[2], 'Sitemap 1 - Page Title 9' );

		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/?PX=px2dthelper.page.move_page_info_raw'
				.'&from_filefullname=create_new_sitemap_2.csv'
				.'&from_row=5'
				.'&to_filefullname=create_new_sitemap.csv'
				.'&to_row=9'
		] );
		$json = json_decode( $output );
		$this->assertTrue( $json->result );
		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.page.get_page_info_raw&filefullname=create_new_sitemap.csv&row=9' ] );
		$json = json_decode( $output );
		$this->assertSame( $json->page_info[0], '/added_page_sample/sitemap/9.html' );
		$this->assertSame( $json->page_info[3], 'Sitemap 1 - Page Title 9' );
		$this->assertSame( $json->page_info[13], '' );



		// --------------------------------------
		// 対象ページがない (エラー)
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/?PX=px2dthelper.page.move_page_info_raw'
				.'&from_filefullname=notexist_sitemap.csv'
				.'&from_row=1'
				.'&to_filefullname=create_new_sitemap.csv'
				.'&to_row=5'
		] );
		$json = json_decode( $output );
		$this->assertFalse( $json->result );
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/?PX=px2dthelper.page.move_page_info_raw'
				.'&from_filefullname=create_new_sitemap.csv'
				.'&from_row=1'
				.'&to_filefullname=notexist_sitemap.csv'
				.'&to_row=5'
		] );
		$json = json_decode( $output );
		$this->assertFalse( $json->result );


	} // testPageMovePageInfoRaw()

	/**
	 * PX=px2dthelper.page.update_page_info_raw のテスト
	 */
	public function testPageUpdatePageInfoRaw(){

		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/?PX=clearcache'
		] );
		$page_info = array(
			'page_info' => array(
				'path'=>'/added_page_sample/2.html',
				'title'=>'Page Title 2',
			),
		);
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/?PX=px2dthelper.page.update_page_info_raw&filefullname=create_new_sitemap.csv&row=1&'.http_build_query($page_info)
		] );
		clearstatcache();
		$json = json_decode( $output );
		$this->assertTrue( is_object($json) );
		$this->assertTrue( $json->result );

		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/?PX=px2dthelper.page.get_page_info_raw&filefullname=create_new_sitemap.csv&row=1'
		] );
		clearstatcache();
		$json = json_decode( $output );
		$this->assertTrue( is_object($json) );
		$this->assertTrue( $json->result );
		$this->assertIsArray( $json->sitemap_definition );
		$this->assertIsArray( $json->page_info );
		$this->assertSame( $json->page_info[0], '/added_page_sample/2.html' );
		$this->assertSame( $json->page_info[3], 'Page Title 2' );

	} // testPageUpdatePageInfoRaw()

	/**
	 * PX=px2dthelper.page.update_page_info_raw のテスト - パンくずの変化をともなう変更
	 */
	public function testPageUpdatePageInfoRaw_ChangeLogicalPath(){

		// 事前準備
		$tmp_sitemap = array(
			array('* path', '* id', '* content', '* title', '* logical_path', ),
			array('/add_page_test/index.html', '', '', 'Test Page 1', '', ),
			array('/add_page_test/a/', '', '', 'Test Page C-A (before change)', '/add_page_test/', ),
			array('/add_page_test/a/a/', '', '', 'Test Page C-A-A', '/add_page_test/>/add_page_test/a/', ),
			array('/add_page_test/a/b/', '', '', 'Test Page C-A-B', '/add_page_test/>/add_page_test/a/', ),
			array('/add_page_test/a/c/', 'add_page_test_a_c', '', 'Test Page C-A-C', '/add_page_test/>/add_page_test/a/', ),
			array('/add_page_test/a/c/a/', '', '', 'Test Page C-A-C-A', '/add_page_test/>/add_page_test/a/>/add_page_test/a/c/', ),
			array('/add_page_test/a/c/b/', '', '', 'Test Page C-A-C-B', '/add_page_test/>/add_page_test/a/>add_page_test_a_c', ),
			array('/add_page_test/a/c/c/', '', '', 'Test Page C-A-C-C', '/add_page_test/index.html>/add_page_test/a/index.html>/add_page_test/a/c/index.html', ),
			array('/add_page_test/b/', '', '', 'Test Page C-B', '/add_page_test/index.html', ),
			array('/add_page_test/c/', '', '', 'Test Page C-C', '/add_page_test/', ),
			array('/update_page_test/index.html', '', '', 'Test Page 2', '', ),
		);
		$this->fs->save_file(__DIR__.'/testData/standard/px-files/sitemaps/create_new_sitemap_3.csv', $this->fs->mk_csv($tmp_sitemap));
		$this->assertTrue( is_file(__DIR__.'/testData/standard/px-files/sitemaps/create_new_sitemap_3.csv') );

		$tmp_sitemap = array(
			array('* path', '* id', '* content', '* title', '* logical_path', ),
			array('/add_page_test/a/c/d/', '', '', 'Test Page C-A-C-D', '/add_page_test/>/add_page_test/a/>/add_page_test/a/c/', ),
			array('/add_page_test/a/c/e/', '', '', 'Test Page C-A-C-E', '/add_page_test/index.html>/add_page_test/a/index.html>/add_page_test/a/c/', ),
		);
		$this->fs->save_file(__DIR__.'/testData/standard/px-files/sitemaps/create_new_sitemap_4.csv', $this->fs->mk_csv($tmp_sitemap));
		$this->assertTrue( is_file(__DIR__.'/testData/standard/px-files/sitemaps/create_new_sitemap_4.csv') );

		// キャッシュを削除
		$output = $this->px2query->query( [__DIR__.'/testData/standard/.px_execute.php', '/?PX=clearcache' ] );

		// --------------------------------------
		// ページ "Test Page C-A" のパスとパンくずを変更する
		$page_info = array(
			'page_info' => array(
				'path'=>'/changed_new_path/a/',
				'title'=>'*** Test Page C-A (changed)',
				'logical_path'=>'/update_page_test/',
			),
		);
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/?PX=px2dthelper.page.update_page_info_raw&filefullname=create_new_sitemap_3.csv&row=2&'.http_build_query($page_info)
		] );
		clearstatcache();
		$json = json_decode( $output );
		$this->assertTrue( is_object($json) );
		$this->assertTrue( $json->result );

		// 本体
		$json = json_decode( $this->px2query->query( [__DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.page.get_page_info_raw&filefullname=create_new_sitemap_3.csv&row=2'] ) );
		$this->assertSame( $json->page_info[0], '/changed_new_path/a/' ); // path
		$this->assertSame( $json->page_info[3], '*** Test Page C-A (changed)' ); // title
		$this->assertSame( $json->page_info[4], '/update_page_test/' ); // logical_path

		// 子ページ
		$json = json_decode( $this->px2query->query( [__DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.page.get_page_info_raw&filefullname=create_new_sitemap_3.csv&row=3'] ) );
		$this->assertSame( $json->page_info[4], '/update_page_test/>/changed_new_path/a/' ); // logical_path
		$json = json_decode( $this->px2query->query( [__DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.page.get_page_info_raw&filefullname=create_new_sitemap_3.csv&row=4'] ) );
		$this->assertSame( $json->page_info[4], '/update_page_test/>/changed_new_path/a/' ); // logical_path
		$json = json_decode( $this->px2query->query( [__DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.page.get_page_info_raw&filefullname=create_new_sitemap_3.csv&row=5'] ) );
		$this->assertSame( $json->page_info[4], '/update_page_test/>/changed_new_path/a/' ); // logical_path
		$json = json_decode( $this->px2query->query( [__DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.page.get_page_info_raw&filefullname=create_new_sitemap_3.csv&row=6'] ) );
		$this->assertSame( $json->page_info[4], '/update_page_test/>/changed_new_path/a/>/add_page_test/a/c/' ); // logical_path
		$json = json_decode( $this->px2query->query( [__DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.page.get_page_info_raw&filefullname=create_new_sitemap_3.csv&row=7'] ) );
		$this->assertSame( $json->page_info[4], '/update_page_test/>/changed_new_path/a/>add_page_test_a_c' ); // logical_path
		$json = json_decode( $this->px2query->query( [__DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.page.get_page_info_raw&filefullname=create_new_sitemap_3.csv&row=8'] ) );
		$this->assertSame( $json->page_info[4], '/update_page_test/>/changed_new_path/a/>/add_page_test/a/c/index.html' ); // logical_path

		$json = json_decode( $this->px2query->query( [__DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.page.get_page_info_raw&filefullname=create_new_sitemap_4.csv&row=1'] ) );
		$this->assertSame( $json->page_info[4], '/update_page_test/>/changed_new_path/a/>/add_page_test/a/c/' ); // logical_path
		$json = json_decode( $this->px2query->query( [__DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.page.get_page_info_raw&filefullname=create_new_sitemap_4.csv&row=2'] ) );
		$this->assertSame( $json->page_info[4], '/update_page_test/>/changed_new_path/a/>/add_page_test/a/c/' ); // logical_path

	} // testPageUpdatePageInfoRaw_ChangeLogicalPath()

	/**
	 * PX=px2dthelper.page.delete_page_info_raw のテスト
	 */
	public function testPageDeletePageInfoRaw(){

		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/?PX=clearcache'
		] );
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/?PX=px2dthelper.page.delete_page_info_raw&filefullname=create_new_sitemap.csv&row=1'
		] );
		clearstatcache();
		$json = json_decode( $output );
		$this->assertTrue( is_object($json) );
		$this->assertTrue( $json->result );

		// --------------------------------------
		// 存在しない行を削除する (エラー)
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/?PX=clearcache'
		] );
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/?PX=px2dthelper.page.get_page_info_raw&filefullname=create_new_sitemap.csv&row=100'
		] );
		clearstatcache();
		$json = json_decode( $output );
		$this->assertTrue( is_object($json) );
		$this->assertFalse( $json->result ); // 失敗 `false` が得られる
		$this->assertSame( $json->message, 'Invalid row number.' );

	} // testPageDeletePageInfoRaw()

	/**
	 * PX=px2dthelper.sitemap.delete のテスト
	 */
	public function testSitemapDelete(){

		// ---------------------------
		// サイトマップファイルを削除
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/?PX=px2dthelper.sitemap.delete&filename=create_new_sitemap'
		] );
		clearstatcache();
		$json = json_decode( $output );
		$this->assertTrue( is_object($json) );
		$this->assertTrue( $json->result );
		$this->assertFalse( $this->fs->is_file( __DIR__.'/testData/standard/px-files/sitemaps/create_new_sitemap.csv' ) );
		$this->assertFalse( $this->fs->is_file( __DIR__.'/testData/standard/px-files/sitemaps/create_new_sitemap.xlsx' ) );

		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/?PX=px2dthelper.sitemap.delete&filename=create_new_sitemap_2'
		] );
		$this->assertFalse( $this->fs->is_file( __DIR__.'/testData/standard/px-files/sitemaps/create_new_sitemap_2.csv' ) );
		$this->assertFalse( $this->fs->is_file( __DIR__.'/testData/standard/px-files/sitemaps/create_new_sitemap_2.xlsx' ) );

		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/?PX=px2dthelper.sitemap.delete&filename=create_new_sitemap_3'
		] );
		$this->assertFalse( $this->fs->is_file( __DIR__.'/testData/standard/px-files/sitemaps/create_new_sitemap_3.csv' ) );
		$this->assertFalse( $this->fs->is_file( __DIR__.'/testData/standard/px-files/sitemaps/create_new_sitemap_3.xlsx' ) );

		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'/?PX=px2dthelper.sitemap.delete&filename=create_new_sitemap_4'
		] );
		$this->assertFalse( $this->fs->is_file( __DIR__.'/testData/standard/px-files/sitemaps/create_new_sitemap_4.csv' ) );
		$this->assertFalse( $this->fs->is_file( __DIR__.'/testData/standard/px-files/sitemaps/create_new_sitemap_4.xlsx' ) );

		// 後始末
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	} // testSitemapDelete()

}
