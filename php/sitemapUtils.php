<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper;

/**
 * sitemapUtils.php
 */
class sitemapUtils{

	/** Picklesオブジェクト */
	private $px;

	/** px2dthelperオブジェクト */
	private $px2dthelper;

	/** ロックファイルのパス */
    private $lockfilepath;

	/** ロック解除を待つ回数(秒数) */
    private $timeout_limit = 5;

	/** ロックファイルの有効期限 */
	private $lockfile_expire = 60;

	/** サイトマップディレクトリ */
	private $realpath_sitemap_dir;

	/** 開かれているCSVファイルの内容 */
	private $opened_csv = array();

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
		$this->lockfilepath = $this->px->get_realpath_homedir().'_sys/ram/caches/sitemaps/making_sitemap_cache.lock.txt';
	}


	// --------------------------------------
	// サイトマップCSVファイル操作

	/**
	 * CSVファイルを開く
	 */
	public function &csv_open( $filefullname ){
		$realpath_csv = $this->realpath_sitemap_file($filefullname);
		if( !$realpath_csv || !is_file($realpath_csv) ){
			// Error: CSVファイルが存在しない。
			$rtn = false;
			return $rtn;
		}
		if( !is_readable($realpath_csv) ){
			// Error: CSVファイルが読み込めない。
			$rtn = false;
			return $rtn;
		}
		if( isset($this->opened_csv[$realpath_csv]) ){
			// すでに開かれている
			return $this->opened_csv[$realpath_csv];
		}

		// 開く
		$this->opened_csv[$realpath_csv] = array();
		$this->opened_csv[$realpath_csv]['filefullname'] = $filefullname;
		$this->opened_csv[$realpath_csv]['realpath'] = $realpath_csv;
		$this->opened_csv[$realpath_csv]['csv_rows'] = $this->px->fs()->read_csv($realpath_csv);

		return $this->opened_csv[$realpath_csv];
	}

	/**
	 * CSVの行を取得する
	 */
	public function csv_get_row( $filefullname, $row_index ){
		$csv = &$this->csv_open($filefullname);
		if( !$csv ){
			// 開けなければ失敗
			return false;
		}
		if( !isset($csv['csv_rows'][$row_index]) ){
			// 対象の行がなければ失敗
			return false;
		}

		// 空行を取得する
		$row = $csv['csv_rows'][$row_index];

		// 行をassocする
		$sitemap_definition = $this->parse_sitemap_definition( $csv['csv_rows'] );

		$rtn = array();
		foreach( $row as $idx=>$col ){
			$rtn[$sitemap_definition[$idx]] = $col;
		}

		return $rtn;
	}

	/**
	 * CSVの行を追加する
	 */
	public function csv_add_row( $filefullname, $row_index, $row_assoc ){
		$csv = &$this->csv_open($filefullname);
		if( !$csv ){
			// 開けなければ失敗
			return false;
		}

		// 空行を追加する
		array_splice($csv['csv_rows'], $row_index, 0, array(array()));

		// 追加した行を更新する
		$result =  $this->csv_update_row($filefullname, $row_index, $row_assoc);

		return $result;
	}

	/**
	 * CSVの行を更新する
	 */
	public function csv_update_row( $filefullname, $row_index, $row_assoc ){
		$csv = &$this->csv_open($filefullname);
		if( !$csv ){
			// 開けなければ失敗
			return false;
		}
		$row_index = intval($row_index);

		$sitemap_definition = $this->parse_sitemap_definition( $csv['csv_rows'] );
		$sitemap_definition_flip = array_flip($sitemap_definition);

		$sitemap_row = $csv['csv_rows'][$row_index];
		foreach( $sitemap_definition as $definition_col ){
			$colidx = $sitemap_definition_flip[$definition_col];
			if( !isset($sitemap_row[$colidx]) ){
				$sitemap_row[$colidx] = '';
			}
			if( isset($row_assoc[$definition_col]) ){
				$sitemap_row[$colidx] = $row_assoc[$definition_col];
			}
		}
		$csv['csv_rows'][$row_index] = $sitemap_row;

		return true;
	}

	/**
	 * CSVの行を削除する
	 */
	public function csv_remove_row( $filefullname, $row_index ){
		$csv = &$this->csv_open($filefullname);
		if( !$csv ){
			// 開けなければ失敗
			return false;
		}
		if( !isset($csv['csv_rows'][$row_index]) ){
			// 行がない場合
			return false;
		}

		// 行を削除する
		unset($csv['csv_rows'][$row_index]);

		return true;
	}

	/**
	 * サイトマップに定義行が含まれるか調べる
	 */
	public function csv_has_sitemap_definition( $filefullname ){
		$csv = &$this->csv_open($filefullname);
		if( !$csv ){
			// 開けなければ失敗
			return false;
		}
		return $this->has_sitemap_definition($csv['csv_rows']);
	}

	/**
	 * 開かれているすべてのCSVファイルを保存して閉じる
	 */
	public function csv_save_all(){
		$rtn = true;
		foreach( $this->opened_csv as $realpath_csv => $csv_info ){
			$src_from_csv = $this->px->fs()->mk_csv($csv_info['csv_rows']);
			$result = $this->px->fs()->save_file( $csv_info['realpath'], $src_from_csv );
			if( !$result ){
				$rtn = false;
				continue;
			}

			// 保存できたCSVは閉じる
			unset($this->opened_csv[$realpath_csv]);

			// NOTE: 暫定処理: CSVを更新したら、xlsx も更新する。
			$this->csv2xlsx( $csv_info['filefullname'] );
		}
		return $rtn;
	}



	// --------------------------------------
	// その他

	/**
	 * 実在するサイトマップファイルの絶対パスを取得する
	 *
	 * 大文字・小文字 の区別をせずに検索する。
	 *
	 * @param string $filefullname 対象ファイル名(拡張子を含む)
	 * @return string|boolean ファイルの絶対パスを返す。ファイルが見つからない場合に `false` を返す。
	 */
	public function realpath_sitemap_file( $filefullname ){
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
	public function validate_page_info( $page_info ){
		$rtn = array();
		foreach( $page_info as $key=>$val ){
			switch( $key ){
				case 'title':
					if( !strlen( trim($val ?? '') ) ){
						$rtn[$key] = 'title は必須項目です。';
					}
					break;
				case 'path':
					if( !strlen( $val ?? '' ) ){
						$rtn[$key] = 'path は必須項目です。';
					}elseif( !preg_match( '/^\//', $val ?? '' ) ){
						$rtn[$key] = 'path は "/" (スラッシュ) から始まる値である必要があります。';
					}elseif( !preg_match( '/(?:\/|\.html?|\{[\*\$][a-zA-Z0-9\-\_]*\})$/', $val ?? '' ) ){
						$rtn[$key] = 'path は "/" (スラッシュ) または .html で終わる値である必要があります。';
					}
					break;
			}
		}
		return $rtn;
	}

	/**
	 * デフォルトのサイトマップ定義を取得する
	 */
	public function get_default_sitemap_definition(){
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
	public function has_sitemap_definition( $csv ){
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
	public function parse_sitemap_definition( $csv ){
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
	 * すべての下層ページの行番号を得る
	 *
	 * ページ情報を更新する際に、パンくずを修正する必要のある影響範囲を返します。
	 */
	public function get_under_children_row( $path, &$all_list = array() ){
		$children = $this->px->site()->get_children( $path, array('filter'=>false) );
		foreach( $children as $child_pid ){
			$page_info = $this->px->site()->get_page_info($child_pid);

			$page_originated_csv = $this->px->site()->get_page_originated_csv($child_pid);
			$page_originated_csv['id'] = $page_info['id'];
			$page_originated_csv['path'] = $page_info['path'];
			$page_originated_csv['logical_path'] = $page_info['logical_path'];
			array_push($all_list, $page_originated_csv);

			$this->get_under_children_row( $page_info['path'], $all_list );
		}
		return $all_list;
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
	public function csv2xlsx( $filefullname ){
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

    /**
     * 排他ロックする
	 *
	 * @return bool ロック成功時に `true`、失敗時に `false` を返します。
     */
    public function lock(){

		if( !$this->px->fs()->is_dir( dirname( $this->lockfilepath ) ) ){
			$this->px->fs()->mkdir_r( dirname( $this->lockfilepath ) );
		}

		clearstatcache();

		$i = 0;
		while( $this->is_locked() ){
			$i ++;
			if( $i >= $this->timeout_limit ){
				return false;
				break;
			}
			sleep(1);

			// PHPのFileStatusCacheをクリア
			clearstatcache();
		}
		$src = '';
		$src .= 'ProcessID='.getmypid()."\r\n";
		$src .= @date( 'Y-m-d H:i:s' , time() )."\r\n";
		$RTN = $this->px->fs()->save_file( $this->lockfilepath , $src );
		return	$RTN;
    }

    /**
     * 排他ロックされているか確認する
	 *
	 * @return bool ロック中の場合に `true`、それ以外の場合に `false` を返します。
     */
    public function is_locked(){

		clearstatcache();

		if( $this->px->fs()->is_file($this->lockfilepath) ){
			if( ( time() - filemtime($this->lockfilepath) ) > $this->lockfile_expire ){
				// 有効期限を過ぎていたら、ロックは成立する。
				return false;
			}
			return true;
		}
		return false;
    }

    /**
     * 排他ロックを解除する
	 *
	 * @return bool ロック解除成功時に `true`、失敗時に `false` を返します。
     */
    public function unlock(){

		clearstatcache();
		if( !$this->px->fs()->is_file( $this->lockfilepath ) ){
			return true;
		}

		return unlink( $this->lockfilepath );
    }

	/**
	 * アプリケーションロックファイルの更新日を更新する。
	 *
	 * @return bool 成功時に `true`、失敗時に `false` を返します。
	 */
	public function touch_lockfile(){

		clearstatcache();
		if( !$this->px->fs()->is_file( $this->lockfilepath ) ){
			return false;
		}

		return touch( $this->lockfilepath );
	}
}
