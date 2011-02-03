<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<title>CI Hierarchy</title>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<style type="text/css">
	
		body {
			font: 13px/18px Verdana, sans-serif;
			color: #333;
			margin: 1%;
		}
		
		h1 {
			color: #555;
			padding-bottom: 5px;
			border-bottom: 5px solid #efefef;
		}
		
		h2 {
			color: #555;
			padding-bottom: 5px;
			border-bottom: 5px solid #ddd;
		}
		
		h3 {
			margin: 0px;
			padding: 0px;
		}
		
		/* MENU */
		
		#menu {
			margin: 0px;
			padding: 0px;
		}
		
		#menu ul {
			margin-left: 30px;
			padding: 0px;
		}
		
		#menu li {
			display: block;
			margin: 5px 0px;
		}
		
		#menu .item {
			padding: 5px;
			box-shadow: 0px 0px 5px rgba(0,0,0,.15) inset;
			border-radius: 5px;
		}
		
		/* Let's get the whole rainbow up in here */
		#menu .deep_0 {	background-color: #F78181; }
		#menu .deep_1 {	background-color: #F7BE81; }
		#menu .deep_2 {	background-color: #F3F781; }
		#menu .deep_3 {	background-color: #BEF781; }
		#menu .deep_4 {	background-color: #81F781; }
		#menu .deep_5 {	background-color: #81F7BE; }
		#menu .deep_6 {	background-color: #81F7F3; }
		#menu .deep_7 {	background-color: #81BEF7; }
		#menu .deep_8 {	background-color: #8181F7; }
		#menu .deep_9 {	background-color: #BE81F7; }
		#menu .deep_10 { background-color: #F781F3; }
		#menu .deep_11 { background-color: #F781BE; }
		#menu .deep_12 { background-color: #F78181; }
		
		#menu a {
			line-height: 30px;
			color: #555;
			text-shadow: 0px 1px 0px rgba(255,255,255,.25);
			text-decoration: none;
		}
		
		#menu a:hover,
		#menu a:focus {
			color: #333;
			text-shadow: 0px 1px 0px rgba(255,255,255,.5);
		}
		
		#menu a:hover strong,
		#menu a:focus strong {
			text-decoration: underline;
		}
		
		#menu em {
			font-size: 11px;
			color: #888;
		}
		
		#menu fieldset {
			border: 0px;
			margin: 0px;
			padding: 0px;
		}
		
		/* COMMENTS */
		
		#comments {
			list-style-type: none;
			margin: 0px;
			padding: 0px;
		}
		
		#comments ul {
			list-style-type: none;
			margin: 0px 0px 0px 30px;
			padding: 0px;
		}
		
		#comments div.comment {
			background-color: #efefef;
			margin: 5px 0px;
			padding: 5px;
			color: #555;
			box-shadow: 0px 0px 5px rgba(0,0,0,.25) inset;
			border-radius: 5px;
			text-shadow: 0px 1px 0px #fff;
		}
		
		#comments blockquote {
			margin: 5px;
			padding: 0px;
		}
		
		#comments cite {
			font-size: 11px;
		}
		
		#comments cite a:link,
		#comments cite a:visited {
			color: #F78181;
		}
		
		#comments cite a:hover,
		#comments cite a:focus {
			color: #F7BE81;
		}
		
		#comments em.details {
			font-size: 11px;
			color: #999;
		}
		
		/* ADD COMMENT */
		
		#add_comment {
			width: 710px;
		}
		
		#add_comment fieldset {
			border: 0px none;
			margin: 0px;
			padding: 0px;
		}
		
		#add_comment div {
			margin: 15px 0px;
		}
		
		#add_comment label {
			display: inline-block;
			width: 220px;
			font-weight: bold;
			margin-right: 20px;
			vertical-align: top;
		}
		
		#add_comment em.req {
			color: red;
		}
		
		#add_comment input[type="text"],
		#add_comment textarea {
			font: 13px sans-serif;
			color: #efefef;
			text-shadow: 0px 1px 0px black;
			box-shadow: 0px 0px 5px black inset;
			padding: 5px;
			border: 0px none;
			margin: 0px;
			background-color: #555;
			display: inline-block;
			width: 460px;
		}
		
		#add_comment textarea {
			height: 120px;
		}
		
		#add_comment input[type="text"]:focus,
		#add_comment input[type="text"]:hover,
		#add_comment textarea:focus,
		#add_comment textarea:hover {
			color: #fff;
			background-color: #666;
		}
		
		#add_comment input[type="submit"] {
			float: right;
			margin-right: 1%;
		}
		
		#add_comment div.error {
			margin-top: 0px;
			font-size: 12px;
			color: #fff;
			text-shadow: 0px 1px 0px rgba(0,0,0,.25);
			margin-left: 240px;
			background-color: #f78181;
			padding: 5px;
			box-shadow: 0px 0px 5px rgba(0,0,0,.15) inset;
			border-radius: 0px 0px 5px 5px;
			width: 460px;
		}
		
		#add_comment input.error,
		#add_comment textarea.error {
			box-shadow: 0px 0px 5px red inset;
		}
		
	</style>
	<script type="text/javascript">
		var BASE_URL = "<?php echo rtrim(site_url(), '/').'/';?>";
	</script>
	<script type="text/javascript" src="http://www.google.com/jsapi"></script>
	<script type="text/javascript">
		google.load("jquery", "1.4.4");
		google.setOnLoadCallback(function() {
	    	
	    	// Add comment form
	    	var add_comment = $('#add_comment');
	    	
	    	// Move add comment form
	    	$('button.reply').live('click', function() {  		
	    		var parent = $(this).closest('div');
	    		var id = $(parent).attr('id');
	    		id = id.replace('comment_id_', '');
	    		
	    		// Update hidden value
	    		$('#parent_id').val(id);
	    		
	    		// Move element
	    		$(parent).append(add_comment);
	    		
	    		// Change button
	    		$(this).html('Cancel Reply').addClass('active');
	    	});
	    	
	    	// Cancel reply
	    	$('button.reply.active').live('click', function() {
	    		
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
		    			$('#comments').load(location.href + ' #comments > *');
		    		}
	    			
	    			// Enable submit button
	    			$(form).find('input[type="submit"]').attr('disabled', false);
	    			
	    		}, 'json');
	    	});
	    	
	    	// Delete Comment
	    	$('button.delete').live('click', function() {
	    		
	    		var answer = confirm('Are you sure you want to delete this comment (and all child comments)?');
	    		
	    		if (answer) {
	    			
	    			var id = $(this).closest('div').attr('id').replace('comment_id_', '');
	    			
	    			var url = BASE_URL + 'hierarchy_demo/delete/' + id;
	    			
	    			$.get(url, function() {
	    				$('#comments').load(location.href + ' #comments > *');
	    			});
	    		}
	    		
	    	});
	    	
		});
	</script>
	
</head>
<body>

<h1>My Menu</h1>
<?php echo $menu; ?>

<h1>My Comments</h1>
<?php echo $comments; ?>

<form method="post" accept-charset="utf-8" action="<?php echo site_url('hierarchy_demo/add_comment'); ?>" id="add_comment">
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
			<input type="hidden" name="parent_id" id="parent_id" value="" /><input type="submit" value="Submit Comment" />
		</div>
	</fieldset>
</form>

</body>
</html>