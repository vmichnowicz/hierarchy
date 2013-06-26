<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Name:  Hierarchy
* Author: Victor Michnowicz, www.vmichnowicz.com, @vmichnowicz
* Location: http://github.com/vmichnowicz/hierarchy
* Description: Get your hierarchy on
* Requirements: CodeIgniter 2, PHP 5, and MySQL with InnoDB support
*/
class Hierarchy
{
	protected $CI;

	public $table;
	public $extras;
	public $is_ordered = FALSE;

	public $order_by = 'lineage';
	public $order_by_order = 'ASC';

	public $extra_data = array();

	public $items_array;
	public $hierarchial_items_array;

	public $template;
	public $attributes = '';

	public function __construct()
	{
		$this->CI =& get_instance();
		
		$this->CI->load->model('hierarchy_model');
		$this->CI->load->helper('hierarchy');
		$this->CI->config->load('hierarchy_config');
	}
	
	/**
	 * __call
	 *
	 * If the method can not be found here, look in hierarchy_model
	 * I love magic (methods)
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
	 * @param string		Name of table
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

		$this->CI->hierarchy_model->table = $table;

		return $this;
	}
	
	/**
	 * Set template
	 * 
	 * @access public
	 * @param string		Name of template file (located in views folder)
	 * @return object
	 */
	public function template($template = NULL)
	{
		// Set template name (if no name provided, default to hierarchy_tablename_template)
		$this->template = $template ? $template : 'hierarchy_' . $this->table . '_template';
		
		return $this;
	}
	
	/**
	 * Set order by
	 * 
	 * @access public
	 * @param string		Name of row to order by
	 * @return object
	 */
	function order_by($order_by)
	{
		$this->order_by = $order_by;
		return $this;
	}
	
	/**
	 * Add some extra data to each hierarchy item
	 * 
	 * This comes in handy if you want to, for example, display a select list
	 * along with each hierarchy item. You can pass your select options along as
	 * an extra data array. Then you can access this data in your view template.
	 * 
	 * @access public
	 * @param array		Array of extra data
	 * @return object
	 */
	function extra_data($data)
	{
		$this->extra_data = array_merge($this->extra_data, $data);
		return $this;
	}
	
	/**
	 * Set order by order
	 * 
	 * @access public
	 * @param string		Order ASC or DESC
	 * @return object
	 */
	function order_by_order($order_by_order)
	{
		$this->order_by_order = ( strtolower($order_by_order) == 'asc' || strtolower($order_by_order) == 'desc' || strtolower($order_by_order) == 'random' ) ? strtoupper($order_by_order) : 'ASC';
		return $this;
	}

	/**
	 * Get array of all items
	 * 
	 * @access public
	 * @return object
	 */
	public function get_items_array($parent_id = NULL)
	{
		$this->items_array = $this->CI->hierarchy_model->get_items_array($this->table, $this->order_by, $this->order_by_order, $parent_id);
		return $this;
	}

	/**
	 * Get multi-dimensional array of all items
	 * 
	 * @access public
	 * @return object
	 */
	public function get_hierarchical_items_array($parent_id = NULL)
	{
		if ( ! $this->items_array )
		{
			$this->get_items_array($this->table, $this->order_by, $this->order_by_order, $parent_id);
		}

		$this->hierarchial_items_array = $this->CI->hierarchy_model->get_hierarchical_items_array($this->items_array);
		return $this;
	}

	/**
	 * Generate HTML list
	 * 
	 * @access public
	 * @param string		Template name (located in "views" folder)
	 * @param string 		List type ("ul" or "li")
	 * @param string 		List attributes (add in extra JS, an ID, or some CSS)
	 * @return string
	 */
	public function generate_hierarchial_list($template = NULL, $type = 'ul', $attributes = '')
	{
		// If no template was provided AND there isno current template set
		if ( ! $template AND ! $this->template )
		{
			$this->template();
		}
		
		// If no list has already been created
		if ( ! $this->hierarchial_items_array )
		{
			$this->get_hierarchical_items_array();
		}
		
		// Return HTML list
		return $this->_list($type, $template, $this->hierarchial_items_array, $attributes);
	}

