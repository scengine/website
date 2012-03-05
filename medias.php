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
require_once ('lib/misc.php');
require_once ('lib/User.php');


function print_screenshot (array &$media)
{
	$uri = MEDIA_DIR_R.'/'.$media['uri'];
	
	echo '
	<a href="',$uri,'" title="',$media['desc'],'">
		<img src="',$uri,'" alt="',$media['desc'],'" style="max-width:100%;" />
	</a>';
}

function print_movie (array &$media)
{
	$uri = MEDIA_DIR_R.'/'.$media['uri'];
	$tb_uri = MEDIA_DIR_R.'/'.$media['tb_uri'];
	$type = filename_get_mime_type ($uri);
	
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
	$handlers = array (
		'image/' => 'print_screenshot',
		'video/' => 'print_movie'
	);
	$handled = false;
	$mime = filename_get_mime_type ($media['uri']);
	
	foreach ($handlers as $mimesec => $hanlder)
	{
		if (str_has_prefix ($mime, $mimesec))
		{
			$hanlder ($media);
			$handled = true;
			break;
		}
	}
	if (! $handled)
	{
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
		if (User::has_rights (ADMIN_LEVEL_MEDIA))
		{
			echo '
			<div class="admin">
				[<a href="',UrlTable::admin_medias ('edit', $media['id']),'">Edit</a>]
				[<a href="',UrlTable::admin_medias ('rm', $media['id']),'">Delete</a>]
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
			[<a href="',$uri,'">Direct link</a>]
		</div>';
		/* tags if any */
		echo '<div class="links tags">Tags: ';
		print_tag_links ($media['type'], $media['tags']);
		echo '</div>';
		/* comment if any */
		if (! empty ($media['comment']))
		{
			echo '<div class="comment"><p>',$media['comment'],'</p></div>';
		}
		
		if (User::has_rights (ADMIN_LEVEL_MEDIA))
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
		<h3>Invalid media</h3>
		<p>
		 The media you are looking for does not exist.
		</p>';
	}
	
	echo '
	<p class="links">';
	/* wether we want to return to the media page and not the previous one */
	if ($_GET['noreturn'])
	{
		print_button ('Back', UrlTable::medias ());
	}
	else
	{
		print_backbutton ('Back', UrlTable::medias ());
	}
	echo '
	</p>';
}

function print_tag_links ($type, $tags)
{
	echo '<ul>';
	if (! empty ($tags[0])) {
		foreach ($tags as $tag) {
			echo '<li><a href="',UrlTable::medias_tags (array ($type), array ($tag)),'">',$tag,'</a></li>';
		}
	} else {
		echo '<li><a href="',UrlTable::medias_tags (array ($type), array ('')),'">Untagged</a></li>';
	}
	echo '</ul>';
}

function print_medias_internal (&$medias, $bytags=false)
{
	foreach ($medias as $tag => $tagmedias)
	{
		if ($bytags)
		{
			if ($tag == '')
				$tag = 'Not tagged';
			echo '<h4 class="mediatitle">',$tag,'</h4>';
		}
		
		array_multisort_2nd ($tagmedias, 'mdate', SORT_DESC);
		
		foreach ($tagmedias as $media)
		{
			$media['uri'] = MEDIA_DIR_R.'/'.$media['uri'];
			$media['tb_uri'] = MEDIA_DIR_R.'/'.$media['tb_uri'];
			
			echo
			'<div class="mediacontainer">
				<div class="media">
					<a href="',UrlTable::medias ($media['id'], false, $media['desc']),'#watch" title="',$media['desc'],'" class="noicon">
						<img src="',$media['tb_uri'],'" alt="',$media['desc'],'" />
					</a>
				</div>
				<div class="links">
					<div class="tags">
						Tags: ';
			/* tags */
			print_tag_links ($media['type'], $media['tags']);
			echo '
					</div>
				</div>
			</div>';
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
	//	$types = explode (',', $types);
	$bytags = $showtag !== null;
	
	if (! is_array ($showtag) && $bytags) {
		$tags = explode (',', $showtag);
	} else {
		$tags = $showtag;
	}
	
	for ($type = 1; $type < MediaType::N_TYPES; $type++) {
		if ($types !== null && ! in_array ($type, $types)) {
			/* filtering by type and not matching, skip */
			continue;
		}
		
		$medias = media_get_array ($type, $bytags, $tags);
		if ($types !== null || $showtag === null || ! empty ($medias)) {
			echo '
			<h2 id="',MediaType::to_id ($type),'">
				',ucfirst (MediaType::to_string ($type, true)),'
			</h2>
			<div>';
			if (! empty ($medias)) {
				print_medias_internal ($medias, $bytags);
			} else {
				echo '<p>';
				if ($tags === null) {
					echo 'This section has no media.';
				} else {
                                    echo 'No media was found in this section for th';
                                    if (count ($tags) == 1) {
                                        echo 'is tag.';
                                    } else {
                                        echo 'ese tags.';
                                    }
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
	$types = explode (',', $_GET['type']);
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
	$tags = explode (',', $_GET['showtag']);
}


?>

	<div id="presentation">
		<h2><?php echo TITLE ?></h2>
		<p>
                   Direct access to sub-sections:
		</p>
			<ul>
				<li><a href="<?php echo UrlTable::medias (); ?>#medias_screens">Screenshots</a></li>
				<li><a href="<?php echo UrlTable::medias (); ?>#medias_movies">Videos</a></li>
			</ul>
		<p>
		</p>
		<div class="foldable" id="fld_mtt0">
			<form method="post" action="<?php echo UrlTable::medias (); ?>">
				<fieldset>
					<legend>
						Tag matching
						<a href="#" id="fld_mtt0_btn"
							 onclick="toggle_folding ('fld_mtt0_btn', 'fld_mtt0'); return false;">
							[-]
						</a>
					</legend>
					<input type="hidden" name="post" value="true" />
					<div>
						<fieldset class="noframe">
						<legend>Types:
							<span class="small">
								(Tout <a href="javascript:set_checked_by_name ('type[]', true);">check</a>/<a href="javascript:set_checked_by_name ('type[]', false);">uncheck</a>)
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
						<legend>Tags:
							<span class="small">
								(<a href="javascript:set_checked_by_name ('showtag[]', true);">Check</a>/<a href="javascript:set_checked_by_name ('showtag[]', false);">uncheck</a> all)
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
						<input type="submit" value="Match" />
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
		if (User::has_rights (ADMIN_LEVEL_MEDIA))
		{
			echo '
			<div class="admin">
				<a href="',UrlTable::admin_medias ('new'),'"><input type="button" value="Add a media"/></a>
			</div>';
		}
		
		/* watch a media if asked */
		if (isset ($_GET['watch']) && settype ($_GET['watch'], 'int'))
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
