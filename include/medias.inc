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

define (PAGE, 'medias');


require_once ('include/misc.php');
require_once ('include/MyDB.php');
require_once ('include/medias.php');


function print_return_link ($button=false)
{
	if (! $button)
	{
		echo '
		<p>
			<a href="?page=',PAGE,'">&lArr; Retour à la gestion des médias</a>
		</p>';
	}
	else
	{
		echo '
		<a href="?page=',PAGE,'" onclick="window.location.replace(this.href)">
			<input type="button" value="Retour" />
		</a>';
	}
}

function print_message_bad_media ()
{
	echo '
	<h3>Média invalide</h3>
	<p>
		Le média que vous avez demandé n\'existe pas.
	</p>';
	print_return_link (false);
}


function print_media_show ($media_id, $show_return_links=true,
                           $show_admin_links=true, $media=null)
{
	if ($media === null)
		$media = media_get_by_id ($media_id);
	
	if ($media === false)
	{
		print_message_bad_media ();
	}
	else
	{
		$media['uri'] = MEDIA_DIR_R.'/'.$media['uri'];
		$media['tb_uri'] = MEDIA_DIR_R.'/'.$media['tb_uri'];
		
		echo '
		<table>
			<tr>
				<th></th>
				<th>Aperçu</th>
				<th>Description</th>
				<th>Commentaire</th>
			</tr>
			<tr>
				<th>', $show_admin_links ? '
					<a href="?page='.PAGE.'&amp;action=edit&amp;media='.$media['id'].'" title="Éditer">
						<img src="styles/'.STYLE.'/edit.png" alt="Éditer" />
					</a>' : '', '
				</th>
				<td rowspan="2" align="center">
					<a class="noicon" href="',$media['uri'],'" title="',$media['desc'],'">
						<img src="',$media['tb_uri'],'" alt="',$media['desc'],'" />
					</a>
				</td>
				<td rowspan="2">',$media['desc'],'</td>
				<td rowspan="2">',$media['comment'],'</td>
			</tr>
			<tr>
				<th>', $show_admin_links ? '
					<a href="?page='.PAGE.'&amp;action=rm&amp;media='.$media['id'].'" title="Supprimer">
						<img src="styles/'.STYLE.'/delete.png" alt="Éditer" />
					</a>' : '', '
				</th>
			</tr>
			<tr>
				<th>URI</th>
				<td colspan="3">
					<a class="noicon" href="',$media['uri'],'">',$media['uri'],'</a>
				</td>
			</tr>
		</table>';
		
		if ($show_return_links)
			print_return_link (false);
	}
}

/* prints all medias in $medias in section $type
 * note that if $medias is empty, the section is not created
 * \returns false if section wasn't created, true otherwise
 */
function print_media_table_for_type ($type, array &$medias)
{
	$empty = empty ($medias);
	
	if (! $empty)
	{
		echo '
		<h3>',ucfirst (MediaType::to_string ($type, true)),'</h3>
		<table>
			<thead>
				<tr>
					<th></th>
					<th></th>
					<!--th>ID</th-->
					<!--th>Type</th-->
					<th>Tags</th>
					<th>Description</th>
					<th><acronym title="Uploader ID">UID</acronym></th>
					<th>URI</th>
					<th>Taille</th>
					<th>Date</th>
				</tr>
			</thead>
			<tbody>';
		
		foreach ($medias as &$media)
		{
			echo '
			<tr>
				<td>
					<a href="?page=',PAGE,'&amp;action=edit&amp;media=',$media['id'],'" title="Éditer">
						<img src="styles/',STYLE,'/edit.png" alt="Éditer" />
					</a>
				</td>
				<td>
					<a href="?page=',PAGE,'&amp;action=rm&amp;media=',$media['id'],'" title="Supprimer">
						<img src="styles/',STYLE,'/delete.png" alt="Éditer" />
					</a>
				</td>
				<!--td>',$media['id'],'</td-->
				<!--td>',MediaType::to_string ($media['type']),'</td-->
				<td>',$media['tags'],'</td>
				<td>',$media['desc'],'</td>
				<td>',$media['uid'],'</td>
				<td>
					<a href="?page=',PAGE,'&amp;action=show&amp;media=',$media['id'],'" title="Afficher">
						',$media['uri'],'
					</a>
				</td>
				<td>',get_size_string ($media['size']),'</td>
				<td>',date ('d/m/Y', $media['mdate']),'</td>
			</tr>';
		}
		
		echo '
			</tbody>
		</table>';
	}
	
	return $empty;
}

