<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper;

/**
 * sitemapUtils.php
 */
class sitemapUtils{

	/** Picklesオブジェクト */
	private $px;

	/** px2dthelperオブジェクト */
	private $px2dthelper;

	/** ロックファイルのパス */
    private $lockfilepath;

	/** ロック解除を待つ回数(秒数) */
    private $timeout_limit = 5;

	/** ロックファイルの有効期限 */
	private $lockfile_expire = 60;

	/**
	 * constructor
	 *
	 * @param object $px2dthelper $px2dthelperオブジェクト
	 * @param object $px $pxオブジェクト
	 */
	public function __construct( $px2dthelper, $px ){
		$this->px2dthelper = $px2dthelper;
		$this->px = $px;

		$this->lockfilepath = $this->px->get_realpath_homedir().'_sys/ram/caches/sitemaps/making_sitemap_cache.lock.txt';
	}

    /**
     * 排他ロックする
	 *
	 * @return bool ロック成功時に `true`、失敗時に `false` を返します。
     */
    public function lock(){

		if( !$this->px->fs()->is_dir( dirname( $this->lockfilepath ) ) ){
			$this->px->fs()->mkdir_r( dirname( $this->lockfilepath ) );
		}

		clearstatcache();

		$i = 0;
		while( $this->is_locked() ){
			$i ++;
			if( $i >= $this->timeout_limit ){
				return false;
				break;
			}
			sleep(1);

			// PHPのFileStatusCacheをクリア
			clearstatcache();
		}
		$src = '';
		$src .= 'ProcessID='.getmypid()."\r\n";
		$src .= @date( 'Y-m-d H:i:s' , time() )."\r\n";
		$RTN = $this->px->fs()->save_file( $this->lockfilepath , $src );
		return	$RTN;
    }

    /**
     * 排他ロックされているか確認する
	 *
	 * @return bool ロック中の場合に `true`、それ以外の場合に `false` を返します。
     */
    public function is_locked(){

		clearstatcache();

		if( $this->px->fs()->is_file($this->lockfilepath) ){
			if( ( time() - filemtime($this->lockfilepath) ) > $this->lockfile_expire ){
				// 有効期限を過ぎていたら、ロックは成立する。
				return false;
			}
			return true;
		}
		return false;
    }

    /**
     * 排他ロックを解除する
	 *
	 * @return bool ロック解除成功時に `true`、失敗時に `false` を返します。
     */
    public function unlock(){

		clearstatcache();
		if( !$this->px->fs()->is_file( $this->lockfilepath ) ){
			return true;
		}

		return unlink( $this->lockfilepath );
    }

	/**
	 * アプリケーションロックファイルの更新日を更新する。
	 *
	 * @return bool 成功時に `true`、失敗時に `false` を返します。
	 */
	public function touch_lockfile(){

		clearstatcache();
		if( !$this->px->fs()->is_file( $this->lockfilepath ) ){
			return false;
		}

		return touch( $this->lockfilepath );
	}
}
