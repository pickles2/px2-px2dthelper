<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper\fncs\get;

/**
 * fncs/get/listUnassignedContents.php
 */
class listUnassignedContents{

	/** Picklesオブジェクト */
	private $px;

	/** px2dthelperオブジェクト */
	private $px2dthelper;

	/** サイトマップに登録されたコンテンツファイルのパス */
	private $content_file_paths_via_sitemap;

	/** docroot のパス */
	private $realpath_docroot;

	/**
	 * constructor
	 *
	 * @param object $px2dthelper $px2dthelperオブジェクト
	 * @param object $px $pxオブジェクト
	 */
	public function __construct( $px2dthelper, $px ){
		$this->px2dthelper = $px2dthelper;
		$this->px = $px;

		$this->content_file_paths_via_sitemap = array();
		$this->realpath_docroot = $this->px->get_realpath_docroot();
	}

    /**
     * 未割当のコンテンツファイルの一覧を取得する
     */
    public function get_unassigned_contents(){
		$rtn = (object) array();
		$rtn->result = true;
		$rtn->unassigned_contents = array();

		$this->content_file_paths_via_sitemap = array();
		$sitemap = $this->px->site()->get_sitemap();
		foreach($sitemap as $page_info){
			if( !isset($page_info['content']) || !is_string($page_info['content']) ){
				continue;
			}
			$this->content_file_paths_via_sitemap[$page_info['content']] = $page_info;
		}

		$filelist = $this->scan_dir();

        return $rtn;
    }

	/**
	 * ディレクトリをスキャンする
	 */
	private function scan_dir( $local_path = '' ){
		$rtn = array();
		$filelist = $this->px->fs()->ls($this->realpath_docroot.$local_path);
		foreach($filelist as $basename){
			if( is_file($this->realpath_docroot.$local_path.$basename) ){
				if( isset($this->content_file_paths_via_sitemap['/'.$local_path.$basename]) ){
					// サイトマップに定義済みのコンテンツなのでスキップ
					continue;
				}
				array_push($rtn, $local_path.$basename);
			}elseif( is_dir($this->realpath_docroot.$local_path.$basename) ){
				$tmp_filelist = $this->scan_dir($local_path.$basename.'/');
				$rtn = array_merge($rtn, $tmp_filelist);
			}
		}
		return $rtn;
	}
}