/* fetch medias from DB and call the print function */
function print_media_tables ()
{
	echo '
	<p>
		<a href="?page=',PAGE,'&amp;action=new"
		   onclick="window.location.replace(this.href)">
			<button type="button">Ajouter un média</button>
		</a>
	</p>';
	
	$db = &new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
	$db->select_table (MEDIA_TABLE);
	for ($type = 1; $type < MediaType::N_TYPES; $type++)
	{
		$medias = array ();
		
		$db->select ('*', '`type`=\''.$type.'\'');
		while (($resp = $db->fetch_response ()) !== false)
		{
			media_unescape_db_array ($resp);
			$medias[] = $resp;
		}
		
		print_media_table_for_type ($type, $medias);
	}
	unset ($db);
}


function remove_media_query ($media_id)
{
	$media = media_get_by_id ($media_id);
	if ($media === false)
	{
		print_message_bad_media ();
	}
	else
	{
		if ($_POST['rm'] == 'rm')
		{
			if (media_remove ($media_id))
				echo '
				<p>
					Média supprimé avec succès
				</p>';
			else
				echo '
				<p>
					Errur lors de la suppréssion du média
				</p>';
			print_return_link (false);
		}
		else
		{
			echo '
			<form method="post" action="?page=',PAGE,'&amp;action=rm&amp;media=',$media_id,'">
				<p>
					Voulez-vous vraiment supprimer le média ci-dessous&nbsp;?
				</p>
				',print_media_show ($media_id, false, true, $media),'
				<p>
					<input type="hidden" name="rm" value="rm" />
					<input type="submit" value="Supprimer"/>
					',print_return_link (true),'
				</p>
			</form>';
		}
	}
}


/****/

define (FFIELD,  'ffield');
define (TBFIELD, 'tbfield');
define (MAX_FILE_SIZE, 1073741824); // maximum filesize to be uploaded (in bytes).

