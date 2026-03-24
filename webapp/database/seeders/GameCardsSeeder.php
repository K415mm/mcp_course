<?php

namespace Database\Seeders;

use App\Models\GameCard;
use Illuminate\Database\Seeder;

class GameCardsSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing cards
        GameCard::query()->delete();

        // ── Blue Team Cards (12) ────────────────────────────────────
        $blueCards = [
            ['name' => 'Audit de code', 'phase' => 'Reconnaissance', 'description' => "Scanne le dépôt pour détecter credentials exposés, backdoors ou dépendances malveillantes dans l'historique git. Révèle jusqu'à 2 IoC actifs.", 'effect' => "Révèle jusqu'à 2 IoC actifs", 'cost' => 2, 'points' => 10],
            ['name' => 'Rotation des secrets', 'phase' => 'Intrusion', 'description' => "Révoque et renouvelle tous les tokens API, clés SSH, credentials AWS et certificats TLS. Annule 1 carte Red active utilisant des credentials compromis.", 'effect' => 'Annule 1 carte Red active', 'cost' => 2, 'points' => 8],
            ['name' => 'Alerte SIEM', 'phase' => 'Reconnaissance', 'description' => "Déclenche une règle de corrélation sur les logs. Révèle les tentatives d'accès anormales et les patterns de scan actifs.", 'effect' => 'Révèle 1 cible attaquée', 'cost' => 1, 'points' => 5],
            ['name' => 'WAF renforcé', 'phase' => 'Défense', 'description' => "Active des règles WAF strictes sur l'API Gateway. Bloque injections SQL, XSS et scans automatisés.", 'effect' => "Immunise l'API Gateway pendant 2 tours", 'cost' => 2, 'points' => 12, 'duration' => '2 tours'],
            ['name' => 'Isolation CI/CD', 'phase' => 'Défense', 'description' => "Coupe le pipeline CI/CD du réseau de production. Empêche tout déploiement non validé par double approbation humaine.", 'effect' => 'Bloque le pivot Red Team', 'cost' => 3, 'points' => 15],
            ['name' => 'Restauration snapshot', 'phase' => 'Remédiation', 'description' => "Restaure la DB production depuis un snapshot S3 chiffré pré-compromission avec validation de checksums et signature GPG.", 'effect' => '+20 pts si DB Prod ciblée', 'cost' => 3, 'points' => 20],
            ['name' => 'Investigation forensique', 'phase' => 'Remédiation', 'description' => "Analyse CloudTrail, git logs et network flows pour reconstituer la kill chain complète. Identifie tous les IoC.", 'effect' => 'Rapport complet +10 pts', 'cost' => 2, 'points' => 10],
            ['name' => 'Notification CNIL', 'phase' => 'Post-incident', 'description' => "Notifie ANSSI et CNIL dans les 72h RGPD. Évite les amendes réglementaires. Démontre la maturité incidentielle.", 'effect' => 'Évite pénalité RGPD -15 pts', 'cost' => 1, 'points' => 15],
            ['name' => 'Patch zero-day', 'phase' => 'Défense', 'description' => "Déploie un correctif d'urgence en 30 min via hotfix branch et pipeline accéléré. Ferme la CVE exploitée.", 'effect' => 'Ne peut être bloqué par Red', 'cost' => 4, 'points' => 18],
            ['name' => 'Honeypot', 'phase' => 'Reconnaissance', 'description' => "Faux serveur de staging avec credentials piégés. Si Red Team mord: révèle sa prochaine carte d'attaque.", 'effect' => 'Révèle prochaine carte Red', 'cost' => 2, 'points' => 0, 'duration' => '2 tours'],
            ['name' => 'Threat hunting proactif', 'phase' => 'Reconnaissance', 'description' => "Recherche proactive des IoC sur tous les systèmes. Révèle les compromissions cachées.", 'effect' => '+5 pts par système trouvé', 'cost' => 2, 'points' => 10],
            ['name' => 'Micro-segmentation', 'phase' => 'Défense', 'description' => "Isole les zones réseau critiques (DevOps/Cloud/Data). Empêche tout mouvement latéral sans authentification forte.", 'effect' => 'Bloque le pivot interne Red', 'cost' => 3, 'points' => 14],
        ];

        foreach ($blueCards as $card) {
            GameCard::create(array_merge($card, ['type' => 'blue', 'team' => 'blue']));
        }

        // ── Red Team Cards (12) ─────────────────────────────────────
        $redCards = [
            ['name' => 'GitHub secret scan', 'phase' => 'Reconnaissance', 'description' => "Détecte des clés API AWS, tokens GitHub ou mots de passe exposés dans l'historique git public.", 'effect' => 'Accès initial sans bruit', 'cost' => 1, 'points' => 10],
            ['name' => 'Supply chain attack', 'phase' => 'Intrusion', 'description' => "Empoisonne un package npm utilisé dans le build. Backdoor activé à l'import lors du prochain déploiement.", 'effect' => 'Touche tous les microservices', 'cost' => 4, 'points' => 20],
            ['name' => 'Phishing développeur', 'phase' => 'Intrusion', 'description' => "Email ciblant un développeur avec fausse notification GitHub ou Jira. Vol de credentials SSO.", 'effect' => 'Vol de credentials SSO', 'cost' => 1, 'points' => 10],
            ['name' => 'Pivot via CI/CD', 'phase' => 'Persistance', 'description' => "Injecte des instructions malveillantes dans un workflow GitHub Actions. Accès production automatisé.", 'effect' => 'Accès prod à chaque push', 'cost' => 2, 'points' => 15],
            ['name' => 'Exfiltration S3', 'phase' => 'Impact', 'description' => "Télécharge tous les buckets S3 accessibles avec les credentials IAM volés. Données clients exfiltrées.", 'effect' => 'Données clients exfiltrées', 'cost' => 2, 'points' => 10],
            ['name' => 'Crypto-miner K8s', 'phase' => 'Persistance', 'description' => "Déploie un miner XMR dans des containers sur le cluster K8s. Génère +3 pts/tour tant qu'il n'est pas détecté.", 'effect' => '+3 pts/tour si non détecté', 'cost' => 2, 'points' => 8, 'duration' => 'Persistent'],
            ['name' => 'Ransomware partiel', 'phase' => 'Impact', 'description' => "Chiffre la DB de développement et les repos git locaux. Message de rançon. Bloque TOUS les déploiements.", 'effect' => 'Bloque déploiements 2 tours', 'cost' => 4, 'points' => 20, 'duration' => '2 tours'],
            ['name' => 'Defacement', 'phase' => 'Impact', 'description' => "Remplace la homepage du produit par un message de revendication. Impact réputation immédiat.", 'effect' => 'Blue perd 10 pts réputation', 'cost' => 2, 'points' => 8],
            ['name' => 'CRON persistance', 'phase' => 'Persistance', 'description' => "Crée des tâches cron cachées sur plusieurs hosts. Maintient l'accès complet même après rotation.", 'effect' => 'Accès maintenu 3 tours', 'cost' => 2, 'points' => 12, 'duration' => '3 tours'],
            ['name' => 'IAM backdoor', 'phase' => 'Persistance', 'description' => "Crée un rôle IAM AWS administrateur avec des tags innocents. Backdoor totalement invisible.", 'effect' => 'Accès AWS permanent', 'cost' => 3, 'points' => 15],
            ['name' => 'Credential stuffing', 'phase' => 'Reconnaissance', 'description' => "Teste des identifiants volés sur tous les services exposés. 70% de succès si rotation non effectuée.", 'effect' => '70% succès sans rotation', 'cost' => 1, 'points' => 8],
            ['name' => 'Lateral movement', 'phase' => 'Persistance', 'description' => "Pivote depuis le poste initial vers d'autres systèmes via partages réseau et pass-the-hash.", 'effect' => 'Compromet 1 système adjacent', 'cost' => 2, 'points' => 12],
        ];

        foreach ($redCards as $card) {
            GameCard::create(array_merge($card, ['type' => 'red', 'team' => 'red']));
        }

        // ── Resource Cards (12) ─────────────────────────────────────
        $resourceCards = [
            ['name' => 'Personnel IT renforcé', 'description' => "+2 actions supplémentaires à l'équipe DevSecOps ce tour.", 'effect' => '+2 actions ce tour', 'duration' => 'Usage unique', 'team' => 'blue'],
            ['name' => "Budget cybersécurité d'urgence", 'description' => "+3 jetons utilisables immédiatement sur n'importe quelle action Blue.", 'effect' => '+3 jetons immédiats', 'duration' => '2 tours', 'team' => 'blue'],
            ['name' => 'Outils SIEM avancés', 'description' => "Réduit le coût de toutes les cartes Audit et Alerte de 1 jeton pendant 2 tours.", 'effect' => '-1 coût Audit/Alerte', 'duration' => '2 tours', 'team' => 'blue'],
            ['name' => 'Expert forensique externe', 'description' => "Permet de copier et rejouer n'importe quelle carte Blue déjà jouée.", 'effect' => 'Rejoue 1 carte Blue', 'duration' => 'Usage unique', 'team' => 'blue'],
            ['name' => 'Threat intelligence feed', 'description' => "Révèle la prochaine carte Red Team avant qu'elle soit jouée.", 'effect' => 'Révèle prochaine carte Red', 'duration' => 'Usage unique', 'team' => 'blue'],
            ['name' => 'Backup offline certifié', 'description' => "Immunise 1 système critique contre le ransomware pour 2 rounds.", 'effect' => 'Immunité ransomware 2 tours', 'duration' => '2 rounds', 'team' => 'blue'],
            ['name' => 'Accès VPN compromis', 'description' => "+2 actions à l'équipe Lateral Movement. Tunnel VPN SSL non révoqué.", 'effect' => '+2 actions latérales', 'duration' => 'Usage unique', 'team' => 'red'],
            ['name' => 'Botnet loué (DaaS)', 'description' => "+3 jetons utilisables uniquement pour DDoS et brute-force.", 'effect' => '+3 jetons DDoS/brute', 'duration' => 'Usage unique', 'team' => 'red'],
            ['name' => 'Cryptowallet anonyme', 'description' => "Permet de jouer Ransomware partiel sans que Blue Team ne voie le montant.", 'effect' => 'Ransomware anonyme', 'duration' => 'Usage unique', 'team' => 'red'],
            ['name' => 'Insider complice', 'description' => "Révèle 1 carte Blue Team active et annule son effet ce tour.", 'effect' => 'Annule 1 carte Blue active', 'duration' => 'Usage unique', 'team' => 'red'],
            ['name' => 'Infrastructure C2 redondante', 'description' => "Si le C2 est détecté et coupé, il se rétablit automatiquement au tour suivant.", 'effect' => 'C2 auto-rétabli', 'duration' => '1 réactivation', 'team' => 'red'],
            ['name' => 'Données OSINT collectées', 'description' => "Réduit le coût du prochain Phishing Développeur à 0 jeton.", 'effect' => 'Phishing gratuit', 'duration' => 'Usage unique', 'team' => 'red'],
        ];

        foreach ($resourceCards as $card) {
            GameCard::create(array_merge($card, ['type' => 'resource', 'cost' => 0, 'points' => 0]));
        }

        // ── Event Cards (15) ────────────────────────────────────────
        $eventCards = [
            ['subtype' => 'danger', 'name' => 'Package npm compromis', 'description' => "Un package populaire est signalé malveillant. Contient un credential stealer.", 'effect' => 'CI/CD bloqué 1 tour — Blue perd 1 action'],
            ['subtype' => 'danger', 'name' => 'Fuite sur HackerNews', 'description' => "Un dev a posté des credentials AWS par erreur. Thread viral.", 'effect' => 'Red obtient +1 accès GitHub gratis'],
            ['subtype' => 'danger', 'name' => 'Clé AWS dans le code', 'description' => "Dependabot détecte une clé AWS hardcodée dans un commit de 3 mois.", 'effect' => 'Red pioche 1 carte Exfiltration gratis'],
            ['subtype' => 'alerte', 'name' => 'Pull request suspecte', 'description' => "PR d'un compte créé il y a 2 jours, avec du code obfusqué.", 'effect' => 'Blue doit jouer Audit de code ou -5 pts'],
            ['subtype' => 'alerte', 'name' => '52 CVE Dependabot', 'description' => "52 vulnérabilités dont 8 critiques CVSS 9+. Merges bloqués.", 'effect' => 'Blue: -2 jetons — Red: +5 pts'],
            ['subtype' => 'alerte', 'name' => 'Certificat TLS expiré', 'description' => "Le certificat wildcard de l'API Gateway a expiré en production.", 'effect' => '-8 pts Blue (pénalité réputation)'],
            ['subtype' => 'success', 'name' => 'Bug bounty responsable', 'description' => "Un chercheur signale une RCE critique via HackerOne. Processus exemplaire.", 'effect' => 'Blue +10 pts — 1 système restauré gratis'],
            ['subtype' => 'success', 'name' => 'Audit SOC2 Type II validé', 'description' => "SOC2 Type II validé. Zéro finding critique. Certification renouvelée.", 'effect' => 'Blue +15 pts conformité'],
            ['subtype' => 'situation', 'name' => 'Incident production', 'description' => "30% des requêtes retournent 500. L'équipe est en war room.", 'effect' => 'Tous: -1 action — Blue peut patcher gratis'],
            ['subtype' => 'situation', 'name' => 'Sprint deadline dans 2h', 'description' => "Release critique client Grand Compte. Pression maximale.", 'effect' => 'Red: Phishing à -1 jeton de coût'],
            ['subtype' => 'situation', 'name' => 'Stagiaire avec droits admin', 'description' => "Stagiaire DevOps avec droits admin par erreur. Accès complet K8s.", 'effect' => 'Red joue 1 carte Intrusion gratis'],
            ['subtype' => 'joker', 'name' => 'Joker — Blackout réseau', 'description' => "Panne réseau datacenter 5 minutes. Aucune action possible.", 'effect' => 'Tour blanc pour les deux équipes'],
            ['subtype' => 'joker', 'name' => 'Joker — Taupe interne', 'description' => "Un membre Blue Team suspecté de complicité.", 'effect' => 'Red Team voit 2 cartes Blue'],
            ['subtype' => 'joker', 'name' => 'Joker — Budget sécu COMEX', 'description' => "Le COMEX accorde un budget d'urgence cybersécurité.", 'effect' => 'Blue: +4 jetons + 2 cartes gratuites'],
            ['subtype' => 'joker', 'name' => 'Joker — Alerte CERT-FR', 'description' => "Le CERT-FR publie une alerte sur la technique Red. IoC publics.", 'effect' => 'Blue révèle toutes les cartes Red jouées'],
        ];

        foreach ($eventCards as $card) {
            GameCard::create(array_merge($card, ['type' => 'event', 'team' => 'all', 'cost' => 0, 'points' => 0]));
        }

        // ── Blue Team Cards v2 — Scenarios 5-8 (12) ────────────────
        $blueCardsV2 = [
            // BEC Defense
            ['name' => 'DMARC/SPF enforcement', 'phase' => 'Défense', 'description' => "Active DMARC en mode reject et vérifie SPF/DKIM sur tous les domaines de l'entreprise. Bloque les emails usurpés ciblant la direction.", 'effect' => "Bloque 100% des emails spoofés", 'cost' => 2, 'points' => 12],
            ['name' => 'Vérification paiement', 'phase' => 'Défense', 'description' => "Impose une procédure de double validation (call-back téléphonique + signature manager) pour tout virement supérieur à 5 000€.", 'effect' => 'Annule 1 fraude BEC active', 'cost' => 1, 'points' => 10],
            ['name' => 'Simulation phishing', 'phase' => 'Reconnaissance', 'description' => "Lance une campagne de phishing interne réaliste. Identifie les collaborateurs vulnérables et déclenche une formation ciblée.", 'effect' => 'Réduit succès phishing de 50%', 'cost' => 2, 'points' => 8],
            // Ransomware Defense
            ['name' => 'Backup offline vérifié', 'phase' => 'Remédiation', 'description' => "Vérifie l'intégrité des sauvegardes offline air-gapped. Teste la restauration complète sur environnement isolé en 4h.", 'effect' => 'Restaure 1 système chiffré', 'cost' => 3, 'points' => 20],
            ['name' => 'Confinement réseau', 'phase' => 'Défense', 'description' => "Isole immédiatement le segment réseau infecté via NAC et firewall. Empêche la propagation latérale du ransomware.", 'effect' => "Bloque propagation 2 tours", 'cost' => 3, 'points' => 15, 'duration' => '2 tours'],
            ['name' => 'Analyse cryptographique', 'phase' => 'Remédiation', 'description' => "Analyse le variant de ransomware et identifie les failles cryptographiques. Si variante connue, fournit un décrypteur en 24h.", 'effect' => 'Chance 50% de décrypter', 'cost' => 2, 'points' => 10],
            // APT Defense
            ['name' => 'Forensique mémoire', 'phase' => 'Reconnaissance', 'description' => "Capture et analyse les dumps mémoire de tous les serveurs suspects. Détecte les implants fileless, les injections DLL et les rootkits.", 'effect' => 'Révèle 3 cartes Red cachées', 'cost' => 3, 'points' => 15],
            ['name' => 'Corrélation threat intel', 'phase' => 'Reconnaissance', 'description' => "Croise les IoC locaux avec les feeds MISP, FS-ISAC et CERT-FR. Identifie le groupe APT et ses TTPs habituelles.", 'effect' => 'Révèle le plan Red complet', 'cost' => 4, 'points' => 18],
            ['name' => 'Baseline réseau', 'phase' => 'Défense', 'description' => "Établit un modèle comportemental du trafic légitime via NTA. Détecte les communications C2 cachées dans le DNS ou HTTPS.", 'effect' => 'Détecte tout C2 actif', 'cost' => 3, 'points' => 14],
            // Industrial Defense
            ['name' => 'Isolation réseau OT', 'phase' => 'Défense', 'description' => "Coupe physiquement la passerelle IT/OT et active les règles de DMZ industrielle. Aucun trafic IT ne passe vers le réseau SCADA.", 'effect' => 'Immunise la zone OT 2 tours', 'cost' => 3, 'points' => 15, 'duration' => '2 tours'],
            ['name' => 'Vérification firmware', 'phase' => 'Remédiation', 'description' => "Compare le firmware des PLC et RTU avec les images de référence signées. Détecte toute altération du code automate.", 'effect' => 'Restaure 1 PLC modifié', 'cost' => 2, 'points' => 12],
            ['name' => 'Test processus physique', 'phase' => 'Remédiation', 'description' => "Lance une séquence de test supervisée sur les processus physiques critiques. Vérifie que les capteurs et actionneurs répondent normalement.", 'effect' => '+10 pts si SIS intact', 'cost' => 2, 'points' => 10],
        ];

        foreach ($blueCardsV2 as $card) {
            GameCard::create(array_merge($card, ['type' => 'blue', 'team' => 'blue']));
        }

        // ── Red Team Cards v2 — Scenarios 5-8 (12) ─────────────────
        $redCardsV2 = [
            // BEC Attack
            ['name' => 'CEO Impersonation', 'phase' => 'Intrusion', 'description' => "Usurpe l'identité du CEO via un domaine lookalike et un email copiant son style. Demande un virement urgent de 180 000€ à la comptabilité.", 'effect' => 'Vol 15 pts si non bloqué', 'cost' => 2, 'points' => 15],
            ['name' => 'Invoice fraud', 'phase' => 'Impact', 'description' => "Intercepte un fil de discussion email entre DevCo et un fournisseur. Modifie le RIB sur une facture de 47 000€ en cours de paiement.", 'effect' => 'Vol 12 pts si non vérifié', 'cost' => 1, 'points' => 12],
            ['name' => 'Account takeover email', 'phase' => 'Intrusion', 'description' => "Prend le contrôle du compte Office 365 du directeur financier via credential stuffing. Crée des règles de redirection Outlook.", 'effect' => 'Accès email 3 tours', 'cost' => 2, 'points' => 12, 'duration' => '3 tours'],
            // Ransomware Attack
            ['name' => 'Déploiement ransomware', 'phase' => 'Impact', 'description' => "Déploie LockBit 3.0 via GPO sur tous les postes Windows du domaine. Chiffrement AES-256 en 8 minutes. Clé exfiltrée.", 'effect' => 'Chiffre 2 systèmes', 'cost' => 4, 'points' => 25],
            ['name' => 'Double extorsion', 'phase' => 'Impact', 'description' => "Avant le chiffrement, exfiltre 200 Go de données vers un site .onion. Menace de publication si la rançon n'est pas payée sous 72h.", 'effect' => 'Blue perd 15 pts réputation', 'cost' => 3, 'points' => 15],
            ['name' => 'Destruction backups', 'phase' => 'Persistance', 'description' => "Recherche et supprime tous les snapshots Veeam, les shadow copies Windows et les backups S3 accessibles avant le chiffrement.", 'effect' => 'Empêche restauration 1 tour', 'cost' => 3, 'points' => 18],
            // APT Attack
            ['name' => 'Custom malware (APT)', 'phase' => 'Intrusion', 'description' => "Déploie un implant sur mesure compilé spécifiquement pour DevCo. 0 détection VirusTotal. Communique via DNS-over-HTTPS.", 'effect' => 'Invisible 3 tours', 'cost' => 4, 'points' => 20, 'duration' => '3 tours'],
            ['name' => 'Living off the land', 'phase' => 'Persistance', 'description' => "Utilise uniquement des outils légitimes (PowerShell, WMI, certutil). Aucun exécutable suspect. Passe sous le radar EDR.", 'effect' => 'Contourne toute détection', 'cost' => 2, 'points' => 14],
            ['name' => 'Data staging', 'phase' => 'Impact', 'description' => "Compresse et chiffre les données stratégiques (brevets, contrats, RH) dans des fichiers .cab cachés avant exfiltration lente (<10 Ko/s).", 'effect' => 'Exfiltration invisible +18 pts', 'cost' => 3, 'points' => 18],
            // Industrial Attack
            ['name' => 'Exploitation SCADA', 'phase' => 'Intrusion', 'description' => "Exploite une vulnérabilité Modbus/TCP sur le serveur SCADA. Accès lecture/écriture aux registres des automates industriels.", 'effect' => "Compromet la zone OT", 'cost' => 3, 'points' => 18],
            ['name' => 'Reprogrammation PLC', 'phase' => 'Impact', 'description' => "Modifie la logique d'un automate Siemens S7 pour altérer les seuils de température et de pression sans déclencher d'alarme.", 'effect' => 'Dégâts physiques +20 pts', 'cost' => 4, 'points' => 20],
            ['name' => 'Bypass Safety Systems', 'phase' => 'Impact', 'description' => "Désactive le système instrumenté de sécurité (SIS) en envoyant des commandes Triton/TRISIS. Le processus tourne sans filet de sécurité.", 'effect' => 'SIS désactivé = -25 pts Blue', 'cost' => 5, 'points' => 25],
        ];

        foreach ($redCardsV2 as $card) {
            GameCard::create(array_merge($card, ['type' => 'red', 'team' => 'red']));
        }

        // ── Event Cards v2 — Scenarios 5-8 (8) ─────────────────────
        $eventCardsV2 = [
            ['subtype' => 'danger', 'name' => "Faux virement exécuté", 'description' => "La comptabilité a exécuté un virement de 47 000€ vers un compte frauduleux il y a 2h. Banque non joignable.", 'effect' => 'Blue perd 10 pts — Red +10 pts si BEC actif'],
            ['subtype' => 'danger', 'name' => "Note de rançon affichée", 'description' => "Un message 'YOUR FILES HAVE BEEN ENCRYPTED' apparaît sur tous les écrans du bureau. Le helpdesk est submergé.", 'effect' => 'Tous: -1 action — Panique générale'],
            ['subtype' => 'alerte', 'name' => "Alerte ANSSI APT", 'description' => "L'ANSSI publie un bulletin d'alerte sur un groupe APT ciblant le secteur technologique français. IoC et TTPs fournis.", 'effect' => 'Blue révèle 2 cartes Red cachées'],
            ['subtype' => 'danger', 'name' => "Anomalie sur le process", 'description' => "Les capteurs de pression affichent des valeurs incohérentes sur la ligne de production #3. Les opérateurs signalent des vibrations.", 'effect' => 'Blue doit jouer Vérification firmware ou -15 pts'],
            ['subtype' => 'situation', 'name' => "Négociation rançon", 'description' => "L'attaquant ouvre un chat Tor pour négocier. Demande 2.5 BTC (150 000€). Deadline: 48h.", 'effect' => 'Blue peut payer (-20 pts) pour récupérer 1 système'],
            ['subtype' => 'success', 'name' => "Décrypteur NoMoreRansom", 'description' => "Le projet NoMoreRansom.org publie un décrypteur gratuit pour la variante détectée. Testé et fonctionnel.", 'effect' => 'Blue restaure 1 système chiffré gratuitement'],
            ['subtype' => 'situation', 'name' => "Panne automate ligne 2", 'description' => "Un automate PLC s'arrête brutalement sur la ligne 2. L'opérateur ne peut pas le redémarrer. Code d'erreur inconnu.", 'effect' => 'Red +5 pts si PLC compromis — Blue -5 pts'],
            ['subtype' => 'joker', 'name' => "Joker — Cyberassurance", 'description' => "La police de cyberassurance couvre les frais de réponse à incident. Expert forensique externe déployé sous 4h.", 'effect' => 'Blue: +5 jetons + joue 1 carte Forensique gratis'],
        ];

        foreach ($eventCardsV2 as $card) {
            GameCard::create(array_merge($card, ['type' => 'event', 'team' => 'all', 'cost' => 0, 'points' => 0]));
        }
    }
}
