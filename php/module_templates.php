<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper;

/**
 * module_templates.php
 */
class module_templates{

	/**
	 * Picklesオブジェクト
	 */
	private $px;

	/**
	 * px2dthelper main
	 */
	private $main;

	/**
	 * Pickles2 Desktop Tool config
	 */
	private $px2dtconf;

	/**
	 * module list
	 */
	private $mod_templates;

	/**
	 * package info list
	 */
	private $package_infos;

	/**
	 * constructor
	 * 
	 * @param object $px $pxオブジェクト
	 * @param object $main main.php のインスタンス
	 */
	public function __construct( $px, $main ){
		$this->px = $px;
		$this->main = $main;

		$this->px2dtconf = $this->main->get_px2dtconfig();

		require_once(__DIR__.'/module_templates_module.php');

		$this->mod_templates = [];
		$this->mod_templates['_sys/root'] = new module_templates_module(
			$this->px ,
			$this->main ,
			'_sys/root' ,
			null
		);
		$this->mod_templates['_sys/unknown'] = new module_templates_module(
			$this->px ,
			$this->main ,
			'_sys/unknown' ,
			null
		);
		$this->mod_templates['_sys/html'] = new module_templates_module(
			$this->px ,
			$this->main ,
			'_sys/html' ,
			null
		);

		$this->package_infos = [];
		$this->package_readmes = [];

		foreach( $this->px2dtconf->paths_module_template as $package_id=>$row ){
			$categories = $this->px->fs()->ls( $row );
			$this->package_infos[$package_id] = new \stdClass();
			if( $this->px->fs()->is_file( $row.'/info.json' ) ){
				$this->package_infos[$package_id]->info = json_decode( $this->px->fs()->read_file( $row.'/info.json' ) );
			}
			if( $this->px->fs()->is_file( $row.'/README.html' ) ){
				$this->package_infos[$package_id]->readme = $this->px->fs()->read_file( $row.'/README.html' );
			}elseif( $this->px->fs()->is_file( $row.'/README.md' ) ){
				$this->package_infos[$package_id]->readme = \Michelf\MarkdownExtra::defaultTransform( $this->px->fs()->read_file( $row.'/README.md' ) );
			}

			$categories = $this->sort( $categories, @$this->package_infos[$package_id]->info->sort );

			foreach( $categories as $category_id ){
				if( !$this->px->fs()->is_dir( $row.'/'.$category_id ) ){
					continue;
				}
				$this->package_infos[$package_id.'/'.$category_id] = new \stdClass();
				if( $this->px->fs()->is_file( $row.'/'.$category_id.'/info.json' ) ){
					$this->package_infos[$package_id.'/'.$category_id]->info = json_decode( $this->px->fs()->read_file( $row.'/'.$category_id.'/info.json' ) );
				}
				if( $this->px->fs()->is_file( $row.'/'.$category_id.'/README.html' ) ){
					$this->package_infos[$package_id.'/'.$category_id]->readme = $this->px->fs()->read_file( $row.'/'.$category_id.'/README.html' );
				}elseif( $this->px->fs()->is_file( $row.'/'.$category_id.'/README.md' ) ){
					$this->package_infos[$package_id.'/'.$category_id]->readme = \Michelf\MarkdownExtra::defaultTransform( $this->px->fs()->read_file( $row.'/'.$category_id.'/README.md' ) );
				}

				$module_names = $this->px->fs()->ls( $row.'/'.$category_id );

				$module_names = $this->sort( $module_names, @$this->package_infos[$package_id.'/'.$category_id]->info->sort );

				foreach( $module_names as $module_name ){
					if( !$this->px->fs()->is_file( $row.'/'.$category_id.'/'.$module_name.'/template.html' ) ){
						continue;
					}
					$this->mod_templates[$package_id.':'.$category_id.'/'.$module_name] = new module_templates_module(
						$this->px ,
						$this->main ,
						$package_id.':'.$category_id.'/'.$module_name ,
						$row.'/'.$category_id.'/'.$module_name.'/'
					);

				}

			}
		}

	}

	/**
	 * モジュールを並べ替える
	 */
	private function sort( $ary, $sortGuide ){
		sort($ary);
		$rtn = array();
		if( is_array($sortGuide) ){
			foreach( $sortGuide as $row ){
				array_push( $rtn, $row );
				$res = array_search( $row, $ary );
				if( is_int($res) ){
					unset($ary[$res]);
				}
			}
		}
		foreach( $ary as $row ){
			array_push( $rtn, $row );
		}
		return $rtn;
	}

	/**
	 * 指定のモジュールにデータをバインドする
	 */
	public function bind( $modId, $data ){
		$mod = $this->mod_templates[ $modId ];
		return $mod->bind( $data );
	}


	/**
	 * 指定のモジュールオブジェクトを取得する
	 */
	public function get( $modId, $subModName = null ){
		$mod = @$this->mod_templates[ $modId ];
		if( strlen( $subModName ) ){
			$mod = $mod->get_sub_module( $subModName );
		}
		return $mod;
	}

	/**
	 * モジュールの一覧を取得する
	 */
	public function get_module_list(){
		return array_keys( @$this->mod_templates );
	}

	/**
	 * パッケージ情報を取得する
	 * @param string $packageId Package ID
	 * @return object package info
	 */
	public function get_package_info( $packageId ){
		return @$this->package_infos[$packageId];
	}

	/**
	 * カテゴリ情報を取得する
	 * @param string $packageId Package ID
	 * @param string $categoryId Category ID
	 * @return object category info
	 */
	public function get_category_info( $packageId, $categoryId ){
		return @$this->package_infos[$packageId.'/'.$categoryId];
	}

}
