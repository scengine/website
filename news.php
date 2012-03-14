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

/* News */

define ('TITLE', 'News');
define ('NEWS_PREVIEW_SIZE', 250);
define ('NEWS_BY_PAGE', 3);

require_once ('include/defines.php');
require_once ('lib/UrlTable.php');
require_once ('lib/News.php');
require_once ('lib/Html.php');
require_once ('lib/PHPTemplate.php');


/* Base news template with code shared by all views */
abstract class NewsTemplate extends PHPFileTemplate
{
	protected $view;
	
	public function __construct ()
	{
		parent::__construct (
			$this->view,
			array (
				'is_admin'			=> User::has_rights (ADMIN_LEVEL_NEWS),
				'add_news_url'	=> UrlTable::admin_news ('new'),
				'hidden_forms'	=> true,
			)
		);
	}
}

/* Main template displaying the news index */
class NewsIndexTemplate extends NewsTemplate
{
	protected $view = 'views/news/index.phtml';
	
	public function __construct ()
	{
		parent::__construct ();
		$this->feed_url = NEWS_ATOM_FEED_FILE;
		$this->news = $this->get_news ();
		$this->paging = $this->get_page_browser ($this->get_page ());
	}
	
	private $_page = null;
	private function get_page ()
	{
		if ($this->_page === null) {
			$this->_page = filter_input (INPUT_GET, 'page', FILTER_VALIDATE_INT,
			                             array ('options' => array ('min_range' => 1)));
			/* first page is 0 internally but 1 for users */
			if ($this->_page > 0) {
				$this->_page --;
			}
		}
		return $this->_page;
	}
	
	private function get_news ()
	{
		$news_list = News::get ($this->get_page () * NEWS_BY_PAGE, NEWS_BY_PAGE);
		foreach ($news_list as &$news) {
			$news['permalink'] = UrlTable::news ($news['id'], $news['title']);
			/* if ($more) {
			$news['content'] = xmlstr_shortcut ($news['content'], NEWS_PREVIEW_SIZE,
		                                      '… <a href="'.$news['permalink'].'" class="more">read more</a>');
			}*/
		}
		return $news_list;
	}
	
	/* FIXME: this code is ugly */
	private function get_page_browser ($current, $limit = 2, $sep = ' | ')
	{
		$n_news = News::get_n ();
		$n_pages = ceil ($n_news / NEWS_BY_PAGE);
		$has_prev = ($current > 0) ? true : false;
		$has_next = ($n_pages > $current + 1) ? true : false;
		$pager = '';
		
		if ($has_prev) {
			$pager .= '<a href="'.UrlTable::news_page ($current).'">newer</a>';
		} else {
			$pager .= '<span class="disabled">newer</span>';
		}
		
		$was_ok = true;
		for ($i=0; $i < $n_pages; $i++) {
			if ($i < $limit ||
					abs ($i - $current) <= $limit ||
					$i + $limit >= $n_pages ||
					/* only hide page if it would save > 1 link */
					($i < $current && $limit >= $current - 1 - $limit) ||
					($i > $current && $n_pages - 1 - $limit <= $current + 1 + $limit)) {
				if ($i == $current) {
					$pager .= $sep.'<span class="current">'.($i + 1).'</span>';
				} else {
					$pager .= $sep.'<a href="'.UrlTable::news_page ($i + 1).'">'.($i + 1).'</a>';
				}
				$was_ok = true;
			} else if ($was_ok) {
				$pager .= $sep.'...';
				$was_ok = false;
			}
		}
		
		if ($has_next) {
			$pager .= $sep.'<a href="'.UrlTable::news_page ($current + 1 + 1).'">older</a>';
		} else {
			$pager .= $sep.'<span class="disabled">older</span>';
		}
		
		return $pager;
	}
}

/* View template for viewing a particular media */
class NewsViewTemplate extends NewsTemplate
{
	protected $view = 'views/news/view.phtml';
	
	public function __construct ($id)
	{
		parent::__construct ();
		$this->news = $this->get_news ($id);
	}
	
	private function get_news ($id)
	{
		$news = News::get_by_id ($id);
		if ($news) {
			$news['permalink'] = UrlTable::news ($news['id'], $news['title']);
		}
		return $news;
	}
}


/******************************************************************************/

$view_id = filter_input (INPUT_GET, 'shownews', FILTER_VALIDATE_INT,
                         array ('options' => array ('min_range' => 0)));
if (is_int ($view_id)) {
	$tpl = new NewsViewTemplate ($view_id);
} else {
	$tpl = new NewsIndexTemplate ();
}


require_once ('include/top.minc');
$tpl->render ();
require_once ('include/bottom.minc');
