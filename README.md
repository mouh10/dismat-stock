## Architecture

### Multi-tenant (multi-boutique SaaS)
Comme l'app d'origine, chaque boutique inscrite est un **tenant** isolé.
Le trait `App\Models\Concerns\BelongsToTenant` (utilisé par la quasi-totalité
des modèles) :
- filtre automatiquement toutes les requêtes par `tenant_id` de l'utilisateur connecté (Global Scope) ;
- assigne automatiquement le `tenant_id` à la création d'un enregistrement.

### Base de données (29 tables)
Migrations dans `database/migrations/`, fidèle au schéma original
(`src/lib/types.ts` de l'app Next.js) : tenants, users, magasins, categories,
produits, clients, fournisseurs, factures + facture_items, creances, achats +
achat_items, dettes_fournisseurs, paiements, stocks, mouvement_stocks,
transferts, inventaires + inventaire_lignes, echanges (troc), trésorerie
(depenses/revenus_divers), notifications, activites, abonnements.

### Logique métier centralisée
`app/Services/StockService.php` centralise tous les mouvements de stock
(entrée, sortie) pour garantir que la table `stocks` (quantité courante) et
`mouvement_stocks` (historique/traçabilité) restent toujours synchronisées.
Utilisé par la Caisse (sorties) et les Achats (entrées).

### Modules livrés (CRUD complet, testés syntaxiquement)

| Module | Route | Description |
|---|---|---|
| Tableau de bord | `/dashboard` | Stats du jour/mois, alertes stock, dernières ventes |
| Catégories | `/categories` | CRUD |
| Produits | `/produits` | CRUD + stock initial à la création |
| Stocks | `/stocks` | Vue par magasin, entrées/sorties manuelles |
| **Caisse (POS)** | `/caisse` | Panier tactile, encaissement, décrément stock auto |
| Ventes (historique) | `/ventes` | Liste des factures/tickets + téléchargement PDF |
| Achats | `/achats` | Bon d'achat multi-lignes, entrée stock auto, dette fournisseur si non soldé |
| Fournisseurs | `/fournisseurs` | CRUD |
| Clients | `/clients` | CRUD |
| Créances | `/creances` | Encaissement de créances clients |
| Dettes | `/dettes` | Règlement de dettes fournisseurs |
| Magasins | `/magasins` | CRUD, gestion du magasin principal |
| Équipe | `/equipe` | Gestion des utilisateurs et rôles (admin/gestionnaire/caissier) |
| Trésorerie | `/tresorerie` | Dépenses et revenus divers |
| Rapports | `/rapports` | CA, achats, marge, top produits par période + graphiques (évolution CA, modes de paiement, top produits) |
| Paramètres | `/parametres` | Infos boutique, TVA par défaut |

## Factures PDF

Générées avec **barryvdh/laravel-dompdf** (ajouté à `composer.json`, installé
automatiquement via `composer install`). Depuis l'historique des ventes
(`/ventes`), clique sur **Télécharger** sur n'importe quelle ligne. Depuis la
Caisse, un lien **Voir le reçu PDF** apparaît juste après avoir validé une
vente. Le template (`resources/views/pdf/facture.blade.php`) reprend
l'identité DISMAT (logo, couleurs) et affiche : infos boutique, client,
lignes de la facture, totaux HT/TVA/remise/TTC, montant payé/restant.

Pour appliquer le même principe à un bon d'achat ou tout autre document,
duplique `FactureController` + la vue PDF en adaptant les champs.

## Graphiques

Basés sur **Chart.js** (chargé via CDN, aucune dépendance npm supplémentaire) :
- **Tableau de bord** : évolution des ventes sur les 14 derniers jours (courbe)
- **Rapports** : évolution du chiffre d'affaires sur la période sélectionnée
  (courbe), répartition des modes de paiement (anneau), top produits par
  quantité vendue (barres horizontales) — tout se met à jour automatiquement
  quand tu changes la période dans le sélecteur.

## Rôles et permissions

Trois rôles, appliqués à la fois **côté routes** (middleware `role:...`) et
**côté menu** (les liens non autorisés n'apparaissent pas) :

| Page | Admin | Gestionnaire | Caissier |
|---|:---:|:---:|:---:|
| Tableau de bord | ✅ | ✅ | ✅ |
| Caisse, Ventes, Clients | ✅ | ✅ | ✅ |
| Produits, Catégories, Stocks | ✅ | ✅ | ❌ |
| Achats, Fournisseurs | ✅ | ✅ | ❌ |
| Créances, Dettes, Trésorerie, Rapports | ✅ | ✅ | ❌ |
| Magasins, Équipe, Paramètres | ✅ | ❌ | ❌ |

`super_admin` a toujours accès à tout. Un accès direct par URL à une page non
autorisée renvoie une page "Accès refusé" (403) plutôt qu'une erreur brute.

Pour changer cette matrice : édite le tableau `$nav` dans
`resources/views/layouts/app.blade.php` (affichage du menu) et les groupes
`Route::middleware('role:...')` dans `routes/web.php` (contrôle réel d'accès
— c'est **cette partie qui protège vraiment**, le menu n'est qu'un confort
visuel).

## Contrôles métier et alertes

- **Stock jamais négatif** : toute sortie de stock (vente en caisse, sortie
  manuelle) passe par `StockService`, qui verrouille la ligne en base
  (`lockForUpdate`) et lève une `InsufficientStockException` si la quantité
  demandée dépasse le disponible — impossible de survendre, y compris avec
  deux caissiers simultanés sur le même produit.
- **Caisse** : les produits en rupture sont grisés et non cliquables ; un
  badge orange signale un stock proche du seuil minimum ; le bouton "+" du
  panier se désactive à la limite du stock disponible.
- **Paiements (créances, dettes, achats)** : impossible de saisir un montant
  supérieur au solde restant — une erreur de validation s'affiche au lieu de
  plafonner silencieusement le montant.
- **Cloche d'alertes** (header, visible admin/gestionnaire) : regroupe les
  produits en stock bas et les créances clients en retard d'échéance, avec
  lien direct vers la page concernée.
- **Créances en retard** : surlignées en rouge dans `/creances` dès que la
  date d'échéance est dépassée.

## Étendre l'application

Chaque module suit **exactement le même schéma** :
```
app/Livewire/<Module>/Index.php       — composant Livewire (state + logique)
resources/views/livewire/<module>/index.blade.php  — vue (table + modale CRUD)
```
Pour ajouter un nouveau module, duplique le couple le plus proche (ex: pour un
module "Transferts entre magasins", pars de `Stocks/Index.php`) et adapte les
champs, la validation et la vue.

## Roadmap suggérée (non incluse dans cette première version)

- Génération de PDF (factures, tickets) — package conseillé : `barryvdh/laravel-dompdf`
- Inventaires physiques (module de comptage avec écarts) — la table `inventaires`/`inventaire_lignes` existe déjà, il ne manque que le composant Livewire
- Transferts de stock entre magasins — table `transferts` déjà migrée
- Trocage/échange (le module `echanges` d'origine) — table déjà migrée
- Intégration paiement en ligne (l'app d'origine utilise PayTech, un service de paiement sénégalais) pour la facturation d'abonnement SaaS
- Notifications temps réel (table `gstock_notifications` déjà prête, prévoir `laravel-echo` + un driver websockets pour du push en direct)
- Permissions plus fines par rôle — package conseillé : `spatie/laravel-permission`

## Ton compte

Modifie `database/seeders/DatabaseSeeder.php` avant de lancer `php artisan db:seed` :

```php
'nom' => 'Ma Boutique',              // <- Nom de ta boutique
...
'nom' => 'Admin',                     // <- Ton nom
'email' => 'admin@maboutique.com',    // <- Ton email de connexion
'password' => Hash::make('changeme123'), // <- Ton mot de passe
```

Une fois connecté, tu peux tout modifier (nom boutique, TVA, etc.) depuis
**Paramètres**, et ajouter d'autres utilisateurs (caissiers, gestionnaires)
depuis **Équipe**.
