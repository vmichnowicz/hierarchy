<div class="comment deep_{deep}" id="comment_id_{hierarchy_id}">
	<h3><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h3>
	<blockquote>
		<p><?php echo nl2br(htmlspecialchars($comment, ENT_QUOTES, 'UTF-8')); ?></p>
		<cite>
			Posted by
			<?php if ($author AND $url): ?>
				<a href="<?php echo htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($author, ENT_QUOTES, 'UTF-8'); ?></a>
			<?php elseif ($author): ?>
				<?php echo htmlspecialchars($author, ENT_QUOTES, 'UTF-8'); ?>
			<?php endif; ?>
			on {timestamp}
		</cite>
	</blockquote>

	<button class="reply">Reply</button>
	<button class="delete">Delete</button>
	<em class="details">(id: {hierarchy_id}, num children: {num_children})</em>
</div>