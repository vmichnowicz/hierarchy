<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Generates unordered hierarchical list
 *
 * @access public
 *
 * @param array			The list array
 * @param string 		List template
 * @param string 		List attributes
 *
 * @return string
 */
function hierarchical_ul($list, $template = NULL, $attributes = '')
{
	return _list('ul', $list, $template, $attributes);
}

/**
 * Generates ordered hierarchical list
 *
 * @access public
 *
 * @param array			The list array
 * @param string 		List template
 * @param string 		List attributes
 *
 * @return string
 */
function hierarchical_ol($list, $template = NULL, $attributes = '')
{
	return _list('ol', $list, $template, $attributes);
}

/**
 * Generates the hierarchical list
 *
 * @access private
 *
 * @param string		List type (unordered or ordered)
 * @param array			The list array
 * @param string 		List attributes
 *
 * @return string
 */
function _list($type = 'ul', $list, $template, $attributes = '')
{
	$out = '<' . $type . ' ' . $attributes . '>';
	
	foreach ($list as $item)
	{	
		$out .=  '<li>' . _parse($template, $item['root'], array());
		
		if ( isset($item['children']) )
		{
			$out .= _list($type, $item['children'], $template);
		}
		
		$out .= '</li>';
	}
	
	$out .= '</' . $type . '>';
	
	return $out;
}

/**
 * Get your parse on
 *
 * @access public
 *
 * @param string		The template
 * @param array			The array of individual list data
 *
 * @return string
 */
 function _parse($template, $data)
 {
	if ( ! $template )
	{
		return;
	}
	
	foreach ($data as $key=>$value)
	{
		// Convert to string
		$value = (string)$value;
		$template = str_replace('{' . $key . '}', $value, $template);
	}
	
	return $template;
 }