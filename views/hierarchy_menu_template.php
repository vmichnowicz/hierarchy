<div class="item deep_{deep}" id="hierarchy_id_{hierarchy_id}">
	<a href="<?php echo htmlspecialchars($url, ENT_QUOTES, 'UTF-8') ?>" rel="nofollow" class="main"><strong><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8');?></strong> <em>(id: {hierarchy_id}, num children: {num_children}, order: {hierarchy_order})</em></a>
	<a href="javascript:void(0);" class="button edit">Edit</a>
	
	<form method="post" action="<?php echo site_url(); ?>/menu/new_parent/{hierarchy_id}">
		<fieldset>
			<label>Order:</label>
			<a id="order_decrease" href="<?php echo site_url('menu/order_decrease/' . $hierarchy_id);?>" title="Move element up">Move Up</a><a id="order_increase" href="<?php echo site_url('menu/order_increase/' . $hierarchy_id);?>" title="Move element down">Move Down</a>
		</fieldset>
		<fieldset>
			<label for="parent_id_{hierarchy_id}">Parent:</label>
			<select name="parent_id" id="parent_id_{hierarchy_id}">
				<option value=""></option>
				<?php foreach ($elements as $key=>$value): ?>
					<?php if ($key == $parent_id): ?>
						<option selected="selected" value="<?php echo $key; ?>"><?php echo htmlspecialchars($value['title'], ENT_QUOTES, 'UTF-8');?></option>
					<?php else: ?>
						<option value="<?php echo $key; ?>"><?php echo htmlspecialchars($value['title'], ENT_QUOTES, 'UTF-8');?></option>
					<?php endif; ?>
				<?php endforeach; ?>
			</select>
			<input type="submit" value="Update Parent" />
		</fieldset>
		<fieldset>
			<label>Add Child:</label>
			<a href="javascript:void(0);" class="button child">Add Child</a>
		</fieldset>
		<fieldset>
			<label>Delete Item:</label>
			<a href="<?php echo site_url('/menu/delete/' . $hierarchy_id); ?>" class="button delete">Delete</a>
		</fieldset>
	</form>
</div>