function print_upload_form ($action, $fillwith=false, $upload=true)
{
	$DPATHS = array ('screens/', 'movies/', 'downloads/', 'news/');
	
	?>
	<script type="text/javascript">
		<!--
		/* toggle entry disabled from selection */
		function doDestListChanged (e, name, id)
		{
			var sel = e.value;
			var entry = document.getElementById (id);
			/* static variable hack */
			if (this.n == "undefined") this.n = 0;
			
			if (sel == name)
			{
				entry.removeAttribute ("disabled");
				entry.focus ();
			}
			else
			{
				entry.value = e.value;
				entry.setAttribute ("disabled", "disabled");
			}
			this.n++;
		}
		
		function doTypeListChanged (e, select_id, name, manual_e_id)
		{
			select = document.getElementById (select_id);
			switch (e.value)
			{
				case '1': select.value = '<?php echo $DPATHS[0]; ?>'; break;
				case '2': select.value = '<?php echo $DPATHS[1]; ?>'; break;
				case '3': select.value = '<?php echo $DPATHS[2]; ?>'; break;
				case '4': select.value = '<?php echo $DPATHS[3]; ?>'; break;
				case '5': select.value = name; break;
			}
			doDestListChanged (select, name, manual_e_id);
		}
		//-->
	</script>
	<?php if ($upload) { ?>
		<p>Vous ne pourrez probablement pas uploader de fichiers de plus de <?php echo ini_get ('upload_max_filesize'); ?>.</p>
	<?php } ?>
	<form action="?page=<?php echo PAGE; ?>&amp;action=<?php echo $action; ?>"
				method="post" enctype="multipart/form-data">
		<div>
			<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MAX_FILE_SIZE; ?>" />
			<!-- FIXME: show a true ID -->
			<input type="hidden" name="uid" value="0" />
		</div>
		<table class="leftheads">
		<?php
		/* switchy affichage URI/sélcetion de fichier */
		if ($upload)
		{
		?>
			<tr>
				<th>
					<label for="f__0">Fichier&nbsp;:</label>
				</th>
				<td colspan="2">
					<input id="f__0" type="file" name="<?php echo FFIELD; ?>" />
				</td>
			</tr>
			<tr>
				<th>
					<label for="f__1"
					       title="Si la vignette est omise, elle sera générée si possible. La génération ne fonctionne qu'avec les images PNG et JPEG">
						Vignette&nbsp;:
					</label>
				</th>
				<td colspan="2">
					<input id="f__1" type="file" name="<?php echo TBFIELD; ?>" />
				</td>
			</tr>
		<?php
		} /* fin de l'affichage upload */
		else
		{
		?>
			<tr>
				<th>
					<label for="f__0">URI du média&nbsp;:</label>
				</th>
				<td colspan="2">
					<input id="f__0" type="text" name="<?php echo FFIELD; ?>"
					       value="<?php echo $fillwith['uri']; ?>" />
				</td>
			</tr>
			<tr>
				<th>
					<label for="f__1">
						URI de la vignette&nbsp;:
					</label>
				</th>
				<td colspan="2">
					<input id="f__1" type="text" name="<?php echo TBFIELD; ?>"
					       value="<?php echo $fillwith['tb_uri']; ?>" />
				</td>
			</tr>
		<?php
		} /* fin de l'affichage des URIs */
		?>
			<tr>
				<th>
					<label for="f__3">Type de média&nbsp;:</label>
				</th>
				<td colspan="2">
					<select name="type" id="f__3"
					<?php if ($upload) { ?>
						      onchange="doTypeListChanged(this, 'f__2', 'other', 'manual_entry');"
					<?php } ?>
						>
						<?php
							for ($type = 1; $type < MediaType::N_TYPES; $type++)
								echo '
								<option value="',$type,'"',
									($type == $fillwith['type']) ? ' selected="selected"' : '','>
									',MediaType::to_string ($type),'
								</option>';
						?>
					</select>
				</td>
			</tr>
		<?php
		if ($upload)
		{
		?>
			<tr>
				<th>
					<label for="f__2">Destination&nbsp;:</label>
				</th>
				<td>
					<select name="dest" id="f__2" style="width: 100%;"
					        onchange="doDestListChanged(this, 'other', 'manual_entry');">
	<?php
	foreach ($DPATHS as $val)
	{
		echo '<option value="',$val,'">',$val,'</option>';
	}
	?>
						<option value="other">Autre</option>
					</select>
				</td>
				<td>
					<label><?php echo strshortcut (MEDIA_DIR_W, 25); ?>
						<input class="nosize" type="text" id="manual_entry"
									 name="manual_entry" value="/" />
					</label>
				</td>
			</tr>
		<?php
		} /* fin du non-affichage si on upload pas */
		?>
			<tr>
				<th>
					<label for="f__4" title="Attention, un tag ne peut pas contenir d'espace">Tags&nbsp;:</label>
				</th>
				<td colspan="2">
					<input type="text" id="f__4" name="tags"
					       value="<?php echo $fillwith['tags']; ?>" />
				</td>
			</tr>
			<tr>
				<th>
					<label for="f__5">Description&nbsp;:</label>
				</th>
				<td colspan="2">
					<input type="text" id="f__5" name="desc"
					       value="<?php echo $fillwith['desc']; ?>" />
				</td>
			</tr>
			<tr>
				<th>
					<label for="f__6">Commentaire&nbsp;:</label>
				</th>
				<td colspan="2">
					<textarea id="f__6" rows="5" cols="30" name="comment"><?php echo $fillwith['comment']; ?></textarea>
				</td>
			</tr>
		</table>
		
		<p class="form_buttons">
			<input type="submit" value="<?php echo ($upload) ? 'Upload' : 'Mettre à jour'; ?>" />
			<input type="reset" value="Vider" />
			<?php print_return_link (true); ?>
		</p>
	</form>
	<?php
	if ($upload)
	{
	?>
		<script type="text/javascript">
			<!--
			/* disable manual entry by default */
			document.getElementById ('manual_entry').setAttribute ("disabled", "disabled");
			/* set default manual entry content */
			document.getElementById ('manual_entry').value = "<?php echo $DPATHS[0]; ?>";
			//-->
		</script>
	<?php
	}
}

