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

		$init_options = array(
			'appMode' => 'web', // 'web' or 'desktop'. default to 'web'
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
						if( $init_options['paths_module_template'][$infoJson->sort[$idx]] ){
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
					if( $init_options['paths_module_template'][$fileList[$idx]] ){
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
		// customFields
		// TODO: 未実装
		$init_options['customFields'] = array();
		// // px2ce が拡張するフィールド
		// customFields.table = require('broccoli-field-table').get({'php': nodePhpBinOptions});

		// // 呼び出し元アプリが拡張するフィールド
		// for( var idx in px2ce.options.customFields ){
		// 	customFields[idx] = px2ce.options.customFields[idx];
		// }

		// // プロジェクトが拡張するフィールド
		// var confCustomFields = {};
		// try {
		// 	confCustomFields = @$this->px->conf()->plugins->px2dt->guieditor.custom_fields;
		// 	for( var fieldName in confCustomFields ){
		// 		try {
		// 			if( confCustomFields[fieldName].backend.require ){
		// 				var path_backend_field = require('path').resolve(px2ce.entryScript, '..', confCustomFields[fieldName].backend.require);
		// 				customFields[fieldName] = require( path_backend_field );
		// 			}else{
		// 				console.error( 'FAILED to load custom field: ' + fieldName + ' (backend);' );
		// 				console.error( 'unknown type' );
		// 			}
		// 		} catch (e) {
		// 			console.error( 'FAILED to load custom field: ' + fieldName + ' (backend);' );
		// 			console.error(e);
		// 		}
		// 	}
		// } catch (e) {
		// }

		// --------------------------------------
		// bindTemplate
		// TODO: テーマ編集の場合のコードは異なる
		if( $this->target_mode == 'theme_layout' ){
			$init_options['bindTemplate'] = function($htmls){
				// var fin = '';
				// for( var bowlId in htmls ){
				// 	if( bowlId == 'main' ){
				// 		fin += htmls['main'];
				// 	}else{
				// 		fin += "\n";
				// 		fin += "\n";
				// 		fin += '<'.'?php ob_start(); ?'.'>'+"\n";
				// 		fin += (utils79.toStr(htmls[bowlId]).length ? htmls[bowlId]+"\n" : '');
				// 		fin += '<'.'?php $px->bowl()->send( ob_get_clean(), '+JSON.stringify(bowlId)+' ); ?'.'>'+"\n";
				// 		fin += "\n";
				// 	}
				// }
				// var template = '<'.'%- body %'.'>';
				// var pathThemeLayout = _this.documentRoot+$this->theme_id+'/broccoli_module_packages/_layout.html';
				// if(is_file(pathThemeLayout)){
				// 	template = fs.readFileSync( pathThemeLayout ).toString();
				// }else{
				// 	template = fs.readFileSync( __dirname+'/tpls/broccoli_theme_layout.html' ).toString();
				// }
				// fin = ejs.render(template, {'body': fin}, {delimiter: '%'});

				// var baseDir = _this.documentRoot+$this->theme_id+'/theme_files/';
				// fsx.ensureDirSync( baseDir );
				// _this.getModuleCssJsSrc($this->theme_id, function(CssJs){
				// 	fs.writeFileSync(baseDir+'modules.css', CssJs.css);
				// 	fs.writeFileSync(baseDir+'modules.js', CssJs.js);
				// 	callback(fin);
				// });

				// return;
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

}
