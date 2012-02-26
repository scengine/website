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

define ('TITLE', 'Downloads');

require_once ('lib/UrlTable.php');
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
				$tag = 'Not tagged';
			echo '<h4 class="mediatitle">',$tag,'</h4>';
			
			array_multisort_2nd ($tagmedias, 'mdate', SORT_DESC);
			
			echo '
			<table>
				<tr>
					<th>URI</th>
					<th>Description</th>
					<th>Size</th>
					<th>Date</th>';
			if (User::has_rights (ADMIN_LEVEL_MEDIA))
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
				if (User::has_rights (ADMIN_LEVEL_MEDIA))
				{
					echo '
					<td>
						<a href="',UrlTable::admin_medias ('edit', $media['id']),'"
						   title="Edit">
							<img src="styles/',STYLE,'/edit.png" alt="Edit" />
						</a>
					</td>
					<td>
						<a href="',UrlTable::admin_medias ('rm', $media['id']),'"
						   title="Delete">
							<img src="styles/',STYLE,'/delete.png" alt="Delete" />
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
		echo '<p>This section has no media.</p>';
	}
}


require_once ('include/top.minc');

?>

<div id="presentation">
	<h2><?php echo TITLE; ?></h2>
	<p>
		<span class="u">Note:</span> the sources are no longer released
    as versionned archives. Instead, you can get the latest sources through our
    git repositories.
	</p>
</div>

<div id="content">
	<h3>Development version</h3>
	<p>
	  The latest version of the engine can be retrieved using these
    repositories:
	</p>
	<pre>git clone git://gitorious.org/scengine/utils.git
git clone git://gitorious.org/scengine/core.git
git clone git://gitorious.org/scengine/renderer-gl.git
git clone git://gitorious.org/scengine/interface.git</pre>
    <p>Mirror (not as much up-to-date as the previous one):</p>
	<pre>git clone git://git.tuxfamily.org/gitroot/scengine/utils.git
git clone git://git.tuxfamily.org/gitroot/scengine/core.git
git clone git://git.tuxfamily.org/gitroot/scengine/renderergl.git
git clone git://git.tuxfamily.org/gitroot/scengine/interface.git</pre>
	<p>
		Gitorious wiki may give you additional information:
		<a title="Gitorious Wiki page" href="https://gitorious.org/scengine/pages/Home">
https://gitorious.org/scengine/pages/Home</a>
	</p>

	<h3>Released versions</h3>
	<?php
		if (User::has_rights (ADMIN_LEVEL_MEDIA))
		{
			echo '
			<div>
				',print_button ('Add a file', UrlTable::admin_medias ('new')),'
			</div>';
		}
		
		print_downloads ();
	?>
		
</div> <!-- content ends -->

<?php
require_once ('include/bottom.minc');
