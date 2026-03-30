# Gestion des Backlinks - Application SEO Agency

Application interne complète pour la gestion des backlinks d'agence SEO spécialisée dans les services de limousine.

## Objectif

Permettre la gestion complète des clients et le suivi des backlinks réalisés, avec détection de doublons, synchronisation automatique des données et génération de rapports PDF/Excel. Application interne uniquement - aucun accès client, pas d'API externe, pas d'automatisation.

## Stack Technique Réelle

- **Backend**: Laravel 12.0 avec Sanctum (authentification)
- **Frontend**: React 19.2.4 avec Material-UI 7.3.8 et Axios 1.13.6
- **Database**: MySQL avec migrations et seeders
- **Librairies**: jsPDF 4.2.0, jsPDF-autotable 5.0.7, XLSX 0.18.5
- **Architecture**: REST API + SPA React avec Context API

### Prérequis
- PHP 8.2+
- Composer
- Node.js 16+
- MySQL

### Backend
```bash
cd back-end
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

### Frontend
```bash
cd front-end
npm install
npm start
```

### Accès par Défaut
- **Admin**: admin@agency.com / admin123
- **Staff**: staff@agency.com / staff123

### Données de Test
La commande `php artisan migrate:fresh --seed` charge automatiquement:
- 6 clients limousine (Luxury Limo, NYC Limo Service, Royal Jet Limo, Empire Chauffeurs, Blacklane VIP, Carey International)
- 6 sites sources avec metrics (quality_score, dr, traffic_estimated, spam_score)
- 6 backlinks exemples avec différents statuts et types
- 5 types de backlinks (Guest post, Directory, Profil, Comment, Other)
- 2 utilisateurs par défaut (admin et staff)

## API Endpoints Principaux

### Authentification
- `POST /api/login` - Connexion
- `POST /api/logout` - Déconnexion
- `GET /api/me` - Profil utilisateur
- `PUT /api/profile` - Mise à jour profil

### Clients (Admin Only)
- `GET /api/clients` - Liste des clients (tous rôles)
- `POST /api/clients` - Ajouter un client (admin only)
- `PUT /api/clients/{id}` - Modifier un client (admin only)
- `DELETE /api/clients/{id}` - Supprimer un client (admin only)

### Sites Sources (Admin Only)
- `GET /api/sources` - Liste des sites sources (tous rôles)
- `POST /api/sources` - Ajouter un site source (admin only)
- `PUT /api/sources/{id}` - Modifier un site source (admin only)
- `DELETE /api/sources/{id}` - Supprimer un site source (admin only)
- `GET /api/grouped-sources` - Sources groupées par domaine avec statistiques

### Backlinks (Staff + Admin)
- `GET /api/backlinks` - Liste des backlinks
- `POST /api/backlinks` - Ajouter un backlink
- `PUT /api/backlinks/{id}` - Modifier un backlink
- `DELETE /api/backlinks/{id}` - Supprimer un backlink
- `POST /api/backlinks/import` - Importer des backlinks (CSV/Excel)

### Source Summaries (Staff + Admin)
- `GET /api/summary-sources` - Liste des résumés sources avec pagination
- `GET /api/all-summary-sources` - Tous les résumés sources (export)
- `PUT /api/summary-sources/{id}` - Mettre à jour un résumé source
- `DELETE /api/summary-sources/{id}` - Supprimer un résumé source
- `POST /api/summary/import` - Importer des résumés sources (CSV/Excel)

### Backlink Types (Admin Only)
- `GET /api/backlink-types` - Liste des types de backlinks
- `POST /api/backlink-types` - Ajouter un type de backlink
- `DELETE /api/backlink-types/{id}` - Supprimer un type de backlink

### Rapports (Admin Only)
- `POST /api/reports/pdf/{clientId?}` - Générer rapport PDF client (optionnel)
- `POST /api/reports/excel/{clientId?}` - Générer rapport Excel client (optionnel)

### Utilitaires
- `POST /api/sources/import` - Importer des sites sources (CSV/Excel)
- `GET /api/dashboard-stats` - Statistiques du dashboard

## Sécurité et Permissions

### Rôles et Restrictions
- **Admin**: Accès complet à toutes les fonctionnalités
- **Staff**: Accès limité aux backlinks + lecture clients/sources pour dashboard
- **Middleware**: `AdminMiddleware` et `StaffMiddleware` activés
- **Authentification**: Sanctum avec tokens JWT

### Protection des Routes
- Routes admin protégées par middleware `admin`
- Routes staff protégées par middleware `staff` 
- Toutes les routes API protégées par `auth:sanctum`

## Fonctionnalités Implémentées

✅ **Gestion des Utilisateurs**
- Authentification avec rôles Admin/Staff
- Middleware de protection des routes
- Dashboard différencié par rôle
- Gestion du profil utilisateur
- Reset de mot de passe

✅ **Gestion des Clients**
- CRUD complet (admin only)
- Lecture seule pour dashboard staff
- Détection automatique des doublons par email
- Synchronisation automatique des summaries

✅ **Gestion des Sites Sources**
- CRUD complet avec métriques SEO (quality_score, DR, traffic, spam_score)
- Groupement par domaine avec statistiques agrégées
- Import/export massif (CSV/Excel)
- Synchronisation automatique avec les summaries

✅ **Gestion des Backlinks**
- CRUD complet avec détection de doublons
- Types de backlinks personnalisables
- Auto-remplissage des métriques depuis la source
- Import/export massif (CSV/Excel)
- Statuts (Pending, Live, Lost)
- Tracking des coûts et performances

✅ **Source Summaries**
- Synchronisation automatique avec les backlinks
- Calculs agrégés par domaine (total_backlinks, live_backlinks, costs)
- Accesseurs dynamiques pour données en temps réel
- Import/export massif
- Interface Material-UI responsive

✅ **Types de Backlinks**
- Gestion dynamique des types
- Protection contre suppression si utilisés
- Types par défaut (Guest Post, Directory, Profil, Comment, Other)

✅ **Rapports PDF/Excel**
- Génération par client et période
- Statistiques détaillées avec graphiques
- Export avec jsPDF et XLSX
- Templates personnalisables

✅ **Imports/Exports**
- Support CSV et Excel
- Mapping flexible des colonnes
- Validation des données
- Gestion des erreurs détaillée
- Templates d'import inclus

✅ **Sécurité**
- Middleware AdminMiddleware et StaffMiddleware
- Validation des rôles côté backend
- Protection contre accès non autorisé
- Tokens Sanctum avec expiration

✅ **Interface Utilisateur**
- Material-UI 7.3.8 moderne et responsive
- Context API pour gestion d'état
- Pages Admin et Staff distinctes
- Dashboard avec statistiques en temps réel
- Tables paginées et filtrables

## Livrables

- ✅ Repository GitHub complet
- ✅ Migrations base de données complètes
- ✅ Compte admin par défaut configuré
- ✅ Templates CSV/Excel d'import inclus
- ✅ Documentation complète et à jour
- ✅ Interface Material-UI responsive
- ✅ Gestion des rôles Admin/Staff
- ✅ Détection automatique des doublons
- ✅ Export PDF/Excel fonctionnel
- ✅ Système de synchronisation automatique
- ✅ Imports/Exports massifs
- ✅ Table source_summaries avec agrégations
- ✅ Commandes Artisan de synchronisation

## Commandes Artisan

- `php artisan app:sync-backlinks-to-summary` - Synchronise tous les backlinks vers source_summaries
- `php artisan app:sync-source-summaries` - Synchronise les summaries avec les sources
- `php artisan app:restore-source-sites` - Restaure les sites sources depuis les backlinks

## Architecture Technique

### Backend (Laravel)
- **Modèles Eloquent**: Backlink, Client, SourceSite, SourceSummary, BacklinkType, User, Staff
- **Contrôleurs**: AuthController, BacklinkController, ClientController, SourceSiteController, ReportController
- **Observers**: BacklinkObserver (synchronisation automatique)
- **Middleware**: AdminMiddleware, StaffMiddleware
- **Commands**: SyncBacklinksToSummary, SyncSourceSummaries, RestoreSourceSites

### Frontend (React)
- **Pages**: Login, Dashboard, Clients, Backlinks, Sources, Reports, Profile
- **Composants**: Tables responsive, formulaires, imports/exports
- **Context**: AuthContext pour gestion d'état
- **Routing**: React Router DOM avec routes protégées
- **UI**: Material-UI avec thème personnalisé

### Base de Données
- **Tables**: users, clients, source_sites, backlinks, source_summaries, backlink_types, reports
- **Relations**: BelongsTo, HasMany, MorphMany
- **Migrations**: Structure complète avec contraintes et index
- **Seeders**: Données de test réalistes

## Hors Périmètre

- ❌ Accès client 
- ❌ API SEO externe 
- ❌ Automatisation externe
- ❌ Notifications email
- ❌ Système de cache avancé
