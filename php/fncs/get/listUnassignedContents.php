<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper\fncs\get;
use tomk79\pickles2\px2dthelper\fncs\packages;

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
		$this->add_path_to_ignored_path_list($realpath_homedir);

		// パブリッシュ先ディレクトリを除外リストに登録する
		if( isset($this->px->conf()->path_publish_dir) && is_string($this->px->conf()->path_publish_dir) ){
			$this->add_path_to_ignored_path_list($this->px->conf()->path_publish_dir);
		}

		// 公開キャッシュディレクトリを除外リストに登録する
		if( isset($this->px->conf()->public_cache_dir) && is_string($this->px->conf()->public_cache_dir) ){
			$this->add_path_to_ignored_path_list($this->px->conf()->public_cache_dir);
		}

		// vendor, node_modules, を除外リストに登録する
		$packages = new packages($this->px, $this->px2dthelper);
		$composer_root = $packages->get_path_composer_root_dir();
		if( is_string($composer_root) ){
			$this->add_path_to_ignored_path_list($composer_root.'/vendor/');
		}
		$npm_root = $packages->get_path_npm_root_dir();
		if( is_string($npm_root) ){
			$this->add_path_to_ignored_path_list($npm_root.'/node_modules/');
		}

		// サイトマップに登録されているファイルを除外する
		$sitemap = $this->px->site()->get_sitemap();
		foreach($sitemap as $page_info){
			if( !isset($page_info['content']) || !is_string($page_info['content']) ){
				continue;
			}
			$relatedpath_content = $this->px->fs()->get_relatedpath($page_info['content'], '/');
			$this->add_path_to_ignored_path_list($relatedpath_content);
			foreach( $this->conf_funcs_processors as $proc_ext ){
				$this->add_path_to_ignored_path_list($relatedpath_content.'.'.$proc_ext);
			}
		}

		$rtn->unassigned_contents = $this->scan_dir();
        return $rtn;
    }

	/**
	 * 対象外ディレクトリに加える
	 */
	private function add_path_to_ignored_path_list( $path_target ){
		if( !is_string($path_target) ){
			return;
		}
		$path_target = $this->px->fs()->normalize_path( $this->px->fs()->get_relatedpath( $path_target ) );
		if( preg_match('/^\.\//', $path_target) ){
			$this->ignored_path_list[ preg_replace('/^\.\//', '/', $path_target) ] = true;
		}
		return;
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
