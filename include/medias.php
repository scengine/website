<?php
/* LICENSE
 * 
 * BanSE - a site base (designed to be the SCEngine website)
 * Copyright (C) 2009 Colomban "Ban" Wendling <ban@herbesfolles.org>
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
require_once ('include/string.php');
require_once ('include/MyDB.php');

define (MEDIA_THUMBNAIL_WIDTH,  160.0);
define (MEDIA_THUMBNAIL_HEIGHT, 120.0);
/* default thumbnails if none */
define (MEDIA_UNKNOWN_THUMBNAIL_URI,    'default/media_unknown.png');
define (MEDIA_COMPRESSED_THUMBNAIL_URI, 'default/media_compressed.png');


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
		MediaType::UNKNOWN    => 'inconnu',
		MediaType::SCREENSHOT => 'screenshot',
		MediaType::MOVIE      => 'vidéo',
		MediaType::RELEASE    => 'release',
		MediaType::NEWS       => 'news',
		MediaType::OTHER      => 'autre',
		MediaType::N_TYPES    => 'type invalide'
	);
	protected static $names_plurials = array (
		MediaType::UNKNOWN    => 'inconnus',
		MediaType::SCREENSHOT => 'screenshots',
		MediaType::MOVIE      => 'vidéos',
		MediaType::RELEASE    => 'releases',
		MediaType::NEWS       => 'news',
		MediaType::OTHER      => 'autres',
		MediaType::N_TYPES    => 'type invalides'
	);
	
	private function calibrate_media_type (&$media_type)
	{
		if (! settype ($media_type, integer) ||
		    $media_type > MediaType::N_TYPES || $media_type < 0)
		{
			$media_type = MediaType::N_TYPES;
		}
		
		return $media_type;
	}
	
	public function to_string ($media_type, $plurial = false)
	{
		self::calibrate_media_type ($media_type);
		
		if ($plurial)
			return self::$names_plurials[$media_type];
		else
			return self::$names[$media_type];
	}
	
	public function to_id ($media_type)
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
	$arr['uri']     = stripslashes (rawurldecode ($arr['uri']));
	$arr['tb_uri']  = stripslashes (rawurldecode ($arr['tb_uri']));
	$arr['tags']    = stripslashes ($arr['tags']);
	$arr['desc']    = stripslashes ($arr['desc']);
	$arr['comment'] = stripslashes ($arr['comment']);
	
	return $arr;
}

/**
 * Given a array of database reponse, escape fields that needs it.
 * it is uset to ensure it is safe to put value into the DB
 * \warning Calling this function twice with the same array will escape all two
 *          times
 */
function media_escape_db_array (array &$arr)
{
	$arr['uri']     = rawurlencode (addslashes ($arr['uri']));
	$arr['tb_uri']  = rawurlencode (addslashes ($arr['tb_uri']));
	$arr['tags']    = addslashes ($arr['tags']);
	$arr['desc']    = addslashes ($arr['desc']);
	$arr['comment'] = addslashes ($arr['comment']);
	settype ($arr['id'],    integer) or die ('Bad ID');
	settype ($arr['size'],  integer) or die ('Bad size');
	settype ($arr['mdate'], integer) or die ('Bad mdate');
	settype ($arr['type'],  integer) or die ('Bad type');
	settype ($arr['uid'],   integer) or die ('Bad UID');
	
	return $arr;
}

/*
 * \returns an array representing the media or false if it doesnt exist
 */
