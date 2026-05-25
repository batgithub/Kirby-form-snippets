# Kirby form snippets

## Présentation générale

Ce plugin Kirby permet de créer des formulaires plus facilement. Il permet de :

- créer un formulaire à partir d'un tableau
- récupérer les éléments (règles de validation, liste des inputs) grâce à une [classe](classes/form.php)
- envoyer un email retournant la totalité des informations entrées dans le formulaire
- créer des inputs de formulaire grâce aux snippets

## Dépendances

[Kirby Uniform](https://github.com/mzur/kirby-uniform)

```bash
composer require mzur/kirby-uniform:^5.0
```

Ce plugin ne fonctionne pas sans Uniform. La version 5.x est compatible avec Kirby 3.5+, 4 et 5.

## Installation en submodule

```bash
git submodule add -f https://github.com/batgithub/Kirby-form-snippets.git composer/plugins/kirby-form-snippets
```

## Utilisation

### Configuration des champs

Définir un tableau de champs. La clé du tableau devient l'`id` du champ (utilisé pour `name`, règles Uniform, etc.) :

```php
use repliq\RepliqForm;
use Uniform\Form;

$fields = [
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
];

$formConfig = new RepliqForm($fields);
$form = new Form($formConfig->getRules());
```

### Rendu des champs

Le snippet `form-fields` parcourt la configuration et affiche chaque champ (avec `id` et `form` injectés automatiquement) :

```php
<?php snippet('form-fields', [
    'formConfig' => $formConfig,
    'form' => $form,
]) ?>
```

Vous pouvez aussi passer directement le tableau de champs, sans instancier `RepliqForm` une seconde fois pour le rendu :

```php
<?php snippet('form-fields', [
    'fields' => $fields,
    'form' => $form,
]) ?>
```

Pour un contrôle fin du markup (wrapper par champ, grille, etc.), `getInputs()` reste disponible :

```php
<?php foreach ($formConfig->getInputs($form) as $field): ?>
    <div class="form-row"><?= $field ?></div>
<?php endforeach ?>
```

### Options configurables

Dans `site/config/config.php` :

```php
return [
    'baptiste.kirby-form-snippets.placeholder' => 'Votre réponse',
    'baptiste.kirby-form-snippets.maxLength.input' => 1000,
    'baptiste.kirby-form-snippets.messages.required' => 'Merci d\'entrer une réponse',
];
```

### Snippets disponibles

| Snippet | Rôle |
|---------|------|
| `form-fields` | rendu de tous les champs d'une configuration |
| `form-input` | type `input` |
| `form-textarea` | `textarea` |
| `form-select` | `select` |
| `form-checkbox` | `checkbox` |
| `form-checkbox-group` | `checkbox-group` |
| `form-radio-group` | `radio-group` |
| `form-honeypot` | `honeypot` |
| `form-label`, `form-info`, `form-notif`, `form-field-errors` | composition |
| `form-card`, `form-section-title` | mise en page |

Les options de select, checkbox et checkbox-group utilisent `value` si présent, sinon un slug dérivé du `label`.

## Wiki

[Allez voir le wiki](https://github.com/batgithub/Kirby-form-snippets/wiki)