	/**
	 * Generate HTML list
	 * 
	 * @access private
	 * @param string 		List type ("ul" or "li")
	 * @param string		Template name (located in "views" folder)
	 * @param array 		List array
	 * @param string		List attributes (add in extra JS, an ID, or some CSS)
	 * @return string
	 */
	private function _list($type, $template, $list, $attributes)
	{
		$out = '<' . $type . ' ' . $attributes . '>';
		
		foreach ($list as $item)
		{	
			$out .= '<li>' . $this->CI->parser->parse($template, $item['root'] + $this->extra_data, TRUE);
			
			if ( isset($item['children']) )
			{
				$out .= $this->_list($type, $template, $item['children'], '');
			}
			
			$out .= '</li>';
		}
		
		$out .= '</' . $type . '>';
		
		return $out;
	}

	/**
	 * If an item exists, get its data
	 *
	 * @access public
	 * @param int			Hierarchy ID
	 * @return null
	 */
	public function item_exists($hierarchy_id)
	{
		return $this->CI->hierarchy_model->item_exists($hierarchy_id, $this->table);
	}

	/**
	 * Return an items lineage (kinda like breadcrumbs)
	 *
	 * @access public
	 * @param int			Hierarchy ID
	 * @return null
	 */
	public function item_lineage($hierarchy_id)
	{
		return $this->CI->hierarchy_model->item_lineage($hierarchy_id, $this->table);
	}

	/**
	 * Add item
	 *
	 * @access public
	 * @param array			Array containing parent ID and other data in extra_data array
	 * @return null
	 */
	public function add_item($data)
	{
		$this->CI->hierarchy_model->add_item($data, $this->table, $this->is_ordered);
	}

	/**
	 * Shift an element left
	 *
	 * @access public
	 * @param int			Hierarchy ID
	 * @return null
	 */
	public function shift_left($hierarchy_id)
	{
		return $this->CI->hierarchy_model->shift_left($hierarchy_id, $this->table, $this->is_ordered);
	}

	/**
	 * Given an item a new parent
	 *
	 * @access public
	 * @param int			Hierarchy ID of item that we are moving
	 * @param int			Hierarchy ID of new parent
	 * @return null
	 */
	public function update_item_parent($hierarchy_id, $parent_id)
	{
		// If our parent ID is an empty string
		$parent_id = $parent_id ? $parent_id : NULL;

		$this->CI->hierarchy_model->update_item_parent($hierarchy_id, $parent_id, $this->table, $this->is_ordered);
	}

	/**
	 * Reorder all elements
	 *
	 * @access public
	 * @param string		Column to order by
	 * @param sring			ASC or DESC
	 * @return null
	 */
	public function reorder()
	{
		$this->CI->hierarchy_model->reorder($this->order_by, $this->order_by_order, $this->table);
	}

	/**
	 * Given an item a new order
	 *
	 * @access public
	 * @param int			Hierarchy ID of item that we are reordering
	 * @param int			New order
	 * @return bool
	 */
	public function new_order($hierarchy_id, $new_order)
	{
		// We can only order items that have a hierarchy_order column
		if ($this->is_ordered)
		{
			return $this->CI->hierarchy_model->new_order($hierarchy_id, $new_order, $this->table);
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Increase an items order (e.g. from 5 to 6)
	 *
	 * @access public
	 * @param int			Hierarchy ID of item that we are moving
	 * @return null
	 */
	public function order_increase($hierarchy_id)
	{
		$item = $this->CI->hierarchy_model->item_exists($hierarchy_id, $this->table);

		if ($item)
		{
			$current_order = $item['hierarchy_order'];
			$next_order = $this->CI->hierarchy_model->next_order($current_order, $item['parent_id'], $this->table);

			if ($next_order)
			{
				$this->new_order($hierarchy_id, $next_order);
			}
			// If we are on the last element, make it the first
			else
			{
				$this->new_order($hierarchy_id, 0);
			}
		}
	}

	/**
	 * Decrease an items order (e.g. from 6 to 5)
	 *
	 * @access public
	 * @param int			Hierarchy ID of item that we are moving
	 * @return null
	 */
	public function order_decrease($hierarchy_id)
	{
		$item = $this->CI->hierarchy_model->item_exists($hierarchy_id, $this->table);

		if ($item)
		{
			$current_order = $item['hierarchy_order'];
			$prev_order = $this->CI->hierarchy_model->prev_order($current_order, $item['parent_id'], $this->table);

			// Our previous order could be 0...
			if ($prev_order !== FALSE)
			{
				$this->new_order($hierarchy_id, $prev_order);
			}
			// If we are at the first element, move it to the bottom
			else
			{
				$highest_order = $this->CI->hierarchy_model->highest_order($item['parent_id'], $this->table);
				$this->new_order($hierarchy_id, $highest_order);
			}
		}
	}
	
}