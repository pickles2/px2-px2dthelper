<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper\fncs\sitemap;

/**
 * fncs/sitemap/sitemapEditor.php
 */
class sitemapEditor{

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
	 * サイトマップファイルリストを取得する
	 *
	 * @return array 実行結果
	 */
	public function filelist(){
		$rtn = array(
			'result'=>true,
			'message'=>'OK',
			'list' => array(),
			'list_origcase' => array(),
			'fullname_list' => array(),
			'fullname_list_origcase' => array(),
		);

		$ls = $this->px->fs()->ls( $this->realpath_sitemap_dir );
		foreach( $ls as $basename ){
			if( preg_match('/^\.\~.*\.csv\#$/', $basename) ){
				continue;
			}
			if( preg_match('/^\~\$.*$/', $basename) ){
				continue;
			}

			array_push( $rtn['fullname_list'], strtolower($basename) );
			array_push( $rtn['fullname_list_origcase'], $basename );

			if( !preg_match('/^(.+)\.(.+?)$/', $basename, $matched) ){
				continue;
			}

			$filename = $matched[1];
			$ext = $matched[2];
			if( !isset($rtn['list_origcase'][$filename]) ){
				$rtn['list_origcase'][$filename] = array();
			}
			array_push($rtn['list_origcase'][$filename], $ext);

			$filename_lowercase = strtolower($filename);
			$ext_lowercase = strtolower($ext);
			if( !isset($rtn['list'][$filename_lowercase]) ){
				$rtn['list'][$filename_lowercase] = array();
			}
			array_push($rtn['list'][$filename_lowercase], $ext_lowercase);
		}
		return $rtn;
	}

