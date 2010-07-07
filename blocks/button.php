<?
if(!isset($type)) {
	$type = 'button';
}
?>
<button <? if(isset($id)) echo 'id="' . $id . '"';?> type="<?=$type?>" name="<?=$name?>" value="<?=@$value?>">
	<?=$content?>
</button>