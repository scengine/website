<?php
/* LICENSE
 * 
 * BanSE - a site base (designed to be the SCEngine website)
 * Copyright (C) 2007-2012 Colomban Wendling <ban@herbesfolles.org>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 */

/*
 * index & news
 */

define ('TITLE', 'Home');
define ('NEWS_PREVIEW_SIZE', 350);

require_once ('include/defines.php');
require_once ('lib/medias.php');
require_once ('lib/UrlTable.php');
require_once ('lib/News.php');
require_once ('lib/MyDB.php');
require_once ('lib/FluxBB.php');
require_once ('lib/FeedReader.php');
require_once ('lib/FeedReaderAtom.php');
require_once ('lib/Metadata.php');
require_once ('lib/Template.php');


abstract class IndexModule {
	public $name = null;
	public $links = array ();
	public $feed = null;
	public $extra_classes = array ();
	
	protected $view;
	
	protected abstract function get_tpl_vars ();
	
	public function display ()
	{
		$extra_classes = '';
		foreach ($this->extra_classes as $class) {
			$extra_classes .= ' '.$class;
		}
		
		$data_tpl = new FileTemplate ($this->view, $this->get_tpl_vars ());
		$tpl = new FileTemplate ('views/index-modules/module.tpl',
			array (
				'STYLE'         => STYLE,
				'extra_classes' => $extra_classes,
				'title'         => $this->name,
				'feed'          => $this->feed,
				'data'          => (string) $data_tpl,
				'links'         => $this->links
			)
		);
		
		echo $tpl;
	}
}

class IndexModuleScreenshot extends IndexModule {
	public $name = 'Random Screenshot';
	protected $view = 'views/index-modules/screenshot.tpl';
	
	protected function get_tpl_vars ()
	{
		$type = MediaType::SCREENSHOT;
		
		$db = new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
		$db->select_table (MEDIA_TABLE);
		
		$db->random_row ('`id`', '`type`=\''.$type.'\'');
		$media = $db->fetch_response ();
		
		if ($media) {
			media_unescape_db_array ($media);
			
			return array (
				'link'        => UrlTable::medias ($media['id'], true),
				'image'       => MEDIA_DIR_R.'/'.$media['tb_uri'],
				'description' => $media['desc']
			);
		} else {
			return array (
				'link'        => null,
				'image'       => null,
				'description' => null
			);
		}
	}
}

class IndexModuleForum extends IndexModule {
	protected $view = 'views/index-modules/forum.tpl';
	
	public function __construct () {
		$this->name = 'Last Forum Posts';
		$this->links = array (BSE_BASE_FLUXBB_PATH => 'Visit the Forum');
		$this->feed = FluxBB::get_recent_feed ();
	}
	
	protected function get_tpl_vars ()
	{
		return array ('items' => FluxBB::get_recent_list (20));
	}
}

class IndexModuleNews extends IndexModule {
	protected $view = 'views/index-modules/news.tpl';
	
	public function __construct () {
		$this->name = 'Last News';
		$this->links = array (UrlTable::news () => 'Browse News');
		$this->feed = NEWS_ATOM_FEED_FILE;
	}
	
	protected function get_tpl_vars ()
	{
		$news = News::get (0, 1);
		$news = $news[0];
		
		$permalink = UrlTable::news ($news['id'], $news['title']);
		
		return array (
			'title' => $news['title'],
			'content' => xmlstr_shortcut ($news['content'], NEWS_PREVIEW_SIZE,
			                              '… <a href="'.$permalink.'" class="more">lire la suite</a>')
		);
	}
}

class IndexModuleCommits extends IndexModule {
	protected $view = 'views/index-modules/commits.tpl';
	
	public function __construct ($feed, $links, $title = 'Last Commits')
	{
		$this->name = $title;
		$this->feed = $feed;
		if (is_array ($links)) {
			$this->links = $links;
		} else {
			$this->links = array ($links => 'Browse Code');
		}
	}
	
	protected function get_tpl_vars ()
	{
		$reader = new FeedReaderAtom ($this->feed);
		$items = $reader->get_items ();
		
		foreach ($items as &$item) {
			/* provide formatted date */
			$item['date'] = date ('Y-m-d H:i', $item[FEED_KEY_PUBLISHED]);
		}
		
		return array (
			'items' => $items
		);
	}
}

class IndexModuleVersion extends IndexModule {
	public $name = 'Latest Version';
	protected $view = 'views/index-modules/version.tpl';
	
	protected function get_tpl_vars ()
	{
		global $MDI;
		
		return array (
			'version' => $MDI->get_version (),
			'url'     => UrlTable::downloads ()
		);
	}
}

class IndexModuleMainImage extends IndexModule {
	private $image_url;
	protected $view = 'views/index-modules/main-image.tpl';
	public $extra_classes = array ('image');
	
	public function __construct ($url)
	{
		$this->image_url = $url;
	}
	
	protected function get_tpl_vars ()
	{
		return array ('url' => $this->image_url);
	}
}



/* Page body */

require_once ('include/top.minc');

?>
	<div id="presentation">
		<h2>Bienvenue sur le site officiel du SCEngine</h2>
		<p>
			Le <acronym title="Simple C Engine">SCEngine</acronym> est un
			<a href="http://fr.wikipedia.org/wiki/Moteur_3D">moteur 3D</a> programmé,
			comme son nom l'indique, en langage C. Il est libre, open-source, et distribué sous
			<a href="<?php echo UrlTable::license (); ?>">licence GNU GPL</a>. Il utilise exclusivement
			l'<acronym title="Application Programming Interface">API</acronym> OpenGL pour le rendu.
		</p>
	</div>
	
	<div id="content">
		
		<div class="main-modules">
			<?php
				$modules = array (
					new IndexModuleMainImage (MEDIA_DIR_R.'/screens/sce009a_011_02-03-09.jpg'
					                          /*MEDIA_DIR_R.'/screens/sce-0.1.0-deferredshadows.png'*/),
					new IndexModuleNews ()
				);
				
				foreach ($modules as &$module) {
					$module->display ();
				}
			?>
		</div>
		
		<div class="modules">
			<?php
			
			$columns = array (
				array (
					new IndexModuleCommits (BSE_BASE_URL . UrlTable::feed ('commits.atom'),
					                        array (
					                          'http://git.tuxfamily.org/?p=gitroot/scengine/utils.git' => 'Utils',
					                          'http://git.tuxfamily.org/?p=gitroot/scengine/core.git' => 'Core',
					                          'http://git.tuxfamily.org/?p=gitroot/scengine/renderergl.git' => 'Renderer-GL',
					                          'http://git.tuxfamily.org/?p=gitroot/scengine/interface.git' => 'Interface'
					                        ),
					                        'Last Commits')
				),
				array (
					new IndexModuleVersion (),
					new IndexModuleForum ()
				)
			);
			
			foreach ($columns as &$column) {
				echo '<div class="column">';
				foreach ($column as &$module) {
					$module->display ();
				}
				echo '</div>';
			}
			
			?>
			<div class="modules-end"></div>
		</div>
		
	</div>
<?php

require_once ('include/bottom.minc');
