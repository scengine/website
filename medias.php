<?php
/* LICENSE
 * 
 * BanSE - a site base (designed to be the SCEngine website)
 * Copyright (C) 2007-2009 Colomban "Ban" Wendling <ban-ubuntu@club-internet.fr>
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

define (TITLE, 'Médias');

require_once ('include/defines.php');
require_once ('include/medias.php');
require_once ('include/misc.php');
require_once ('include/User.php');


function print_screenshot (array &$media)
{
	$uri = MEDIA_DIR_R.'/'.$media['uri'];
	
	echo '
	<a href="',$uri,'" title="',$media['desc'],'">
		<img src="',$uri,'" alt="',$media['desc'],'" style="max-width:100%;" />
	</a>';
}

function get_video_mime_from_ext ($ext)
{
	$ext = strtolower ($ext);
	$type = 'application/octet-stream';
	
	switch (strtolower ($ext))
	{
		case 'ogm':
		case 'ogg':
		case 'ogv':
			$type = 'video/x-ogm';
			break;
		case 'mkv':
			$type = 'video/x-matroska';
			break;
		case 'flv':
			$type = 'video/x-flv';
			break;
		case 'mpg':
		case 'mpeg':
			$type = 'video/mpeg';
			break;
		case 'mp4':
		case 'mpeg4':
		case 'm4v':
			$type = 'video/mp4';
			break;
		case 'avi':
			$type = 'video/avi';
			break;
		case 'mov':
			$type = 'video/quicktime';
			break;
		case 'wmv':
			$type = 'video/x-ms-wmv';
			break;
	}
	
	return $type;
}

function print_movie (array &$media)
{
	$uri = MEDIA_DIR_R.'/'.$media['uri'];
	$tb_uri = MEDIA_DIR_R.'/'.$media['tb_uri'];
	$type = get_video_mime_from_ext (filename_getext ($uri));
	
	echo '
	<object type="',$type,'" data="',$uri,'" width="100%" height="400">
		<param name="src" value="',$uri,'"></param>
		<a href="',$uri,'">
			<img src="',$tb_uri,'" alt="',$media['desc'],'" />
		</a>
	</object>';
}

function print_by_thumbnail (array &$media)
{
	$uri = MEDIA_DIR_R.'/'.$media['uri'];
	$tb_uri = MEDIA_DIR_R.'/'.$media['tb_uri'];
	
	echo '
	<a href="',$uri,'" title="',$media['desc'],'">
		<img src="',$tb_uri,'" alt="',$media['desc'],'" style="max-width:100%;" />
	</a>';
}

/*
 * Fallback function that try to display a media by its extension
 */
function print_media_from_ext (array &$media)
{
	switch (strtolower (filename_getext ($media['uri'])))
	{
		case 'png':
		case 'jpg':
		case 'jpeg':
		case 'gif':
			print_screenshot ($media);
			break;
		
		case 'ogm':
		case 'ogg':
		case 'ogv':
		case 'mkv':
		case 'flv':
		case 'mpg':
		case 'mpeg':
		case 'mp4':
		case 'mpeg4':
		case 'm4v':
		case 'avi':
		case 'mov':
		case 'wmv':
			print_movie ($media);
			break;
		
		default:
			//echo 'Not implemented yet';
			print_by_thumbnail ($media);
	}
}

function print_media ($media_id)
{
	$media = media_get_by_id ($media_id);
	if ($media)
	{
		$uri = MEDIA_DIR_R.'/'.$media['uri'];
		$tb_uri = MEDIA_DIR_R.'/'.$media['tb_uri'];
		
		echo '
		<h3 id="watch">',$media['desc'],'</h3>
		<div class="showmediacontainer">';
		if (User::get_logged ())
		{
			echo '
			<div class="admin">
				[<a href="admin.php?page=medias&amp;action=edit&amp;media=',$media['id'],'">Éditer</a>]
				[<a href="admin.php?page=medias&amp;action=rm&amp;media=',$media['id'],'">Supprimer</a>]
			</div>';
		}
		echo '
			<div class="media">';
		
		switch ($media['type'])
		{
			case MediaType::SCREENSHOT:
				print_screenshot ($media);
				break;
			case MediaType::MOVIE:
				print_movie ($media);
				break;
			default:
				print_media_from_ext ($media);
		}
		
		echo '
		</div>
		<div class="links">
			[<a href="',$uri,'">Lien direct</a>]
		</div>';
		/* tags if any */
		echo '<div class="links tags">Tags&nbsp;: ';
		print_tag_links ($media['type'], $media['tags']);
		echo '</div>';
		/* comment if any */
		if (! empty ($media['comment']))
		{
			echo '<div class="comment"><p>',$media['comment'],'</p></div>';
		}
		
		if (User::get_logged ())
		{
			media_print_code_snippets ($media);
		}
		
		/* fin du showmediacontainer */
		echo '
		</div>';
	}
	else
	{
		echo '
		<h3>Média invalide</h3>
		<p>
			Le média que vous avez demandé n\'existe pas.
		</p>';
	}
	
	echo '
	<p class="links">';
	/* wether we want to return to the media page and not the previous one */
	if ($_GET['noreturn'])
	{
		echo '
		<a href="',basename ($_SERVER['PHP_SELF']),'">
			Retour
		</a>';
	}
	else
	{
		echo '
		<a href="',basename ($_SERVER['PHP_SELF']),'"
			onclick="window.history.back(1);return false;">
			&lArr;&nbsp;Retour
		</a>';
	}
	echo '
	</p>';
}

