<?php
/* LICENSE
 * 
 * BanSE - a site base (designed to be the SCEngine website)
 * Copyright (C) 2007-2008 Colomban "Ban" Wendling <ban-ubuntu@club-internet.fr>
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

/*
 * Written by Antony "Yno" Martin <ynodark@hotmail.fr>
 * Modified by Colomban Wending (see BanSE credits)
 */

/*
 * Media gesture (screenshots & movies)
 */

define (TITLE, 'Médias');

/*
define (SCREENS, 'ftp://yno@scengine:accesftpyno@downloads.goldzoneweb.info/screens/');
define (MOVIES, 'ftp://yno@scengine:accesftpyno@downloads.goldzoneweb.info/movies/');
*/
define (SCREENS, 'screens/');
define (MOVIES, 'movies/');


// dimensions des miniatures
define (TB_W, 160.0);
define (TB_H, 120.0);

// prefixe des miniatures
define (TB, 'tb_');
define (COMMENT, 'comment_');

define (GALLERY_DIR_PIC, 'screens/');
define (GALLERY_DIR_VID, 'movies/');


require_once ('include/defines.php');
require_once ('include/string.php');
require_once ('include/Header.php');


// renvoie la miniature d'une image
function get_thumb ($dir, $fimg)
{
   $tb = $dir.'/'.TB.$fimg;
   
   if (!file_exists ($tb))
   {
      // generation de l'image //
      echo 'generation de la miniature pour ', $fimg, '<br />';
      
      $imgsrc; // image source
      
      // on recupere l'extension
      $ext = file_getext ($fimg);
      
      // PNG
      if ($ext == 'png')
         $imgsrc = @imagecreatefrompng ($dir.$fimg);
      // JPEG
      elseif ($ext == 'jpg' || $ext == 'jpeg')
         $imgsrc = @imagecreatefromjpeg ($dir.$fimg);
      // unknown
      else
      {
         // format non supporte, c'est probablement une video
         echo 'unknown format';
         return $tb;
      }
      
      if (!$imgsrc) /* on vérifie que l'imgae soit bien chargée */
      {
         echo 'Not an image';
         return $tb;
      }
      
      // calcul des dimensions de sortie
      
      // dimensions de la source
      $sw = imagesx ($imgsrc);
      $sh = imagesy ($imgsrc);
      $dw = $sw;
      $dh = $sh;
      
      if (($sh + $sh/3.0) > $sw)
      {
         $div = $sh/TB_H;
         $dh = TB_H;
         $dw = $sw/$div;
      }
      else
      {
         $div = $sw/TB_W;
         $dw = TB_W;
         $dh = $sh/$div;
      }
      
      // on centre l'image
      $dx = TB_W/2 - $dw/2;
      $dy = TB_H/2 - $dh/2;
      
      // miniature
      $imgdst = imagecreatetruecolor (TB_W, TB_H);
      
      imagecopyresampled ($imgdst, $imgsrc, $dx, $dy, 0, 0, $dw, $dh, $sw, $sh);
      
      // PNG
      if ($ext == 'png')
         imagepng ($imgdst, $tb);
      // JPEG
      else
         imagejpeg ($imgdst, $tb);
   }
   
   return $tb;
}

// affiche le commentaire d'une image
function get_comment ($dir, $file)
{
   $cmm = $dir.'/'.COMMENT.$file;
   
   if (file_exists ($cmm))
   {
      $cmm = htmlspecialchars (htmlspecialchars (file_get_contents ($cmm)));
      
      $cmm = nls2p ($cmm);
   }
   else
      $cmm = '';
   
   return $cmm;
}

// affiche le contenu d'un dossier de version
function print_content ($directory)
{
   // tableau des fichiers
   $files = array ();
   
   if ($dir = opendir ($directory))
   {
      $i = 0;
      while (False !== ($file = readdir ($dir)))
      {
         if (!is_dir ($directory.$file) &&
             $file != '.' &&
             $file != '..' &&
             substr ($file, 0, strlen (TB)) != TB &&
             substr ($file, 0, strlen (COMMENT)) != COMMENT)
         {
            $files[$i] = $file;
            $i++;
         }
      }
      
      if ($i == 0)
         echo '<p>Aucun média de cette version n\'est disponible pour le moment.</p>';
      else
      {
         rsort ($files);
         
         for ($j = 0; $j < $i; $j++)
         {
            $img = $directory.$files[$j];
            $tb = get_thumb ($directory, $files[$j]);
            $comment = get_comment ($directory, $files[$j]);
            $imgname = basename ($img);
            
            /** TODO: et pour les videos ? sux... comment on distingue une video d'un screenshot ? :D */
            
            echo '<div class="mediacontainer">
                     <div class="media">
                        <a href="?type=pic&amp;watch=', $img ,'#watch" title="Voir « ', $imgname, ' »">
                           <img src="', $tb, '" alt="image" />
                        </a>
                     </div>
                     <div class="links">
                        [<a href="?type=pic&amp;watch=', $img, '#watch" title="Voir « ', $imgname, ' »">Voir</a>]
                        [<a href="?dwl=', $img, '" title="Télécharger « ', $imgname, ' »">Télécharger</a>]
                     </div>';
            if ($comment)
            {
               echo '
                     <div class="comment">
                        <p>
                           ', $comment, '
                        </p>
                     </div>';
            }
            echo '
                  </div>';
         }
      }
      
      closedir ($dir);
   }
   
   unset ($files);
}

