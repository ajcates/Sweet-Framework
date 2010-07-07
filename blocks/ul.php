<ul>
	<? foreach($items as $item): ?>
		<? if(is_array($item)) {
			$item = B::ul(array('items' => $item));
		}?>
	<li><?=$item?></li>
	<? endforeach;?>
</ul>
