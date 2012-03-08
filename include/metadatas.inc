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

require_once ('include/defines.php');
require_once ('lib/Html.php');
require_once ('lib/Metadata.php'); /* gives MDI instance */

//$md = new Metadata (METADATA_FILE);


if (User::has_rights (ADMIN_LEVEL_METADATA)) {
	/* si on doit enregistrer les données */
	if (isset ($_POST['name'])) {
		$MDI->set_name ($_POST['name']);
		$MDI->set_version ($_POST['version']);
		$MDI->set_license ($_POST['license']);
		
		for ($i=0; true; $i++) {
			/* hack to pass NEW author into this loop */
			if (! isset ($_POST['author'.$i]))
				$i = 'NEW';
			
			$author = stripslashes ($_POST['author'.$i]);
			
			if (isset ($_POST['dev'.$i]))     $MDI->add_authors ($author);
			else                              $MDI->remove_authors ($author);
			if (isset ($_POST['doc'.$i]))     $MDI->add_documenters ($author);
			else                              $MDI->remove_documenters ($author);
			if (isset ($_POST['trans'.$i]))   $MDI->add_translators ($author);
			else                              $MDI->remove_translators ($author);
			if (isset ($_POST['graph'.$i]))   $MDI->add_graphists ($author);
			else                              $MDI->remove_graphists ($author);
			if (isset ($_POST['contrib'.$i])) $MDI->add_contributors ($author);
			else                              $MDI->remove_contributors ($author);
			
			/* see on top of this loop for the hack explanation */
			if ($i === 'NEW') break;
		}
		
		if ($_POST['update_mdate'])
			$MDI->update_mdate ();
		$MDI->update_metadata_mdate ();
		
		$MDI->save ();
	}
}



define ('TEAM_AUTH',    1<<1);
define ('TEAM_DOC',     1<<2);
define ('TEAM_TRANS',   1<<3);
define ('TEAM_GRAPH',   1<<4);
define ('TEAM_CONTRIB', 1<<5);

$team = array ();

function add_to_cat ($name, $cat)
{
	global $team;
	
	if (! isset ($team[$name]))
		$team[$name] = $cat;
	else
		$team[$name] |= $cat;
}

function load_team (&$MDI)
{
	/* load team */
	foreach ($MDI->get_authors () as $name)
		add_to_cat ($name, TEAM_AUTH);
	foreach ($MDI->get_documenters () as $name)
		add_to_cat ($name, TEAM_DOC);
	foreach ($MDI->get_translators () as $name)
		add_to_cat ($name, TEAM_TRANS);
	foreach ($MDI->get_contributors () as $name)
		add_to_cat ($name, TEAM_CONTRIB);
	foreach ($MDI->get_graphists () as $name)
		add_to_cat ($name, TEAM_GRAPH);
}

load_team ($MDI);
//print_r ($team);


function print_team_table (array &$team, $project) {
	echo '<table>
		<caption>Membres de l\'équipe du projet ',$project,'</caption>
		<thead>
			<tr>
				<th>Nom</th>
				<th title="Développeur">Dev</th>
				<th title="Auteur de la documentation">Doc</th>
				<th title="Traducteur">Trad</th>
				<th title="Graphiste">Graph</th>
				<th title="Contributeur">Contrib</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th>Nom</th>
				<th title="Développeur">Dev</th>
				<th title="Auteur de la documentation">Doc</th>
				<th title="Traducteur">Trad</th>
				<th title="Graphiste">Graph</th>
				<th title="Contributeur">Contrib</th>
			</tr>
		</tfoot>
		<tbody>';
	
	$i = 0;
	foreach ($team as $name => $flags) {
		echo '<tr>
			<td><input type="text" name="author',$i,'" value="',Html::escape ($name),'" /></td>
			<td><input type="checkbox" name="dev',$i,'"',($flags & TEAM_AUTH)?' checked="checked"':'',' /></td>
			<td><input type="checkbox" name="doc',$i,'"',($flags & TEAM_DOC)?' checked="checked"':'',' /></td>
			<td><input type="checkbox" name="trans',$i,'"',($flags & TEAM_TRANS)?' checked="checked"':'',' /></td>
			<td><input type="checkbox" name="graph',$i,'"',($flags & TEAM_GRAPH)?' checked="checked"':'',' /></td>
			<td><input type="checkbox" name="contrib',$i,'"',($flags & TEAM_CONTRIB)?' checked="checked"':'',' /></td>
		</tr>';
		
		$i++;
	}
	
	echo '<tr title="Rennomez cet utilisateur et assignez-le à une catégorie pour l\'ajouer">
		<td><input type="text" name="authorNEW" value="Nouveau membre" onclick="if (this.value==\'Nouveau membre\') this.value=\'\';" /></td>
		<td><input type="checkbox" name="devNEW" /></td>
		<td><input type="checkbox" name="docNEW" /></td>
		<td><input type="checkbox" name="transNEW" /></td>
		<td><input type="checkbox" name="graphNEW" /></td>
		<td><input type="checkbox" name="contribNEW" /></td>
	</tr>';
	
	echo '</tbody></table>';
}


/* affichage de la page */
?>
		<div id="presentation">
			<h2>Gestion des métadonnées</h2>
			<p>
				Gestion des métadonnées associées au projet
			</p>
		</div>
		<div id="content">
<?php
if (User::has_rights (ADMIN_LEVEL_METADATA)) {
?>
			<form action="<?php echo UrlTable::admin (strtolower (PAGE)); ?>" method="post">
				<div>
					<label>Nom du projet&nbsp;:<br /><input type="text" name="name" value="<?php echo Html::escape ($MDI->get_name ()); ?>" /></label><br />
					<label>Version du projet&nbsp;:<br /><input type="text" name="version" value="<?php echo Html::escape ($MDI->get_version ()); ?>" /></label><br />
					<label>Licence du projet&nbsp;:<br /><input type="text" name="license" value="<?php echo Html::escape ($MDI->get_license ()); ?>" /></label><br />
					<label><input type="checkbox" name="update_mdate" checked="checked" /> Mettre à jour la date de modification du projet</label><br />
					<?php print_team_table ($team, $MDI->get_name ()); ?>
				</div>
				<p class="form_buttons">
					<input type="submit" value="Appliquer" />
					<!--input type="reset" value="Réinitialiser" /-->
				</p>
			</form>
<?php
}
/* if no sufficients rights */
else
{
?>
			<p>
				Vous n'avez pas les droits suffisants pour modifier les métadonnées.
			</p>
<?php
}
?>
		</div>
