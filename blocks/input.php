<? if(isset($label)): ?>
<label <? if(isset($id)) echo 'for="' . $id . '"';?>><?=$label?></label>
<? endif;?>
<input <? if(!empty($class)) echo 'class="' . $class . '"';?> type="<?=$type?>" <? if(!empty($name)) echo 'name="' . $name . '"';?> <? if(!empty($value)) echo 'value="' . $value . '"';?> <? if(isset($id)) echo 'id="' . $id . '"';?> />