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
		$(document).ready(function() {

			// Add JS class to body
			$('body').addClass('js');

			// Edit an item
			$('a.edit').live('click', function() {
				$(this).siblings('form').slideToggle('slow');
			});

			// Delete an item
			$('a.delete').live('click', function(e) {

				e.preventDefault();
				$('body').addClass('waiting');

				var answer = confirm('Are you sure you want to delete this menu item (and all child elements)?');

				if (answer) {
					var url = $(this).attr('href');
					$.get(url, function() {
						$('#menu').load(location.href + ' #menu > *', function() {
							$('body').removeClass('waiting');
						});
					});
				}
			});

			// Add a child element
			$('a.child').live('click', function() {
				var id = $(this).closest('div').attr('id').replace('hierarchy_id_', '');

				$('form.add select option[value="' + id + '"]').attr('selected', true);
				window.location.hash = 'add_menu_item';
			});

			// Update menu item's parent
			$('#menu form').live('submit', function(e) {

				e.preventDefault();
				$('body').addClass('waiting');

				var action = $(this).attr('action');
				var id = $(this).closest('div').attr('id');

				// post it..
				$.post(action, $(this).serialize(), function() {

					$('#menu').load(location.href + ' #menu > *', function() {
						$('body').removeClass('waiting');
					});

				});

			});

			// Add item
			$('#add_menu_item').live('submit', function(e) {

				e.preventDefault();
				$('body').addClass('waiting');

				// Clear error messages
				$('div.error').remove();
				$('.error').removeClass('error');

				var form = $(this);
				var action = $(this).attr('action');

				// Disable submit button
				$(form).find('input[type="submit"]').attr('disabled', true);

				$.post(action, $(form).serialize(), function(data) {

					// There were some errors...
					if (data.result == 'failure')
					{
						$('body').removeClass('waiting');

						$.each(data.errors, function (item, error) {

							el = $('<div />');
							el.addClass('error');
							el.html(error);

							// Add error message
							$('#' + item).addClass('error').after(el);
						});
					}
					// There were no errors, GREAT SUCCESS!
					else
					{
						// Update hidden value
						$('#parent_id').val('');

						// Reset form
						$(form)[0].reset();

						$('#menu').load(location.href + ' #menu > *', function() {
							$('body').removeClass('waiting');
						});
					}

					// Enable submit button
					$(form).find('input[type="submit"]').attr('disabled', false);

				}, 'json');

			});

			// Adjust item order
			$('a.order_increase, a.order_decrease').live('click', function(e) {

				e.preventDefault();
				$('body').addClass('waiting');

				var url = $(this).attr('href');

				// post it..
				$.post(url, function() {
					$('#menu').load(location.href + ' #menu > *', function() {
						$('body').removeClass('waiting');
					});
				});

			});

		});
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