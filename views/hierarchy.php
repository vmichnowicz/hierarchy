<?php

function ul($data)
{
	$out = '<ul>';
	
	foreach ($data as $item)
	{
		$out .= '<li id="item_' . $item['id'] . '">' . $item['title'];

		if ( isset($item['children']) )
		{
			$out .= ul($item['children']);
		}
		
		$out .= '</li>';
	}
	
	$out .= '</ul>';
	
	return $out;
}

echo ul($menu);

echo '<pre>';
print_r($menu);
echo '</pre>';

?>

