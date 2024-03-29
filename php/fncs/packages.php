<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper\fncs;

/**
 * packages.php
 */
class packages{

	/**
	 * Picklesオブジェクト
	 */
	private $px;

	/**
	 * px2dthelper main
	 */
	private $main;

	/**
	 * constructor
	 *
	 * @param object $px $pxオブジェクト
	 * @param object $main main.php のインスタンス
	 */
	public function __construct( $px, $main ){
		$this->px = $px;
		$this->main = $main;
	}

	/**
	 * パッケージの一覧を取得する
	 * @return array パッケージの一覧
	 */
	public function get_package_list(){
		$rtn = json_decode('{}');
		$rtn->themes = array();
		$rtn->broccoliModules = array();
		$rtn->broccoliFields = array();
		$rtn->plugin = array();
		$rtn->processors = array();
		$rtn->projects = array();

		// composer パッケージをスキャン
		$path_composer_root = $this->get_path_composer_root_dir();
		if( is_dir($path_composer_root) ){
			$rtn = $this->parse_package_dir($path_composer_root, $rtn);
		}
		if( is_dir($path_composer_root.'vendor/') ){
			$ls1 = $this->px->fs()->ls($path_composer_root.'vendor/');
			foreach( $ls1 as $filename1 ){
				if( is_dir($path_composer_root.'vendor/'.$filename1) ){
					$ls2 = $this->px->fs()->ls($path_composer_root.'vendor/'.$filename1);
					foreach( $ls2 as $filename2 ){
						if( is_dir($path_composer_root.'vendor/'.$filename1.'/'.$filename2.'/') ){
							$rtn = $this->parse_package_dir($path_composer_root.'vendor/'.$filename1.'/'.$filename2.'/', $rtn);
						}
					}
				}
			}
		}

		// npm パッケージをスキャン
		$path_npm_root = $this->get_path_npm_root_dir();
		if( is_dir($path_npm_root) ){
			$rtn = $this->parse_package_dir($path_npm_root, $rtn);
		}
		if( is_dir($path_npm_root.'node_modules/') ){
			$ls1 = $this->px->fs()->ls($path_npm_root.'node_modules/');
			foreach( $ls1 as $filename1 ){
				if( is_dir($path_npm_root.'node_modules/'.$filename1) ){
					$rtn = $this->parse_package_dir($path_npm_root.'node_modules/'.$filename1.'/', $rtn);
				}
			}
		}

		return $rtn;
	} // get_package_list()

	/**
	 * ディレクトリを解析してパッケージ情報を抽出する
	 * @param string $path_dir 対象ディレクトリのパス
	 * @param object $package_list 一覧を格納するオブジェクト
	 * @return object 更新された `$package_list`
	 */
	private function parse_package_dir( $path_dir, $package_list ){
		// composer
		if( is_file($path_dir.'/composer.json') ){
			$json = @json_decode($this->px->fs()->read_file($path_dir.'/composer.json'));
			if( is_object($json->extra->px2package ?? null) ){
				// var_dump($path_dir);
				// var_dump($json->extra->px2package);
				$package_list = $this->parse_px2package_in_composer_json_row($json->extra->px2package, null, $path_dir, $package_list);
			}elseif( is_array($json->extra->px2package ?? null) ){
				// var_dump($path_dir);
				foreach( $json->extra->px2package as $number=>$px2package ){
					// var_dump($package);
					$package_list = $this->parse_px2package_in_composer_json_row($px2package, $idx_num, $path_dir, $package_list);
				}
			}
		}

		// broccoli
		if( is_file($path_dir.'/broccoli.json') ){
			$json = @json_decode($this->px->fs()->read_file($path_dir.'/broccoli.json'));
			if( @is_object($json) ){
				// var_dump($path_dir);
				// var_dump($json);
				$package_list = $this->parse_broccoli_in_broccoli_json_row($json, null, $path_dir, $package_list);
			}elseif( @is_array($json) ){
				// var_dump($path_dir);
				foreach($json as $idx_num=>$package){
					// var_dump($json);
					$package_list = $this->parse_broccoli_in_broccoli_json_row($package, $idx_num, $path_dir, $package_list);
				}
			}
		}
		return $package_list;
	} // parse_package_dir()

