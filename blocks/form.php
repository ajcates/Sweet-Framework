<form <? if(isset($id)) echo 'id="' . $id . '"';?> action="<?=$action?>" method="<?=$method?>" accept-charset="utf-8">
	<? foreach((array)$fields as $item): ?>
		<?=$item?>
	<? endforeach; ?>
</form>