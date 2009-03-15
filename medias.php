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

function print_media ($media_id)
{
	$media = media_get_by_id ($media_id);
	if ($media)
	{
		$uri = MEDIA_DIR_R.'/'.$media['uri'];
		$tb_uri = MEDIA_DIR_R.'/'.$media['tb_uri'];
		
		echo '
		<h3 id="watch">',$media['desc'],'</h3>
		<div class="showmediacontainer">
			<div class="media">';
		
		switch ($media['type'])
		{
			case MediaType::SCREENSHOT:
				print_screenshot ($media);
				break;
			case MediaType::MOVIE:
				print_movie ($media);
				break;
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
			?>
			<div class="bbcode_snippet" id="bb_spt_0">
				<div class="fleft">
					<a href="#" id="bb_spt_0_button"
					   onclick="toggle_folding('bb_spt_0_button', 'bb_spt_0', true); return false;"
					   title="Voir les codes pour ce média">
						[-]
					</a>
				</div>
				Code BBanCode/DokuWiki pour insérer un lien avec vignette vers ce média&nbsp;:
			<?php
			echo '
				<textarea readonly="readonly" rows="2" cols="32">[[medias.php?watch=',
					$media['id'],'#watch|{{',$tb_uri,'|',$media['desc'],'}}]]</textarea>
				Code HTML pour insrer un lien avec vignette vers ce média&nbsp;:
				<textarea readonly="readonly" rows="2" cols="32">&lt;a href="medias.php?watch=',
					$media['id'],'#watch"&gt;&lt;img src="',$tb_uri,'" alt="',
					$media['desc'],'" /&gt;&lt;/a&gt;</textarea>';
			?>
			</div>
			<script type="text/javascript">
				<!--
				toggle_folding ('bb_spt_0_button', 'bb_spt_0');
				//-->
			</script>
			<?php
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
	<p class="links">
		<a href="',basename ($_SERVER['PHP_SELF']),'"
		   onclick="window.history.back(1);return false;">
			&lArr;&nbsp;Retour
		</a>
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

function print_medias_internal ($type, $showtag=null)
{
	$tags = split (' ', $showtag);
	
	$medias = media_get_array_tags ($type);
	if (! empty ($medias))
	{
		foreach ($medias as $tag => $tagmedias)
		{
			if ($showtag !== null && ! in_array ($tag, $tags))
				continue;
			
			if ($tag == '')
				$tag = 'Non taggés';
			echo '<h4 class="mediatitle">',$tag,'</h4>';
			
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
				print_tag_links ($type, $media['tags']);
				echo '</div>';
				/* comment if any */
				if (! empty ($media['comment']))
				{
					echo '<div class="comment"><p>',$media['comment'],'</p></div>';
				}
				echo '</div>';
			}
		}
	}
	/* no media selected */
	else
	{
		echo '<p>Aucun média dans cette section</p>';
	}
}

function print_medias ($type, $tag=null)
{
	switch ($type)
	{
		case MediaType::SCREENSHOT:
			echo '
			<h2 id="screens">Screenshots</h2>';
			break;
		case MediaType::MOVIE:
			echo '
			<h2 id="movies">Vidéos</h2>';
			break;
	}
	echo '
	<div>
		',print_medias_internal ($type, $tag),'
	</div>';
}

function print_all_medias ($tag=null)
{
	print_medias (MediaType::SCREENSHOT, $tag);
	print_medias (MediaType::MOVIE, $tag);
}


require_once ('include/top.minc');


?>

	<div id="presentation">
		<h2><?php echo TITLE ?></h2>
		<p>
			Ici sont répertoriés les divers médias du moteur. Voici la liste des catégories disponibles :
		</p>
			<ul>
				<li><a href="<?php echo basename ($_SERVER['PHP_SELF']),'?type=',MediaType::SCREENSHOT/*,'#screens'*/; ?>">Screenshots</a></li>
				<li><a href="<?php echo basename ($_SERVER['PHP_SELF']),'?type=',MediaType::MOVIE/*,'#movies'*/; ?>">Vidéos</a></li>
			</ul>
		<p>
			Chaque catégorie classe ses médias en fonction de la version du moteur,
			en allant de la plus récente à la plus ancienne. Bon visionnage :o)
		</p>
	</div>

	<div id="content">
		<?php
		
		/* watch a media if asked */
		if (isset ($_GET['watch']) && settype ($_GET['watch'], integer))
		{
			print_media ($_GET['watch']);
		}
		/* print media list */
		else
		{
			$type = null;
			$tag = null;
			
			if (isset ($_GET['type']))
			{
				switch ($_GET['type'])
				{
					case MediaType::SCREENSHOT:
					case MediaType::MOVIE:
						$type = $_GET['type'];
						break;
				}
			}
			
			if (isset ($_GET['showtag']))
				$tag = $_GET['showtag'];
			
			if ($type !== null)
				print_medias ($type, $tag);
			else
				print_all_medias ($tag);
		}
		
		?>
		<div style="clear: left"></div>
	</div>

<?php

require_once ('include/bottom.minc');

?>
