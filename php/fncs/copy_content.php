<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper\fncs;

/**
 * fncs/copy_content.php
 */
class copy_content{

	/** Picklesオブジェクト */
	private $px;

	/** px2dthelperオブジェクト */
	private $px2dthelper;

	/**
	 * constructor
	 *
	 * @param object $px2dthelper $px2dthelperオブジェクト
	 * @param object $px $pxオブジェクト
	 */
	public function __construct( $px2dthelper, $px ){
		$this->px2dthelper = $px2dthelper;
		$this->px = $px;
	}

	/**
	 * コンテンツを複製する
	 *
	 * @param  string $path_from コピー元のページパス (サイトマップの path 値)
	 * @param  string $path_to   コピー先のページパス (サイトマップの path 値)
	 * @param  array  $options   オプション
	 * - `force` : `true` を指定すると、すでにコンテンツが存在した場合にも強制的に上書きします。
	 * @return array `array(boolean $result, string $error_msg)`
	 */
	public function copy( $path_from, $path_to, $options = array() ){
		if( !$this->px->site() ){
			return array(false, '$px->site() is not defined.');
		}

		$contRoot = $this->px->fs()->get_realpath( $this->px->get_path_docroot().'/'.$this->px->get_path_controot() );

		$from = array();
		$from['pathContent'] = $this->px2dthelper->find_page_content( $path_from );
		$from['pathFiles'] = $this->px2dthelper->path_files( $path_from );
		$from['procType'] = $this->px->get_path_proc_type( $path_from );

		$to = array();
		$to['pathContent'] = $this->px2dthelper->find_page_content( $path_to );
		$to['pathFiles'] = $this->px2dthelper->path_files( $path_to );
		$to['procType'] = $this->px->get_path_proc_type( $path_to );
		// var_dump($from, $to);

		$flg_force = false;
		if( is_array($options) && array_key_exists('force', $options) ){
			$flg_force = !!$options['force'];
		}

		if( $from['pathContent'] == $to['pathContent'] ){
			return array(false, 'Same paths was given to `$from` and `$to`.');
		}

		if( $this->px->fs()->is_file( $contRoot.'/'.$to['pathContent'] ) && !$flg_force ){
			return array(false, 'Contents already exists.');
		}

		// 一旦削除する
		if( $this->px->fs()->is_file( $contRoot.'/'.$to['pathContent'] ) ){
			$this->px->fs()->rm( $contRoot.'/'.$to['pathContent'] );
		}
		if( $this->px->fs()->is_dir( $contRoot.'/'.$to['pathFiles'] ) ){
			$this->px->fs()->rmdir_r( $contRoot.'/'.$to['pathFiles'] );
		}

		// 格納ディレクトリを作る
		if( !$this->px->fs()->is_dir( $contRoot.'/'.$to['pathFiles'] ) ){
			// 再帰的に作る mkdir_r()
			if( !$this->px->fs()->mkdir_r( $contRoot.'/'.$to['pathFiles'] ) ){
				return array(false, 'ng');
			}
		}

		// 複製する
		if( $this->px->fs()->is_file( $contRoot.'/'.$from['pathContent'] ) ){
			$this->px->fs()->copy( $contRoot.'/'.$from['pathContent'], $contRoot.'/'.$to['pathContent'] );
		}
		if( $this->px->fs()->is_dir( $contRoot.'/'.$from['pathFiles'] ) ){
			$this->px->fs()->copy_r( $contRoot.'/'.$from['pathFiles'], $contRoot.'/'.$to['pathFiles'] );
		}

		// コンテンツのprocTypeが異なる場合
		if( $from['procType'] !== $to['procType'] ){
			// 拡張子を合わせる作業
			$toPageInfo = $this->px->site()->get_page_info( $path_to );

			switch( $from['procType'] ){
				case 'html':
				case 'html.gui':
					$toPathContent = $toPageInfo['content'];
					if( !preg_match( '/\\.html$/i', $toPageInfo['content'] ) ){
						$toPathContent = $toPageInfo['content'].'.html';
					}
					$this->px->fs()->rename(
						$contRoot.'/'.$to['pathContent'],
						$contRoot.'/'.$toPathContent
					);
					break;
				default:
					$this->px->fs()->rename(
						$contRoot.'/'.$to['pathContent'],
						$contRoot.'/'.$toPageInfo['content'].'.'.$from['procType']
					);
					break;
			}

		}

		return array(true, 'ok');
	}

}
