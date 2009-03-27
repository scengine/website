<?php
/* LICENSE
 * 
 * BanSE - a site base (designed to be the SCEngine website)
 * Copyright (C) 2007-2009 Colomban "Ban" Wendling <ban@herbesfolles.org>
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

define (TITLE, 'Téléchargements');

require_once ('lib/User.php');
require_once ('lib/medias.php');
require_once ('lib/misc.php');


function print_downloads ()
{
	$medias = media_get_array (MediaType::RELEASE, true);
	if (! empty ($medias))
	{
		foreach ($medias as $tag => $tagmedias)
		{
			if ($tag == '')
				$tag = 'Non taggés';
			echo '<h4 class="mediatitle">',$tag,'</h4>';
			
			array_multisort_2nd ($tagmedias, 'mdate', SORT_DESC);
			
			echo '
			<table>
				<tr>
					<th>URI</th>
					<th>Description</th>
					<th>Taille</th>
					<th>Date</th>';
			if (User::get_logged ())
			{
				echo '
					<th></th>
					<th></th>';
			}
			echo '
				</tr>';
			
			foreach ($tagmedias as $media)
			{
				$name = basename ($media['uri']);
				$media['uri'] = MEDIA_DIR_R.'/'.$media['uri'];
				
				echo '
				<tr>
					<td>
						<a href="',$media['uri'],'" title="',$media['desc'],'" class="noicon">
							',$name,'
						</a>
					</td>
					<td>
						',$media['desc'],'
					</td>
					<td>
						',get_size_string ($media['size']),'
					</td>
					<td>
						',date ('d/m/Y H:i', $media['mdate']),'
					</td>';
				if (User::get_logged ())
				{
					echo '
					<td>
						<a href="admin.php?page=medias&amp;action=edit&amp;media=',$media['id'],'"
						   title="Éditer">
							<img src="styles/',STYLE,'/edit.png" alt="Éditer" />
						</a>
					</td>
					<td>
						<a href="admin.php?page=medias&amp;action=rm&amp;media=',$media['id'],'"
						   title="Supprimer">
							<img src="styles/',STYLE,'/delete.png" alt="Supprimer" />
						</a>
					</td>';
				}
				echo '
				</tr>';
			}
			
			echo '
			</table>';
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
	<h2><?php echo TITLE; ?></h2>
	<p>
		<span class="u">Avertissement :</span> dans la mesure où le moteur est en constant
		développement et que son interface est modifiée chaque jour, il n'est pas conseillé
		de s'inspirer des sources disponibles en téléchargement pour le moment,
		et encore moins de se familiariser avec les versions actuelles en vue d'utiliser
		le moteur par la suite.<br />
		En revanche je vous conseille vivement de préférer le dépôt SVN aux archives disponibles
		sur cette page, elle est très souvent moins buggée.
	</p>
</div>

<div id="content">
	<h3>Version de développement</h3>
	<p>
		Vous pouvez obtenir la version de développement en utilisant
		<a href="http://fr.wikipedia.org/wiki/Subversion_(logiciel)">SVN</a>&nbsp;:<br />
		<code>svn co svn://svn.tuxfamily.org/svnroot/scengine/scengine scengine</code>
	</p>

	<h3>Versions publiées</h3>
	<?php
		if (User::get_logged ())
		{
			echo '
			<div>
				<a href="admin.php?page=medias&amp;action=new"
				   onclick="window.location.replace (this.href); return false;">
					<input type="button" value="Ajouter un téléchargement" />
				</a>
			</div>';
		}
		
		print_downloads ();
	?>
		
</div> <!-- content ends -->

<?php
require_once ('include/bottom.minc');
?>
