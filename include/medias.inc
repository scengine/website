<?php
/* LICENSE
 * 
 * BanSE - a site base (designed to be the SCEngine website)
 * Copyright (C) 2009-2012 Colomban Wendling <ban@herbesfolles.org>
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

require_once ('lib/UrlTable.php');
require_once ('lib/misc.php');
require_once ('lib/User.php');
require_once ('lib/MyDB.php');
require_once ('lib/medias.php');


function print_return_link ($button=false)
{
	if (! $button)
	{
		echo '
		<p>
			<a href="',UrlTable::admin_medias (),'">&lArr; Retour à la gestion des médias</a>
		</p>';
	}
	else
	{
		print_button ('Retour', UrlTable::admin_medias (),
		              'Retour à la gestion des médias');
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
				<th>';
		if ($show_admin_links)
		{
			echo '
					<a href="',UrlTable::admin_medias ('edit', $media['id']),'" title="Éditer">
						<img src="styles/',STYLE,'/edit.png" alt="Éditer" />
					</a>';
		}
		echo '
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
				<th>';
		if ($show_admin_links)
		{
			echo '
					<a href="',UrlTable::admin_medias ('rm', $media['id']),'" title="Supprimer">
						<img src="styles/',STYLE,'/delete.png" alt="Supprimer" />
					</a>';
		}
		echo '
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
					<a href="',UrlTable::admin_medias ('edit', $media['id']),'" title="Éditer">
						<img src="styles/',STYLE,'/edit.png" alt="Éditer" />
					</a>
				</td>
				<td>
					<a href="',UrlTable::admin_medias ('rm', $media['id']),'" title="Supprimer">
						<img src="styles/',STYLE,'/delete.png" alt="Supprimer" />
					</a>
				</td>
				<!--td>',$media['id'],'</td-->
				<!--td>',MediaType::to_string ($media['type']),'</td-->
				<td>',$media['tags'],'</td>
				<td>',$media['desc'],'</td>
				<td>',$media['uid'],'</td>
				<td>
					<a href="',UrlTable::admin_medias ('show', $media['id']),'" title="Afficher">
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
		',print_button ('Ajouter un média', UrlTable::admin_medias ('new')),'
	</p>';
	
	$db = new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
	$db->select_table (MEDIA_TABLE);
	for ($type = 1; $type < MediaType::N_TYPES; $type++)
	{
		$medias = array ();
		
		$db->select ('*', "`type`='$type'");
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
		if (isset ($_POST['rm']) && $_POST['rm'] == 'rm')
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
			<form method="post" action="',UrlTable::admin_medias ('rm', $media_id),'">
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

define ('FFIELD',  'ffield');
define ('TBFIELD', 'tbfield');
define ('MAX_FILE_SIZE', 1073741824); // maximum filesize to be uploaded (in bytes).

function print_upload_form ($action, $id=false, &$fillwith=null, $upload=true)
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
	<form action="<?php echo UrlTable::admin_medias ($action, $id); ?>"
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
					<label for="f__0">
						Fichier&nbsp;:
						<input type="radio" name="upload_type" value="file" checked="checked" />
					</label>
				</th>
				<td colspan="2">
					<input id="f__0" type="file" name="<?php echo FFIELD; ?>" />
				</td>
			</tr>
			<tr>
				<th>
					<label for="f__0_1">
						<abbr title="Uniform Resource Locator">URL</abbr> du fichier&nbsp;:
						<input type="radio" name="upload_type" value="url" />
					</label>
				</th>
				<td colspan="2">
					<input id="f__0_1" type="text" name="<?php echo FFIELD; ?>" />
				</td>
			</tr>
			
			<tr>
				<th>
					<label for="f__1"
					       title="Si la vignette est omise, elle sera générée si possible. La génération ne fonctionne qu'avec les images PNG et JPEG">
						Vignette&nbsp;:
						<input type="radio" name="upload_tb_type" value="file" checked="checked" />
					</label>
				</th>
				<td colspan="2">
					<input id="f__1" type="file" name="<?php echo TBFIELD; ?>" />
				</td>
			</tr>
			<tr>
				<th>
					<label for="f__1_1"
					       title="Si la vignette est omise, elle sera générée si possible. La génération ne fonctionne qu'avec les images PNG et JPEG">
						<abbr title="Uniform Resource Locator">URL</abbr> de la vignette&nbsp;:
						<input type="radio" name="upload_tb_type" value="url" />
					</label>
				</th>
				<td colspan="2">
					<input id="f__1_1" type="text" name="<?php echo TBFIELD; ?>" />
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
					<label>
					<?php
						$display_dest = strshortcut (MEDIA_DIR_W, 25);
						if (! empty ($display_dest))
							$display_dest .= '/';
						
						echo $display_dest;
						?>
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
					<label for="f__7" title="Date du média au format « YYYY/MM/DD hh:mm:ss » (laisser vide pour la date par défaut)">Date&nbsp;:</label>
				</th>
				<td colspan="2">
					<input type="text" id="f__7" name="mdate" value="<?php if ($fillwith) echo strftime ('%Y/%m/%d %H:%M:%S', $fillwith['mdate']); ?>" />
				</td>
			</tr>
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
			<!--input type="reset" value="Vider" /-->
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

define ('TB_W', 160.0);
define ('TB_H', 120.0);

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

function get_safe_mdate ($strtime, $default_timestamp)
{
	$mdate = $default_timestamp;
	if ($strtime)
	{
		$mdate = strtotime ($strtime);
		if ($mdate === false)
		{
			$mdate = $default_timestamp;
			echo '
			<p class="error">
				Le format de la date semble invalide, la date n\'a pas été modifiée.
			</p>';
		}
	}
	
	return $mdate;
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
			$file_tmp_path = '';
			$file_old_name = '';
			$file_type     = '';
			$upload_type   = '';
			$tb_path   = '';
			
			if ($_POST['dest'] == 'other')
				$_POST['dest'] = $_POST['manual_entry'];
			
			/* File */
			if ($_POST['upload_type'] == 'url')
			{
				if (empty ($_POST[FFIELD]))
					$error = 'Upload error: no URL';
				else
				{
					$file_tmp_path = '/tmp/tmpupload_'.time ();
					$file_old_name = basename ($_POST[FFIELD]);
					$file_type = filename_get_mime_type ($file_old_name);
					$upload_type = 'url';
					
					if (! copy ($_POST[FFIELD], $file_tmp_path))
						$error = 'Failed to upload URL';
				}
			}
			else
			{
				if ($_FILES[FFIELD]['error'] != UPLOAD_ERR_OK)
					$error = 'Upload error: '.upload_error_to_string ($_FILES[FFIELD]['error']);
				else
				{
					$file_tmp_path = $_FILES[FFIELD]['tmp_name'];
					$file_old_name = $_FILES[FFIELD]['name'];
					$file_type = $_FILES[FFIELD]['type'];
					$upload_type = 'file';
				}
			}
			
			/* Tumbnail */
			if (! $error)
			{
				// Thumbnail is created after uploading media to be able to read it
				$prefix = '' /*time ()*/;
				$tb_path = path_clean ($_POST['dest'].'/tb_'.$prefix.$file_old_name);
				//$tb_r_path = MEDIA_DIR_R.'/'.$tb_path;
				$tb_w_path = MEDIA_DIR_W.'/'.$tb_path;
				
				if ($_POST['upload_tb_type'] == 'url')
				{
					if (! empty ($_POST[TBFIELD]))
					{
						$tb_tmp_path = '/tmp/tmpuploadtb_'.time ();
						
						if (! copy ($_POST[TBFIELD], $tb_tmp_path))
							$error = 'Failed to upload URL';
						else
						{
							if (! rename ($tb_tmp_path, $tb_w_path))
							{
								$error = 'Failed to move thumbnail to the final location';
								unlink ($tb_tmp_path);
							}
						}
					}
				}
				else
				{
					if ($_FILES[TBFIELD]['error'] == UPLOAD_ERR_OK)
					{
						$tb_tmp_path = $_FILES[TBFIELD]['tmp_name'];
						if (! move_uploaded_file ($tb_tmp_path, $tb_w_path))
							$error = 'Failed to move thumbnail to the final location';
					}
					else if ($_FILES[TBFIELD]['error'] != UPLOAD_ERR_NO_FILE)
						$error = 'Failed to upload thumb';
				}
				
				if (empty ($tb_tmp_path) && ! $error)
				{
					if (! create_thumbnail ($file_type, $file_tmp_path, $tb_w_path))
					{
						$tb_path = media_get_default_thumbnail_from_uri ($old_name);
					}
				}
			}
			
			if (! $error)
			{
				/* no errors, we can work with data */
				$prefix = '' /*time ()*/;
				$path = path_clean ($_POST['dest'].'/'.$prefix.$file_old_name);
				$r_path = MEDIA_DIR_R.'/'.$path;
				$w_path = MEDIA_DIR_W.'/'.$path;
				
				$statbuf = stat ($file_tmp_path);
				
				if ($upload_type == 'url')
				{
					if (! rename ($file_tmp_path, $w_path))
					{
						unlink ($file_tmp_path);
						$error = 'Failed to move file to the final location.';
					}
				}
				else
				{
					if (! move_uploaded_file ($file_tmp_path, $w_path))
						$error = 'Failed to move file to the final location.';
				}
				
				if (! $error)
				{
					$mdate = get_safe_mdate ($_POST['mdate'], $statbuf['mtime']);
					
					if (media_add ($path, $tb_path, $statbuf['size'],
					               $mdate, $_POST['type'], $_POST['uid'], 
					               $_POST['tags'], $_POST['desc'], $_POST['comment']))
					{
						echo '
						<p class="ok">
							"',basename ($file_old_name),'" has been sucessully stored in the folder "',dirname ($w_path),'".
						</p>';
					}
					else
					{
						$error = 'Failed to insert media in media database';
						unlink ($w_path);
					}
				}
			}
			
			/*
			if ($_FILES[FFIELD]['error'] != UPLOAD_ERR_OK)
				$error = 'Upload error: '.upload_error_to_string ($_FILES[FFIELD]['error']);
			else
			{
				// no errors, we can work with data
				$prefix = ''; //time ();
				$tmp_path = $_FILES[FFIELD]['tmp_name'];
				$old_name = $_FILES[FFIELD]['name'];
				$path = path_clean ($_POST['dest'].'/'.$prefix.$old_name);
				$r_path = MEDIA_DIR_R.'/'.$path;
				$w_path = MEDIA_DIR_W.'/'.$path;
				$tb_path = path_clean ($_POST['dest'].'/tb_'.$prefix.$old_name);
				$tb_r_path = MEDIA_DIR_R.'/'.$tb_path;
				$tb_w_path = MEDIA_DIR_W.'/'.$tb_path;
				
				// on crée le thumb avant pour pouvoir lire depuis le dossier temporaire
				if ($_FILES[TBFIELD]['error'] == UPLOAD_ERR_OK)
				{
					if (! move_uploaded_file ($_FILES[TBFIELD]['tmp_name'], $tb_w_path))
						$error = 'Failed to move thumbnail to the final location';
				}
				else if ($_FILES[TBFIELD]['error'] == UPLOAD_ERR_NO_FILE)
				{
					// if no thumb, try to create one
					if (! create_thumbnail ($_FILES[FFIELD]['type'], $tmp_path, $tb_w_path))
					{
						$tb_path = media_get_default_thumbnail_from_uri ($old_name);
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
			*/
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
	           $_POST['tags'], $_POST['desc'], $_POST['comment'], $_POST['mdate']))
	{
		$media = media_get_by_id ($media_id);
		$mdate = get_safe_mdate ($_POST['mdate'], $media['mdate']);
		
		if (media_set_from_values ($media_id,
		                           $_POST[FFIELD], $_POST[TBFIELD], $media['size'],
		                           $mdate, $_POST['type'], $_POST['uid'], 
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
	print_upload_form ('edit', $media_id, $media, false);
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

if (User::has_rights (ADMIN_LEVEL_MEDIA))
{
	if (isset ($_GET['action']))
	{
		switch ($_GET['action'])
		{
			case 'show':
				if (! isset ($_GET['id']))
					$error = 'Média manquant';
				else
					print_media_show ($_GET['id']);
				break;
			
			case 'rm':
				if (! isset ($_GET['id']))
					$error = 'Média manquant';
				else
					remove_media_query ($_GET['id']);
				break;
			
			case 'edit':
				if (! isset ($_GET['id']))
					$error = 'Média manquant';
				else
					update_media_query ($_GET['id']);
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
}
/* if not allowed to go there */
else
{
	echo '
	<p>
		Vous n\'avez pas les droits suffisants pour gérer les médias.
	</p>';
}

echo '
	</div>';
