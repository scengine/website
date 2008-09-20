<?php;/* LICENSE
 * 
 * BanSE - a site base (designed to be the SCEngine website)
 * Copyright (C) 2007 Colomban "Ban" Wendling <ban-ubuntu@club-internet.fr>
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

setLocale (LC_TIME, 'fr_FR', 'fr_FR.UTF8', 'FRA');

define (DWLDIR, 'downloads/');


/**
sort a multidimantional array to one of his 2nd level keys.
@param $array_to_sort the multidimetional array to sort
@param $sort_key the key of the 2nd level that you want to be sorted by
@param $sort_direction the direction to sort. can be SORT_DESC or SORT_ASC.
*/
function array_multisort_2nd ($array_to_sort, $sort_key, $sort_direction=SORT_DESC)
{
	if (!is_array ($array_to_sort) ||
	    empty ($array_to_sort) ||
	    !is_string ($sort_key))
		return false;
	
	$sort_arr = array ();
	foreach ($array_to_sort as $id => $row)
	{
		foreach ($row as $key=>$value)
			$sort_arr[$key][$id] = $value;
	}
	
	array_multisort ($sort_arr[$sort_key], $sort_direction, SORT_REGULAR, $array_to_sort);
	
	return $array_to_sort;
}

function get_ndwl ($file) {
	$file = DWLDIR.'.'.$file;
	
	if (file_exists ($file) && is_file ($file))
		return file_get_contents ($file);
	else
		return 0;
}

function array_from_file ($file)
{
	if (!file_exists ($file))
		return false;
	
	$matches = Array ();
	$rv = Array ('file',
		'path',
		'size',
		'date',
		'name',
		'version',
		'os',
		'arch',
		'osimg'
	);
	
	$filestats = stat ($file);
	$item = substr (strrchr ($file, '/'), 1);
	
	# parsing du nom de fichier
	preg_match ('#^(.*)_(.*)-([a-z]+)(32|64|PPC)?\..*$#U', $item, $matches);
	
	$rv['file'] = $item;
	$rv['path'] = $file;
	$rv['size'] = $filestats['size'];
	$rv['date'] = $filestats['mtime'];
	$rv['name'] = ucfirst ($matches[1]);
	$rv['version'] = $matches[2];
	$rv['os'] = ucfirst ($matches[3]);
	switch ($matches[4])
	{
		case 32:
			$rv['arch'] = 'x86';
			break;
		case 64:
			$rv['arch'] = 'x86_64';
			break;
		case 'PPC':
			$rv['arch'] = 'PowerPC';
			break;
		default:
			if ($matches[3] == 'src')
				$rv['arch'] = 'all';
			else
				$rv['arch'] = '?';
	}
	
	switch ($matches[3])
	{
		case 'linux':
			$rv['osimg'] = '<img alt="Linux" title="Linux" src="images/icons/linux.png"/>';
			break;
		case 'windows':
			$rv['osimg'] = '<img alt="Windows" title="Windows" src="images/icons/win.png"/>';
			break;
		case 'macosx':
			$rv['osimg'] = '<img alt="Mac OSX" title="Mac OSX" src="images/icons/osx.png"/>';
			break;
		case 'macos':
			$rv['osimg'] = '<img alt="Mac OS" title="Mac OS" src="images/icons/osx.png"/>';
			break;
		case 'src':
			$rv['osimg'] = 'all';
			break;
		default:
			$rv['osimg'] = 'Unknown';
	}
	
	return $rv;
}

function array_from_dir ($dir)
{
	$array = array ();
	$opendir = opendir ($dir);
	if (!$opendir)
		return false;
	
	while (($item = readdir ($opendir)) !== false)
	{
		# si le fichier n'est pas . ou ..
		if (substr ($item, 0, 1) != '.')
		{
			# récupération des infos sur le fichier (taille, date de modif...)
			$array[] = array_from_file ($dir.'/'.$item);
		}
	}
	
	return $array;
}

