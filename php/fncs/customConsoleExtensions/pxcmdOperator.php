<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper\fncs\customConsoleExtensions;

/**
 * pxcmdOperator.php
 *
 * PX Command `PX=px2dthelper.custom_console_extensions.*` を処理します。
 */
class pxcmdOperator{

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
		if( !is_object($conf) || !property_exists($conf, 'custom_console_extensions') ){
			return false;
		}
		$ccEConf = (array) $conf->custom_console_extensions;
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
			$tmp_cce_info = array();
			$tmp_cce_info['id'] = $cce_id;
			$tmp_cce_info['label'] = $cce_id;
			$tmp_cce_info['class_name'] = null;
			$tmp_cce_info['capability'] = array();
			if( is_string($ccEInfo) ){
				$tmp_cce_info['class_name'] = $ccEInfo;
			}elseif( is_array($ccEInfo) || is_object($ccEInfo) ){
				$objCcEInfo = json_decode( json_encode($ccEInfo) );
				$tmp_cce_info['class_name'] = $ccEInfo->class_name ?? '';
				if( is_string( $ccEInfo->capability ?? null ) ){
					array_push($tmp_cce_info['capability'], $ccEInfo->capability);
				}elseif( is_array( $ccEInfo->capability ?? null ) ){
					$tmp_cce_info['capability'] = array_merge($tmp_cce_info['capability'], $ccEInfo->capability);
				}
			}
			$tmp_cce_info['client_initialize_function'] = null;

			$cceObj = $this->get($cce_id);
			if( !$cceObj ){
				continue;
			}
			if( is_callable( array($cceObj, 'get_label') ) ){
				$tmp_cce_info['label'] = $cceObj->get_label();
			}
			if( is_callable( array($cceObj, 'get_client_initialize_function') ) ){
				$tmp_cce_info['client_initialize_function'] = $cceObj->get_client_initialize_function();
			}

			$rtn[$cce_id] = $tmp_cce_info;
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
		$capability = array();
		if( is_string($ccEInfo) ){
			$className = $ccEInfo;
		}elseif( is_array($ccEInfo) || is_object($ccEInfo) ){
			$objCcEInfo = json_decode( json_encode($ccEInfo) );
			$className = $objCcEInfo->class_name;
			if( is_string( $ccEInfo->capability ?? null ) ){
				array_push($capability, $ccEInfo->capability);
			}elseif( is_array( $ccEInfo->capability ?? null ) ){
				$capability = array_merge($capability, $ccEInfo->capability);
			}
		}
		if( !strlen($className ?? '') ){
			return false;
		}

		// 権限をチェック
		if( is_object($this->px->authorizer) && count($capability) ){
			foreach($capability as $capability_row){
				if (!$this->px->authorizer->is_authorized($capability_row)) {
					return false;
				}
			}
		}

		// オプションがあるなら、取り出す
		$className = preg_replace( '/^\\\\*/', '\\', $className );
		$option_value = null;
		preg_match( '/^(.*?)(?:\\((.*)\\))?$/s', $className, $matched );
		if(array_key_exists( 1, $matched )){
			$className = @$matched[1];
		}
		if(array_key_exists( 2, $matched )){
			$option_value = @$matched[2];
		}
		unset($matched);
		if( strlen( trim(''.$option_value) ) ){
			$option_value = json_decode( $option_value );
		}else{
			$option_value = null;
		}

		if( !class_exists($className) ){
			return false;
		}


		// $cceAgent
		$cceAgent = new cceAgent($cce_id, $this->px, $this->main);

		$rtn = new $className( $this->px, $option_value, $cceAgent );

