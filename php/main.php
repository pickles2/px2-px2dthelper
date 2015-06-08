<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper;

/**
 * main.php
 */
class main{

	/**
	 * Picklesオブジェクト
	 */
	private $px;

	/**
	 * PXコマンド名
	 */
	private $command = array();

	/**
	 * px2dtconfig
	 */
	private $px2dtconfig;

	/**
	 * $module_templates
	 */
	private $obj_module_templates;

	/**
	 * entry
	 */
	static public function register($px){
		$px->pxcmd()->register('px2dthelper', function($px){
			(new self( $px ))->kick();
			exit;
		}, true);
	}

	/**
	 * px2-px2dthelper のバージョン情報を取得する。
	 * 
	 * <pre> [バージョン番号のルール]
	 *    基本
	 *      メジャーバージョン番号.マイナーバージョン番号.リリース番号
	 *        例：1.0.0
	 *        例：1.8.9
	 *        例：12.19.129
	 *      - 大規模な仕様の変更や追加を伴う場合にはメジャーバージョンを上げる。
	 *      - 小規模な仕様の変更や追加の場合は、マイナーバージョンを上げる。
	 *      - バグ修正、ドキュメント、コメント修正等の小さな変更は、リリース番号を上げる。
	 *    開発中プレビュー版
	 *      基本バージョンの後ろに、alpha(=α版)またはbeta(=β版)を付加し、その連番を記載する。
	 *        例：1.0.0-alpha1 ←最初のα版
	 *        例：1.0.0-beta12 ←12回目のβ版
	 *      開発中およびリリースバージョンの順序は次の通り
	 *        1.0.0-alpha1 -> 1.0.0-alpha2 -> 1.0.0-beta1 ->1.0.0-beta2 -> 1.0.0 ->1.0.1-alpha1 ...
	 *    ナイトリービルド
	 *      ビルドの手順はないので正確には "ビルド" ではないが、
	 *      バージョン番号が振られていない、開発途中のリビジョンを
	 *      ナイトリービルドと呼ぶ。
	 *      ナイトリービルドの場合、バージョン情報は、
	 *      ひとつ前のバージョン文字列の末尾に、'-nb' を付加する。
	 *        例：1.0.0-beta12-nb (=1.0.0-beta12リリース後のナイトリービルド)
	 *      普段の開発においてコミットする場合、
	 *      必ずこの get_version() がこの仕様になっていることを確認すること。
	 * </pre>
	 * 
	 * @return string バージョン番号を示す文字列
	 */
	public function get_version(){
		return '2.0.0-alpha1-nb';
	}

	/**
	 * constructor
	 * 
	 * @param object $px $pxオブジェクト
	 */
	public function __construct( $px ){
		$this->px = $px;
		$this->px2dtconfig = json_decode('{}');
		if( @is_object($this->px->conf()->plugins->px2dt) ){
			$this->px2dtconfig = $this->px->conf()->plugins->px2dt;
		}elseif( is_file( $this->px->get_path_homedir().'px2dtconfig.json' ) ){
			$this->px2dtconfig = json_decode( $this->px->fs()->read_file( $this->px->get_path_homedir().'px2dtconfig.json' ) );
		}

		require_once( __DIR__.'/module_templates.php' );
		$this->obj_module_templates = new module_templates($this->px, $this);

	}

	/**
	 * px2dtconfigを取得する
	 */
	public function get_px2dtconfig(){
		return $this->px2dtconfig;
	}

	/**
	 * ドキュメントモジュール定義をロードする
	 */
	public function document_modules(){
		require_once( __DIR__.'/document_modules.php' );
		$rtn = '';
		$rtn = new document_modules($this->px, $this);
		return $rtn;
	}

	/**
	 * フィールド定義オブジェクトを取得
	 */
	public function get_field_definition( $field_type ){
		require_once( __DIR__.'/field_base.php' );
		$rtn = null;
		if( is_file( __DIR__.'/fields/field.'.$field_type.'.php' ) ){
			require_once( __DIR__.'/fields/field.'.$field_type.'.php' );
			$class_name = '\\tomk79\\pickles2\\px2dthelper\\field_'.$field_type;
			$rtn = new $class_name();
		}else{
			$rtn = new field_base();
		}
		return $rtn;
	}

	/**
	 * ドキュメントモジュール定義をロードする
	 */
	public function module_templates(){
		return $this->obj_module_templates;
	}

	/**
	 * kick as PX Command
	 * 
	 * @return void
	 */
	private function kick(){
		$this->command = $this->px->get_px_command();

		switch( @$this->command[1] ){
			case 'ping':
				// 疎通確認応答
				@header('Content-type: text/plain;');
				print 'ok'."\n";
				exit;
				break;
			case 'convert_table_excel2html':
				$path_xlsx = $this->px->req()->get_param('path');
				if( !is_file($path_xlsx) || !is_readable($path_xlsx) ){
					print $this->data_convert( false );
					exit;
					break;
				}
				$excel2html = new \tomk79\excel2html\main($path_xlsx);
				$val = $excel2html->get_html(array(
					'header_row' => $this->px->req()->get_param('header_row') ,
					'header_col' => $this->px->req()->get_param('header_col') ,
					'renderer' => $this->px->req()->get_param('renderer') ,
					'cell_renderer' => $this->px->req()->get_param('cell_renderer') ,
					'strip_table_tag' => true
				));
				print $this->data_convert( $val );
				exit;
				break;
		}

		print $this->px->pxcmd()->get_cli_header();
		print 'Pickles 2 Desktop Tool Helper plugin.'."\n";
		print $this->px->pxcmd()->get_cli_footer();
		exit;
	}

	// -------------------------------------

