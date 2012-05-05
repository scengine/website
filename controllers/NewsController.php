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

define ('NEWS_PREVIEW_SIZE', 250);
define ('NEWS_BY_PAGE', 3);

require_once ('include/defines.php');
require_once ('lib/LayoutController.php');
require_once ('lib/UrlTable.php');
require_once ('lib/News.php');
require_once ('lib/Html.php');


class NewsModel
{
	private function ajust_news (&$news)
	{
		$news['permalink'] = UrlTable::news ($news['id'], $news['title']);
		/* if ($more) {
		$news['content'] = xmlstr_shortcut ($news['content'], NEWS_PREVIEW_SIZE,
																				'… <a href="'.$news['permalink'].'" class="more">read more</a>');
		}*/
	}
	
	public function find ($offset = 0, $count = NEWS_BY_PAGE)
	{
		$news_list = News::get ($offset, $count);
		foreach ($news_list as &$news) {
			$this->ajust_news ($news);
		}
		return $news_list;
	}
	
	public function get ($id)
	{
		$news = News::get_by_id ($id);
		if ($news) {
			$this->ajust_news ($news);
		}
		return $news;
	}
}

class NewsController extends LayoutController
{
	private $News = null;
	
	public function __construct ()
	{
		$this->News = new NewsModel ();
	}
	
	private function get_common_vars ()
	{
		return array (
			'is_admin'			=> User::has_rights (ADMIN_LEVEL_NEWS),
			'news_url'			=> UrlTable::news (),
			'add_news_url'	=> UrlTable::admin_news ('new'),
			'hidden_forms'	=> true,
		);
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
	
	protected function get_layout_vars ($route, $action_data)
	{
		$vars = parent::get_layout_vars ($route, $action_data);
		
		if ($route->action == 'view') {
			$vars['page_title'] = Html::escape ($action_data['news']['title']).' &mdash; '.$vars['page_title'];
		}
		
		return $vars;
	}
	
	/* actions */
	
	public function index ($page = 1)
	{
		$page = filter_var ($page, FILTER_VALIDATE_INT, array (
			'options' => array (
				'default' => 1,
				'min_range' => 1
			)
		)) - 1;
		$vars = $this->get_common_vars ();
		
		$vars['feed_url'] = NEWS_ATOM_FEED_FILE;
		$vars['news']     = $this->News->find ($page * NEWS_BY_PAGE, NEWS_BY_PAGE);
		$vars['paging']   = $this->get_page_browser ($page);
		
		return $vars;
	}
	
	public function view ($id = -1)
	{
		$vars = $this->get_common_vars ();
		$vars['news'] = $this->News->get ($id);
		
		return $vars;
	}
}
