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
 * Media gesture (screenshots & movies)
 */

define ('TITLE', 'Media');

require_once ('include/defines.php');
require_once ('lib/medias.php');
require_once ('lib/Html.php');
require_once ('lib/User.php');
require_once ('lib/PHPTemplate.php');


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


/* Base medias template with code shared by all views */
abstract class MediasTemplate extends PHPFileTemplate
{
	protected $view;
	
	public function __construct ()
	{
		parent::__construct (
			$this->view,
			array (
				'is_admin'			=> User::has_rights (ADMIN_LEVEL_MEDIA),
				'display_types'	=> $this->get_types_filter (),
				'display_tags'	=> $this->get_tags_filter (),
				'all_types'			=> $this->get_all_types (),
				'all_tags'			=> $this->get_all_tags ()
			)
		);
	}
	
	private $_types_filter = null;
	protected function get_types_filter ()
	{
		if ($this->_types_filter === null) {
			if (isset ($_POST['type']) && is_array ($_POST['type'])) {
				$this->_types_filter = $_POST['type'];
			} else if (isset ($_GET['type'])) {
				$this->_types_filter = explode (',', $_GET['type']);
			} else {
				/* Defaults to screenshots & videos */
				$this->_types_filter = array (MediaType::SCREENSHOT, MediaType::MOVIE);
			}
		}
		
		return $this->_types_filter;
	}
	
	private $_tags_filter = null;
	protected function get_tags_filter ()
	{
		if ($this->_tags_filter === null) {
			if (isset ($_POST['showtag']) && is_array ($_POST['showtag'])) {
				$this->_tags_filter = $_POST['showtag'];
			} else if (isset ($_GET['showtag'])) {
				$this->_tags_filter = explode (',', $_GET['showtag']);
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
			foreach (media_get_all_tags () as $tag) {
				$this->_all_tags[] = array (
					'id'			=> $tag,
					'name'		=> ($tag == '' ? 'Not tagged' : $tag),
					'checked'	=> in_array ($tag, $this->get_tags_filter ())
				);
			}
		}
		
		return $this->_all_tags;
	}
}

/* Main template displaying the medias index */
class MediasIndexTemplate extends MediasTemplate
{
	protected $view = 'views/medias/index.phtml';
	
	public function __construct ()
	{
		parent::__construct ();
		$this->sections = $this->get_sections ();
	}
	
	private function get_display_medias ()
	{
		$medias = media_get_medias (
			$this->get_types_filter (),
			$this->get_tags_filter (),
			array (
				'type' => 'ASC',
				'mdate' => 'DESC'
			)
		);
		
		if ($medias) {
			/* adjust the medias array */
			foreach ($medias as &$media) {
				$media['uri'] = MEDIA_DIR_R.'/'.$media['uri'];
				$media['tb_uri'] = MEDIA_DIR_R.'/'.$media['tb_uri'];
			}
		}
		
		return $medias;
	}
	
	private function get_sections ()
	{
		$sections = array ();
		
		$medias = $this->get_display_medias ();
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
}

/* View template for viewing a particular media */
class MediasViewTemplate extends MediasTemplate
{
	protected $view = 'views/medias/view.phtml';
	
	public function __construct ($media_id)
	{
		parent::__construct ();
		$this->media = $this->get_media ($media_id);
		$this->noreturn = filter_input (INPUT_GET, 'noreturn', FILTER_VALIDATE_BOOLEAN);
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
	
	private function get_media ($id)
	{
		$media = media_get_by_id ($id);
		if ($media) {
			$media['uri']				= MEDIA_DIR_R.'/'.$media['uri'];
			$media['tb_uri']		= MEDIA_DIR_R.'/'.$media['tb_uri'];
			$media['mime_type']	= filename_get_mime_type ($media['uri']);
			
			/* make sure media type is useful for us */
			$this->fix_media_type ($media);
		}
		
		return $media;
	}
}



/******************************************************************************/

/* compatibility with old API */
if (isset ($_GET['watch']) && settype ($_GET['watch'], 'int')) {
	$_GET['view'] = $_GET['watch'];
}

if (isset ($_GET['view']) && settype ($_GET['view'], 'int')) {
	$tpl = new MediasViewTemplate ($_GET['view']);
} else {
	$tpl = new MediasIndexTemplate ();
}


require_once ('include/top.minc');
$tpl->render ();
require_once ('include/bottom.minc');
