<div id="presentation">
	<h2>Infos PHP</h2>
	<p>
		Information concernant les variables globales ou la configuration de PHP.
	</p>
</div>
<div id="content">

<?php
if (isset ($_GET['phpinfo'])) {
	phpinfo ();
}
else {
	echo '<p>
				<a href="phpinfo.php"
				onclick="window.open(this.href, \'PHPInfo()\', \'status=no, directories=no, toolbar=no, location=no, menubar=no, scrollbars=yes\');return false;">
					Voir le PHPInfo()
				</a>
			</p>';

	if (!empty ($_SERVER)) {
		echo '<table>
			<caption>Variables serveur</caption>';
			
		foreach ($_SERVER as $key => $value) {
			if (is_array ($value)) {
				$value = 'Array ()';
			}
			
			echo '<tr>
						<td>', $key, '</td>
						<td>', htmlspecialchars ($value), '</td>
					</tr>';
		}
		
		echo '</table>';
	}


	if (!empty ($_ENV)) {
		echo '<table>
			<caption>Variables d\'environnement</caption>';
			
		foreach ($_ENV as $key => $value) {
			if (is_array ($value)) {
				$value = 'Array ()';
			}
			
			echo '<tr>
						<td>', $key, '</td>
						<td>', htmlspecialchars ($value), '</td>
					</tr>';
		}
		
		echo '</table>';
	}


	if (!empty ($_SESSION)) {
		echo '<table>
			<caption>Variables de session</caption>';
			
		foreach ($_SESSION as $key => $value) {
			if (is_array ($value)) {
				$value = 'Array ()';
			}
			
			echo '<tr>
						<td>', $key, '</td>
						<td>', htmlspecialchars ($value), '</td>
					</tr>';
		}
		
		echo '</table>';
	}


	if (!empty ($_COOKIE)) {
		echo '<table>
			<caption>Cookie</caption>';
			
		foreach ($_COOKIE as $key => $value) {
			if (is_array ($value)) {
				$value = 'Array ()';
			}
			
			echo '<tr>
						<td>', $key, '</td>
						<td>', htmlspecialchars ($value), '</td>
					</tr>';
		}
		
		echo '</table>';
	}

}
?>

</div> <!-- content -->
