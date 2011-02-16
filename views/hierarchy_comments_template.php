<div class="comment deep_{deep}" id="comment_id_{hierarchy_id}">
	<h3><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h3>
	<blockquote>
		<p><?php echo nl2br(htmlspecialchars($comment, ENT_QUOTES, 'UTF-8')); ?></p>
		<div>
			<cite>
				Posted by
				<?php if ($author AND $url): ?>
					<a href="<?php echo htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($author, ENT_QUOTES, 'UTF-8'); ?></a>
				<?php elseif ($author): ?>
					<?php echo htmlspecialchars($author, ENT_QUOTES, 'UTF-8'); ?>
				<?php endif; ?>
				on {timestamp}
			</cite>
		</div>
	</blockquote>

	<a href="<?php echo site_url('comments?reply_to=' . $hierarchy_id . '#add_comment'); ?>" class="reply button">Reply</a>
	<a href="<?php echo site_url('comments/delete/' . $hierarchy_id); ?>" class="delete button">Delete</a>

	<em class="details">(id: {hierarchy_id}, level: {deep}, num children: {num_children})</em>
</div>