function print_tag_links ($type, $taglist)
{
	$tagged = true;
	
	if (! empty ($taglist))
	{
		$tags = split (' ', $taglist);
		$n_tags = count ($tags);
		for ($i = 0; $i < $n_tags; $i++)
		{
			echo '<a href="?type=',$type,'&amp;showtag=',$tags[$i],'">',$tags[$i],'</a>';
			if ($i < ($n_tags -1))
				echo ', ';
		}
	}
	else
	{
		$tagged = false;
		echo '<a href="?type=',$type,'&amp;showtag=">Non taggé</a>';
	}
	
	return $tagged;
}

function print_medias_internal (&$medias, $bytags=false)
{
	foreach ($medias as $tag => $tagmedias)
	{
		if ($bytags)
		{
			if ($tag == '')
				$tag = 'Non taggés';
			echo '<h4 class="mediatitle">',$tag,'</h4>';
		}
		
		array_multisort_2nd ($tagmedias, 'mdate', SORT_DESC);
		
		foreach ($tagmedias as $media)
		{
			$name = basename ($media['uri']);
			$media['uri'] = MEDIA_DIR_R.'/'.$media['uri'];
			$media['tb_uri'] = MEDIA_DIR_R.'/'.$media['tb_uri'];
			
			echo
			'<div class="mediacontainer">
				<div class="media">
					<a href="?watch=',$media['id'],'#watch" title="',$media['desc'],'" class="noicon">
						<img src="',$media['tb_uri'],'" alt="',$media['desc'],'" />
					</a>
				</div>
				<div class="links">
					[<a class="noicon" href="?watch=',$media['id'],'" title="Voir « ',$name,' »">Voir</a>]
					[<a class="noicon" href="',$media['uri'],'" title="Lien direct vers « ',$name,' »">Lien direct</a>]
				</div>';
			/* tags if any */
			echo '<div class="links tags">Tags&nbsp;: ';
			print_tag_links ($media['type'], $media['tags']);
			echo '</div>';
			/* comment if any *//*
			if (! empty ($media['comment']))
			{
				echo '<div class="comment"><p>',$media['comment'],'</p></div>';
			}*/
			echo '</div>';
		}
	}
}

/*
 * \param $types mixed: array of types or string of comma-separated types to show
 * \param $tag   tags to filter with
 */
function print_medias ($types, $showtag=null)
{
	//if (! is_array ($types) && $types !== null)
	//	$types = split (',', $types);
	$bytags = $showtag !== null;
	
	if (! is_array ($showtag) && $showtag !== null)
		$tags = split (' ', $showtag);
	else
		$tags = $showtag;
	
	for ($type = 1; $type < MediaType::N_TYPES; $type++)
	//foreach ($types as $type)
	{
		$medias = media_get_array ($type, $bytags, $tags);
		if (($types !== null || ($showtag === null || ! empty ($medias))) &&
		    ($types === null || in_array ($type, $types)))
		{
			echo '
			<h2 id="',MediaType::to_id ($type),'">
				',ucfirst (MediaType::to_string ($type, true)),'
			</h2>
			<div>';
			if (! empty ($medias))
				print_medias_internal ($medias, $bytags);
			else
			{
				echo '<p>';
				if ($tags === null)
					echo 'Aucun média dans cette section.';
				else
				{
					$s = (count ($tags) == 1) ? '' : 's';
					
					echo 'Aucun média pour ce',$s,' tag',$s,' dans cette section.';
				}
				echo '</p>';
			}
			echo '</div>';
		}
	}
}

