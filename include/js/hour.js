function hour () {
   // récupération des heures, minutes et secondes
   var now = new Date ();
   var h = now.getHours ();
   var m = now.getMinutes();
   var s = now.getSeconds();
   
   // on rjoute un zero si le nombre a moins de 2 chiffres
   if (h < 10)
      h = '0'+h;
   if (m < 10)
      m  = '0'+m;
   if (s < 10)
      s  = '0'+s;
   
   // et on affiche l'heure
   document.getElementById ("hour").innerHTML = h+':'+m+':'+s;
}

// on rafraichit toutes les 1 secondes
window.setInterval ("hour ()", 1000);
