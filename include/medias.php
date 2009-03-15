<?php
/* LICENSE
 * 
 * BanSE - a site base (designed to be the SCEngine website)
 * Copyright (C) 2007-2008 Colomban "Ban" Wendling <ban-ubuntu@club-internet.fr>
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
require_once ('include/MyDB.php');

define (MEDIA_THUMBNAIL_WIDTH,  160.0);
define (MEDIA_THUMBNAIL_HEIGHT, 120.0);

abstract class MediaType
{
	const UNKNOWN    = 0;
	const SCREENSHOT = 1;
	const MOVIE      = 2;
	const RELEASE    = 3;
	const OTHER      = 4;
	const N_TYPES    = 5;
	
	public function to_string ($media_type)
	{
		switch ($media_type)
		{
			case self::UNKNOWN:    return 'inconnu';
			case self::SCREENSHOT: return 'image';
			case self::MOVIE:      return 'vidéo';
			case self::RELEASE:    return 'release';
			case self::OTHER:      return 'autre';
			default:               return 'type invalide';
		}
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
		media_unescape_db_array ($media);
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

function media_remove ($id, $rm_files=true)
{
	$db = &new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
	$db->select_table (MEDIA_TABLE);
	
	$db->select ('`uri`,`tb_uri`', "`id`='$id'");
	$media = $db->fetch_response ();
	if ($media)
	{
		media_unescape_db_array ($media);
		if ($media['uri'])
			unlink (MEDIA_DIR_W.'/'.$media['uri']);
		if ($media['tb_uri'])
			unlink (MEDIA_DIR_W.'/'.$media['tb_uri']);
	}
	return $db->delete ("`id`='$id'");
}

/*
 * \param $type the type of medias to get (see MediaType)
 * \returns a two-dimentionnal array of medias matching type \p $type
 *          the array is an array of tags, and each tag is an array of medias
 *          matching the tag. Each media is a standard media array.
 *          The returned array is sorted in reverse order by default.
 * 
 * I.e:
 * $arr = media_get_array_tags(MediaType::SCREENSHOT);
 * print_r ($arr['a_tag'][0]); // dump of the first media tagged 'a_tag'
 */
function media_get_array_tags ($type)
{
	$medias = array ();
	settype ($type, int) or die ('$type must be integer');
	
	$db = &new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
	$db->select_table (MEDIA_TABLE);
	$db->select ('*', '`type`=\''.$type.'\'');
	while (($resp = $db->fetch_response ()) !== false)
	{
		media_unescape_db_array ($resp);
		$tags = split (' ', $resp['tags']);
		foreach ($tags as $tag)
		{
			$medias[$tag][] = $resp;
		}
	}
	
	unset ($db);
	krsort ($medias);
	
	return $medias;
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
	
	?>
	<div class="code_snippet_box" id="bb_spt_0">
		<div class="fleft">
			<a href="#" id="bb_spt_0_button"
				 onclick="toggle_folding('bb_spt_0_button', 'bb_spt_0', true); return false;"
				 title="Voir les codes pour ce média">
				[-]
			</a>
		</div>
		Liste des extraits de codes pour lier ce média
		
		<div class="code_snippet">
			Code BBanCode/DokuWiki pour insérer un lien avec vignette vers ce média&nbsp;:
			<?php
			echo
				__media_print_code_snippet_textarea (
					'[['.BSE_BASE_URL.'medias.php?watch='.$media['id'].'#watch|{{'.
					$tb_uri.'|'.$media['desc'].'}}]]'
				);
			?>
		</div>
		<div class="code_snippet">
			Code BBCode pour insérer un lien avec vignette vers ce média&nbsp;:
			<?php
				__media_print_code_snippet_textarea (
					'[url='.BSE_BASE_URL.'medias.php?watch='.$media['id'].'#watch][img]'.
					$tb_uri.'[/img][/url]'
				);
			?>
		</div>
		<div class="code_snippet">
			Code HTML pour insrer un lien avec vignette vers ce média&nbsp;:
			<?php
				__media_print_code_snippet_textarea (
					'<a href="'.BSE_BASE_URL.'medias.php?watch='.$media['id'].
					'#watch"><img src="'.$tb_uri.'" alt="'.$media['desc'].'" /></a>'
				);
			?>
		</div>
		<div class="code_snippet">
			Code BBanCode/DokuWiki pour insérer un lien direct vers ce média&nbsp;:
			<?php
				__media_print_code_snippet_textarea ('[['.$uri.'|'.$media['desc'].']]');
			?>
		</div>
		<div class="code_snippet">
			Code BBCode pour insérer un lien direct vers ce média&nbsp;:
			<?php
				__media_print_code_snippet_textarea ('[url='.$uri.']'.$media['desc'].'[/url]');
			?>
		</div>
		<div class="code_snippet">
			Code HTML pour insrer un lien direct vers ce média&nbsp;:
			<?php
				__media_print_code_snippet_textarea ('<a href="'.$uri.'">'.$media['desc'].'</a>');
			?>
		</div>
	</div>
	<script type="text/javascript">
		<!--
		toggle_folding ('bb_spt_0_button', 'bb_spt_0');
		//-->
	</script>
	<?php
}
