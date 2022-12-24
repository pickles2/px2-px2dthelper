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

	/** docroot のパス */
	private $realpath_docroot;

	/** スキャン対象から除外するパスの一覧 */
	private $ignored_path_list;

	/** 定義された拡張子の一覧 */
	private $conf_funcs_processors;

	/**
	 * constructor
	 *
	 * @param object $px2dthelper $px2dthelperオブジェクト
	 * @param object $px $pxオブジェクト
	 */
	public function __construct( $px2dthelper, $px ){
		$this->px2dthelper = $px2dthelper;
		$this->px = $px;

		$this->ignored_path_list = array();
		$this->realpath_docroot = $this->px->get_realpath_docroot();
	}

    /**
     * 未割当のコンテンツファイルの一覧を取得する
     */
    public function get_unassigned_contents(){
		$rtn = (object) array();
		$rtn->result = true;
		$rtn->unassigned_contents = array();

		$this->ignored_path_list = array();

		$this->conf_funcs_processors = array_keys((array) $this->px->conf()->funcs->processor);

		// homedir を除外リストに登録する
		$realpath_homedir = $this->px->get_realpath_homedir();
		$relatedpath_homedir = $this->px->fs()->normalize_path($this->px->fs()->get_relatedpath($realpath_homedir));
		if( preg_match('/^\.\//', $relatedpath_homedir) ){
			$this->ignored_path_list[ preg_replace('/^\.\//', '/', $relatedpath_homedir) ] = true;
		}

		// サイトマップに登録されているファイルを除外する
		$sitemap = $this->px->site()->get_sitemap();
		foreach($sitemap as $page_info){
			if( !isset($page_info['content']) || !is_string($page_info['content']) ){
				continue;
			}
			$this->ignored_path_list[$page_info['content']] = true;
			foreach( $this->conf_funcs_processors as $proc_ext ){
				$this->ignored_path_list[$page_info['content'].'.'.$proc_ext] = true;
			}
		}

		$rtn->unassigned_contents = $this->scan_dir();
        return $rtn;
    }

	/**
	 * ディレクトリをスキャンする
	 */
	private function scan_dir( $local_path = '' ){
		$rtn = array();
		$filelist = $this->px->fs()->ls($this->realpath_docroot.$local_path);
		$imploded_conf_funcs_processors = implode('|', $this->conf_funcs_processors);

		// 先にファイルを処理する
		// 除外するディレクトリ `*_files` は、ファイル名から評価するため、
		// ディレクトリを評価する前にすべてのファイルの評価が完了している必要がある。
		foreach($filelist as $basename){

			if( is_file($this->realpath_docroot.$local_path.$basename) ){
				$path_files = $this->px2dthelper->path_files('/'.$local_path.$basename);
				$this->ignored_path_list[$path_files] = true;
				if( isset($this->ignored_path_list['/'.$local_path.$basename]) ){
					// サイトマップに定義済みのコンテンツなので除外
					continue;
				}
				if( !preg_match( '/\.html?$/', $basename ) && !preg_match( "/\.html\.(?:$imploded_conf_funcs_processors)?$/", $basename ) ){
					continue;
				}
				array_push($rtn, '/'.$local_path.$basename);
			}
		}

		// 続いて、ディレクトリを処理する
		foreach($filelist as $basename){
			if( isset($this->ignored_path_list['/'.$local_path.$basename.'/']) && $this->ignored_path_list['/'.$local_path.$basename.'/'] ){
				continue;
			}
			if( is_dir($this->realpath_docroot.$local_path.$basename) ){
				$tmp_filelist = $this->scan_dir($local_path.$basename.'/');
				$rtn = array_merge($rtn, $tmp_filelist);
			}
		}
		return $rtn;
	}
}
