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


/* string utilities */

/* string-related functions */

/* we work on UTF-8 */
mb_internal_encoding ('UTF-8');


function filename_getext ($filename)
{
	$f = basename ($filename);
	
	$e = strrchr ($f, '.');
	if ($e !== false)
		$e = substr ($e, 1);
	
	unset ($f);
	
	return $e;
}

function str_has_prefix ($str, $prefix)
{
	return (strncmp ($str, $prefix, strlen ($prefix)) == 0);
}

function nls2p ($string)
{
	return preg_replace ('#[\r?\n]{2,}#', '</p><p>', $string);
}

function br2nl ($str) {
	return preg_replace ('#<br />#', '', $str);
}

/* return file name without extension and path */
#string file_getname (string filename)
function file_getname ($filename)
{
	$f = basename ($filename);
	
	$e = filename_getext ($f);
	$elen = ($e !== FALSE) ? strlen ($e) + 1 : 0;
	
	$n = substr ($f, 0, strlen ($f) - $elen);
	
	unset ($f, $e, $elen);
	
	return $n;
}


/* truncate (or not) string to obtain a strin of length up to $maxlen */
#string strshortcut (string str, int maxlen)
function strshortcut ($str, $maxlen, $pos=0)
{
	if (($len = strlen ($str)) > $maxlen)
	{
		$maxlen -= 3; /* substract length of '...' */
		$sep = '...';
		
		if ($pos < 0) {
			$str = $sep . substr ($str, $len - $maxlen);
		} else if ($pos > 0) {
			$str = substr ($str, 0, $maxlen) . $sep;
		} else {
			$part = $maxlen/2;
			$str = substr ($str, 0, $part) . '...' . substr ($str, $len - $part);
		}
	}
	
	return $str;
}

/* truncate string of XML data to obtain a string of the given maximum length.
 * If needed, ellipsis is added at the end of the XML data
 * This function only truncates data in tags and keeps all tags and their
 * attributes unchanged unless they don't appear in first characters. */
function xmlstr_shortcut ($xml, $maxlen, $ellipsis='…')
{
	$stack = array ();
	$item;
	$shortxml = '';
	$end = true;
	$j = 0;
	$i;
	
	/* last pass is only to know it there's leaved data to know whether to add
	 * ellipsis */
	for ($i = 0; $i < strlen ($xml) && $j <= $maxlen; $i++)
	{
		$xml[$i];
		
		if ($xml[$i] == '<' && $xml[$i + 1] != '/')
		{
			$name_len = strpos ($xml, '>', $i + 1) - $i;
			$k;
			$item = '';
			if ($name_len === false)
				return 'Bad XML data';
			
			if ($j < $maxlen)
			{
				/* if not a short tag, add to stack */
				if ($xml[$i + $name_len - 1] != '/')
				{
					for ($k = 1; $k < $name_len; $k++)
					{
						if (strchr (" \t\n", $xml[$i+$k]) === false)
							$item .= $xml[$i+$k];
						else
							break;
					}
					array_push ($stack, $item);
				}
				$shortxml .= substr ($xml, $i, $name_len + 1);
			}
			$i += $name_len;
		}
		else if ($xml[$i] == '<' && $xml[$i + 1] == '/')
		{
			$item = array_pop ($stack);
			if ($j < $maxlen)
			{
				$shortxml .= '</'.$item.'>';
			}
			$i += strlen ($item) + 2;
		}
		else
		{
			if ($j >= $maxlen)
				$end = false;
			else
			{
				$end = true;
				$shortxml .= $xml[$i];
			}
			$j++;
		}
	}
	
	if (! $end)
		$shortxml .= $ellipsis;
	
	while (($item = array_pop ($stack)) !== null)
	{
		$shortxml .= '</'.$item.'>';
	}
	
	return $shortxml;
}

//echo xmlstr_shortcut ('<span class="patate">coucou<br /> les gus&nbsp;!</span>', 10), "\n";


function path_clean ($str)
{
	$c = '';
	$clean = '';
	$strlen = strlen ($str);
	for ($i = 0; $i < $strlen; $i++)
	{
		if (! ($c == '/' && $c == $str[$i]))
		{
			$clean .= $str[$i];
		}
		$c = $str[$i];
	}
	return $clean;
}

function mime_type_from_ext ($ext)
{
	switch (strtolower ($ext))
	{
		/* Images */
		case 'png':
			return 'image/png';
		case 'jpg':
		case 'jpeg':
			return 'image/jpeg';
		case 'gif':
			return 'image/gif';
		case 'svg':
			return 'image/svg+xml';
		case 'tif':
		case 'tiff':
			return 'image/tiff';
		/* Videos */
		case 'ogm':
		case 'ogg':
		case 'ogv':
			return 'video/x-ogm';
		case 'mkv':
			return 'video/x-matroska';
		case 'flv':
			return 'video/x-flv';
		case 'mpg':
		case 'mpeg':
			return 'video/mpeg';
		case 'mp4':
		case 'mpeg4':
		case 'm4v':
			return 'video/mp4';
		case 'avi':
			return 'video/avi';
		case 'mov':
		case 'qt':
			return 'video/quicktime';
		case 'wmv':
			return 'video/x-ms-wmv';
		
		default:
			return 'application/octet-stream';
	}
}

function filename_get_mime_type ($filename)
{
	return mime_type_from_ext (filename_getext ($filename));
}

function normalize_string_for_url ($str, $repl_char='-')
{
	$str = mb_strtolower ($str/*, 'UTF-8'*/);
	$str = str_replace (array ('à','â','ä','ã','å','ǎ','ą',
	                           'é','è','ê','ë','ẽ','ě','ȩ','ę',
	                           'î','ï','ĩ','ǐ','į',
	                           'ô','ö','õ','ǒ','ǫ',
	                           'ù','û','ü','ũ','ů','ǔ','ų',
	                           'ŷ','ÿ','ỹ','ẙ'),
	                    array ('a','a','a','a','a','a','a',
	                           'e','e','e','e','e','e','e','e',
	                           'i','i','i','i','i',
	                           'o','o','o','o','o',
	                           'u','u','u','u','u','u','u',
	                           'y','y','y','y'),
	                    $str);
	$final_str = '';
	$prev_used = true;
	$str_len = strlen ($str);
	/* don't worry about MB strings here, we use UTF-8: sub-bytes don't match any
	 * character */
	for ($i=0; $i < $str_len; $i++)
	{
		if (strpos ('abcdefghijklmnopqrstuvwxyz0123456789-+', $str[$i]) === false)
		{
			if ($prev_used)
				$final_str .= $repl_char;
			$prev_used = false;
		}
		else
		{
			$final_str .= $str[$i];
			$prev_used = true;
		}
	}
	
	return rtrim ($final_str, $repl_char);
}

/* naive and maybe stupid email obfuscation */
function obfuscate_email ($email)
{
	$parts = explode ('@', $email);
	$first = true;
	$obf = '';
	
	$obf .= '<a class="email" href="#" onclick="return unobfuscate_email (this);">';
	foreach ($parts as &$part) {
		if (! $first) {
			$obf .= '<span class="at"></span>';
		}
		$first = true;
		foreach (explode ('.', $part) as $sub_part) {
			if (! $first) {
				$obf .= '<span class="dot"></span>';
			}
			$obf .= '<span>'.$sub_part.'</span>';
			$first = false;
		}
		$first = false;
	}
	$obf .= '</a>';
	
	return $obf;
}