	/**
	 * px2package を調査してパッケージ情報を抽出する
	 * @param  object $row 1件分のパッケージ情報
	 * @param  int $idx_num インデックス番号
	 * @param  string $path_dir 対象ディレクトリのパス
	 * @param  object $package_list 一覧を格納するオブジェクト
	 * @return object 更新された `$package_list`
	 */
	private function parse_px2package_in_composer_json_row($row, $idx_num, $path_dir, $package_list){
		switch( $row->type ){
			case 'theme':
				$row->path = $this->px->fs()->get_realpath( $path_dir.'/'.$row->path );
				array_push($package_list->themes, $row);
				break;
			case 'plugin':
				array_push($package_list->plugin, $row);
				break;
			case 'processor':
				array_push($package_list->processors, $row);
				break;
			case 'project':
				$row->path = $this->px->fs()->get_realpath( $path_dir.'/'.$row->path );
				$row->path_homedir = $this->px->fs()->get_realpath( $path_dir.'/'.$row->path_homedir );
				array_push($package_list->projects, $row);
				break;
		}
		return $package_list;
	}
	/**
	 * broccoli.json を調査してパッケージ情報を抽出する
	 * @param  object $row 1件分のパッケージ情報
	 * @param  int $idx_num インデックス番号
	 * @param  string $path_dir 対象ディレクトリのパス
	 * @param  object $package_list 一覧を格納するオブジェクト
	 * @return object 更新された `$package_list`
	 */
	private function parse_broccoli_in_broccoli_json_row($row, $idx_num, $path_dir, $package_list){
		switch( $row->type ){
			case 'module':
				$row->path = $this->px->fs()->get_realpath( $path_dir.'/'.$row->path );
				array_push($package_list->broccoliModules, $row);
				break;
			case 'field':
				$row->backend->require = $this->px->fs()->get_realpath( $path_dir.'/'.$row->backend->require );
				if( is_array($row->frontend->file) ){
					foreach( $row->frontend->file as $num => $val ){
						$row->frontend->file[$num] = $this->px->fs()->get_realpath( $path_dir.'/'.$row->frontend->file[$num] );
					}					
				}elseif( is_string($row->frontend->file) ){
					$row->frontend->file = $this->px->fs()->get_realpath( $path_dir.'/'.$row->frontend->file );
				}
				array_push($package_list->broccoliFields, $row);
				break;
		}
		return $package_list;
	}

	/**
	 * composer root を取得
	 * `composer.json` が設置されているディレクトリのパスを返します。
	 * @return string `composer.json` が設置されているディレクトリのパス
	 */
	public function get_path_composer_root_dir(){
		$cd = realpath('.');
		if(!is_dir($cd)){
			return false;
		}
		while(1){
			if( is_file($cd.'/composer.json') ){
				// 発見
				return $cd.DIRECTORY_SEPARATOR;
			}
			if( realpath($cd) == realpath('/') ){
				// もうルートディレクトリまで来てしまった
				return false;
			}
			$cd = realpath(dirname($cd));
		}
		return false;
	} // get_path_composer_root_dir()

	/**
	 * npm root を取得
	 * `package.json` が設置されているディレクトリのパスを返します。
	 * @return string `package.json` が設置されているディレクトリのパス
	 */
	public function get_path_npm_root_dir(){
		$cd = realpath('.');
		if(!is_dir($cd)){
			return false;
		}
		while(1){
			if( is_file($cd.'/package.json') ){
				// 発見
				return $cd.DIRECTORY_SEPARATOR;
			}
			if( realpath($cd) == realpath('/') ){
				// もうルートディレクトリまで来てしまった
				return false;
			}
			$cd = realpath(dirname($cd));
		}
		return false;
	} // get_path_npm_root_dir()

}
