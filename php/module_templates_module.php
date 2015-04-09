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
	 * 最上位モジュール(サブモジュールにセットされる)
	 */
	private $modTop = null;

	/**
	 * sub module name (サブモジュールにセットされる)
	 */
	private $sub_mod_name = array();

	/**
	 * constructor
	 */
	public function __construct( $px, $main, $mod_id, $mod_path, $options = null ){
		$this->px = $px;
		$this->main = $main;

		$this->px2dtconf = $this->main->get_px2dtconfig();
		$this->modTpls = $this->main->module_templates();

		$this->mod_id = $mod_id;

		if( strlen( $mod_path ) && is_dir( $mod_path ) ){
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
		}
		$this->sub_modules = [];
		$this->fields = array();

		if( is_object( $options ) ){
			$this->modTop = $options->modTop;
			$this->template = $options->src;
			$this->sub_mod_name = $options->subModName;
		}else{
			$this->modTop = $this;
		}

		$this->parse( $this->template );
	}

	/**
	 * モジュールをパースする
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
				$this->fields[$field->loop->name] = $field->loop;
				$this->fields[$field->loop->name]->fieldType = 'loop';

				$tmpSearchResult = $this->search_end_tag( $src, 'loop' );
				if( !is_array( $this->sub_modules ) ){
					$this->sub_modules = [];
				}
				@$this->modTop->sub_modules[@$field->loop->name] = new module_templates_module(
					$this->px,
					$this->main,
					$this->get_id(),
					null,
					json_decode( json_encode([
						"src"=>$tmpSearchResult->content,
						"subModName"=>$field->loop->name,
						"modTop"=>$this->modTop
					]) )
				);
				$src = $tmpSearchResult->nextSrc;

			}elseif( @$field->if ){

			}elseif( @$field->echo ){

			}

			continue;
		}

		return true;
	}

	/* 閉じタグを探す */
	private function search_end_tag( $src, $fieldType ){
		$rtn = json_decode('{
			"content": "",
			"nextSrc": ""
		}');
		$rtn->nextSrc = $src;

		$depth = 0;
		while( 1 ){
			if( !preg_match( '/^(.*?)\\{\\&(.*?)\\&\\}(.*)$/is', $rtn->nextSrc, $matched ) ){
				break;
			}
			$rtn->content .= $matched[1];
			$fieldSrc = trim($matched[2]);
			$field = json_decode( $fieldSrc );
			$rtn->nextSrc = $matched[3];

			if( $field == 'end'.$fieldType ){
				if( $depth ){
					$depth --;
					$rtn->content .= '{&'.$fieldSrc.'&}';
					continue;
				}
				return $rtn;
			}else if( @$field->{$fieldType} ){
				$depth ++;
				$rtn->content .= '{&'.$fieldSrc.'&}';
				continue;
			}else{
				$rtn->content .= '{&'.$fieldSrc.'&}';
				continue;
			}
		}
		return $rtn;
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
		return @$this->sub_modules[ $sub_module_name ];
	}

	/**
	 * モジュールにデータをバインドして返す
	 */
	public function bind( $data, $mode = 'finalize' ){
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
				$tmpVal = '';

				$tmpVal .= $this->main->get_field_definition( $field->input->type )->bind( $data->fields->{@$field->input->name}, $mode );

				if( !@$field->input->hidden ){//← "hidden": true だったら、非表示(=出力しない)
					$rtn .= $tmpVal;
				}
				@$this->nameSpace->vars[@$field->input->name] = [
					"fieldType"=>"input",
					"type"=>$field->input->type,
					"val"=>"tmpVal"
				];


			}elseif( @$field->module ){
				foreach( $data->fields->{$field->module->name} as $tmp_data ){
					// $rtn .= $tmp_data;
				}

			}elseif( @$field->loop ){
				$tmpSearchResult = $this->search_end_tag( $src, 'loop' );
				foreach( $data->fields->{$field->loop->name} as $tmp_data ){
					$rtn .= $this->get_sub_module( $field->loop->name )->bind( $tmp_data );
				}
				$src = $tmpSearchResult->nextSrc;

			}elseif( @$field->if ){
				// if field
				// is_set に指定されたフィールドに値があったら、という評価ロジックを取り急ぎ実装。
				// もうちょっとマシな条件の書き方がありそうな気がするが、あとで考える。
				$tmpSearchResult = $this->search_end_tag( $src, 'if' );
				$src = '';
				if( @$this->nameSpace->vars[@$field->if->is_set] && strlen(trim(@$this->nameSpace->vars[@$field->if->is_set]->val)) ){
					$src .= $tmpSearchResult->content;
				}
				$src .= $tmpSearchResult->nextSrc;

			}elseif( @$field->echo ){
				// echo field
				if( @$this->nameSpace->vars[@$field->echo->ref] && @$this->nameSpace->vars[@$field->echo->ref]->val ){
					$rtn .= $this->nameSpace->vars[$field->echo->ref]->val;
				}

			}

			continue;
		}
		return $rtn;
	}

}
