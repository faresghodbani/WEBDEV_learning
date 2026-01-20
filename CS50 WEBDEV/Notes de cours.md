Notes de cours 
HTML (structure)
CSS (style)
JavaScript (interactivité)

Internet = réseau mondial interconnectant des ordinateurs.
-Permet l’échange de données entre machines.
ARPANET : Ancêtre d’Internet.Premières connexions entre ordinateurs
-Les routeurs décident par où les données transitent.
-Les données sont découpées en paquets.
-Les paquets peuvent emprunter différents chemins.
-En cas de congestion, un autre chemin est utilisé.
IP (Internet Protocol) : Identifie chaque machine sur Internet.
Format IPv4 : #.#.#.#
-Chaque nombre est entre 0 et 255.
IPv4 = 32 bits (~4 milliards d’adresses).
IPv6 = 128 bits (beaucoup plus d’adresses).
TCP (Transmission Control Protocol) : Assure que les paquets arrivent tous et dans le bon ordre.
-Redemande les paquets perdus.
-Confirme la réception complète des données.
-Utilise des ports :
80 → HTTP
443 → HTTPS
DNS :Domain Name System.
-Associe un nom de domaine à une adresse IP.
Exemple :
harvard.edu → adresse IP correspondante.
-Évite de mémoriser des adresses numériques.
DHCP : Attribue automatiquement :
l’adresse IP
la passerelle par défaut
les serveurs DNS
HTTP : Protocole de communication web.
-Utilise principalement :
GET
POST
HTTPS : Version sécurisée de HTTP.
-Chiffre les données échangées.
URL
Exemple :
https://www.example.com/folder/file.html
https : protocole
.com : domaine de premier niveau
/folder/file.html : chemin
Codes de réponse HTTP
200 : OK
301 : Moved Permanently
302 : Found
304 : Not Modified
401 : Unauthorized
403 : Forbidden
404 : Not Found
418 : I'm a teapot
500 : Internal Server Error
503 : Service Unavailable
Les erreurs 500 sont souvent dues au développeur.

HTML (HyperText Markup Language)
Principe :
Langage de balisage.
Utilise des balises.
Structure la page web.

Structure de base :
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>title</title>
    </head>
    <body>
        content
    </body>
</html>

Hiérarchie (DOM) :
Les balises sont imbriquées.
Relation parent / enfant.
Le navigateur lit de haut en bas.
Paragraphes : Les espaces ne sont pas pris en compte.
Utiliser <p>
Titres
<h1> à <h6>
-Représentent des niveaux de titres.
Liste non ordonnée : <ul>
Liste ordonnée : <ol>
Élément : <li>

Tableaux
<table>, <tr>, <td>, <th>
Organisation en lignes et colonnes.

Images
<img src="image.png" alt="description">

Vidéos
<video controls muted>
    <source src="video.mp4" type="video/mp4">
</video>

Liens
<a href="page.html">Lien</a>

Formulaires
<form>
<input>
<button>

Exemple (Google search) :
<form action="https://www.google.com/search" method="get">
    <input name="q" type="search">
    <button>Search</button>
</form>

Expressions régulières (Regex)
Permettent de valider des formats.
Exemple : email .edu
<input type="email" pattern=".+@.+\.edu">

CSS (Cascading Style Sheets)
Rôle : Gère l’apparence des pages HTML.
Utilise des règles : propriété / valeur.
CSS en ligne
<p style="text-align: center; font-size: large;">
CSS avec classes
.centered {
    text-align: center;
}
CSS externe
Fichier .css
Lié avec :
<link href="style.css" rel="stylesheet">
Frameworks 
Bootstrap
Bibliothèque CSS/JS.
Facilite la mise en forme.
Utilisation via CDN.
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
JavaScript : Ajoute de l’interactivité. Modifie le DOM dynamiquement.

Événements :
click
submit
keyup

DOMContentLoaded : Attend que la page soit chargée avant d’exécuter le script.

Manipulation du DOM
-document.querySelector
-innerHTML
-style

Exemple :
document.querySelector('button').addEventListener('click', function() {
    document.body.style.backgroundColor = 'red';
});

setInterval : Exécute une fonction à intervalle régulier.

Résumé final :

Dans cette leçon, nous avons vu :
Internet
Routeurs
DNS
DHCP
HTTPS
HTML
Regex
CSS
Frameworks 
CONCLUSION : Rôle de chaque langage (à mémoriser)
HTML → Structure / affichage
Contenu de la page
Titres, paragraphes, images, boutons, formulaires
« Ce qui est affiché »
Exemples :
texte
boutons
images
formulaires

CSS → Style / apparence
Couleurs
Tailles
Polices
Positionnement
Mise en page
Exemples :
couleur du texte
arrière-plan
marges
alignement

JavaScript → Interaction / comportement
Réagit aux actions de l’utilisateur
Modifie le contenu sans recharger la page
Logique dynamique
Exemples :
clic sur un bouton
animation
validation de formulaire
changement de texte ou de couleur