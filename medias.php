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


function print_medias ($type)
{
	$medias = media_get_array_tags ($type);
	if (! empty ($medias))
	{
		foreach ($medias as $tag => $tagmedias)
		{
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
						<a href="',$media['uri'],'" title="',$media['desc'],'" class="noicon">
							<img src="',$media['tb_uri'],'" alt="',$media['desc'],'" />
						</a>
					</div>
					<div class="links">
						[<a class="noicon" href="',$media['uri'],'" title="Voir « ',$name,' »">Voir</a>]
						[<a class="noicon" href="',$media['uri'],'" title="Lien direct vers « ',$name,' »">Lien direct</a>]
					</div>';
				/* tags if any */
				if (! empty ($media['tags']))
				{
					echo '<div class="links tags">Tags&nbsp;: ';
					$tags = split (' ', $media['tags']);
					foreach ($tags as $tag)
					{
						echo '<a href="?showtag=',$tag,'">',$tag,'</a> ';
					}
					echo '</div>';
				}
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



require_once ('include/top.minc');


?>

	<div id="presentation">
		<h2><?php echo TITLE ?></h2>
		<p>
			Ici sont répertoriés les divers médias du moteur. Voici la liste des catégories disponibles :
		</p>
			<ul>
				<li><a href="<?php echo basename ($_SERVER['PHP_SELF']) ?>#screens">Screenshots</a></li>
				<li><a href="<?php echo basename ($_SERVER['PHP_SELF']) ?>#movies">Vidéos</a></li>
			</ul>
		<p>
			Chaque catégorie classe ses médias en fonction de la version du moteur,
			en allant de la plus récente à la plus ancienne. Bon visionnage :o)
		</p>
	</div>

	<div id="content">
		<h2 id="screens">Screenshots</h2>
		<div>
			<?php print_medias (MediaType::SCREENSHOT); ?>
		</div>
		
		<h2 id="movies">Vidéos</h2>
		<div>
			<?php print_medias (MediaType::MOVIE); ?>
		</div>
		
		<div style="clear: left"></div>
	</div>

<?php

require_once ('include/bottom.minc');

?>
