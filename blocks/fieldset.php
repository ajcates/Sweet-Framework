<fieldset class="ui-widget-content ui-corner-all">
	<? if(isset($legend)): ?><legend><?=$legend?></legend><? endif; ?>
	<? foreach($fields as $field): ?>
		<?=$field?>
	<? endforeach;?>
</fieldset>