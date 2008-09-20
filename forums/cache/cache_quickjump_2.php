<?php

if (!defined('PUN')) exit;
define('PUN_QJ_LOADED', 1);

?>				<form id="qjump" method="get" action="viewforum.php">
					<div><label><?php echo $lang_common['Jump to'] ?>

					<br /><select name="id" onchange="window.location=('viewforum.php?id='+this.options[this.selectedIndex].value)">
						<optgroup label="Général">
							<option value="3"<?php echo ($forum_id == 3) ? ' selected="selected"' : '' ?>>Général programmation</option>
						</optgroup>
						<optgroup label="SCEngine">
							<option value="1"<?php echo ($forum_id == 1) ? ' selected="selected"' : '' ?>>Discussions sur le SCEngine</option>
							<option value="6"<?php echo ($forum_id == 6) ? ' selected="selected"' : '' ?>>Rapports de bogues</option>
					</optgroup>
					</select>
					<input type="submit" value="<?php echo $lang_common['Go'] ?>" accesskey="g" />
					</label></div>
				</form>
