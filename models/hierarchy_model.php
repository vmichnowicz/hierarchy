<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Hierarchy_model extends CI_Model {
	
	public $items = NULL;
	
	public function __construct()
	{
		$this->config->load('hierarchy_config');
	}
	
	/**
	 * Get all items from a provided table
	 *
	 * @author Victor Michnowicz
	 * 
	 * @access public
	 * 
	 * @param string		Name of table
	 * 
	 * @return array
	 */
	public function get_list($table = NULL, $sort = NULL)
	{
		if ($table)
		{
			// Get sort order
			if ( ! $sort )
			{
				$sort = 'lineage';
			}
			
			// Run query
			$query = $this->db->query("
				SELECT *,
					(
						SELECT COUNT(*)
						FROM hierarchy
						WHERE hierarchy.lineage LIKE (CONCAT(h.lineage,'%')) AND hierarchy.lineage != h.lineage
					) AS num_children
				FROM
					hierarchy as h,
					$table as j
				WHERE h.hierarchy_id = j.hierarchy_id
				ORDER BY h.$sort
			");
			
			// If we got some results
			if ($query->num_rows() > 0)
			{
				// Keep a counter so we can add in extra data later
				$counter = 0;
				
				foreach ($query->result() as $row)
				{
					$this->items[$counter] = array(
						'hierarchy_id' 	=> $row->hierarchy_id,
						'deep' 			=> $row->deep,
						'lineage' 		=> $row->lineage ? explode('-', $row->lineage) : NULL,
						'parent_id' 	=> $row->parent_id ? $row->parent_id : NULL,
						'num_children' 	=> $row->num_children
					);
					
					// Add in extra data
					foreach ($this->config->item('hierarchy_' . $table) as $extra_row)
					{
						$this->items[$counter][$extra_row] = $row->$extra_row;
					}
					
					// Advance counter
					$counter++;
				}
				
				return $this->items;
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
	 * Get all items from a provided table and generate hierarchy
	 *
	 * @author Victor Michnowicz
	 * 
	 * @access public
	 * 
	 * @param string		Name of table
	 * 
	 * @return array
	 */
	public function get_hierarchical_list($table, $sort = NULL)
	{
		// If user has NOT  already generated a list of items
		if ( ! $this->items)
		{
			// Get list of all items
			$this->get_list($table, $sort);
			
			// If we don't have any items to display
			if ($this->items == NULL)
			{
				return array();
			}
		}
		
		$count = 0;
		
		$heirarchy = array();
		
		// Loop through all items
		foreach ($this->items as $item)
		{	
			$eval = '$heirarchy';
				
			foreach ($item['lineage'] as $lineage)
			{
				// If this is NOT the first or last element
				if (count($item['lineage']) > 1  AND $count != count($item['lineage']) - 1)
				{
					$eval .= '[' . $lineage . ']' . '["children"]';
				}
				// If this IS the first and/or last element
				else
				{
					$eval .= '[' . $lineage . ']' . '["root"]';
				}
				$count++;
			}
			
			$count = 0;
			
			$eval .= '=$item;';
			
			// There has to be a better way to do this...
			eval($eval);
		}
		
		// Sort it out
		
		function sortify($a, $b)
		{
			// Do some sorting...
		}
		
		// usort($heirarchy, 'sortify');
		
		// Return heirarchial list of all elements
		return $heirarchy;
	}
	
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
	
	public function add_item($table, $data)
	{
		// Make sure the parent_id is set
		if ( ! array_key_exists('parent_id', $data) )
		{
			return FALSE;
		}
		
		// Make sure this item exists in the given table
		$item = $this->item_exists($data['parent_id']);
		
		// If this item does not exist (expect when parent_id is set to NULL)
		if ( ! $item AND $data['parent_id'] != NULL)
		{
			return FALSE;
		}
		
		// Insert Item
		$this->db->insert('hierarchy', array('parent_id' => $data['parent_id']));
		
		// Insert new hierarchy
		$insert_id = $this->db->insert_id();
		
		// Update extra data...
		foreach ($this->config->item('hierarchy_' . $table) as $extra_row)
		{
			$extra_data[$extra_row] = $data[$extra_row];
		}
		
		// Add hierarchy ID to extra data array
		$extra_data['hierarchy_id'] = $insert_id;
		
		// Add item in database
		$this->db->insert($table, $extra_data);
		
		// If a parent ID was provided
		if ($data['parent_id'])
		{
			$item['lineage'][] = $insert_id;
			
			$update_data = array(
				'lineage' 	=> implode('-', $item['lineage']),
				'deep' 		=> $item['deep'] + 1
			);
			
			$this->db
				->where('hierarchy_id', $insert_id)
				->update('hierarchy', $update_data);
				
			// Add extra data
			unset($data['parent_id']);
			
			$data['hierarchy_id'] = $insert_id;
			
			$this->db->insert($table, $data);
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
	}
	
	public function delete_item($hierarchy_id, $delete_children = FALSE)
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
					// if we DO want to delte the children
					if ( $delete_children )
					{
						$this->db->where('hierarchy_id', $row->hierarchy_id)->delete('hierarchy');
					}
					// if we DO NOT want to delte the children (shift left)
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
		}
		else
		{
			return FALSE;
		}
	}
	
}