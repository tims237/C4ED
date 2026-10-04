# C4ED

**Application web bancaire simulée en PHP et MySQL.**

C4ED permet à des clients de gérer un compte en ligne (dépôts, retraits, virements entre utilisateurs, historique) et à des administrateurs de superviser les comptes et les opérations. Le projet met l'accent sur la sécurité des échanges et la gestion des rôles.

---

## Fonctionnalités

### Espace client

- Inscription et connexion sécurisées
- Tableau de bord avec solde calculé en temps réel à partir des opérations
- Dépôt, retrait et virement vers un autre utilisateur (identifié par son email)
- Contrôle du solde avant tout retrait ou virement
- Historique des opérations
- Modification du profil
- Mot de passe oublié avec lien de réinitialisation à durée limitée

### Espace administrateur

- Connexion dédiée, réservée au rôle admin
- Création, suppression et consultation des utilisateurs
- Définition d'un plafond de dépenses par client
- Crédit d'un compte client
- Validation des transactions dépassant le plafond

## Sécurité

| Risque | Protection mise en place |
|---|---|
| Injection SQL | Requêtes préparées PDO, émulation désactivée |
| Vol de mot de passe en base | Hachage avec `password_hash` / `password_verify` |
| Falsification de requêtes (CSRF) | Jeton par session, vérifié avec `hash_equals` |
| Fixation de session | `session_regenerate_id` à la connexion |
| Injection de script (XSS) | Échappement des sorties avec `htmlspecialchars` |
| Réinitialisation de mot de passe | Jeton aléatoire (`random_bytes`) avec date d'expiration |
| Accès non autorisé | Middleware de contrôle de la connexion et du rôle |

## Base de données

Quatre tables MySQL :

- `users` : comptes clients et administrateurs, rôle, plafond de dépenses
- `transactions` : dépôts, retraits et virements, avec expéditeur, destinataire et statut de validation
- `password_resets` : jetons de réinitialisation et date d'expiration
- `admins_logs` : journal des actions des administrateurs

Le solde n'est pas stocké : il est recalculé à partir de l'ensemble des transactions de l'utilisateur, ce qui évite toute incohérence entre le solde et l'historique.

## Stack technique

- **Back-end** : PHP 8 (PDO)
- **Base de données** : MySQL
- **Front-end** : HTML, CSS
- **Environnement** : WAMP / phpMyAdmin

## Structure du projet

```
c4ed/
├── assets/          Styles et logo
├── c4ed/            Script SQL de création de la base
├── config/          Connexion à la base de données
├── includes/        Authentification, middleware, fonctions utilitaires, navigation
├── public/
│   ├── users/       Pages de l'espace client
│   ├── admin/       Pages de l'espace administrateur
│   └── sup_admin/   Espace super administrateur (en cours)
└── templates/       En-tête, pied de page et mise en page communs
```

## Installation

Prérequis : PHP 8 et MySQL (par exemple avec WAMP, XAMPP ou MAMP).

1. Cloner le dépôt dans le dossier web de votre serveur local (`www` pour WAMP, `htdocs` pour XAMPP) :

   ```bash
   git clone https://github.com/tims237/c4ed.git
   ```

2. Importer le fichier `c4ed/c4ed.sql` dans phpMyAdmin. Il crée la base `c4ed` et toutes les tables.

3. Adapter si besoin les identifiants de connexion dans `config/config.php`.

4. Ouvrir la page de connexion client dans le navigateur :

   ```
   http://localhost/c4ed/public/users/login.php
   ```

## Évolutions prévues

- [ ] Espace super administrateur : gestion des admins, validation des opérations, consultation des journaux
- [ ] Exécution du contrôle de solde et de l'enregistrement de l'opération dans une transaction SQL, pour éviter les découverts en cas de requêtes simultanées
- [ ] Stockage des montants en centimes entiers pour supprimer les erreurs d'arrondi
- [ ] Envoi réel de l'email de réinitialisation du mot de passe
- [ ] Nouvelle version de l'application avec une stack moderne (React, TypeScript, Node.js)

## Auteur

**Elvis Noubissie** — Étudiant Bachelor Informatique Data & IA, ECE Paris
[LinkedIn](https://www.linkedin.com/in/elvisnoubissie) · [GitHub](https://github.com/tims237)
