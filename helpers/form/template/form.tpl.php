<form <?php echo $this->current_form->params; ?>>
    <fieldset>
        <legend><?php echo $this->current_form->title; ?></legend>
        
    <?php foreach ($this->current_form->fields as $oField): ?>
            <?php if (is_object($oField)): ?>
                <?php echo $oField->__toString(); ?>
            <?php endif; ?>
    <?php endforeach; ?>
    
    <div class="control-group">
        <label class="control-label"></label>
        <div class="controls">
            <input type="submit" class="<?php echo $this->current_form->submit->class; ?>" value="<?php echo $this->current_form->submit->value; ?>" id="<?php echo $this->current_form->submit->id; ?>" />
        </div>
    </div>

    </fieldset>
</form>