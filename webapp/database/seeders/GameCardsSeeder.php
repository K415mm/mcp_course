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
    }
}
