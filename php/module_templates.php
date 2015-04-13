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

		foreach( $this->px2dtconf->paths_module_template as $package_id=>$row ){
			$categories = $this->px->fs()->ls( $row );
			foreach( $categories as $category_id ){
				$module_names = $this->px->fs()->ls( $row.'/'.$category_id );
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
		$mod = $this->mod_templates[ $modId ];
		if( strlen( $subModName ) ){
			$mod = $mod->get_sub_module( $subModName );
		}
		return $mod;
	}

}