function upload_error_to_string ($u_err)
{
	switch ($u_err)
	{
		case UPLOAD_ERR_OK:
			return 'Pas d\'erreur.';
		case UPLOAD_ERR_INI_SIZE:
			return 'Le fichier téléchargé excède la taille maximale configurée sur le serveur.';
		case UPLOAD_ERR_FORM_SIZE:
			return 'Le fichier téléchargé excède la taille maximale spécifiée dans le formulaire HTML.';
		case UPLOAD_ERR_PARTIAL:
			return 'Le fichier n\'a été que partiellement téléchargé.';
		case UPLOAD_ERR_NO_FILE:
			return 'Aucun fichier n\'a été téléchargé.';
		case UPLOAD_ERR_NO_TMP_DIR:
			return 'Un dossier temporaire est manquant.';
		case UPLOAD_ERR_CANT_WRITE:
			return 'Échec de l\'écriture du fichier sur le disque.';
		case UPLOAD_ERR_EXTENSION:
			return 'L\'envoi de fichier a été arrêté par l\'extension.';
		default:
			return 'Erreur inconnue';
	}
}

define (TB_W, 160.0);
define (TB_H, 120.0);

function create_thumbnail_img (&$imgsrc)
{
	$imgdst;
	
	$sw = imagesx ($imgsrc);
	$sh = imagesy ($imgsrc);
	$ratio = $sw/$sh;
	$dw = min ($sw, TB_W);
	$dh = min ($sh, TB_H);
	
	if ($ratio > (TB_W/TB_H))
	{
		$dh = $dw / $ratio;
	}
	else
	{
		$dw = $dh * $ratio;
	}
	
	// on centre l'image
	$dx = TB_W/2 - $dw/2;
	$dy = TB_H/2 - $dh/2;
	
	// miniature
	$imgdst = imagecreatetruecolor (TB_W, TB_H);
	
	if (imagecopyresampled ($imgdst, $imgsrc, $dx, $dy, 0, 0, $dw, $dh, $sw, $sh))
		return $imgdst;
	else
		return null;
}

function create_thumbnail_png ($input, $output, $compress=9)
{
	$imgsrc = imagecreatefrompng ($input);
	if (! $imgsrc)
		return false;
	
	$imgdst = create_thumbnail_img ($imgsrc);
	return imagepng ($imgdst, $output, $compress);
}

function create_thumbnail_jpeg ($input, $output, $quality=75)
{
	$imgsrc = imagecreatefromjpeg ($input);
	if (! $imgsrc)
		return false;
	
	$imgdst = create_thumbnail_img ($imgsrc);
	return imagejpeg ($imgdst, $output, $quality);
}

function create_thumbnail_gif ($input, $output)
{
	$imgsrc = imagecreatefromgif ($input);
	if (! $imgsrc)
		return false;
	
	$imgdst = create_thumbnail_img ($imgsrc);
	return imagegif ($imgdst, $output);
}

function create_thumbnail ($mime, $input, $output)
{
	switch ($mime)
	{
		case 'image/png':
			return create_thumbnail_png ($input, $output);
		case 'image/jpeg':
			return create_thumbnail_jpeg ($input, $output);
		case 'image/gif':
			return create_thumbnail_gif ($input, $output);
	}
	
	return false;
}

