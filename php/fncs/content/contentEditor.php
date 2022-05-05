<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper\fncs\content;

/**
 * fncs/content/contentEditor.php
 */
class contentEditor{

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
	 * コンテンツファイルを削除する
	 */
	public function delete(){
		if( !$this->px->site() ){
			return array(false, '$px->site() is not defined.');
		}

		$contRoot = $this->px->fs()->get_realpath( $this->px->get_path_docroot().'/'.$this->px->get_path_controot() );
		$path = $this->px->req()->get_request_file_path();

		$pathContent = $this->px2dthelper->find_page_content( $path );
		$pathFiles = $this->px2dthelper->path_files( $path );
		$procType = $this->px->get_path_proc_type( $path );

		$resMain = $this->px->fs()->rm( $contRoot.''.$pathContent );
		$resFiles = $this->px->fs()->rm( $contRoot.''.$pathFiles );
		if( !$resMain || !$resFiles ){
			return array(
				'result' => false,
				'message' => 'Failed to remove contents.',
			);
		}

		return array(
			'result' => true,
			'message' => 'OK',
		);
	}

}
