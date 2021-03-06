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

define ('NEWS_PREVIEW_SIZE', 350);

require_once ('include/defines.php');
require_once ('lib/medias.php');
require_once ('lib/UrlTable.php');
require_once ('lib/Html.php');
require_once ('lib/Flash.php');
require_once ('lib/News.php');
require_once ('lib/medias.php');
require_once ('lib/MyDB.php');
require_once ('lib/FluxBB.php');
require_once ('lib/FeedReader.php');
require_once ('lib/FeedReaderAtom.php');
require_once ('lib/Metadata.php');
require_once ('lib/PHPTemplate.php');
require_once ('lib/LayoutController.php');


class IndexFeedReaderAtom extends FeedReaderAtom  {
	protected function fill ()
	{
		/* force flushing the already generated output since the next operation
		 * might take a long time.  this prevents the user from waiting too long to
		 * see anything simply because we're downloading a feed;  she will at least
		 * see the page we output until now, waiting only for upcoming data */
		ob_flush ();
		flush ();
		
		return parent::fill ();
	}
}


abstract class IndexModule {
	public $name = null;
	public $links = array ();
	public $feed = null;
	public $extra_classes = array ();
	
	protected $view;
	
	protected abstract function get_tpl_vars ();
	
	public function __toString ()
	{
		$extra_classes = '';
		foreach ($this->extra_classes as $class) {
			$extra_classes .= ' '.$class;
		}
		
		$data_tpl = new PHPFileTemplate ($this->view, $this->get_tpl_vars ());
		$tpl = new PHPFileTemplate ('views/index/modules/module.phtml',
			array (
				'extra_classes' => $extra_classes,
				'title'         => htmlentities ($this->name),
				'feed'          => htmlentities ($this->feed),
				'data'          => (string) $data_tpl,
				'links'         => $this->links
			)
		);
		
		return (string) $tpl;
	}
}

class IndexModuleScreenshot extends IndexModule {
	public $name = 'Random Screenshot';
	protected $view = 'views/index/modules/screenshot.phtml';
	
	protected function get_tpl_vars ()
	{
		$type = MediaType::SCREENSHOT;
		
		$db = new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
		$db->select_table (MEDIA_TABLE);
		
		$db->random_row ('*', array ('type' => $type));
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
	protected $view = 'views/index/modules/forum.phtml';
	
	public function __construct () {
		$this->name = 'Last Forum Posts';
		$this->links = array (BSE_BASE_FLUXBB_PATH => 'Visit the Forum');
		$this->feed = FluxBB::get_recent_feed ();
	}
	
	protected function get_tpl_vars ()
	{
		$reader = new IndexFeedReaderAtom ($this->feed, 300 /* 5min cache */);
		
		return array (
			'items' => $reader->get_items ()
		);
	}
}

class IndexModuleNews extends IndexModule {
	protected $view = 'views/index/modules/news.phtml';
	
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
			                              '… <a href="'.$permalink.'" class="more">read more</a>'),
			'permalink' => $permalink,
			'author' => $news['author'],
			'date' => $news['date']
		);
	}
}

class IndexModuleCommits extends IndexModule {
	protected $view = 'views/index/modules/commits.phtml';
	
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
		$reader = new IndexFeedReaderAtom ($this->feed);
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
	protected $view = 'views/index/modules/version.phtml';
	
	protected function get_tpl_vars ()
	{
		return array (
			'version' => Metadata::get_instance ()->get_version (),
			'url'     => UrlTable::downloads ()
		);
	}
}

class IndexModuleMainImage extends IndexModule {
	private $urls = array ();
	protected $view = 'views/index/modules/main-image.phtml';
	public $extra_classes = array ('image');
	
	public function __construct ($tag)
	{
		$medias = media_get_medias (array (MediaType::SCREENSHOT), array ($tag));
		foreach ($medias as $media) {
			$this->urls[] = MEDIA_DIR_R.'/'.$media['uri'];
		}
	}
	
	protected function get_tpl_vars ()
	{
		return array ('urls' => $this->urls);
	}
}

class IndexModuleMailingList extends IndexModule {
	public $name = 'Mailing list';
	public $extra_classes = array ('ml', 'subscribe');
	protected $view = 'views/index/modules/mailing-list.phtml';
	
	private $email;
	private $ml_request_email;
	
	public function __construct ($ml_request_email, $ml_archives_url)
	{
		$this->ml_request_email = $ml_request_email;
		
		$this->links = array (
			$ml_archives_url => 'Mailing List Archives'
		);
		
		/* handle subscriptions */
		$this->handle_user_request ();
	}
	
	private function handle_user_request ()
	{
		$email = filter_input (INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
		$subscribe = filter_input (INPUT_POST, 'subscribe', FILTER_VALIDATE_BOOLEAN);
		
		$this->email = filter_input (INPUT_POST, 'email');
		if ($email) {
			if (! mail ($this->ml_request_email,
			            $subscribe ? 'subscribe' : 'unsubscribe', '',
			            'From: '.$email)) {
				Flash::add ('error', 'Failed to send confirmation email.');
			} else {
				Flash::add ('info', 'A confirmation email has been sent to you.');
			}
		} else if (isset ($_POST['email'])) {
			Flash::add ('error', 'Invalid email address.');
		} else {
			$this->email = 'email@domain.tld';
		}
	}
	
	protected function get_tpl_vars ()
	{
		return array (
			'email'		=> Html::escape ($this->email)
		);
	}
}


/* Controller */
class IndexController extends LayoutController
{
	protected function get_layout_vars ($route, $action_data)
	{
		$vars = parent::get_layout_vars ($route, $action_data);
		
		$vars['page_title'] = 'Home';
		$vars['site_title'] .= ', '.Metadata::get_instance ()->get_description ();
		
		return $vars;
	}
	
	public function index ()
	{
		return array (
			'modules' => array (
				new IndexModuleMainImage ('.home'),
				new IndexModuleNews ()
			),
			'columns' => array (
				array (
					new IndexModuleCommits (
						BSE_SITE_URL.UrlTable::feed ('commits.atom'),
						array (
							'http://git.tuxfamily.org/scengine/utils.git' => 'Utils',
							'http://git.tuxfamily.org/scengine/core.git' => 'Core',
							'http://git.tuxfamily.org/scengine/renderergl.git' => 'Renderer-GL',
							'http://git.tuxfamily.org/scengine/interface.git' => 'Interface'
						),
						'Last Commits'
					)
				),
				array (
					new IndexModuleMailingList ('scengine-request@lists.tuxfamily.org',
																			'http://listengine.tuxfamily.org/lists.tuxfamily.org/scengine/'),
					new IndexModuleForum ()
				)
			)
		);
	}
}
