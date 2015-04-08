<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper;

/**
 * module_templates_module.php
 */
class module_templates_module{

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
	 * module templtes
	 */
	private $modTpls;

	/**
	 * module ID
	 */
	private $mod_id;

	/**
	 * template source
	 */
	private $template;

	/**
	 * module info
	 */
	private $info = array();

	/**
	 * 編集フィールド情報
	 */
	private $fields = array();

	/**
	 * sub modules
	 */
	private $sub_modules = array();

	/**
	 * constructor
	 */
	public function __construct( $px, $main, $mod_id, $mod_path ){
		$this->px = $px;
		$this->main = $main;

		$this->px2dtconf = $this->main->get_px2dtconfig();
		$this->modTpls = $this->main->module_templates();

		$this->mod_id = $mod_id;
		$this->template = $this->px->fs()->read_file( $mod_path.'/template.html' );
		$this->info = json_decode('{}');
		if( $this->px->fs()->is_file( $mod_path.'/info.json' ) ){
			$this->info = json_decode($this->px->fs()->read_file( $mod_path.'/info.json' ));
		}
		$this->sub_modules = array();
		$this->fields = array();

		$this->parse( $this->template );
	}

	/**
	 * モジュールIDをパースする
	 */
	private function parse( $src ){
		// UTODO: 未実装
		return true;
	}

	/**
	 * モジュールIDを取得する
	 */
	public function get_id(){
		return $this->mod_id;
	}

	/**
	 * モジュール名称を取得する
	 */
	public function get_name(){
		if( @strlen( $this->info->name ) ){
			return $this->info->name;
		}
		return $this->mod_id;
	}

	/**
	 * テンプレートソースをそのまま取得する
	 */
	public function get_template(){
		return $this->template;
	}

	/**
	 * サブモジュールを取得する
	 */
	public function get_sub_module( $sub_module_name ){
		return $this->sub_modules[ $sub_module_name ];
	}

	/**
	 * モジュールにデータをバインドして返す
	 */
	public function bind( $data ){
		// UTODO: 暫定的にそのまま返す
		return $this->template;
	}

}
