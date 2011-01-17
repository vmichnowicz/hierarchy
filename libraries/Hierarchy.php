<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Name:  Hierarchy
*
* Author: Victor Michnowicz, www.vmichnowicz.com, @vmichnowicz
*
* Location: http://github.com/vmichnowicz/hierarchy
*
* Description: Get your hierarchy on
*
* Requirements: CodeIgniter 2(?), PHP 5, and MySQL with InoDB support
*
*/

class Hierarchy
{
	protected $CI;
	
	public $table;
	public $extras;
	public $is_ordered = FALSE;
	
	public $order_by = 'lineage';
	public $order_by_order = 'ASC';
	
	public $items_array;
	public $hierarchial_items_array;
	
	public $template = 'hierarchy_template';
	public $attributes = '';
	
	public function __construct()
	{
		$this->CI =& get_instance();
		
		$this->CI->load->model('hierarchy_model');
		$this->CI->config->load('hierarchy_config');
	}
	
	/**
	 * __call
	 *
	 * If the method can not be found here, look in hierarchy_model
	 * 
	 * I love magic (methods)
	 *
	 **/
	public function __call($method, $arguments)
	{
		if ( ! method_exists($this->CI->hierarchy_model, $method) )
		{
			throw new Exception('Undefined method Hierarchy::' . $method . '() called');
		}

		return call_user_func_array(array($this->CI->hierarchy_model, $method), $arguments);
	}
	
	/**
	 * Set table used for extra data
	 * 
	 * @access public
	 * 
	 * @param string		Name of table
	 * 
	 * @return object
	 */
	public function table($table)
	{
		// Set table name
		$this->table = $table;
		
		// Get table columns from config file
		$this->extras = $this->CI->config->item('hierarchy_' . $table);
		
		// Is this table ordered? (look for "hierarchy_order")
		$this->is_ordered = in_array('hierarchy_order', $this->extras);
		
		return $this;
	}
	
	/**
	 * Set order by
	 * 
	 * @access public
	 * 
	 * @param string		Name of row to order by
	 * 
	 * @return object
	 */
	function order_by($order_by)
	{
		$this->order_by = $order_by;
		
		return $this;
	}
	
	/**
	 * Set order by order
	 * 
	 * @access public
	 * 
	 * @param string		Order ASC or DESC
	 * 
	 * @return object
	 */
	function order_by_order($order_by_order)
	{
		$this->order_by_order = ( strtolower($order_by_order) == 'asc' || strtolower($order_by_order) == 'desc' ) ? strtoupper($order_by_order) : 'ASC'; 
	
		return $this;
	}
	
	/**
	 * Get array of all items
	 * 
	 * @access public
	 * 
	 * @param string		Name of row to order by
	 * @param string 		Order ASC or DESC
	 * 
	 * @return object
	 */
	public function get_items_array()
	{
		$this->items_array = $this->CI->hierarchy_model->get_items_array($this->table, $this->order_by, $this->order_by_order);
		
		return $this;
	}
	
	/**
	 * Get multi-dimensional array of all items
	 * 
	 * @access public
	 * 
	 * @param string		Name of row to order by
	 * @param string 		Order ASC or DESC
	 * 
	 * @return object
	 */
	public function get_hierarchical_items_array()
	{
		if ( ! $this->items_array )
		{
			$this->get_items_array($this->table, $this->order_by, $this->order_by_order);
		}
		
		$this->hierarchial_items_array = $this->CI->hierarchy_model->get_hierarchical_items_array($this->table, $this->order_by, $this->order_by_order);
		
		return $this;
	}
	
	/**
	 * Generate HTML list
	 * 
	 * @access public
	 * 
	 * @param string		Template name (stored in "views" folder)
	 * @param string 		List type ("ul" or "li")
	 * @param string 		List attributes (add in extra JS, an ID, or some CSS)
	 * 
	 * @return string
	 */
	public function generate_hierarchial_list($template = NULL, $type = 'ul', $attributes = '')
	{
		$this->template = $template ? $template : $this->template;
		$this->attributes = $attributes ? $attributes : $this->attributes;
		
		// Return HTML list
		return $this->_list($type, $this->hierarchial_items_array);
	}

	/**
	 * Generate HTML list
	 * 
	 * @access private
	 * 
	 * @param string		Generate HTML list (recursive fun)
	 * @param string 		List type ("ul" or "li")
	 * @param string 		List array
	 * 
	 * @return string
	 */
	private function _list($type, $list)
	{
		$out = '<' . $type . ' ' . $this->attributes . '>';
		
		foreach ($list as $item)
		{	
			$out .=  '<li>' . $this->CI->parser->parse($this->template, $item['root'], TRUE);
			
			if ( isset($item['children']) )
			{
				$out .= $this->_list($type, $item['children']);
			}
			
			$out .= '</li>';
		}
		
		$out .= '</' . $type . '>';
		
		return $out;
	}
	
}
	