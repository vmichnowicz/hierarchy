<div class="item deep_{deep}" id="hierarchy_id_{hierarchy_id}">
	<a href="<?php echo isset($url) ? htmlspecialchars($url, ENT_QUOTES, 'UTF-8') : NULL; ?>" rel="nofollow" class="main"><strong><?php echo isset($title) ? htmlspecialchars($title, ENT_QUOTES, 'UTF-8') : NULL; ?></strong> <em>(id: {hierarchy_id}, num children: {num_children}, order: {hierarchy_order})</em></a>
	<a href="javascript:void(0);" class="button edit">Edit</a>
	
	<form method="post" action="<?php echo site_url('menu/new_parent/' . ( isset($hierarchy_id) ? $hierarchy_id : NULL) );?>">
		<fieldset>
			<label>Order:</label>
			<a class="order_decrease" href="<?php echo site_url('menu/order_decrease/' . ( isset($hierarchy_id) ? $hierarchy_id : NULL) );?>" title="Move element up">Move Up</a><a class="order_increase" href="<?php echo site_url('menu/order_increase/' . $hierarchy_id);?>" title="Move element down">Move Down</a>
		</fieldset>
		<fieldset>
			<label for="parent_id_{hierarchy_id}">Parent:</label>
			<select name="parent_id" id="parent_id_{hierarchy_id}">
				<option value=""></option>
				<?php if ( isset($elements) AND is_array($elements) ): ?>
					<?php foreach ($elements as $key=>$value): ?>
						<?php if ($key == $parent_id): ?>
							<option selected="selected" value="<?php echo $key; ?>"><?php echo htmlspecialchars($value['title'], ENT_QUOTES, 'UTF-8');?></option>
						<?php elseif ($key != $hierarchy_id): ?>
							<option value="<?php echo $key; ?>"><?php echo htmlspecialchars($value['title'], ENT_QUOTES, 'UTF-8');?></option>
						<?php endif; ?>
					<?php endforeach; ?>
				<?php endif; ?>
			</select>
			<input type="submit" value="Update Parent" />
		</fieldset>
		<fieldset>
			<label>Add Child:</label>
			<a href="<?php echo site_url('menu?parent_id=' . ( isset($hierarchy_id) ? $hierarchy_id : NULL ) . '#add_menu_item'); ?>" class="button child">Add Child</a>
		</fieldset>
		<fieldset>
			<label>Delete Item:</label>
			<a href="<?php echo site_url('menu/delete/' . ( isset($hierarchy_id) ? $hierarchy_id : NULL) ); ?>" class="button delete">Delete</a>
		</fieldset>
	</form>
</div>