function add_media_query ()
{
	$error = '';
	
	if (isset ($_FILES[FFIELD]))
	{
		if (!isset ($_POST['dest']) || ($_POST['dest'] == 'other' && !$_POST['manual_entry']))
		{
			$error = 'No destionation';
		}
		else
		{
			if ($_POST['dest'] == 'other')
				$_POST['dest'] = $_POST['manual_entry'];
			
			if ($_FILES[FFIELD]['error'] != UPLOAD_ERR_OK)
				$error = 'Upload error: '.upload_error_to_string ($_FILES[FFIELD]['error']);
			else
			{
				/* no errors, we can work with data */
				$prefix = '' /*time ()*/;
				$tmp_path = $_FILES[FFIELD]['tmp_name'];
				$old_name = $_FILES[FFIELD]['name'];
				$path = path_clean ($_POST['dest'].'/'.$prefix.$old_name);
				$r_path = MEDIA_DIR_R.'/'.$path;
				$w_path = MEDIA_DIR_W.'/'.$path;
				$tb_path = path_clean ($_POST['dest'].'/tb_'.$prefix.$old_name);
				$tb_r_path = MEDIA_DIR_R.'/'.$tb_path;
				$tb_w_path = MEDIA_DIR_W.'/'.$tb_path;
				
				/* on crée le thumb avant pour pouvoir lire depuis le dossier temporaire */
				if ($_FILES[TBFIELD]['error'] == UPLOAD_ERR_OK)
				{
					if (! move_uploaded_file ($_FILES[TBFIELD]['tmp_name'], $tb_w_path))
						$error = 'Failed to move thumbnail to the final location';
				}
				else if ($_FILES[TBFIELD]['error'] == UPLOAD_ERR_NO_FILE)
				{
					/* if no thumb, try to create one */
					if (! create_thumbnail ($_FILES[FFIELD]['type'], $tmp_path, $tb_w_path))
					{
						$tb_path = null;
					}
				}
				else
					$error = 'Failed to upload thumb';
				
				if (! $error)
				{
					$statbuf = stat ($tmp_path);
					if (move_uploaded_file ($_FILES[FFIELD]['tmp_name'], $w_path))
					{
						if (media_add ($path, $tb_path, $statbuf['size'],
						               $statbuf['mtime'], $_POST['type'], $_POST['uid'], 
						               $_POST['tags'], $_POST['desc'], $_POST['comment']))
						{
							echo '
							<p class="ok">
								"',basename ($old_name),'" has been sucessully stored in the folder "',dirname ($w_path),'".
							</p>';
						}
						else
							$error = 'Failed to insert media in media database';
					}
					else
						$error = 'Failed to move file to the final location.';
				}
				else
				{
					if (! $error)
						$error = 'Unknown error';
				}
			}
		}
		
		/* erros display */
		if ($error)
			echo '<p class="error">', $error, '</p>';
		echo '<hr />';
	}
	else if (isset ($_POST['dest']))
	{
		echo '<p class="error">Unknown error</p>';
		echo '<hr />';
	}
	
	print_upload_form ('new');
}


function update_media_query ($media_id)
{
	if (isset ($_POST[FFIELD], $_POST[TBFIELD], $_POST['type'], $_POST['uid'], 
	           $_POST['tags'], $_POST['desc'], $_POST['comment']))
	{
		$media = media_get_by_id ($media_id);
		if (media_set_from_values ($media_id,
		                           $_POST[FFIELD], $_POST[TBFIELD], $media['size'],
		                           $media['mdate'], $_POST['type'], $_POST['uid'], 
		                           $_POST['tags'], $_POST['desc'], $_POST['comment']))
		{
			echo '
			<p class="ok">
				Média mis à jour avec succès
			</p>';
		}
		else
		{
			echo '
			<p class="error">
				Erreur lors de la mise à jour du média
			</p>';
		}
	}
	
	$media = media_get_by_id ($media_id);
	print_upload_form ('edit&amp;media='.$media_id, &$media, false);
}

/******************************************************************************/

$error = null;

echo '
<div id="presentation">
	<h2>Gestion des médias</h2>
		<p>
			Ajout, édition &amp; suppression de médias. Les médias sont aussi bien les
			captures d\'écran que les archives au téléchargement.
		</p>
	</div>
	
	<div id="content">';

if (isset ($_GET['action']))
{
	switch ($_GET['action'])
	{
		case 'show':
			if (! isset ($_GET['media']))
				$error = 'Média manquant';
			else
				print_media_show ($_GET['media']);
			break;
		
		case 'rm':
			if (! isset ($_GET['media']))
				$error = 'Média manquant';
			else
				remove_media_query ($_GET['media']);
			break;
		
		case 'edit':
			if (! isset ($_GET['media']))
				$error = 'Média manquant';
			else
				update_media_query ($_GET['media']);
			break;
		
		case 'new':
			add_media_query ();
			break;
		
		default:
			$error = 'Action invalide';
	}
}
else
	print_media_tables ();

if ($error)
{
	echo '
	<p class="error">',$error,'</p>
	<p>',print_return_link (false),'</p>';
}

echo '
</div>';
