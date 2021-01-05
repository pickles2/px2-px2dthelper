<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper;

/**
 * pxcmd.php
 */
class customConsoleExtensions_pxcmdOperator{

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
	 * Custom Console Extensions 設定を取得する
	 * @return array 抜き出された設定値。設定されていない場合は `false` を返します。
	 */
	private function get_cce_conf(){
		$conf = $this->main->get_px2dtconfig();
		if( !is_object($conf) || !property_exists($conf, 'customConsoleExtensions') ){
			return false;
		}
		$ccEConf = (array) $conf->customConsoleExtensions;
		return $ccEConf;
	}

	/**
	 * 拡張機能の一覧を返す
	 * @return array 拡張機能の一覧
	 */
	public function get_list(){
		$ccEConf = $this->get_cce_conf();
		if( !is_array($ccEConf) && !is_object($ccEConf) ){
			return false;
		}

		$rtn = array();
		foreach($ccEConf as $cce_id=>$ccEInfo){
			$rtn[$cce_id] = array();
			$rtn[$cce_id]['id'] = $cce_id;
			$rtn[$cce_id]['label'] = $cce_id;
			$rtn[$cce_id]['className'] = null;
			if( is_string($ccEInfo) ){
				$rtn[$cce_id]['className'] = $ccEInfo;
			}
			$cceObj = $this->get($cce_id);
			if( !$cceObj ){
				continue;
			}
			$rtn[$cce_id]['label'] = $cceObj->get_label();
		}

		return $rtn;
	}

	/**
	 * 拡張機能を返す
	 * @return object 拡張機能。拡張機能をロードできない場合、 `false` を返します。
	 */
	public function get( $cce_id ){
		$ccEConf = $this->get_cce_conf();
		if( !is_array($ccEConf) && !is_object($ccEConf) ){
			return false;
		}

		$ccEInfo = null;
		if( is_array($ccEConf) ){
			if( !array_key_exists($cce_id, $ccEConf) ){
				return false;
			}
			$ccEInfo = $ccEConf[$cce_id];
		}elseif( is_object($ccEConf) ){
			if( !property_exists($ccEConf, $cce_id) ){
				return false;
			}
			$ccEInfo = $ccEConf->{$cce_id};
		}

		$className = null;
		if( is_string($ccEInfo) ){
			$className = $ccEInfo;
		}
		if( !strlen($className) ){
			return false;
		}
		if( !class_exists($ccEInfo) ){
			return false;
		}

		$rtn = new $ccEInfo( $this->px );

		return $rtn;
	}

	/**
	 * PX Command を実行する
	 */
	public function execute_px_command( $ary_px_command ){
		$cce_id = null;
		$subcommand = null;

		if( !count($ary_px_command) ){
			// 拡張機能のIDがない場合、
			// 拡張機能の一覧を返す。
			$list = $this->get_list();
			return $list;
		}
		if( array_key_exists(0, $ary_px_command) && strlen($ary_px_command[0]) ){
			$cce_id = $ary_px_command[0];
		}
		if( array_key_exists(1, $ary_px_command) && strlen($ary_px_command[1]) ){
			$subcommand = $ary_px_command[1];
		}

		$ccExt = $this->get($cce_id);
		if( !$ccExt ){
			return array(
				'result' => false,
				'message' => 'Custom Console Extension "'.$cce_id.'" is NOT available.',
			);
		}
		if( strlen($subcommand) ){
			switch( $subcommand ){
				case 'gpi':
					// $data = $this->px->req()->get_param('data');
					// $data_filename = $this->px->req()->get_param('data_filename');
					// if( strlen($data_filename) ){
					// 	if( strpos( $data_filename, '/' ) !== false || strpos( $data_filename, '\\' ) !== false || $data_filename == '.' || $data_filename == '.' ){
					// 		// ディレクトリトラバーサル対策
					// 		return false;
					// 		break;
					// 	}
					// }
					// $realpath_data_file = $this->px->get_realpath_homedir().'/_sys/ram/data/'.$data_filename;
					// if( !strlen( $data ) && strlen($data_filename) && is_file( $realpath_data_file ) ){
					// 	$data = file_get_contents( $realpath_data_file );
					// 	$data = json_decode($data, true);
					// }else{
					// 	$data = json_decode(base64_decode($data), true);
					// }
					// $rtn = $px2ce->gpi( $data );
					// return $rtn;

					$request = $this->px->req()->get_param('request');
					$request = json_decode($request);
					$result = $ccExt->gpi( $request );
					return $result;
					break;

				case 'client_resources':
					$realpath_base_dir = $ccExt->get_client_resource_base_dir();
					$client_resources = $ccExt->get_client_resource_list();
					$realpath_dist = $this->px->req()->get_param('dist');
					if( strlen($realpath_dist) ){
						$this->px->fs()->copy_r($realpath_base_dir, $realpath_dist);
					}
					$rtn = array();
					foreach($client_resources as $key=>$row){
						$rtn[$key] = array();
						foreach($row as $path){
							if( !strlen($realpath_dist) ){
								$path = realpath($realpath_base_dir.'/'.$path);
							}
							array_push($rtn[$key], $path);
						}
					}
					return $rtn;
					break;
			}
		}

		return false;
	}

}
