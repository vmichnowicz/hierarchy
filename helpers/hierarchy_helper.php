<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// If we want to custom format some data we use the format: tablename_rowname
function comments_timestamp($date)
{
	return date('l, F jS Y', $date);
}
