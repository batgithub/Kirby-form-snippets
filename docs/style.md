# Personnalisation visuelle

Guide pour styler les formulaires du plugin. Le plugin **ne fournit pas de CSS** : il génère un markup sémantique avec des **classes stables** que vous ciblez depuis votre thème Kirby.

| Document | Usage |
|----------|-------|
| [README.md](../README.md) | Installation et démarrage rapide |
| [inputs.md](inputs.md) | Référence des champs et paramètres |
| [examples.md](examples.md) | Markup personnalisé, plusieurs formulaires |
| **[style.md](style.md)** | Ce fichier — classes, états, exemples CSS |

**Sommaire**

- [Principe](#principe)
- [Cibler un formulaire](#cibler-un-formulaire)
- [Arborescence HTML](#arborescence-html)
- [Référence des classes](#référence-des-classes)
- [États et accessibilité](#états-et-accessibilité)
- [Variables CSS recommandées](#variables-css-recommandées)
- [Feuille de style de départ](#feuille-de-style-de-départ)
- [Cibler un champ précis](#cibler-un-champ-précis)
- [Champs décoratifs](#champs-décoratifs)
- [Aller plus loin](#aller-plus-loin)

---

## Principe

1. **Classes = points d'accroche** — pas de styles inline (sauf le honeypot, masqué pour l'accessibilité).
2. **Une classe par formulaire** — via `formClass` pour isoler le scope CSS.
3. **États explicites** — la classe `.error` est ajoutée sur le conteneur du champ ou du groupe en cas d'erreur de validation.
4. **Pas de framework imposé** — Tailwind, SCSS, CSS natif : tout fonctionne tant que vous ciblez les bonnes classes.

---

## Cibler un formulaire

Par défaut, la balise `<form>` reçoit la classe `repliq-form-{formKey}` :

```php
<?php snippet('form-page', ['formKey' => 'contact']) ?>
// → <form class="repliq-form-contact" …>
```

Pour un scope CSS dédié, passez `formClass` :

```php
<?php snippet('form-page', [
    'formKey' => 'contact',
    'formClass' => 'contact-form',
]) ?>
```

Le block Panel utilise `repliq-form block-form-{blockId}` — pratique pour cibler un formulaire inséré via les blocks.

En markup personnalisé, la classe est libre ; pensez à aligner `formSelector` pour le rafraîchissement CSRF :

```php
<form class="contact-form" …>
    <?php snippet('form-fields', [
        'formConfig' => $formConfig,
        'form' => $form,
        'formSelector' => '.contact-form',
    ]) ?>
</form>
```

Voir [examples.md — Markup personnalisé](examples.md#markup-personnalisé).

---

## Arborescence HTML

Structure type d'un formulaire en mode submit (`form-page`) :

```html
<!-- Succès -->
<p class="form-success">Merci, votre message a bien été envoyé.</p>

<!-- Formulaire -->
<form class="repliq-form-contact" action="…" method="post">
    <!-- CSRF (champ hidden) -->
    <!-- Honeytime (champ hidden, si activé — pas de style requis) -->

    <!-- Champ texte -->
    <div class="field">
        <label for="name">Nom <abbr title="requis">*</abbr></label>
        <div class="label-desc">Texte d'aide optionnel</div>
        <input type="text" id="name" name="name" …>
        <span id="name-error" class="notif error" role="alert">…</span>
    </div>

    <!-- Checkbox seule -->
    <div class="field checkbox">
        <div class="wrap-input">
            <input class="cursor-pointer" type="checkbox" …>
            <label for="…"><p class="cursor-pointer">…</p></label>
        </div>
    </div>

    <!-- Groupe radio / checkbox -->
    <div class="field-group">
        <fieldset>
            <legend>… <abbr title="requis">*</abbr></legend>
            <div class="field radio">…</div>
            <div class="field checkbox">…</div>
        </fieldset>
        <span id="…-error" class="notif error" role="alert">…</span>
    </div>

    <!-- Honeypot (masqué) -->
    <div class="honeypot" aria-hidden="true">…</div>

    <button type="submit">Envoyer</button>
</form>
```

---

## Référence des classes

| Classe | Élément | Rôle |
|--------|---------|------|
| `repliq-form-{formKey}` | `<form>` | Classe par défaut ; scope principal |
| `form-success` | `<p>` | Message après envoi réussi |
| `field` | `<div>` | Conteneur d'un champ (input, textarea, select, checkbox/radio seul, section-title) |
| `field.error` | `<div>` | Champ en erreur de validation |
| `field.checkbox` | `<div>` | Variante checkbox |
| `field.radio` | `<div>` | Variante radio |
| `field-group` | `<div>` | Conteneur d'un `<fieldset>` (groupes checkbox/radio) |
| `field-group.error` | `<div>` | Groupe en erreur |
| `wrap-input` | `<div>` | Flex interne checkbox/radio (input + label) |
| `cursor-pointer` | `<input>`, `<p>` | Curseur pointeur sur les contrôles cliquables |
| `label-desc` | `<div>` | Texte d'aide (`info` dans la config) |
| `notif.error` | `<span>` | Message d'erreur (`role="alert"`) |
| `form-section-title` | `<a>` | Titre de section cliquable (ancre) |
| `form-card` | `<div>` | Encadré informatif |
| `honeypot` | `<div>` | Piège anti-spam — **ne pas afficher** |

Classes additionnelles configurables (champs décoratifs) :

| Paramètre config | Appliqué sur | Exemple |
|------------------|--------------|---------|
| `class` sur `line` | `<hr>` | `form-divider` |
| `class` sur `card` | `<div class="form-card …">` | `form-info-box` |
| `tag` sur `section-title` | `<a class="form-section-title h3 …">` | `h2`, `h3`, … |

Le bouton submit n'a **pas** de classe dédiée : stylisez `form button[type="submit"]` ou ajoutez votre propre markup via [examples.md](examples.md#markup-personnalisé).

---

## États et accessibilité

| Situation | Signaux HTML / CSS |
|-----------|-------------------|
| Champ invalide | `.field.error` ou `.field-group.error` + `aria-invalid="true"` sur l'input / le `<fieldset>` |
| Message d'erreur | `#id-error.notif.error` lié via `aria-describedby` |
| Champ requis | attribut `required` + `<abbr title="requis">*</abbr>` dans le label ou la `<legend>` |
| Succès | `.form-success` à la place du `<form>` |

**Bonnes pratiques CSS :**

- Ne pas vous fier à la couleur seule pour les erreurs : combinez bordure, icône ou texte explicite.
- Conserver un contraste ≥ 4,5:1 pour le texte et les messages d'erreur.
- `:focus-visible` sur inputs, select, textarea et bouton submit.
- La classe `.honeypot` doit rester invisible (`display: none` ou équivalent sr-only) — ne jamais la rendre visible.
- Honeytime : champ hidden injecté par le plugin — aucune règle CSS nécessaire.

---

## Variables CSS recommandées

Déclarez des custom properties sur le conteneur du formulaire pour centraliser les réglages :

```css
.contact-form {
    --form-gap: 1.25rem;
    --form-label-weight: 600;
    --form-input-padding: 0.625rem 0.875rem;
    --form-input-border: 1px solid #ccc;
    --form-input-radius: 0.375rem;
    --form-input-focus: #0066cc;
    --form-error-color: #b00020;
    --form-success-color: #1b5e20;
}
```

Réutilisez-les dans tous vos sélecteurs `.contact-form …` pour ajuster l'ensemble du formulaire en un seul endroit.

---

## Feuille de style de départ

Exemple minimal à placer dans `assets/css/forms.css` (ou équivalent) :

```css
/* Scope : adapter la classe à votre formClass */
.repliq-form-contact {
    display: flex;
    flex-direction: column;
    gap: var(--form-gap, 1.25rem);
    max-width: 40rem;
}

.repliq-form-contact .field,
.repliq-form-contact .field-group {
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
}

.repliq-form-contact label,
.repliq-form-contact legend {
    font-weight: var(--form-label-weight, 600);
}

.repliq-form-contact .label-desc {
    font-size: 0.875rem;
    opacity: 0.85;
}

.repliq-form-contact input:not([type="checkbox"]):not([type="radio"]),
.repliq-form-contact textarea,
.repliq-form-contact select {
    width: 100%;
    padding: var(--form-input-padding, 0.625rem 0.875rem);
    border: var(--form-input-border, 1px solid #ccc);
    border-radius: var(--form-input-radius, 0.375rem);
}

.repliq-form-contact input:focus-visible,
.repliq-form-contact textarea:focus-visible,
.repliq-form-contact select:focus-visible,
.repliq-form-contact button[type="submit"]:focus-visible {
    outline: 2px solid var(--form-input-focus, #0066cc);
    outline-offset: 2px;
}

/* Erreurs */
.repliq-form-contact .field.error input,
.repliq-form-contact .field.error textarea,
.repliq-form-contact .field.error select,
.repliq-form-contact .field-group.error fieldset {
    border-color: var(--form-error-color, #b00020);
}

.repliq-form-contact .notif.error {
    color: var(--form-error-color, #b00020);
    font-size: 0.875rem;
}

/* Checkbox / radio */
.repliq-form-contact .wrap-input {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
}

.repliq-form-contact .wrap-input label p {
    margin: 0;
}

/* Bouton */
.repliq-form-contact button[type="submit"] {
    align-self: flex-start;
    padding: 0.75rem 1.5rem;
    cursor: pointer;
}

/* Succès */
.form-success {
    color: var(--form-success-color, #1b5e20);
    padding: 1rem;
    border: 1px solid currentColor;
    border-radius: var(--form-input-radius, 0.375rem);
}

/* Honeypot — toujours masqué */
.honeypot {
    position: absolute;
    left: -9999px;
    width: 1px;
    height: 1px;
    overflow: hidden;
}
```

---

## Cibler un champ précis

Chaque clé dans `fields` devient l'`id` HTML du champ. Ciblez-la directement :

```css
/* Le champ email du formulaire contact */
.repliq-form-contact #email {
    font-family: inherit;
}

/* Textarea message plus haute */
.repliq-form-contact #message {
    min-height: 8rem;
}
```

Pour les groupes, les options reçoivent un id `{fieldKey}-{value}` :

```css
.repliq-form-contact #subject-urgent { /* radio « urgent » du champ subject */ }
```

---

## Champs décoratifs

### Titre de section (`section-title`)

```css
.form-section-title {
    display: block;
    margin-block: 2rem 0.75rem;
    text-decoration: none;
    color: inherit;
}

.form-section-title h2,
.form-section-title h3 {
    margin: 0;
}
```

Le paramètre `tag` ajoute une classe (`h2`, `h3`, …) sur le lien :

```css
.form-section-title.h3 { font-size: 1.125rem; }
```

### Carte info (`card`)

```php
'info' => [
    'input' => 'card',
    'text' => 'Réponse sous 48 h.',
    'class' => 'form-info-box',
],
```

```css
.form-card.form-info-box {
    padding: 1rem;
    background: #f5f5f5;
    border-radius: 0.375rem;
}
```

### Séparateur (`line`)

```php
'sep' => ['input' => 'line', 'class' => 'form-divider'],
```

```css
.form-divider {
    border: none;
    border-top: 1px solid #ddd;
    margin-block: 1.5rem;
}
```

---

## Aller plus loin

### Plusieurs formulaires sur une page

Attribuez une `formClass` distincte à chaque instance pour éviter les conflits CSS et le CSRF :

```php
snippet('form-page', ['formKey' => 'contact', 'formClass' => 'footer-contact']);
snippet('form-page', ['formKey' => 'newsletter', 'formClass' => 'footer-newsletter']);
```

Voir [examples.md — Plusieurs formulaires](examples.md#plusieurs-formulaires-sur-une-page).

### Surcharger les snippets Kirby

Pour modifier le HTML (ajouter des classes par champ, changer la structure), copiez un snippet du plugin dans `site/snippets/` :

```
site/snippets/form-input.php      → remplace form-input du plugin
site/snippets/form-label.php    → remplace form-label du plugin
```

Kirby résout d'abord `site/snippets/`, puis le plugin. Utile si vous avez besoin d'une classe `field--email` sur certains types sans toucher au plugin.

### Mode filtre

Le markup des champs est identique ; seule la balise `<form>` change (`method="get"`, pas de CSRF). Les mêmes règles CSS s'appliquent avec la `formClass` du snippet `form-filter`.

---

## Voir aussi

- [examples.md](examples.md) — markup personnalisé, blocks Panel
- [inputs.md](inputs.md) — paramètres `info`, `class` (décoratifs), `tag`
- [tech.md](tech.md) — architecture des snippets
