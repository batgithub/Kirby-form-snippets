# Référence des champs

Guide de configuration dans `site/config/config.php`, clé `baptiste.kirby-form-snippets.forms`.

| Document | Usage |
|----------|-------|
| [README.md](../README.md) | Installation et démarrage rapide |
| **[inputs.md](inputs.md)** | Ce fichier — champs, options, paramètres |
| [examples.md](examples.md) | Scénarios complets (contact Panel, filtre blog, controller…) |
| [style.md](style.md) | Personnalisation visuelle (classes CSS, états) |
| [tech.md](tech.md) | Architecture, routes, CSRF |

**Sommaire :**

- [Structure minimale](#structure-minimale)
- [Règles communes](#règles-communes)
- [Alimenter les options](#alimenter-les-options-dun-select-radio-ou-checkbox-group)
- [Mode filtre](#mode-filtre)
- [Destinataires email](#destinataires-email-tofrom)
- [Overrides et hook](#overrides-et-hook)
- [Champs interactifs](#champs-interactifs)
- [Honeytime — anti-spam par délai](#honeytime--anti-spam-par-délai)
- [Champs décoratifs](#champs-décoratifs)
- [Checklists](#checklist-nouveau-formulaire-mode-submit)

## Structure minimale

```php
'baptiste.kirby-form-snippets.forms' => [
    'mon-formulaire' => [           // formKey — identifiant libre
        'fields' => [
            'nom_du_champ' => [     // id HTML + name POST + clé dans l'email
                'input' => 'input', // type de champ (obligatoire)
                // … autres paramètres
            ],
        ],
        'email' => [
            'to' => 'contact@example.com',
            'from' => 'noreply@example.com',
            'subject' => 'Nouveau message',
        ],
    ],
],
```

Affichage :

```php
<?php snippet('form-page', ['formKey' => 'mon-formulaire']) ?>

// Avec overrides depuis le controller
<?php snippet('form-page', [
    'formKey' => 'mon-formulaire',
    'overrides' => $formOverrides,
]) ?>
```

## Règles communes

| Règle | Détail |
|-------|--------|
| Clé du tableau `fields` | Devient `id` et `name` du champ soumis (sauf honeypot avec `name` custom) |
| `input` | Type de rendu : correspond au snippet `form-{input}` |
| `required` | `true` pour rendre le champ obligatoire (validation serveur Uniform) |
| `label` | Texte affiché ; requis pour les champs interactifs |
| `info` | Texte d'aide sous le label (`input`, `textarea`, `select`) |
| `placeholder` | Surcharge le placeholder global (`baptiste.kirby-form-snippets.placeholder`) |
| Options (`select`, groupes) | `value` explicite si besoin, sinon slug du `label` |
| `optionsFrom` | Complète `options` depuis le Panel ou des pages (voir ci-dessous) |
| `mode` | `submit` (défaut) ou `filter` (GET, pas d'email) |

**Valeur d'une option :**

```php
['label' => 'Option A']           // value = "option-a"
['label' => 'Option A', 'value' => 'a']  // value = "a"
```

---

## Alimenter les options d'un select, radio ou checkbox-group

Chaque option = un **libellé** (ce que voit le visiteur) + une **valeur** (ce qui est soumise dans le formulaire).

Résultat HTML typique :

```html
<select name="category">
  <option value="">Toutes</option>
  <option value="tech">Tech</option>
  <option value="design">Design</option>
</select>
```

En mode filtre, une soumission produit par ex. `/blog?category=tech`. En mode submit, la valeur est envoyée en POST (`subject=devis`).

### Tableau comparatif

| Méthode | Où vous configurez | L'éditeur Panel peut modifier ? |
|---------|-------------------|--------------------------------|
| `'options' => [...]` | `config.php` uniquement | Non |
| `optionsFrom` type `pages` | `config.php` + pages Kirby | Oui (en créant des pages) |
| `optionsFrom` type `structure` | `config.php` + champ structure YAML | Oui (tableau dans le Panel) |
| `optionsFrom` callable | Votre fonction PHP | Selon votre fonction |

Les `options` statiques et `optionsFrom` se **fusionnent** au chargement (statiques en premier). Utile pour ajouter « Toutes » ou « — Choisir — » avant les options dynamiques.

---

### 1. Statique — liste dans `config.php`

**Quand :** la liste ne change pas, ou vous préférez tout gérer en code.

**Panel :** rien à configurer.

```php
'category' => [
    'input' => 'select',
    'label' => 'Catégorie',
    'options' => [
        ['label' => 'Toutes', 'value' => ''],
        ['label' => 'Tech', 'value' => 'tech'],
        ['label' => 'Design', 'value' => 'design'],
    ],
],
```

Dans le template (mode filtre) :

```php
if ($cat = get('category')) {
    $articles = $articles->filterBy('category', $cat);
}
```

---

### 2. Pages Panel — une option par page enfant

**Quand :** les options sont des **pages** gérées dans Kirby (ex. catégories de blog).

**Arborescence :**

```
blog/
├── article-1
└── categories/
    ├── tech/
    ├── design/
    └── business/
```

**Blueprint / contenu :** pages sous `blog/categories`.

**Config :**

```php
'category' => [
    'input' => 'select',
    'label' => 'Catégorie',
    'options' => [
        ['label' => 'Toutes', 'value' => ''],
    ],
    'optionsFrom' => [
        'type' => 'pages',
        'parent' => 'blog/categories',
        'label' => 'title',   // défaut
        'value' => 'slug',    // défaut
    ],
],
```

**Ce que fait le plugin :** liste les enfants publiés de `blog/categories` et construit les options automatiquement. L'éditeur ajoute une page → la catégorie apparaît sans toucher au code.

---

### 3. Structure Panel — une option par ligne de tableau

**Quand :** liste éditable dans le Panel, sans créer de vraies pages (sujets de contact, objets de demande…).

**Blueprint** (`site/blueprints/site.yml`) — voir aussi [`blueprints/examples/site-form-settings.yml`](../blueprints/examples/site-form-settings.yml) :

```yaml
contactSubjects:
  type: structure
  label: Objets de contact
  fields:
    label:
      type: text
      label: Libellé affiché
    value:
      type: text
      label: Valeur soumise
```

**Panel** (exemple rempli par l'éditeur) :

| Libellé affiché | Valeur soumise |
|-----------------|----------------|
| Demande de devis | devis |
| Support | support |

**Config :**

```php
'subject' => [
    'input' => 'select',
    'label' => 'Objet de votre demande',
    'required' => true,
    'options' => [
        ['label' => '— Choisir —', 'value' => ''],
    ],
    'optionsFrom' => [
        'type' => 'structure',
        'field' => 'contactSubjects',
        'page' => 'site',       // défaut ; ou 'agences/paris' pour une page
        'label' => 'label',     // clé structure (défaut)
        'value' => 'value',     // clé structure (défaut)
    ],
],
```

Seuls `label` et `value` sont utilisés pour le select. D'autres colonnes de la structure (ex. emails) ne sont **pas** exposées au visiteur.

**Même structure sur une page** (formulaire local à une agence) :

```php
'optionsFrom' => [
    'type' => 'structure',
    'field' => 'contactSubjects',
    'page' => 'agences/paris',
],
```

---

### 4. Callable — fonction PHP custom

**Quand :** la liste vient d'une logique que vous définissez (tags des articles, déduplication, API…). **Vous** choisissez la source ; le plugin n'infère rien automatiquement.

**Helper** (`site/helpers/forms.php`) :

```php
<?php

use Kirby\Cms\Page;

function blogTagOptions(Page $blogPage): array
{
    $tags = [];

    foreach ($blogPage->children()->listed() as $article) {
        foreach ($article->tags()->split(',') as $tag) {
            $tag = trim($tag);
            if ($tag !== '') {
                $tags[$tag] = $tag;
            }
        }
    }

    $options = [['label' => 'Tous les tags', 'value' => '']];

    foreach ($tags as $tag) {
        $options[] = [
            'label' => ucfirst($tag),
            'value' => $tag,
        ];
    }

    return $options;
}
```

**Config :**

```php
'tag' => [
    'input' => 'select',
    'label' => 'Tag',
    'optionsFrom' => fn (): array => blogTagOptions(page()),
],
```

**Variante simple** — liste en code, centralisée dans une fonction réutilisable (controller, hook, config) :

```php
function regionOptions(): array
{
    return [
        ['label' => 'Toute la France', 'value' => ''],
        ['label' => 'Île-de-France', 'value' => 'idf'],
        ['label' => 'Provence', 'value' => 'paca'],
    ];
}

// config.php
'region' => [
    'input' => 'select',
    'label' => 'Région',
    'optionsFrom' => fn (): array => regionOptions(),
],
```

---

### Exemple complet — filtre blog

Voir [examples.md — Filtre blog](examples.md#filtre-blog) (pages Panel, callable tags, template).

Fonctionne aussi avec `checkbox-group` et `radio-group`.

---

## Options dynamiques (`optionsFrom`) — référence rapide

Alias de la section ci-dessus pour navigation interne.

| `type` | Paramètres principaux |
|--------|----------------------|
| `pages` | `parent`, `label` (défaut `title`), `value` (défaut `slug`) |
| `structure` | `field`, `page` (défaut `site`), `label` / `value` (défaut `label` / `value`) |
| callable | `fn (): array => [ ['label' => '…', 'value' => '…'], … ]` |

---

## Mode filtre

`'mode' => 'filter'` — formulaire GET, pas de clé `email`, pas de CSRF, honeypot ni honeytime.

```php
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

- Affichage : `snippet('form-filter', ['formKey' => 'blog-filter'])`
- Filtrage : `get('nom_du_champ')` dans le template — le plugin **n'applique pas** le filtre sur la collection

Scénarios détaillés → [examples.md](examples.md#filtre-blog).

---

## Destinataires email (`toFrom`)

Alternative à `'to' => '…'`. Résolu à la **soumission POST** uniquement.

| Cas | Config | Détail |
|-----|--------|--------|
| Destinataire fixe | `'to' => 'contact@…'` | — |
| Par `formKey` (Panel) | `toFrom` + structure `formRecipients` | [examples.md](examples.md#contact--email-par-formkey-panel) |
| Par objet choisi | callable `toFrom` ou hook | [examples.md](examples.md#contact--email-selon-lobjet) |
| Champ Site simple | `'toFrom' => 'site.contactEmail'` | — |

```php
// Statique
'email' => ['to' => 'contact@example.com', 'from' => '…', 'subject' => '…'],

// Panel — match formKey
'email' => [
    'toFrom' => ['field' => 'formRecipients', 'match' => 'formKey', 'value' => 'email'],
    'to' => 'contact@example.com',
],

// Objet soumis — callable
'email' => [
    'toFrom' => fn (string $formKey): string|array => match (get('subject')) {
        'devis' => ['devis@example.com', 'sales@example.com'],
        default => 'contact@example.com',
    },
],
```

Fallback : `baptiste.kirby-form-snippets.defaultEmailTo`.

---

## Overrides et hook

Injecter des options ou de l'email à l'affichage (controller) ou à la soumission (hook).

```php
// Controller → template
snippet('form-filter', ['formKey' => 'blog-filter', 'overrides' => $formOverrides]);

// Ou directement
repliq_form('contact', ['fields' => ['subject' => ['options' => $myOptions]]]);
```

Règles de fusion : `fields.{id}` merge récursif ; `fields.{id}.options` **remplace** ; `email` merge récursif.

Hook `repliq.form.config` — `$context` : `render` | `submit`. Nécessaire pour l'email dynamique à la POST (pas de controller sur la route plugin).

Scénarios complets → [examples.md — Options depuis un controller](examples.md#options-depuis-un-controller).

Ordre : **`config.php`** → hook → overrides. Le Panel est lu via `optionsFrom` / `toFrom`.

---

## Paramètres des snippets

### `form-page`

| Paramètre | Défaut |
|-----------|--------|
| `formKey` | obligatoire |
| `overrides` | — |
| `formData` | — |
| `formClass` | `repliq-form-{formKey}` |
| `formSelector` | `.{formClass}` |
| `submitLabel` | `Envoyer` (résolution : session → config formulaire → option globale) |
| `successMessage` | message de remerciement (idem ; requis pour HTMX si hors block Panel — voir [tech.md](tech.md#message-de-succès-et-libellé-du-bouton)) |

### `form-filter`

| Paramètre | Défaut |
|-----------|--------|
| `formKey` | obligatoire |
| `overrides` | — |
| `formData` | — |
| `formClass` | `repliq-form-{formKey}` |
| `submitLabel` | `Filtrer` |
| `formActionOverride` | URL page courante |

---

## Champs interactifs

### `input` — champ texte

Types HTML courants via `type` : `text` (défaut), `email`, `tel`, `phone` (alias de `tel`), `number`, `url`, etc.

| Paramètre | Type | Défaut | Validation serveur |
|-----------|------|--------|-------------------|
| `label` | string | — | — |
| `required` | bool | `false` | `required` |
| `type` | string | `text` | `email`, `tel` si type correspondant |
| `placeholder` | string | option plugin | — |
| `info` | string | — | — |
| `pattern` | string | — | HTML uniquement |
| `inputmode` | string | — | HTML uniquement (clavier mobile : `numeric`, `tel`, `decimal`, `email`…) |
| `minlength` | int | — | HTML uniquement |
| `maxlength` | int | — | HTML uniquement (+ limite plugin : 1000 car.) |

```php
'name' => [
    'input' => 'input',
    'label' => 'Nom complet',
    'required' => true,
],
'email' => [
    'input' => 'input',
    'type' => 'email',
    'label' => 'Adresse email',
    'required' => true,
    'info' => 'Nous ne partagerons jamais votre email.',
],
'phone' => [
    'input' => 'input',
    'type' => 'tel',
    'label' => 'Téléphone',
],
```

---

### `textarea` — texte multiligne

| Paramètre | Type | Défaut | Validation serveur |
|-----------|------|--------|-------------------|
| `label` | string | — | — |
| `required` | bool | `false` | `required` |
| `placeholder` | string | option plugin | — |
| `info` | string | — | — |
| `rows` | int | — | — |
| `minlength` | string/int | — | HTML uniquement |
| `maxlength` | string/int | — | HTML uniquement (+ limite plugin : 3000 car.) |

```php
'message' => [
    'input' => 'textarea',
    'label' => 'Votre message',
    'required' => true,
    'rows' => 6,
],
```

---

### `select` — liste déroulante

| Paramètre | Type | Défaut | Validation serveur |
|-----------|------|--------|-------------------|
| `label` | string | — | — |
| `required` | bool | `false` | `required` |
| `options` | array | — | `in:` sur les values |
| `optionsFrom` | array | — | fusionne des options Panel/pages (voir section dédiée) |
| `multiselect` | bool | `false` | `notEmpty` si requis, `in:` sur les values |
| `info` | string | — | — |

Chaque option : `label`, `value` (optionnel), `selected` (optionnel, état initial).

```php
'subject' => [
    'input' => 'select',
    'label' => 'Motif de contact',
    'required' => true,
    'options' => [
        ['label' => 'Demande de devis', 'value' => 'devis'],
        ['label' => 'Support', 'value' => 'support'],
        ['label' => 'Autre'],
    ],
],
```

---

### `checkbox` — case à cocher unique

Le libellé sert aussi de label cliquable. Valeur soumise : `value` ou slug du `label`.

| Paramètre | Type | Défaut | Validation serveur |
|-----------|------|--------|-------------------|
| `label` | string | — | — |
| `required` | bool | `false` | `required` (doit être cochée) |
| `value` | string | slug du label | — |

```php
'rgpd' => [
    'input' => 'checkbox',
    'label' => 'J\'accepte la politique de confidentialité',
    'required' => true,
    'value' => 'accepted',
],
```

---

### `checkbox-group` — plusieurs cases

Les valeurs cochées sont envoyées sous forme de tableau (`name[]`).

| Paramètre | Type | Défaut | Validation serveur |
|-----------|------|--------|-------------------|
| `label` | string | — | — (legend du fieldset) |
| `required` | bool | `false` | `notEmpty` si obligatoire |
| `options` | array | — | `in:` sur les values |

```php
'interests' => [
    'input' => 'checkbox-group',
    'label' => 'Centres d\'intérêt',
    'required' => true,
    'options' => [
        ['label' => 'Design', 'value' => 'design'],
        ['label' => 'Développement', 'value' => 'dev'],
        ['label' => 'SEO', 'value' => 'seo'],
    ],
],
```

---

### `radio-group` — choix unique parmi plusieurs

| Paramètre | Type | Défaut | Validation serveur |
|-----------|------|--------|-------------------|
| `label` | string | — | — (legend du fieldset) |
| `required` | bool | `false` | `required` |
| `options` | array | — | `in:` sur les values |

```php
'budget' => [
    'input' => 'radio-group',
    'label' => 'Budget estimé',
    'required' => true,
    'options' => [
        ['label' => 'Moins de 5 000 €', 'value' => 'lt-5k'],
        ['label' => '5 000 – 15 000 €', 'value' => '5k-15k'],
        ['label' => 'Plus de 15 000 €', 'value' => 'gt-15k'],
    ],
],
```

---

### `honeypot` — anti-spam

Champ invisible pour les bots. **À placer dans chaque formulaire public.** Si rempli, la soumission échoue silencieusement.

| Paramètre | Type | Défaut | Validation serveur |
|-----------|------|--------|-------------------|
| `name` | string | clé du champ | `HoneypotGuard` Uniform (rejet si rempli) |

Utilisez un nom peu évident pour le honeypot :

```php
'website' => [
    'input' => 'honeypot',
    'name' => 'website',  // optionnel — par défaut = clé "website"
],
```

---

## Honeytime — anti-spam par délai

Guard Uniform optionnel : rejette les soumissions **trop rapides** (comportement typique des bots). Champ hidden avec timestamp chiffré — invisible pour l'utilisateur, sans JavaScript.

**Ce n'est pas un type `input` dans `fields`.** Configuration au niveau plugin ou formulaire. Doc Uniform : [Honeytime Guard](https://kirby-uniform.readthedocs.io/en/latest/guards/honeytime/).

### Prérequis

Clé de chiffrement (une fois par site) :

```bash
head -c 32 /dev/urandom | base64
```

```php
// site/config/config.php
'uniform.honeytime.key' => 'base64:VOTRE_CLE=',
```

### Activation globale

```php
'baptiste.kirby-form-snippets' => [
    'honeytime' => [
        'enabled' => true,
        'seconds' => 10,              // délai minimum (défaut Uniform)
        'field' => 'uniform-honeytime', // nom du champ hidden
        // 'key' => '…',              // optionnel si uniform.honeytime.key est défini
    ],
    'forms' => [
        'contact' => [ /* … */ ],
    ],
],
```

### Par formulaire

| Valeur | Effet |
|--------|-------|
| `'honeytime' => true` | Active avec les options globales |
| `'honeytime' => false` | Désactive même si global activé |
| `'honeytime' => ['seconds' => 15]` | Active avec surcharges locales |

```php
'contact' => [
    'honeytime' => true,
    'fields' => [ /* … */ ],
],
'newsletter' => [
    'honeytime' => false, // pas de Honeytime sur ce form
    'fields' => [ /* … */ ],
],
```

Le snippet `form-fields` injecte automatiquement le champ hidden et `form-honeytime-refresh` (timestamp frais via JS, compatible cache pages). `handleSubmit()` appelle `honeytimeGuard()`. Combinable avec le honeypot.

### Rafraîchissement JS (cache)

Comme le CSRF, le timestamp n'est **pas** figé dans le HTML :

1. `form-honeytime` — `<input type="hidden" value="">`
2. Au chargement, `form-honeytime-refresh` appelle `GET kirby-form-snippets/honeytime-token` → `{ "value": "…" }`

Ajoutez la route à `cache.ignore` (voir [README](../README.md) et [tech.md](tech.md#cache)).

### Honeypot + Honeytime

Recommandé pour les formulaires publics :

```php
'contact' => [
    'honeytime' => true,
    'fields' => [
        // … champs visibles …
        'website' => ['input' => 'honeypot'],
    ],
],
```

---

## Champs décoratifs

Pas de soumission, pas de validation. Utiles pour structurer visuellement le formulaire.

### `line` — séparateur

| Paramètre | Type | Défaut |
|-----------|------|--------|
| `class` | string | — |

```php
'separator' => [
    'input' => 'line',
    'class' => 'form-divider',
],
```

---

### `section-title` — titre de section

| Paramètre | Type | Défaut |
|-----------|------|--------|
| `title` | string | — |
| `tag` | string | `h2` (`h2`–`h6`) |

L'`id` HTML est la clé du champ dans `fields`.

```php
'section-coordonnees' => [
    'input' => 'section-title',
    'title' => 'Vos coordonnées',
    'tag' => 'h3',
],
```

---

### `card` — encadré informatif

| Paramètre | Type | Défaut |
|-----------|------|--------|
| `text` | string | — |
| `class` | string | — |

```php
'info-delai' => [
    'input' => 'card',
    'text' => 'Nous répondons sous 48 h ouvrées.',
    'class' => 'form-info-box',
],
```

---

## Exemple complet

Formulaire de contact avec structure, validation et honeypot :

```php
'contact' => [
    'fields' => [
        'section-infos' => [
            'input' => 'section-title',
            'title' => 'Informations',
        ],
        'name' => [
            'input' => 'input',
            'label' => 'Nom',
            'required' => true,
        ],
        'email' => [
            'input' => 'input',
            'type' => 'email',
            'label' => 'Email',
            'required' => true,
        ],
        'phone' => [
            'input' => 'input',
            'type' => 'tel',
            'label' => 'Téléphone',
        ],
        'line-1' => [
            'input' => 'line',
        ],
        'section-message' => [
            'input' => 'section-title',
            'title' => 'Votre message',
            'tag' => 'h3',
        ],
        'subject' => [
            'input' => 'select',
            'label' => 'Sujet',
            'required' => true,
            'options' => [
                ['label' => 'Devis', 'value' => 'devis'],
                ['label' => 'Question', 'value' => 'question'],
            ],
        ],
        'message' => [
            'input' => 'textarea',
            'label' => 'Message',
            'required' => true,
            'rows' => 5,
        ],
        'rgpd' => [
            'input' => 'checkbox',
            'label' => 'J\'accepte le traitement de mes données',
            'required' => true,
            'value' => 'yes',
        ],
        'website' => [
            'input' => 'honeypot',
        ],
    ],
    'email' => [
        'to' => 'contact@example.com',
        'from' => 'noreply@example.com',
        'subject' => 'Contact site web',
        // 'template' => 'emails/submition.html',  // optionnel — défaut plugin
        // 'theme' => ['intro' => '…', 'colors' => ['accent' => '#2563EB']],
        // 'themeFrom' => 'site.emailBranding',
    ],
],
```

---

## Checklist nouveau formulaire (mode submit)

1. Choisir un `formKey` unique (ex. `contact`, `newsletter`, `devis`).
2. Lister les champs dans `fields` — une clé par champ soumis.
3. Ajouter `'input' => 'honeypot'` en fin de liste (recommandé).
4. (Optionnel) Activer Honeytime — `'honeytime' => true` et clé `uniform.honeytime.key` (voir [Honeytime](#honeytime--anti-spam-par-délai)).
5. Configurer `email` (destinataire, expéditeur, sujet — `to` ou `toFrom`).
6. Exclure les routes plugin du cache Kirby : `csrf-token`, `honeytime-token`, `submit` (voir [README](../README.md#installation)).
7. Afficher avec `snippet('form-page', ['formKey' => '…'])`.
8. Tester : champs requis, email invalide, honeypot rempli, soumission immédiate (Honeytime), succès après envoi.

## Checklist formulaire filtre

1. Choisir un `formKey` unique (ex. `blog-filter`).
2. Définir `'mode' => 'filter'` et les champs dans `fields`.
3. Afficher avec `snippet('form-filter', ['formKey' => '…'])`.
4. Appliquer `get('…')` sur la collection dans le template.
5. (Optionnel) Vérifier le cache : routes plugin dans `cache.ignore` ; Honeytime/CSRF se rafraîchissent en JS — pas besoin d'exclure la page du cache pages pour ça (voir [tech.md](tech.md#cache)).

## Personnalisation des messages d'erreur

Surcharge dans `config.php` :

```php
'baptiste.kirby-form-snippets.messages.required' => 'Ce champ est obligatoire.',
'baptiste.kirby-form-snippets.messages.email' => 'Email invalide.',
'baptiste.kirby-form-snippets.maxLength.input' => 500,
```

Clés disponibles : `required`, `requiredSelect`, `requiredCheckbox`, `requiredCheckboxGroup`, `requiredRadioGroup`, `email`, `tel`, `maxLengthInput`, `maxLengthTextarea`, `honeypot`, `honeytime`, `honeytimeInvalid`, `in`.

Pour Honeytime, les messages affichés passent par les clés Uniform `uniform-honeytime-reject` et `uniform-honeytime-invalid` (traductions FR fournies par le plugin). Voir [tech.md](tech.md#honeytime).

## Voir aussi

- [README.md](../README.md) — installation et démarrage
- [examples.md](examples.md) — scénarios d'implémentation
- [tech.md](tech.md) — architecture, routes, CSRF
