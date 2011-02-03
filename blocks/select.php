<? if(isset($label)): ?>
<label for="<?=$id?>"><?=$label?></label>
<? endif;?>
<select name="<?=$name?>" <? if(isset($id)) echo 'id="' . $id . '"';?>>
	<? foreach($options as $option): ?>
		<?=$option?>
	<? endforeach; ?>
</select>