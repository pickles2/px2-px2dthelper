<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper;

/**
 * fncs/config/parser.php
 */
class fncs_config_parser{

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
	}

	/**
	 * configファイルを解析する
	 *
	 * @return array 解析結果
	 */
	public function parse(){
		$realpath_homedir = $this->px->get_realpath_homedir();
		if( !is_dir($realpath_homedir) ){
			return array(
				'result'=>false,
				'message'=>'Home Directory is NOT defined.',
				'values'=>array(),
				'symbols'=>array(),
			);
		}
		if( is_file($realpath_homedir.'config.json') ){
			return $this->parse_json($realpath_homedir.'config.json');
		}
		if( is_file($realpath_homedir.'config.php') ){
			return $this->parse_php($realpath_homedir.'config.php');
		}
		return array(
			'result'=>false,
			'message'=>'Unknown problem.',
			'values'=>array(),
			'symbols'=>array(),
		);
	}

	/**
	 * configファイルを更新する
	 *
	 * @param array $set_data 更新するデータのリスト(連想配列)
	 * @return array 実行結果
	 */
	public function update($set_data = null){
		$set_data = json_decode( json_encode($set_data), true );
		if( is_array($set_data) ){
			if( !array_key_exists('values', $set_data) ){
				$set_data['values'] = array();
			}
			if( !array_key_exists('symbols', $set_data) ){
				$set_data['symbols'] = array();
			}
		}
		$realpath_homedir = $this->px->get_realpath_homedir();
		if( !is_dir($realpath_homedir) ){
			return array(
				'result'=>false,
				'message'=>'Home Directory is NOT defined.',
				'values'=>array(),
				'symbols'=>array(),
			);
		}
		if( is_file($realpath_homedir.'config.json') ){
			return $this->parse_json($realpath_homedir.'config.json', $set_data);
		}
		if( is_file($realpath_homedir.'config.php') ){
			return $this->parse_php($realpath_homedir.'config.php', $set_data);
		}
		return array(
			'result'=>false,
			'message'=>'Unknown problem.',
			'values'=>array(),
			'symbols'=>array(),
		);
	}

	/**
	 * config.json を解析する
	 *
	 * @param string $path_json `config.json` のパス
	 * @param array $set_data 更新するデータのリスト(連想配列)
	 * @return array 解析結果
	 */
	private function parse_json( $path_json, $set_data = null ){
		$rtn = array(
			'result'=>false,
			'message'=>'config.json is NOT supported.',
			'values'=>array(),
			'symbols'=>array(),
		);
		// $rtn['values'] = json_decode( file_get_contents( $path_json ) );
		return $rtn;
	}

	/**
	 * config.php を解析する
	 *
	 * @param string $path_php `config.php` のパス
	 * @param array $set_data 更新するデータのリスト(連想配列)
	 * @return array 解析結果
	 */
	private function parse_php( $path_php, $set_data = null ){
		$rtn = array(
			'result'=>true,
			'message'=>'OK',
			'values'=>array(),
			'symbols'=>array(),
		);
		$src_config_php = file_get_contents( $path_php );

		$patterns = array(
			'name' => array(
				'value_div' => 'values',
				'preg_pattern' => '/\$conf\-\>name\s*\=\s*(?:(\'|\")([a-zA-Z0-9\-\_\.\:\;\@\#\$\%\^\&\*\+\=\s]+)\1|null|NULL)\s*\;/s',
				'parse' => function( $pattern, $src_config_php ){
					$rtn = array(
						'matched' => true,
						'value' => null,
					);
					if( preg_match($pattern['preg_pattern'], $src_config_php, $matched) ){
						if( strlen($matched[1]) ){
							$rtn['value'] = $matched[2];
							return $rtn;
						}
						return $rtn;
					}
					return array(
						'matched' => false,
					);
				},
				'replace' => function( $pattern, $src_config_php, $val ){
					$src_config_php = preg_replace(
						$pattern['preg_pattern'],
						'$conf->name = '.var_export($val,true).';',
						$src_config_php
					);
					return $src_config_php;
				},
				'validator' => function( $val ){
					if(!preg_match('/^[a-zA-Z0-9\-\_\.\:\;\@\#\$\%\^\&\*\+\=\s]+$/s', $val)){
						return false;
					}
					return true;
				},
			),
			'scheme' => array(
				'value_div' => 'values',
				'preg_pattern' => '/\$conf\-\>scheme\s*\=\s*(?:(\'|\")([a-zA-Z0-9\-\_\.\:]+)\1|(null|NULL))\s*\;/s',
				'parse' => function( $pattern, $src_config_php ){
					$rtn = array(
						'matched' => true,
						'value' => null,
					);
					if( preg_match($pattern['preg_pattern'], $src_config_php, $matched) ){
						if( strlen($matched[1]) ){
							$rtn['value'] = $matched[2];
							return $rtn;
						}
						return $rtn;
					}
					return array(
						'matched' => false,
					);
				},
				'replace' => function( $pattern, $src_config_php, $val ){
					$src_config_php = preg_replace(
						$pattern['preg_pattern'],
						'$conf->scheme = '.var_export($val,true).';',
						$src_config_php
					);
					return $src_config_php;
				},
				'validator' => function( $val ){
					if( is_null($val) ){
						return false; // not nullable
					}
					if(!preg_match('/^(?:http|https)$/s', $val)){
						return false;
					}
					return true;
				},
			),
			'domain' => array(
				'value_div' => 'values',
				'preg_pattern' => '/\$conf\-\>domain\s*\=\s*(?:(\'|\")([a-zA-Z0-9\-\_\.\:]+)\1|(null|NULL))\s*\;/s',
				'parse' => function( $pattern, $src_config_php ){
					$rtn = array(
						'matched' => true,
						'value' => null,
					);
					if( preg_match($pattern['preg_pattern'], $src_config_php, $matched) ){
						if( strlen($matched[1]) ){
							$rtn['value'] = $matched[2];
							return $rtn;
						}
						return $rtn;
					}
					return array(
						'matched' => false,
					);
				},
				'replace' => function( $pattern, $src_config_php, $val ){
					$src_config_php = preg_replace(
						$pattern['preg_pattern'],
						'$conf->domain = '.var_export($val,true).';',
						$src_config_php
					);
					return $src_config_php;
				},
				'validator' => function( $val ){
					if( is_null($val) ){
						return true; // nullable
					}
					if(!preg_match('/^[a-zA-Z0-9\-\_\.]+(?:\:[1-9][0-9]*)?$/s', $val)){
						return false;
					}
					return true;
				},
			),
			'theme_id' => array(
				'value_div' => 'symbols',
				'preg_pattern' => '/(\'|\")default_theme_id\1\s*\=\>\s*(\'|\")([a-zA-Z0-9\-\_]+)\2/s',
				'parse' => function( $pattern, $src_config_php ){
					$rtn = array(
						'matched' => true,
						'value' => null,
					);
					if( preg_match($pattern['preg_pattern'], $src_config_php, $matched) ){
						$rtn['value'] = $matched[3];
						return $rtn;
					}
					return array(
						'matched' => false,
					);
				},
				'replace' => function( $pattern, $src_config_php, $val ){
					$src_config_php = preg_replace(
						$pattern['preg_pattern'],
						'$1default_theme_id$1 => '.var_export($val,true).'',
						$src_config_php
					);
					return $src_config_php;
				},
				'validator' => function( $val ){
					if(!preg_match('/^[a-zA-Z0-9\-\_]+$/s', $val)){
						return false;
					}
					return true;
				},
			),
		);

		foreach($patterns as $name=>$pattern){
			$matched = $pattern['parse']($pattern, $src_config_php);
			if( $matched['matched'] ){
				$rtn[$pattern['value_div']][$name] = $matched['value'];

				if( is_array($set_data) && array_key_exists($name, $set_data[$pattern['value_div']]) ){
					if( $pattern['validator']( $set_data[$pattern['value_div']][$name] ) ){
						$src_config_php = $pattern['replace']($pattern, $src_config_php, $set_data[$pattern['value_div']][$name]);

						$matched = $pattern['parse']($pattern, $src_config_php);
						if( $matched['matched'] ){
							$rtn[$pattern['value_div']][$name] = $matched['value'];
						}

					}else{
						$rtn['result'] = false;
						$rtn['message'] = 'Some options contain invalid values.';
					}
				}
			}
		}

		if( !is_null( $set_data ) && $rtn['result'] ){
			$this->px->fs()->save_file( $path_php, $src_config_php );
		}

		return $rtn;
	}
 }