function print_fileinfos ($file) {
	$array = array_from_file ($file);
	
	echo '
		<table>
			<caption>', $array['file'], '</caption>
			<thead>
				<tr>
					<th>Fichier</th>
					<th>Taille</th>
					<th>Date</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><a href="', $array['path'], '">', $array['file'], '</a></td>
					<td><span title="', $array['size'], ' octets">', round ($array['size']/1024/1024, 1), ' Mio</span></td>
					<td><span title="', strftime ('%d %B %Y (%T)', $array['date']), '">', date ('d/m/y', $array['date']), '</span></td>
				</tr>
			</tbody>
		</table>';
}

function print_dircontent ($arraycnt, $sortkey='date', $sortdir=SORT_ASC)
{
	$array = array_multisort_2nd ($arraycnt, $sortkey, $sortdir);
	if (!$array)
		return False;
	
	$passed = array (); # tableau allant rescenser les noms déjà passés
	$i = 0;
	foreach ($array as $pass)
	{
		# si c'est la 1ère fois qu'on affiche ce nom, on y met un id
		if (!in_array ($pass['name'], $passed))
			echo '<tr id="', strtolower ($pass['name']), '">';
		else # sinon, on respecte le W3C et ne met pas 2 fois la même ID
			echo '<tr>';
		
		$ndwl = get_ndwl ($pass['file']);
		
		echo '
			<td><a href="', $pass['path'], '">', $pass['file'], '</a></td>
			<td><span title="', $pass['size'], ' octets">', round ($pass['size']/1024, 2), ' Kio</span></td>
			<td><span title="', strftime ('%d %B %Y (%T)', $pass['date']), '">', date ('d/m/y', $pass['date']), '</span></td>
			<td>', $pass['osimg'], '</td>
			<td>', $pass['arch'], '</td>
			<td><span title="Téléchargé ', $ndwl, ' fois">', $ndwl, '</span></td>
			<td><a href="?page=', strtolower (PAGE), '&amp;action=mv&amp;file=', $pass['file'], '"><img src="styles/', STYLE, '/edit.png" alt="Éditer" title="Éditer ', $pass['file'], '" /></a></td>
			<td><a href="?page=', strtolower (PAGE), '&amp;action=rm&amp;file=', $pass['file'], '"><img src="styles/', STYLE, '/delete.png" alt="Supprimer" title="Supprimer ', $pass['file'], '" /></a></td>
		</tr>';
		
		$passed[$i] = $pass['name'];
		$i++;
	}
}

function delete_file ($file) {
	return unlink ($file);
}

