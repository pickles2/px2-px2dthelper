<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper\fncs\page;

/**
 * fncs/page/editor.php
 */
class pageEditor{

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
	 * サイトマップファイルから行データを直接取得する
	 */
	public function get_page_info_raw( $filefullname, $row ){
		$rtn = array(
			'result'=>true,
			'message'=>'OK',
			'sitemap_definition'=>null,
			'page_info' => null,
		);

		$realpath_csv = $this->realpath_sitemap_file( $filefullname );
		$csv = $this->px->fs()->read_csv($realpath_csv);
		if( !$this->has_sitemap_definition( $csv ) && !$row){
			return array(
				'result'=>false,
				'message'=>'Invalid row number.',
			);
		}
		$rtn['sitemap_definition'] = $this->parse_sitemap_definition( $csv );
		if( !isset($csv[$row]) ){
			return array(
				'result'=>false,
				'message'=>'Invalid row number.',
			);
		}
		$rtn['page_info'] = $csv[$row];

		return $rtn;
	}

	/**
	 * サイトマップファイルに行データを直接追加する
	 */
	public function add_page_info_raw( $filefullname, $row = 0, $page_info = array() ){
		$rtn = array(
			'result'=>true,
			'message'=>'OK',
			'errors' => null,
		);
		$validated = $this->validate_page_info( $page_info );
		if( count($validated) ){
			$rtn = array(
				'result'=>false,
				'message'=>'Validation Error.',
				'errors' => $validated,
			);
			return $rtn;
		}

		$realpath_csv = $this->realpath_sitemap_file( $filefullname );
		$csv = $this->px->fs()->read_csv( $realpath_csv );
		if( !is_array($csv) ){
			return array(
				'result' => false,
				'message' => 'Failed to load sitemap file.',
			);
		}
		if( !$this->has_sitemap_definition( $csv ) && !$row){
			return array(
				'result'=>false,
				'message'=>'Invalid row number.',
			);
		}
		$sitemap_definition = $this->parse_sitemap_definition( $csv );

		$sitemap_row = array();
		foreach( $sitemap_definition as $definition_col ){
			$row_col_value = '';
			if( isset($page_info[$definition_col]) ){
				$row_col_value = $page_info[$definition_col];
			}
			array_push($sitemap_row, $row_col_value);
		}
		array_splice($csv, $row, 0, array($sitemap_row));

		$src_csv = $this->px->fs()->mk_csv($csv);
		$result = $this->px->fs()->save_file( $realpath_csv, $src_csv );
		if( !$result ){
			return array(
				'result' => false,
				'message' => 'Failed to save sitemap file.',
			);
		}

		// NOTE: 暫定処理: CSVを更新したら、xlsx も更新する。
		$this->csv2xlsx( $filefullname );

		return $rtn;
	}

	/**
	 * サイトマップファイルの行データを直接更新する
	 */
	public function update_page_info_raw( $filefullname, $row = 0, $page_info = array() ){
		$rtn = array(
			'result'=>true,
			'message'=>'OK',
			'errors' => null,
		);
		$validated = $this->validate_page_info( $page_info );
		if( count($validated) ){
			$rtn = array(
				'result'=>false,
				'message'=>'Validation Error.',
				'errors' => $validated,
			);
			return $rtn;
		}

		$realpath_csv = $this->realpath_sitemap_file( $filefullname );
		$csv = $this->px->fs()->read_csv( $realpath_csv );
		if( !is_array($csv) ){
			return array(
				'result' => false,
				'message' => 'Failed to load sitemap file.',
			);
		}
		if( !$this->has_sitemap_definition( $csv ) && !$row){
			return array(
				'result'=>false,
				'message'=>'Invalid row number.',
			);
		}
		$sitemap_definition = $this->parse_sitemap_definition( $csv );

		$sitemap_row = array();
		foreach( $sitemap_definition as $definition_col ){
			$row_col_value = '';
			if( isset($page_info[$definition_col]) ){
				$row_col_value = $page_info[$definition_col];
			}
			array_push($sitemap_row, $row_col_value);
		}
		$csv[$row] = $sitemap_row;

		$src_csv = $this->px->fs()->mk_csv($csv);
		$result = $this->px->fs()->save_file( $realpath_csv, $src_csv );
		if( !$result ){
			return array(
				'result' => false,
				'message' => 'Failed to save sitemap file.',
			);
		}

		// NOTE: 暫定処理: CSVを更新したら、xlsx も更新する。
		$this->csv2xlsx( $filefullname );

		return $rtn;
	}

