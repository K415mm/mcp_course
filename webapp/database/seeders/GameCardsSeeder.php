<?php

namespace Database\Seeders;

use App\Models\GameCard;
use Illuminate\Database\Seeder;

class GameCardsSeeder extends Seeder
{
    public function run(): void
    {
        GameCard::query()->delete();

        // ── Blue Team Cards (12) ────────────────────────────────────
        $blueCards = [
            ['name' => 'Audit de code', 'phase' => 'Reconnaissance', 'description' => "Scanne le dépôt pour détecter credentials exposés, backdoors ou dépendances malveillantes dans l'historique git. Révèle jusqu'à 2 IoC actifs.", 'effect' => "Révèle jusqu'à 2 IoC actifs", 'cost' => 2, 'points' => 10, 'mitre_id' => 'D3-SCA', 'mitre_name' => 'Source Code Analysis', 'mitre_description' => 'Analyze code to detect anomalies or vulnerabilities.'],
            ['name' => 'Rotation des secrets', 'phase' => 'Intrusion', 'description' => "Révoque et renouvelle tous les tokens API, clés SSH, credentials AWS et certificats TLS. Annule 1 carte Red active utilisant des credentials compromis.", 'effect' => 'Annule 1 carte Red active', 'cost' => 2, 'points' => 8, 'mitre_id' => 'D3-CR', 'mitre_name' => 'Credential Revocation', 'mitre_description' => 'Invalidate compromised authentication tokens.'],
            ['name' => 'Alerte SIEM', 'phase' => 'Reconnaissance', 'description' => "Déclenche une règle de corrélation sur les logs. Révèle les tentatives d'accès anormales et les patterns de scan actifs.", 'effect' => 'Révèle 1 cible attaquée', 'cost' => 1, 'points' => 5, 'mitre_id' => 'D3-AL', 'mitre_name' => 'Analyze Log', 'mitre_description' => 'Analyze system logs to identify malicious indicators.'],
            ['name' => 'WAF renforcé', 'phase' => 'Défense', 'description' => "Active des règles WAF strictes sur l'API Gateway. Bloque injections SQL, XSS et scans automatisés.", 'effect' => "Immunise l'API Gateway pendant 2 tours", 'cost' => 2, 'points' => 12, 'duration' => '2 tours', 'mitre_id' => 'D3-WAF', 'mitre_name' => 'Web Application Firewall', 'mitre_description' => 'Filter HTTP traffic to block web attacks.'],
            ['name' => 'Isolation CI/CD', 'phase' => 'Défense', 'description' => "Coupe le pipeline CI/CD du réseau de production. Empêche tout déploiement non validé par double approbation humaine.", 'effect' => 'Bloque le pivot Red', 'cost' => 3, 'points' => 15, 'mitre_id' => 'D3-NS', 'mitre_name' => 'Network Segmentation', 'mitre_description' => 'Isolate critical deployment pipelines.'],
            ['name' => 'Restauration snapshot', 'phase' => 'Remédiation', 'description' => "Restaure la DB production depuis un snapshot S3 chiffré pré-compromission avec validation de checksums.", 'effect' => '+20 pts si DB Prod ciblée', 'cost' => 3, 'points' => 20, 'mitre_id' => 'D3-BR', 'mitre_name' => 'Backup Recovery', 'mitre_description' => 'Restore services from a verified isolated backup.'],
            ['name' => 'Investigation forensique', 'phase' => 'Remédiation', 'description' => "Analyse CloudTrail, git logs et network flows pour reconstituer la kill chain complète. Identifie tous les IoC.", 'effect' => 'Rapport complet +10 pts', 'cost' => 2, 'points' => 10, 'mitre_id' => 'D3-FA', 'mitre_name' => 'Forensic Analysis', 'mitre_description' => 'Perform deep analysis of system artifacts to rebuild attack chain.'],
            ['name' => 'Notification CNIL', 'phase' => 'Post-incident', 'description' => "Notifie ANSSI et CNIL dans les 72h RGPD. Évite les amendes réglementaires. Démontre la maturité incidentielle.", 'effect' => 'Évite pénalité RGPD -15 pts', 'cost' => 1, 'points' => 15, 'mitre_id' => 'D3-IR', 'mitre_name' => 'Incident Reporting', 'mitre_description' => 'Comply with legal breach notification requirements.'],
            ['name' => 'Patch zero-day', 'phase' => 'Défense', 'description' => "Déploie un correctif d'urgence en 30 min via hotfix branch et pipeline accéléré. Ferme la CVE exploitée.", 'effect' => 'Ne peut être bloqué', 'cost' => 4, 'points' => 18, 'mitre_id' => 'D3-PM', 'mitre_name' => 'Patch Management', 'mitre_description' => 'Rapidly deploy software fixes to remediate vulnerabilities.'],
            ['name' => 'Honeypot', 'phase' => 'Reconnaissance', 'description' => "Faux serveur de staging avec credentials piégés. Si Red Team mord: révèle sa prochaine carte d'attaque.", 'effect' => 'Révèle prochaine carte Red', 'cost' => 2, 'points' => 0, 'duration' => '2 tours', 'mitre_id' => 'D3-SD', 'mitre_name' => 'System Decoy', 'mitre_description' => 'Deploy a decoy system to monitor attacker behavior.'],
            ['name' => 'Threat hunting proactif', 'phase' => 'Reconnaissance', 'description' => "Recherche proactive des IoC sur tous les systèmes. Révèle les compromissions cachées.", 'effect' => '+5 pts par système trouvé', 'cost' => 2, 'points' => 10, 'mitre_id' => 'D3-TH', 'mitre_name' => 'Threat Hunting', 'mitre_description' => 'Actively search through networks to detect isolated threats.'],
            ['name' => 'Micro-segmentation', 'phase' => 'Défense', 'description' => "Isole les zones réseau critiques (DevOps/Cloud/Data). Empêche tout mouvement latéral sans authentification forte.", 'effect' => 'Bloque le pivot interne', 'cost' => 3, 'points' => 14, 'mitre_id' => 'D3-MS', 'mitre_name' => 'Micro-segmentation', 'mitre_description' => 'Create secure zones to constrain attacker movement.'],
        ];

        foreach ($blueCards as $card) {
            GameCard::create(array_merge($card, ['type' => 'blue', 'team' => 'blue']));
        }

        // ── Red Team Cards (12) ─────────────────────────────────────
        $redCards = [
            ['name' => 'GitHub secret scan', 'phase' => 'Reconnaissance', 'description' => "Détecte des clés API exposées dans l'historique git public.", 'effect' => 'Accès initial sans bruit', 'cost' => 1, 'points' => 10, 'mitre_id' => 'T1552', 'mitre_name' => 'Unsecured Credentials', 'mitre_description' => 'Search repositories for hardcoded secrets.'],
            ['name' => 'Supply chain attack', 'phase' => 'Intrusion', 'description' => "Empoisonne un package npm utilisé dans le build. Backdoor activé à l'import.", 'effect' => 'Touche les microservices', 'cost' => 4, 'points' => 20, 'mitre_id' => 'T1195.002', 'mitre_name' => 'Compromise Software Dependencies', 'mitre_description' => 'Manipulate dependencies to inject malicious code during build.'],
            ['name' => 'Phishing développeur', 'phase' => 'Intrusion', 'description' => "Email ciblant un dev avec fausse notification GitHub. Vol de credentials.", 'effect' => 'Vol de credentials SSO', 'cost' => 1, 'points' => 10, 'mitre_id' => 'T1566.002', 'mitre_name' => 'Spearphishing Link', 'mitre_description' => 'Send emails containing malicious links to steal credentials.'],
            ['name' => 'Pivot via CI/CD', 'phase' => 'Persistance', 'description' => "Injecte des instructions malveillantes dans un workflow GitHub Actions.", 'effect' => 'Accès prod à chaque push', 'cost' => 2, 'points' => 15, 'mitre_id' => 'T1059', 'mitre_name' => 'Command and Scripting Interpreter', 'mitre_description' => 'Execute malicious scripts inside build pipelines.'],
            ['name' => 'Exfiltration S3', 'phase' => 'Impact', 'description' => "Télécharge les buckets S3 avec les credentials IAM volés. Données exfiltrées.", 'effect' => 'Données exfiltrées', 'cost' => 2, 'points' => 10, 'mitre_id' => 'T1530', 'mitre_name' => 'Data from Cloud Storage', 'mitre_description' => 'Access and exfiltrate data from cloud environments.'],
            ['name' => 'Crypto-miner K8s', 'phase' => 'Persistance', 'description' => "Déploie un miner XMR dans le cluster K8s.", 'effect' => '+3 pts/tour', 'cost' => 2, 'points' => 8, 'duration' => 'Persistent', 'mitre_id' => 'T1496', 'mitre_name' => 'Resource Hijacking', 'mitre_description' => 'Use compromised cloud resources for cryptomining.'],
            ['name' => 'Ransomware partiel', 'phase' => 'Impact', 'description' => "Chiffre la DB dev et repos git. Bloque les déploiements.", 'effect' => 'Bloque déploiements 2 tours', 'cost' => 4, 'points' => 20, 'duration' => '2 tours', 'mitre_id' => 'T1486', 'mitre_name' => 'Data Encrypted for Impact', 'mitre_description' => 'Encrypt data to disrupt operations and extort the victim.'],
            ['name' => 'Defacement', 'phase' => 'Impact', 'description' => "Remplace la homepage par un message de revendication.", 'effect' => 'Blue perd 10 pts réput.', 'cost' => 2, 'points' => 8, 'mitre_id' => 'T1491', 'mitre_name' => 'Defacement', 'mitre_description' => 'Modify visual content of an external-facing application.'],
            ['name' => 'CRON persistance', 'phase' => 'Persistance', 'description' => "Crée des tâches cron cachées sur plusieurs hosts. Maintient l'accès.", 'effect' => 'Accès maintenu 3 tours', 'cost' => 2, 'points' => 12, 'duration' => '3 tours', 'mitre_id' => 'T1053.003', 'mitre_name' => 'Cron', 'mitre_description' => 'Schedule jobs to execute malicious code continuously.'],
            ['name' => 'IAM backdoor', 'phase' => 'Persistance', 'description' => "Crée un rôle IAM AWS administrateur invisible.", 'effect' => 'Accès AWS permanent', 'cost' => 3, 'points' => 15, 'mitre_id' => 'T1098', 'mitre_name' => 'Account Manipulation', 'mitre_description' => 'Maintain access by creating backdoored cloud accounts.'],
            ['name' => 'Credential stuffing', 'phase' => 'Reconnaissance', 'description' => "Teste des identifiants volés. 70% de succès sans rotation.", 'effect' => '70% succès', 'cost' => 1, 'points' => 8, 'mitre_id' => 'T1110.004', 'mitre_name' => 'Credential Stuffing', 'mitre_description' => 'Use leaked credential pairs across multiple services.'],
            ['name' => 'Lateral movement', 'phase' => 'Persistance', 'description' => "Pivote via partages réseau et pass-the-hash.", 'effect' => 'Compromet 1 système', 'cost' => 2, 'points' => 12, 'mitre_id' => 'T1550.002', 'mitre_name' => 'Pass the Hash', 'mitre_description' => 'Use stolen credential hashes to move laterally without passwords.'],
        ];

        foreach ($redCards as $card) {
            GameCard::create(array_merge($card, ['type' => 'red', 'team' => 'red']));
        }

        // ── Resource Cards (12) ─────────────────────────────────────
        $resourceCards = [
            ['name' => 'Personnel IT renforcé', 'description' => "+2 actions supplémentaires à l'équipe DevSecOps ce tour.", 'effect' => '+2 actions ce tour', 'duration' => 'Usage unique', 'team' => 'blue', 'mitre_id' => 'D3-ITR', 'mitre_name' => 'Incident Team Ramp-up', 'mitre_description' => 'Mobilize extra personnel for rapid response.'],
            ['name' => "Budget cybersécurité d'urgence", 'description' => "+3 jetons utilisables immédiatement sur n'importe quelle action Blue.", 'effect' => '+3 jetons', 'duration' => '2 tours', 'team' => 'blue', 'mitre_id' => 'D3-EBA', 'mitre_name' => 'Emergency Budget', 'mitre_description' => 'Unlock financial resources to counter urgent threats.'],
            ['name' => 'Outils SIEM avancés', 'description' => "Réduit le coût de toutes les cartes Audit et Alerte de 1 jeton.", 'effect' => '-1 coût Audit/Alerte', 'duration' => '2 tours', 'team' => 'blue', 'mitre_id' => 'D3-EV', 'mitre_name' => 'Enhanced Visibility', 'mitre_description' => 'Improve logging tool efficiency.'],
            ['name' => 'Expert forensique externe', 'description' => "Permet de copier et rejouer n'importe quelle carte Blue.", 'effect' => 'Rejoue 1 carte Blue', 'duration' => 'Usage unique', 'team' => 'blue', 'mitre_id' => 'D3-EFA', 'mitre_name' => 'External Forensics', 'mitre_description' => 'Bring in third-party experts for deep analysis.'],
            ['name' => 'Threat intelligence feed', 'description' => "Révèle la prochaine carte Red Team avant qu'elle soit jouée.", 'effect' => 'Révèle prochaine carte Red', 'duration' => 'Usage unique', 'team' => 'blue', 'mitre_id' => 'D3-TI', 'mitre_name' => 'Threat Intelligence', 'mitre_description' => 'Use external generic intelligence to predict attacks.'],
            ['name' => 'Backup offline certifié', 'description' => "Immunise 1 système critique contre le ransomware pour 2 rounds.", 'effect' => 'Immunité ransomware 2 tours', 'duration' => '2 rounds', 'team' => 'blue', 'mitre_id' => 'D3-DOB', 'mitre_name' => 'Data Offline Backup', 'mitre_description' => 'Secure data out of reach of network attacks.'],
            ['name' => 'Accès VPN compromis', 'description' => "+2 actions à l'équipe Lateral Movement. Tunnel VPN SSL non révoqué.", 'effect' => '+2 actions latérales', 'duration' => 'Usage unique', 'team' => 'red', 'mitre_id' => 'T1133', 'mitre_name' => 'External Remote Services', 'mitre_description' => 'Exploit an existing VPN connection.'],
            ['name' => 'Botnet loué (DaaS)', 'description' => "+3 jetons utilisables uniquement pour DDoS et brute-force.", 'effect' => '+3 jetons DDoS', 'duration' => 'Usage unique', 'team' => 'red', 'mitre_id' => 'T1583.005', 'mitre_name' => 'Botnet', 'mitre_description' => 'Lease a botnet infrastructure for attacks.'],
            ['name' => 'Cryptowallet anonyme', 'description' => "Permet de jouer Ransomware partiel sans que Blue Team ne voie le montant.", 'effect' => 'Ransomware anonyme', 'duration' => 'Usage unique', 'team' => 'red', 'mitre_id' => 'T1659', 'mitre_name' => 'Cryptocurrency Obfuscation', 'mitre_description' => 'Launder tracking of received extortion funds.'],
            ['name' => 'Insider complice', 'description' => "Révèle 1 carte Blue Team active et annule son effet ce tour.", 'effect' => 'Annule 1 carte Blue', 'duration' => 'Usage unique', 'team' => 'red', 'mitre_id' => 'T1078.004', 'mitre_name' => 'Cloud Accounts Insider', 'mitre_description' => 'Use a rogue employee to bypass defenses.'],
            ['name' => 'Infrastructure C2 redondante', 'description' => "Si le C2 est détecté, il se rétablit automatiquement au tour suivant.", 'effect' => 'C2 auto-rétabli', 'duration' => '1 réactivation', 'team' => 'red', 'mitre_id' => 'T1090.002', 'mitre_name' => 'Hidden C2 Fallback', 'mitre_description' => 'Maintain resilience of command nodes.'],
            ['name' => 'Données OSINT collectées', 'description' => "Réduit le coût du prochain Phishing Développeur à 0 jeton.", 'effect' => 'Phishing gratuit', 'duration' => 'Usage unique', 'team' => 'red', 'mitre_id' => 'T1589', 'mitre_name' => 'Gather Victim Identity Info', 'mitre_description' => 'Use OSINT to prepare social engineering.'],
        ];

        foreach ($resourceCards as $card) {
            GameCard::create(array_merge($card, ['type' => 'resource', 'cost' => 0, 'points' => 0]));
        }

        // ── Event Cards (15) ────────────────────────────────────────
        $eventCards = [
            ['subtype' => 'danger', 'name' => 'Package npm compromis', 'description' => "Un package populaire est signalé malveillant. Contient un stealer.", 'effect' => 'CI/CD bloqué 1 tour'],
            ['subtype' => 'danger', 'name' => 'Fuite sur HackerNews', 'description' => "Un dev a posté des credentials AWS par erreur.", 'effect' => 'Red: +1 accès GitHub gratis'],
            ['subtype' => 'danger', 'name' => 'Clé AWS dans le code', 'description' => "Dependabot détecte une clé AWS hardcodée.", 'effect' => 'Red pioche 1 carte Exfiltration'],
            ['subtype' => 'alerte', 'name' => 'Pull request suspecte', 'description' => "PR d'un compte créé il y a 2 jours.", 'effect' => 'Blue doit jouer Audit ou -5 pts'],
            ['subtype' => 'alerte', 'name' => '52 CVE Dependabot', 'description' => "52 vulns dont 8 critiques. Merges bloqués.", 'effect' => 'Blue: -2 jetons — Red: +5 pts'],
            ['subtype' => 'alerte', 'name' => 'Certificat TLS expiré', 'description' => "Le certificat de l'API Gateway a expiré.", 'effect' => '-8 pts Blue (réputation)'],
            ['subtype' => 'success', 'name' => 'Bug bounty responsable', 'description' => "Un chercheur signale une RCE critique. Process exemplaire.", 'effect' => 'Blue +10 pts — 1 système restauré'],
            ['subtype' => 'success', 'name' => 'Audit SOC2 Type II validé', 'description' => "Zéro finding critique. Certification renouvelée.", 'effect' => 'Blue +15 pts conformité'],
            ['subtype' => 'situation', 'name' => 'Incident production', 'description' => "30% des requêtes retournent 500.", 'effect' => 'Tous: -1 action'],
            ['subtype' => 'situation', 'name' => 'Sprint deadline dans 2h', 'description' => "Release critique. Pression maximale.", 'effect' => 'Red: Phishing à -1 jeton'],
            ['subtype' => 'situation', 'name' => 'Stagiaire admin', 'description' => "Accès admin global pour un stagiaire.", 'effect' => 'Red joue Intrusion gratis'],
            ['subtype' => 'joker', 'name' => 'Joker — Blackout', 'description' => "Panne réseau datacenter 5 minutes.", 'effect' => 'Tour blanc pour tous'],
            ['subtype' => 'joker', 'name' => 'Joker — Taupe interne', 'description' => "Membre Blue suspecté de complicité.", 'effect' => 'Red voit 2 cartes Blue'],
            ['subtype' => 'joker', 'name' => 'Joker — Budget d\'urgence', 'description' => "COMEX accorde un budget sécurité.", 'effect' => 'Blue: +4 jetons + 2 cartes gratis'],
            ['subtype' => 'joker', 'name' => 'Joker — Alerte CERT', 'description' => "Alerte technique publique.", 'effect' => 'Blue révèle les cartes Red'],
        ];

        foreach ($eventCards as $card) {
            GameCard::create(array_merge($card, ['type' => 'event', 'team' => 'all', 'cost' => 0, 'points' => 0]));
        }

        // ── Blue Team Cards v2 — Scenarios 5-8 (12) ────────────────
        $blueCardsV2 = [
            ['name' => 'DMARC/SPF enforcement', 'phase' => 'Défense', 'description' => "Active DMARC en mode reject sur les domaines. Bloque les emails usurpés.", 'effect' => "Bloque emails spoofés", 'cost' => 2, 'points' => 12, 'mitre_id' => 'D3-DAS', 'mitre_name' => 'Domain Authentication', 'mitre_description' => 'Enforce DMARC/SPF to drop forged emails.'],
            ['name' => 'Vérification paiement', 'phase' => 'Défense', 'description' => "Double validation (call-back + manager) pour tout virement > 5000€.", 'effect' => 'Annule 1 fraude BEC', 'cost' => 1, 'points' => 10, 'mitre_id' => 'D3-MFA', 'mitre_name' => 'Multi-Factor Validation', 'mitre_description' => 'Ensure out-of-band validation for sensitive actions.'],
            ['name' => 'Simulation phishing', 'phase' => 'Reconnaissance', 'description' => "Campagne de phishing interne. Forme les collaborateurs.", 'effect' => 'Réduit succès phishing de 50%', 'cost' => 2, 'points' => 8, 'mitre_id' => 'D3-AT', 'mitre_name' => 'Security Awareness Training', 'mitre_description' => 'Train personnel to detect social engineering.'],
            ['name' => 'Backup offline vérifié', 'phase' => 'Remédiation', 'description' => "Vérifie l'intégrité des sauvegardes offline air-gapped.", 'effect' => 'Restaure 1 système chiffré', 'cost' => 3, 'points' => 20, 'mitre_id' => 'D3-BR', 'mitre_name' => 'Offline Backup Recovery', 'mitre_description' => 'Recover from fully isolated backups.'],
            ['name' => 'Confinement réseau', 'phase' => 'Défense', 'description' => "Isole immédiatement le segment réseau infecté.", 'effect' => "Bloque propagation 2 tours", 'cost' => 3, 'points' => 15, 'duration' => '2 tours', 'mitre_id' => 'D3-NI', 'mitre_name' => 'Network Isolation', 'mitre_description' => 'Quarantine infected subnets dynamically.'],
            ['name' => 'Analyse cryptographique', 'phase' => 'Remédiation', 'description' => "Analyse la variante ransomware. 50% chance décryptage.", 'effect' => '50% de décrypter', 'cost' => 2, 'points' => 10, 'mitre_id' => 'D3-MRA', 'mitre_name' => 'Malware Reverse Engineering', 'mitre_description' => 'Analyze binaries to extract encryption keys.'],
            ['name' => 'Forensique mémoire', 'phase' => 'Reconnaissance', 'description' => "Capture et analyse les dumps mémoire (rootkits/implants).", 'effect' => 'Révèle 3 cartes Red', 'cost' => 3, 'points' => 15, 'mitre_id' => 'D3-MD', 'mitre_name' => 'Memory Dump Analysis', 'mitre_description' => 'Hunt for in-memory only artifacts.'],
            ['name' => 'Corrélation threat intel', 'phase' => 'Reconnaissance', 'description' => "Croise les IoC avec MISP/CERT. Révèle le plan du groupe APT.", 'effect' => 'Révèle le plan Red complet', 'cost' => 4, 'points' => 18, 'mitre_id' => 'D3-TIO', 'mitre_name' => 'Threat Intel Operations', 'mitre_description' => 'Correlate global intel against local telemetry.'],
            ['name' => 'Baseline réseau', 'phase' => 'Défense', 'description' => "Modèle comportemental NTA. Détecte les flux C2 HTTPs/DNS.", 'effect' => 'Détecte tout C2 actif', 'cost' => 3, 'points' => 14, 'mitre_id' => 'D3-NTA', 'mitre_name' => 'Network Traffic Analysis', 'mitre_description' => 'Establish baseline and detect anomalies.'],
            ['name' => 'Isolation réseau OT', 'phase' => 'Défense', 'description' => "Coupe la passerelle IT/OT et active le mode dégradé.", 'effect' => 'Immunise la zone OT 2 tours', 'cost' => 3, 'points' => 15, 'duration' => '2 tours', 'mitre_id' => 'D3-ZSN', 'mitre_name' => 'Zone Segregation (OT)', 'mitre_description' => 'Purdue model air-gap enforcement.'],
            ['name' => 'Vérification firmware', 'phase' => 'Remédiation', 'description' => "Compare le firmware des automates avec les hashs de référence.", 'effect' => 'Restaure 1 PLC modifié', 'cost' => 2, 'points' => 12, 'mitre_id' => 'D3-FIV', 'mitre_name' => 'Firmware Integrity Verification', 'mitre_description' => 'Ensure SCADA devices run approved code.'],
            ['name' => 'Test processus physique', 'phase' => 'Remédiation', 'description' => "Test matériel supervisé des capteurs/actionneurs.", 'effect' => '+10 pts si SIS intact', 'cost' => 2, 'points' => 10, 'mitre_id' => 'D3-PT', 'mitre_name' => 'Physical State Testing', 'mitre_description' => 'Validate physical safety limits.'],
        ];

        foreach ($blueCardsV2 as $card) {
            GameCard::create(array_merge($card, ['type' => 'blue', 'team' => 'blue']));
        }

        // ── Red Team Cards v2 — Scenarios 5-8 (12) ─────────────────
        $redCardsV2 = [
            ['name' => 'CEO Impersonation', 'phase' => 'Intrusion', 'description' => "Usurpe le CEO via domaine lookalike. Virement urgent exigé.", 'effect' => 'Vol 15 pts si non bloqué', 'cost' => 2, 'points' => 15, 'mitre_id' => 'T1566.002', 'mitre_name' => 'CEO Fraud / BEC', 'mitre_description' => 'Spearphishing targeting finance department.'],
            ['name' => 'Invoice fraud', 'phase' => 'Impact', 'description' => "Modifie le RIB sur une facture fournisseur.", 'effect' => 'Vol 12 pts', 'cost' => 1, 'points' => 12, 'mitre_id' => 'T1565.002', 'mitre_name' => 'Data Manipulation', 'mitre_description' => 'Transmitted Invoice data modification.'],
            ['name' => 'Account takeover email', 'phase' => 'Intrusion', 'description' => "Contrôle du compte O365 du DAF. Crée des règles de redirection.", 'effect' => 'Accès email 3 tours', 'cost' => 2, 'points' => 12, 'duration' => '3 tours', 'mitre_id' => 'T1098.002', 'mitre_name' => 'Exchange Rules Manipulation', 'mitre_description' => 'Hide incoming communications via inbox rules.'],
            ['name' => 'Déploiement ransomware', 'phase' => 'Impact', 'description' => "Déploie LockBit via GPO. Chiffrement AES-256 général.", 'effect' => 'Chiffre 2 systèmes', 'cost' => 4, 'points' => 25, 'mitre_id' => 'T1486', 'mitre_name' => 'Data Encrypted for Impact', 'mitre_description' => 'Mass encryption via Active Directory.'],
            ['name' => 'Double extorsion', 'phase' => 'Impact', 'description' => "Exfiltre 200 Go vers site .onion et menace de publication.", 'effect' => 'Blue perd 15 pts réputation', 'cost' => 3, 'points' => 15, 'mitre_id' => 'T1530', 'mitre_name' => 'Data Staged and Exfiltrated', 'mitre_description' => 'Extortion tactic applying brand damage pressure.'],
            ['name' => 'Destruction backups', 'phase' => 'Persistance', 'description' => "Supprime snapshots Veeam et Shadow Copies.", 'effect' => 'Empêche restauration 1 tour', 'cost' => 3, 'points' => 18, 'mitre_id' => 'T1490', 'mitre_name' => 'Inhibit System Recovery', 'mitre_description' => 'Delete volume shadow copies and cloud backups.'],
            ['name' => 'Custom malware (APT)', 'phase' => 'Intrusion', 'description' => "Implant sur mesure (0 détection VT). C2 via DNS-over-HTTPS.", 'effect' => 'Invisible 3 tours', 'cost' => 4, 'points' => 20, 'duration' => '3 tours', 'mitre_id' => 'T1071.004', 'mitre_name' => 'Application Layer Protocol: DNS', 'mitre_description' => 'Hide command communications in DNS queries.'],
            ['name' => 'Living off the land', 'phase' => 'Persistance', 'description' => "Utilise PowerShell, WMI. Aucun exécutable. Passe l'EDR.", 'effect' => 'Contourne détection', 'cost' => 2, 'points' => 14, 'mitre_id' => 'T1059.001', 'mitre_name' => 'PowerShell', 'mitre_description' => 'LOLBins to evade endpoint controls.'],
            ['name' => 'Data staging', 'phase' => 'Impact', 'description' => "Chiffre/compresse les données avant lente exfiltration.", 'effect' => 'Exfiltration invisible +18 pts', 'cost' => 3, 'points' => 18, 'mitre_id' => 'T1074', 'mitre_name' => 'Data Staged', 'mitre_description' => 'Archive collected data prior to exfiltration over time.'],
            ['name' => 'Exploitation SCADA', 'phase' => 'Intrusion', 'description' => "Exploite Modbus/TCP. Accès RW aux registres automates.", 'effect' => "Compromet la zone OT", 'cost' => 3, 'points' => 18, 'mitre_id' => 'T0831', 'mitre_name' => 'Manipulation of Control', 'mitre_description' => 'ICS MITRE ATT&CK: Modbus parameters manipulation.'],
            ['name' => 'Reprogrammation PLC', 'phase' => 'Impact', 'description' => "Altere la logique automate Siemens S7. Dommages d'équipement.", 'effect' => 'Dégâts physiques +20 pts', 'cost' => 4, 'points' => 20, 'mitre_id' => 'T0889', 'mitre_name' => 'Modify Control Logic', 'mitre_description' => 'ICS MITRE: Download malicious logic to RTU/PLC.'],
            ['name' => 'Bypass Safety Systems', 'phase' => 'Impact', 'description' => "Désactive le SIS (Safety Instrumented System, ex: Triton).", 'effect' => 'SIS désactivé = -25 pts', 'cost' => 5, 'points' => 25, 'mitre_id' => 'T0880', 'mitre_name' => 'Loss of Safety', 'mitre_description' => 'ICS MITRE: Reprogram trip limits causing unsafe status.'],
        ];

        foreach ($redCardsV2 as $card) {
            GameCard::create(array_merge($card, ['type' => 'red', 'team' => 'red']));
        }

        // ── Event Cards v2 — Scenarios 5-8 (8) ─────────────────────
        $eventCardsV2 = [
            ['subtype' => 'danger', 'name' => "Faux virement exécuté", 'description' => "47 000€ virés vers compte frauduleux.", 'effect' => 'Blue perd 10 pts'],
            ['subtype' => 'danger', 'name' => "Note de rançon", 'description' => "YOUR FILES ARE ENCRYPTED sur les écrans.", 'effect' => 'Panique générale: -1 action'],
            ['subtype' => 'alerte', 'name' => "Alerte ANSSI APT", 'description' => "Bulletin ANSSI sur les TTP du groupe.", 'effect' => 'Blue révèle 2 cartes Red'],
            ['subtype' => 'danger', 'name' => "Anomalie process OT", 'description' => "Capteurs pression ligne #3 affolés.", 'effect' => 'Blue joue Patch ou -15 pts'],
            ['subtype' => 'situation', 'name' => "Négociation rançon", 'description' => "Demande 2.5 BTC. Deadline 48h.", 'effect' => 'Blue peut payer pour -20 pts'],
            ['subtype' => 'success', 'name' => "NoMoreRansom", 'description' => "Décrypteur gratuit publié.", 'effect' => 'Blue restaure 1 système gratis'],
            ['subtype' => 'situation', 'name' => "Panne automate", 'description' => "Arrêt inexpliqué d'un PLC.", 'effect' => 'Red +5 pts ou Blue -5 pts'],
            ['subtype' => 'joker', 'name' => "Joker — Assurance", 'description' => "L'assurance déploie des experts sous 4h.", 'effect' => 'Blue +5 jetons + 1 Forensique gratis'],
        ];

        foreach ($eventCardsV2 as $card) {
            GameCard::create(array_merge($card, ['type' => 'event', 'team' => 'all', 'cost' => 0, 'points' => 0]));
        }
    }
}
