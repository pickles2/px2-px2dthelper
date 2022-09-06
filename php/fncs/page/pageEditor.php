<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper\fncs\page;
use tomk79\pickles2\px2dthelper\fncs\content\contentEditor;
use tomk79\pickles2\px2dthelper\sitemapUtils;

/**
 * fncs/page/editor.php
 */
class pageEditor{

	/** Picklesオブジェクト */
	private $px;

	/** px2dthelperオブジェクト */
	private $px2dthelper;

	/** サイトマップのロックファイル制御 */
	private $sitemapUtils;

	/**
	 * constructor
	 *
	 * @param object $px2dthelper $px2dthelperオブジェクト
	 * @param object $px $pxオブジェクト
	 */
	public function __construct( $px2dthelper, $px ){
		$this->px2dthelper = $px2dthelper;
		$this->px = $px;
		$this->sitemapUtils = new sitemapUtils( $px2dthelper, $px );
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

		$realpath_csv = $this->sitemapUtils->realpath_sitemap_file( $filefullname );
		$csv = $this->px->fs()->read_csv($realpath_csv);
		if( !$this->sitemapUtils->has_sitemap_definition( $csv ) && !$row){
			return array(
				'result'=>false,
				'message'=>'Invalid row number.',
				'sitemap_definition'=>null,
				'page_info' => null,
			);
		}
		$rtn['sitemap_definition'] = $this->sitemapUtils->parse_sitemap_definition( $csv );
		if( !isset($csv[$row]) ){
			return array(
				'result'=>false,
				'message'=>'Invalid row number.',
				'sitemap_definition'=>null,
				'page_info' => null,
			);
		}
		$rtn['page_info'] = $csv[$row];

		return $rtn;
	}

	/**
	 * サイトマップファイルに行データを直接追加する
	 */
	public function add_page_info_raw( $filefullname, $row = 0, $page_info = array() ){
		if( !$this->sitemapUtils->lock() ){
			$rtn = array(
				'result'=>false,
				'message'=>'Sitemap is locked.',
				'errors' => null,
			);
			return $rtn;
		}

		$rtn = array(
			'result'=>true,
			'message'=>'OK',
			'errors' => null,
		);
		$validated = $this->sitemapUtils->validate_page_info( $page_info );
		if( count($validated) ){
			$rtn = array(
				'result'=>false,
				'message'=>'Validation Error.',
				'errors' => $validated,
			);
			$this->sitemapUtils->unlock();
			return $rtn;
		}
		if( isset($page_info['id']) && strlen($page_info['id']) && $this->px->site()->get_page_info_by_id($page_info['id']) ){
			$validated['id'] = 'ID is already exists.';
			$rtn = array(
				'result'=>false,
				'message'=>'ID is already exists.',
				'errors' => $validated,
			);
			$this->sitemapUtils->unlock();
			return $rtn;
		}
		$tmp_page_info = $this->px->site()->get_page_info($page_info['path']);
		if( isset($page_info['path']) && strlen($page_info['path']) && $tmp_page_info ){
			if( $this->px->href($page_info['path']) == $this->px->href($tmp_page_info['path']) ){
				$validated['path'] = 'Path is already exists.';
				$rtn = array(
					'result'=>false,
					'message'=>'Path is already exists.',
					'errors' => $validated,
				);
				$this->sitemapUtils->unlock();
				return $rtn;
			}
		}

		// --------------------------------------
		// サイトマップCSVを開く
		if( !$this->sitemapUtils->csv_open($filefullname) ){
			$this->sitemapUtils->unlock();
			return array(
				'result' => false,
				'message' => 'Failed to load sitemap file.',
				'errors' => null,
			);
		}
		if( !$this->sitemapUtils->csv_has_sitemap_definition( $filefullname ) && !$row){
			$this->sitemapUtils->unlock();
			return array(
				'result'=>false,
				'message'=>'Invalid row number.',
				'errors' => null,
			);
		}

		// --------------------------------------
		// 行を追加する
		$this->sitemapUtils->csv_add_row( $filefullname, $row, $page_info );

		// --------------------------------------
		// 変更されたCSVをすべて保存する
		$result = $this->sitemapUtils->csv_save_all();
		if( !$result ){
			$this->sitemapUtils->unlock();
			return array(
				'result' => false,
				'message' => 'Failed to save sitemap file.',
				'errors' => null,
			);
		}

		$this->sitemapUtils->unlock();

		return $rtn;
	}


