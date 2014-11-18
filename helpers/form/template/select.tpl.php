<select name="<?php echo $this->element->name; ?>" class="select2 input-large" id="<?php echo $this->element->id; ?>"<?php if ($this->element->multiple):?> multiple<?php endif; ?>>
    <?php foreach($this->element->options as $sValue => $sLabel): ?>
        <option<?php if ($this->element->value == $sValue || (is_array($this->element->values) && in_array($sValue, $this->element->values))): ?> selected="selected"<?php endif; ?> value="<?php echo $sValue; ?>"><?php echo $sLabel; ?></option>
    <?php endforeach; ?>
</select>