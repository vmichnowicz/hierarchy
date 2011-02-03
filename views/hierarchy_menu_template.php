<div class="item deep_{deep}" id="hierarchy_id_{hierarchy_id}">
	<a href="{url}"><strong>{title}</strong> <em>(id: {hierarchy_id}, num children: {num_children}, order: {hierarchy_order})</em></a>
	<form method="post" action="<?php echo site_url(); ?>/hierarchy_demo/new_parent/menu/{hierarchy_id}">
		<fieldset>
			<label for="parent_id_{hierarchy_id}">Parent:</label>
			<select name="parent_id" id="parent_id_{hierarchy_id}">
				<option value=""></option>
				<?php foreach ($elements as $key=>$value): ?>
					<?php if ($key == $parent_id): ?>
						<option selected="selected" value="<?php echo $key; ?>"><?php echo $value['title']; ?></option>
					<?php else: ?>
						<option value="<?php echo $key; ?>"><?php echo $value['title']; ?></option>
					<?php endif; ?>
				<?php endforeach; ?>
			</select>
			<input type="submit" value="Update Parent" />
		</fieldset>
	</form>
	<form method="post" action="<?php echo site_url(); ?>/hierarchy_demo/delete/{hierarchy_id}">
		<fieldset>
			<input type="submit" value="Delete" />
		</fieldset>
	</form>
</div>