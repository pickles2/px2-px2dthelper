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
				'vars'=>array(),
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
			'vars'=>array(),
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
		$realpath_homedir = $this->px->get_realpath_homedir();
		if( !is_dir($realpath_homedir) ){
			return array(
				'result'=>false,
				'message'=>'Home Directory is NOT defined.',
				'vars'=>array(),
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
			'vars'=>array(),
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
			'vars'=>array(),
		);
		// $rtn['vars'] = json_decode( file_get_contents( $path_json ) );
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
			'vars'=>array(),
		);
		$src_config_php = file_get_contents( $path_php );
		$patterns = array(
			'theme_id' => array(
				'preg_pattern' => '/(\'|\")default_theme_id\1\s*\=\>\s*(\'|\")([a-zA-Z0-9\-\_]+)\2/s',
				'index' => 3,
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
			if( preg_match($pattern['preg_pattern'], $src_config_php, $matched) ){
				$rtn['vars']['theme_id'] = $matched[$pattern['index']];

				if( is_array($set_data) && array_key_exists($name, $set_data) ){
					if( $pattern['validator']( $set_data[$name] ) ){
						$src_config_php = $pattern['replace']($pattern, $src_config_php, $set_data[$name]);
						if( preg_match($pattern['preg_pattern'], $src_config_php, $matched) ){
							$rtn['vars']['theme_id'] = $matched[$pattern['index']];
						}
					}
				}
			}
		}

		if( !is_null( $set_data ) ){
			$this->px->fs()->save_file( $path_php, $src_config_php );
		}

		return $rtn;
	}
 }
