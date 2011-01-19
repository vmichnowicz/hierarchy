<div class="comment deep_{deep}" id="comment_id_{hierarchy_id}">
	<h3>{title}</h3>
	<blockquote>
		<p>{comment}</p>
		<cite>
			Posted by
			<?php if ($author AND $url): ?>
				<a href="<?php echo $url; ?>">{author}</a>
			<?php elseif ($author): ?>
				{author}
			<?php endif; ?>
			on {timestamp}
		</cite>
	</blockquote>

	<button class="reply">Reply</button>
	<em class="details">(id: {hierarchy_id}, num children: {num_children})</em>
</div>