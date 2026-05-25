<?php

$theme = is_array($theme ?? null) ? $theme : [];
$colors = array_replace([
    'pageBg' => '#F5F5F5',
    'cardBg' => '#FFFFFF',
    'border' => '#E5E5E5',
    'label' => '#737373',
    'text' => '#171717',
    'accent' => '#2563EB',
], is_array($theme['colors'] ?? null) ? $theme['colors'] : []);
$width = (int) ($theme['width'] ?? 600);
$fontFamily = is_string($theme['fontFamily'] ?? null) ? $theme['fontFamily'] : "'Helvetica Neue', Helvetica, Arial, sans-serif";
$emptyPlaceholder = is_string($theme['emptyPlaceholder'] ?? null) ? $theme['emptyPlaceholder'] : '—';
$logo = is_string($theme['logo'] ?? null) && $theme['logo'] !== '' ? $theme['logo'] : null;
$logoAlt = is_string($theme['logoAlt'] ?? null) ? $theme['logoAlt'] : ($siteName ?? '');
$logoLink = is_string($theme['logoLink'] ?? null) && $theme['logoLink'] !== '' ? $theme['logoLink'] : ($siteUrl ?? '');
$intro = is_string($theme['intro'] ?? null) && $theme['intro'] !== '' ? $theme['intro'] : null;
$footer = is_string($theme['footer'] ?? null) && $theme['footer'] !== '' ? $theme['footer'] : null;
$previewText = is_string($preview ?? null) && $preview !== '' ? $preview : ($date ?? '');
$datas = is_array($datas ?? null) ? $datas : [];

?>
<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml"
    xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <title><?= html($formName ?? 'Formulaire') ?></title>
    <!--[if !mso]><!-- -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!--<![endif]-->
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style type="text/css">
        #outlook a { padding: 0; }
        .ReadMsgBody { width: 100%; }
        .ExternalClass { width: 100%; }
        .ExternalClass * { line-height: 100%; }
        body { margin: 0; padding: 0; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; }
        p { display: block; margin: 13px 0; }
        ul { margin-top: 0; padding-left: 24px; }
    </style>
    <!--[if !mso]><!-->
    <style type="text/css">
        @media only screen and (max-width:480px) {
            @-ms-viewport { width: 320px; }
            @viewport { width: 320px; }
        }
    </style>
    <!--<![endif]-->
    <!--[if mso]>
        <xml>
        <o:OfficeDocumentSettings>
          <o:AllowPNG/>
          <o:PixelsPerInch>96</o:PixelsPerInch>
        </o:OfficeDocumentSettings>
        </xml>
        <![endif]-->
    <!--[if lte mso 11]>
        <style type="text/css">.outlook-group-fix { width:100% !important; }</style>
        <![endif]-->
</head>

<body style="background-color:<?= esc($colors['pageBg'], 'attr') ?>;">
    <div style="display:none;font-size:1px;color:#ffffff;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;">
        <?= html($previewText) ?>
    </div>

    <div style="background-color:<?= esc($colors['pageBg'], 'attr') ?>;padding:28px 8px 32px;">
        <div style="margin:0 auto;max-width:<?= $width ?>px;">
            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
                <tbody>
                    <?php if ($logo !== null): ?>
                    <tr>
                        <td style="padding:0 0 24px;">
                            <?php if ($logoLink !== ''): ?>
                            <a href="<?= esc($logoLink, 'attr') ?>" style="text-decoration:none;">
                                <img src="<?= esc($logo, 'attr') ?>" alt="<?= esc($logoAlt, 'attr') ?>" width="180" style="display:block;max-width:180px;height:auto;border:0;">
                            </a>
                            <?php else: ?>
                            <img src="<?= esc($logo, 'attr') ?>" alt="<?= esc($logoAlt, 'attr') ?>" width="180" style="display:block;max-width:180px;height:auto;border:0;">
                            <?php endif ?>
                        </td>
                    </tr>
                    <?php endif ?>

                    <tr>
                        <td style="padding:0 0 8px;border-left:4px solid <?= esc($colors['accent'], 'attr') ?>;padding-left:16px;">
                            <div style="font-family:<?= esc($fontFamily, 'attr') ?>;font-size:24px;font-weight:600;line-height:32px;color:<?= esc($colors['text'], 'attr') ?>;">
                                <?= html($formName ?? 'Formulaire') ?>
                            </div>
                            <div style="font-family:<?= esc($fontFamily, 'attr') ?>;font-size:16px;font-weight:400;line-height:24px;color:<?= esc($colors['label'], 'attr') ?>;padding-top:8px;">
                                <?= html($date ?? '') ?>
                            </div>
                        </td>
                    </tr>

                    <?php if ($intro !== null): ?>
                    <tr>
                        <td style="padding:16px 0 24px;font-family:<?= esc($fontFamily, 'attr') ?>;font-size:16px;line-height:24px;color:<?= esc($colors['text'], 'attr') ?>;">
                            <?= html($intro) ?>
                        </td>
                    </tr>
                    <?php endif ?>

                    <tr>
                        <td style="background:<?= esc($colors['cardBg'], 'attr') ?>;border:1px solid <?= esc($colors['border'], 'attr') ?>;border-radius:4px;padding:24px;">
                            <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
                                <tbody>
                                    <?php foreach ($datas as $data): ?>
                                    <?php
                                    $value = $data['value'] ?? '';
                                    $isEmpty = $value === '' || $value === [];
                                    ?>
                                    <tr>
                                        <td style="padding:12px 0;border-bottom:1px solid <?= esc($colors['border'], 'attr') ?>;">
                                            <div style="font-family:<?= esc($fontFamily, 'attr') ?>;font-size:12px;font-weight:700;line-height:20px;letter-spacing:0.04em;text-transform:uppercase;color:<?= esc($colors['label'], 'attr') ?>;">
                                                <?= html($data['label'] ?? '') ?>
                                            </div>
                                            <div style="font-family:<?= esc($fontFamily, 'attr') ?>;font-size:16px;font-weight:400;line-height:24px;color:<?= esc($colors['text'], 'attr') ?>;padding-top:4px;">
                                                <?php if (!$isEmpty && is_array($value)): ?>
                                                    <ul style="margin:0;padding-left:24px;">
                                                        <?php foreach ($value as $item): ?>
                                                        <li><?= html((string) $item) ?></li>
                                                        <?php endforeach ?>
                                                    </ul>
                                                <?php elseif (!$isEmpty): ?>
                                                    <?= html((string) $value) ?>
                                                <?php else: ?>
                                                    <?= html($emptyPlaceholder) ?>
                                                <?php endif ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach ?>
                                </tbody>
                            </table>
                        </td>
                    </tr>

                    <?php if ($footer !== null || ($siteName ?? '') !== ''): ?>
                    <tr>
                        <td style="padding:24px 0 0;font-family:<?= esc($fontFamily, 'attr') ?>;font-size:13px;line-height:20px;color:<?= esc($colors['label'], 'attr') ?>;text-align:center;">
                            <?php if ($footer !== null): ?>
                                <?= html($footer) ?>
                            <?php elseif (($siteUrl ?? '') !== ''): ?>
                                <a href="<?= esc($siteUrl, 'attr') ?>" style="color:<?= esc($colors['accent'], 'attr') ?>;text-decoration:none;">
                                    <?= html($siteName) ?>
                                </a>
                            <?php else: ?>
                                <?= html($siteName) ?>
                            <?php endif ?>
                        </td>
                    </tr>
                    <?php endif ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>
