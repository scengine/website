<?php
/* LICENSE
 * 
 * BanSE - a site base (designed to be the SCEngine website)
 * Copyright (C) 2009-2012 Colomban Wendling <ban@herbesfolles.org>
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

require_once ('include/defines.php');
require_once ('lib/UrlTable.php');
require_once ('lib/string.php');
require_once ('lib/MyDB.php');

define ('MEDIA_THUMBNAIL_WIDTH',  160.0);
define ('MEDIA_THUMBNAIL_HEIGHT', 120.0);
/* default thumbnails if none */
define ('MEDIA_UNKNOWN_THUMBNAIL_URI',    'default/media_unknown.png');
define ('MEDIA_COMPRESSED_THUMBNAIL_URI', 'default/media_compressed.png');

/*
 * Table shape
 *  - id      INT(10) UNSIGNED AUTO_INCREMENT
 *  - uri     VARCHAR(1024)
 *  - tb_uri  VARCHAR(1024)
 *  - size    INT(10) UNSIGNED
 *  - mdate   BIGINT(20)
 *  - type    INT(11)
 *  - uid     INT(10) UNSIGNED
 *  - tags    VARCHAR(256)
 *  - desc    VARCHAR(512)
 *  - comment TEXT
 */

abstract class MediaType
{
	const UNKNOWN    = 0;
	const SCREENSHOT = 1;
	const MOVIE      = 2;
	const RELEASE    = 3;
	const NEWS       = 4; // for news medias, such as screens but not from engine
	const OTHER      = 5;
	const N_TYPES    = 6;
	
	protected static $id_names = array (
		MediaType::UNKNOWN    => 'medias_unknowns',
		MediaType::SCREENSHOT => 'medias_screens',
		MediaType::MOVIE      => 'medias_movies',
		MediaType::RELEASE    => 'medias_releases',
		MediaType::NEWS       => 'medias_news',
		MediaType::OTHER      => 'medias_others',
		MediaType::N_TYPES    => 'medias_invalids'
	);
	protected static $names = array (
		MediaType::UNKNOWN    => 'unknown',
		MediaType::SCREENSHOT => 'screenshot',
		MediaType::MOVIE      => 'video',
		MediaType::RELEASE    => 'release',
		MediaType::NEWS       => 'news',
		MediaType::OTHER      => 'other',
		MediaType::N_TYPES    => 'invalid type'
	);
	protected static $names_plurials = array (
		MediaType::UNKNOWN    => 'unknowns',
		MediaType::SCREENSHOT => 'screenshots',
		MediaType::MOVIE      => 'videos',
		MediaType::RELEASE    => 'releases',
		MediaType::NEWS       => 'news',
		MediaType::OTHER      => 'others',
		MediaType::N_TYPES    => 'invalid types'
	);
	
	private static function calibrate_media_type (&$media_type)
	{
		if (! settype ($media_type, 'int') ||
		    $media_type > MediaType::N_TYPES || $media_type < 0)
		{
			$media_type = MediaType::N_TYPES;
		}
		
		return $media_type;
	}
	
	public static function to_string ($media_type, $plurial = false)
	{
		self::calibrate_media_type ($media_type);
		
		if ($plurial)
			return self::$names_plurials[$media_type];
		else
			return self::$names[$media_type];
	}
	
	public static function to_id ($media_type)
	{
		self::calibrate_media_type ($media_type);
		
		return self::$id_names[$media_type];
	}
}


/*
 * Given a array of database reponse, unescape fields that needs it.
 * \warning Calling this function twice with the same array will unescape all
 *          two times
 */
function media_unescape_db_array (array &$arr)
{
	$arr['uri']     = rawurldecode ($arr['uri']);
	$arr['tb_uri']  = rawurldecode ($arr['tb_uri']);
	$arr['tags']    = explode (',', $arr['tags']);
	sort ($arr['tags']);
	
	return $arr;
}

/* sets an array's element type with settype() if that element exists
 * returns true on success, false otherwise */
function array_value_settype (array &$arr, $key, $type, $required = false)
{
	if (array_key_exists ($key, $arr)) {
		return settype ($arr[$key], $type);
	}
	return ! $required;
}

/* escapes an array element with rawurlencode() if the elements exists */
function array_value_rawurlencode (array &$arr, $key)
{
	if (array_key_exists ($key, $arr)) {
		$arr[$key] = rawurlencode ($arr[$key]);
	}
}

/**
 * Given a array of database reponse, escape fields that needs it.
 * it is uset to ensure it is safe to put value into the DB
 * \warning Calling this function twice with the same array will escape all two
 *          times
 */
