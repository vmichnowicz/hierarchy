<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * Hierarchy Config
 * 
 * Add a database table to Hierarchy
 * 
 * If you want to add the table "comments," use the array $config['hierarchy_comments']
 * If you want to enable a custom sort order, add "hierarchy_order" to the array
 * 
 * e.g. $config['hierarchy_comments'] = array('author', 'comment', 'hierarchy_order');
 * 
 * All tables should have a column, "hierarchy_id," that has a foreign key
 * relationship with the "hierarchy_id" column in the "hierarchy" table.
 * "hierarchy_id" is an unsigned int(64)
 * 
 * NOTE: InnoDB is *REQUIRED* for foreign key relationships in MySQL
 * 
 */
$config['hierarchy_menu'] = array('title', 'url', 'hierarchy_order');
$config['hierarchy_comments'] = array('title', 'comment', 'author', 'email', 'url', 'timestamp');