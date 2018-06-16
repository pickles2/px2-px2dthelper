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
		$broccoli = new \broccoliHtmlEditor\broccoliHtmlEditor();
		$broccoli->init(
			array(
				'appMode' => 'web', // 'web' or 'desktop'. default to 'web'
				'paths_module_template' => array(
					'testMod1' => '/realpath/to/modules1/' ,
					'testMod2' => '/realpath/to/modules2/'
				) ,
				'documentRoot' => '/realpath/to/www/htdocs/', // realpath
				'pathHtml' => '/path/to/your_preview.html',
				'pathResourceDir' => '/path/to/your_preview_files/resources/',
				'realpathDataDir' => '/realpath/to/www/htdocs/path/to/your_preview_files/guieditor.ignore/',
				'customFields' => array(
					// カスタムフィールドを実装します。
					// このクラスは、 `broccoliHtmlEditor\\fieldBase` を基底クラスとして継承します。
					// customFields のキー(ここでは custom1)が、フィールドの名称になります。
					'custom1' => 'broccoli_class\\field_custom1'
				) ,
				'bindTemplate' => function($htmls){
					$fin = '';
					$fin .= '<!DOCTYPE html>'."\n";
					$fin .= '<html>'."\n";
					$fin .= '    <head>'."\n";
					$fin .= '        <title>sample page</title>'."\n";
					$fin .= '    </head>'."\n";
					$fin .= '    <body>'."\n";
					$fin .= '        <div data-contents="main">'."\n";
					$fin .= $htmls['main']."\n";
					$fin .= '        </div><!-- /main -->'."\n";
					$fin .= '        <div data-contents="secondly">'."\n";
					$fin .= $htmls['secondly']."\n";
					$fin .= '        </div><!-- /secondly -->'."\n";
					$fin .= '    </body>'."\n";
					$fin .= '</html>';

					return $fin;
				},
				'log' => function($msg){
					// エラー発生時にコールされます。
					// msg を受け取り、適切なファイルへ出力するように実装してください。
					error_log('[ERROR HANDLED]'.$msg, 3, '/path/to/error.log');
				}
			)
		);
		return $broccoli;
	}

}