	/**
	 * サイトマップファイルの行データを移動する
	 */
	public function move_page_info_raw( $from_filename, $from_row, $to_filename, $to_row ){
		if( !$this->sitemapUtils->lock() ){
			return array(
				'result' => false,
				'message' => 'Sitemap is locked.',
			);
		}

		// --------------------------------------
		// サイトマップCSVを開く
		if( !$this->sitemapUtils->csv_open($from_filename) ){
			$this->sitemapUtils->unlock();
			return array(
				'result' => false,
				'message' => 'Failed to open from_filename.',
			);
		}
		if( !$this->sitemapUtils->csv_open($to_filename) ){
			$this->sitemapUtils->unlock();
			return array(
				'result' => false,
				'message' => 'Failed to open to_filename.',
			);
		}

		$from_row_assoc = $this->sitemapUtils->csv_get_row($from_filename, $from_row);
		$this->sitemapUtils->csv_remove_row($from_filename, $from_row);


		if( $from_filename === $to_filename && $from_row < $to_row ){
			// 同じファイル内での移動で、かつ、from より to のほうが行番号が大きいとき
			// from を1行抜いた分、toを1つ小さくする。
			$to_row --;
		}

		$result = $this->sitemapUtils->csv_add_row($to_filename, $to_row, $from_row_assoc);
		if( !$result ){
			$this->sitemapUtils->unlock();
			return array(
				'result' => false,
				'message' => 'Failed to move sitemap file (from, to).',
			);
		}

		// --------------------------------------
		// 変更されたCSVをすべて保存する
		$result = $this->sitemapUtils->csv_save_all();
		if( !$result ){
			$this->sitemapUtils->unlock();
			return array(
				'result' => false,
				'message' => 'Failed to save sitemap file.',
			);
		}

		$this->sitemapUtils->unlock();

		return array(
			'result' => true,
			'message' => 'OK',
		);
	}


