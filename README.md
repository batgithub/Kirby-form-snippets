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
```git submodule add -f https://github.com/batgithub/kirby-blocks-repliq.git composer/plugins/kirby-form-snippets```


## How to use in Templates

**form wrapper**
```html
 <form method="post" class="" action="<?= $page->url() ?>">
      <?php snippet('form-honeypot', ['name' => 'website']) ?>
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
