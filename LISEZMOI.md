# Mise à jour : catalogue public, auteurs, paiement Kkiapay

## 1. Fichiers

Copiez tout le contenu de ce dossier dans le projet (mêmes chemins). `routes/web.php` est **remplacé** en entier.

Supprimez ces fichiers de la livraison précédente, ils sont remplacés :

- `app/Http/Controllers/Etudiant/CatalogueController.php`
- `app/Http/Controllers/Etudiant/LectureController.php`
- `app/Http/Controllers/Etudiant/Concerns/VerifieAccesMemoire.php`
- `resources/views/etudiant/catalogue/` (dossier entier, devenu `resources/views/public/catalogue/`)

Aucun paquet Composer n'est ajouté : la vérification Kkiapay passe par le client HTTP de Laravel.

## 2. Configuration Kkiapay

`.env` (Sandbox pour le développement et les tests) :

```
KKIAPAY_PUBLIC_KEY=
KKIAPAY_PRIVATE_KEY=
KKIAPAY_SECRET=
KKIAPAY_SANDBOX=true
```

`config/services.php` (ajouter dans le tableau retourné) :

```php
'kkiapay' => [
    'public_key' => env('KKIAPAY_PUBLIC_KEY'),
    'private_key' => env('KKIAPAY_PRIVATE_KEY'),
    'secret' => env('KKIAPAY_SECRET'),
    'sandbox' => env('KKIAPAY_SANDBOX', true),
],
```

Le prix (500 FCFA) est dans `config/memoires.php`. Après chaque changement : `php artisan config:clear`.

**En production** : `KKIAPAY_SANDBOX=false` avec les clés Live. Ce réglage pilote à la fois le widget et l'appel de vérification.
Ne jamais déployer avec `KKIAPAY_SANDBOX=true` : une transaction de test serait alors acceptée comme un vrai paiement.

## 3. Modifications à faire à la main dans vos modèles

Je n'ai pas vu `app/Models/Memoire.php` : je ne le remplace pas.

```php
// Memoire : ajouter 'autres_auteurs' à $fillable
protected $fillable = [ /* ...existants... */ 'autres_auteurs' ];

// Memoire : relation nécessaire au compteur (withCount('lectures')), si elle n'existe pas déjà
public function lectures(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(Lecture::class);
}
```

`Lecture` : rien d'obligatoire (`paiement_id` est affecté directement, hors `$fillable`).

## 4. Base de données

```
php artisan migrate
```

Trois migrations : colonne `memoires.autres_auteurs`, table `paiements`, `lectures.user_id` rendu nullable + `lectures.paiement_id`.
Le `down()` de la troisième supprime les lectures anonymes (impossible de remettre `user_id` en NOT NULL sinon).

## 5. Comment l'accès fonctionne

| Qui | Lecture | Téléchargement |
|---|---|---|
| Connecté (étudiant, responsable, admin) | règle existante : public, ou propriétaire, ou staff | seulement si `telechargement_autorise` est coché |
| Externe ayant payé (code valide pour CE mémoire) | oui, si le mémoire est public | oui, toujours |

Le code d'accès est généré après vérification serveur, affiché une seule fois, et seul son hash SHA-256 est stocké.
Il est mémorisé en session après le paiement, et peut être ressaisi (« J'ai déjà payé ») depuis un autre appareil.

## 6. Test en Sandbox

1. Déconnecté, ouvrir un mémoire du catalogue : le résumé est visible, le PDF ne l'est pas, le bouton de paiement apparaît.
2. Payer avec un numéro de test de la documentation Kkiapay : retour sur la page avec le code d'accès et le PDF.
3. Se déconnecter / vider les cookies, saisir le code : l'accès revient.
4. Réutiliser la même référence de transaction : refusée. Utiliser le code sur un autre mémoire : refusé.
5. Vérifier en base : `paiements` (statut `reussi`, `jeton_hash` rempli) et `lectures` (`user_id` NULL, `paiement_id` rempli).
