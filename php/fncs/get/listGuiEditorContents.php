<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper\fncs\get;
use tomk79\pickles2\px2dthelper\fncs\packages;

/**
 * `PX=px2dthelper.get.list_gui_editor_contents`
 */
class listGuiEditorContents{

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
     * GUIエディターコンテンツの一覧を取得する
     */
    public function get_gui_editor_contents(){
		$rtn = (object) array();
		$rtn->result = true;
		$rtn->gui_editor_contents = array();

		// サイトマップを検索
		$sitemap = $this->px->site()->get_sitemap();
		foreach($sitemap as $page_info){
			$editor_mode = $this->px2dthelper->check_editor_mode( $page_info['path'] );
			if( $editor_mode == 'html.gui' ){
				array_push($rtn->gui_editor_contents, $page_info['path']);
			}
		}

		// ブログマップを検索
		if( is_object($this->px->blog) ){
			$blogs = $this->px->blog->get_blog_list();
			foreach($blogs as $blog){
				$articles = $this->px->blog->get_article_list($blog['blog_id']);
				foreach($articles as $page_info){
					$editor_mode = $this->px2dthelper->check_editor_mode( $page_info['path'] );
					if( $editor_mode == 'html.gui' ){
						array_push($rtn->gui_editor_contents, $page_info['path']);
					}
				}
			}
		}

		// 未アサインコンテンツを検索
		$listUnassignedContents = new listUnassignedContents($this->px2dthelper, $this->px);
		$unassignedContents = $listUnassignedContents->get_unassigned_contents();
		foreach( $unassignedContents->unassigned_contents as $unassignedContent ){
			$content_path = $unassignedContent;
			// 拡張子 .html, .htm の2重拡張子以外の場合は、最後の拡張子を削除する
			$content_path = preg_replace('/^(.*\.html?)(?:\.[a-zA-Z0-9]+)$/s', '$1', $content_path);

			$editor_mode = $this->px2dthelper->check_editor_mode( $content_path );
			if( $editor_mode == 'html.gui' ){
				array_push($rtn->gui_editor_contents, $unassignedContent);
			}
		}

        return $rtn;
    }
}