function filename_is_good ($filename) {
	$rv = True;
	$quality = array (
		'', 
		'<span class="error">erreur</span>',
		'<span class="error">erreur</span>', 
		'<span class="error">erreur</span>',
		'<span class="error">erreur</span>',
		'<span class="warning">attention</span>'
	);
	
	preg_match ('#^(.*)_(.*)-([a-z]+)(32|64|PPC)?\.(.*)$#U', $filename, $matches);
	
	// vérification du nom
	if (!empty ($matches[1]))
		$quality[1] = '<span class="ok">ok</span>';
	else
		$rv = False;
	
	// vérification de la version
	if (!empty ($matches[2])) {
		if (preg_match ('#^[0-9]+#', $matches[2]))
			$quality[2] = '<span class="ok">ok</span>';
		else
			$quality[2] = '<span class="warning">No numeric value</span>';
	}
	else
		$rv = False;
	
	// vérification de l'OS
	if (!empty ($matches[3])) {
		if ($matches[3] == 'linux' ||
			 $matches[3] == 'windows' ||
			 $matches[3] == 'macosx' ||
			 $matches[3] == 'macos' ||
			 $matches[3] == 'src')
			$quality[3] = '<span class="ok">ok</span>';
		else {
			$quality[3] = '<span class="warning">Unknown OS</span>';
		}
	}
	else
		$rv = False;
	
	// architecture
	if ($matches[4] == 32 ||
		 $matches[4] == 64 ||
		 $matches[4] == 'PPC')
		$quality[4] = '<span class="ok">ok</span>';
	else if ($matches[3] == 'src' && empty ($matches[4]))
		$quality[4] = '<span class="ok">ok</span>';
	else
		$rv = False;
	
	
	// vérification de l'extension
	if ($matches[5] == 'tar.gz' ||
	    $matches[5] == 'tgz' ||
	    $matches[5] == 'tar.bz2' ||
	    $matches[5] == 'tar' ||
	    $matches[5] == 'zip')
		$quality[5] = '<span class="ok">ok</span>';
	
	echo '<table>
				<caption>Vérification de <span class="i">', $filename, '</span></caption>
				<tr>
					<th></th>
					<th>Valeur</th>
					<th>État</th>
				</tr>
				<tr>
					<th class="right">Application</th>
					<td>', $matches[1], '</td>
					<td>', $quality[1], '</td>
				</tr>
				<tr>
					<th class="right">Version</th>
					<td>', $matches[2], '</td>
					<td>', $quality[2], '</td>
				</tr>
				<tr>
					<th class="right">OS</th>
					<td>', $matches[3], '</td>
					<td>', $quality[3], '</td>
				</tr>
				<tr>
					<th class="right">Architecture</th>
					<td>', $matches[4], '</td>
					<td>', $quality[4], '</td>
				</tr>
				<tr>
					<th class="right">Extension</th>
					<td>', $matches[5], '</td>
					<td>', $quality[5], '</td>
				</tr>
				<tr>
					<th class="right">Résultat</th>
					<td>', $filename, '</td>
					<td>', ($rv) ? '<span class="ok">ok</span>' : '<span class="error">erreur</span>', '</td>
				</tr>
			</table>';
	
	return $rv;
}


