# 📝 CyberBreach : Guide des Scénarios

CyberBreach dispose de **8 topologies réseau d'entreprises massives** distinctes. Chaque scénario ne charge que les nœuds d'infrastructures pertinents pour la trame narrative, créant un terrain de jeu allant de 15 à plus de 25 cibles (endpoints complets).

---

## 🟢 Scénarios v1 (Base & Cloud)

### Scénario 1 : Start-up FinTech / Supply Chain
**La Menace :** Attaque sur la chaîne d'approvisionnement (NPM, GitLab, Docker).
**Focus du Réseau :** Fortement orienté CI/CD et Cloud public. Vous y trouverez des serveurs Jenkins branchés à un Docker Registry interne et des AWS EC2 provisionnés en temps réel.
**Chemin Critique :** L'API Gateway -> Le Jenkins Master -> Hashicorp Vault -> Production DB. Obtenir le Vault est la clé de voûte de ce scénario.

### Scénario 2 : PME de Services Cloud
**La Menace :** Fuite massive de données Cloud à partir d'un Bucket mal configuré ou via la prise d'un outil de CI (K8S Control Plane).
**Focus du Réseau :** Kubernetes (Workers & Control), Elastic Search, et le dépôt NPM.

### Scénario 3 : Architecture "Zero-Trust" Hybride
**La Menace :** Abus des identités IAM et des accès VPN.
**Focus du Réseau :** Les ressources On-Prem (AD primaire et secondaire) sont scindées des ressources AWS (IAM / EC2 / S3), reliées uniquement via des accès sécurisés. L'attaque passe inéluctablement par les PC des employés (Laptops RH, Laptops Ops) pour pivoter.

### Scénario 4 : Conglomérat Média (CRON Persistence)
**La Menace :** Des nœuds malveillants injectés dans des Cron jobs pour orchestrer des Defacements constants des façades web.
**Focus du Réseau :** Possède une API Gateway Legacy extrêmement vulnérable. PAM (Privileged Access Management) est central pour empêcher l'usurpation.

---

## 🔴 Scénarios v2 (Menaces Avancées & OT)

Les scénarios v2 introduisent les dernières menaces globales et demandent l'utilisation complète du pool des **68 cartes du jeu**.

### Scénario 5 : Business Email Compromise (BEC)
**La Menace :** Harponnage social, usurpation du CEO, et fraudes aux factures par virement de fonds.
**Focus du Réseau :** Ce graphe possède très peu de serveurs mais d'innombrables terminaux humains ! Portails O365, Terminaux des Départements Finance, RH et Direction. Intranet et Portails de Prestataires (Supplier Portal) et Banque (Bank Portal).
**Chemin Critique :** Pivoter des laptops vers les accès O365 puis déclencher la fraude au niveau de l'ERP.

### Scénario 6 : Attaque Ransomware Double Extorsion
**La Menace :** Chiffrement massif suivi de la menace de publication de la propriété intellectuelle volée.
**Focus du Réseau :** Orientation pure vers la donnée. Serveurs de fichiers, NAS, Stockage AWS S3, et le graal absolu : Les clusters de sauvegarde hors-ligne (Veeam et Glacier).
**Secteur Clef :** La survie de la Blue Team dépend de sa gestion des sauvegardes immuables (Veeam / Offline). L'attaque Red Team visera systématiquement à détruire les Backups Veeam avant de crypter brutalement le vCenter.

### Scénario 7 : Advanced Persistent Threat (APT)
**La Menace :** Implants sophistiqués, contournements de mémoire, exfiltrations cachées dans des flux DNS légitimes.
**Focus du Réseau :** Graphe étendu contenant de multiples "Jump Hosts" (RDP et Linux Jump). Présence d'outils massifs de détection (Splunk SIEM, EDR sur tous les hôtes). 
**Chemin Critique :** C2 Malicieux -> Linux Jump Host -> K8S Control Plane.

### Scénario 8 : Attaque Industrielle (OT/ICS)
**La Menace :** Manipulation physique destructive d'infrastructures de fabrication industrielle. Le Cauchemar cyber-physique.
**Focus du Réseau :** Sépare visuellement le réseau d'entreprise (ERP) et l'usine via un `IT/OT Firewall` restrictif. Au-delà du firewall résident les systèmes SCADA, les contrôleurs de process (PLC Assemblage / PLC Refroidissement) et l'ultime rempart physique (le SIS - Safety System).
**Chemin Critique :** Traverser le pare-feu IT/OT, maîtriser le serveur SCADA, reprogrammer les PLC de refroidissement. La Blue Team doit utiliser la carte *Honeynet OT* pour piéger les attaquants avant l'envoi des commandes mortelles.