	/**
	 * 新規サイトマップファイルを作成する
	 *
	 * @param string $filename 対象ファイル名(拡張子を含まない)
	 * @return array 実行結果
	 */
	public function create( $filename ){
		if( !strlen(''.$filename) ){
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

		$plugin_name = 'tomk79\pickles2\sitemap_excel\pickles_sitemap_excel::exec';
		$func_div = null;
		$val = $this->px2dthelper->plugins()->get_plugin_options($plugin_name, $func_div);
		if( count($val) ){
			foreach( $val as $option ){
				if(!is_object( $option->options )){
					continue;
				}
				if($option->options->master_format == 'pass'){
					continue;
				}
				if( is_object($option->options->files_master_format) ){
					if( $option->options->files_master_format->{$filename} == 'pass' ){
						continue;
					}
				}
				if( class_exists('\tomk79\pickles2\sitemap_excel\pickles_sitemap_excel') ){
					$sitemapexcel = new \tomk79\pickles2\sitemap_excel\pickles_sitemap_excel( $this->px );
					$sitemapexcel->csv2xlsx($this->realpath_sitemap_dir.$filename.'.csv', $this->realpath_sitemap_dir.$filename.'.xlsx');
				}
			}
		}

		return array(
			'result'=>true,
			'message'=>'OK',
		);
	}

	/**
	 * サイトマップファイルを読み込むする
	 *
	 * @param string $filefullname 対象ファイル名(拡張子を含む)
	 * @return array 実行結果
	 */
	public function read( $filefullname ){
		$rtn = array(
			'result' => true,
			'message' => 'OK',
			'filename' => null,
			'bin' => false,
		);
		$realpath = $this->realpath_sitemap_file( $filefullname );
		if( $realpath === false ){
			return array(
				'result' => false,
				'message' => 'File not found.',
				'filename' => null,
				'bin' => false,
			);
		}
		$rtn['filename'] = basename( $realpath );
		$rtn['bin'] = $this->px->fs()->read_file( $realpath );
		if( !$rtn['bin'] ){
			$rtn['result'] = false;
			$rtn['message'] = 'Failed to open file.';
		}
		return $rtn;
	}

	/**
	 * サイトマップファイルを保存する
	 *
	 * @param string $filefullname 対象ファイル名(拡張子を含む)
	 * @param string $bin ファイル
	 * @return array 実行結果
	 */
	public function save( $filefullname, $bin ){
		if( !preg_match('/^[a-zA-Z0-9].*\.[a-zA-Z0-9]+$/', $filefullname) ){
			return array(
				'result' => false,
				'message' => 'Invalid Filename',
				'filename' => null,
			);
		}
		if( !is_string($bin) || !strlen($bin) ){
			return array(
				'result' => false,
				'message' => 'File contains no contents.',
				'filename' => null,
			);
		}

		$rtn = array(
			'result' => true,
			'message' => 'OK',
			'filename' => null,
		);
		$filefullname_lower = strtolower($filefullname);
		$ls = $this->px->fs()->ls($this->realpath_sitemap_dir);
		foreach( $ls as $basename ){
			if( strtolower($basename) == $filefullname_lower ){
				$rtn['filename'] = $basename;
				$rtn['result'] = $this->px->fs()->save_file( $this->realpath_sitemap_dir.$basename, $bin );
				if( $rtn['result'] ){
					// 保存したファイルを CSV/Xlsx 変換する
					$this->save_convert_csv_xlsx( $filefullname );
				}else{
					// 保存に失敗したら
					$rtn['result'] = false;
					$rtn['message'] = 'Failed to overwrite file.';
				}
				return $rtn;
			}
		}

		// 既存の該当ファイルが見つからない場合、
		// lowercase に変換した名前で保存する。
		$rtn['filename'] = $filefullname_lower;
		$rtn['result'] = $this->px->fs()->save_file( $this->realpath_sitemap_dir.$filefullname_lower, $bin );
		if( $rtn['result'] ){
			// 保存したファイルを CSV/Xlsx 変換する
			$this->save_convert_csv_xlsx( $filefullname );
		} else {
			// 保存に失敗したら
			$rtn['result'] = false;
			$rtn['message'] = 'Failed to write new file.';
		}

		return $rtn;
	}

	/**
	 * サイトマップファイルの保存後に CSV/Xlsx 変換する
	 */
	private function save_convert_csv_xlsx( $filefullname ){
		if( !preg_match( '/^(.*)\.([a-zA-Z0-9]+)$/', $filefullname, $matched ) ){
			return false;
		}
		$filename = $matched[1];
		$ext_from = $matched[2];
		$filename_lower = strtolower($filename);
		$ext_from_lower = strtolower($ext_from);
		$ext_to = null;
		$ext_to_lower = null;

		$ls = $this->px->fs()->ls($this->realpath_sitemap_dir);
		$realpath_from = null;
		$realpath_to = null;
		foreach( $ls as $basename ){
			if( preg_match('/^'.preg_quote($filename, '/').'\.'.preg_quote($ext_from, '/').'$/i', $basename) ){
				$realpath_from = $this->px->fs()->get_realpath($this->realpath_sitemap_dir.$basename);
				continue;
			}
			if( preg_match('/^'.preg_quote($filename, '/').'\.(.+?)$/i', $basename, $matched) ){
				$ext_to = $matched[1];
				$ext_to_lower = strtolower($ext_to);
				$realpath_to = $this->px->fs()->get_realpath($this->realpath_sitemap_dir.$basename);
				continue;
			}
		}

		if( !$realpath_from || !is_file($realpath_from) ){
			return false;
		}
		if( !$realpath_to || !is_file($realpath_to) ){
			// 既存のファイルがないなら変換しない
			return false;
		}

		$px2_sitemapexcel = new \tomk79\pickles2\sitemap_excel\pickles_sitemap_excel($this->px);
		$result = null;
		if( $ext_from_lower == 'csv' ){
			// アップロードされたファイルがCSVなら、XLSXに変換する
			$result = !!$px2_sitemapexcel->csv2xlsx(
				$realpath_from,
				$realpath_to
			);
		}else{
			// アップロードされたファイルがXLSXなら、CSVに変換する
			$result = !!$px2_sitemapexcel->xlsx2csv(
				$realpath_from,
				$realpath_to
			);
		}

		return $result;
	}

	/**
	 * xlsx を CSV に変換する
	 *
	 * @param string $filename 対象ファイル名(拡張子を含まない)
	 * @return array 実行結果
	 */
	public function xlsx2csv( $filename ){
		if( !strlen(''.$filename) ){
			return array(
				'result'=>false,
				'message'=>'Filename is required.',
			);
		}

		if( !class_exists('\\tomk79\\pickles2\\sitemap_excel\\pickles_sitemap_excel') ){
			return array(
				'result'=>false,
				'message'=>'px2-sitemapexcel is not defined.',
			);
		}

		$rtn = array(
			'result'=>true,
			'message'=>'OK',
		);

		$px2_sitemapexcel = new \tomk79\pickles2\sitemap_excel\pickles_sitemap_excel($this->px);
		$rtn['result'] = !!$px2_sitemapexcel->xlsx2csv(
			$this->realpath_sitemap_dir.$filename.'.xlsx',
			$this->realpath_sitemap_dir.$filename.'.csv'
		);
		if( !$rtn['result'] ){
			$rtn['message'] = 'Failed to convert.';
		}
		return $rtn;
	}

	/**
	 * CSV を xlsx に変換する
	 *
	 * @param string $filename 対象ファイル名(拡張子を含まない)
	 * @return array 実行結果
	 */
	public function csv2xlsx( $filename ){
		if( !strlen(''.$filename) ){
			return array(
				'result'=>false,
				'message'=>'Filename is required.',
			);
		}

		if( !class_exists('\\tomk79\\pickles2\\sitemap_excel\\pickles_sitemap_excel') ){
			return array(
				'result'=>false,
				'message'=>'px2-sitemapexcel is not defined.',
			);
		}

		$rtn = array(
			'result'=>true,
			'message'=>'OK',
		);

		$px2_sitemapexcel = new \tomk79\pickles2\sitemap_excel\pickles_sitemap_excel($this->px);
		$rtn['result'] = !!$px2_sitemapexcel->csv2xlsx(
			$this->realpath_sitemap_dir.$filename.'.csv',
			$this->realpath_sitemap_dir.$filename.'.xlsx'
		);
		if( !$rtn['result'] ){
			$rtn['message'] = 'Failed to convert.';
		}
		return $rtn;
	}

	/**
	 * サイトマップファイルを削除する
	 *
	 * @param string $filename 対象ファイル名(拡張子を含まない)
	 * @return array 実行結果
	 */
	public function delete( $filename ){
		if( !strlen(''.$filename) ){
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

	/**
	 * 実在するファイルの絶対パスを取得する
	 *
	 * 大文字・小文字 の区別をせずに検索する。
	 *
	 * @param string $filefullname 対象ファイル名(拡張子を含む)
	 * @return string|boolean ファイルの絶対パスを返す。ファイルが見つからない場合に `false` を返す。
	 */
	private function realpath_sitemap_file( $filefullname ){
		$filefullname_lower = strtolower($filefullname);
		$ls = $this->px->fs()->ls($this->realpath_sitemap_dir);
		foreach( $ls as $basename ){
			if( strtolower($basename) == $filefullname_lower ){
				$realpath = $this->px->fs()->get_realpath( $this->realpath_sitemap_dir.$basename );
				return $realpath;
			}
		}
		return false;
	}

}
