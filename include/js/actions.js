/* LICENSE
 * 
 * BanSE - a site base (designed to be the SCEngine website)
 * Copyright (C) 2007 Colomban "Ban" Wendling <ban-ubuntu@club-internet.fr>
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
/* functions for the right menu */

function edit (id, el) {
   if (document.getElementById ("f"+id).style.display == "none")
   {
      document.getElementById ("f"+id).style.display = "block";
      document.getElementById ("m"+id).style.display = "none";
      el.innerHTML = "Annuler";
   }
   else
   {
      document.getElementById ("f"+id).style.display = "none";
      document.getElementById ("m"+id).style.display = "block";
      el.innerHTML = "Éditer";
   }
}

function entry_more (id)
{
   document.getElementById(id).rows++;
}
function entry_lesser (id)
{
   document.getElementById(id).rows--;
}
