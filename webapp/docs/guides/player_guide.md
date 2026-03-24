# 🎮 Guide des Joueurs (Red & Blue Teams)

Bienvenue dans l'interface tactique de **CyberBreach**. Ce manuel explique les mécaniques à la disposition des équipes afin de dominer le Wargame.

---

## 🎴 La Main de Cartes & Les Jetons

Chaque équipe possède sa propre interface en bas de l'écran affichant :
1.  **Vos Jetons Disponibles :** Cette "monnaie", générée passivement à chaque nouveau tour (selon la configuration du Modérateur), vous permet d'agir.
2.  **Le Bouton "Piocher" :** Contre 1 Jeton, vous pouvez tirer une carte aléatoire depuis la base de données.
3.  **Votre Main Actuelle :** Vos cartes d'attaque ou de défense non jouées. Vous devrez coordonner l'utilisation des cartes au micro (Discord, Zoom ou en salle physique) avec votre Capitaine (le joueur qui a cliqué sur *"S'inscrire comme"* en premier).

> 💡 **Conseil pro :** Analysez toujours le coût d'une carte avant de la jouer. Si vous avez peu de jetons, gardez vos cartes "Zéro-Day" coûteuses pour la fin de la partie.

## 🎯 Comment Jouer une Carte d'Action ?

Le jeu bénéficie d'un système intuitif **HTML5 Drag & Drop**.
Pour lancer une attaque ciblée ou appliquer un correctif préventif matériel :

1.  **Saisissez la carte** depuis votre Menu de main.
2.  **Glissez-là au-dessus de la carte d'infrastructures** centrale.
3.  **Relâchez là (Drop)** n'importe où sur l'icône / Boîte du serveur visé (ex: `☁️ AWS EC2`).
   
*Note : Si la cible est invalide ou qu'elle nécessite une évaluation manuelle, la carte indiquera de consulter le Modérateur.*

## 👁️ Le Brouillard de Guerre (Fog of War)

Dans CyberBreach Enterprise, les attaquants n'ont PAS le droit de savoir à l'avance si leur approche fonctionnera.
**Vous ne verrez aucun pourcentage d'efficacité en sélectionnant vos propres cartes.**
C'est à votre expertise technique de jouer. Est-ce pertinent de lancer du ransomware contre un simple API Gateway sans base de données attachées ? Probablement très peu efficace.

*À la seconde où vous droppez la carte, le serveur backend calculera sa validité par rapport à la topologie de l'entreprise visée. Des dommages minimes ? Vous obtiendrez moins de Score.*

## 🔧 Exemples de Métriques MITRE
Au dos de l'immense majorité des cartes (ainsi qu'au clic pour visualiser), vous verrez apparaître des notations expertes du **MITRE Framework**.
*   **Rouge : MITRE ATT&CK** (ex: *T1566.002* - Spearphishing Link).
*   **Bleu : MITRE D3FEND** (ex: *D3-ZTA* - Zero Trust Architecture).

Servez-vous de vos connaissances IRL en cybersécurité pour prédire si votre MITRE Technique a du sens face à la Mappe Topologique qui vous est affichée. Utilisez le bouton "Changer Vue" si vous n'arrivez pas à distinguer quel nœud supervise l'autre !
