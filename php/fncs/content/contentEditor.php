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

		$this->directory_index_primary = $this->px->get_directory_index_primary();
		$this->realpath_docroot = $this->px->fs()->normalize_path($this->px->get_realpath_docroot());
		$this->path_controot = $this->px->fs()->normalize_path($this->px->get_path_controot());
		$this->realpath_controot = $this->px->fs()->normalize_path($this->px->fs()->get_realpath($this->realpath_docroot.$this->path_controot));
		$this->realpath_homedir = $this->px->fs()->normalize_path($this->px->get_realpath_homedir());
	}

	/**
	 * コンテンツファイルを移動する
	 */
	public function move( $from, $to ){

		// 対象コンテンツファイルのリストを作成する
		$pathsFromTo = $this->make_content_file_list($from, $to);
		if(!is_array($pathsFromTo)){
			return array(
				'result' => false,
				'message' => 'Failed to make content file list.',
			);
		}

		// 対象コンテンツファイルを移動する
		$result = $this->move_content_files($pathsFromTo);
		if( !$result->result ){
			return array(
				'result' => false,
				'message' => 'Failed to move some files or directories.',
			);
		}

		// // コンテンツに記述されたリソースファイルのリンクを解決する
		// $this->resolve_content_resource_links($pathsFromTo, $from, $to);

		// // コンテンツの `data.json` に記述されたリソースファイルのリンクを解決する
		// $this->resolve_content_resource_links_in_datajson($pathsFromTo, $from, $to);

		// // コンテンツの被リンクを解決する
		// $this->resolve_content_incoming_links($pathsFromTo, $from, $to);


		return array(
			'result' => true,
			'message' => 'OK',
		);
	}

	/**
	 * 対象コンテンツファイルのリストを作成する
	 * @param  string $from 対象コンテンツのパス
	 * @param  string $to   移動先のコンテンツパス
	 * @return array ファイルの一覧
	 */
	private function make_content_file_list( $from, $to ){
		if(preg_match('/\/$/s', $from)){ $from = $from.$this->px->get_directory_index_primary(); }
		if(preg_match('/\/$/s', $to  )){ $to   = $to  .$this->px->get_directory_index_primary(); }

		$pathsFromTo = array();

		$dirname = dirname($from);
		if( !is_dir($this->realpath_controot.$dirname) ){
			// 対象ディレクトリが存在しません。
			return false;
		}
		$ls = $this->px->fs()->ls($this->realpath_controot.$dirname);
		foreach($ls as $basename){
			if( !is_file($this->realpath_controot.$dirname.'/'.$basename) ){
				// ファイル以外はスキップ
				continue;
			}
			if( $basename == basename($from) ){
				// ズバリ存在したら
				array_push(
					$pathsFromTo,
					array(
						$this->px->fs()->normalize_path($this->px->fs()->get_realpath($from)),
						$this->px->fs()->normalize_path($this->px->fs()->get_realpath($to))
					)
				);
				continue;
			}
			if( preg_match( '/^'.preg_quote(basename($from), '/').'\\.([a-zA-Z0-9]+)$/s', $basename, $matched ) ){
				// 2重拡張子と判定できる場合
				array_push(
					$pathsFromTo,
					array(
						$this->px->fs()->normalize_path($this->px->fs()->get_realpath($from.'.'.$matched[1])),
						$this->px->fs()->normalize_path($this->px->fs()->get_realpath($to.'.'.$matched[1]))
					)
				);
				continue;
			}
		}

		// コンテンツの専用リソースパス
		array_push(
			$pathsFromTo,
			array(
				$this->px->fs()->normalize_path($this->px->fs()->get_realpath($this->px2dthelper->path_files($from))),
				$this->px->fs()->normalize_path($this->px->fs()->get_realpath($this->px2dthelper->path_files($to)))
			)
		);
		return $pathsFromTo;
	}

	/**
	 * コンテンツのファイルを移動させる
	 * @param  array $pathsFromTo 対象コンテンツのパス
	 * @return object 実行結果
	 */
	private function move_content_files( $pathsFromTo ){

		// 移動できるか検証する
		foreach( $pathsFromTo as $fromTo ){
			if( file_exists($this->realpath_controot.$fromTo[1]) ){
				// 移動先にすでにファイルが存在する。
				return (object) array(
					"result"=>false,
				);
			}
		}

		// 実際の移動処理
		foreach( $pathsFromTo as $fromTo ){
			$result = $this->px->fs()->rename_f(
				$this->realpath_controot.$fromTo[0],
				$this->realpath_controot.$fromTo[1]
			);
		}

		return (object) array(
			"result"=>true,
		);
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
