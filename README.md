# Kirby form snippets



- [x] honeypot
- [x] input
- [x] textarea
- [x] info
- [x] notif
- [ ] checkbox
- [ ] checkbox group
- [ ] radio
- [ ] select
- [ ] number
- [ ] number group
- [ ] password
- [ ] Line

## Dependances
[Kirby uniform](https://github.com/mzur/kirby-uniform)<br> 
```bash
composer require mzur/kirby-uniform:^4.0
```
## install as submodule
Install repo as git submodule<br>
```git submodule add -f https://github.com/batgithub/Kirby-form-snippets.git composer/plugins/kirby-form-snippets```


## How to use in Templates

**form wrapper**
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

**Text**</br>
Info is a descriptive texte.

```php
<?php snippet('form-input', [
    'id'          => '',
    'label'       => '',
    'info'        => '',
    'placeholder' => '',
    'type'        => 'text',
    'pattern'     => '',
    'maxlength'   => '',
    'minlength'   => '',
    'required'    => true // default false
]) ?>
```
**Textarea**</br>
```php
<?php snippet('form-textarea', [
    'id'          => 'message',
    'label'       => 'Message',
    'info'        => '',
    'rows'         => 5,
    'maxlength'   => '',
    'minlength'   => '',
    'required'    => true // default false
]) ?>
```
**Checkbox**</br>
```php
<?php snippet('form-checkbox', [
    'name'       => 'Checkbox',
    'label'       => 'Checkbox 1',
    'value'       => 'Checkbox 1',
    'required'    => false // default false
]) ?>
```
**Checkbox Group**</br>
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