function print_all_medias ($tag=null)
{
	print_medias (MediaType::SCREENSHOT, $tag);
	print_medias (MediaType::MOVIE, $tag);
}


/******************************************************************************/

require_once ('include/top.minc');

/* filtering */
$types = null;
$tags = null;

/* types */
if (isset ($_POST['post']))
{
	if (isset ($_POST['type']))
	{
		$types = $_POST['type'];
	}
}
else if (isset ($_GET['type']))
{
	$types = split (',', $_GET['type']);
}
else
{
	/* Defaults to screenshots & movies */
	$types = array (MediaType::SCREENSHOT, MediaType::MOVIE);
}

/* tags */
if (isset ($_POST['showtag']))
{
	$tags = $_POST['showtag'];
}
else if (isset ($_GET['showtag']))
{
	$tags = split (' ', $_GET['showtag']);
}


?>

	<div id="presentation">
		<h2><?php echo TITLE ?></h2>
		<p>
			Ici sont répertoriés les divers médias du moteur. Voici la liste des catégories disponibles :
		</p>
			<ul>
				<li><a href="<?php echo basename ($_SERVER['PHP_SELF']); ?>#medias_screens">Screenshots</a></li>
				<li><a href="<?php echo basename ($_SERVER['PHP_SELF']); ?>#medias_movies">Vidéos</a></li>
			</ul>
		<p>
			Chaque catégorie classe ses médias en fonction de la version du moteur,
			en allant de la plus récente à la plus ancienne. Bon visionnage :o)
		</p>
		<div class="foldable" id="fld_mtt0">
			<form method="post" action="<?php echo basename (__FILE__); ?>">
				<fieldset>
					<legend>
						Filtrage des médias…
						<a href="#" id="fld_mtt0_btn"
							 onclick="toggle_folding ('fld_mtt0_btn', 'fld_mtt0'); return false;">
							[-]
						</a>
					</legend>
					<input type="hidden" name="post" value="true" />
					<div>
						<fieldset class="noframe">
						<legend>Types&nbsp;:
							<span class="small">
								(Tout <a href="javascript:set_checked_by_name ('type[]', true);">cocher</a>/<a href="javascript:set_checked_by_name ('type[]', false);">décocher</a>)
							</span>
						</legend>
						<?php
							for ($type = 1; $type < MediaType::N_TYPES; $type++)
							{
								echo '
								<label>
									<input type="checkbox" name="type[]" value="',$type,'" ';
								if ($types !== null && in_array ($type, $types))
									echo 'checked="checked" ';
								echo '/>',
									MediaType::to_string ($type),'
								</label>';
							}
						?>
						</fieldset>
						<fieldset class="noframe">
						<legend>Tags&nbsp;:
							<span class="small">
								(Tout <a href="javascript:set_checked_by_name ('showtag[]', true);">cocher</a>/<a href="javascript:set_checked_by_name ('showtag[]', false);">décocher</a>)
							</span>
						</legend>
						<?php
							$list_tags = media_get_all_tags ();
							foreach ($list_tags as $list_tag => $list_tag_name)
							{
								echo '
								<label>
									<input type="checkbox" name="showtag[]" value="',$list_tag,'" ';
								if ($tags !== null && in_array ($list_tag, $tags))
									echo 'checked="checked" ';
								echo '
									/>',
									$list_tag_name,'
								</label>';
							}
						?>
						</fieldset>
					</div>
					<div class="form_buttons">
						<input type="submit" value="Filtrer" />
					</div>
				</fieldset>
				<script type="text/javascript">
					<!--
					toggle_folding ('fld_mtt0_btn', 'fld_mtt0');
					//-->
				</script>
			</form>
		</div>
	</div>

	<div id="content">
		<?php
		if (User::get_logged ())
		{
			echo '
			<div class="admin">
				[<a href="admin.php?page=medias&amp;action=new">Ajouter un média</a>]
			</div>';
		}
		
		/* watch a media if asked */
		if (isset ($_GET['watch']) && settype ($_GET['watch'], integer))
		{
			print_media ($_GET['watch']);
		}
		/* print media list */
		else
		{
			//if ($types !== null)
				print_medias ($types, $tags);
			//else
			//	print_all_medias ($tags);
		}
		
		?>
		<div style="clear: left"></div>
	</div>

<?php

require_once ('include/bottom.minc');

?>
