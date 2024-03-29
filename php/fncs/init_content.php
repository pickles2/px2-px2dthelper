<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper\fncs;

/**
 * fncs/init_content.php
 */
class init_content{

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
	 * @param  array  $options   オプション
	 * - `force` : `true` を指定すると、すでにコンテンツが存在した場合にも強制的に上書きします。
	 * @return array `array(boolean $result, string $error_msg)`
	 */
	public function init_content( $editor_mode = 'html', $options = array() ){
		if(!strlen($editor_mode ?? '')){
			$editor_mode = 'html';
		}
		$flg_force = false;
		if( is_array($options) && array_key_exists('force', $options) ){
			$flg_force = !!$options['force'];
		}
		$page_info = $this->px->site()->get_current_page_info();
		$path_content = $this->px->req()->get_request_file_path();
		if( !is_null($page_info) ){
			$path_content = $page_info['content'];
		}
		$contRoot = $this->px->fs()->get_realpath( $this->px->get_path_docroot().'/'.$this->px->get_path_controot() );
		$path_find_exist_content = $this->px2dthelper->find_page_content( $path_content );
		$realpath_content = $this->px->fs()->get_realpath( $this->px->get_path_docroot().$this->px->get_path_controot().$path_content );
		$realpath_files = $this->px->fs()->get_realpath( $this->px->realpath_files() );

		if( $this->px->fs()->is_file( $contRoot.'/'.$path_find_exist_content ) && !$flg_force ){
			return array(false, 'Contents already exists.');
		}

		// 一旦削除する
		if( $this->px->fs()->is_file( $contRoot.'/'.$path_find_exist_content ) ){
			$this->px->fs()->rm( $contRoot.'/'.$path_find_exist_content );
		}
		if( $this->px->fs()->is_dir( $realpath_files ) ){
			$this->px->fs()->rmdir_r( $realpath_files );
		}

		// ディレクトリを作成
		$this->px->fs()->mkdir_r( dirname($realpath_content) );

		// コンテンツテンプレートが利用可能な場合
		// テンプレートからコンテンツを生成する
		$contents_template = new contentsTemplate\contentsTemplate($this->px2dthelper, $this->px);
        $is_available = $contents_template->is_available();
		if( $is_available ){
			$result = $contents_template->init_content( $path_content, $editor_mode );
			return $result;
		}

		// コンテンツ本体を作成
		$extension = $this->px->fs()->get_extension( $realpath_content );
		if( $editor_mode != 'html.gui' && $editor_mode != $extension ){
			$realpath_content .= '.'.$editor_mode;
		}
		$this->px->fs()->save_file( $realpath_content, '' );

		if( $editor_mode == 'html.gui' ){
			// broccoli-html-editor の data.json を作成
			$realpath_data_dir = $this->px2dthelper->get_realpath_data_dir();
			$this->px->fs()->mkdir_r( $realpath_data_dir );
			$this->px->fs()->save_file( $realpath_data_dir.'/data.json', '{}' );

		}

		return array(true, 'ok');
	}

}
