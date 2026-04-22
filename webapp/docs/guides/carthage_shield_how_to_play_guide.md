# Guide D'Utilisation De Carthage Shield

## 1) Objectif Du Jeu

Carthage Shield est une simulation de crise cyber en mode tabletop impliquant plusieurs equipes.  
Votre objectif est de prendre, sous pression et dans un temps limite, de meilleures decisions strategiques au niveau national.

La reussite se mesure par :

- Le score obtenu grace a des decisions de qualite et des votes collectifs
- La qualite de la coordination entre les institutions
- La capacite a communiquer clairement sous pression de crise

## 2) Roles

### 2.1 Moderateur (Game Master)

- Cree et controle la session
- Demarre ou met en pause le chronometre et fait avancer les phases
- Envoie les annonces, declenche les injects et les messages de pression fantome
- Ouvre et ferme les votes puis attribue les points
- Attribue des badges pour les performances exceptionnelles

### 2.2 Equipes

Equipes typiques :

- ANCS : commandement national et coordination strategique
- CERT : detection technique, analyse et traitement des incidents
- Finance : continuite bancaire et des systemes de paiement
- Transport : mobilite et continuite des transports critiques
- E-Gov : services publics numeriques destines aux citoyens
- Communication : message media/public et gestion des rumeurs

### 2.3 Joueurs Et Capitaine

- Les joueurs rejoignent une equipe avec un nom d'affichage
- Le premier joueur d'une equipe devient automatiquement capitaine
- L'equipe doit designer qui soumet les decisions finales a chaque phase

## 3) Cycle De Vie D'Une Session

1. Le moderateur cree la session et partage le code de session.
2. Les joueurs rejoignent une equipe.
3. Le moderateur confirme que tout le monde est pret et lance la Phase 0.
4. Les equipes traitent les injects, discutent les options et soumettent leurs decisions.
5. Le moderateur note les decisions, peut lancer un vote, puis passe a la phase suivante.
6. La phase finale comprend la cloture, les badges et le debriefing.

## 4) Actions En Cours De Jeu

## 4.1 Soumettre Une Decision

Les equipes soumettent une ou plusieurs entrees structurees :

- `decision` : action concrete de reponse
- `escalade` : action d'escalade ou de gouvernance
- `communication` : message public ou message a destination des parties prenantes
- `question` : demande de clarification adressee au moderateur

Les bonnes soumissions sont courtes, actionnables et justifiees.

## 4.2 Voter Sur Des Choix Strategiques

- Le moderateur ouvre une question de vote avec plusieurs options (A/B/C...)
- Chaque equipe vote une seule fois
- La fermeture du vote calcule le resultat
- S'il y a un seul gagnant et que des points sont configures, toutes les equipes recoivent ces points (recompense collective)
- En cas d'egalite, aucun point de vote n'est attribue

## 4.3 Recevoir Des Injects Et Des Annonces

- Les injects simulent de nouveaux faits, l'incertitude et l'escalade
- Des injects surprises peuvent apparaitre a tout moment
- Les injects cibles ne sont visibles que par l'equipe concernee
- Les annonces sont des messages officiels du moderateur pour tous les participants

## 5) Chronometre Et Discipline De Phase

- Le chronometre est controle par le moderateur et fait autorite cote serveur
- En pause, le temps restant est conserve
- Le passage a la phase suivante recharge le contexte avec la duree de la nouvelle phase
- La phase finale marque la session comme terminee

Regle operationnelle :

- N'attendez pas d'avoir une information parfaite
- Produisez une decision justifiee avant la fin du temps

## 6) Modele De Score (Ce Qui Donne Des Points)

- Points attribues par le moderateur selon la qualite des decisions
- Points du vote strategique gagnant (collectifs)
- Points bonus des badges (+5 chacun actuellement)
- Ajustement manuel du score si le facilitateur doit corriger

Evitez de perdre des points a cause de :

- Decisions non structurees ou contradictoires
- Absence de chemin d'escalade
- Communication faible sous pression publique
- Ignorance des dependances intersectorielles

## 7) Playbook Par Phase (Recommande)

### Phase 0 - Ouverture

- Confirmer la chaine de commandement et les responsabilites des equipes
- Definir le protocole de communication et le format de reporting

### Phase 1 - Detection

- Construire une premiere vision de l'incident (quoi, ou, impact)
- Decider de la posture de confinement et du seuil d'escalade

### Phase 2 - Amplification

- Prioriser les services essentiels et la continuite d'activite
- Synchroniser les decisions techniques et de gouvernance entre les equipes

### Phase 3 - Crise Mediatique

- Publier une narration officielle unique et coherente
- Designer un porte-parole et definir les declarations autorisees

### Phase 4 - Arbitrage / Choix National

- Voter la posture strategique avec une justification claire
- Documenter les compromis (securite, continuite, diplomatie, confiance du public)

### Phase 5 - Cloture

- Capturer les lecons apprises et les risques non resolus
- Transformer les enseignements en actions concretes

## 8) Procedure D'Alignement Tabletop Et Referentiels

Utilisez cette checklist pendant chaque session.

- Objectif du pre-brief : relier chaque phase a un objectif de capacite (detecter, contenir, coordonner, communiquer, retablir)
- Collecte des preuves : exiger que chaque decision d'equipe mentionne un responsable, une action et une echeance
- Discipline d'escalade : verifier les conditions de declenchement de la coordination nationale
- Qualite de communication : verifier l'exactitude, le timing et la coherence des messages
- Qualite du debriefing : produire une liste post-action avec des mesures correctives

Referentiels de reference pour aligner la facilitation :

- ISO/IEC 27035 pour le cycle de vie de gestion d'incident
- NIST SP 800-61 pour la logique de confinement et de reponse
- ISO 22320 pour la structure de commandement et de coordination
- Procedures nationales tunisiennes de gouvernance des incidents cyber (modele operationnel ANCS/CERT)

## 9) Conseils De Facilitation Pour De Meilleurs Resultats

- Maintenez une pression realiste, pas chaotique
- Evaluez la qualite des comportements, pas le volume de parole
- Recompensez explicitement la collaboration inter-equipes
- Remettez en cause les hypotheses avec des injects cibles
- Terminez avec des actions d'amelioration mesurables

## 10) Demarrage Rapide (10 Minutes)

1. Creez une session et choisissez le scenario.
2. Verifiez que les six equipes sont bien pourvues.
3. Expliquez les types de decisions et le mode de notation.
4. Lancez le chronometre pour la Phase 0.
5. Declenchez le premier inject et demandez la premiere decision structuree.
6. Repetez le cycle : inject -> discussion -> decision -> score -> phase suivante.
7. Lancez le vote final puis faites le debriefing.

Cette sequence suffit pour animer une session tabletop complete et alignee sur les standards dans Carthage Shield.
