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
 * Media management (screenshots & movies)
 */

require_once ('include/defines.php');
require_once ('lib/medias.php');
require_once ('lib/Html.php');
require_once ('lib/User.php');
require_once ('lib/LayoutController.php');


/* Splits an array in chunks according to $callback
 * 
 * @array An array to split
 * @callback a user callback to determine where to cut the array.  The callback
 *           takes two elements and should return an strcmp()-like value.
 *           A slit occurs when the callback returns a non-0 value.
 * 
 * Like array_chunk() but instead of cutting the array in equally-sized chunks,
 * cut it according to the result of $callback */
function array_chunk_callback (array $array, $callback)
{
	$chunks	= array ();
	
	if (count ($array) > 0) {
		$chunk	= array ();
		$first	= true;
		$prev		= null;
		
		$chunks[] = &$chunk;
		
		foreach ($array as &$element) {
			if ($first) {
				$first = false;
			} else {
				if ($callback ($prev, $element) != 0) {
					unset ($chunk); /* clear ref */
					$chunk = array ();
					$chunks[] = &$chunk;
				}
			}
			$chunk[] = $element;
			$prev = $element;
		}
	}
	
	return $chunks;
}


class MediasModel
{
	public static function is_hidden_tag ($tag)
	{
		return strlen ($tag) > 0 && $tag[0] == '.';
	}
	
	public static function remove_hidden_tags (&$media)
	{
		foreach ($media['tags'] as $k => $t) {
			if (self::is_hidden_tag ($t)) {
				unset ($media['tags'][$k]);
			}
		}
	}
	
	public function find (array $types = array (),
	                      array $tags = array (),
	                      array $sort = array ('type' => 'ASC',
	                                           'mdate' => 'DESC'))
	{
		$medias = media_get_medias ($types, $tags, $sort);
		if ($medias) {
			/* adjust the medias array */
			foreach ($medias as &$media) {
				$media['uri'] = MEDIA_DIR_R.'/'.$media['uri'];
				$media['tb_uri'] = MEDIA_DIR_R.'/'.$media['tb_uri'];
				$this->remove_hidden_tags ($media);
			}
		}
		
		return $medias;
	}
	
	private function is_visible_tag ($t)
	{
		return ! $this->is_hidden_tag ($t);
	}
	
	public function find_tags ()
	{
		return array_filter (
			media_get_all_tags (),
			array ($this, 'is_visible_tag')
		);
	}
	
	private function fix_media_type (array &$media)
	{
		if ($media['type'] != MediaType::SCREENSHOT &&
				$media['type'] != MediaType::MOVIE) {
			/* try to map to a known type if possible */
			if (str_has_prefix ($media['mime_type'], 'image/')) {
				$media['type'] = MediaType::SCREENSHOT;
			} else if (str_has_prefix ($media['mime_type'], 'video/')) {
				$media['type'] = MediaType::MOVIE;
			}
		}
	}
	
	public function get ($id)
	{
		$media = media_get_by_id ($id);
		if ($media) {
			$media['uri']				= MEDIA_DIR_R.'/'.$media['uri'];
			$media['tb_uri']		= MEDIA_DIR_R.'/'.$media['tb_uri'];
			$media['mime_type']	= filename_get_mime_type ($media['uri']);
			
			$this->remove_hidden_tags ($media);
			/* make sure media type is useful for us */
			$this->fix_media_type ($media);
		}
		
		return $media;
	}
}

class MediasController extends LayoutController
{
	private $Medias;
	
	public function __construct ()
	{
		$this->Medias = new MediasModel ();
	}
	
	private $_types_filter = null;
	protected function get_types_filter ($types = null)
	{
		if ($this->_types_filter === null) {
			if (isset ($_POST['type']) && is_array ($_POST['type'])) {
				$this->_types_filter = $_POST['type'];
			} else if ($types !== null) {
				$this->_types_filter = explode (',', $types);
			} else {
				/* Defaults to screenshots & videos */
				$this->_types_filter = array (MediaType::SCREENSHOT, MediaType::MOVIE);
			}
		}
		
		return $this->_types_filter;
	}
	
	private $_tags_filter = null;
	protected function get_tags_filter ($tags = null)
	{
		if ($this->_tags_filter === null) {
			if (isset ($_POST['showtag']) && is_array ($_POST['showtag'])) {
				$this->_tags_filter = $_POST['showtag'];
			} else if ($tags !== null) {
				$this->_tags_filter = explode (',', $tags);
			} else {
				$this->_tags_filter = array ();
			}
		}
		
		return $this->_tags_filter;
	}
	
	private $_all_types = null;
	protected function get_all_types ()
	{
		if ($this->_all_types === null) {
			$this->_all_types = array ();
			for ($type = 1; $type < MediaType::N_TYPES; $type++) {
				$this->_all_types[] = array (
					'id'			=> $type,
					'name'		=> MediaType::to_string ($type),
					'checked'	=> in_array ($type, $this->get_types_filter ()),
				);
			}
		}
		
		return $this->_all_types;
	}
	
	private $_all_tags = null;
	protected function get_all_tags ()
	{
		if ($this->_all_tags === null) {
			$this->_all_tags = array ();
			foreach ($this->Medias->find_tags () as $tag) {
				$this->_all_tags[] = array (
					'id'			=> $tag,
					'name'		=> ($tag == '' ? 'Not tagged' : $tag),
					'checked'	=> in_array ($tag, $this->get_tags_filter ())
				);
			}
		}
		
		return $this->_all_tags;
	}
	
	private function get_common_vars ($types = null, $tags = null)
	{
		return array (
			'is_admin'			=> User::has_rights (ADMIN_LEVEL_MEDIA),
			'display_types'	=> $this->get_types_filter ($types),
			'display_tags'	=> $this->get_tags_filter ($tags),
			'all_types'			=> $this->get_all_types (),
			'all_tags'			=> $this->get_all_tags ()
		);
	}
	
	private function get_sections ($types = null, $tags = null)
	{
		$sections = array ();
		
		$medias = $this->Medias->find (
			$this->get_types_filter ($types),
			$this->get_tags_filter ($tags),
			array (
				'type' => 'DESC',
				'mdate' => 'DESC'
			)
		);
		if ($medias) {
			/* expand the medias to blocks of the same type */
			$medias_by_types = array_chunk_callback (
				$medias,
				function ($a, $b) {
					return $a['type'] - $b['type'];
				}
			);
			
			/* and build each section details */
			foreach ($medias_by_types as &$medias) {
				$sections[] = array (
					'id'			=> MediaType::to_id ($medias[0]['type']),
					'name'		=> ucfirst (MediaType::to_string ($medias[0]['type'], true)),
					'medias'	=> &$medias
				);
			}
		}
		
		return $sections;
	}
	
	protected function get_layout_vars ($route, $action_data)
	{
		$vars = parent::get_layout_vars ($route, $action_data);
		
		if ($route->action == 'view') {
			$media_title = $action_data['media']['desc'];
			if (strlen ($media_title) > 0) {
				$vars['page_title'] = Html::escape ($media_title).' &mdash; '.$vars['page_title'];
			}
		}
		
		return $vars;
	}
	
	/* actions */
	
	public function index ($types = null, $tags = null)
	{
		return array_merge (
			$this->get_common_vars ($types, $tags),
			array ('sections' => $this->get_sections ($types, $tags))
		);
	}
	
	public function view ($media_id = -1, $noreturn = false)
	{
		return array_merge (
			$this->get_common_vars (),
			array (
				'media' => $this->Medias->get ($media_id),
				'noreturn' => $noreturn == true
			)
		);
	}
}
