# 🛡️ Manuel Global de CyberBreach

**CyberBreach** est une simulation de gestion de crise et d'opérations de cybersécurité (Wargame) au format web. Il oppose deux équipes (Red Team et Blue Team) sous la supervision d'un Maître de Jeu (Modérateur) au sein d'infrastructures d'entreprises réalistes (Purdue Model, Cloud, DevOps, OT/ICS).

---

## 🎲 Concept du Wargame

L'objectif principal du jeu est de simuler l'escalade et la réponse à incident dans des environnements d'entreprise complexes. Le jeu se joue au tour par tour (Team-based). 

*   **🟥 Red Team (Attaquants) :** Cherche à compromettre, s'infiltrer, exfiltrer des données ou perturber l'infrastructure en utilisant des TTPs (Tactics, Techniques, and Procedures) basées sur le framework **MITRE ATT&CK**.
*   **🟦 Blue Team (Défenseurs) :** Cherche à sécuriser l'infrastructure, détecter les menaces, analyser les logs et bloquer les attaques via des contre-mesures basées sur le framework **MITRE D3FEND**.
*   **👑 Modérateur (Game Master) :** Supervise la partie, tranche les litiges, déclenche des "Évènements" scénarisés inattendus et aide au débriefing final.

## 🏗️ L'Infrastructure Enterprise (Le Terrain de Jeu)

Chaque partie prend place sur une **Mappe d'Infrastructure**. Le jeu possède un catalogue expansif de plus de **75 nœuds de serveurs réalistes** allant des API Gateways aux contrôleurs SCADA. 

Le terrain est dynamique. Le jeu supporte deux modes de visualisation que tous les joueurs peuvent basculer librement en temps-réel :
1.  **Vue Organique (Force-Directed) :** Les serveurs s'organisent physiquement via un moteur physique 2D, idéal pour voir les dépendances directes et le "poids" des réseaux. (Utilise un algorithme *Up-Down* dense).
2.  **Vue Hiérarchique (Purdue / Flat Model) :** Dénue le graphe de la physique élastique pour classer rigoureusement les composants des couches les plus exposées (Internet) aux couches les plus sécurisées (Safety Instrumented Systems, IAM Vaults).

## 🃏 La Mécanique des Cartes

Le système de jeu repose sur l'utilisation stratégique de cartes :
*   **Cartes d'Action (Ciblées) :** Se jouent exclusivement en les **glissant et déposant (Drag & Drop)** directement sur un nœud d'infrastructure victime (ex: *Glisser "Ransomware" sur "DB Prod"*). L'impact de la carte dépend de la pertinence de la cible selon la Matrice du jeu.
*   **Cartes de Ressource (Globales) :** Cartes octroyant des bonus d'escouades, du budget d'urgence, ou des annulations d'effets. Elles ne nécessitent pas de cible et s'activent d'un simple clic.
*   **Cartes Évènement :** Réservées au Modérateur pour injecter du chaos.

## ⚙️ Les Jetons et le Système de Score (Scoring Engine)

Les deux équipes reçoivent des **Jetons d'Action (Tokens)** au début de chaque tour. Piocher une nouvelle carte coûte 1 Jeton. Jouer une carte puissante (ex: Golden Ticket AD) peut coûter jusqu'à 5 Jetons, nécessitant d'économiser sur plusieurs tours.

**Le Calcul du Score** est réalisé de façon autonome et invisible par le serveur grâce à la `CardEffectivenessMatrix` intégrée :
*   Si l'équipe Red cible l'hôte parfait pour une faille logicielle, la carte octroie **100%** de ses points.
*   Si la cible est partiellement appropriée (ex: Un jump host intermédiaire limité), **80% ou 50%**.
*   Si la carte n'a logiquement aucun sens (ex: Glisser "Reprogrammation PLC" sur une instance "Slack"), le jeu détectera une attaque inefficace (0%), générera les logs SIEM mais **n'octroiera aucun point**.
*   🌟 **Chemins Critiques :** Cibler précisément les composants centraux d'un Scénario (Ex: Scada Master dans l'environnement OT) octroiera un multiplicateur bonus de **+20%**. 
