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
	 * README.md
	 */
	private $readme = '';

	/**
	 * 編集フィールド情報
	 */
	private $fields = array();

	/**
	 * sub modules
	 */
	private $sub_modules = array();

	/**
	 * モジュール個別の名前領域
	 */
	private $nameSpace = array();

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
			$this->info = json_decode( $this->px->fs()->read_file( $mod_path.'/info.json' ) );
		}
		if( $this->px->fs()->is_file( $mod_path.'/README.html' ) ){
			$this->readme = $this->px->fs()->read_file( $mod_path.'/README.html' );
		}elseif( $this->px->fs()->is_file( $mod_path.'/README.md' ) ){
			$this->readme = \Michelf\MarkdownExtra::defaultTransform( $this->px->fs()->read_file( $mod_path.'/README.md' ) );
		}
		$this->sub_modules = array();
		$this->fields = array();

		$this->parse( $this->template );
	}

	/**
	 * モジュールIDをパースする
	 */
	private function parse( $src ){
		while( 1 ){
			if( !preg_match( '/^(.*?)\\{\\&(.*?)\\&\\}(.*)$/si', $src, $matched ) ){
				break;
			}
			$field = json_decode(trim($matched[2]));
			$src = $matched[3];

			if( @$field->input ){
				$this->fields[$field->input->name] = $field->input;
				$this->fields[$field->input->name]->fieldType = 'input';

			}elseif( @$field->module ){
				$this->fields[$field->module->name] = $field->module;
				$this->fields[$field->module->name]->fieldType = 'module';

			}elseif( @$field->loop ){

			}elseif( @$field->if ){

			}elseif( @$field->echo ){

			}

			continue;
		}
		return true;
	}

	/**
	 * モジュールIDを取得する
	 */
	public function get_id(){
		return $this->mod_id;
	}

	/**
	 * info.json の内容を取得する
	 */
	public function get_info(){
		return $this->info;
	}

	/**
	 * README.md の内容を取得する
	 */
	public function get_readme(){
		return $this->readme;
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
		$src = $this->template;
		$rtn = '';
		while( 1 ){
			if( !preg_match( '/^(.*?)\\{\\&(.*?)\\&\\}(.*)$/si', $src, $matched ) ){
				$rtn .= $src;
				break;
			}
			$rtn .= $matched[1];
			$field = json_decode(trim($matched[2]));
			$src = $matched[3];

			if( @$field->input ){
				// input field
				$tmpVal = $data->fields->{@$field->input->name};
				if( !@$field->input->hidden ){//← "hidden": true だったら、非表示(=出力しない)
					$rtn .= $tmpVal;
				}
				@$this->nameSpace->vars[@$field->input->name] = [
					"fieldType"=>"input",
					"type"=>$field->input->type,
					"val"=>"tmpVal"
				];


			}elseif( @$field->module ){
				// $this->fields[$field->module->name] = $field->module;
				// $this->fields[$field->module->name]->fieldType = 'module';

			}elseif( @$field->loop ){

			}elseif( @$field->if ){

			}elseif( @$field->echo ){

			}

			continue;
		}
		return $rtn;
	}

}