	/**
	 * サイトマップファイルの行データを直接更新する
	 */
	public function update_page_info_raw( $filefullname, $row = 0, $page_info = array() ){
		if( !$this->sitemapUtils->lock() ){
			return array(
				'result' => false,
				'message' => 'Sitemap is locked.',
				'errors' => null,
			);
		}

		$rtn = array(
			'result'=>true,
			'message'=>'OK',
			'errors' => null,
		);
		$is_impact_to_children = false; // 子ページへの影響
		$validated = $this->sitemapUtils->validate_page_info( $page_info );
		if( count($validated) ){
			$rtn = array(
				'result'=>false,
				'message'=>'Validation Error.',
				'errors' => $validated,
			);
			$this->sitemapUtils->unlock();
			return $rtn;
		}

		// --------------------------------------
		// サイトマップCSVを開く
		$csv = &$this->sitemapUtils->csv_open($filefullname);
		if( !$csv ){
			$this->sitemapUtils->unlock();
			return array(
				'result' => false,
				'message' => 'Failed to load sitemap file.',
				'errors' => null,
			);
		}
		if( !$this->sitemapUtils->has_sitemap_definition( $csv['csv_rows'] ) && !$row){
			$this->sitemapUtils->unlock();
			return array(
				'result'=>false,
				'message'=>'Invalid row number.',
				'errors' => null,
			);
		}


		$sitemap_definition = $this->sitemapUtils->parse_sitemap_definition( $csv['csv_rows'] );
		$sitemap_definition_flip = array_flip($sitemap_definition);


		// --------------------------------------
		// content の変更を検出
		$tmp_diff_content = array(
			'before' => null,
			'after' => null,
		);
		if( isset($sitemap_definition_flip['content']) && isset($csv['csv_rows'][$row][$sitemap_definition_flip['content']]) && strlen($csv['csv_rows'][$row][$sitemap_definition_flip['content']]) ){
			$tmp_diff_content['before'] = $csv['csv_rows'][$row][$sitemap_definition_flip['content']];
		}elseif( isset($sitemap_definition_flip['path']) && isset($csv['csv_rows'][$row][$sitemap_definition_flip['path']]) ){
			$tmp_diff_content['before'] = $csv['csv_rows'][$row][$sitemap_definition_flip['path']];
		}
		if( isset($page_info['content']) ){
			$tmp_diff_content['after'] = $page_info['content'];
		}elseif( isset($page_info['path']) ){
			$tmp_diff_content['after'] = $page_info['path'];
		}
		if( is_string($tmp_diff_content['before']) && is_string($tmp_diff_content['after']) && $tmp_diff_content['before'] !== $tmp_diff_content['after'] ){

			// もともとアサインされていたコンテンツファイルの移動
			$contentEditor = new contentEditor($this->px2dthelper, $this->px);
			$result = $contentEditor->move( $tmp_diff_content['before'], $tmp_diff_content['after'] );

		}


		// --------------------------------------
		// path の変更を検出
		$tmp_diff_path = array(
			'before' => null,
			'after' => null,
		);
		if( isset($sitemap_definition_flip['path']) && isset($csv['csv_rows'][$row][$sitemap_definition_flip['path']]) ){
			$tmp_diff_path['before'] = $csv['csv_rows'][$row][$sitemap_definition_flip['path']];
		}
		if( isset($page_info['path']) ){
			$tmp_diff_path['after'] = $page_info['path'];
		}
		if( is_string($tmp_diff_path['before']) && is_string($tmp_diff_path['after']) && $tmp_diff_path['before'] !== $tmp_diff_path['after'] ){
			// TODO: path の変更にあたり影響範囲にも変更を反映する処理を追加する。
			// - 他の記事に含まれるこのページへのリンクの張り替え

			$is_impact_to_children = true;
		}


		// --------------------------------------
		// logical_path の変更を検出
		$tmp_diff_logical_path = array(
			'before' => null,
			'after' => null,
		);
		if( isset($sitemap_definition_flip['logical_path']) && isset($csv['csv_rows'][$row][$sitemap_definition_flip['logical_path']]) ){
			$tmp_diff_logical_path['before'] = $csv['csv_rows'][$row][$sitemap_definition_flip['logical_path']];
		}
		if( isset($page_info['logical_path']) ){
			$tmp_diff_logical_path['after'] = $page_info['logical_path'];
		}
		if( is_string($tmp_diff_logical_path['before']) && is_string($tmp_diff_logical_path['after']) && $tmp_diff_logical_path['before'] !== $tmp_diff_logical_path['after'] ){
			$is_impact_to_children = true;
		}


		// --------------------------------------
		// 影響下にある子ページを抽出
		$impact_children = array();
		$logical_path_depth_before = 0;
		$logical_path_array_before = array();
		$logical_path_depth_after = 0;
		$logical_path_array_after = array();
		if( $is_impact_to_children ){
			$current_path = $csv['csv_rows'][$row][$sitemap_definition_flip['path']];
			$current_page_info = $this->px->site()->get_page_info($current_path);
			$impact_children = $this->sitemapUtils->get_under_children_row( $current_page_info['path'] );

			if( strlen(trim($current_page_info['logical_path'])) ){
				$logical_path_array_before = preg_split('/\s*\>\s*/', $current_page_info['logical_path']);
			}
			array_push( $logical_path_array_before, $current_page_info['path'] );
			$logical_path_depth_before = count($logical_path_array_before);

			if( isset($tmp_diff_logical_path['after']) && is_string($tmp_diff_logical_path['after']) && strlen($tmp_diff_logical_path['after']) ){
				$logical_path_array_after = preg_split('/\s*\>\s*/', $tmp_diff_logical_path['after']);
			}
			array_push( $logical_path_array_after, $tmp_diff_path['after'] );
			$logical_path_depth_after = count($logical_path_array_after);
		}


		// --------------------------------------
		// 対象ページ自身の変更を反映
		if( !$this->sitemapUtils->csv_update_row($filefullname, $row, $page_info) ){
			$this->sitemapUtils->unlock();
			return array(
				'result' => false,
				'message' => 'Failed to update CSV row.',
				'errors' => null,
			);
		}

		// --------------------------------------
		// 子ページへの影響を反映
		if( $is_impact_to_children && count($impact_children) ){
			foreach($impact_children as $row_info){

				$logical_path_array = preg_split('/\s*\>\s*/', $row_info['logical_path']);

				array_splice($logical_path_array, 0, $logical_path_depth_before, $logical_path_array_after);

				$new_logical_path = implode('>', $logical_path_array);

				$this->sitemapUtils->csv_update_row($row_info['basename'], $row_info['row'], array('logical_path'=>$new_logical_path));
			}
		}

		// --------------------------------------
		// 変更されたCSVをすべて保存する
		$result = $this->sitemapUtils->csv_save_all();
		if( !$result ){
			$this->sitemapUtils->unlock();
			return array(
				'result' => false,
				'message' => 'Failed to save sitemap file.',
				'errors' => null,
			);
		}

		$this->sitemapUtils->unlock();

		return $rtn;
	}

	/**
	 * サイトマップファイルの行データを直接削除する
	 */
	public function delete_page_info_raw( $filefullname, $row = 0, $page_info = array() ){
		if( !$this->sitemapUtils->lock() ){
			return array(
				'result' => false,
				'message' => 'Sitemap is locked.',
			);
		}

		$rtn = array(
			'result'=>true,
			'message'=>'OK',
		);

		if( !$this->sitemapUtils->csv_open($filefullname) ){
			$this->sitemapUtils->unlock();
			return array(
				'result' => false,
				'message' => 'Failed to load sitemap file.',
			);
		}

		if( !$this->sitemapUtils->csv_has_sitemap_definition( $filefullname ) && !$row){
			$this->sitemapUtils->unlock();
			return array(
				'result'=>false,
				'message'=>'Invalid row number.',
			);
		}

		if( !$this->sitemapUtils->csv_remove_row($filefullname, $row) ){
			$this->sitemapUtils->unlock();
			return array(
				'result'=>false,
				'message'=>'Failed to remove CSV row.',
			);
		}

		$result = $this->sitemapUtils->csv_save_all();
		if( !$result ){
			$this->sitemapUtils->unlock();
			return array(
				'result' => false,
				'message' => 'Failed to save sitemap file.',
			);
		}

		$this->sitemapUtils->unlock();

		return $rtn;
	}

}
