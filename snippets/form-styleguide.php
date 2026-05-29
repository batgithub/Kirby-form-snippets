<?php

use repliq\RepliqPreviewState;

/**
 * Styleguide : rend chaque composant de formulaire dans tous ses états visuels
 * (default, filled, error, filled+error, required) en réutilisant les vrais
 * snippets de champs. Sert de page de référence pour calibrer le CSS.
 *
 * Paramètres optionnels :
 * - $formClass  : classe du <form> (scope CSS). Défaut : repliq-form-styleguide
 * - $states     : liste des états à décliner. Défaut : les 5 états ci-dessous
 * - $showLabels : afficher les libellés d'état au-dessus de chaque variante. Défaut : true
 */

$formClass = $formClass ?? 'repliq-form-styleguide';
$showLabels = $showLabels ?? true;
$states = $states ?? ['default', 'filled', 'error', 'filled_error', 'required'];

$stateLabels = [
    'default' => 'Default',
    'filled' => 'Filled',
    'error' => 'Error',
    'filled_error' => 'Filled + Error',
    'required' => 'Required',
];

// Jeux d'options réutilisés par les champs à choix.
$selectOptions = [
    ['label' => 'Demande générale', 'value' => 'general'],
    ['label' => 'Support technique', 'value' => 'support'],
    ['label' => 'Commercial', 'value' => 'commercial'],
];
$checkboxGroupOptions = [
    ['label' => 'Email', 'value' => 'email'],
    ['label' => 'Téléphone', 'value' => 'phone'],
    ['label' => 'Courrier', 'value' => 'mail'],
];
$radioGroupOptions = [
    ['label' => 'Madame', 'value' => 'mme'],
    ['label' => 'Monsieur', 'value' => 'mr'],
];

/**
 * Déclaration des composants à décliner.
 * - snippet : suffixe du snippet (form-{snippet})
 * - base    : paramètres communs à tous les états
 * - filled  : valeur renvoyée par old() pour les états filled / filled_error
 * - message : message d'erreur pour les états error / filled_error
 */
$components = [
    [
        'snippet' => 'input',
        'title' => 'Champ texte',
        'base' => ['label' => 'Nom'],
        'filled' => 'Jean Dupont',
        'message' => 'Merci d\'entrer une réponse',
    ],
    [
        'snippet' => 'input',
        'title' => 'Champ email',
        'base' => [
            'label' => 'Email',
            'type' => 'email',
            'info' => 'Nous ne partagerons jamais votre adresse.',
        ],
        'filled' => 'jean@exemple.com',
        'filledError' => 'jean(at)exemple',
        'message' => 'Veuillez entrer un format d\'email valide.',
    ],
    [
        'snippet' => 'input',
        'title' => 'Champ téléphone',
        'base' => ['label' => 'Téléphone', 'type' => 'tel'],
        'filled' => '06 12 34 56 78',
        'message' => 'Veuillez entrer un format de téléphone valide.',
    ],
    [
        'snippet' => 'textarea',
        'title' => 'Zone de texte',
        'base' => ['label' => 'Message'],
        'filled' => 'Bonjour, je souhaite obtenir des informations…',
        'message' => 'Merci d\'entrer une réponse',
    ],
    [
        'snippet' => 'select',
        'title' => 'Liste déroulante',
        'base' => ['label' => 'Sujet', 'options' => $selectOptions],
        'filled' => 'support',
        'message' => 'Merci de sélectionner un élément',
    ],
    [
        'snippet' => 'select',
        'title' => 'Liste déroulante multiple',
        'base' => ['label' => 'Services', 'multiselect' => true, 'options' => $selectOptions],
        'filled' => ['support', 'commercial'],
        'message' => 'Merci de sélectionner un élément',
    ],
    [
        'snippet' => 'checkbox',
        'title' => 'Case à cocher',
        'base' => ['label' => 'J\'accepte les conditions générales', 'value' => 'accept'],
        'filled' => 'accept',
        'message' => 'Merci de cocher cette case',
    ],
    [
        'snippet' => 'checkbox-group',
        'title' => 'Groupe de cases',
        'base' => ['label' => 'Centres d\'intérêt', 'options' => $checkboxGroupOptions],
        'filled' => ['email', 'phone'],
        'message' => 'Merci de sélectionner au moins un élément',
    ],
    [
        'snippet' => 'radio-group',
        'title' => 'Groupe de boutons radio',
        'base' => ['label' => 'Civilité', 'options' => $radioGroupOptions],
        'filled' => 'mme',
        'message' => 'Merci de sélectionner une option',
    ],
];

// Première passe : construire les variantes et alimenter l'état factice.
$values = [];
$errors = [];
$sections = [];

