<?php
/**
 * Test for pickles2\px2-px2dthelper
 */

class customConsoleExtensionsTest extends PHPUnit\Framework\TestCase{

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
	 * PX=px2dthelper.custom_console_extensions のテスト
	 */
	public function testCustomConsoleExtensions(){

		// ----------------------------------------
		// PX Command: Custom Console Extensions の一覧取得
		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.custom_console_extensions' ] );
		$json = json_decode( $output );
		$this->assertTrue( is_object($json) );
		$this->assertSame( $json->result, true );
		$this->assertSame( $json->message, 'OK' );
		$this->assertSame( $json->list->customConsoleExtensionsTest0001->id, 'customConsoleExtensionsTest0001' );
		$this->assertSame( $json->list->customConsoleExtensionsTest0001->label, '拡張機能0001' );
		$this->assertSame( $json->list->customConsoleExtensionsTest0001->capability, array() );
		$this->assertSame( $json->list->customConsoleExtensionsTest0001->client_initialize_function, 'window.customConsoleExtensionsTest0001' );
		$this->assertSame( $json->list->customConsoleExtensionsTest0003->id, 'customConsoleExtensionsTest0003' );
		$this->assertSame( $json->list->customConsoleExtensionsTest0003->label, '拡張機能0003' );
		$this->assertSame( $json->list->customConsoleExtensionsTest0003->capability, array("manage") );
		$this->assertSame( $json->list->customConsoleExtensionsTest0003->client_initialize_function, 'window.customConsoleExtensionsTest0001' );

		// ----------------------------------------
		// PX Command: Custom Console Extensions 存在しない拡張機能の情報取得
		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.custom_console_extensions.undefinedCustomConsoleExtension' ] );
		$json = json_decode( $output );
		$this->assertTrue( is_object($json) );
		$this->assertFalse( $json->result );
		$this->assertSame( $json->message, 'Custom Console Extension "undefinedCustomConsoleExtension" is NOT available.' );

		// ----------------------------------------
		// PX Command: Custom Console Extensions 拡張機能0001の情報取得
		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.custom_console_extensions.customConsoleExtensionsTest0001' ] );
		$json = json_decode( $output );
		$this->assertTrue( is_object($json) );
		$this->assertSame( $json->result, true );
		$this->assertSame( $json->message, 'OK' );
		$this->assertSame( $json->info->id, 'customConsoleExtensionsTest0001' );
		$this->assertSame( $json->info->label, '拡張機能0001' );
		$this->assertSame( $json->info->capability, array() );
		$this->assertSame( $json->info->client_initialize_function, 'window.customConsoleExtensionsTest0001' );

		// ----------------------------------------
		// PX Command: Custom Console Extensions のクライアント資材一覧取得
		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.custom_console_extensions.customConsoleExtensionsTest0001.client_resources' ] );
		$json = json_decode( $output );
		$this->assertTrue( is_object($json) );
		$this->assertSame( $json->result, true );
		$this->assertSame( $json->message, 'OK' );
		$this->assertSame( $json->path_base, null );
		$this->assertSame( $json->resources->css[0], realpath(__DIR__.'/testData/standard/px-files/customConsoleExtensions/customConsoleExtensionsTest0001/resources/styles/cce0001.css') );
		$this->assertSame( $json->resources->js[1], realpath(__DIR__.'/testData/standard/px-files/customConsoleExtensions/customConsoleExtensionsTest0001/resources/scripts/cce0001.js') );
		$this->assertSame( false, is_file(__DIR__.'/testData/standard/px-files/_sys/ram/caches/tmpResTest/'.$json->resources->css[0]) );
		$this->assertSame( false, is_file(__DIR__.'/testData/standard/px-files/_sys/ram/caches/tmpResTest/'.$json->resources->js[1]) );

		// ----------------------------------------
		// PX Command: Custom Console Extensions のクライアント資材一覧取得 (出力先を指定した場合)
		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.custom_console_extensions.customConsoleExtensionsTest0001.client_resources&dist='.__DIR__.'/testData/standard/px-files/_sys/ram/caches/tmpResTest/' ] );
		$json = json_decode( $output );
		$this->assertTrue( is_object($json) );
		$this->assertSame( $json->result, true );
		$this->assertSame( $json->message, 'OK' );
		$this->assertSame( $json->path_base, null );
		$this->assertSame( $json->resources->css[0], 'styles/cce0001.css' );
		$this->assertSame( $json->resources->js[1], 'scripts/cce0001.js' );
		$this->assertSame( true, is_file(__DIR__.'/testData/standard/px-files/_sys/ram/caches/tmpResTest/'.$json->resources->css[0]) );
		$this->assertSame( true, is_file(__DIR__.'/testData/standard/px-files/_sys/ram/caches/tmpResTest/'.$json->resources->js[1]) );


		// ----------------------------------------
		// PX Command: Custom Console Extensions のGPIを呼び出す
		$this->fs->save_file(__DIR__.'/testData/standard/px-files/_sys/ram/data/tmp_request.txt', 'PX=px2dthelper.custom_console_extensions.customConsoleExtensionsTest0001.gpi&request='.urlencode(json_encode(array('command'=>'test-command'))));
		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '--method', 'post', '--body-file', 'tmp_request.txt', '/' ] );
		$json = json_decode( $output );
		$this->assertTrue( is_object($json) );
		$this->assertSame( true, $json->result );
		$this->assertSame( 'OK', $json->message );
		$this->assertTrue( is_object($json->response) );
		$this->assertSame( true, $json->response->result );
		$this->assertSame( 'OK', $json->response->message );
		$this->fs->rm(__DIR__.'/testData/standard/px-files/_sys/ram/data/tmp_request.txt');


		// 後始末
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );
	}

	/**
	 * PX=px2dthelper.custom_console_extensions の認可のテスト
	 */
	public function testAuthorizeCustomConsoleExtensions(){

		// ----------------------------------------
		// PX Command: Custom Console Extensions の一覧取得
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php',
			'--role', 'member',
			'/?PX=px2dthelper.custom_console_extensions',
		] );
		$json = json_decode( $output );
		$this->assertTrue( is_object($json) );
		$this->assertSame( $json->result, true );
		$this->assertSame( $json->message, 'OK' );
		$this->assertSame( $json->list->customConsoleExtensionsTest0001->id, 'customConsoleExtensionsTest0001' );
		$this->assertSame( $json->list->customConsoleExtensionsTest0001->label, '拡張機能0001' );
		$this->assertSame( $json->list->customConsoleExtensionsTest0001->capability, array() );
		$this->assertSame( $json->list->customConsoleExtensionsTest0001->client_initialize_function, 'window.customConsoleExtensionsTest0001' );

		$this->assertSame( $json->list->customConsoleExtensionsTest0002->id, 'customConsoleExtensionsTest0002' );
		$this->assertSame( $json->list->customConsoleExtensionsTest0002->label, '拡張機能0002' );
		$this->assertSame( $json->list->customConsoleExtensionsTest0002->capability, array() );
		$this->assertSame( $json->list->customConsoleExtensionsTest0002->client_initialize_function, 'window.customConsoleExtensionsTest0001' );

		$this->assertFalse( property_exists($json->list, 'customConsoleExtensionsTest0003') );

		// 後始末
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );
	}

	/**
	 * サーバーサイドでappModeを取り扱うテスト
	 */
	public function testAppMode(){

		// ----------------------------------------
		// PX Command: Custom Console Extensions のGPIを呼び出す
		$this->fs->save_file(
			__DIR__.'/testData/standard/px-files/_sys/ram/data/tmp_request.txt',
			'PX=px2dthelper.custom_console_extensions.customConsoleExtensionsTest0001.gpi'
				.'&appMode=desktop'
				.'&request='.urlencode(json_encode(array(
					'command'=>'get-app-mode',
				)))
		);
		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '--method', 'post', '--body-file', 'tmp_request.txt', '/' ] );
		$json = json_decode( $output );
		$this->assertTrue( is_object($json) );
		$this->assertSame( true, $json->result );
		$this->assertSame( 'OK', $json->message );
		$this->assertSame( 'desktop', $json->response->{'appMode'} );
		$this->fs->rm(__DIR__.'/testData/standard/px-files/_sys/ram/data/tmp_request.txt');

		// ----------------------------------------
		// PX Command: Custom Console Extensions のGPIを呼び出す
		$this->fs->save_file(
			__DIR__.'/testData/standard/px-files/_sys/ram/data/tmp_request.txt',
			'PX=px2dthelper.custom_console_extensions.customConsoleExtensionsTest0001.gpi'
				.'&appMode=web'
				.'&request='.urlencode(json_encode(array(
					'command'=>'get-app-mode',
				)))
		);
		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '--method', 'post', '--body-file', 'tmp_request.txt', '/' ] );
		$json = json_decode( $output );
		$this->assertTrue( is_object($json) );
		$this->assertSame( true, $json->result );
		$this->assertSame( 'OK', $json->message );
		$this->assertSame( 'web', $json->response->{'appMode'} );
		$this->fs->rm(__DIR__.'/testData/standard/px-files/_sys/ram/data/tmp_request.txt');


		// 後始末
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	}


	/**
	 * サーバーサイドで非同期処理を実行するテスト
	 */
	public function testAsync(){
		$realpath_output_file = __DIR__.'/testData/standard/px-files/_sys/ram/data/__output.txt';

		// ----------------------------------------
		// Sync
		$this->fs->save_file(
			__DIR__.'/testData/standard/px-files/_sys/ram/data/tmp_request.txt',
			'PX=px2dthelper.custom_console_extensions.customConsoleExtensionsTest0001.gpi'
				.'&appMode=desktop'
				.'&request='.urlencode(json_encode(array(
					'command'=>'test-async',
				)))
		);
		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '--method', 'post', '--body-file', 'tmp_request.txt', '/' ] );
		$json = json_decode( $output );
		$this->assertTrue( is_object($json) );
		$this->assertSame( true, $json->result );
		$this->assertSame( 'OK', $json->message );
		$this->fs->rm(__DIR__.'/testData/standard/px-files/_sys/ram/data/tmp_request.txt');

		$this->assertTrue( is_file( $realpath_output_file ) );
		$this->assertSame( 'test-async-run: called.', file_get_contents( $realpath_output_file ) );
		unlink( $realpath_output_file );


		// ----------------------------------------
		// File

		$realpath_sync_dir = __DIR__.'/testData/standard/px-files/_sys/ram/data/syncDir/';
		if(is_dir($realpath_sync_dir)){
			$this->fs->rm($realpath_sync_dir);
		}
		$this->fs->mkdir($realpath_sync_dir);
		$this->fs->save_file(
			__DIR__.'/testData/standard/px-files/_sys/ram/data/tmp_request.txt',
			'PX=px2dthelper.custom_console_extensions.customConsoleExtensionsTest0001.gpi'
				.'&asyncMethod=file'
				.'&asyncDir='.urlencode($realpath_sync_dir)
				.'&appMode=desktop'
				.'&request='.urlencode(json_encode(array(
					'command'=>'test-async',
				)))
		);

		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '--method', 'post', '--body-file', 'tmp_request.txt', '/' ] );
		$json = json_decode( $output );
		$this->assertTrue( is_object($json) );
		$this->assertSame( true, $json->result );
		$this->assertSame( 'OK', $json->message );
		$this->fs->rm(__DIR__.'/testData/standard/px-files/_sys/ram/data/tmp_request.txt');

		clearstatcache();
		$files = $this->fs->ls( $realpath_sync_dir );
		$this->assertSame( 1, count($files) );
		$json = json_decode( file_get_contents( $realpath_sync_dir.$files[0] ) );
		$this->assertSame( 'customConsoleExtensionsTest0001', $json->cce_id );

		$this->fs->save_file(
			__DIR__.'/testData/standard/px-files/_sys/ram/data/tmp_request.txt',
			'PX=px2dthelper.custom_console_extensions_async_run'
				.'&asyncMethod=file'
				.'&asyncDir='.urlencode($realpath_sync_dir)
				.'&appMode=desktop'
		);
		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '--method', 'post', '--body-file', 'tmp_request.txt', '/' ] );
		$json = json_decode( $output );
		$this->assertTrue( is_object($json) );
		$this->assertSame( true, $json->result );
		$this->assertSame( 'OK', $json->message );
		$this->fs->rm(__DIR__.'/testData/standard/px-files/_sys/ram/data/tmp_request.txt');

		clearstatcache();
		$files = $this->fs->ls( $realpath_sync_dir );
		$this->assertSame( 0, count($files) );

		$this->assertTrue( is_file( $realpath_output_file ) );
		$this->assertSame( 'test-async-run: called.', file_get_contents( $realpath_output_file ) );
		unlink( $realpath_output_file );


		// 後始末
		$this->fs->rm($realpath_sync_dir);
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	}

	/**
	 * broadcast() を実行するテスト
	 */
	public function testBroadcast(){

		// ----------------------------------------
		// File
		$realpath_sync_dir = __DIR__.'/testData/standard/px-files/_sys/ram/data/broadcastDir/';
		$this->fs->mkdir($realpath_sync_dir);
		$this->fs->save_file(
			__DIR__.'/testData/standard/px-files/_sys/ram/data/tmp_request.txt',
			'PX=px2dthelper.custom_console_extensions.customConsoleExtensionsTest0001.gpi'
				.'&broadcastMethod=file'
				.'&broadcastDir='.urlencode($realpath_sync_dir)
				.'&appMode=desktop'
				.'&request='.urlencode(json_encode(array(
					'command'=>'test-broadcast',
				)))
		);
		$output = $this->px2query->query( [ __DIR__.'/testData/standard/.px_execute.php', '--method', 'post', '--body-file', 'tmp_request.txt', '/' ] );
		$json = json_decode( $output );
		$this->assertTrue( is_object($json) );
		$this->assertSame( true, $json->result );
		$this->assertSame( 'OK', $json->message );
		$this->fs->rm(__DIR__.'/testData/standard/px-files/_sys/ram/data/tmp_request.txt');

		clearstatcache();
		$files = $this->fs->ls( $realpath_sync_dir );
		$this->assertSame( 1, count($files) );
		$json = json_decode( file_get_contents( $realpath_sync_dir.$files[0] ) );
		$this->assertSame( 'This is a boroadcast message.', $json->message );


		// 後始末
		$this->fs->rm($realpath_sync_dir);
		$output = $this->px2query->query( [
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	}

}