	/**
	 * サイトマップファイルの行データを直接削除する
	 */
	public function delete_page_info_raw( $filefullname, $row = 0, $page_info = array() ){
		$rtn = array(
			'result'=>true,
			'message'=>'OK',
		);

		$realpath_csv = $this->realpath_sitemap_file( $filefullname );
		$csv = $this->px->fs()->read_csv( $realpath_csv );
		if( !is_array($csv) ){
			return array(
				'result' => false,
				'message' => 'Failed to load sitemap file.',
			);
		}
		if( !$this->has_sitemap_definition( $csv ) && !$row){
			return array(
				'result'=>false,
				'message'=>'Invalid row number.',
			);
		}

		unset($csv[$row]);

		$src_csv = $this->px->fs()->mk_csv($csv);
		$result = $this->px->fs()->save_file( $realpath_csv, $src_csv );
		if( !$result ){
			return array(
				'result' => false,
				'message' => 'Failed to save sitemap file.',
			);
		}

		// NOTE: 暫定処理: CSVを更新したら、xlsx も更新する。
		$this->csv2xlsx( $filefullname );

		return $rtn;
	}


	// ----------------------------------------------------------------------------

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

	/**
	 * Validation: ページ情報
	 */
	private function validate_page_info( $page_info ){
		$rtn = array();
		foreach( $page_info as $key=>$val ){
			switch( $key ){
				case 'path':
					if( !strlen( $val ) ){
						$rtn[$key] = 'path は必須項目です。';
					}elseif( !preg_match( '/^\//', $val ) ){
						$rtn[$key] = 'path は "/" (スラッシュ) から始まる値である必要があります。';
					}
					break;
				case 'title':
					if( !strlen( $val ) ){
						$rtn[$key] = 'title は必須項目です。';
					}
					break;
			}
		}
		return $rtn;
	}

	/**
	 * デフォルトのサイトマップ定義を取得する
	 */
	private function get_default_sitemap_definition(){
		$sitemap_definition = array(
			'path',
			'content',
			'id',
			'title',
			'title_breadcrumb',
			'title_h1',
			'title_label',
			'title_full',
			'logical_path',
			'list_flg',
			'layout',
			'orderby',
			'keywords',
			'description',
			'category_top_flg',
			'role',
			'proc_type',
		);
		return $sitemap_definition;
	}

	/**
	 * サイトマップに定義行が含まれるか調べる
	 */
	private function has_sitemap_definition( $csv ){
		if( !is_array($csv) || !count($csv) || !isset($csv[0]) ){
			return false;
		}

		$row = $csv[0];
		if( !is_array($row) || !count($row) || !isset($row[0]) ){
			return false;
		}
		if( !preg_match('/^\*/', $row[0]) ){
			return false;
		}
		return true;
	}

	/**
	 * デフォルトのサイトマップ定義を取得する
	 */
	private function parse_sitemap_definition( $csv ){
		if( !$this->has_sitemap_definition( $csv ) ){
			return $this->get_default_sitemap_definition();
		}

		$row = $csv[0];
		$rtn = array();
		foreach($row as $col){
			$def = preg_replace('/^\*\s*/', '', $col);
			array_push( $rtn, $def );
		}

		return $rtn;
	}


	/**
	 * CSV を xlsx に変換する
	 *
	 * NOTE: これは暫定的な処理です。サイトマップのページ数が多くなると、この処理は重くなります。
	 * TODO: CSV全体を変換することはせず、CSVに反映した変更と同じ変更をXlsxにも差分反映させる処理に変更します。
	 *
	 * @param string $filefullname 対象ファイル名(拡張子を含む)
	 * @return array 実行結果
	 */
	private function csv2xlsx( $filefullname ){
		if( !class_exists('\\tomk79\\pickles2\\sitemap_excel\\pickles_sitemap_excel') ){
			return false;
		}
		if( !is_string($filefullname) || !strlen($filefullname) ){
			return false;
		}
		if( !preg_match('/\.csv$/si', $filefullname) ){
			return false;
		}

		$realpath_csv = $this->realpath_sitemap_file( $filefullname );
		if( !is_file( $realpath_csv ) ){
			return false;
		}
		$filefullname_xlsx = preg_replace('/\.csv$/si', '.xlsx', $filefullname);
		$realpath_xlsx = $this->realpath_sitemap_file( $filefullname_xlsx );
		if( !is_file( $realpath_xlsx ) ){
			return false;
		}

		$px2_sitemapexcel = new \tomk79\pickles2\sitemap_excel\pickles_sitemap_excel($this->px);
		$result = !!$px2_sitemapexcel->csv2xlsx(
			$realpath_csv,
			$realpath_xlsx
		);
		return $result;
	}

}
