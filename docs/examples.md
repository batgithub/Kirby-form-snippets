# Exemples d'implémentation

Scénarios pas à pas pour les cas courants. La structure des champs se déclare toujours dans `config.php` ; le Panel Kirby sert de **source de données** optionnelle (`optionsFrom`, `toFrom`).

**Sommaire**

- [Contact simple](#contact-simple)
- [Contact avec Honeytime](#contact-avec-honeytime)
- [Contact — options depuis le Panel](#contact--options-depuis-le-panel)
- [Contact — email selon l'objet](#contact--email-selon-lobjet)
- [Contact — email par formKey (Panel)](#contact--email-par-formkey-panel)
- [Filtre blog](#filtre-blog)
- [Options depuis un controller](#options-depuis-un-controller)
- [Plusieurs formulaires sur une page](#plusieurs-formulaires-sur-une-page)
- [Markup personnalisé](#markup-personnalisé)
- [HTMX — soumission AJAX](#htmx--soumission-ajax)
- [HTMX — script intégré à la main](#htmx--script-intégré-à-la-main)
- [Block Panel](#block-panel)
- [Récapitulatif des flux](#récapitulatif-des-flux)

---

## Contact simple

Formulaire statique, un seul destinataire. Aucune admin Panel.

**Fichiers :** `site/config/config.php`, template ou block.

```php
'baptiste.kirby-form-snippets.forms' => [
    'contact' => [
        'fields' => [
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
            'subject' => 'Nouveau message',
        ],
    ],
],
```

```php
<?php snippet('form-page', ['formKey' => 'contact']) ?>
```

---

## Contact avec Honeytime

Formulaire public avec honeypot **et** [Honeytime](https://kirby-uniform.readthedocs.io/en/latest/guards/honeytime/) : champ hidden, guard serveur et **timestamp régénéré en JS** (compatible cache pages), une fois la clé configurée.

**Fichiers :** `site/config/config.php`, template ou block.

```php
// Clé site (une fois)
'uniform.honeytime.key' => 'base64:VOTRE_CLE_GENEREE=',

'baptiste.kirby-form-snippets' => [
    'honeytime' => [
        'enabled' => true,
        'seconds' => 10,
    ],
    'forms' => [
        'contact' => [
            'fields' => [
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
                'subject' => 'Nouveau message',
            ],
        ],
    ],
],
```

**Désactiver Honeytime sur un seul formulaire** alors que le global est activé :

```php
'newsletter' => [
    'honeytime' => false,
    'fields' => [ /* … */ ],
],
```

**Tests manuels :**

1. Soumettre immédiatement après chargement (attendre que le JS ait rempli le champ) → rejet (message « patienter »).
2. Attendre ≥ `seconds`, remplir et envoyer → succès.
3. Vérifier que le champ Honeytime n'apparaît pas dans l'email reçu.
4. Avec cache pages activé : recharger une page cache servie, soumettre trop tôt → rejet ; après délai → succès.

---

## Contact — options depuis le Panel

Le visiteur choisit un **objet** dans un select. La liste est gérée dans le Panel ; les emails ne sont pas affichés.

### Blueprint (`site/blueprints/site.yml`)

Copiez ou adaptez `[blueprints/examples/site-form-settings.yml](../blueprints/examples/site-form-settings.yml)` :

```yaml
contactSubjects:
  type: structure
  label: Objets de contact
  fields:
    label:
      type: text
      label: Libellé affiché
      required: true
    value:
      type: text
      label: Valeur soumise
      required: true
```

### Panel (exemple)


| Libellé affiché  | Valeur soumise |
| ---------------- | -------------- |
| Demande de devis | devis          |
| Support          | support        |


### Config

```php
'contact' => [
    'fields' => [
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
                'page' => 'site',
            ],
        ],
        // name, email, message, honeypot…
    ],
    'email' => [
        'to' => 'contact@example.com',
        'from' => 'noreply@example.com',
        'subject' => 'Nouveau message',
    ],
],
```

**Variante page locale** — structure sur une page agence :

```php
'optionsFrom' => [
    'type' => 'structure',
    'field' => 'contactSubjects',
    'page' => 'agences/paris',
],
```

---

## Contact — email selon l'objet

Le visiteur sélectionne un objet ; l'email part vers un ou plusieurs destinataires selon la valeur soumise. Les emails restent **invisibles** côté front.

À la soumission POST, Kirby passe par la route du plugin (pas de controller). Deux approches :

### A — Callable `toFrom` (recommandé)

```php
'email' => [
    'toFrom' => fn (string $formKey): string|array => match (get('subject')) {
        'devis' => ['devis@example.com', 'sales@example.com'],
        'support' => 'support@example.com',
        default => 'contact@example.com',
    },
    'from' => 'noreply@example.com',
    'subject' => 'Nouveau message',
],
```

### B — Hook + helper

`**site/helpers/forms.php` :**

```php
function contactEmailTo(string $subject): string|array
{
    return match ($subject) {
        'devis' => ['devis@example.com', 'sales@example.com'],
        'support' => 'support@example.com',
        default => 'contact@example.com',
    };
}
```

`**site/config/config.php` :**

```php
'hooks' => [
    'repliq.form.config' => function (array $config, string $formKey, string $context): array {
        if ($context === 'submit' && $formKey === 'contact') {
            $config['email']['to'] = contactEmailTo(get('subject') ?? '');
        }
        return $config;
    },
],
```

Pour lire les objets **depuis le Panel** dans le helper, parcourez la structure `contactSubjects` et matchez sur `value`.

---

## Contact — email par formKey (Panel)

Un destinataire par formulaire, géré dans le Panel (ex. `contact` → `contact@…`, `devis` → `devis@…`).

### Blueprint

```yaml
formRecipients:
  type: structure
  label: Destinataires par formulaire
  fields:
    formKey:
      type: text
      label: Clé formulaire
      required: true
    email:
      type: email
      label: Destinataire
      required: true
```

### Config

```php
'email' => [
    'toFrom' => [
        'field' => 'formRecipients',
        'match' => 'formKey',
        'value' => 'email',
        'page' => 'site',
    ],
    'to' => 'contact@example.com',
    'from' => 'noreply@example.com',
    'subject' => 'Nouveau message',
],
```

---

## Filtre blog

Le plugin affiche le formulaire en GET ; **vous** filtrez la collection dans le template.

### Fichiers concernés


| Fichier                             | Rôle                                          |
| ----------------------------------- | --------------------------------------------- |
| `site/config/config.php`            | `'mode' => 'filter'` + champs                 |
| `site/templates/blog.php`           | Snippet + `get()` + `filterBy()`              |
| `site/blueprints/pages/article.yml` | (optionnel) champ `category` sur les articles |
| `site/pages/blog/categories/`       | (optionnel) pages catégories                  |


### Options statiques

```php
'blog-filter' => [
    'mode' => 'filter',
    'fields' => [
        'category' => [
            'input' => 'select',
            'label' => 'Catégorie',
            'options' => [
                ['label' => 'Toutes', 'value' => ''],
                ['label' => 'Tech', 'value' => 'tech'],
                ['label' => 'Design', 'value' => 'design'],
            ],
        ],
    ],
],
```

### Options depuis des pages Panel

Arborescence :

```
blog/categories/tech
blog/categories/design
```

```php
'category' => [
    'input' => 'select',
    'label' => 'Catégorie',
    'options' => [['label' => 'Toutes', 'value' => '']],
    'optionsFrom' => [
        'type' => 'pages',
        'parent' => 'blog/categories',
    ],
],
```

### Tags via callable

`**site/helpers/forms.php` :**

```php
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
        $options[] = ['label' => ucfirst($tag), 'value' => $tag];
    }

    return $options;
}
```

```php
'tag' => [
    'input' => 'select',
    'label' => 'Tag',
    'optionsFrom' => fn (): array => blogTagOptions(page()),
],
```

### Config + template complets

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
        'tag' => [
            'input' => 'select',
            'label' => 'Tag',
            'optionsFrom' => fn (): array => blogTagOptions(page()),
        ],
        'q' => [
            'input' => 'input',
            'label' => 'Recherche',
            'type' => 'search',
        ],
    ],
],
```

```php
<?php snippet('form-filter', ['formKey' => 'blog-filter']) ?>

<?php
$articles = $page->children()->listed();

if ($cat = get('category')) {
    $articles = $articles->filterBy('category', $cat);
}
if ($tag = get('tag')) {
    $articles = $articles->filterBy('tags', $tag, ',');
}
if ($q = get('q')) {
    $articles = $articles->search($q);
}
?>

<ul>
    <?php foreach ($articles as $article): ?>
        <li><?= $article->title()->esc() ?></li>
    <?php endforeach ?>
</ul>
```

### Déroulement

1. L'utilisateur choisit « Tech » → clic **Filtrer**
2. Navigateur : `/blog?category=tech`
3. `form-filter` pré-remplit le select
4. Le template filtre `$articles`

Pas de CSRF, pas de honeypot, pas de honeytime, pas de route POST. En mode GET, Kirby ne cache pas les URLs avec query string — voir [tech.md](tech.md#cache).

---

## Options depuis un controller

Quand les options dépendent du **contexte de la page** (controller disponible à l'affichage, pas à la soumission POST).

### Fichiers


| Fichier                     | Rôle                                              |
| --------------------------- | ------------------------------------------------- |
| `site/helpers/forms.php`    | Logique partagée                                  |
| `site/controllers/blog.php` | Overrides à l'affichage                           |
| `site/templates/blog.php`   | Passe `$formOverrides` au snippet                 |
| `site/config/config.php`    | Hook pour la soumission POST (si email dynamique) |


### Helper

```php
use Kirby\Cms\Page;

function blogFilterFields(Page $page): array
{
    $categories = $page->find('categories')?->children()->listed() ?? new \Kirby\Cms\Pages();

    return [
        'category' => [
            'options' => array_merge(
                [['label' => 'Toutes', 'value' => '']],
                $categories->map(fn ($cat) => [
                    'label' => $cat->title()->value(),
                    'value' => $cat->slug(),
                ])->values()
            ),
        ],
    ];
}
```

### Controller

```php
return function ($page) {
    return [
        'formOverrides' => [
            'fields' => blogFilterFields($page),
        ],
    ];
};
```

### Template

```php
<?php snippet('form-filter', [
    'formKey' => 'blog-filter',
    'overrides' => $formOverrides,
]) ?>
```

### Hook (même logique à la soumission ou pour réappliquer les options)

```php
'hooks' => [
    'repliq.form.config' => function (array $config, string $formKey, string $context): array {
        if ($formKey === 'blog-filter' && $page = page()) {
            $config['fields'] = array_replace_recursive(
                $config['fields'],
                blogFilterFields($page)
            );
        }
        if ($context === 'submit' && $formKey === 'contact') {
            $config['email']['to'] = contactEmailTo(get('subject') ?? '');
        }
        return $config;
    },
],
```

---

## Plusieurs formulaires sur une page

Une `formClass` (et donc un `formSelector` CSRF) **distincte** par instance :

```php
<?php snippet('form-page', [
    'formKey' => 'contact',
    'formClass' => 'footer-contact',
]) ?>

<?php snippet('form-page', [
    'formKey' => 'newsletter',
    'formClass' => 'footer-newsletter',
]) ?>
```

Classe par défaut : `repliq-form-{formKey}`.

---

## Markup personnalisé

Sans `form-page`, composez avec `repliq_form()` et `form-fields` :

```php
<?php $formData = repliq_form('contact') ?>
<?php if ($formData !== null): ?>
    <?php extract($formData) ?>
    <?php if ($form->success()): ?>
        <p>Merci, votre message a bien été envoyé.</p>
    <?php else: ?>
        <form class="contact-form" action="<?= esc($formAction, 'attr') ?>" method="post">
            <?php snippet('form-errors-summary', [
                'form' => $form,
                'formKey' => $formKey,
                'overrides' => $overrides ?? [],
            ]) ?>
            <?php snippet('form-fields', [
                'formConfig' => $formConfig,
                'form' => $form,
                'formSelector' => '.contact-form',
                'formKey' => $formKey,
                'overrides' => $overrides ?? [],
                'honeytime' => $honeytime ?? null,
            ]) ?>
            <button type="submit">Envoyer</button>
        </form>
    <?php endif ?>
<?php endif ?>
```

Avec overrides :

```php
$formData = repliq_form('blog-filter', $formOverrides);
```

---

## HTMX — soumission AJAX

Soumission sans rechargement de page via [htmx](https://htmx.org/). Le plugin injecte `hx-post`, `hx-target` et `hx-swap` sur le `<form>`, et la route `submit` renvoie un fragment HTML quand l'en-tête `HX-Request: true` est présent.

**Fichiers :** `site/config/config.php`, template.

```php
'baptiste.kirby-form-snippets' => [
    'htmx' => [
        'enabled' => true,
    ],
    'forms' => [
        'contact' => [
            'htmx' => true,
            'fields' => [
                // … même structure que le contact simple
            ],
            'email' => [
                'to' => 'contact@example.com',
                'from' => 'noreply@example.com',
                'subject' => 'Nouveau message',
            ],
        ],
    ],
],
```

```php
<?php snippet('form-page', ['formKey' => 'contact']) ?>
```

**Message de succès en HTMX** — la route `submit` ne connaît pas les paramètres du template. Pour un texte personnalisé après envoi AJAX :

- passer `successMessage` au snippet (ou utiliser le block `contact-form` du Panel), **ou**
- définir `forms.contact.successMessage` / `messages.success` dans `config.php`.

Voir [tech.md — Message de succès et libellé du bouton](tech.md#message-de-succès-et-libellé-du-bouton) et [Session après soumission HTMX](tech.md#session-après-soumission-htmx).

Par défaut, le script htmx.org est chargé une fois par page depuis le CDN (`form-htmx-script`). Les routes CSRF et Honeytime doivent rester hors cache — voir [tech.md](tech.md#cache).

---

## HTMX — script intégré à la main

Pour charger HTMX depuis votre layout (Vite, assets Kirby, etc.) plutôt que depuis le CDN du plugin.

**Fichiers :**


| Fichier | Rôle |
| ------- | ---- |
| `site/config/config.php` | `loadScript => false` |
| `site/snippets/header.php` (ou layout) | Balise `<script>` htmx |
| `assets/js/repliq-form-htmx.js` (ou snippet site) | Rafraîchissement CSRF / Honeytime après swap |


### Config

Désactiver le chargement automatique ; le plugin garde les attributs `hx-*` sur le formulaire :

```php
'baptiste.kirby-form-snippets' => [
    'htmx' => [
        'enabled' => true,
        'loadScript' => false,
    ],
    'forms' => [
        'contact' => [
            'htmx' => true,
            'fields' => [
                // …
            ],
        ],
    ],
],
```

Équivalent par snippet, sans toucher la config globale :

```php
<?php snippet('form-page', [
    'formKey' => 'contact',
    'htmx' => [
        'enabled' => true,
        'loadScript' => false,
    ],
]) ?>
```

### Layout — lib HTMX

Chargez la lib **avant** la fin du `<body>` (version compatible : htmx.org 2.x, voir `index.php` du plugin pour la version pinée) :

```php
<!-- site/snippets/header.php ou footer.php -->
<script src="<?= url('assets/js/htmx.min.js') ?>" defer></script>
<script src="<?= url('assets/js/repliq-form-htmx.js') ?>" defer></script>
```

### JS — rafraîchissement CSRF / Honeytime

En mode HTMX, les snippets `form-csrf-refresh` et `form-honeytime-refresh` ne sont pas rendus. Après chaque swap, les tokens hidden doivent être régénérés (même logique que `snippets/form-htmx-script.php` du plugin).

Exemple minimal dans `assets/js/repliq-form-htmx.js` :

```javascript
(function () {
    var config = {
        csrfField: 'csrf_token',
        honeytimeField: 'uniform-honeytime',
        tokenUrl: '/kirby-form-snippets/csrf-token',
        honeytimeUrl: '/kirby-form-snippets/honeytime-token',
    };

    function refreshContainer(container) {
        if (!container || !container.hasAttribute('data-repliq-form')) {
            return;
        }

        fetch(config.tokenUrl)
            .then(function (response) { return response.ok ? response.json() : null; })
            .then(function (data) {
                if (!data || !data.token) return;
                container.querySelectorAll('[name="' + config.csrfField + '"]').forEach(function (field) {
                    field.value = data.token;
                });
            });

        if (!container.querySelector('[name="' + config.honeytimeField + '"]')) {
            return;
        }

        fetch(config.honeytimeUrl)
            .then(function (response) { return response.ok ? response.json() : null; })
            .then(function (data) {
                if (!data || !data.value) return;
                container.querySelectorAll('[name="' + config.honeytimeField + '"]').forEach(function (field) {
                    field.value = data.value;
                });
            });
    }

    function refreshAll() {
        document.querySelectorAll('[data-repliq-form]').forEach(refreshContainer);
    }

    document.addEventListener('DOMContentLoaded', refreshAll);
    document.body.addEventListener('htmx:afterSwap', function (event) {
        var target = event.detail && event.detail.target;
        if (!target) return;
        if (target.hasAttribute('data-repliq-form')) {
            refreshContainer(target);
            return;
        }
        target.querySelectorAll('[data-repliq-form]').forEach(refreshContainer);
    });
})();
```

Adaptez les URLs si vous avez personnalisé `csrf.route` ou `honeytime.route` dans la config du plugin.

### Variante — script local, chargement auto

Si vous voulez seulement **héberger le fichier** sans gérer le JS de refresh vous-même, laissez `loadScript` à `true` et pointez `script` vers votre asset :

```php
'htmx' => [
    'enabled' => true,
    'loadScript' => true,
    'script' => url('assets/js/htmx.min.js'),
    'scriptIntegrity' => null,
],
```

Le plugin inclut alors `form-htmx-script` (lib + refresh CSRF/Honeytime) depuis votre URL.

---

## Block Panel

Autorisez le block dans votre blueprint de page :

```yaml
fields:
  blocks:
    type: blocks
    fieldsets:
      contact-form: blocks/contact-form
```

Le block expose dans le Panel :

- `formKey` (défaut : `contact`)
- `submitLabel`
- `successMessage`

La **composition des champs** reste dans `config.php` — le block ne permet pas d'ajouter des champs depuis le Panel.

---

## Récapitulatif des flux


| Moment                    | Mécanisme                                                               | Fichier typique                    |
| ------------------------- | ----------------------------------------------------------------------- | ---------------------------------- |
| Affichage formulaire POST | `snippet('form-page')`                                                  | template / block                   |
| Soumission POST           | Route plugin + hook                                                     | `config.php`                       |
| Affichage filtre          | `snippet('form-filter')` + overrides controller                         | template + controller              |
| Clic « Filtrer »          | `get('champ')` dans le template                                         | template                           |
| Options Panel             | `optionsFrom` dans config                                               | `config.php` + blueprint Site/page |
| Email dynamique           | `toFrom` callable ou hook                                               | `config.php`                       |
| Anti-spam Honeytime       | `honeytime.enabled` + `uniform.honeytime.key` + route `honeytime-token` | `config.php`                       |
| Soumission AJAX (HTMX)    | `htmx.enabled` + attributs `hx-*` sur le form                           | `config.php` + template            |
| Script HTMX manuel        | `htmx.loadScript => false` + lib + JS refresh dans le layout            | layout + `assets/js/`              |
| Cache                     | `cache.ignore` sur les routes plugin                                    | `config.php`                       |


Ordre de fusion : `**config.php`** → hook → overrides controller. Le Panel est lu via `optionsFrom` / `toFrom`, pas en remplacement de la config.

## Voir aussi

- [README.md](../README.md) — installation
- [inputs.md](inputs.md) — référence des champs et paramètres
- [tech.md](tech.md) — architecture et routes

