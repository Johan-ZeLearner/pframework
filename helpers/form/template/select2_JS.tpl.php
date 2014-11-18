<?php P\lib\framework\helpers\JSManager::addFile('/js/select2/select2.min.js'); ?>
<?php P\lib\framework\helpers\CssManager::addFile('/js/select2/select2.css'); ?>
$("#<?php echo $this->element->id; ?>").select2();