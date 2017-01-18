<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper;

/**
 * fncs/init_content.php
 */
class fncs_init_content{

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
	 * コンテンツを初期化する
	 *
	 * @param  string $editor_mode  コピー先のページパス (サイトマップの path 値)
	 * @return array `array(boolean $result, string $error_msg)`
	 */
	public function init_content( $editor_mode = 'html' ){
		if(!@strlen($editor_mode)){ $editor_mode = 'html'; }
		$page_info = $this->px->site()->get_current_page_info();
		$path_content = $this->px->req()->get_request_file_path();
		if( !is_null($page_info) ){
			$path_content = $page_info['content'];
		}
		$realpath_content = $this->px->fs()->get_realpath( $this->px->get_path_docroot().$this->px->get_path_controot().$path_content );
		$realpath_files = $this->px->fs()->get_realpath( $this->px->realpath_files() );


		// ディレクトリを作成
		$this->px->fs()->mkdir_r( dirname($realpath_content) );

		// コンテンツ本体を作成
		$extension = $this->px->fs()->get_extension( $realpath_content );
		if( $editor_mode != 'html.gui' && $editor_mode != $extension ){
			$realpath_content .= '.'.$editor_mode;
		}
		$this->px->fs()->save_file( $realpath_content, '' );
		// var_dump($realpath_content);
		// var_dump(is_file($realpath_content));

		if( $editor_mode == 'html.gui' ){
			// broccoli-html-editor の data.json を作成
			$realpath_data_dir = $this->px2dthelper->get_realpath_data_dir();
			// var_dump($realpath_data_dir);
			$this->px->fs()->mkdir_r( $realpath_data_dir );
			$this->px->fs()->save_file( $realpath_data_dir.'/data.json', '{}' );

		}

		return array(true, 'ok');
	}

}