// affiche tout
function print_medias ($directory)
{
   // noms des dossiers des versions
   $versions = array ();
   
   if ($dir = opendir ($directory))
   {
      $i = 0;
      while (False !== ($file = readdir ($dir)))
      {
         // tant qu'on lit un dossier, et que celui-ci n'est pas . ou ..
         if (is_dir ($directory.$file) &&
             $file != '.' &&
             $file != '..')
         {
            // on stocke le nom du dossier
            $versions[$i] = $file;
            $i++;
         }
      }
        
      if ($i == 0)
            echo '<p>Aucune version pour cette catégorie n\'est disponible pour le moment.</p>';
      else
      {
         rsort ($versions);
         
         for ($j = 0; $j < $i; $j++)
         {
            echo '<h4 class="mediatitle">Version ', $versions[$j], '</h4>';
            print_content ($directory.$versions[$j].'/');
         }
      }
      
      closedir ($dir);
   }
}

function media_is_in_gallery ($med)
{
   if (str_has_prefix ($med, GALLERY_DIR_PIC) ||
       str_has_prefix ($med, GALLERY_DIR_VID))
   {
      if (file_exists ($med))
         return true;
   }
   
   return false;
}


function watch_picture ($pic)
{
	$picname = basename ($pic);
	
	echo '<h2 id="watch">', $picname, '</h2>';
	
	if (!media_is_in_gallery ($pic))
		echo '<p>Le fichier demandé n\'existe pas&nbsp;!</p>';
	else
	{
		echo '<a href="', $pic, '" onclick="window.open(this.href, \'', $picname, '\', \'status=no, directories=no, toolbar=no, location=no, menubar=no, scrollbars=yes\'); return false;" title="Taille entière (nouvelle fenêtre)">
					<img src="', $pic, '" alt="', $picname, '" style="max-width:100%;" />
				</a>';
	}
	
	echo '<div class="links">
				[<a href="?dwl=', $pic, '" title="Télécharger « ', $picname, ' »">Télécharger</a>]
			</div>';
	
	unset ($picname);
}

function watch_movie ($mov)
{
   echo '<h2 id="watch">Lecture de ', basename ($mov), '</h2>';
   
	if (!media_is_in_gallery ($pic))
		echo '<p>Le fichier demandé n\'existe pas&nbsp;!</p>';
	else
	{
		echo '<div class="movie">
				<object type="application/x-shockwave-flash" 
					  width="400" height="320" 
					  data="plugins/flvplayer/flvplayer.swf">
				<param name="movie" value="flvplayer.swf" />
				<param name="bgcolor" value="#eeeeee" />
				<param name="wmode" value="transparent" />
				<param name="flashvars" value="file=../../', $mov, '&amp;autostart=true&amp;" />
				<!-- si le client n\'a pas Flash, le texte ci-dessous apparaît -->
				<p>
					Ce lecteur requiert Adobe Flash Player 8 ou supérieur.
				</p>
				<p>

					Vous pouvez télécharger Flash Player 8 pour GNU/Linux, MacOS ou 
					Windows sur 
					<a href="http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash&amp;promoid=BIOW">
						le site d\'Adobe.
					</a>
				</p>
				<p>
					Si vous ne souhaitez pas installer Flash Player 8 ou que vous
					ne pouvez le faire, vous pouvez <a href="', $mov, '">
					télécharger la vidéo au format AVI</a>.
				</p>

			</object>
			</div>';
	}
}


function do_download ()
{
   /* dwl a screen */
   if ($_GET['dwl'])
   {
      $file = htmlspecialchars (urldecode ($_GET['dwl']));
      
      // on vérifie que le fichier est un screen
      if (!media_is_in_gallery ($file))
      {
         /* fake d'un affichage d'err 404 */
         Header::h404 (dirname ($_SERVER['PHP_SELF']).'/'.$file);
         exit (1);
      }
      
      $type = file_getext ($file);
      
      if      ($type == 'flv')
         header ('Content-Type: applicatino/x-shockwave-flash');
      else if ($type == 'png')
         header ('Content-Type: image/png');
      else if ($type == 'jpeg' || $type == 'jpg')
         header ('Content-Type: image/jpeg');
      else
         header ('Content-Type: application/octet-stream');
      
      header ('Content-Disposition: attachment; filename="'.basename ($file).'"');
      readfile ($file);
      
      exit ();
   }
}


## end of functions

do_download ();


require_once ('include/top.minc');


?>

   <div id="presentation">
        <h2><?php echo TITLE ?></h2>
        <p>
            Ici sont répertoriés les divers médias du moteur. Voici la liste des catégories disponibles :
        </p>
            <ul>
                <li><a href="<?php echo basename ($_SERVER['PHP_SELF']) ?>#screens">Screenshots</a></li>
                <li><a href="<?php echo basename ($_SERVER['PHP_SELF']) ?>#movies">Vidéos</a></li>
            </ul>
        <p>
            Chaque catégorie classe ses médias en fonction de la version du moteur,
            en allant de la plus récente à la plus ancienne. Bon visionnage :o)
        </p>
    </div>
   
   <div id="content">

<?php
if ($_GET['type'] && $_GET['watch'])
{
   # si c'est une vidéo
   if ($_GET['type'] == 'mov')
   {
      watch_movie ($_GET['watch']);
   }
   # si ce n'est pas une vidéo, on suppose que c'ets une image
   else
   {
      watch_picture ($_GET['watch']);
   }
   
   if ($comment = get_comment (dirname ($_GET['watch']), basename ($_GET['watch'])))
   {
      echo '<p class="comment">', $comment, '</p>';
   }
   
   echo '<p>
            <a href="', basename ($_SERVER['PHP_SELF']), '" onclick="window.history.back(1);return false;">Retour</a>
         </p>';
}
else
{
   echo '<h2 id="screens">Screenshots</h2>';
      print_medias (SCREENS);
   
   echo '<h2 id="movies">Vidéos</h2>';
      print_medias (MOVIES);
}
?>

   </div>

<?php

require_once ('include/bottom.minc');

?>