/* affichage de la page : */
# un peu de JS...
/**
cette fonction sert à mettre à jour le "value" d'un élément (input) avec 
      celui d'un autre
@param id l'ID de l'élément où chercer la valeur
@param el l'élément appellant (this pour l'élément appellant)
*/
#echo '
?>
<script type="text/javascript"> <!--
	function getContent (id, el) {
		if (!el.value) { // changer uniquement si l'entrée est vide
			// Opera n'envoi que le fichier, mais Firefox, Konqueror, IE...
			// envoient toute la chaîne.
			// Il faut donc se plier aux désirs de Firefox et compagnie, on cherche
			// la portion de chaîne qui nous intéresse (entre le dernier / (ou \)
			// et la fin)
			//
			// J'emmerde Windows avec ses \ :@ Et IE simplifie pas le boulot...
			
			if (document.all) { // IE
				var elval = document.all[id].value; // on récupère la chaîne
			}
			else { // Pour Firefox, Konqueror... les navigateurs pas pourris
				var elval = document.getElementById (id).value;
			}
			
			// on récupère la platform pour connaître le délimiteur de dossier 
			//                                       (\ (Windows) ou / (Unix-like))
			if (navigator.platform == "Win32") { // Windows
				// récupration de ce qui suit le dernier \
				var newval = elval.replace(/^.*\\/, "");
			}
			else { // Linux, Unix, MacOS...
				// récupration de ce qui suit le dernier /
				var newval = elval.replace(/^.*\//, "");
			}
			
			el.value = newval; // on assigne la nouvelle valeur à l'input
		}
		
		
		// on coche la case de renommage
		if (document.all) // IE
			document.all["rename"].checked="checked";
		else // Pour Firefox, Konqueror... les navigateurs pas pourris
			document.getElementById ("rename").checked="checked";
	}
--> </script>
<?
#';


/* affichage de la page : */
echo '<div id="presentation">
			<h2>Téléchargements</h2>
		</div>
		<div id="content">';

if ($_GET['action'] == 'new') {
	echo '<h3>Ajouter un fichier</h3>';

	if ($_FILES) { // les données on été envoyées
		if ($_FILES['file']['error']) {
			echo '<p>Erreur de transfert vers le serveur. Vous pouvez réessayer 
					en revenant sur la 
					<a href="?page=', strtolower (PAGE), '&amp;action=new">
					page d\'upload</a>.</p>';
		}
		else { // s'il n'y a pas d'erreur
			$error = False; // initialisation de la avriable d'erreur
			
			// nom du fichier final :
			if ($_POST['rename'] && $_POST['newname'])
				$newname = $_POST['newname'];
			else
				$newname = $_FILES['file']['name'];
			
			
			if (!$_POST['noverify']) { // si on n'a pas activé le mode "pas de vérification"
				if (!filename_is_good ($newname)) { // si le nom du fichier n'est pas standard, on demande
					echo '<p>Le nom du fichier n\'est pas conforme aux spécifications.</p>';
					
					// on informe l'utilisateur de la structure acceptée
					echo '<p>Le nom du fichier doit avoir un squelette de type <cite>application_version-osARCH.extention</cite>.</p>
							<p>Par exemple <cite>', ENGINE, '_0.1-linux32.tar.gz</cite> sera correctement reconu. par contre <cite>', ENGINE, '_0.1-linux.tar.gz</cite> posera problème, car l\'architecture n\'et pas spécifiée.<br />
							Les architectures suivantes sont reconues&nbsp;:</p>
							
							<ul>
								<li><span class="b">32</span> (x86)</li>
								<li><span class="b">64</span> (x86_64)</li>
								<li><span class="b">PPC</span> (PowerPC)</li>
							</ul>';
					
					$error = True;
				}
			}
			
			if (strstr ($newname, '&') ||
				 strstr ($newname, '/')) {
				echo '<p>
							Le nom du fichier contient des caractères non-autorisés.
							Veuillez les retirer et réessayer.
						</p>
						<p>
							Les caractères suivants ne sont pas acceptés&nbsp;:<br />
							&amp; /
						</p>';
				$error = True;
			}
			
			
			// déplacement du fichier
			if (!$error) { // s'il n'y a pas d'erreur...
				$error = move_uploaded_file($_FILES['file']['tmp_name'], DWLDIR.'/'.$newname);
				
				if (!$error) {
					echo '<p>Erreur lors du déplacement.</p>';
					exit;
				}
				else {
					echo '<p>Le fichier «&nbsp;', $newname, '&nbsp;» a été uploadé avec succès&nbsp;!</p>';
				}
			}
			else { // on propose de passer outre les erreurs
				echo '<p>
						Des erreurs ont été rencontrées. Si vous voulez passer outre 
						la vérification des erreurs, vous devez cocher 
						"Ne pas vérifier le nom de fichier" dans le formulaire 
						d\'upload.
					</p>';
			}
			
			echo '<p class="form_buttons">
						<a href="?page=', strtolower (PAGE), '" onclick="window.location.replace(this.href);return false;">
							<input type="button" value="Retour" />
						</a>
						<a href="?page=', strtolower (PAGE), '&amp;action=new" onclick="window.location.replace(this.href);return false;">
							<input type="button" value="Retour à l\'envoi de fichier" />
						</a>
					</p>';
		}
	}
	else { // on affiche le formulaire
		echo '<p>Espace disque disponible dans le répertoire « ', DWLDIR, ' »&nbsp;: ',
			round (disk_free_space(DWLDIR)/1000/1000/1000, 2),
			' Gio (',
			round (disk_free_space(DWLDIR)/1000, 0),
			' Kio)</p>';
		
			echo '<form method="post" action="?page=', strtolower (PAGE), '&amp;action=new" enctype="multipart/form-data">
					<p>
						<input type="file" name="file" id="file" /> <br />
						<label><input type="checkbox" name="rename" id="rename"/>&nbsp;Renommer le fichier en&nbsp;:</label><br />
						<input type="text" name="newname" onclick="getContent(\'file\', this);"/><br />
						<label><input type="checkbox" name="noverify" />&nbsp;Ne pas vérifier le nom du fichier (déconseillé)</label>
					</p>
					<p class="form_buttons">
						<input type="submit" value="Envoyer" />
						<input type="reset" value="Vider" />
						<a href="?page=', strtolower (PAGE), '" onclick="window.location.replace(this.href);return false;">
							<input type="button" value="Annuler" />
						</a>
					</p>
				</form>';
	}
}
elseif ($_GET['action'] == 'rm') { // suppression
	echo '<h3>Suppression d\'un fichier</h3>';

	if ($_GET['file']) {
		if (file_exists (DWLDIR.'/'.$_GET['file'])) {
			if ($_POST['rm']) {
				if (unlink (DWLDIR.'/'.$_GET['file'])) {
					echo '<p>« ', $_GET['file'], ' » supprimé avec succès.</p>
							<p>
								<a href="?page=', strtolower (PAGE), '" onclick="window.location.replace(this.href);return false;">
									<input type="button" value="Retour" />
								</a>
							</p>';
				}
				else {
					echo '<p>Erreur lors de la suppression de « ', $_GET['file'], ' »</p>
							<p>
								<a href="?page=', strtolower (PAGE), '" onclick="window.location.replace(this.href);return false;">
									<input type="button" value="Retour" />
								</a>
							</p>';
				}
			}
			else { // demande de confirmation
				print_fileinfos (DWLDIR.'/'.$_GET['file']);
				echo '<p>Êtes-vous sûr de vouloir supprimer le fichier «&nbsp;', $_GET['file'], '&nbsp;»&nbsp;?</p>
						<form method="post" action="?page=', strtolower (PAGE), '&amp;action=rm&amp;file=', $_GET['file'], '">
							<div>
								<input type="hidden" name="rm" value="True" />
							</div>
							<p class="form_buttons">
								<input type="submit" value="Supprimer" />
								<a href="?page=', strtolower (PAGE), '" onclick="window.location.replace(this.href);return false;">
									<input type="button" value="Annuler" />
								</a>
							</p>
						</form>';
			}
		}
		else { // le fchier n'existe pas
			echo '<p>Le fichier que vous voulez supprimer n\'existe pas&nbsp;!</p>
					<p>
						<a href="?page=', strtolower (PAGE), '" onclick="window.location.replace(this.href);return false;">
							<input type="button" value="Retour" />
						</a>
					</p>';
		}
	}
	else { // aucun fichier passé en argument
		echo '<p>Aucun fichier désigné.</p>
				<p>
					<a href="?page=', strtolower (PAGE), '" onclick="window.location.replace(this.href);return false;">
						<input type="button" value="Retour" />
					</a>
				</p>';
	}
}
else if ($_GET['action'] == 'mv') {
	echo '<h3>Renommage d\'un fichier</h3>';

	if ($_GET['file']) { // si un fichier est donné
		if (file_exists (DWLDIR.'/'.$_GET['file'])) { // est-ce que le ficheir existe
			if ($_POST['newname']) { // si le nouveau nom est envoyé
				
				$error = False;
				$newname = $_POST['newname'];
				
				
				
				if (!$_POST['noverify']) { // si on n'a pas activé le mode "pas de vérification"
					if (!filename_is_good ($newname)) { // si le nom du fichier n'est pas standard, on demande
						echo '<p>Le nom du fichier n\'est pas conforme aux spécifications.</p>';
						$error = True;
					}
				}
				
				
				// les noms de fichiers n'om pas le droit aux / ou &
				if (strstr ($newname, '&') ||
					strstr ($newname, '/')) {
					echo '<p>
								Le nom du fichier contient des caractères non-autorisés.
								Veuillez les retirer et réessayer.
							</p>
							<p>
								Les caractères suivants ne sont pas acceptés&nbsp;:<br />
								&amp; /
							</p>';
					$error = True;
				}
				
				
				// déplacement du fichier
				if (!$error) { // s'il n'y a pas d'erreur...
					$error = rename (DWLDIR.'/'.$_GET['file'], DWLDIR.'/'.$newname);
					
					if (!$error) {
						echo '<p>Erreur lors du renommage.</p>';
						exit;
					}
					else {
						echo '<p>Le fichier «&nbsp;', $newname, '&nbsp;» a été renommé avec succès&nbsp;!</p>';
					}
				}
				else { // on propose de passer outre les erreurs
					echo '<p>
							Des erreurs ont été rencontrées. Si vous voulez passer outre 
							la vérification des erreurs, vous devez cocher 
							"Ne pas vérifier le nom de fichier" dans le formulaire 
							de renommage.
						</p>';
				}
				
				echo '<p class="form_buttons">
							<a href="?page=', strtolower (PAGE), '" 
								onclick="window.location.replace(this.href);return false;">
								<input type="button" value="Retour" />
							</a>
						</p>';
				
				
			}
			else { // sinon, on propose le formulaire
				echo '<form method="post" action="?page=', strtolower (PAGE), '&amp;action=mv&amp;file=', $_GET['file'], '">
							<p>
								<label>Nom actuel&nbsp;: <br />
									<input type="text" disabled="disabled" value="', $_GET['file'], '"/> <br />
								</label>
								<label>Nouveau nom&nbsp;: <br />
									<input type="text" name="newname" value="', $_GET['file'], '" /> <br />
								</label>
								<label>
									<input type="checkbox" name="noverify" />&nbsp;Ne pas vérifier le nom du fichier (déconseillé)
								</label>
							</p>
							<p class="form_buttons">
								<input type="submit" value="Renommer" />
								<input type="reset" value="Réinitialiser" />
								<a href="?page=', strtolower (PAGE), '" 
									onclick="window.location.replace(this.href);return false;">
									<input type="button" value="Annuler" />
								</a>
							</p>
						</form>';
			}
		}
		else {
			echo '<p>Le fichier sépécifié n\'esxist pas.</p>
					<p>
						<a href="?page=', strtolower (PAGE), '" onclick="window.location.replace(this.href);return false;">
							<input type="button" value="Retour" />
						</a>
					</p>';
		}
	}
	else { // s'il n'y à rien à faire
		echo '<p>Aucun fichier désigné.</p>
				<p>
					<a href="?page=', strtolower (PAGE), '" onclick="window.location.replace(this.href);return false;">
						<input type="button" value="Retour" />
					</a>
				</p>';
	}
}
else {
	echo '<p>
				<a href="?page=', strtolower (PAGE), '&amp;action=new" onclick="window.location.replace(this.href)">
					<input type="button" value="Ajouter un fichier" />
				</a>
			</p>';
	$array = array_from_dir (DWLDIR);
	echo '<table>
				<caption>Fichiers dans <span class="i">', dirname ($array[0]['path']), '/</span></caption>
				<thead>
				<tr>
					<th>Fichier</th>
					<th>Taille</th>
					<th>Date</th>
					<th><abbr title="Operating System">OS</abbr></th>
					<th>Arch</th>
					<th></th>
					<th></th>
				</tr>
				</thead>
				<tfoot>
				<tr>
					<th>Fichier</th>
					<th>Taille</th>
					<th>Date</th>
					<th><abbr title="Operating System">OS</abbr></th>
					<th>Arch</th>
					<th></th>
					<th></th>
				</tr>
				</tfoot>
				<tbody>';
	print_dircontent ($array, 'date', SORT_DESC);
	echo '   </tbody>
			</table>';
}


echo '</div>'; // fin de content
?>