foreach ($components as $index => $component) {
    $slug = $component['snippet'] . '-' . $index;
    $variants = [];

    foreach ($states as $state) {
        $id = 'sg-' . $slug . '-' . $state;
        $params = array_merge($component['base'], ['id' => $id]);

        $isFilled = in_array($state, ['filled', 'filled_error'], true);
        $isError = in_array($state, ['error', 'filled_error'], true);

        if ($isFilled) {
            $values[$id] = ($state === 'filled_error' && isset($component['filledError']))
                ? $component['filledError']
                : $component['filled'];
        }

        if ($isError) {
            $errors[$id] = [$component['message']];
        }

        if ($state === 'required') {
            $params['required'] = true;
        }

        $variants[] = [
            'state' => $state,
            'snippet' => 'form-' . $component['snippet'],
            'params' => $params,
        ];
    }

    $sections[] = [
        'title' => $component['title'],
        'anchor' => 'sg-section-' . $slug,
        'variants' => $variants,
    ];
}

$previewForm = new RepliqPreviewState($values, $errors);

// Récap d'erreurs : pointe vers les champs en état d'erreur réellement rendus.
$errorsSummaryItems = [];

foreach ($sections as $section) {
    foreach ($section['variants'] as $variant) {
        if ($variant['state'] !== 'error') {
            continue;
        }

        $fieldId = $variant['params']['id'];
        $errorsSummaryItems[] = [
            'id' => $fieldId,
            'label' => $variant['params']['label'] ?? $fieldId,
            'messages' => $errors[$fieldId] ?? [],
        ];
    }
}
?>

<div class="repliq-form-container repliq-form-styleguide-container">

    <section class="styleguide-section" aria-labelledby="sg-section-success">
        <?php snippet('form-section-title', [
            'id' => 'sg-section-success',
            'title' => 'État de succès (post-envoi)',
            'tag' => 'h2',
        ]) ?>
        <p class="styleguide-note">
            En production, ce message remplace le <code>&lt;form&gt;</code> après un envoi réussi.
        </p>
        <p class="form-success">Merci, votre message a bien été envoyé.</p>
    </section>

    <form class="<?= esc($formClass, 'attr') ?>" action="#" method="post" onsubmit="return false;" novalidate>

        <section class="styleguide-section" aria-labelledby="sg-section-errors-summary">
            <?php snippet('form-section-title', [
                'id' => 'sg-section-errors-summary',
                'title' => 'Récapitulatif des erreurs (post-envoi)',
                'tag' => 'h2',
            ]) ?>
            <?php snippet('form-errors-summary', [
                'form' => $previewForm,
                'formKey' => 'styleguide',
                'title' => 'Le formulaire contient des erreurs',
                'items' => $errorsSummaryItems,
            ]) ?>
        </section>

        <?php foreach ($sections as $section): ?>
            <section class="styleguide-section" aria-labelledby="<?= esc($section['anchor'], 'attr') ?>">
                <?php snippet('form-section-title', [
                    'id' => $section['anchor'],
                    'title' => $section['title'],
                    'tag' => 'h2',
                ]) ?>

                <?php foreach ($section['variants'] as $variant): ?>
                    <div class="styleguide-variant" data-state="<?= esc($variant['state'], 'attr') ?>">
                        <?php if ($showLabels): ?>
                            <p class="styleguide-state"><?= html($stateLabels[$variant['state']] ?? $variant['state']) ?></p>
                        <?php endif ?>
                        <?php snippet($variant['snippet'], array_merge($variant['params'], ['form' => $previewForm])) ?>
                    </div>
                <?php endforeach ?>
            </section>
        <?php endforeach ?>

        <section class="styleguide-section" aria-labelledby="sg-section-decorative">
            <?php snippet('form-section-title', [
                'id' => 'sg-section-decorative',
                'title' => 'Composants décoratifs',
                'tag' => 'h2',
            ]) ?>

            <div class="styleguide-variant" data-component="label">
                <?php if ($showLabels): ?><p class="styleguide-state">label</p><?php endif ?>
                <?php snippet('form-label', [
                    'id' => 'sg-deco-label',
                    'label_text' => 'Libellé seul',
                    'required' => true,
                ]) ?>
            </div>

            <div class="styleguide-variant" data-component="info">
                <?php if ($showLabels): ?><p class="styleguide-state">info (label-desc)</p><?php endif ?>
                <?php snippet('form-info', ['text' => 'Texte d\'aide affiché sous un label.']) ?>
            </div>

            <div class="styleguide-variant" data-component="card">
                <?php if ($showLabels): ?><p class="styleguide-state">card</p><?php endif ?>
                <?php snippet('form-card', ['text' => 'Encadré informatif. Réponse sous 48 h.']) ?>
            </div>

            <div class="styleguide-variant" data-component="line">
                <?php if ($showLabels): ?><p class="styleguide-state">line</p><?php endif ?>
                <?php snippet('form-line', ['id' => 'sg-deco-line']) ?>
            </div>
        </section>

        <button type="submit">Envoyer</button>
    </form>
</div>
