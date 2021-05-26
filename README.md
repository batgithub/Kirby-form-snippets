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

## How to use in Templates

**Honeypot**
```php
 <?php snippet('form-honeypot', ['name' => 'website']) ?>
```

**Text**</br>
Info is a descriptive texte.

```php
<?php snippet('form/input', [
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