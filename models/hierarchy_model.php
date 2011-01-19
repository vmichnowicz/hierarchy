<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Hierarchy_model extends CI_Model {
	
	public $items_array = NULL; 
	
	public function __construct()
	{
		$this->config->load('hierarchy_config');
	}
	
	/**
	 * Get all items from a provided table and generate an array
	 *
	 * @author Victor Michnowicz
	 * 
	 * @access public
	 * 
	 * @param string		Name of table
	 * @param string		Name of row to order by
	 * @param string		Order ASC or DESC
	 * 
	 * @return array
	 */
	public function get_items_array($table, $order_by, $order_by_order)
	{
		if ($table)
		{
			// Run query
			$query = $this->db->query("
				SELECT *,
					(
						SELECT COUNT(*)
						FROM hierarchy
						WHERE hierarchy.lineage LIKE (CONCAT(h.lineage,'-%')) AND hierarchy.lineage != h.lineage
					) AS num_children
				FROM
					hierarchy as h,
					$table as j
				WHERE h.hierarchy_id = j.hierarchy_id
				ORDER BY deep ASC, $order_by $order_by_order
			");
			
			// If we got some results
			if ($query->num_rows() > 0)
			{
				// Keep a counter so we can add in extra data later
				$counter = 1;
				
				foreach ($query->result() as $row)
				{
					$items_array[$row->hierarchy_id] = array(
						'hierarchy_id' 	=> $row->hierarchy_id,
						'deep' 			=> $row->deep,
						'lineage' 		=> $row->lineage ? explode('-', $row->lineage) : NULL,
						'parent_id' 	=> $row->parent_id ? $row->parent_id : NULL,
						'num_children' 	=> $row->num_children,
						'surrogate_id' 	=> $counter
					);
					
					// Add in extra data
					foreach ($this->config->item('hierarchy_' . $table) as $extra_row)
					{
						// See if helper function exists to help format this data
						if (function_exists($table . '_' . $extra_row))
						{
							$function = $table . '_' . $extra_row;
							$items_array[$row->hierarchy_id][$extra_row] = $function($row->$extra_row);
						}
						else
						{
							$items_array[$row->hierarchy_id][$extra_row] = $row->$extra_row;
						}
					}
					
					// Advance counter
					$counter++;
				}
				
				return $items_array;
			}
			
			// If no results were found
			else
			{
				return array();
			}
	
		}
		
		// If no table name was provided
		else
		{
			return array();
		}
	}
	
	/**
	 * Get all items from a provided table and generate multi-dimensional array
	 *
	 * @author Victor Michnowicz
	 * 
	 * @access public
	 * 
	 * @param string		Name of table
	 * @param string		Name of row to order by
	 * @param string		Order ASC or DESC
	 * 
	 * @return array
	 */
	public function get_hierarchical_items_array($items_array)
	{
		$count = 0;
		
		$heirarchy = array();
		
		// Loop through all items
		foreach ($items_array as $item)
		{	
			$eval = '$heirarchy';
				
			foreach ($item['lineage'] as $lineage)
			{
				
				if ($lineage != $item['hierarchy_id'])
				{
					//$eval .= '[' . $lineage . ']' . '["children"]';
					$eval .= '[' . $items_array[$lineage]['surrogate_id'] . ']' . '["children"]';
				}
				else
				{
					//$eval .= '[' . $lineage . ']' . '["root"]';
					$eval .= '[' . $items_array[$lineage]['surrogate_id'] . ']' . '["root"]';
				}
				
				$count++;
			}
			
			$count = 0;
			
			$eval .= '=$item;';
			
			// There has to be a better way to do this...
			eval($eval);
		}

		// Return heirarchial list of all elements		
		return $heirarchy;
	}
	
	/**
	 * See if a hierarchy item exists
	 *
	 * @author Victor Michnowicz
	 * 
	 * @access public
	 * 
	 * @param int			Item ID
	 * 
	 * @return mixed
	 */
	public function item_exists($id)
	{
		$query = $this->db
			->where('hierarchy_id', $id)
			->limit(1)
			->get('hierarchy');
		
		if ($query->num_rows() > 0)
		{
			$row = $query->row(); 
			
			$data = array(
				'hierarchy_id' 	=> $row->hierarchy_id,
				'parent_id' 	=> $row->parent_id,
				'lineage' 		=> explode('-', $row->lineage),
				'deep' 			=> $row->deep
			);
			
			return $data;
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * Add a hierarchy item
	 *
	 * @author Victor Michnowicz
	 * 
	 * @access public
	 *
	 * @param string		Extra table name
	 * @param array			Extra data
	 * 
	 * @return bool
	 */
	public function add_item($data)
	{
		// Make sure the parent_id is set
		if ( ! array_key_exists('parent_id', $data) )
		{
			return FALSE;
		}
		
		// See if this parent item exists in the given table
		$parent = $this->item_exists($data['parent_id']);
		
		// If a non-NULL parent_id was set, make sure it refers to a valid item
		if ( ! $parent AND $data['parent_id'] != NULL)
		{
			return FALSE;
		}
		
		// If we want to set an order
		if ($this->hierarchy->is_ordered)
		{
			$query = $this->db->query("
				SELECT *
				FROM hierarchy as h, {$this->hierarchy->table} as j
				WHERE
					parent_id = {$data['parent_id']} AND
					h.hierarchy_id = j.hierarchy_id
				ORDER BY hierarchy_order DESC
				LIMIT 1
			");
				
			$row = $query->row(); 
			
			// Get highest order and add one
			$data['hierarchy_order'] = $row->hierarchy_order + 1;
		}
		
		// Insert Item into hierarchy table
		$this->db->insert('hierarchy', array('parent_id' => $data['parent_id']));
		
		// Get insert ID
		$insert_id = $this->db->insert_id();
		
		// Update extra data...
		foreach ($this->config->item('hierarchy_' . $this->hierarchy->table) as $extra_row)
		{
			$extra_data[$extra_row] = $data[$extra_row];
		}
		
		// Add hierarchy ID to extra data array
		$extra_data['hierarchy_id'] = $insert_id;
		
		// Add item in database
		$this->db->insert($this->hierarchy->table, $extra_data);
		
		// If a parent ID was provided
		if ($data['parent_id'])
		{
			$parent['lineage'][] = $insert_id;
			
			$update_data = array(
				'lineage' 	=> implode('-', $parent['lineage']),
				'deep' 		=> $parent['deep'] + 1
			);
			
			$this->db
				->where('hierarchy_id', $insert_id)
				->update('hierarchy', $update_data);
		}
		
		// If no parent ID was provided
		else
		{
			// We just need to update the lineage
			$update_data = array(
				'lineage' 	=> $insert_id
			);
			
			$this->db
				->where('hierarchy_id', $insert_id)
				->update('hierarchy', $update_data);
		}

		return TRUE;
	}
	
	/**
	 * Delete a hierarchy item
	 *
	 * @author Victor Michnowicz
	 * 
	 * @access public
	 *
	 * @param int			Item ID
	 * @param bool			Shall we delete all of this items children?
	 * 
	 * @return bool
	 */
	public function delete_item($hierarchy_id, $delete_children)
	{
		$query = $this->db
			->select('parent_id, lineage')
			->where('hierarchy_id', $hierarchy_id)
			->limit(1)
			->get('hierarchy');
		
		if ($query->num_rows() > 0)
		{
			$row = $query->row(); 
			
			$parent_id = $row->parent_id;
			$lineage = $row->lineage;
			
			$query = $this->db
				->where('lineage LIKE', $lineage . '%')
				->where('lineage !=', $lineage)
				->order_by('hierarchy_id', 'DESC') // Foreign key constraints strike again
				->get('hierarchy');
			
			// If we have children
			if ($query->num_rows() > 0)
			{
				foreach ($query->result() as $row)
				{
					// If we DO want to delte the children
					if ( $delete_children )
					{
						$this->db->where('hierarchy_id', $row->hierarchy_id)->delete('hierarchy');
					}
					// If we DO NOT want to delte the children (shift left)
					else
					{
						$lineage_array = explode('-', $row->lineage);
						
						foreach ($lineage_array as $key=>$value)
						{
							if ($value == $hierarchy_id)
							{
								unset($lineage_array[$key]);
								
								// We can stop here, this ID will only be found once
								break;
							}
						}
						
						$new_lineage = implode('-', $lineage_array);
						
						if (count($lineage_array) > 1)
						{
							end($lineage_array);
							$new_parent = prev($lineage_array);
							$new_deep = $row->deep - 1;
						}
						else
						{
							$new_parent = NULL;
							$new_deep = 0;
						}
						
						$new_data = array(
							'parent_id' => $new_parent,
							'lineage' => $new_lineage,
							'deep' => $new_deep
						);
						
						$this->db
							->where('hierarchy_id', $row->hierarchy_id)
							->update('hierarchy', $new_data);
					}
				}
			}
			
			// Delete item (we have to do this last because of foreign key constraints)
			$this->db->where('hierarchy_id', $hierarchy_id)->delete('hierarchy');
			
			return TRUE;
		}
		
		// If this element does not have any children
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * Shift a hierarchy item left (and bring its children along with it)
	 *
	 * @author Victor Michnowicz
	 * 
	 * @access public
	 *
	 * @param int			Item ID
	 * 
	 * @return bool
	 */
	public function shift_left($hierarchy_id)
	{
		// Get item info
		$item = $this->item_exists($hierarchy_id);
		
		// Make sure this is not a root element
		if ( ! $parent = $this->item_exists($item['parent_id']) )
		{
			return FALSE;
		}
		
		// Get the parent ID of the parent element
		$parent_parent_id = $parent['parent_id'];
		
		// Get all items that share this lineage (that includes the item with the ID passed thru)
		$query = $this->db
			->where('lineage LIKE', implode('-', $item['lineage']) . '%')
			->order_by('hierarchy_id', 'DESC') // Foreign key constraints strike again
			->get('hierarchy');
			
		// Loop through each result (we are guaranteed at least one result)
		foreach ($query->result() as $row)
		{
			$lineage_array = explode('-', $row->lineage);
						
			foreach ($lineage_array as $key=>$value)
			{
				if ($value == $item['parent_id'])
				{
					unset($lineage_array[$key]);
					
					// We can stop here, this ID will only be found once
					break;
				}
			}
			
			$new_lineage = implode('-', $lineage_array);
			$new_deep = (count($lineage_array) > 1) ? $row->deep - 1 : 0;
			
			$new_data = array(
				'lineage' => $new_lineage,
				'deep' => $new_deep
			);
			
			// If we are on the item that we decided to shift left
			if ($row->hierarchy_id == $hierarchy_id)
			{
				$new_data['parent_id'] = $parent_parent_id;
			}
			
			// Update item
			$this->db
				->where('hierarchy_id', $row->hierarchy_id)
				->update('hierarchy', $new_data);
		}
		
		return TRUE;

	}

	/**
	 * Give an element a new parent (and bring all children along with it)
	 *
	 * @author Victor Michnowicz
	 * 
	 * @access public
	 *
	 * @param int			Item ID
	 * @param int 			New parent ID
	 * 
	 * @return bool
	 */
	public function new_parent($hierarchy_id, $parent_id)
	{
		if ( ! $item = $this->item_exists($hierarchy_id) )
		{
			return FALSE;
		}
		
		$parent = $parent_id ? $this->item_exists($parent_id) : NULL;
		
		// Get all items that share this lineage (that includes the item with the ID passed thru)
		$query = $this->db
			->where('lineage LIKE', implode('-', $item['lineage']) . '%')
			->order_by('hierarchy_id', 'DESC') // Foreign key constraints strike again
			->get('hierarchy');
		
		// Loop through each result (we are guaranteed at least one result)
		foreach ($query->result() as $row)
		{
			// Explode our lineage array for this item
			$lineage_array = explode('-', $row->lineage);
			
			// If the item we are moving has a parent
			if ($parent)
			{
				$new_lineage = array_merge($parent['lineage'], array_slice($lineage_array, $item['deep']));
			}
			
			// If we are making a root item
			else
			{
				$new_lineage =  array_slice($lineage_array, $item['deep'] - 1);
			}
			
			$data = array(
				'lineage' => implode('-', $new_lineage),
				'deep' =>count($new_lineage) - 1,
			);
			
			// Update elements
			$this->db
				->where('hierarchy_id', $row->hierarchy_id)
				->update('hierarchy', $data);
		}
		
		// Update main element
		$data = array(
			'parent_id' => $parent_id ? $parent_id : NULL
		);
		
		$this->db
			->where('hierarchy_id', $hierarchy_id)
			->update('hierarchy', $data);
			
		return TRUE;
	}

	/**
	 * Generate full item lineage
	 *
	 * @author Victor Michnowicz
	 * 
	 * @access public
	 *
	 * @param int			Item ID
	 * 
	 * @return array
	 */
	public function item_lineage($hierarchy_id, $table)
	{
		if ( $item = $this->item_exists($hierarchy_id) )
		{
			$lineage_csv = implode(',', $item['lineage']);
			
			$query = $this->db->query("
				SELECT *
				FROM hierarchy as h, $table as j
				WHERE
					h.hierarchy_id = j.hierarchy_id AND
					h.hierarchy_id IN ($lineage_csv)
			");
			
			// Keep a counter so we can add in extra data later
			$counter = 0;
			
			foreach ($query->result() as $row)
			{
				$data[$counter] = array(
					'hierarchy_id' 	=> $row->hierarchy_id,
					'deep' 			=> $row->deep,
					'lineage' 		=> $row->lineage ? explode('-', $row->lineage) : NULL,
					'parent_id' 	=> $row->parent_id ? $row->parent_id : NULL,
				);
				
				// Add in extra data
				foreach ($this->config->item('hierarchy_' . $table) as $extra_row)
				{
					$data[$counter][$extra_row] = $row->$extra_row;
				}
				
				// Advance counter
				$counter++;
			}
			
			return $data;
		}
	}
	
	/**
	 * Give an element a new order
	 *
	 * @author Victor Michnowicz
	 * 
	 * @access public
	 *
	 * @param int			Item ID
	 * @param int 			New order
	 * 
	 * @return null
	 */
	public function new_order($hierarchy_id, $new_order)
	{
		$new_order = (int)$new_order;
		
		// Get element
		$query = $this->db->query("
			SELECT *
			FROM hierarchy as h, {$this->hierarchy->table} as j
			WHERE
				h.hierarchy_id = j.hierarchy_id AND
				h.hierarchy_id = $hierarchy_id
			LIMIT 1
		");
		
		// If this item exists
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			
			$parent_id = $row->parent_id;
			$current_order = $row->hierarchy_order;
			
			// Get all items with the same parent ID that have an order between 
			$orders = $this->db->query("
				SELECT *
				FROM hierarchy as h, {$this->hierarchy->table} as j
				WHERE
					h.hierarchy_id = j.hierarchy_id AND
					parent_id = $parent_id AND
					hierarchy_order BETWEEN $new_order AND $current_order
			");
			
			foreach ($orders->result() as $row)
			{
				// If this is the element that we want to move
				if ($row->hierarchy_order == $current_order)
				{
					$this->db
						->where('hierarchy_id', $row->hierarchy_id)
						->update($this->hierarchy->table, array('hierarchy_order' => $new_order));
				}
				else
				{
					$this->db
						->where('hierarchy_id', $row->hierarchy_id)
						->update($this->hierarchy->table, array('hierarchy_order' => $row->hierarchy_order + 1));
				}
			}
		}
	}
	
	/**
	 * Reorder all hierarchy items of a given group
	 *
	 * @author Victor Michnowicz
	 * 
	 * @access public
	 * 
	 * @return null
	 */
	public function reorder()
	{	
		// Get all items
		$query = $this->db
			->order_by($this->hierarchy->order_by, $this->hierarchy->order_by_order)
			->join($this->hierarchy->table, $this->hierarchy->table . '.hierarchy_id = hierarchy.hierarchy_id')
			->get('hierarchy');
		
		if ($query->num_rows() > 0)
		{
			// Create an array of hierarchy items ordered by parent ID
			foreach ($query->result() as $row)
			{
				$data[$row->parent_id][] = $row->hierarchy_id;
			}
			
			// For each parent group
			foreach ($data as $parent_group)
			{
				// For each item in our parent group
				foreach ($parent_group as $key=>$value)
				{
					// Update database with new order
					$this->db
						->where('hierarchy_id', $value)
						->update($this->hierarchy->table, array('hierarchy_order' => $key));
				}
			}
		}
	}
	
}