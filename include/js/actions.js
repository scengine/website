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

// toggle edition of a news (on news page)
function news_edit (id, el) {
	var f_id = 'f' + id;
	var m_id = 'm' + id;
	var f_e = document.getElementById (f_id);
	var m_e = document.getElementById (m_id);
	var hash = null;
	if (f_e.style.display == "none") {
		f_e.style.display = "block";
		m_e.style.display = "none";
		el.innerHTML = "Cancel";
		hash = f_id;
	} else {
		f_e.style.display = "none";
		m_e.style.display = "block";
		el.innerHTML = "Edit";
		hash = m_id;
	}
	
	if (hash) {
		window.location.hash = '#' + hash;
	}
	// return false to ease use on oncklick links
	return false;
}
// ask for deleting a news
function news_delete (news_id) {
	if (confirm ('Do you really want to delete this news?')) {
		window.location.replace ('post.php?sec=news&id='+news_id+'&act=rm');
	}
	// return false to ease use on oncklick links
	return false;
}

function entry_more (id) {
	document.getElementById(id).rows++;
}
function entry_lesser (id) {
	document.getElementById(id).rows--;
}

function toggle_folding (button_id, element_id) {
	var folded_height = '1.2em';
	var button = document.getElementById (button_id);
	var element = document.getElementById (element_id);
	if (element.style.height == folded_height ||
	    element.style.height == '1,2em' /* hack for WebKit in french */) {
		button.innerHTML = '[-]';
		element.style.height = '';
	} else {
		button.innerHTML = '[+]';
		element.style.height = folded_height;
	}
}

/* toggle visibility of an element
 * This function returns false to ease use of it in onclick links */
function toggle_display (element_id, display_type) {
	var el = document.getElementById (element_id);
	if (el.style.display == 'none') {
		el.style.display = display_type;
	} else {
		el.style.display = 'none';
	}
	
	return false;
}

/* set all checkbox named \p name checked or not according to \p state */
function set_checked_by_name (name, state) {
	var els = document.getElementsByName (name);
	if (els) {
		for (i in els) {
			els[i].checked = state;
		}
	}
}

function textarea_insert (area_id, before_sel, after_sel) {
  var area = document.getElementById (area_id);
  
  if (area.selectionStart >= 0 && area.selectionEnd >= 0) {
    var start = area.value.substring (0, area.selectionStart);
    var sel = area.value.substring (area.selectionStart, area.selectionEnd);
    var end = area.value.substring (area.selectionEnd);
    
    area.value = start + before_sel + sel + after_sel +	end;
    area.setSelectionRange (start.length + before_sel.length,
                            area.value.length - end.length - after_sel.length);
  } else {
    area.value += before_sel + after_sel;
  }
  area.focus ();
  
  // return false to ease use in onclicks
  return false;
}

function select_all (element) {
	if ((element.selectionStart - element.selectionEnd) == 0) {
		element.setSelectionRange (0, element.value.length);
	}
}

function textarea_insert_around (area_id, around_sel) {
  return textarea_insert (area_id, around_sel, around_sel);
}

/* emails to the obfuscated email starting in @root
 * see string.php:obfuscate_email() */
function unobfuscate_email (root) {
	var url = 'mailto:';
	for (i in root.childNodes) {
		var node = root.childNodes[i];
		if (node.nodeName == 'SPAN') {
			if (node.className == 'dot') {
				url += '.';
			} else if (node.className == 'at') {
				url += '@';
			} else {
				url += node.textContent;
			}
		}
	}
	window.location = url;
	return false;
}

/* sets an element's opacity in a portable way */
function set_opacity (element, value) {
	element.style.opacity = value;
	element.style.MozOpacity = value;
	element.style.WebkitOpacity = value;
}

/* changes the uri of an image with a fade */
function image_fade (image, uri, fade_out_ms) {
	var tmp = new Image ();
	tmp.src = uri;
	tmp.onload = function () {
		image.parentNode.style.backgroundImage = 'url('+tmp.src+')';
		image.parentNode.style.backgroundSize = '100%';
		image.parentNode.style.backgroundRepeat = 'no-repeat';
		
		var interval = 50;
		var opacity = 100;
		var step = opacity / (fade_out_ms * 1.0 / interval);
		var fade_id = setInterval (function () {
			opacity -= step;
			if (opacity > 0) {
				set_opacity (image, opacity / 100.0);
			} else {
				image.src = tmp.src;
				set_opacity (image, 1.0);
				clearInterval (fade_id);
			}
		}, interval);
	}
}

/* Starts a slideshow on image @element
 * @element: the image to change
 * @uris: and array of image uris to display
 * @display_ms: display duration for each image
 * @fade_ms: fade duration between two images
 * 
 * Returns: the interval ID of the slideshow, which can be used to stop it
 * with e.g. clearInterval().
 */
function slideshow (element, uris, display_ms, fade_ms) {
	/* don't launch a slideshow if there is only one image */
	if (uris.length < 2) {
		return;
	}
	
	var current = 0;
	return setInterval (function () {
		if (++current >= uris.length) {
			current = 0;
		}
		var uri = uris[current];
		image_fade (element, uri, fade_ms);
	}, display_ms);
}
