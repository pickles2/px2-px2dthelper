<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper;

/**
 * broccoli/apis.php
 */
class broccoli_apis{

	/** $px object */
	private $px;

	/** px2dthelper object */
	private $main;

	/**
	 * 編集対象のモード
	 * 'page_content' (default) or 'theme_layout'
	 */
	private $target_mode = 'page_content';

	/**
	 * 編集対象のテーマID
	 * $target_mode が 'theme_layout' の場合にセットされる。
	 */
	private $theme_id = null;

	/**
	 * constructor
	 * @param object $px Picklesオブジェクト
	 * @param object $main px2dthelper オブジェクト
	 */
	public function __construct($px, $main){
		$this->px = $px;
		$this->main = $main;
	}

	/**
	 * PXコマンドを実行する
	 * @param string $px_command_2 PXコマンドの第3引数(添字 2 に該当)
	 * @return string       生成されたHTMLソース
	 */
	public function execute_px_command($px_command_2){
		$broccoli = $this->create_broccoli();
		switch($px_command_2){
			case 'gpi':
				$rtn = $broccoli->gpi(
					$this->px->req()->get_param('api'),
					json_decode($this->px->req()->get_param('options'), true)
				);
				return $rtn;
				break;
		}
		return false;
	} // execute_px_command()

