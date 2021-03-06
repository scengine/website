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


$XHTML = array (
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
  'align_left'  => 'class="align_left"', /*<! attr */
  'align_right' => 'class="align_right"', /*<! attr */
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


function remove_special_chars ($str)
{
  $valid = array (
    'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r',
        's','t','u','v','w','x','y','z','_',
    '0','1','2','3','4','5','6','7','8','9'
  );
  
  $srch = array (
    array ('à','ā','ä','â','ã','å','ą'),
    array ('č','ç'),
    array ('è','ē','ë','ê','é','ě','ę'),
    array ('ģ'),
    array ('ì','ī','ï','î','ĩ','į'),
    array ('ķ'),
    array ('ļ'),
    array ('ñ','ņ'),
    array ('ò','ō','ö','ô','õ'),
    array ('ŗ'),
    array ('ş','š'),
    array ('ù','ū','ü','û','ũ','ů','ų'),
    array ('ÿ'),
    array ('ž')
  );
  $rplc = array (
    'a',
    'c',
    'e',
    'g',
    'i',
    'k',
    'l',
    'n',
    'o',
    'r',
    's',
    'u',
    'y',
    'z'
  );
  
  $str = strtolower ($str);
  foreach ($srch as $k => $v)
    $str = str_replace ($v, $rplc[$k], $str);
  
  $len = strlen ($str);
  for ($i=0; $i < $len; $i++)
  {
    if (!in_array ($str[$i], $valid))
      $str[$i] = '_';
  }
  
  unset ($len, $i);
  
  return $str;
}

define ('PARSER_PREV_NOPARSE', -1);

define ('PARSER_PREV_NONE',  0);
define ('PARSER_PREV_TITLE', 1);
define ('PARSER_PREV_P',     2);
define ('PARSER_PREV_UL',    3);
define ('PARSER_PREV_OL',    4);
define ('PARSER_PREV_QUOTE', 5);
define ('PARSER_PREV_HR',    6);
define ('PARSER_PREV_CODE',  7);
define ('PARSER_PREV_TABLE', 8);
define ('PARSER_PREV_COMMENT', 9);
  
  
class WKParser
{
  /* Parser de WikiCode-like
   * simple et rapide mais linéaire et peu puissant
   * noshit, pas si rapide que ça je dirais vu le nombre de regex et la
   * stupidité de certains bouts de code...  */
  /* Il marche bien tant qu'on est gentil avec lui :D */
  /**
   * TODO:
   * 
   * amélioration des tableaux
   * */
  
  
  protected $language;
  protected $opened_tags;
  protected $prev;
  protected $parsed;
  protected $ids;
  
  public function __construct ($str, &$language)
  {
    $this->language = $language;
    $this->opened_tags = array ();
    $this->prev = PARSER_PREV_NONE;
    $this->ids = array ();
    $line='';
    
    ob_start ();
    
    for ($i=0; isset ($str[$i]); $i++)
    {
      $cur = $str[$i];
      
      if ($cur == "\r")
      {
        if (! isset ($str[$i+1]) || $str[$i+1] != "\n")
          $cur = "\n";
        else
          continue;
      }
      
      if ($cur == "\n")
      {
        if (!$this->parse_wkc_query ($line))
        {
          if (($line = $this->parse_comments ($line)) !== false)
          {
            $line = $this->parse_title ($line);
            $line = $this->parse_hr ($line);
            $line = $this->parse_ul ($line);
            $line = $this->parse_ol ($line);
            $line = $this->parse_quote ($line);
            $line = $this->parse_code ($line);
            $line = $this->parse_table ($line);
            $line = $this->parse_paragraph ($line);
            //$line = $this->parse_links ($line);
            //$line = $this->parse_other ($line);
          }
        }
        
        echo $line; //, "\n";
        $line = '';
      }
      else
        $line .= $cur;
      
      if (! isset ($str[$i+1]) && $cur != "\n")
        $str[$i+1] = "\n";
    }
    
    $this->close_opened_tags ();
    
    $this->parsed = ob_get_contents ();
    ob_end_clean ();
  }
  
  private function set_prev ($v)
  {
    $this->prev = $v;
  }
  private function get_prev ()
  {
    return $this->prev;
  }
  
  private function open_tag ($tag, $attrs = array ())
  {
    array_push ($this->opened_tags, $tag);
    echo '<',$tag;
    foreach ($attrs as $attr => $value) {
      echo $this->attr_s ($attr, $value);
    }
    echo '>';
  }
  
  private function get_close_tag ($tag)
  {
    $ctag = $tag;
    
    /* pour gérer les attributs */
    $pos = strpos ($tag, ' ');
    if ($pos)
      $ctag = substr ($tag, 0, $pos);
    
    return $ctag;
  }
  
  private function close_tag ($tag)
  {
    do
    {
      $cur = array_pop ($this->opened_tags);
      
      if ($cur === Null) break;
      
      echo '</',$this->get_close_tag ($cur),'>';
    }
    while ($cur != $tag);
  }
  
  private function close_opened_tags ()
  {
    $this->close_tag (Null);
  }
  
  /* creates a string representing XML attribute @name with value @value */
  private function attr_s ($name, $value)
  {
    /* FIXME: handle special chars in @value */
    return ' ' . $name . '="' . $value . '"';
  }
  
  /* creates a string representing XML tag @name with data @data and
   * attributes @attrs */
  private function tag_s ($name, $data = null, $attrs = array ())
  {
    $tag = '<' . $name;
    
    foreach ($attrs as $attr => $value) {
      $tag .= $this->attr_s ($attr, $value);
    }
    if ($data !== null) {
      $tag .= '>' . $data . '</' . $this->get_close_tag ($name) . '>';
    } else {
      $tag .= '/>';
    }
    
    return $tag;
  }
  
  private function id_from_string ($str)
  {
    $id = remove_special_chars ($str);
    if (ctype_digit ($id[0]))
      $id = '_'.$id;
    
    $nid = $id;
    $i = 2;
    while (in_array ($nid, $this->ids))
      $nid = $id.'_'.$i++;
    $this->ids[] = $nid;
    
    unset ($i, $id);
    
    return $nid;
  }
  
  private function parse_wkc_query (&$line)
  {
    $founds = array ();
    
    if (preg_match ('#^wkcp>(.*)#', $line, $founds))
    {
      $query = trim ($founds[1]);
      
      if ($query == 'noparse')
      {
        $this->set_prev (PARSER_PREV_NOPARSE);
      }
      else if ($query == 'parse')
      {
        $this->set_prev (PARSER_PREV_NONE);
      }
      else
      {
        echo '
          <div style="color:red;">
          <strong>ERROR</strong>: unknown parser query
          </div>';
      }
      
      $line = '';
      return true;
    }
    if ($this->get_prev () == PARSER_PREV_NOPARSE)
      return true;
    
    return false;
  }
  
  private function parse_title ($line)
  {
    $founds;
    
    if (preg_match ('#^[ \t]*(={2,6})(.*)#', $line, $founds))
    {
      if ($pos = strpos ($founds[2], $founds[1]))
      {
        $title;
        switch (strlen($founds[1]))
        {
          case 6: $title = 'title1'; break;
          case 5: $title = 'title2'; break;
          case 4: $title = 'title3'; break;
          case 3: $title = 'title4'; break;
          case 2: $title = 'title5'; break;
          case 1: $title = 'title6'; break;
        }
        $title = $this->language[$title];
        
        $content = trim (substr ($founds[2], 0, $pos));
        $id = $this->id_from_string ($content);
        
        $this->close_opened_tags ();
        $this->open_tag ($title, array ($this->language['id'] => $id));
        echo $this->parse_all_inline ($content);
        $this->close_tag ($title);
        
        $this->set_prev (PARSER_PREV_TITLE);
        
        return /*"\n\n" .*/ substr ($founds[2], $pos + strlen($founds[1])) /*. "\n"*/;
      }
    }
    
    return $line;
  }
  
  private function parse_ul ($line)
  {
    static $level = 0;
    $founds = array ();
    
    if (preg_match ('#^([ \t]{2,})\* ?(.*)#', $line, $founds))
    {
      $c_level = strlen ($founds[1]) -2;
      
      if ($c_level < $level)
        $this->close_tag ($this->language['ul']);
      
      if ($this->get_prev () != PARSER_PREV_UL)
      {
        $this->close_opened_tags ();
        $this->open_tag ($this->language['ul']);
      }
      else
      {
        if ($c_level > $level)
          $this->open_tag ($this->language['ul']);
        else
          $this->close_tag ($this->language['li']);
      }
      
      $this->open_tag ($this->language['li']);
      //echo trim ($this->parse_links ($this->parse_other ($founds[2])));
      echo trim ($this->parse_all_inline ($founds[2]));
      //$this->close_tag ($this->language['li']);
      
      $this->set_prev (PARSER_PREV_UL);
      $level = $c_level;
    }
    else
    {
      if ($this->get_prev () == PARSER_PREV_UL)
      {
        $this->close_tag ($this->language['ul']);
        $this->set_prev (PARSER_PREV_NONE);
      }
      $level = 0;
    }
    
    return ($this->get_prev () != PARSER_PREV_UL) ? $line : false;
  }
  
  private function parse_ol ($line)
  {
    static $level = 0;
    $founds = array ();
    
    if (preg_match ('#^([ \t]{2,})- ?(.*)#', $line, $founds))
    {
      $c_level = strlen ($founds[1]) -2;
      
      if ($c_level < $level)
        $this->close_tag ($this->language['ol']);
      
      if ($this->get_prev () != PARSER_PREV_OL)
      {
        $this->close_opened_tags ();
        $this->open_tag ($this->language['ol']);
      }
      else
      {
        if ($c_level > $level)
          $this->open_tag ($this->language['ol']);
        else
          $this->close_tag ($this->language['li']);
      }
      
      $this->open_tag ($this->language['li']);
      //echo trim ($this->parse_links ($this->parse_other ($founds[2])));
      echo trim ($this->parse_all_inline ($founds[2]));
      //$this->close_tag ($this->language['li']);
      
      $this->set_prev (PARSER_PREV_OL);
      $level = $c_level;
    }
    else
    {
      if ($this->get_prev () == PARSER_PREV_OL)
      {
        $this->close_tag ($this->language['ol']);
        $this->set_prev (PARSER_PREV_NONE);
      }
      $level = 0;
    }
    
    return ($this->get_prev () != PARSER_PREV_OL) ? $line : false;
  }
  
  private function parse_quote ($line)
  {
    static $level = 0;
    $founds = array ();
    
    if (preg_match ('#^[ \t]+((?:> *)+)(.*)#', $line, $founds))
    {
      $c_level = strlen (str_replace (' ', '', $founds[1])) -1;
      
      if ($c_level < $level)
      {
        $this->close_tag ($this->language['p']);
        $this->close_tag ($this->language['quote']);
        $this->open_tag ($this->language['p']);
      }
      
      if ($this->get_prev () != PARSER_PREV_QUOTE)
      {
        $this->close_opened_tags ();
        $this->open_tag ($this->language['quote']);
        $this->open_tag ($this->language['p']);
      }
      else
      {
        if ($c_level > $level)
        {
          $this->close_tag ($this->language['p']);
          $this->open_tag ($this->language['quote']);
          $this->open_tag ($this->language['p']);
        }
      }
      
      if (trim ($founds[2]))
      {
        //$this->open_tag ($this->language['p']);
        //echo trim ($this->parse_links ($this->parse_other ($founds[2])));
        echo trim ($this->parse_all_inline ($founds[2])), ' ';
        //$this->close_tag ($this->language['li']);
      }
      else
      {
        $this->close_tag ($this->language['p']);
        $this->open_tag ($this->language['p']);
      }
      
      $this->set_prev (PARSER_PREV_QUOTE);
      $level = $c_level;
    }
    else
    {
      if ($this->get_prev () == PARSER_PREV_QUOTE)
      {
        $this->close_tag ($this->language['p']);
        $this->close_tag ($this->language['quote']);
        $this->set_prev (PARSER_PREV_NONE);
      }
      $level = 0;
    }
    
    return ($this->get_prev () != PARSER_PREV_QUOTE) ? $line : false;
  }
  
  private function parse_table ($line)
  {
    $founds = array ();
    $in_tr = false;
    $i = 0;
    
    for ($i=0; preg_match ('#^[ \t]*(\||\^)([^\|\^]*)#', $line, $founds); $i++)
    {
      //print_r ($founds);
      if ($this->get_prev () != PARSER_PREV_TABLE)
      {
        $this->close_opened_tags ();
        $this->open_tag ('table');
      }
      
      if (!$in_tr)
        $this->open_tag ('tr');
      
      if ($founds[2] != '')
      {
        echo $this->tag_s (($founds[1] == '^') ? 'th' : 'td',
                           trim ($this->parse_all_inline ($founds[2])));
      }
      
      $in_tr = true;
      $this->set_prev (PARSER_PREV_TABLE);
      $line = preg_replace ('#^[ \t]*(?:\||\^)[^\|\^]*#', '', $line, 1);
    }
    
    if ($i>0 && $in_tr)
      $this->close_tag ('tr');
    
    if ($i==0)
    {
      if ($this->get_prev () == PARSER_PREV_TABLE)
      {
        $this->close_tag ($this->language['table']);
        $this->set_prev (PARSER_PREV_NONE);
      }
    }
    
    return $line;
  }
  
  private function parse_code ($line)
  {
    $founds;
    
    if (preg_match ('#^([ \t]{2,})(~)?(.*)$#', $line, $founds))
    {
      /* ltrim => to avoid empty code */
      $void = (!$founds[2] && (ltrim ($founds[3]) == ''));
      
      if ($this->get_prev () != PARSER_PREV_CODE && !$void)
      {
        $this->close_opened_tags ();
        $this->open_tag ($this->language['code']);
      }
      else
        echo "\n"; /* recreate eol */
      
      //$this->open_tag ($this->language['code']);
      /* une ligne vide n'est pas affichée par <pre> */
      echo $this->parse_entities ($founds[3], true); //(ltrim($founds[2])) ? $founds[2] : ' ';
      //$this->close_tag ($this->language['li']);
      
      if (!$void)
        $this->set_prev (PARSER_PREV_CODE);
    }
    else
    {
      if ($this->get_prev () == PARSER_PREV_CODE)
      {
        $this->close_tag ($this->language['code']);
        $this->set_prev (PARSER_PREV_NONE);
      }
    }
    
    return ($this->get_prev () != PARSER_PREV_CODE) ? $line : false;
  }
  
  private function parse_paragraph ($line)
  {
    if (trim ($line))
    {
      if ($this->get_prev () != PARSER_PREV_P)
      {
        $this->close_opened_tags ();
        $this->open_tag ($this->language['p']);
      }
      else
        echo $this->tag_s ($this->language['newline']); /* newlines are handeled as
                                                         * newlines (Yno's choice...
                                                         * below, single newline are
                                                         * handeled as nothing, and
                                                         * only \\ break lines.) */
        //echo ' '; // un sepace pour séparer les mots entre les retours de lignes
      //echo trim ($this->parse_links ($this->parse_images ($this->parse_other ($line))));
      echo trim ($this->parse_all_inline ($line));
      $this->set_prev (PARSER_PREV_P);
    }
    else
    {
      if ($this->get_prev () == PARSER_PREV_P)//$in_p)
      {
        $this->close_tag ($this->language['p']);
        $this->set_prev (PARSER_PREV_NONE);
      }
    }
    
    return ($this->get_prev () != PARSER_PREV_P) ? $line : false;
  }
  
  private function parse_hr ($line)
  {
    if (preg_match ('#^[ \t]*-{4,}[ \t]*$#', $line))
    {
      $this->close_opened_tags ();
      echo $this->tag_s ($this->language['hr']);
      $this->set_prev (PARSER_PREV_HR);
      return '';
    }
    return $line;
  }
  
  private function parse_comments ($line)
  {
    static $prev;
    
    if ($this->get_prev () != PARSER_PREV_COMMENT)
    {
      $prev = $this->get_prev ();
    }
    
    if (str_has_prefix ($line, '!!'))
    {
      if ($this->get_prev () != PARSER_PREV_COMMENT)
      {
        echo '<!--';
      }
      //echo '<!-- ';
      echo str_replace ('--', '&#45;&#45;', substr ($line, 2)), "\n";
      //echo  ' -->';
      
      $this->set_prev (PARSER_PREV_COMMENT);
      return false;
    }
    elseif ($this->get_prev () == PARSER_PREV_COMMENT)
    {
      echo '-->';
      $this->set_prev ($prev);
    }
    
    return $line;
  }
  
  private function parse_links ($line)
  {
    $founds = array ();
    
    while (preg_match ('#\[\[(.*)(?:\|(.*))?\]\]#U', $line, $founds))
    {
      $href = $founds[1];
      $title = isset ($founds[2]) ? $founds[2] : null;
      
      /* support for easy Wikipedia links of the format "wp[lang]:Page" */
      if (str_has_prefix ($href, 'wp')) {
        $lang = null;
        
        $pos = strpos ($href, ':', 2);
        if ($pos == 2) {
          $lang = 'en'; /* default language is English */
        } else if ($pos >= 4 && $pos <= 5) {
          /* otherwise, use the 2/3 character after "wp" as the language */
          $lang = substr ($href, 2, $pos - 2);
        }
        
        if ($lang) {
          $page = substr ($href, $pos + 1);
          
          /* if the link has no title, use the page name */
          if ($title === null) {
            $title = $page;
          }
          $href = 'http://' . $lang . '.wikipedia.org/wiki/'. $page;
        }
      }
      
      $link = $this->tag_s ($this->language['link'],
                            isset ($title) ? $title : $href,
                            array ($this->language['link_addr'] => $href));
      
      $line = preg_replace ('#\[\[.*\]\]#U', $link, $line, 1);
    }
    
    return $line;
  }
  
  private function parse_images ($line)
  {
    $founds = array ();
    
    while (preg_match ('#\{\{( +)?([^ ].*)(?:\?([0-9]*(?:x[0-9]+)?))?( +)?(?:\|(.*))?\}\}#U', $line, $founds))
    {
      //print_r ($founds);
      $obj_attrs = array ();
      $img_attrs = array (
        $this->language['img_src'] => $founds[2],
        $this->language['img_alt'] => (isset ($founds[5]) ? $founds[5] : $founds[2])
      );
      
      if (!empty ($founds[1]) || !empty ($founds[4]))
      {
        $lmargin = strlen ($founds[1]);
        $rmargin = strlen ($founds[4]);
        
        if ($lmargin < $rmargin)
          $align = $this->language['align_left'];
        elseif ($lmargin > $rmargin)
          $align = $this->language['align_right'];
        else
          $align = $this->language['align_center'];
        
        $p = explode ('=', $align, 2);
        $obj_attrs[$p[0]] = trim ($p[1], '"');
        
        unset ($lmargin, $rmargin, $p);
      }
      
      if (! empty ($founds[3])) {
        $tmp = explode ('x', $founds[3], 2);
        
        if (isset ($tmp[0])) {
          $img_attrs['width'] = $tmp[0];
        }
        if (isset ($tmp[1])) {
          $img_attrs['width'] = $tmp[1];
        }
      }
      
      $img = $this->tag_s ($this->language['inline_obj'],
                           $this->tag_s ($this->language['img'],
                                         null,
                                         $img_attrs),
                           $obj_attrs);
      
      $line = preg_replace ('#\{\{.*\}\}#U', $img, $line, 1);
    }
    
    return $line;
  }
  
  
  private function parse_abbr ($line)
  {
    $founds = array ();
    
    while (preg_match ('#\(\((.*)(?:\|(.*))?\)\)#U', $line, $founds))
    {
      $abbr = $this->tag_s ('abbr',
                            isset ($founds[2]) ? $founds[2] : $founds[1],
                            array ('title' => $founds[1]));
      
      $line = preg_replace ('#\(\(.*\)\)#U', $abbr, $line, 1);
    }
    
    return $line;
  }
  
  private function parse_other ($line)
  {
    $line = $this->parse_entities ($line);
    $line = preg_replace ('#([^:/]|^)//(.*)//#U', '$1' . $this->tag_s ($this->language['italic'], '$2'), $line);
    $line = preg_replace ('#\*\*(.*)\*\*#U', $this->tag_s ($this->language['bold'], '$1'), $line);
    $line = preg_replace ('#__(.*)__#U', $this->tag_s ($this->language['underlined'], '$1'), $line);
    $line = preg_replace ('#--(.*)--#U', $this->tag_s ($this->language['strike'], '$1'), $line);
    $line = preg_replace ('#\'\'(.*)\'\'#U', $this->tag_s ($this->language['smallcode'], '$1'), $line);
    /* removed \\ below because now the newlines in paragraphs are handeled as
     * newlines */
    //$line = str_replace ('\\\\', $this->tag_s ($this->language['newline']), $line);
    return $line;
  }
  
  private function parse_entities ($line, $simple = false)
  {
    $line = str_replace ('&', '&'.$this->language['entity_amp'].';', $line);
    $line = str_replace ('<', '&'.$this->language['entity_lt'].';', $line);
    $line = str_replace ('>', '&'.$this->language['entity_gt'].';', $line);
    //$line = str_replace ('"', '&'.$this->language['entity_quote'].';', $line);
    if (! $simple) {
      $line = preg_replace ('# +([?!:;])#', '&'.$this->language['entity_space'].';$1', $line);
    }
    return $line;
  }
  
  private function parse_all_inline ($line)
  {
    $line = $this->parse_other ($line);
    $line = $this->parse_abbr ($line);
    $line = $this->parse_images ($line);
    $line = $this->parse_links ($line);
    
    return $line;
  }
  
  
  public function get_xml ()
  {
    return $this->parsed;
  }
}