function media_get_by_id ($media_id)
{
	$db = &new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
	$db->select_table (MEDIA_TABLE);
	
	$db->select ('*', '`id`=\''.$media_id.'\'');
	$media = $db->fetch_response ();
	if ($media !== false)
	{
		/* FIXME: would be cool in a certain way but needs some work to compatibilize callers */
		/*
		$media['tags'] = split (' ', $media['tags']);
		sort ($media['tags']);
		*/
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
	$valid_keys = array ('uri', 'tb_uri', 'size', 'mdate', 'type', 'uid',
	                     'tags', 'desc', 'comment');
	media_escape_db_array ($values);
	
	$db = &new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
	$db->select_table (MEDIA_TABLE);
	if ($id < 0)
	{
		$query = "'','${values['uri']}','${values['tb_uri']}','${values['size']}',".
		         "'${values['mdate']}','${values['type']}','${values['uid']}',".
		         "'${values['tags']}','${values['desc']}','${values['comment']}'";
		return $db->insert ($query);
	}
	else
	{
		$query = "`uri`='${values['uri']}', `tb_uri`='${values['tb_uri']}', `size`='${values['size']}', ".
		         "`mdate`='${values['mdate']}', `type`='${values['type']}', `uid`='${values['uid']}', ".
		         "`tags`='${values['tags']}', `desc`='${values['desc']}', `comment`='${values['comment']}'";
		return $db->update ($query, "`id`='$id'");
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
	$db = &new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
	$db->select_table (MEDIA_TABLE);
	
	$db->select ('`uri`,`tb_uri`', "`id`='$id'");
	$media = $db->fetch_response ();
	if ($media)
	{
		$uri_n = $db->count ("`uri`='${media['uri']}'");
		$tb_uri_n = $db->count ("`tb_uri`='${media['tb_uri']}'");
		
		media_unescape_db_array ($media);
		if ($media['uri'] && $uri_n <= 1)
		{
			unlink (MEDIA_DIR_W.'/'.$media['uri']);
		}
		if ($media['tb_uri'] && $tb_uri_n <= 1 &&
		    ! __media_is_default_thumbnail ($media['tb_uri']))
		{
			unlink (MEDIA_DIR_W.'/'.$media['tb_uri']);
		}
	}
	return $db->delete ("`id`='$id'");
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
	settype ($type, int) or die ('$type must be integer');
	
	$db = &new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
	$db->select_table (MEDIA_TABLE);
	$db->select ('*', '`type`=\''.$type.'\'');
	while (($resp = $db->fetch_response ()) !== false)
	{
		media_unescape_db_array ($resp);
		$resp['tags'] = split (' ', $resp['tags']);
		sort ($resp['tags']);
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

function media_get_all_tags ()
{
	$list_tags = array ();
	
	$db = &new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
	$db->select_table (MEDIA_TABLE);
	$db->select ('*');
	while (($resp = $db->fetch_response ()) !== false)
	{
		media_unescape_db_array ($resp);
		$tags = split (' ', $resp['tags']);
		foreach ($tags as $tag)
		{
			$list_tags[$tag] = ($tag === '') ? 'Non taggé' : $tag;
		}
	}
	
	unset ($db);
	krsort ($list_tags);
	
	return $list_tags;
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
						'[['.BSE_BASE_URL.'medias.php?watch='.$media['id'].'#watch|{{'.
						$tb_uri.'|'.$media['desc'].'}}]]'
					);
				?>
			</div>
			<div class="code_snippet">
				BBCode&nbsp;:
				<?php
					__media_print_code_snippet_textarea (
						'[url='.BSE_BASE_URL.'medias.php?watch='.$media['id'].'#watch][img]'.
						$tb_uri.'[/img][/url]'
					);
				?>
			</div>
			<div class="code_snippet">
				ZCode&nbsp;:
				<?php
					__media_print_code_snippet_textarea (
						'<lien url="'.BSE_BASE_URL.'medias.php?watch='.$media['id'].
						'#watch"><image>'.$tb_uri.'</image></lien>'
					);
				?>
			</div>
			<div class="code_snippet">
				XHTML&nbsp;:
				<?php
					__media_print_code_snippet_textarea (
						'<a href="'.BSE_BASE_URL.'medias.php?watch='.$media['id'].
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
						'[['.BSE_BASE_URL.'medias.php?watch='.$media['id'].'#watch|{{'.
						$uri.'|'.$media['desc'].'}}]]'
					);
				?>
			</div>
			<div class="code_snippet">
				BBCode&nbsp;:
				<?php
					__media_print_code_snippet_textarea (
						'[url='.BSE_BASE_URL.'medias.php?watch='.$media['id'].'#watch][img]'.
						$uri.'[/img][/url]'
					);
				?>
			</div>
			<div class="code_snippet">
				ZCode&nbsp;:
				<?php
					__media_print_code_snippet_textarea (
						'<lien url="'.BSE_BASE_URL.'medias.php?watch='.$media['id'].
						'#watch"><image>'.$uri.'</image></lien>'
					);
				?>
			</div>
			<div class="code_snippet">
				XHTML&nbsp;:
				<?php
					__media_print_code_snippet_textarea (
						'<a href="'.BSE_BASE_URL.'medias.php?watch='.$media['id'].
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
