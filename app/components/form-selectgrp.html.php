<?php

?>

<div class="form-group">
  <label for="<?= $fieldName ?>"><?= $fieldLabel ?? $fieldName ?></label>
  <select class="<?= $fieldClass ?? '' ?>"
          id="<?= $fieldName ?>"
          name="<?= $fieldName ?>"
    <?= implode(" ", [ ($fieldRequired ?? ""), ($fieldReadonly ?? "") ]) ?>
  >
    <?php foreach ($fieldSelect as $optionGroup => $optionList) : ?>
      <optgroup label="<?= $optionGroup ?>">
        <?php foreach ($optionList as $optionK => $optionV) : ?>
          <option value="<?= $optionK ?>" <?= $optionK == $fieldValue ? "selected" : "" ?>><?= $optionV ?></option>
        <?php endforeach; ?>
      </optgroup>
    <?php endforeach; ?>
  </select>
</div>
