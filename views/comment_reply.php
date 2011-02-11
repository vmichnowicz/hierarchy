<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<title>Hierarchical Comments | Reply to: <?php echo htmlspecialchars($comment['title'], ENT_QUOTES, 'UTF-8'); ?></title>

	<!-- CSS -->
	<link href="/css/style.css" rel="stylesheet" type="text/css" />
</head>
<body>
	<h1>Hierarchical Comments | Reply</h1>

	<blockquote>
		<p><?php echo htmlspecialchars($comment['comment'], ENT_QUOTES, 'UTF-8'); ?></p>
		<cite>&mdash; <?php echo htmlspecialchars($comment['author'], ENT_QUOTES, 'UTF-8'); ?></cite>
	</blockquote>

	<form method="post" accept-charset="utf-8" action="<?php echo site_url('comments/add'); ?>" id="add_comment">
		<fieldset>
			<h2>Comment:</h2>
			<div>
				<p>
					Please enter your comment below.
					Asterisks (<em class="req">*</em>) designate a required field.
				</p>
			</div>
			<div>
				<label for="author">Name<em class="req">*</em>:</label><input type="text" name="author" id="author" />
			</div>
			<div>
				<label for="url">URL:</label><input type="text" name="url" id="url" />
			</div>
			<div>
				<label for="email">Email (not published)<em class="req">*</em>:</label><input type="text" name="email" id="email" />
			</div>
			<div>
				<label for="title">Title<em class="req">*</em>:</label><input type="text" name="title" id="title" />
			</div>
			<div>
				<label for="comment">Comment<em class="req">*</em>:</label><textarea name="comment" id="comment"></textarea>
			</div>
			<div>
				<input type="hidden" name="parent_id" id="parent_id" value="<?php echo $comment['hierarchy_id']; ?>" /><input type="submit" value="Submit Comment" />
			</div>
		</fieldset>
	</form>

</body>
</html>