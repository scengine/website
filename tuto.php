<?php
define (TITLE, 'Tutoriel');
define (NEWS_OFFSET, 8);

require_once ('include/top.minc');
?>
	<div id="presentation">
		<h2>Tutoriel officiel du SCEngine </h2><p>Bienvenue dans ce tutoriel d’initiation au moteur 3D SCEngine. </p><p>Ce tutoriel a pour but de vous présenter brièvement le SCEngine et ses concepts d’utilisation. La version présentée du moteur est la 0.0.7 Alpha mais il est très probable que je mette à jour l’article afin de toujours vous proposer une version assez récente. Toutefois je vous déconseille fortement d’utiliser la version Git du moteur avec ce tutoriel, des incompatibilités peuvent surgir. </p> 
	</div>

	<div id="content">

<h3>Introduction et application de test
</h3>

<h4>Préambule ; dépendances requises et installation
</h4>Le moteur dépend des bibliothèques suivantes :
<ul><li>OpenGL ;
</li><li>GLU ;
</li><li>GLEW (<a class="extern" href="http://glew.sourceforge.net/">http://glew.sourceforge.net/</a>) ;
</li><li>libwar
(<a class="extern" href="http://yno.goldzeneweb.info/libwar/">http://yno.goldzoneweb.info/libwar/</a>) ;
</li><li>DevIL (<a class="extern" href="http://openil.sourceforce.net/">http://openil.sourceforce.net/</a>) ;
</li></ul><p>Par défaut les sources du moteur ne sont fournies qu’avec un Makefile Linux,
et aucune version binaire du moteur n’est actuellement proposée, il va donc
vous falloir le compiler. Sous Linux c’est extrêmement simple, lancez simplement
la compilation en tapant « make », puis procédez à l’installation en tapant « $
make install » (nécessite les droits du super-utilisateur). Si vous possédez
Doxygen vous pouvez générer la documentation via « $ make doc », celle-ci est
ensuite disponible dans le répertoire doc. Pour la doc en HTML, voyez
doc/html/index.html.
</p>

<h4>Présentation de la structure du moteur
</h4><p>Le SCEngine est un moteur 3D en C qui offre une sur-couche haut niveau à des
bibliothèques de bas niveau telles que OpenGL ou DevIL, il n’offre aucun
contexte OpenGL ni aucune gestion du fenêtrage ou des événements, ce loisir est
laissé à l’utilisateur. Ce choix offre une plus grande diversité d’utilisation
du moteur, il est ainsi très aisé d’avoir un rendu avec le SCEngine dans une
fenêtre Qt ou GTK+, ou autre. Mes exemples utiliserons la bibliothèque SDL pour
le fenêtrage.
</p><p>Le moteur est divisé en trois groupes principaux :
</p><dl><dt>utils</dt><dd>contient des modules génériques pour la gestion des erreurs, de la
mémoire, manipulations mathématiques, etc.
</dd><dt>core</dt><dd>intéragit avec toutes les bibliothèques bas niveau utilisées dans le
moteur et offre une interface simplifiée.
</dd><dt>interface</dt><dd>constitue l’interface utilisateur principale, contient toutes les
fonctions que l’utilisateur sera amené à utiliser le plus fréquemment.
</dd></dl><p>core dépend de utils et interface dépend de core et (outre le fait que core
dépende lui-même de utils) de utils. Comme vous l’aurez compris vous utiliserez
la plupart du temps l’interface, toutefois certaines fonctions de utils pourront
vous être utiles.
</p>

<h4>Syntaxe
</h4><p>Toutes les fonctions et tous les types du moteur sont préfixés par SCE. S’en
suit généralement un underscore. Dans le cas des fonctions vient ensuite le nom
du module auquel appartient la fonction, suivi d’un nouvel underscore puis enfin
du nom de la fonction. Voici par exemple une fonction provenant du module
&quot;Texture&quot; :
</p><pre><code>SCE_Texture_Create ()
</code></pre><p>Pour les types, après SCE_ vient une lettre, S ou T, désignant respectivement
une structure ou un typedef, puis directement après, le nom du type :
</p><pre><code>SCE_STexture /* voici la structure de type &quot;Texture&quot; */
SCE_TMatrix4 /* et voici le typedef d'une matrice 4×4 */
</code></pre><p>Le cœur défini une autre syntaxe, tous les noms commencent par SCE_C et sont
directement suivi du nom du type ou de la fonction :
</p><pre><code>SCE_CTexture /* et voici une texture côté cœur */
SCE_CCreateTexture () /* et une fonction du module &quot;CTexture&quot; */
</code></pre><p>Les noms des modules du cœur sont en effet tous préfixés par &quot;C&quot;, pour « Core ».
Si un nom est composé de plusieurs mots (ou pas), chaque mot possède sa première
lettre en majuscule :
</p><pre><code>SCE_Foo_BarFooBar ()
</code></pre>

<h4>Première application de test
</h4><p>Nous allons coder une toute petite application qui se chargera d’initialiser le
moteur, cet exemple me permettra de vous présenter la syntaxe des fonctions et
vous permettra de savoir si vous avez bien installé le moteur.
</p><p>Commencez par inclure les en-têtes :
</p><pre><code>#include &lt;SCE/SCEngine.h&gt;
#include &lt;SDL.h&gt;
</code></pre><p>Et créez une application SDL tout ce qu’il y a de plus normal :
</p><pre><code>#define W 800
#define H 600

int main (void)
{
    SDL_Event ev;
    int loop = 1;

    SDL_Init (SDL_INIT_VIDEO);
    SDL_SetVideoMode (W, H, 32, SDL_OPENGL);

    while (loop) {
        while (SDL_PollEvent (&amp;ev)) {
            switch (ev.type) {
            case SDL_QUIT: loop = 0; break;
            default:;
            }
        }
        /* foo () */
        SDL_Delay (50);
    }

    SDL_Quit ();
    return 0;
}
</code></pre><p>SDL_SetVideoMode() avec le flag SDL_OPENGL crée un contexte OpenGL, toute
fonction OpenGL ne doit être appelée qu’après la création du contexte, autrement
dit nous allons initialiser le SCEngine après l’appel à SDL_SetVideoMode() avec
l’appel à SCE_Init() :
</p><pre><code>SCE_Init (stderr, 0);
</code></pre><p>Le premier argument est le flux sur lequel le moteur écrira les erreurs et les
divers autres messages. Le second argument est utilisé pour y placer certains
flags mais actuellement le seul flag existant représente une fonctionnalité
quelque peu &quot;suspendue&quot;. Il est possible de tester le retour de SCE_Init() afin
de savoir si l’initialisation du moteur a réussie. Par convention, les fonctions
susceptibles d’échouer renvoient un int et celui-ci est inférieur à zéro si la
fonction a échoué, 0 sinon. Des constantes symboliques existent pour ces valeurs
respectivement SCE_ERROR et SCE_OK. Une fois le retour d’une fonction testé il
est nécessaire de traiter l’erreur si erreur il y a, nous allons pour cela faire
appel au gestionnaire d’erreurs du moteur. Pour le moment celui-ci dispose de
fonctions qui ne sont pas préfixées par SCE, je pense que je devrais changer ça.
Afin d’afficher l’erreur générée par le moteur, vous devez appeler la fonction
Logger_Out(), celle-ci affichera l’erreur sur le flux spécifié à SCE_Init().
Un système de backtrace pour les erreurs a été mis en œuvre, ainsi au lieu
d’appeler directement Logger_Out() si vous êtes dans l’une de vos fonctions par
exemple, vous pouvez spécifier la source de l’erreur et retourner un code
d’erreur, ainsi la fonction appellante pourraît en faire de même et ainsi de
suite jusqu’à votre fonction main(). Pour déterminer une nouvelle source pour le
backtracer, appelez Logger_LogSrc(). L’appel à SCE_Init() pourraît ainsi
devenir :
</p><pre><code>if (SCE_Init (stderr, 0) &lt; 0) {
    Logger_LogSrc ();
/* pour l'exemple */
#if INFUNCTIONMAIN
    Logger_Out ();
    return EXIT_FAILURE;
#else
    return SCE_ERROR;
#endif
}
</code></pre><p>Il est peu probable que vous compreniez tous les messages d’erreurs existants,
dans certains cas il n’y a même pas de message. Vous pouvez si vous le souhaitez
me retourner vos messages d’erreurs, compréhensibles ou non, avec votre code
source et votre configuration matérielle sur le forum afin de faire progresser
l’avancement du moteur et/ou votre aptitude à l’utiliser.
</p><p>Au dessus de l’appel à SDL_Quit() nous pouvons faire un appel similaire pour le
moteur en appellant SCE_Quit(). Cette fonction ne prend aucun paramètre. En
bonus vous pouvez appeler SCE_Mem_List() après SCE_Quit(), cette fonction
liste toutes les allocations faites par le moteur qui n’ont pas été libérées,
cette fonction m’est surtout utile pour déboguer mais ne présente pas vraiment
d’autres intérêts.
</p><p>Bien, notre première application est créée, et avec un peu de chance est même
opérationnelle. Ce premier article touche désormais à sa fin ; les concepts
fondamentaux du moteur y ont été introduits vous permettant dorénavant de mieux
assimiler les articles suivants qui parleront quant à eux de l’utilisation des
modules du moteur afin de créer des scènes 3D.
</p>

<h3>Gestion de la scène et modèles 3D
</h3><p>Il est temps à présent d’afficher quelque chose dans notre fenêtre de rendu.
Nous allons dans cette partie nous intéresser au chargement d’un modèle 3D à
partir d’un fichier et au rendu de celui-ci via le gestionnaire de scènes.
</p>

<h4>Architecture du gestionnaire de scènes
</h4><p>Le gestionnaire de scènes est un ensemble de fonctions manipulant une structure
de type SCE_SScene laquelle contient tous les éléments de la scène 3D. Elle
contient notamment les objets, les lumières, les skyboxes, etc. À l’heure
actuelle peu de fonctionnalités sont implémentées, toutefois l’architecture
permet (tout du moins je l’espère) d’étendre les possibilités du gestionnaire de
scènes par la suite.
</p><p>Le rendu d’une scène se base sur un principe très simple : une scène destinée à
être rendue doit préalablement être mise à jour, et ceci à chaque frame. Il est
possible de spécifier une configuration différente de la scène pour la mise à
jour et pour le rendu. Pour mettre à jour une scène appelez
SCE_Scene_Update() :
</p><pre><code>SCE_Scene_Update (scene, camera, rendertarget, cubeface);
</code></pre><p>Nous allons pour le moment nous intéresser qu’aux deux premiers paramètres, les
autres peuvent être placés à des valeurs nulles pour le moment. Le premier
paramètre est la scène à mettre à jour et le second est la caméra avec laquelle
la scène sera mise à jour, toutefois ce paramètre peut également être placé à
NULL et c’est d’ailleurs ce que nous ferons dans un premier temps. Le rendu
de la scène se fait via une fonction aux paramètres identiques, cependant si
cette fois ci ils sont positionnés à NULL ce sont les valeurs passées à
SCE_Scene_Update() qui seront utilisés (excepté pour le premier paramètre qui
doit impérativement être spécifié) :
</p><pre><code>SCE_Scene_Render (scene, camera, rendertarget, cubeface);
</code></pre><p>Cette option va dessiner la scène à l’écran, toutefois n’oubliez pas que dans le
cadre de l’utilisation d’un rendu sur double-tampon il est nécessaire d’échanger
les tampons (via SDL_GL_SwapBuffers() pour la SDL).
</p><p>Un des &quot;atouts&quot; du gestionnaire de scènes est la possibilité d’effectuer des
rendus imbriqués, c’est-à-dire de pouvoir rendre la scène pendant son rendu
et/ou sa mise à jour. Supposez que vous ayez défini des fonctions callbacks que
SCE_Scene_Update() se chargera d’appeler pour vous, et supposez que l’un de
ces callbacks effectue le rendu de la scène, vous vous retrouverez à rendre une
scène en cours de mise à jour ! En pratique cela est relativement impossible,
toutefois j’ai implémenté un système de rendu imbriqué qui rend la chose
possible. Lors de sa mise à jour, la scène stocke des informations dans des
structures internes, lues plus tard par la fonction de rendu. Or ces structures
internes existe sous forme de pile, ainsi il vous est possible d’en spécifier
une autre et tout repart de 0 ; vous pouvez effectuer une nouvelle mise à jour
et un nouveau rendu sans perturber la mise à jour en cours. Pour vous déplacer
dans cette pile, utilisez SCE_Scene_PushUpdate() et SCE_Scene_PopUpdate() :
</p><pre><code>SCE_Scene_Update (scene, ...); /* 1ere mise a jour */
    SCE_Scene_PushUpdate (scene);
    SCE_Scene_Update (scene, ...); /* 2eme mise a jour */
    SCE_Scene_Render (scene, ...); /* utilise la 2eme mise a jour */
    SCE_Scene_PopUpdate (scene);
SCE_Scene_Render (scene, ...); /* utilise la 1ere mise a jour */
</code></pre><p>La fonction SCE_Scene_PushUpdate() peut être appelée n’importe quand, dès lors
les fonctions de manipulation de la scène agiront sur une autre structure et
prépareront un nouveau rendu entièrement indépendant des précédents. 8
structures de rendu sont pour le moment disponibles, ainsi il vous est possible
d’appeler SCE_Scene_PushUpdate() sept fois simultanément. Cette limite peut
être augmentée par la simple modification d’une constante interne au moteur,
accessible uniquement via le code source, ainsi si cette limite vous est
insuffisante vous pouvez me contacter pour une augmentation ;)
</p><p>Enfin nous ne pouvions pas terminer cette partie sans parler de la création de
la scène, ne nous éternisons pas :
</p><pre><code>SCE_SScene *scene = SCE_Scene_Create ();
...
SCE_Scene_Delete (scene);
</code></pre><p>La syntaxe pour les fonctions de création et de suppression d’un objet est la
même pour tous les modules, les fonctions de création renvoient systématiquement
un pointeur, égal à NULL en cas d’échec.
</p>

<h4>Chargement d’un modèle 3D
</h4><p>Un modèle est une structure de type SCE_SModel, celle-ci contient les données
des meshs constituant le modèle ainsi que son matériau (nous traiterons les
matériaux plus tard). En gros ce module permet la gestion d’un modèle 3D. Nous
allons commencer par charger un mesh à partir d’un fichier, après quoi nous
ajouterons le modèle à notre scène pour qu’il soit pris en charge par le
gestionnaire de scènes. Créons et chargeons tout d’abord notre modèle :
</p><pre><code>SCE_SModel *model = SCE_Model_Create ();
SCE_Model_LoadMesh (model, &quot;mesh.obj&quot;);
</code></pre><p>Pour les petits étourdis, sachez que le second paramètre de la fonction
SCE_Model_LoadMesh() est le nom du fichier à charger.
</p><pre><code>SCE_Scene_AddModel (scene, model);
</code></pre><p>Et voilà que notre modèle est ajouté à notre scène ! (pour les grands étourdis)
</p><p>Avant de clore cette courte partie, je voudrais juste dire un mot à propos des
formats de fichier supportés par le moteur. À l’heure actuelle seuls deux
formats sont pris en compte, le format <abbr>obj et un perso dont tout le monde se
fout royalement</abbr> Toutefois il est possible d’ajouter dynamiquement des loaders
pour n’importe quel format, ils vous suffit pour cela de programmer vous-même
cette fonction et d’indiquer au gestionnaire de médias qu’un nouveau type de
fichier est supporté, et de lui donner votre fonction bien entendu. Nous verrons
plus tard comment utiliser le gestionnaire de médias pour cela.
</p>

<h4>Nœuds et mouvements
</h4><p>Afin de déterminer la position de notre modèle dans la scène, nous allons pour
cela déplacer son nœud, ou plus précisément appliquer des transformations sur la
matrice du nœud. Nous allons donc récupérer le nœud de notre modèle, récupérer
la matrice du nœud et enfin appliquer les transformations de notre choix sur
cette matrice via le gestionnaire de matrices dans SCEMatrix utils :
</p><pre><code>SCE_SNode *node; /* noeud */
float *m;        /* pointeur sur la matrice de 'node' */

node = SCE_Model_GetNode (model);
m = SCE_Node_GetMatrix (node);

SCE_Matrix4_Scale (m, 1.0/2.0, 1.0/2.0, 1.0); /* hop, une transfo */
</code></pre><p>Le gestionnaire de nœuds permet de construire un arbre et d’assigner à chaque
nœud de cet arbre une matrice, une matrice finale est calculée en multipliant la
matrice du nœud avec les matrices de tous les nœuds parents. Supposons a et b
les parents de c, et am, bm et cm leur matrices respectives, alors la matrice
finale de c est égale à :
</p><pre><code>cf = am * bm * cm
</code></pre><p>On décalera simplement notre modèle vers &quot;l’arrière&quot; comme dans le code montré
quelques lignes plus haut, je vous présenterai les nœuds plus en détail une
autre fois.
</p>

<h4>Ajout d’une source lumineuse
</h4><p>Dernière étape avant de pouvoir admirer notre modèle à l’écran ; ajouter une
source lumineuse à notre scène, en d’autres termes rajouter une lumière dans la
scène. Là encore je serai bref, la lumière n’est utile dans notre que pour…
éclairer. On pourrait certes désactiver l’éclairage, mais dans ce cas vous ne
verriez que la silhouette de votre modèle, et elle serait entièrement blanche.
</p><p>Nous allons donc rajouter cette portion de code :
</p><pre><code>SCE_SLight *light = SCE_Light_Create ();

SCE_Light_SetColor (light, 0.9, 0.6, 0.4); /* on va definir une couleur */
SCE_Scene_AddLight (scene, light);
</code></pre><p>Tout comme pour les modèles, les lumières ont un nœud qui leur est assigné par
défaut. Nous allons déplacer un petit peu notre lumière :
</p><pre><code>float *m = SCE_Node_GetMatrix (SCE_Light_GetNode (light));
SCE_Matrix4_Translate (m, 0.0, 4.0, 4.0);
</code></pre><p>Rien de plus pour le moment.
</p><p>Comme vous pouvez le constater (ou pas), votre modèle est à présent visible
au centre de l’écran. Il n’y a pas de perspective, c’est normal ; nous ne l’avons
pas demandé, cela explique également le problème lié à la profondeur des faces du
modèle. Cet exemple avait pour but et vous présenter brièvement le
gestionnaire de scènes afin que son utilisation ne vous pose plus problème par
la suite, et qu’ainsi nous puissions tranquillement découvrir de nouvelles
fonctionnalités. Vous pourrez trouver le code source complet de cet exemple dans
le dossier &quot;samples&quot; livré avec les versions releases du moteur.
</p>

	</div><!-- content -->

<?php

require_once ('include/bottom.minc');
