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

/**
 * \brief Gets a string representing a size in the most optimum unit
 * \param $bytes Size to repesent, in bytes
 * \param $round Precision of the displayed size
 * \param $mul   Size of a uint (should not be modified since it displays false
 *               sizes for now.
 * \returns a string of the form 'size unit', e.g. '465.64 Kio'.
 */
function get_size_string ($bytes, $round = 2, $mul = 1024)
{
	$n = 0;
	$size = $bytes;
	$sizes = array (
		'O',
		'Kio',
		'Mio',
		'Gio',
		'Tio'
	);
	
	for ($n = 0; $n < count ($sizes) && $size > $mul; $n ++)
	{
		$size /= $mul;
	}
	
	return round ($size, $round).' '.$sizes[$n];
}

/**
 * \brief Sorts a multidimantional array to one of its 2nd level keys.
 * \param $array_to_sort the multidimetional array to sort
 * \param $sort_key the key of the 2nd level that you want to be sorted by
 * \param $sort_direction the direction to sort. can be SORT_DESC or SORT_ASC.
*/
function array_multisort_2nd (array &$array_to_sort, $sort_key, $sort_direction=SORT_DESC) {
	if (!is_array ($array_to_sort) ||
	    empty ($array_to_sort) ||
	    !is_string ($sort_key)) {
		return false;
	}
	
	$sort_arr = array ();
	foreach ($array_to_sort as $id => &$row) {
		foreach ($row as $key => &$value) {
			$sort_arr[$key][$id] = $value;
		}
	}
	
	array_multisort ($sort_arr[$sort_key], $sort_direction, SORT_REGULAR, $array_to_sort);
	
	return $array_to_sort;
}

/* is this function meanful? hum, if htmlspecialchars() is not good for
 * attributes values (such as title="...") yes. */
function escape_html_quotes (&$str)
{
	return str_replace ('"', '&quot;', $str);
} 

/* prints a portable link button */
function print_button_full ($label, $url, $title=null, $js='')
{
	echo '
	<a href="',htmlspecialchars ($url, ENT_COMPAT, 'UTF-8'),'"
		', $title !== null ? 'title="'.htmlspecialchars ($title, ENT_COMPAT, 'UTF-8').'"' : '','
	   onclick="',$js,'">',
		'<input type="button" value="',htmlspecialchars ($label, ENT_COMPAT, 'UTF-8'),'" />',
	'</a>';
}

function print_button ($label, $url, $title=null)
{
	print_button_full ($label, $url, $title, 'window.location.replace (this.href); return false;');
}

function print_backbutton ($label, $url, $title=null, $back=1)
{
	print_button_full ($label, $url, $title, 'window.history.back ('.$back.'); return false;');
}

function print_button_js ($label, $js, $title=null)
{
	/* the double click handler is to fix a Konqueror bug/problem:
	 * it submits on click unless JS returns false; but even if there's an onclick
	 * handler and no ondblclick one, it use return value of ondblclick on double
	 * click.
	 */
	echo '
	<button onclick="',$js,'; return false;" ondblclick="return false;"
		', $title !== null ? 'title="'.htmlspecialchars ($title, ENT_COMPAT, 'UTF-8').'"' : '','
	  >',
		$label,
	'</button>';
	//print_button_full ($label, '#', $title, $js);
}

/* same as print_button_full() but returns a string instead of printing it.
 * This is slower but needed sometimes */
function get_button_full ($label, $url, $title=null, $js='')
{
	if ($title !== null)
		$title = 'title="'.htmlspecialchars ($title, ENT_COMPAT, 'UTF-8').'"';
	
	return '
	<a href="'.htmlspecialchars ($url, ENT_COMPAT, 'UTF-8').'" '.$title.'
	   onclick="'.$js.'">'.
		'<input type="button" value="'.htmlspecialchars ($label, ENT_COMPAT, 'UTF-8').'" />'.
	'</a>';
}

function get_button ($label, $url, $title=null)
{
	return get_button_full ($label, $url, $title, 'window.location.replace (this.href); return false;');
}

function get_backbutton ($label, $url, $title=null, $back=1)
{
	return get_button_full ($label, $url, $title, 'window.history.back ('.$back.'); return false;');
}

function get_button_js ($label, $js, $title=null)
{
	return get_button_full ($label, '#', $title, $js);
}
