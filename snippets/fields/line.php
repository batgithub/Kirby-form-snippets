<?php
// Reserved for a future Panel block — uses Kirby Field API ($fld), not RepliqForm config.
?>
    <hr<?php if ($fld->field_name()->isNotEmpty()): ?> id="<?= attr($fld->field_name()) ?>"<?php endif; ?><?php if ($fld->field_class()->isNotEmpty()): ?> class="<?= attr($fld->field_class()) ?>"<?php endif; ?>>