		return $rtn;
	}

	/**
	 * PX Command を実行する
	 */
	public function execute_px_command( $ary_px_command ){
		$rtn = array(
			'result' => true,
			'message' => 'OK',
		);
		$cce_id = null;
		$subcommand = null;

		if( !count($ary_px_command) ){
			// 拡張機能のIDがない場合、
			// 拡張機能の一覧を返す。
			$rtn['list'] = $this->get_list();
			return $rtn;
		}
		if( array_key_exists(0, $ary_px_command) && strlen($ary_px_command[0] ?? '') ){
			$cce_id = $ary_px_command[0];
		}
		if( array_key_exists(1, $ary_px_command) && strlen($ary_px_command[1] ?? '') ){
			$subcommand = $ary_px_command[1];
		}

		$ccExt = $this->get($cce_id);
		if( !$ccExt ){
			return array(
				'result' => false,
				'message' => 'Custom Console Extension "'.$cce_id.'" is NOT available.',
			);
		}
		if( strlen($subcommand ?? '') ){
			switch( $subcommand ){
				case 'gpi':
					if( !is_callable( array($ccExt, 'gpi') ) ){
						return array(
							'result' => false,
							'message' => 'Custom Console Extension: `gpi()` is NOT callable.',
						);
					}
					$request = $this->px->req()->get_param('request');
					$request = json_decode($request);
					$rtn['response'] = $ccExt->gpi( $request );
					return $rtn;
					break;

				case 'client_resources':
					if( !is_callable( array($ccExt, 'get_client_resource_base_dir') ) ){
						return array(
							'result' => false,
							'message' => 'Custom Console Extension: `get_client_resource_base_dir()` is NOT callable.',
						);
					}
					if( !is_callable( array($ccExt, 'get_client_resource_list') ) ){
						return array(
							'result' => false,
							'message' => 'Custom Console Extension: `get_client_resource_list()` is NOT callable.',
						);
					}
					$realpath_base_dir = $ccExt->get_client_resource_base_dir();
					$client_resources = $ccExt->get_client_resource_list();

					$realpath_dist = null;
					if( $this->px->req()->is_cmd() ){
						// CLI
						if( $this->px->req()->get_param('dist') ){
							$realpath_dist = $this->px->req()->get_param('dist');
						}
					}else{
						$realpath_dist = $this->px->fs()->normalize_path($this->px->fs()->get_realpath( $this->px->realpath_plugin_files('/').'../__console_resources/cce/'.urlencode($cce_id).'/' ));
					}
					if( strlen(''.$realpath_dist) ){
						$realpath_dist = $this->px->fs()->get_realpath($realpath_dist.'/');
						$this->px->fs()->copy_r($realpath_base_dir, $realpath_dist);
					}
					$resources = array();
					foreach($client_resources as $key=>$row){
						$resources[$key] = array();
						if( $key == 'js' ){
							$realpath_cceAgent = __DIR__.'/Px2dthelperCceAgent.js';
							if( !strlen(''.$realpath_dist) ){
								array_push($resources[$key], $realpath_cceAgent);
							}else{
								$this->px->fs()->copy($realpath_cceAgent, $realpath_dist.'Px2dthelperCceAgent.js');
								array_push($resources[$key], 'Px2dthelperCceAgent.js');
							}
						}
						foreach($row as $path){
							if( !strlen(''.$realpath_dist) ){
								$path = realpath($realpath_base_dir.'/'.$path);
							}
							array_push($resources[$key], $path);
						}
					}

					$rtn['resources'] = $resources;

					$rtn['path_base'] = null;
					if( !$this->px->req()->is_cmd() ){
						$rtn['path_base'] = $this->px->fs()->normalize_path( $this->px->fs()->get_realpath( $this->px->path_plugin_files('/').'../__console_resources/cce/'.urlencode($cce_id).'/', '/' ) );
					}

					return $rtn;
					break;

			}
		}

		// $cce_id 単体の情報を返す
		$list = $this->get_list();
		if( array_key_exists($cce_id, $list) ){
			$rtn['info'] = $list[$cce_id];
			return $rtn;
		}

		return array(
			'result' => false,
			'message' => 'Custom Console Extension: Unavailable command.',
		);
	}

}
