# Kirby form snippets

- [x] honeypot
- [x] input
- [x] textarea
- [ ] checkbox
- [ ] checkbox group
- [ ] radio
- [ ] select
- [ ] number
- [ ] number group
- [ ] password
- [ ] Line
- [x] info


## How to use in Templates

**Honeypot**
```php
 <?php snippet('form/honeypot', ['name' => 'website']) ?>
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
    'required'    => true
]) ?>
```
**Textarea**</br>
```php
<?php snippet('components/input-textarea', [
    'id'          => 'message',
    'label'       => 'Message',
    'info'        => '',
    'rows'         => 5,
    'required'    => false
]) ?>
```