	/**
	 * $broccoli オブジェクトを生成する
	 * @return object $broccoli
	 */
	private function create_broccoli(){
		$current_page_info = $this->px->site()->get_current_page_info();
		$broccoli = new \broccoliHtmlEditor\broccoliHtmlEditor();
		$appMode = $this->px->req()->get_param('appMode');
		if( !$appMode ){
			$appMode = 'web';
		}

		$init_options = array(
			'appMode' => $appMode, // 'web' or 'desktop'. default to 'web'
			'paths_module_template' => array() ,
			'customFields' => array() ,
			'documentRoot' => $this->px->get_realpath_docroot(),
			'pathHtml' => $this->px->req()->get_request_file_path(),
			'pathResourceDir' => $this->px->path_files().'resources/',
			'realpathDataDir' => $this->px->realpath_files().'guieditor.ignore/',
			'contents_bowl_name_by' => @$this->px->conf()->plugins->px2dt->contents_bowl_name_by,
			'bindTemplate' => null,
			'log' => function($msg){
				$this->px->error($msg);
			}
		);

		// --------------------------------------
		// モジュールパッケージを読み込む
		$init_options['paths_module_template'] = array();
		if( $this->target_mode == 'page_content' ){
			// コンテンツ編集時のみ。
			// テーマ編集では paths_module_template を読み込まない。
			$paths_module_template = @$this->px->conf()->plugins->px2dt->paths_module_template;
			if( is_array($paths_module_template) ){
				foreach( @$this->px->conf()->plugins->px2dt->paths_module_template as $idx=>$path_module_template){
					$init_options['paths_module_template'][$idx] = $this->px->fs()->normalize_path($this->px->fs()->get_realpath($path_module_template.'/'));
				}
			}
		}

		// モジュールフォルダからロード
		$pathModuleDir = @$this->px->conf()->plugins->px2dt->path_module_templates_dir;
		if( $this->target_mode == 'theme_layout' ){
			// テーマ編集では `broccoli_module_packages` をロードする。
			$pathModuleDir = $this->px->realpath_homedir().$this->theme_id.'/broccoli_module_packages/';
		}
		if( is_string($pathModuleDir) ){
			$pathModuleDir = $this->px->fs()->normalize_path( $this->px->fs()->get_realpath( $pathModuleDir.'/' ) );
			if( is_dir($pathModuleDir) ){
				// info.json を読み込み
				$infoJson = json_decode('{}');
				if( is_file($pathModuleDir.'/info.json') ){
					$srcInfoJson = file_get_contents($pathModuleDir.'/info.json');
					$infoJson = json_decode($srcInfoJson);
				}
				if( is_array(@$infoJson->sort) ){
					// 並び順の指定がある場合
					foreach( $infoJson->sort as $idx=>$packageId ){
						if( @$init_options['paths_module_template'][$infoJson->sort[$idx]] ){
							// 既に登録済みのパッケージIDは上書きしない
							// (= paths_module_template の設定を優先)
							continue;
						}
						if( is_dir($pathModuleDir.$infoJson->sort[$idx]) ){
							$init_options['paths_module_template'][$infoJson->sort[$idx]] = $this->px->fs()->normalize_path( $this->px->fs()->get_realpath( $pathModuleDir.$infoJson->sort[$idx].'/' ) );
						}
					}
				}

				// モジュールディレクトリ中のパッケージをスキャンして一覧に追加
				$fileList = $this->px->fs()->ls($pathModuleDir);
				sort($fileList); // sort
				foreach( $fileList as $idx=>$packageId ){
					if( @$init_options['paths_module_template'][$fileList[$idx]] ){
						// 既に登録済みのパッケージIDは上書きしない
						// (= paths_module_template の設定を優先)
						continue;
					}
					if( is_dir($pathModuleDir.$fileList[$idx]) ){
						$init_options['paths_module_template'][$fileList[$idx]] = $this->px->fs()->normalize_path( $this->px->fs()->get_realpath( $pathModuleDir.$fileList[$idx].'/' ) );
					}
				}
			}
		}
		// var_dump($init_options['paths_module_template']);

		// --------------------------------------
		// カスタムフィールドを読み込む
		$init_options['customFields'] = array();

		// px2dthelper が拡張するフィールド
		// $init_options['customFields']['table'] = 'broccoliHtmlEditor\\broccoliFieldTable'; // TODO: 未実装

		// // 呼び出し元アプリが拡張するフィールド (px2dthelperへの移植に伴い廃止)
		// foreach( var idx in px2ce.options.customFields ){
		// 	customFields[idx] = px2ce.options.customFields[idx];
		// }

		// プロジェクトが拡張するフィールド
		$confCustomFields = @$this->px->conf()->plugins->px2dt->guieditor->custom_fields;
		if( is_array($confCustomFields) ){
			foreach( $confCustomFields as $fieldName=>$field){
				if( $confCustomFields[$fieldName]->backend->require ){
					$path_backend_field = $this->px->fs()->normalize_path( $this->px->fs()->get_realpath( $confCustomFields[$fieldName]->backend->require ) );
					require_once( $path_backend_field );
				}
				if( $confCustomFields[$fieldName]->backend->class ){
					$init_options['customFields'] = $confCustomFields[$fieldName]->backend->class;
				}
			}
		}
		// var_dump($init_options['customFields']);

		// --------------------------------------
		// bindTemplate
		if( $this->target_mode == 'theme_layout' ){
			$init_options['bindTemplate'] = function($htmls){
				$fin = '';
				foreach( $htmls as $bowlId=>$html ){
					if( $bowlId == 'main' ){
						$fin .= $html;
					}else{
						$fin .= "\n";
						$fin .= "\n";
						$fin .= '<'.'?php ob_start(); ?'.'>'."\n";
						$fin .= (strlen($html) ? $html."\n" : '');
						$fin .= '<'.'?php $px->bowl()->send( ob_get_clean(), '.json_encode($bowlId).' ); ?'.'>'."\n";
						$fin .= "\n";
					}
				}
				$template = '<'.'%- body %'.'>';
				$pathThemeLayout = $this->px->realpath_homedir().$this->theme_id.'/broccoli_module_packages/_layout.html';
				if(is_file($pathThemeLayout)){
					$template = file_get_contents( $pathThemeLayout );
				}else{
					$template = file_get_contents( __DIR__.'/tpls/broccoli_theme_layout.html' );
				}

				// TODO: PHP では ejs は使えない。Twigなどに置き換える
				// $fin = $ejs->render($template, {'body': $fin}, array('delimiter'=>'%'));

				$baseDir = $this->px->realpath_homedir().$this->theme_id.'/theme_files/';
				// fsx.ensureDirSync( $baseDir ); // TODO: このメソッドなんだっけ？
				$CssJs = $this->get_module_css_js_src($this->theme_id);
				$this->px->fs()->save_file($baseDir.'modules.css', $CssJs['css']);
				$this->px->fs()->save_file($baseDir.'modules.js', $CssJs['js']);

				return $fin;
			};
		}else{
			$init_options['bindTemplate'] = function($htmls){
				$fin = '';
				foreach( $htmls as $bowlId=>$html ){
					if( $bowlId == 'main' ){
						$fin .= $html;
					}else{
						$fin .= "\n";
						$fin .= "\n";
						$fin .= '<?php ob_start(); ?>'."\n";
						$fin .= (@strlen($html) ? $html."\n" : '');
						$fin .= '<?php $px->bowl()->send( ob_get_clean(), '.json_encode($bowlId).' ); ?>'."\n";
						$fin .= "\n";
					}
				}
				return $fin;
			};
		}

		// var_dump($init_options);
		$broccoli->init($init_options);
		return $broccoli;
	}


	/**
	 * モジュールのCSS,JSソースを取得する
	 */
	private function get_module_css_js_src($theme_id){
		// TODO: 未実装
		// theme_id = theme_id || '';
		// var rtn = {
		// 	'css': '',
		// 	'js': ''
		// };
		// _this.px2proj.query('/?PX=px2dthelper.document_modules.build_css&theme_id='+encodeURIComponent(theme_id), {
		// 	"output": "json",
		// 	"complete": function(data, code){
		// 		// console.log(data, code);
		// 		rtn.css += data;

		// 		_this.px2proj.query('/?PX=px2dthelper.document_modules.build_js&theme_id='+encodeURIComponent(theme_id), {
		// 			"output": "json",
		// 			"complete": function(data, code){
		// 				// console.log(data, code);
		// 				rtn.js += data;

		// 				callback(rtn);
		// 			}
		// 		});
		// 	}
		// });
	} // get_module_css_js_src

}
