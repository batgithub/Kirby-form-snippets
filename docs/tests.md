# Tests automatisés

Suite PHPUnit pour éviter les régressions sur la logique métier, les guards anti-spam et le pipeline de soumission. Approche [monolithique](https://getkirby.com/docs/cookbook/plugins/monolithic-plugin-setup) : Kirby et Uniform sont installés en dev dans le même dépôt que le plugin.

## Prérequis

- PHP 8.2+
- [Composer](https://getcomposer.org/)

## Installation

```bash
composer install
```

`getkirby/cms` (^5.0) est déclaré dans `require` : il fixe le plancher de compatibilité Kirby 5 et est installé localement dans `kirby/` pour la suite monolithique. Les dépendances de développement (`phpunit/phpunit`, `mzur/kirby-uniform`) ne sont pas requises en production ; `mzur/kirby-uniform` s'installe dans `site/plugins/`.

## Lancer les tests

```bash
composer test
```

Avec rapport de couverture texte :

```bash
composer test:coverage
```

## CI

GitHub Actions exécute `composer test` sur chaque push et pull request vers `main` / `master` (PHP 8.2). Voir `.github/workflows/tests.yml`.

## Structure

```
tests/
├── bootstrap.php              # Instance Kirby + contenu de test
├── content/                   # Pages minimales (home, site)
└── suites/
    ├── TestCase.php           # Helpers partagés (requête simulée, pipeline submit)
    ├── RepliqFormTest.php     # Unités : règles, config, guards, email
    ├── PluginIntegrationTest.php  # Plugin, routes GET, hook config
    └── SubmissionIntegrationTest.php  # POST simulé + CSRF + soumission complète

site/
├── config/config.php          # Formulaires de test (contact, honeytime, filter, options)
└── plugins/
    └── baptiste-kirby-form-snippets/index.php  # Charge le plugin racine

phpunit.xml.dist
```

Les dossiers `kirby/`, `vendor/` et les plugins Composer (`site/plugins/kirby-uniform`, etc.) sont générés localement et ignorés par Git.

## Ce qui est testé

### `RepliqFormTest` — logique métier

| Domaine | Exemples |
|---------|----------|
| Options select / radio | `optionValue()`, slug automatique |
| Honeypot | résolution du nom de champ (`name` custom ou id) |
| Config | `buildConfig()`, overrides, formulaire inconnu |
| Modes | `filter` sans règles Uniform |
| Règles Uniform | champs requis, email, select, checkbox-group, radio-group |
| Honeytime | options guard, génération de valeur chiffrée |
| Email | thème, destinataire par défaut via `toFrom` |
| Validation | `Jevets\Kirby\Validator` avec les règles du plugin |
| Guards | honeypot rempli, honeytime expiré |

### `PluginIntegrationTest` — Kirby + routes

- Enregistrement du plugin et snippets
- Route GET `kirby-form-snippets/csrf-token` → JSON `{ "token": "…" }`
- Route GET `kirby-form-snippets/honeytime-token` → JSON `{ "value": "…" }`
- Hook `repliq.form.config` (arguments nommés `config`, `formKey`, `context`)
- État GET du mode filtre (`RepliqFilterState`)

### `SubmissionIntegrationTest` — soumission POST

Simule une soumission réelle : requête POST injectée dans Kirby, token CSRF de session, pipeline identique à `RepliqForm::handleSubmit()` (sans redirection HTTP).

| Scénario | Résultat attendu |
|----------|------------------|
| POST valide + CSRF | `$form->success()` true, email capturé |
| CSRF absent ou invalide | `TokenMismatchException` |
| Email invalide | erreurs sur le champ, pas d'email |
| Honeypot rempli | échec spam, pas d'email |
| Honeytime immédiat | échec spam |
| Honeytime après délai | succès |
| Token obtenu via route CSRF | utilisable pour soumettre |

Les emails ne partent pas réellement : `Email::$debug = true` les stocke dans `Email::$emails`.

## Helpers de test (`TestCase`)

### `simulateRequest($method, $body, $path)`

Remplace la requête Kirby par une instance `Kirby\Http\Request` explicite. À utiliser à la place de `$_POST` seul, car Kirby met en cache le body après la première lecture.

```php
$this->simulateRequest('POST', [
    'name' => 'Jane',
    'email' => 'jane@example.com',
], '/kirby-form-snippets/submit/contact');
```

### `postWithCsrf($fields)`

Ajoute `csrf_token` avec un token valide de session :

```php
$payload = $this->postWithCsrf([
    'name' => 'Jane Doe',
    'email' => 'jane@example.com',
    'message' => 'Bonjour',
    'website' => '',
]);
```

### `runSubmitPipeline($formKey, $fields)`

Exécute validation → guards (honeypot, honeytime) → `emailAction()`, avec `withoutRedirect()` et `withoutFlashing()`. Retourne l'instance `Uniform\Form` pour assertions :

```php
$form = $this->runSubmitPipeline('contact', $this->postWithCsrf([/* champs */]));

$this->assertTrue($form->success());
$this->assertCount(1, Email::$emails);
```

### `honeytimeFieldPayload($config)`

Génère un champ honeytime avec timestamp « expiré » (soumission autorisée) :

```php
$config = RepliqForm::buildConfig('honeytime');
$fields = array_merge(
    $this->postWithCsrf(['email' => 'jane@example.com']),
    $this->honeytimeFieldPayload($config)
);
$form = $this->runSubmitPipeline('honeytime', $fields);
```

## Formulaires de test

Déclarés dans `site/config/config.php` (usage dev uniquement, exclu des releases ZIP via `.gitattributes`) :

| `formKey` | Usage |
|-----------|--------|
| `contact` | Formulaire complet : input, textarea, honeypot, email |
| `honeytime` | Guard honeytime activé |
| `filter` | Mode filtre GET |
| `options` | Select, checkbox-group, radio-group, checkbox |

Clé honeytime de test : `uniform.honeytime.key` dans la même config (valeur fixe, identique aux tests Uniform).

## Ajouter un test

1. Créer une méthode `test…()` dans la suite appropriée (ou un nouveau fichier dans `tests/suites/`).
2. Étendre `Repliq\Tests\TestCase`.
3. Pour une nouvelle config de formulaire, l'ajouter dans `site/config/config.php`.
4. Pour une soumission POST, préférer `runSubmitPipeline()` + `postWithCsrf()`.
5. Lancer `composer test` avant de committer.

Exemple — vérifier qu'un champ requis est bien rejeté :

```php
public function testContactSubmitRequiresName(): void
{
    $form = $this->runSubmitPipeline('contact', $this->postWithCsrf([
        'name' => '',
        'email' => 'jane@example.com',
        'message' => 'Bonjour',
        'website' => '',
    ]));

    $this->assertFalse($form->success());
    $this->assertNotEmpty($form->errors('name'));
}
```

## Limites connues

| Sujet | Détail |
|-------|--------|
| Redirection HTTP | `RepliqForm::handleSubmit()` appelle `die()` via Uniform après succès. Les tests utilisent `withoutRedirect()` sur le pipeline, pas la route POST directement. |
| Cache body Kirby | Toujours passer par `simulateRequest()` après avoir défini les données POST. |
| Emails | Mode debug Kirby uniquement ; pas de test SMTP réel. |
| Panel / snippets HTML | Non couverts ; les tests ciblent PHP et Uniform. |

## Releases

Les fichiers de test, la config site de dev, `kirby/` et `vendor/` sont exclus du ZIP de release (`.gitattributes` `export-ignore`). Seul le plugin (classes, snippets, `index.php`, etc.) est distribué.
