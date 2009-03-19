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

/* parser for BanCode */

require_once ('WKParser.php');

abstract class BCode
{
  protected static $XHTML = array (
    'id'        => 'id', /*<! attr */
    'title1'    => 'h1',
    'title2'    => 'h2',
    'title3'    => 'h3',
    'title4'    => 'h4',
    'title5'    => 'h5',
    'title6'    => 'h6',
    'ul'        => 'ul',
    'ol'        => 'ol',
    'li'        => 'li',
    'p'         => 'p',
    'link'      => 'a',
    'link_addr' => 'href', /*<! attr */
    'img'       => 'img', /*<! short */
    'img_src'   => 'src', /*<! attr */
    'img_alt'   => 'alt', /*<! attr */
    'align_left'  => 'class="fleft"', /*<! attr */
    'align_right' => 'class="fright"', /*<! attr */
    'align_center'=> 'class="align_center"', /*<! attr */
    'italic'    => 'em',
    'bold'      => 'strong',
    'underlined'=> 'span class="u"',
    'strike'    => 'span class="s"',
    'newline'   => 'br', /*<! short */
    'hr'        => 'hr', /*<! short */
    'quote'     => 'blockquote',
    'code'      => 'pre class="code"',
    'smallcode' => 'code',
    'inline_obj'=> 'span',
    
    'entity_space' => 'nbsp',
    'entity_amp'   => 'amp',
    'entity_gt'    => 'gt',
    'entity_lt'    => 'lt',
    'entity_quote' => 'quote'
  );
  
  public function parse ($str)
  {
    $rv;
    
    $p = &new WKParser ($str, self::$XHTML);
    $rv = $p->get_xml ();
    unset ($p);
    
    return $rv;
  }
  
  public function unparse ($str)
  {
    return $str;
  }
}

?>
