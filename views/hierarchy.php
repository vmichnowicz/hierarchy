<html>
<head>
	<title>CI Hierarchy</title>
	<style type="text/css">
		body {
			font: 13px Verdana, sans-serif;
			color: #333;
		}
		#menu li {
			display: block;
			border-top: 1px solid #fff;
		}
		#menu .item {
			line-height: 50px;
			padding: 5px;
		}
		#menu .deep_0 {
			background-color: #F6CEE3;
		}
		#menu .deep_1 {
			background-color: #CECEF6;
		}
		#menu .deep_2 {
			background-color: #CEF6F5;
		}
		#menu .deep_3 {
			background-color: #CEF6CE;
		}
		#menu .deep_4 {
			background-color: #F6E3CE;
		}
		#menu a {
			line-height: 25px;
			color: #555;
		}
		
	</style>
	<script type="text/javascript" src="http://www.google.com/jsapi"></script>
	<script type="text/javascript">
		google.load("jquery", "1.4.4");
		google.setOnLoadCallback(function() {
	    	//alert('asdf');
		});
	</script>
</head>
<body>

<h1>My Menu</h1>
<?php echo $menu; ?>

<h1>My Comments</h1>

</body>
</html>