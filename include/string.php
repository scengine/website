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


/* string utilities */

/* string-related functions */


function filename_getext ($filename)
{
  $f = basename ($filename);
  
  $e = strrchr ($f, '.');
  if ($e !== false)
    $e = substr ($e, 1);
  
  unset ($f);
  
  return $e;
}

function path_add_filename_prefix ($path, $prefix) {
  return dirname ($path).'/'.$prefix.basename ($path);
}

function str_has_prefix ($str, $prefix)
{
  if (substr ($str, 0, strlen ($prefix)) == $prefix)
    return true;
  
  return false;
}

/* return the start of a string until @p $c is found */
#string sstrchr (string str, char c)
function sstrchr ($str, $c)
{
  $f = '';
  
  for ($i = 0; $str[$i] != $c && $str[$i] !== False; $i++)
    $f .= $str[$i];
  
  return $f;
}


/* check if a filename has the prfix $prfix
 * note tha $filename can be a path, only th prefix of the filename will be checked */
function filename_has_prefix ($path, $prefix)
{
   return str_has_prefix (basename ($path), $prefix);
}

/* check if a file have an extension */
#boolean file_hasext (string filename)
function file_hasext ($filename)
{
   return (filename_getext ($filename) === false) ? false : true;
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
function strshortcut ($str, $maxlen)
{
  if (($len = strlen ($str)) > $maxlen)
  {
    $maxlen -= 3; /* substract length of '...' */
    $part = $maxlen/2;
    
    $nstr = substr ($str, 0, $part);
    $nstr .= '...';
    $nstr .= substr ($str, $len - $part);
    
    $str = $nstr;
  }
  
  return $str;
}

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
