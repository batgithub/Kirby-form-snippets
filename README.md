# Kirby form snippets

Formulaires Kirby **sans contrôleur de page** : déclarez une config, passez un `formKey`, placez le snippet où vous voulez. Validation, CSRF, honeypot, [Honeytime](https://kirby-uniform.readthedocs.io/en/latest/guards/honeytime/) et envoi d'email via [Uniform](https://github.com/mzur/kirby-uniform) v5.

## Prérequis

- Kirby 3.5+, 4 ou 5
- [mzur/kirby-uniform](https://github.com/mzur/kirby-uniform) ^5.0
- Configuration email Kirby ([guide](https://getkirby.com/docs/guide/emails))

## Installation

```bash
composer require mzur/kirby-uniform:^5.0
```

Installez ce plugin (Composer ou [submodule](https://github.com/batgithub/Kirby-form-snippets)).

Ajoutez dans `site/config/config.php` :

```php
'cache' => [
    'ignore' => [
        'kirby-form-snippets/csrf-token',
        'kirby-form-snippets/honeytime-token',
        'kirby-form-snippets/submit',
    ],
],
```

Ces routes ne doivent **pas** être mises en cache : elles servent au rafraîchissement JS du token CSRF et du timestamp Honeytime (compatible avec le cache des pages). Détail → [docs/tech.md](docs/tech.md#cache).

> Si un script de votre site rafraîchit déjà le token CSRF sur tous les `<form>`, excluez-en les formulaires du plugin (attribut `data-repliq-form`) pour éviter une `TokenMismatchException`. Détail → [docs/tech.md](docs/tech.md#csrf).

## Démarrage rapide

### 1. Déclarer le formulaire

Dans `site/config/config.php` :

```php
'baptiste.kirby-form-snippets.forms' => [
    'contact' => [
        'title' => 'Contact',
        'fields' => [
            'name' => [
                'input' => 'input',
                'label' => 'Nom',
                'required' => true,
            ],
            'email' => [
                'input' => 'input',
                'type' => 'email',
                'label' => 'Adresse email',
                'required' => true,
            ],
            'message' => [
                'input' => 'textarea',
                'label' => 'Message',
                'required' => true,
            ],
            'website' => [
                'input' => 'honeypot',
            ],
        ],
        'email' => [
            'to' => 'contact@example.com',
            'from' => 'noreply@example.com',
            'subject' => 'Nouveau message depuis le site',
            'theme' => [
                'intro' => 'Message reçu via le formulaire contact.',
                'colors' => ['accent' => '#2563EB'],
            ],
        ],
    ],
],
```

La clé `contact` est votre `formKey` (identifiant libre).

### 2. Afficher le formulaire

Dans un template, un snippet ou un block :

```php
<?php snippet('form-page', ['formKey' => 'contact']) ?>
```

C'est tout. Le plugin gère validation, CSRF, honeypot, envoi email et redirection vers la page courante.

**Récapitulatif d'erreurs (optionnel)** — en tête du formulaire, en plus des messages sous chaque champ :

```php
// Global
'errorsSummary' => ['enabled' => true],

// Ou par formulaire
'contact' => [
    'errorsSummary' => true,
    // …
],

// Ou à l'affichage
snippet('form-page', ['formKey' => 'contact', 'errorsSummary' => true]);
```

Snippet dédié : `form-errors-summary`. Styles → [docs/style.md](docs/style.md).

### 3. Personnaliser l'email (optionnel)

Le template email par défaut (`emails/submition.html`) se personnalise **sans modifier le HTML** via `defaultEmailTheme` (global) et `email.theme` (par formulaire).

```php
'baptiste.kirby-form-snippets' => [
    'defaultEmailTheme' => [
        'logo' => 'https://example.com/logo.png',
        'colors' => [
            'pageBg' => '#F5F5F5',
            'cardBg' => '#FFFFFF',
            'border' => '#E5E5E5',
            'label' => '#737373',
            'text' => '#171717',
            'accent' => '#2563EB',
        ],
    ],
    'forms' => [
        'contact' => [
            'title' => 'Contact', // titre affiché dans l'email
            // …
            'email' => [
                'to' => 'contact@example.com',
                'from' => 'noreply@example.com',
                'subject' => 'Nouveau message depuis le site',
                'theme' => [
                    'intro' => 'Message reçu via le formulaire contact.',
                    'footer' => 'Répondre via reply-to si disponible.',
                    'colors' => ['accent' => '#E11D48'], // merge partiel
                ],
                // Depuis le Panel (fichier ou structure) :
                // 'themeFrom' => 'site.emailBranding',
                // 'themeFrom' => ['field' => 'formEmailThemes', 'match' => 'formKey'],
            ],
        ],
    ],
],
```

| Clé `theme` | Rôle |
|-------------|------|
| `colors.*` | Couleurs (`pageBg`, `cardBg`, `border`, `label`, `text`, `accent`) |
| `logo`, `logoAlt`, `logoLink` | En-tête avec logo |
| `intro`, `footer`, `preview` | Textes |
| `hideEmptyFields` | Masquer les champs vides |
| `templateData` | Variables supplémentaires dans le template |

Template custom : `'template' => 'emails/mon-template.html'` ou surcharge dans `site/templates/emails/`. Détail → [docs/tech.md](docs/tech.md#template-email).

### 4. Honeytime (optionnel)

Le [Honeytime guard](https://kirby-uniform.readthedocs.io/en/latest/guards/honeytime/) Uniform rejette les soumissions trop rapides (bots). Contrairement au honeypot, ce n'est **pas** un champ dans `fields` : c'est une option globale ou par formulaire.

Générez une clé de chiffrement :

```bash
head -c 32 /dev/urandom | base64
```

Puis dans `site/config/config.php` :

```php
'uniform.honeytime.key' => 'base64:VOTRE_CLE_GENEREE=',

'baptiste.kirby-form-snippets' => [
    'honeytime' => [
        'enabled' => true,
        'seconds' => 10, // délai minimum avant soumission (défaut Uniform)
    ],
    'forms' => [
        'contact' => [
            // 'honeytime' => false, // désactiver sur ce form uniquement
            'fields' => [ /* … */ ],
        ],
    ],
],
```

Le champ hidden et la validation serveur sont gérés automatiquement ; le timestamp est régénéré en JavaScript (comme le CSRF), y compris si la page est en cache. Détail → [docs/inputs.md](docs/inputs.md#honeytime--anti-spam-par-délai), [docs/tech.md](docs/tech.md#honeytime).

## Deux modes

| Mode | Config | Snippet | Méthode | Rôle |
|------|--------|---------|---------|------|
| **Email** | `submit` (défaut) | `form-page` | POST | Envoi email |
| **Filtre** | `'mode' => 'filter'` | `form-filter` | GET | Affiche le formulaire ; **vous** filtrez dans le template |

La structure des champs se déclare toujours dans `config.php`.

### Filtre en bref

```php
// config.php
'blog-filter' => [
    'mode' => 'filter',
    'fields' => [
        'category' => [
            'input' => 'select',
            'label' => 'Catégorie',
            'options' => [['label' => 'Toutes', 'value' => '']],
            'optionsFrom' => ['type' => 'pages', 'parent' => 'blog/categories'],
        ],
    ],
],
```

```php
// template blog.php
<?php snippet('form-filter', ['formKey' => 'blog-filter']) ?>

<?php
$articles = $page->children()->listed();
if ($cat = get('category')) {
    $articles = $articles->filterBy('category', $cat);
}
?>
```

Scénarios complets (Panel, controller, tags…) → [docs/examples.md](docs/examples.md).

## Cas courants

| Besoin | Voir |
|--------|------|
| Options statiques, Panel ou callable | [docs/inputs.md](docs/inputs.md#alimenter-les-options-dun-select-radio-ou-checkbox-group) |
| Email selon l'objet choisi | [docs/examples.md](docs/examples.md#contact--email-selon-lobjet) |
| Filtre blog avec catégories / tags | [docs/examples.md](docs/examples.md#filtre-blog) |
| Overrides depuis un controller | [docs/examples.md](docs/examples.md#options-depuis-un-controller) |
| Markup HTML personnalisé | [docs/examples.md](docs/examples.md#markup-personnalisé) |
| Personnalisation CSS | [docs/style.md](docs/style.md) |
| Personnalisation email (thème, logo, couleurs) | [docs/tech.md](docs/tech.md#template-email) |
| Honeytime (soumission trop rapide) | [docs/inputs.md](docs/inputs.md#honeytime--anti-spam-par-délai) |
| Cache des pages + formulaires | [docs/tech.md](docs/tech.md#cache) |
| Block Panel | [docs/examples.md](docs/examples.md#block-panel) |

## Block Kirby

Autorisez le block fourni par le plugin dans votre blueprint :

```yaml
fields:
  blocks:
    type: blocks
    fieldsets:
      contact-form: blocks/contact-form
```

Le block expose `formKey`, `submitLabel` et `successMessage` dans le Panel.

## Documentation

| Fichier | Contenu |
|---------|---------|
| **[README.md](README.md)** | Installation et démarrage rapide (ce fichier) |
| **[docs/](docs/)** | Index de la documentation |
| **[docs/inputs.md](docs/inputs.md)** | Référence des champs, options, `toFrom`, checklists |
| **[docs/examples.md](docs/examples.md)** | Scénarios d'implémentation pas à pas |
| **[docs/style.md](docs/style.md)** | Personnalisation visuelle (classes, CSS) |
| **[docs/tech.md](docs/tech.md)** | Architecture, routes, CSRF, honeytime, cache, flux internes |

