<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<title>Hierarchical Menu</title>

	<!-- CSS -->
	<link href="/css/style.css" rel="stylesheet" type="text/css" />

	<!-- JavaScript -->
	<script type="text/javascript">
		var BASE_URL = "<?php echo rtrim(site_url(), '/').'/';?>";
	</script>
	<script type="text/javascript" src="<?php echo base_url(); ?>js/jquery-1.5.min.js"></script>
	<script type="text/javascript">
		
	</script>
</head>
<body>

<h1>Hierarchy Demo</h1>

<div id="breadcrumbs">
	<a href="<?php echo site_url(); ?>">Main</a> &raquo;
	Hierarchical Menu
</div>

<h2>Hierarchical Menu</h2>

<p>
	Menu items are <strong>not moderated</strong>.
	If, for example, you are offended by menu items titled &ldquo;Victor is the Best,&rdquo; I suggest you delete those items.
	And if you are still pissed, I suggest deleting all the menu items.
	With that said, would you care to <a href="#add_menu_item">add a menu item</a>?
</p>

<?php echo $menu; ?>

<form method="post" class="add" id="add_menu_item" action="<?php echo site_url('menu/add'); ?>" style="float: left;">
	<fieldset>
		<h2>Add Menu Item</h2>
		<div>
			<label for="title">Title:</label><input type="text" name="title" id="title" />
		</div>
		<div>
			<label for="url">URL:</label><input type="text" name="url" id="url" />
		</div>
		<div>
			<label for="parent_id">Parent:</label><select name="parent_id" id="parent_id">
				<option value="">None</option>
				<?php foreach ($elements as $el): ?>
					<?php if ($parent['hierarchy_id'] == $el['hierarchy_id']): ?>
						<option selected="selected" value="<?php echo $el['hierarchy_id']; ?>"><?php echo htmlspecialchars($el['title'], ENT_QUOTES, 'UTF-8') ?></option>
					<?php else: ?>
						<option value="<?php echo $el['hierarchy_id']; ?>"><?php echo htmlspecialchars($el['title'], ENT_QUOTES, 'UTF-8') ?></option>
					<?php endif; ?>
				<?php endforeach; ?>
			</select>
		</div>
		<div>
			<input type="submit" value="Add Menu Item" />
		</div>
	</fieldset>
</form>

<div id="reorder">
	<h2>Reorder</h2>
	<p>
		So you totally messed up the order of the menu&hellip;
		Why did you do that?
		Do not worry, as you can order the menu alphabetically by title!
	</p>
	<a href="<?php echo site_url('menu/reorder/title/asc'); ?>" class="button" title="Alphabetical order">A-Z</a>
	<a href="<?php echo site_url('menu/reorder/title/desc'); ?>" class="button" title="Reverse alphabetical order">Z-A</a>
	<a href="<?php echo site_url('menu/reorder/title/random'); ?>" class="button" title="Random order">?</a>
</div>

<div id="footer">
	<a href="https://github.com/vmichnowicz/hierarchy">Download Source Code on Github</a>
</div>

</body>
</html>