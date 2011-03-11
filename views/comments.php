<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<title>Hierarchical Comments</title>

	<!-- CSS -->
	<link href="/css/style.css" rel="stylesheet" type="text/css" />

	<!-- JavaScript -->
	<script type="text/javascript">
		var BASE_URL = "<?php echo rtrim(site_url(), '/').'/';?>";
	</script>
	<script type="text/javascript" src="<?php echo base_url(); ?>js/jquery-1.5.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {

			// Add comment form
			var add_comment = $('#add_comment');

			// Move add comment form
			$('a.reply').live('click', function(e) {

				e.preventDefault();

				var parent = $(this).closest('div');
				var id = $(parent).attr('id');
				id = id.replace('comment_id_', '');

				// Update hidden value
				$('#parent_id').val(id);

				// Move element
				$(parent).append(add_comment);

				// Change button
				$(this).html('Cancel').addClass('active');
			});

			// Cancel reply
			$('a.reply.active').live('click', function(e) {

				e.preventDefault();

				// Update hidden value
				$('#parent_id').val('');

				// Move element
				$('body').append(add_comment);

				// Change button
				$(this).html('Reply').removeClass('active');

			});

			// Submit comment
			$(add_comment).live('submit', function(e) {

				e.preventDefault();

				// Clear error messages
				$('div.error').remove();
				$('.error').removeClass('error');

				var form = $(this);
				var action = $(add_comment).attr('action');

				// Disable submit button
				$(form).find('input[type="submit"]').attr('disabled', true);

				$.post(action, $(add_comment).serialize(), function(data) {

					// There were some errors...
					if (data.result == 'failure')
					{
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
						$(add_comment)[0].reset();

						// Move element
						$('body').append(add_comment);

						$('#comments').load(location.href + ' #comments > *');
					}

					// Enable submit button
					$(form).find('input[type="submit"]').attr('disabled', false);

				}, 'json');
			});

			// Delete Comment
			$('a.delete').live('click', function(e) {

				e.preventDefault();

				var answer = confirm('Are you sure you want to delete this comment (and all child comments)?');

				if (answer) {

					var id = $(this).closest('div').attr('id').replace('comment_id_', '');

					var url = BASE_URL + 'comments/delete/' + id;

					$.get(url, function() {
						$('#comments').load(location.href + ' #comments > *');
					});
				}

			});

		});
	</script>
</head>
<body>

<h1>Hierarchy Demo</h1>

<div id="breadcrumbs">
	<a href="<?php echo site_url(); ?>">Main</a> &raquo;
	Hierarchical &ldquo;threaded&rdquo; Comments
</div>

<h2>Hierarchical &ldquo;threaded&rdquo; Comments</h2>

<p>
	Comments are <strong>not moderated</strong>.
	If, for example, you are offended by people saying unicorns are the best mythical creatures, and someone makes a post about how dragons eat unicorns all the time &mdash; delete that comment.
	And if you are still pissed, I suggest deleting all the comments.
	Then you can post 100 comments about how one unicorn can slay at least 20 dragons by impaling them with their magical horn.
	With that said, would you care to <a href="#add_comment">add a comment</a>?
</p>

<?php echo $comments; ?>

<form method="post" class="add" accept-charset="utf-8" action="<?php echo site_url('comments/add'); ?>" id="add_comment">
	<fieldset>
		<h2>Comment<?php echo $reply_to ? ' in reply to: <em>' . $reply_to['title'] . '</em>' : NULL; ?></h2>
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
			<label for="comment">Comment<em class="req">*</em>:</label><textarea name="comment" id="comment" rows="10" cols="50"></textarea>
		</div>
		<div>
			<input type="hidden" name="captcha_index" value="<?php echo $captcha['index']; ?>" />
			<label for="captcha"><?php echo $captcha['question']; ?><em class="req">*</em>:</label><input type="text" name="captcha" id="captcha" />
		</div>
		<div>
			<input type="hidden" name="parent_id" id="parent_id" value="<?php echo $reply_to ? $reply_to['hierarchy_id'] : NULL; ?>" /><input type="submit" value="Submit Comment" />
		</div>
	</fieldset>
</form>

<div class="clear"></div>

<div id="footer">
	<a href="https://github.com/vmichnowicz/hierarchy">Download Source Code on Github</a>
</div>

</body>
</html>