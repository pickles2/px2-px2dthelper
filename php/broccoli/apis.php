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

		// paths_module_template
		// TODO: 未実装
		$init_options['bindTemplate'] = array();

		// customFields
		// TODO: 未実装
		$init_options['customFields'] = array();

		// bindTemplate
		// TODO: テーマ編集の場合のコードは異なる
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

		// var_dump($init_options);
		$broccoli->init($init_options);
		return $broccoli;
	}

}
