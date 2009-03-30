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


require_once ('lib/BCode.php');
require_once ('include/defines.php');
require_once ('lib/MyDB.php');
require_once ('lib/User.php');

/* required JavaScript */
$HEAD_ADDS[] = '<script type="text/javascript" src="include/js/actions.js"></script>';


function escape_html_quotes (&$str)
{
	$str = str_replace ('"', '&quot;', $str);
	return $str;
} 

/* Prints a news form */
function news_print_form ($title='', $source='', $action='new', $id='',
                          $redirect=null, $extra_buttons='', $extra_div_attrs='')
{
	$title = escape_html_quotes (stripslashes ($title));
	$source = htmlspecialchars (stripslashes ($source), ENT_COMPAT, 'UTF-8');
	
	if ($redirect)
	{
		$redirect = '&amp;redirect='.urlencode ($redirect);
	}
	
	echo '
	<div class="formedit" id="fn',$id,'" ',$extra_div_attrs,'>
		<form method="post" action="post.php?sec=news&amp;id=',$id,'&amp;act=',$action,$redirect,'">
			<div>
				<a href="javascript:entry_more(\'tn',$id,'\')">[+]</a>
				<a href="javascript:entry_lesser(\'tn',$id,'\')">[-]</a>
			</div>
			<p>
				<label>T<span class="u">i</span>tre&nbsp;:<br />
					<input type="text" name="title" accesskey="i" value="',$title,'" />
				</label>
				<br />
				<br />
				<label><span class="u">C</span>ontenu&nbsp;:<br />
					<textarea name="content" cols="24" rows="16" accesskey="c" id="tn',$id,'">',
						$source,
					'</textarea>
				</label>
				<br />
				<input type="submit" value="Poster" accesskey="p" title="Poster (Alt + P)" />
				<!--input type="reset" value="Réinitialiser" accesskey="x" title="Vider le forumlaire (Alt + X)" /-->
				',$extra_buttons,'
			</p>
		</form>
	</div>';
}
