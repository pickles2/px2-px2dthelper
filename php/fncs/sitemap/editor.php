<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper;

/**
 * fncs/sitemap/editor.php
 */
class fncs_sitemap_editor{

	/** Picklesオブジェクト */
	private $px;

	/** px2dthelperオブジェクト */
	private $px2dthelper;

	/** サイトマップディレクトリ */
	private $realpath_sitemap_dir;

	/**
	 * constructor
	 *
	 * @param object $px2dthelper $px2dthelperオブジェクト
	 * @param object $px $pxオブジェクト
	 */
	public function __construct( $px2dthelper, $px ){
		$this->px2dthelper = $px2dthelper;
		$this->px = $px;

		$this->realpath_sitemap_dir = $this->px->get_realpath_homedir().'sitemaps/';
	}

	/**
	 * 新規サイトマップファイルを作成する
	 *
	 * @param string $filename 対象ファイル名(拡張子を含まない)
	 * @return array 実行結果
	 */
	public function create( $filename ){
		if( !strlen($filename) ){
			return array(
				'result'=>false,
				'message'=>'Filename is required.',
			);
		}
		if( !preg_match('/^[a-zA-Z0-9\-\_]+$/s', $filename) ){
			return array(
				'result'=>false,
				'message'=>'Filename contains invalid charactor. Can use /^[a-zA-Z0-9\-\_]+$/s',
			);
		}
		$ls = $this->px->fs()->ls($this->realpath_sitemap_dir);
		foreach($ls as $basename){
			$tmp_filename = preg_replace('/\..+?$/s', '', $basename);
			if( strtolower($tmp_filename) == strtolower( $filename ) ){
				return array(
					'result'=>false,
					'message'=>'Filename '.var_export($filename,true).' is already exists.',
				);
			}
		}

		$csvSrc = '"* path","* content","* id","* title","* title_breadcrumb","* title_h1","* title_label","* title_full","* logical_path","* list_flg","* layout","* orderby","* keywords","* description","* category_top_flg","* role","* proc_type","* **delete_flg"'."\r\n";
		$this->px->fs()->save_file($this->realpath_sitemap_dir.$filename.'.csv', $csvSrc);

		return array(
			'result'=>true,
			'message'=>'OK',
		);
	}

	/**
	 * サイトマップファイルを削除する
	 *
	 * @param string $filename 対象ファイル名(拡張子を含まない)
	 * @return array 実行結果
	 */
	public function delete( $filename ){
		if( !strlen($filename) ){
			return array(
				'result'=>false,
				'message'=>'Filename is required.',
			);
		}

		$rtn = array(
			'result'=>true,
			'message'=>'OK',
			'deleted'=>array(),
			'failed'=>array(),
		);
		$ls = $this->px->fs()->ls($this->realpath_sitemap_dir);
		foreach($ls as $basename){
			$tmp_filename = preg_replace('/\..+?$/s', '', $basename);
			if( strtolower($tmp_filename) == strtolower( $filename ) ){
				if($this->px->fs()->rm($this->realpath_sitemap_dir.$basename)){
					array_push($rtn['deleted'], $basename);
				}else{
					array_push($rtn['failed'], $basename);
				}
			}
		}

		if( !count($rtn['deleted']) || count($rtn['failed']) ){
			$rtn['result'] = false;
			$rtn['message'] = 'Failed to delete file(s).';
		}

		return $rtn;
	}

}
