<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper\fncs;

/**
 * fncs/change_content_editor_mode.php
 */
class change_content_editor_mode{

	/** Picklesオブジェクト */
	private $px;

	/** px2dthelperオブジェクト */
	private $px2dthelper;

	/**
	 * constructor
	 *
	 * @param object $px2dthelper $px2dthelperオブジェクト
	 * @param object $px $pxオブジェクト
	 */
	public function __construct( $px2dthelper, $px ){
		$this->px2dthelper = $px2dthelper;
		$this->px = $px;
	}

	/**
	 * コンテンツの種類(編集モード)を変更する
	 * @param string $editor_mode_to 変換後の編集モード名
	 * @param string $page_path ページのパス。
	 */
	public function change_content_editor_mode( $editor_mode_to, $page_path=null ){
		if( !strlen(''.$editor_mode_to) ){
			// 必須オプションが省略
			return array(false, 'Option "$editor_mode_to" is required.');
		}

		$page_info = null;
		$path_content_to = null;
		if( $this->px->site() ){
			$page_info = $this->px->site()->get_page_info( $page_path );
			if( is_array($page_info) && array_key_exists('content', $page_info) ){
				$path_content_to = $page_info['content'];
			}
		}
		if( !strlen( ''.$path_content_to ) ){
			$path_content_to = $page_path;
		}
		$path_content_from = $this->px2dthelper->find_page_content( $page_path );
		$editor_mode_from = $this->px2dthelper->check_editor_mode( $page_path );
		$realpath_controot = $this->px->get_path_docroot().$this->px->get_path_controot();
		$realpath_content_from = $realpath_controot.$path_content_from;
		$realpath_content_to = $realpath_controot.$path_content_to;

		if( !$this->px->fs()->is_file($realpath_content_from) ){
			// コンテンツファイルが存在しない
			return array(false, 'Content file it is not exists. Change not applied.');
		}

		if( $editor_mode_to == $editor_mode_from ){
			// 変更なし
			return array(false, 'It is already "'.$editor_mode_to.'" mode. Change not applied.');
		}

		// var_dump( $editor_mode_from, $editor_mode_to );
		// var_dump( $path_content_to );
		// var_dump( $path_content_from );

		$realpath_data_dir = $this->px2dthelper->get_realpath_data_dir( $page_path );
		$code_before = $this->px->fs()->read_file( $realpath_content_from );
		// var_dump($code_before);


		if( $editor_mode_from == 'html.gui' ){
			if( ($editor_mode_to == 'html' || $editor_mode_to == 'htm') && preg_match( '/\\.'.preg_quote($editor_mode_to,'/').'$/i', $realpath_content_to ) ){
				$this->px->fs()->rename_f( $realpath_content_from, $realpath_content_to );
			}else{
				$this->px->fs()->rename_f( $realpath_content_from, $realpath_content_to.'.'.$editor_mode_to );
			}
			$this->px->fs()->rmdir_r( $realpath_data_dir );

		}elseif( $editor_mode_from == 'html' || $editor_mode_from == 'htm' ){
			if( $editor_mode_to == 'html.gui' ){
				$this->px->fs()->rename_f( $realpath_content_from, $realpath_content_to );
				$this->create_gui_data( $realpath_controot, $realpath_data_dir, $code_before );
			}else{
				$this->px->fs()->rename_f( $realpath_content_from, $realpath_content_to.'.'.$editor_mode_to );
			}

		}else{
			if( $editor_mode_to == 'html.gui' || $editor_mode_to == 'html' || $editor_mode_to == 'htm' ){
				if( preg_match( '/\\.html?$/i', $realpath_content_to ) ){
					$this->px->fs()->rename_f( $realpath_content_from, $realpath_content_to );
				}else{
					$this->px->fs()->rename_f( $realpath_content_from, $realpath_controot.$path_content_to.'.'.($editor_mode_to=='html.gui' ? 'html.gui' : $editor_mode_to) );
				}
				if( $editor_mode_to == 'html.gui' ){
					$this->create_gui_data( $realpath_controot, $realpath_data_dir, $code_before );
				}else{
					$this->px->fs()->rmdir_r( $realpath_data_dir );
				}
			}else{
				$this->px->fs()->rename_f( $realpath_content_from, $realpath_content_to.'.'.$editor_mode_to );
				$this->px->fs()->rmdir_r( $realpath_data_dir );
			}

		}
		return array(true, 'ok');
	}

	/**
	 * GUI編集のデータを生成する
	 * @param  string $realpath_controot コンテンツルートのサーバー内部絶対パス
	 * @param  string $realpath_data_dir GUI編集データディレクトリのサーバー内部絶対パス
	 * @param  string $code_before       変換前のコンテンツのソースコード
	 * @return null null
	 */
	private function create_gui_data( $realpath_controot, $realpath_data_dir, $code_before ){
		if( !$this->px->fs()->is_dir( $realpath_data_dir ) ){
			$this->px->fs()->mkdir_r( $realpath_data_dir );
		}
		$this->px->fs()->save_file(
			$realpath_data_dir.'/data.json',
			json_encode( array(
				"bowl" => array(
					"main" => array(
						"modId" => "_sys/root" ,
						"fields" => array(
							"main" => array(
								array(
									"modId" => "_sys/html" ,
									"fields" => array(
										"main" => $code_before
									)
								)
							)
						)
					)
				)
			), JSON_PRETTY_PRINT )
		);

		return;
	}

}