function media_escape_db_array (array &$arr)
{
	array_value_rawurlencode ($arr, 'uri');
	array_value_rawurlencode ($arr, 'tb_uri');
	array_value_settype ($arr, 'id',    'int') or die ('Bad ID');
	array_value_settype ($arr, 'size',  'int') or die ('Bad size');
	array_value_settype ($arr, 'mdate', 'int') or die ('Bad mdate');
	array_value_settype ($arr, 'type',  'int') or die ('Bad type');
	array_value_settype ($arr, 'uid',   'int') or die ('Bad UID');
	
	/* sanitize tags */
	if (array_key_exists ('tags', $arr)) {
		$tags = $arr['tags'];
		if (! is_array ($tags)) {
			$tags = explode (',', $tags);
			foreach ($tags as &$tag) {
				$tag = trim ($tag);
			}
		}
		$arr['tags'] = implode (',', $tags);
	}
	
	return $arr;
}

/*
 * \returns an array representing the media or false if it doesnt exist
 */
function media_get_by_id ($media_id)
{
	$db = new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
	$db->select_table (MEDIA_TABLE);
	
	$db->select ('*', array ('id' => $media_id));
	$media = $db->fetch_response ();
	if ($media !== false) {
		media_unescape_db_array ($media);
	}
	unset ($db);
	return $media;
}

function media_add ($uri,
                    $tb_uri,
                    $size,
                    $mdate,
                    $type,
                    $uid,
                    $tags,
                    $desc,
                    $comment)
{
	return media_set_from_values (-1, $uri, $tb_uri, $size, $mdate, $type, $uid,
	                                  $tags, $desc, $comment);
}

function media_update ($id,
                       $uri,
                       $tb_uri,
                       $size,
                       $mdate,
                       $type,
                       $uid,
                       $tags,
                       $desc,
                       $comment)
{
	return media_set_from_values ($id, $uri, $tb_uri, $size, $mdate, $type, $uid,
	                                   $tags, $desc, $comment);
}

function media_set_from_values ($id,
                                $uri,
                                $tb_uri,
                                $size,
                                $mdate,
                                $type,
                                $uid,
                                $tags,
                                $desc,
                                $comment)
{
	return media_set ($id, array ('uri'     => $uri,
	                              'tb_uri'  => $tb_uri,
	                              'size'    => $size,
	                              'mdate'   => $mdate,
	                              'type'    => $type,
	                              'uid'     => $uid,
	                              'tags'    => $tags,
	                              'desc'    => $desc,
	                              'comment' => $comment));
}

function media_set ($id, array $values)
{
	/*$valid_keys = array ('uri', 'tb_uri', 'size', 'mdate', 'type', 'uid',
	                     'tags', 'desc', 'comment');*/
	media_escape_db_array ($values);
	
	$db = new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
	$db->select_table (MEDIA_TABLE);
	if ($id < 0) {
		return $db->insert ($values);
	} else {
		return $db->update ($values, array ('id' => $id));
	}
}

function media_get_default_thumbnail_from_uri (&$uri)
{
	switch (strtolower (filename_getext ($uri)))
	{
		case 'gz':
		case 'bz2':
		case 'tar':
		case 'tgz':
		case 'tbz2':
		case 'zip':
		case '7z':
		case 'jar':
			return MEDIA_COMPRESSED_THUMBNAIL_URI;
		
		default:
			return MEDIA_UNKNOWN_THUMBNAIL_URI;
	}
}

function __media_is_default_thumbnail (&$uri)
{
	return ($uri == MEDIA_UNKNOWN_THUMBNAIL_URI ||
	        $uri == MEDIA_COMPRESSED_THUMBNAIL_URI);
}

function media_remove ($id, $rm_files=true)
{
	$db = new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
	$db->select_table (MEDIA_TABLE);
	
	$db->select (array ('uri', 'tb_uri'), array ('id' => $id));
	$media = $db->fetch_response ();
	if ($media)
	{
		$uri_n = $db->count (array ('uri' => $media['uri']));
		$tb_uri_n = $db->count (array ('tb_uri' => $media['tb_uri']));
		
		media_unescape_db_array ($media);
		if ($media['uri'] && $uri_n <= 1) {
			unlink (MEDIA_DIR_W.'/'.$media['uri']);
		}
		if ($media['tb_uri'] && $tb_uri_n <= 1 &&
		    ! __media_is_default_thumbnail ($media['tb_uri'])) {
			unlink (MEDIA_DIR_W.'/'.$media['tb_uri']);
		}
	}
	return $db->delete (array ('id' => $id));
}

