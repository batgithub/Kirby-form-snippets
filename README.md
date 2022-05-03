# Kirby form snippets



- [x] honeypot
- [x] input
- [x] textarea
- [x] info
- [x] notif
- [x] checkbox
- [x] checkbox group
- [x] radio
- [ ] select
- [ ] File
- [ ] number
- [ ] number group
- [ ] password

## Dependances
[Kirby uniform](https://github.com/mzur/kirby-uniform)<br> 
```bash
composer require mzur/kirby-uniform:^4.0
```
## install as submodule
Install repo as git submodule<br>
```git submodule add -f https://github.com/batgithub/Kirby-form-snippets.git composer/plugins/kirby-form-snippets```


## How to use in Templates

### Form wrapper
```php
 <form method="post" action="<?= $page->url() ?>">
        <?php if(empty($form->error('website')) == false): ?>
            <?php snippet('form-card', [
                'text' => implode('<br>', $form->error('website')),
                'class' => 'error'
            ]) ?>
        <?php endif; ?>
        <?php snippet('form-honeypot', ['name' => 'website']) ?>
        <?php echo csrf_field(); ?>

        // <!-- Les snippets ici -->

      <input type="submit"  name="submit"  value="Envoyer">
 </form>
```

### Input

```php
<?php snippet('form-input', [
    'id'          => '',
    'label'       => '',
]) ?>
```

#### Options disponibles
| Name | obligatoire | default | description 
| --- | --- | --- | ---
| id | ✅   |     |
| label | ✅ |
| info |  | | texte affiché pour donner plus d'informations
| placeholder |  | `Votre réponse` |
| type | | `text` | [D'autres options dispo](https://www.w3schools.com/html/html_form_input_types.asp) comme : `tel`, `email`, `number`, `url`
| required |  | `false`  | Taille minimum de la réponse en caractères
| pattern |  |   | [Pattern de validation](https://www.w3schools.com/tags/att_input_pattern.asp)
| maxlength |  |   | Taille maximum de la réponse en caractères
| minlength |  |   | Taille minimum de la réponse en caractères


### Textarea 
```php
<?php snippet('form-textarea', [
    'id'          => 'message',
    'label'       => 'Message',
]) ?>
```
#### Options disponibles
| Name | obligatoire | default | description 
| --- | --- | --- | ---
| id | ✅   |     |
| label | ✅ |
| info |  | | texte affiché pour donner plus d'informations
| required |  | `false`  | Taille minimum de la réponse en caractères
| rows |  | | hauteur du champ par défaut 
| maxlength |  |   | Taille maximum de la réponse en caractères
| minlength |  |   | Taille minimum de la réponse en caractères


### Checkbox

```php
<?php snippet('form-checkbox', [
    'name'       => 'cgu',
    'label'       => 'en cochant vous acceptez les cgu',
    'value'       => 'cgu acceptées',
]) ?>
```
option `'checked' => true` possible pour sélectionner par défaut

#### Options disponibles
| Name | obligatoire | default | description 
| --- | --- | --- | ---
| name | ✅   |    | utilisé pour lier le label et la case
| label | ✅ | Texte affiché, on peut y mettre du HTML 
| value |  | | valeur envoyé, elle est utilisé pour générer un id (via kebab case)
| checked |  |  | `'checked' => true` pour cocher la case par défaut




### Checkbox Group
```php
  <?php snippet('form-checkbox-group', [
    'legend'       => 'Qu\'est ce que vous voulez ?',
    'name'       => 'Checkbox',
    'required'    => false,
    'options'     => [
        [  
            'label'       => 'Checkbox 2 <a href="#">zoeifjzoefijzoeifj</a>',
            'value'       => 'Checkbox ce soir'
        ]
    ]
]) ?>
```
option `'checked' => true` possible pour sélectionner par défaut

#### Options disponibles
| Name | obligatoire | default | description 
| --- | --- | --- | ---
| legend | ✅   |    | affiché pour décrire la suite de case
| name | ✅ | | identifiant auquel seront reliée les cases
| options | ✅ | | tableau des cases. `label`,`value`et `checked` 





### Radio Group
```php
  <?php snippet('form-radio-group', [
    'legend'       => 'Est-ce que vous êtes humain ?',
    'name'       => 'humain',
    'required'    => false,
    'options'     => [
        [  
            'label'       => 'Oui je suis humain',
            'value'       => 'oui'
        ],
        [  
            'label'       => 'non je suis un robot',
            'value'       => 'non'
        ]
    ]
]) ?>
```
#### Options disponibles
| Name | obligatoire | default | description 
| --- | --- | --- | ---
| legend | ✅   |    | affiché pour décrire la suite de case
| name | ✅ | | identifiant auquel seront reliée les cases
| options | ✅ | | tableau des cases. `label`,`value`et `checked` seulement sur une seule option 


