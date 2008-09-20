<?php;
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

/* upload of medias */

require_once ('include/string.php');


define (DPATH_DELIM, ':'); # path delimiter
define (DPATH, '/screens/:/movies/:/downloads/'); # defaults values
//define (UPLOADS_BPATH, './'); # base directory for uploads
define (UPLOADS_BPATH, 'ftp://yno@scengine:accesftpyno@downloads.goldzoneweb.info/');
define (MAX_FILE_SIZE, 1073741824); # maximum filesize to be uploaded (in bytes).

define (FFIELD, 'ufile'); # @internal name of file field

$error = '';

?>

<div id="presentation">
	<h2>Upload</h2>
	<p>
		Formulaire d'upload de médias.
	</p>
</div>
<div id="content">

<?php
/* gestion des envois */

if (isset ($_FILES[FFIELD]))
{
	if (!isset ($_POST['dest']) || $_POST['dest'] == 'other' && !$_POST['manual_entry'])
	{
		$error = 'No destionation';
	}
	else
	{
		if ($_POST['dest'] == 'other')
			$_POST['dest'] = $_POST['manual_entry'];
		
		if ($_FILES[FFIELD]['error'] != 0)
		{
			$error = 'Upload error: ';
			
			switch ($_FILES[FFIELD]['error'])
			{
				case UPLOAD_ERR_INI_SIZE:
					$error .= 'Le fichier téléchargé excède la taille maximale configurée sur le serveur.';
					break;
				case UPLOAD_ERR_FORM_SIZE:
					$error .= 'Le fichier téléchargé excède la taille maximale spécifiée dans le formulaire HTML.';
					break;
				case UPLOAD_ERR_PARTIAL:
					$error .= 'Le fichier n\'a été que partiellement téléchargé.';
					break;
				case UPLOAD_ERR_NO_FILE:
					$error .= 'Aucun fichier n\'a été téléchargé.';
					break;
				case UPLOAD_ERR_NO_TMP_DIR:
					$error .= 'Un dossier temporaire est manquant.';
					break;
				case UPLOAD_ERR_CANT_WRITE:
					$error .= 'Échec de l\'écriture du fichier sur le disque.';
					break;
				case UPLOAD_ERR_EXTENSION:
					$error .= 'L\'envoi de fichier a été arrêté par l\'extension.';
					break;
				default:
					$error .= 'Unknown error';
			}
		}
		else
		{
			/* no errors, we can work with data */
			$fpath = UPLOADS_BPATH.$_POST['dest'].'/'.$_FILES[FFIELD]['name'];
			
			if (move_uploaded_file ($_FILES[FFIELD]['tmp_name'], $fpath))
			{
				echo '<p class="ok">"',basename ($fpath),'" has been sucessully stored in the folder "',dirname ($fpath),'".</p>';
			}
			else
				$error = 'Failed to move file to the filnal location.';
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


?>


<script type="text/javascript">
	/* toggle entry disabled from selection */
	function doSelectionChanged (e, name, id)
	{
		var sel = e.value;
		var entry = document.getElementById (id);
		/* hack for static variable
		   This is used to set focus only at second click */
		if (typeof this.n == "undefined") this.n=0;
		
		this.n++;
		if (sel == name)
		{
			entry.removeAttribute ("disabled");
			if (this.n > 1)
			{
				entry.focus ();
				this.n = 0;
			}
		}
		else
		{
			entry.value = e.value;
			entry.setAttribute ("disabled", "disabled");
		}
	}
</script>

<p>Vous ne pourrez probablement pas uploader de fichiers de plus de <?php echo ini_get ('upload_max_filesize'); ?>.</p>

<form action="<?php echo '?page=', PAGE ?>" method="post" enctype="multipart/form-data">
	<p>
		<label>Fichier&nbsp;:
		<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MAX_FILE_SIZE; ?>" />
		<input type="file" name="<?php echo FFIELD; ?>" />
		</label>
		
		<br />
		<label>Destination&nbsp;:
			<select name="dest" onclick="doSelectionChanged(this, 'other', 'manual_entry');">
				<?php
					foreach (split (DPATH_DELIM, DPATH) as $val)
						echo '<option value="',$val,'">',$val,'</option>';
				?>
				<option value="other">Autre</option>
			</select>
		</label>
		<label><?php echo strshortcut (UPLOADS_BPATH, 25); ?>
		<input class="nosize" type="text" id="manual_entry" name="manual_entry" value="/" />
		</label>
	</p>
	
	<fieldset>
		<legend>Avancé</legend>
			<label>Description (non utilisée pour le moment)&nbsp;:
			<textarea name="desc" cols="30" rows="3" style="width:99%"></textarea>
		</label>
	</fieldset>
	
	<p class="form_buttons">
		<input type="submit" value="Upload" />
		<input type="reset" value="Vider" />
	</p>
</form>

<script type="text/javascript">
	/* disable manual entry by default */
	document.getElementById ('manual_entry').setAttribute ("disabled", "disabled");
	/* set default manual entry content */
	document.getElementById ('manual_entry').value = "<?php echo substr (DPATH, 0, strpos (DPATH, DPATH_DELIM)) ?>";
</script>

</div>
