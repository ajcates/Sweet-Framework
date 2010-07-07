<header>
	<? if(isset($subtitle) || isset($title)): ?>
	<hgroup>
		<? if(isset($title)): ?>
		<h1><?=$title?></h1>
		<? endif; ?>
		<? if(isset($subtitle)): ?>
		<h2><?=$subtitle?></h2>
		<? endif; ?>
	</hgroup>
	<? endif; ?>
	<? if(isset($nav)): ?>
	<?=$nav?>
	<? endif; ?>
</header>