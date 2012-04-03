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


require_once ('lib/BCode.php');
require_once ('include/defines.php');
require_once ('lib/MyDB.php');
require_once ('lib/User.php');
require_once ('lib/Html.php');

/* required JavaScript */
//$HEAD_ADDS[] = '<script type="text/javascript" src="include/js/actions.js"></script>';


abstract class News
{
	/* Prints a news form */
	public static function print_form ($title='', $source='', $action='new',
	                                   $id='', $redirect=null, $extra_buttons='',
	                                   $extra_div_attrs='', $publish = true)
	{
		$title = Html::escape ($title);
		$source = Html::escape ($source);
		
		if ($redirect)
		{
			$redirect = '&amp;redirect='.urlencode ($redirect);
		}
		
		echo '
		<div class="formedit" id="fn',$id,'" ',$extra_div_attrs,'>
			<form method="post" action="post.php?sec=news&amp;id=',$id,'&amp;act=',$action,$redirect,'">
				<div>
					<label><span class="u">T</span>itle:<br />
						<input type="text" name="title" accesskey="t" value="',$title,'" />
					</label>
					<br />
					<label for="tn',$id,'"><span class="u">C</span>ontent:</label>
					<div class="bcode-editor">
						<div class="form_toolbar">',
							Html::button_js ('+', "entry_more('tn$id')", 'Agrandir le formulaire'),
							Html::button_js ('-', "entry_lesser('tn$id')", 'Rapetisser le formulaire'),
							Html::button_js ('http://', "textarea_insert('tn$id', '[[', ']]')", 'Insérer un lien (Alt+L)', 'l'),
							Html::button_js ('img', "textarea_insert('tn$id', '{{', '|Texte alternatif}}')", 'Insérer une image (Alt+H)', 'h'),
							Html::button_js ('<span class="b">G</span>', "textarea_insert_around('tn$id', '**')", 'Mettre en gras (Alt+B)', 'b'),
							Html::button_js ('<span class="i">I</span>', "textarea_insert_around('tn$id', '//')", 'Mettre en italique (Alt+I)', 'i'),
							Html::button_js ('<span class="u">S</span>', "textarea_insert_around('tn$id', '__')", 'Souligner (Alt+U)', 'u'),
							Html::button_js ('<span class="s">B</span>', "textarea_insert_around('tn$id', '--')", 'Barrer (Alt+S)', 's'),
						'</div>
						<textarea name="content" cols="24" rows="16" accesskey="c" id="tn',$id,'">',
							$source,
						'</textarea>
					</div>
					<div class="options">
						<ul>
							<li><label title="Whether the news should be visible to all"><input type="checkbox" name="publish" ', ($publish) ? 'checked="checked"' : '' , ' />Publish</label></li>',
							($action == 'edit') ? '<li><label title="Whether not to update modification date and author"><input type="checkbox" name="noupdate" checked="checked" />Hide edition</label></li>' : '',
						'</ul>
					</div>
					<input type="submit" value="Poster" accesskey="p" title="Poster (Alt + P)" />
					<!--input type="reset" value="Réinitialiser" accesskey="x" title="Vider le forumlaire (Alt + X)" /-->
					',$extra_buttons,'
				</div>
			</form>
		</div>';
	}
	
	
	/*** Retriving of data from the DB ***/
	
	protected static function get_published_filter ()
	{
		if (User::has_rights (ADMIN_LEVEL_NEWS)) {
			return array ();
		} else {
			return array ('published' => 1);
		}
	}
	
	/* Gets a list of news
	 * \param $start_offset number of news (from the last one) to skip
	 * \param $n            number of news to get from $start_offset
	 * \returns an array of news or false on failure.
	 */
	public static function get ($start_offset=0, $n=0)
	{
		$news = false;
		
		$db = new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
		$db->select_table (NEWS_TABLE);
		if ($db->select ('*', self::get_published_filter (),
			               array ('id' => 'DESC'), $start_offset, $n)) {
			$news = $db->fetch_all_responses ();
		}
		
		unset ($db);
		return $news;
	}
	
	/* Gets a news by its ID
	 * \param $id the ID of the news to get
	 * \returns a news or false on failure.
	 */
	public static function get_by_id ($id)
	{
		$id = intval ($id);
		
		$db = new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
		$db->select_table (NEWS_TABLE);
		if ($db->select ('*', array_merge (self::get_published_filter (),
			                                 array ('id' => $id)))) {
			return $db->fetch_response ();
		} else {
			return false;
		}
	}
	
	/* Gets the total number of news */
	public static function get_n ()
	{
		$db = new MyDB (DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_TRANSFERT_ENCODING);
		$db->select_table (NEWS_TABLE);
		return $db->count (self::get_published_filter ());
	}
}