/*
 * \param $type the type of medias to get (see MediaType)
 * \param $bytags whether to get an array of tags or not
 * \param $seltags an array of tags to select or null for all
 * \returns a two-dimentionnal array of medias matching type \p $type
 *          If \p $bytags is true, the array is an array of tags, and each tag
 *          is an array of medias matching the tag.
 *          Else, if \p bytags is not true, the first level array has only one
 *          key that is 0 for compatibility with the tagged array.
 *          Each media is a standard media array but with array for tags.
 *          The returned array is sorted in reverse order by default.
 * 
 * I.e:
 * $arr = media_get_array(MediaType::SCREENSHOT, true);
 * print_r ($arr['a_tag'][0]); // dump of the first media tagged 'a_tag'
 * Or:
 * $arr = media_get_array(MediaType::SCREENSHOT, false);
 * print_r ($arr[0][0]); // dump of the first media
 */
function media_get_array ($type, $bytags=false, $seltags=null)
{
	$medias = array ();
	settype ($type, 'int') or die ('$type must be integer');
	
	$db = new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
	$db->select_table (MEDIA_TABLE);
	$db->select ('*', array ('type' => $type));
	while (($resp = $db->fetch_response ()) !== false)
	{
		media_unescape_db_array ($resp);
		if ($bytags)
		{
			foreach ($resp['tags'] as $tag)
			{
				if ($seltags === null || in_array ($tag, $seltags))
					$medias[$tag][] = $resp;
			}
		}
		else
		{
			$medias[0][] = $resp;
		}
	}
	
	unset ($db);
	krsort ($medias);
	
	return $medias;
}

function media_get_medias (array $types = array (),
                           array $tags = array (),
                           array $sort = array ('type' => 'ASC',
                                                'mdate' => 'DESC'))
{
	$db = new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
	$db->select_table (MEDIA_TABLE);
	
	/* type filter (inclusive) */
	$type_match = '0';
	foreach ($types as $type) {
		$type_match .= sprintf (' OR `type`=\'%s\'', $db->escape ($type));
	}
	/* tags filter (exclusive) */
	$tags_match = '1';
	foreach ($tags as $tag) {
		$tags_match .= sprintf (' AND FIND_IN_SET(\'%s\', `tags`)', $db->escape ($tag));
	}
	
	$db->select ('*', '('.$type_match.') AND ('.$tags_match.')', $sort);
	
	if (($rows = $db->fetch_all_responses ()) !== false) {
		foreach ($rows as &$media) {
			media_unescape_db_array ($media);
		}
	}
	
	return $rows;
}

function media_get_all_tags ()
{
	$list_tags = array ();
	
	$db = new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
	$db->select_table (MEDIA_TABLE);
	$db->select (array ('tags'));
	while (($resp = $db->fetch_response ()) !== false) {
		/* FIXME: don't duplicate exploding with media_unescape_db_array() */
		$tags = explode (',', $resp['tags']);
		$list_tags = array_merge ($list_tags, $tags);
	}
	
	unset ($db);
	rsort ($list_tags);
	
	return array_unique ($list_tags);
}

function __media_print_code_snippet_textarea ($content)
{
	echo '
	<textarea readonly="readonly" rows="2" cols="32">',
		htmlentities ($content, ENT_COMPAT, 'UTF-8'),
	'</textarea>';
}

