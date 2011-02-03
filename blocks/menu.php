<nav>
	<? foreach($items as $item):?>
		<?= B::tag(array('name'=>'li', 'value'=>$item)) ?>
	<? endforeach; ?>
</nav>