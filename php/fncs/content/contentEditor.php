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

	private $directory_index_primary;
	private $realpath_docroot;
	private $path_controot;
	private $realpath_controot;
	private $realpath_homedir;

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
	 * @param String $from 移動元の content
	 * @param String $to   移動先の content
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

		// コンテンツに記述されたリソースファイルのリンクを解決する
		$this->resolve_content_resource_links($pathsFromTo, $from, $to);

		// コンテンツの `data.json` に記述されたリソースファイルのリンクを解決する
		$this->resolve_content_resource_links_in_datajson($pathsFromTo, $from, $to);

		// コンテンツの被リンクを解決する
		$this->resolve_content_incoming_links($pathsFromTo, $from, $to);


		return array(
			'result' => true,
			'message' => 'OK',
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


		$existsMain = $this->px->fs()->is_file( $contRoot.''.$pathContent );
		$existsFiles = $this->px->fs()->is_dir( $contRoot.''.$pathFiles );
		if( !$existsMain && !$existsFiles ){
			return array(
				'result' => true,
				'message' => 'Contents not exists.',
			);
		}

		$resMain = ($existsMain ? $this->px->fs()->rm( $contRoot.''.$pathContent ) : true);
		$resFiles = ($existsFiles ? $this->px->fs()->rm( $contRoot.''.$pathFiles ) : true);
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

	// ----------------------------------------------------------------------------


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

		if( !count($pathsFromTo) ){
			// 対象ファイルは見つからなかった
			return false;
		}

		// コンテンツの専用リソースパス
		array_push(
			$pathsFromTo,
			array(
				$this->px->fs()->normalize_path($this->px->fs()->get_realpath($this->px2dthelper->bind_path_files($this->px->conf()->path_files ?? '', $from))),
				$this->px->fs()->normalize_path($this->px->fs()->get_realpath($this->px2dthelper->bind_path_files($this->px->conf()->path_files ?? '', $to)))
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
	 * コンテンツに記述されたリソースファイルのリンクを解決する
	 * @param  array $pathsFromTo 対象コンテンツのパス
	 * @param  string $from 対象コンテンツのパス
	 * @param  string $to   移動先のコンテンツパス
	 * @return boolean      実行結果
	 */
	private function resolve_content_resource_links($pathsFromTo, $from, $to){
		foreach($pathsFromTo as $fromTo){
			if( !is_file($this->realpath_controot.$fromTo[1]) ){
				continue;
			}
			$realpath_file = $this->realpath_controot.$fromTo[1];

			$bin = $this->px->fs()->read_file( $realpath_file );
			$bin_md5 = md5($bin);

			$bin = $this->resolve_content_resource_links_in_src($bin, $from, $to);

			if( $bin_md5 !== md5($bin) ){
				$result = $this->px->fs()->save_file( $realpath_file, $bin );
			}
		}
		return true;
	}


	/**
	 * コンテンツに記述されたリソースファイルのリンクを解決する
	 * @param  string $src 対象コンテンツのソース
	 * @param  string $from 対象コンテンツのパス
	 * @param  string $to   移動先のコンテンツパス
	 * @return string      実行後の新しい `$src`
	 */
	private function resolve_content_resource_links_in_src($src, $from, $to){
		$path_detector = new pathDetector($this->px2dthelper, $this->px);
		$src = $path_detector->path_detect_in_md($src, function( $path ) use ($from, $to){
			return $this->resolve_path($path, $from, $to);
		});
		return $src;
	}

	/**
	 * コンテンツの `data.json` に記述されたリソースファイルのリンクを解決する
	 * @param  array $pathsFromTo 対象コンテンツのパス
	 * @param  string $from 対象コンテンツのパス
	 * @param  string $to   移動先のコンテンツパス
	 * @return boolean      実行結果
	 */
	private function resolve_content_resource_links_in_datajson($pathsFromTo, $from, $to){
		$fnc_resolve_r = function($bin_obj) use (&$fnc_resolve_r, $from, $to){
			foreach($bin_obj as $key=>$row){
				if(is_object($row) || is_array($row)){
					if( is_object($bin_obj) ){
						$bin_obj->$key = $fnc_resolve_r($bin_obj->$key);
					}elseif( is_array($bin_obj) ){
						$bin_obj[$key] = $fnc_resolve_r($bin_obj[$key]);
					}
				}elseif(is_string($row)){
					if( preg_match('/^(?:\.\/|\.\.\/|\/)(?:[^\s]*)(?:\.[a-zA-Z0-9]+)?$/s', $row) ){
						// 値全体として1つのパスと認識できる場合
						if( is_object($bin_obj) ){
							$bin_obj->$key = $this->resolve_path($bin_obj->$key, $from, $to);
						}elseif( is_array($bin_obj) ){
							$bin_obj[$key] = $this->resolve_path($bin_obj[$key], $from, $to);
						}
					}else{
						if( is_object($bin_obj) ){
							$bin_obj->$key = $this->resolve_content_resource_links_in_src($bin_obj->$key, $from, $to);
						}elseif( is_array($bin_obj) ){
							$bin_obj[$key] = $this->resolve_content_resource_links_in_src($bin_obj[$key], $from, $to);
						}
					}
				}
			}
			return $bin_obj;
		};

		foreach($pathsFromTo as $fromTo){
			if( !is_dir($this->realpath_controot.$fromTo[1]) ){
				continue;
			}
			if( !is_file($this->realpath_controot.$fromTo[1].'/guieditor.ignore/data.json') ){
				continue;
			}
			$realpath_file = $this->realpath_controot.$fromTo[1].'/guieditor.ignore/data.json';

			$bin = $this->px->fs()->read_file( $realpath_file );
			$bin_obj = json_decode($bin);
			$json_encode_option = JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE;
			$bin_md5 = md5(json_encode($bin_obj, $json_encode_option));

			$bin_obj = $fnc_resolve_r($bin_obj);

			$bin = json_encode($bin_obj, $json_encode_option);
			if( $bin_md5 !== md5($bin) ){
				$result = $this->px->fs()->save_file( $realpath_file, $bin );
			}
		}
		return true;
	}


	/**
	 * コンテンツの被リンクを解決する
	 * @param  string $pathsFromTo
	 * @param  string $from 対象コンテンツのパス
	 * @param  string $to   移動先のコンテンツパス
	 * @return boolean      実行結果
	 */
	private function resolve_content_incoming_links($pathsFromTo, $from, $to){
		$find_contents = new findContents($this->px2dthelper, $this->px, (object) array(
			"realpath_homedir" => $this->realpath_homedir,
			"realpath_controot" => $this->realpath_controot,
		));
		$find_contents->find(function($path_current) use ($pathsFromTo, $from, $to){

			foreach($pathsFromTo as $pathFromTo){
				if( $pathFromTo[1] == $path_current ){
					return;
				}
			}

			// コンテンツを更新
			$realpath_file = $this->realpath_controot.$path_current;
			$bin = $this->px->fs()->read_file( $realpath_file );
			$bin_md5 = md5($bin);

			$bin = $this->resolve_content_resource_incoming_links_in_src($bin, $path_current, $from, $to);

			if( $bin_md5 !== md5($bin) ){
				$result = $this->px->fs()->save_file( $realpath_file, $bin );
			}

			// data.json を更新
			$fnc_resolve_r = function($bin_obj) use (&$fnc_resolve_r, $path_current, $from, $to){
				foreach($bin_obj as $key=>$row){
					if(is_object($row) || is_array($row)){
						if( is_object($bin_obj) ){
							$bin_obj->$key = $fnc_resolve_r($bin_obj->$key);
						}elseif( is_array($bin_obj) ){
							$bin_obj[$key] = $fnc_resolve_r($bin_obj[$key]);
						}
					}elseif(is_string($row)){
						if( preg_match('/^(?:\.\/|\/)(?:[^\s]*)(?:\.[a-zA-Z0-9]+)$/s', $row) ){
							// 値全体として1つのパスと認識できる場合
							if( is_object($bin_obj) ){
								$bin_obj->$key = $this->resolve_incoming_path($bin_obj->$key, $path_current, $from, $to);
							}elseif( is_array($bin_obj) ){
								$bin_obj[$key] = $this->resolve_incoming_path($bin_obj[$key], $path_current, $from, $to);
							}
						}else{
							if( is_object($bin_obj) ){
								$bin_obj->$key = $this->resolve_content_resource_incoming_links_in_src($bin_obj->$key, $path_current, $from, $to);
							}elseif( is_array($bin_obj) ){
								$bin_obj[$key] = $this->resolve_content_resource_incoming_links_in_src($bin_obj[$key], $path_current, $from, $to);
							}
						}
					}
				}
				return $bin_obj;
			};
			$realpath_files = $this->px->fs()->normalize_path($this->realpath_controot.$this->px2dthelper->path_files($path_current).'guieditor.ignore/data.json');
			if( is_file( $realpath_files ) ){
				$bin = $this->px->fs()->read_file( $realpath_files );
				$bin_obj = json_decode($bin);
				$json_encode_option = JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE;
				$bin_md5 = md5(json_encode($bin_obj, $json_encode_option));

				$bin_obj = $fnc_resolve_r($bin_obj);

				$bin = json_encode($bin_obj, $json_encode_option);
				if( $bin_md5 !== md5($bin) ){
					$result = $this->px->fs()->save_file( $realpath_files, $bin );
				}
			}

		});
		return true;
	}

	/**
	 * コンテンツに記述されたリソースファイルのリンクを解決する
	 * @param  string $src 対象コンテンツのソース
	 * @param  string $path_current リンク元のパス
	 * @param  string $from 対象コンテンツのパス
	 * @param  string $to   移動先のコンテンツパス
	 * @return string      実行後の新しい `$src`
	 */
	private function resolve_content_resource_incoming_links_in_src($src, $path_current, $from, $to){
		$path_detector = new pathDetector($this->px2dthelper, $this->px);
		$src = $path_detector->path_detect_in_md($src, function( $path ) use ($path_current, $from, $to){
			return $this->resolve_incoming_path($path, $path_current, $from, $to);
		});
		return $src;
	}

	/**
	 * コンテンツ内のリンクを張り替える新しいパスを生成する
	 * @param  string $path 張り替えるパス
	 * @param  string $from 元のパス
	 * @param  string $to   移動先のパス
	 * @return string       変換後のパス文字列
	 */
	private function resolve_path($path, $from, $to){
		preg_match('/^([\s]*)(.*?)([\s]*)$/s', $path, $matched);
		$pre_s = $matched[1];
		$path = $matched[2];
		$s_end = $matched[3];
		if( preg_match('/^#/', $path) ){
			return $pre_s.$path.$s_end;
		}

		$path_type = 'relative';
		if( preg_match('/^\<\?(?:php|\=)?/', $path) ){
			$path_type = 'php';
			return $pre_s.$path.$s_end;
		}elseif( preg_match('/^[a-zA-Z0-9]+\:\/\//', $path) ){
			$path_type = 'url';
			return $pre_s.$path.$s_end; // TODO: 未実装
		}elseif( preg_match('/^\/\//', $path) ){
			$path_type = 'absolute_double_slashes';
			return $pre_s.$path.$s_end; // TODO: 未実装
		}elseif( preg_match('/^data\:/i', $path) ){
			$path_type = 'data';
			return $pre_s.$path.$s_end;
		}elseif( preg_match('/^javascript\:/i', $path) ){
			$path_type = 'javascript';
			return $pre_s.$path.$s_end;
		}elseif( preg_match('/^\//', $path) ){
			$path_type = 'absolute';
			$path_abs = $this->px->fs()->get_realpath($path, dirname($from));
		}elseif( preg_match('/^\.\//', $path) ){
			$path_type = 'relative_dot_slash';
			$path_abs = $this->px->fs()->get_realpath($path, dirname($from));
		}else{
			$path_type = 'relative';
			$path_abs = $this->px->fs()->get_realpath($path, dirname($from));
		}
		$path_abs = $this->px->fs()->normalize_path($path_abs);

		$new_path_abs = $path_abs;
		$from_files = $this->px2dthelper->path_files($from);
		$to_files = $this->px2dthelper->path_files($to);
		if( preg_match( '/^'.preg_quote($from_files, '/').'(.*)$/s', $path_abs, $matched ) ){
			$new_path_abs = $this->px->fs()->get_realpath($to_files.$matched[1]);
		}else{
			$new_path_abs = $this->px->fs()->get_realpath($path_abs);
		}
		$new_path_abs = $this->px->fs()->normalize_path($new_path_abs);

		$rtn = $path;
		switch($path_type){
			case 'url':
				break;
			case 'absolute_double_slashes':
				break;
			case 'absolute':
				$rtn = $new_path_abs;
				break;
			case 'relative_dot_slash':
				$path_rel = $this->px->fs()->get_relatedpath($new_path_abs, dirname($to));
				$path_rel = $this->px->fs()->normalize_path($path_rel);
				$path_rel = './'.preg_replace('/^\.\//s', '', $path_rel);
				$rtn = $path_rel;
				break;
			case 'relative':
				$path_rel = $this->px->fs()->get_relatedpath($new_path_abs, dirname($to));
				$path_rel = $this->px->fs()->normalize_path($path_rel);
				$path_rel = preg_replace('/^\.\//s', '', $path_rel);
				$rtn = $path_rel;
				break;
		}

		return $pre_s.$rtn.$s_end;
	}

	/**
	 * コンテンツへの被リンクを張り替える新しいパスを生成する
	 * @param  string $path 張り替えるパス
	 * @param  string $path_current リンク元のパス
	 * @param  string $from 元のパス
	 * @param  string $to   移動先のパス
	 * @return string       変換後のパス文字列
	 */
	private function resolve_incoming_path($path, $path_current, $from, $to){
		if(preg_match('/^'.preg_quote($to, '/').'(?:\.[a-zA-Z0-9]+)?$/s', $path_current)){
			// 対象ページ自身は変換対象にしない(処理済みなので)
			return $pre_s.$path.$s_end;
		}

		preg_match('/^([\s]*)(.*?)([\s]*)$/s', $path, $matched);
		$pre_s = $matched[1];
		$path = $matched[2];
		$s_end = $matched[3];

		if( preg_match('/^#/', $path) ){
			return $pre_s.$path.$s_end;
		}

		$path_type = 'relative';
		if( preg_match('/^\<\?(?:php|\=)?/', $path) ){
			$path_type = 'php';
			return $pre_s.$path.$s_end;
		}elseif( preg_match('/^[a-zA-Z0-9]+\:\/\//', $path) ){
			$path_type = 'url';
			return $pre_s.$path.$s_end; // TODO: 未実装
		}elseif( preg_match('/^\/\//', $path) ){
			$path_type = 'absolute_double_slashes';
			return $pre_s.$path.$s_end; // TODO: 未実装
		}elseif( preg_match('/^data\:/i', $path) ){
			$path_type = 'data';
			return $pre_s.$path.$s_end;
		}elseif( preg_match('/^javascript\:/i', $path) ){
			$path_type = 'javascript';
			return $pre_s.$path.$s_end;
		}elseif( preg_match('/^\//', $path) ){
			$path_type = 'absolute';
			$path_abs = $this->px->fs()->get_realpath($path, dirname($path_current));
		}elseif( preg_match('/^\.\//', $path) ){
			$path_type = 'relative_dot_slash';
			$path_abs = $this->px->fs()->get_realpath($path, dirname($path_current));
		}else{
			$path_type = 'relative';
			$path_abs = $this->px->fs()->get_realpath($path, dirname($path_current));
		}
		$path_abs = $this->px->fs()->normalize_path($path_abs);

		if( $path_abs != $from && $path_abs.$this->directory_index_primary != $from ){
			return $pre_s.$path.$s_end;
		}

		$new_path_abs = $this->px->fs()->get_realpath($to);
		$new_path_abs = $this->px->fs()->normalize_path($new_path_abs);

		$rtn = $path;
		switch($path_type){
			case 'url':
				break;
			case 'absolute_double_slashes':
				break;
			case 'absolute':
				$rtn = $new_path_abs;
				break;
			case 'relative_dot_slash':
				$path_rel = $this->px->fs()->get_relatedpath($new_path_abs, dirname($path_current));
				$path_rel = $this->px->fs()->normalize_path($path_rel);
				$path_rel = './'.preg_replace('/^\.\//s', '', $path_rel);
				$rtn = $path_rel;
				break;
			case 'relative':
				$path_rel = $this->px->fs()->get_relatedpath($new_path_abs, dirname($path_current));
				$path_rel = $this->px->fs()->normalize_path($path_rel);
				$path_rel = preg_replace('/^\.\//s', '', $path_rel);
				$rtn = $path_rel;
				break;
		}

		return $pre_s.$rtn.$s_end;
	}

}