function media_print_code_snippets (array &$media)
{
	$uri = MEDIA_DIR_R.'/'.$media['uri'];
	$tb_uri = MEDIA_DIR_R.'/'.$media['tb_uri'];
	static $n = 1;
	
	?>
	<div class="code_snippet_box" id="bb_spt_<?php echo $n; ?>">
		<div class="fleft">
			<a href="#" id="bb_spt_<?php echo $n; ?>_button"
				 onclick="toggle_folding('bb_spt_<?php echo $n; ?>_button', 'bb_spt_<?php echo $n; ?>', true); return false;"
				 title="Voir les codes pour ce média">
				[-]
			</a>
		</div>
		Liste des extraits de codes pour lier ce média
		
		<div class="code_snippet_box" id="bb_spt_0<?php echo $n; ?>">
			<div class="fleft">
				<a href="#" id="bb_spt_0<?php echo $n; ?>_button"
					 onclick="toggle_folding('bb_spt_0<?php echo $n; ?>_button', 'bb_spt_0<?php echo $n; ?>', true); return false;"
					 title="Voir les codes pour ce média">
					[-]
				</a>
			</div>
			Avec une vignette
			
			<div class="code_snippet">
				BBanCode/DokuWiki&nbsp;:
				<?php
				echo
					__media_print_code_snippet_textarea (
						'[['.BSE_BASE_URL.UrlTable::medias ($media['id'], false, $media['desc']).'#watch|{{'.
						$tb_uri.'|'.$media['desc'].'}}]]'
					);
				?>
			</div>
			<div class="code_snippet">
				BBCode&nbsp;:
				<?php
					__media_print_code_snippet_textarea (
						'[url='.BSE_BASE_URL.UrlTable::medias ($media['id'], false, $media['desc']).'#watch][img]'.
						$tb_uri.'[/img][/url]'
					);
				?>
			</div>
			<div class="code_snippet">
				ZCode&nbsp;:
				<?php
					__media_print_code_snippet_textarea (
						'<lien url="'.BSE_BASE_URL.UrlTable::medias ($media['id'], false, $media['desc']).
						'#watch"><image>'.$tb_uri.'</image></lien>'
					);
				?>
			</div>
			<div class="code_snippet">
				XHTML&nbsp;:
				<?php
					__media_print_code_snippet_textarea (
						'<a href="'.BSE_BASE_URL.UrlTable::medias ($media['id'], false, $media['desc']).
						'#watch"><img src="'.$tb_uri.'" alt="'.$media['desc'].'" /></a>'
					);
				?>
			</div>
		</div>
		
		<div class="code_snippet_box" id="bb_spt_1<?php echo $n; ?>">
			<div class="fleft">
				<a href="#" id="bb_spt_1<?php echo $n; ?>_button"
					 onclick="toggle_folding('bb_spt_1<?php echo $n; ?>_button', 'bb_spt_1<?php echo $n; ?>', true); return false;"
					 title="Voir les codes pour ce média">
					[-]
				</a>
			</div>
			Directement
			
			<div class="code_snippet">
				BBanCode/DokuWiki&nbsp;:
				<?php
					__media_print_code_snippet_textarea ('[['.$uri.'|'.$media['desc'].']]');
				?>
			</div>
			<div class="code_snippet">
				BBCode&nbsp;:
				<?php
					__media_print_code_snippet_textarea ('[url='.$uri.']'.$media['desc'].'[/url]');
				?>
			</div>
			<div class="code_snippet">
				ZCode&nbsp;:
				<?php
					__media_print_code_snippet_textarea ('<lien url="'.$uri.'">'.$media['desc'].'</lien>');
				?>
			</div>
			<div class="code_snippet">
				XHTML&nbsp;:
				<?php
					__media_print_code_snippet_textarea ('<a href="'.$uri.'">'.$media['desc'].'</a>');
				?>
			</div>
		</div>
		
		<div class="code_snippet_box" id="bb_spt_2<?php echo $n; ?>">
			<div class="fleft">
				<a href="#" id="bb_spt_2<?php echo $n; ?>_button"
					 onclick="toggle_folding('bb_spt_2<?php echo $n; ?>_button', 'bb_spt_2<?php echo $n; ?>', true); return false;"
					 title="Voir les codes pour ce média">
					[-]
				</a>
			</div>
			En affichant le média
			
			<div class="code_snippet">
				BBanCode/DokuWiki&nbsp;:
				<?php
				echo
					__media_print_code_snippet_textarea (
						'[['.BSE_BASE_URL.UrlTable::medias ($media['id'], false, $media['desc']).'#watch|{{'.
						$uri.'|'.$media['desc'].'}}]]'
					);
				?>
			</div>
			<div class="code_snippet">
				BBCode&nbsp;:
				<?php
					__media_print_code_snippet_textarea (
						'[url='.BSE_BASE_URL.UrlTable::medias ($media['id'], false, $media['desc']).'#watch][img]'.
						$uri.'[/img][/url]'
					);
				?>
			</div>
			<div class="code_snippet">
				ZCode&nbsp;:
				<?php
					__media_print_code_snippet_textarea (
						'<lien url="'.BSE_BASE_URL.UrlTable::medias ($media['id'], false, $media['desc']).
						'#watch"><image>'.$uri.'</image></lien>'
					);
				?>
			</div>
			<div class="code_snippet">
				XHTML&nbsp;:
				<?php
					__media_print_code_snippet_textarea (
						'<a href="'.BSE_BASE_URL.UrlTable::medias ($media['id'], false, $media['desc']).
						'#watch"><img src="'.$uri.'" alt="'.$media['desc'].'" /></a>'
					);
				?>
			</div>
		</div>
		
	</div>
	<script type="text/javascript">
		<!--
		//toggle_folding ('bb_spt_<?php echo $n; ?>_button', 'bb_spt_<?php echo $n; ?>');
		toggle_folding ('bb_spt_0<?php echo $n; ?>_button', 'bb_spt_0<?php echo $n; ?>');
		toggle_folding ('bb_spt_1<?php echo $n; ?>_button', 'bb_spt_1<?php echo $n; ?>');
		toggle_folding ('bb_spt_2<?php echo $n; ?>_button', 'bb_spt_2<?php echo $n; ?>');
		//-->
	</script>
	<?php
	
	$n++;
}
