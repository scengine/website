			<div id="presentation">
				<h2>Administration</h2>
			</div>
			
			<div id="content">
			<p>
				Bienvenue sur la partie d'administration du site du SCEngine&nbsp;!
				<a href="index.php">Retour au site</a>
			</p>
<?php
	require_once('include/User.php');

	if(User::get_logged ())
	{
?>
		<p>
			<a href="connexion.php?act=logout"
				onclick="window.location.replace(this.href)">
				<input type="button" value="Déconnexion" />
			</a>
		</p>
		<h3>Pages</h3>
		<ul>
<?php
	$dir = opendir ('include'); // dossier contenant les pages
	$tpages = Array (); // tableau des pages
	
	// getting files from directory
	for ($i = 0; False !== ($file = readdir ($dir)); $i++) {
		// vreifying filename
		if (substr ($file, strlen ($file) -4) == '.inc')
			$tpages[$i] = substr ($file, 0, strlen ($file) -4);
	}
	
	closedir ($dir); // fermeture du répertoire
	
	sort ($tpages); // sorting array
	
	// dispaying result
	foreach ($tpages as $tpage) {
		$tname = ucfirst ($tpage);
		
		echo '<li><a href="?page=', $tpage, '">', $tname, '</a></li>';
	}
?>
		</ul>
<?php
	}
	else
	{
		if($_POST['username'] || $_POST['password'])
		{
?>
			<p>
				<span class="error">Erreur&nbsp;:</span> mot de passe ou login incorrect&nbsp;!
			</p>
<?php
		}
?>
			<form method="post" action="connexion.php?act=login">
				<div>
					<label>Nom d'utilisateur&nbsp;: <input type="text" name="username" /></label><br />
					<label>Mot de passe&nbsp;: <input type="password" name="password" /></label><br />
					<input type="submit" value="Valider" />
				</div>
			</form>
<?php
	}
?>

		</div>
