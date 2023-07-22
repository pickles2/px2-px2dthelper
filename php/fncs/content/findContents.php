<?php
/**
 * px2-px2dthelper findContents
 */
namespace tomk79\pickles2\px2dthelper\fncs\content;

/**
 * px2-px2dthelper findContents
 */
class findContents{

	/** Picklesオブジェクト */
	private $px;

	/** px2dthelperオブジェクト */
	private $px2dthelper;

	/** 環境情報 */
	private $env;

	/**
	 * constructor
	 *
	 * @param object $px2dthelper $px2dthelperオブジェクト
	 * @param object $px $pxオブジェクト
	 * @param object $env 環境情報
	 */
	public function __construct( $px2dthelper, $px, $env ){
		$this->px2dthelper = $px2dthelper;
		$this->px = $px;
		$this->env = $env;
	}

	/**
	 * コンテンツファイルを検索する
	 * @param  callback $callback 引数に `$realpath` を返す
	 * @return boolean           実行結果
	 */
	public function find($callback){
		return $this->read_dir_r(null, $callback);
	}

	/**
	 * ディレクトリを再帰的に読み込む
	 * @param  string $path_current_dir カレントディレクトリ
	 * @param  callback $callback  [description]
	 * @return boolean           実行結果
	 */
	private function read_dir_r($path_current_dir, $callback){

		$realpath_current_dir = $this->px->fs()->normalize_path(
			$this->px->fs()->get_realpath(
				$this->env->realpath_controot.$path_current_dir
			)
		);
		if( preg_match('/^'.preg_quote($this->env->realpath_homedir, '/').'/s', $realpath_current_dir) ){
			// homedir 内は検索しない
			return true;
		}

		$ls = $this->px->fs()->ls($this->env->realpath_controot.$path_current_dir);
		foreach($ls as $basename){
			if( is_dir($this->env->realpath_controot.$path_current_dir.$basename) ){
				$this->read_dir_r($path_current_dir.$basename.'/', $callback);
			}elseif( is_file($this->env->realpath_controot.$path_current_dir.$basename) ){
				if( !preg_match('/^.*\.html?(?:\.[a-zA-Z0-9]+)?$/s', $basename) ){
					// 拡張子 .html, .htm, およびその2重拡張子以外の場合はスキップ
					continue;
				}

				if( $this->px->get_path_proc_type('/'.$path_current_dir.$basename) == 'ignore' ){
					// 管理外のパスは検出させない
					continue;
				}

				$callback('/'.$path_current_dir.$basename);
			}
		}
		return true;
	}
}