	/**
	 * データを自動的に加工して返す。
	 * 
	 * @param mixed $val 加工するデータ
	 * @return string 加工されたテキストデータ
	 */
	private function data_convert($val){
		$data_type = $this->px->req()->get_param('type');
		if( !is_string($data_type) || !strlen($data_type) ){
			$data_type = 'json';
		}
		header('Content-type: application/xml; charset=UTF-8');
		if( $data_type == 'json' ){
			header('Content-type: application/json; charset=UTF-8');
		}elseif( $data_type == 'jsonp' ){
			header('Content-type: application/javascript; charset=UTF-8');
		}
		switch( $data_type ){
			case 'jsonp':
				return $this->data2jsonp($val);
				break;
			case 'json':
				return $this->data2json($val);
				break;
			case 'xml':
				return $this->data2xml($val);
				break;
		}
		// return self::data2jssrc($val);
		return json_encode($val);
	}

	/**
	 * データをXMLに加工して返す。
	 * 
	 * @param mixed $val 加工するデータ
	 * @return string 加工されたテキストデータ
	 */
	private function data2xml($val){
		return '<api>'.self::xml_encode($val).'</api>';
	}

	/**
	 * データをJSONに加工して返す。
	 * 
	 * @param mixed $val 加工するデータ
	 * @return string 加工されたテキストデータ
	 */
	private function data2json($val){
		// return self::data2jssrc($val);
		return json_encode($val);
	}

	/**
	 * データをJSONPに加工して返す。
	 * 
	 * @param mixed $val 加工するデータ
	 * @return string 加工されたテキストデータ
	 */
	private function data2jsonp($val){
		//JSONPのコールバック関数名は、パラメータ callback に受け取る。
		$cb = trim( $this->px->req()->get_param('callback') );
		if( !strlen($cb) ){
			$cb = 'callback';
		}
		// return $cb.'('.self::data2jssrc($val).');';
		return $cb.'('.json_encode($val).');';
	}


	/**
	 * 変数をXML構造に変換する
	 * 
	 * @param mixed $value 値
	 * @param array $options オプション
	 * <dl>
	 *   <dt>delete_arrayelm_if_null</dt>
	 *     <dd>配列の要素が `null` だった場合に削除。</dd>
	 *   <dt>array_break</dt>
	 *     <dd>配列に適当なところで改行を入れる。</dd>
	 * </dl>
	 * @return string XMLシンタックスに変換された値
	 */
	private static function xml_encode( $value = null , $options = array() ){

		if( is_array( $value ) ){
			#	配列
			$is_hash = false;
			$i = 0;
			foreach( $value as $key=>$val ){
				#	ArrayかHashか見極める
				if( !is_int( $key ) ){
					$is_hash = true;
					break;
				}
				if( $key != $i ){
					#	順番通りに並んでなかったらHash とする。
					$is_hash = true;
					break;
				}
				$i ++;
			}

			if( $is_hash ){
				$RTN .= '<object>';
			}else{
				$RTN .= '<array>';
			}
			if( $options['array_break'] ){ $RTN .= "\n"; }
			foreach( $value as $key=>$val ){
				if( $options['delete_arrayelm_if_null'] && is_null( $value[$key] ) ){
					#	配列のnull要素を削除するオプションが有効だった場合
					continue;
				}
				$RTN .= '<element';
				if( $is_hash ){
					$RTN .= ' name="'.htmlspecialchars( $key ).'"';
				}
				$RTN .= '>';
				$RTN .= self::xml_encode( $value[$key] , $options );
				$RTN .= '</element>';
				if( $options['array_break'] ){ $RTN .= "\n"; }
			}
			if( $is_hash ){
				$RTN .= '</object>';
			}else{
				$RTN .= '</array>';
			}
			if( $options['array_break'] ){ $RTN .= "\n"; }
			return	$RTN;
		}

		if( is_object( $value ) ){
			#	オブジェクト型
			$RTN = '';
			$RTN .= '<object>';
			$proparray = get_object_vars( $value );
			$methodarray = get_class_methods( get_class( $value ) );
			foreach( $proparray as $key=>$val ){
				$RTN .= '<element name="'.htmlspecialchars( $key ).'">';

				$RTN .= self::xml_encode( $val , $options );
				$RTN .= '</element>';
			}
			$RTN .= '</object>';
			return	$RTN;
		}

		if( is_int( $value ) ){
			#	数値
			$RTN = '<value type="int">'.htmlspecialchars( $value ).'</value>';
			return	$RTN;
		}

		if( is_float( $value ) ){
			#	浮動小数点
			$RTN = '<value type="float">'.htmlspecialchars( $value ).'</value>';
			return	$RTN;
		}

		if( is_string( $value ) ){
			#	文字列型
			$RTN = '<value type="string">'.htmlspecialchars( $value ).'</value>';
			return	$RTN;
		}

		if( is_null( $value ) ){
			#	ヌル
			return	'<value type="null"></value>';
		}

		if( is_resource( $value ) ){
			#	リソース型
			return	'<value type="undefined"></value>';
		}

		if( is_bool( $value ) ){
			#	ブール型
			if( $value ){
				return	'<value type="bool">true</value>';
			}else{
				return	'<value type="bool">false</value>';
			}
		}

		return	'<value type="undefined"></value>';

	}

	/**
	 * ダブルクオートで囲えるようにエスケープ処理する。
	 *
	 * @param string $text テキスト
	 * @return string エスケープされたテキスト
	 */
	private static function escape_doublequote( $text ){
		$text = preg_replace( '/\\\\/' , '\\\\\\\\' , $text);
		$text = preg_replace( '/"/' , '\\"' , $text);
		return	$text;
	}

}
