<?php
/* LICENSE
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

define (TITLE, 'Téléchargements');
define (DWLDIR, 'downloads/');

// sert à compter le nombre de dwl
function update_ndwl ($f) {
	$n = 0;
	$file = DWLDIR.'.'.$f; // fichier contenant le nombre

	if (file_exists ($file)) {
		$n = file_get_contents ($file);
		$n++; // un dwl en +
		file_put_contents ($file, $n);
	}
	else {
		$n++; // un dwl en +
		
		$fo = fopen ($file, 'w');
		if ($fo) {
			fwrite ($fo, $n);
			fclose ($fo);
		}
	}
}

// si l'utilisateur demande un fichier
if ($_GET['f']) {
	// le fichier demandé
	$f = urldecode ($_GET['f']);

	// si le fichier existe est n'est pas un fichier caché, on l'envoi à l'utilisateur
	if (is_file (DWLDIR.$f) && substr ($f, 0, 1) != '.') {
		update_ndwl ($f);
		
		$matches = array ();
		// on récupère l'extension pour changerle type de fichier
		preg_match ('#\.(.{1,5})$#U', $f, $matches);
		
		// selon le type de fichier, on change le header
		switch ($matches[1]) {
			case 'gz' :
			case 'bz2':
			case 'zip':
			case 'deb':
			case 'rpm':
				header ('Content-type: application/x-archive'); // archive
				break;
			case 'txt':
				header ('Content-type: text/plain'); // plein texte
				break;
			case 'png':
				header ('Content-type: image/png'); // image png
				break;
			case 'jpeg':
			case 'jpg' :
			case 'jpe' :
				header ('Content-type: image/jpeg'); // image jpeg
				break;
			default: 
				header ('Content-type: application/octet-stream'); // autre chose
		}
		
		// on nomme le fichier de sortie
		header ('Content-Disposition: attachment; filename="'.$f.'"');
		
		// et on ajoute le fichier demandé
		readfile (DWLDIR.$f);
		
		// on quite (pour ne pas voir la page qui suit)
		exit (0);
	}
}


/**
sort a multidimantional array to one of his 2nd level keys.
@param $array_to_sort the multidimetional array to sort
@param $sort_key the key of the 2nd level that you want to be sorted by
@param $sort_direction the direction to sort. can be SORT_DESC or SORT_ASC.
*/
function array_multisort_2nd ($array_to_sort, $sort_key, $sort_direction=SORT_DESC) {
	if(!is_array ($array_to_sort) ||
		empty ($array_to_sort) ||
		!is_string ($sort_key)) {
		
		return false;
	}

	$sort_arr = array ();
	foreach ($array_to_sort as $id => $row) {
		foreach ($row as $key=>$value) {
			$sort_arr[$key][$id] = $value;
		}
	}

	array_multisort ($sort_arr[$sort_key], $sort_direction, SORT_REGULAR, $array_to_sort);

	return $array_to_sort;
}

function array_from_file ($file) {
	if (!file_exists ($file))
		return False;

	$matches = Array ();
	$rv = Array (
		'file',
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
	switch ($matches[4]) {
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

	switch ($matches[3]) {
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

function array_from_dir ($dir) {
	$i = 0;
	$array = array ();
	$opendir = opendir ($dir);
	if (!$opendir)
		return False;

	while ($item = readdir ($opendir)) {
		# si le fichier n'est pas . ou ..
		if (substr ($item, 0, 1) != '.') {
			# récupération des infos sur le fichier (taille, date de modif...)
			$array[$i] = array_from_file ($dir.'/'.$item);
			
			$i++;
		}
	}

	return $array;
}


require_once ('include/top.minc');

?>

<div id="presentation">
	<h2><?php echo TITLE; ?></h2>
	<p>
		<span class="u">Avertissement :</span> aucune version du moteur n'est sortie officiellement !<br />
		Dans la mesure où le moteur est en constant développement et que son interface
		est modifiée chaque jour, il n'est pas conseillé de s'inspirer des sources
		disponibles en téléchargement pour le moment, et encore moins de se familiariser avec
		cette première ébauche en vue d'utiliser le moteur par la suite. La plupart des fichiers disponibles
		servent principalement à tester le module de téléchargements du site.<br />
		Toutefois, cela ne dispense pas les fichiers en téléchargement d'être soumis à la licence GNU GPL,
		vous serez donc prié de bien vouloir la respecter dans le cadre d'une utilisation,
		totale ou partielle, des fichiers téléchargés. Pour plus d'informations sur la licence du moteur,
		rendez-vous sur la page <a href="licence.php">licence</a> du site.<br />
		Merci de votre compréhension.
	</p>
</div>

<div id="content">
	<h3>Version de développement</h3>
	Vous pouvez obtenir la version de développement en utilisant
	<a href="http://fr.wikipedia.org/wiki/Git">Git</a>&nbsp;:<br />
	<code>git clone git://git.tuxfamily.org/gitroot/scengine/scengine.git master</code>
	
	<h3>Versions publiées</h3>
	<?php
		$versions = array ();
		$i = 0; $j = 0;
		
		$array = array_from_dir (DWLDIR);
		
		// s'il y a des fichiers à proposer
		if (!empty ($array)) {
			// on classe les fichiers
			$array = array_multisort_2nd ($array, 'os', SORT_ASC);
			
			// on récupre les versions disponibles
			foreach ($array as $file) {
				if (!in_array ($file['version'], $versions)) {
					$versions[$i] = $file['version'];
					$i++;
				}
			}
			
			// tri des version par ordre décroissant
			rsort ($versions);
			
			// affichage des tableaux pour les versions
			foreach ($versions as $version) {
				echo '<table>
							<caption id="v',str_replace (array('.','-'), '_',$version),'">Version ', $version, '</caption>
							<tr>
								<th>Fichier</th>
								<th><abbr title="Operating System">OS</abbr> (arch <a href="#arch" title="Qu\'est-ce que c\'est ?">[?]</a>)</th>
								<th>Taille</th>
								<th>Date</th>
							</tr>';
				foreach ($array as $file) {
					if ($file['version'] == $version) {
						echo '<tr>';
						echo '<td><a href="?f=', $file['file'], '">', $file['file'], '</a></td>';
						echo '<td>', $file['osimg'], ' (', $file['arch'], ')</td>';
						echo '<td>', round ($file['size']/1024.0, 2), ' Kio</td>';
						echo '<td>', date ('d/m/Y', $file['date']), '</td>';
						echo '</tr>';
					}
				}
				echo '</table>';
			}
		} // empty ($array)
		else {
			echo '<p>Aucun fichier n\'est disponible pour le moment.</p>';
		}
	?>
		
</div> <!-- content -->

<?php
require_once ('include/bottom.minc');
?>
