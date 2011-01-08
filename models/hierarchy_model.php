<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Hierarchy_model extends CI_Model {
	
	public $items = NULL;
	
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
	public function get_list($table = NULL)
	{
		if ($table)
		{
			// Run query
			$query = $this->db->query("
				SELECT *,
					(
						SELECT COUNT(*)
						FROM menu
						WHERE menu.lineage LIKE ( CONCAT(h.lineage,'%') ) AND menu.lineage != h.lineage
					) AS num_children
				FROM $table as h
				ORDER BY h.lineage
			");
			
			// If we got some results
			if ($query->num_rows() > 0)
			{
				foreach ($query->result() as $row)
				{
					$this->items[$row->id] = array(
						'id' 			=> $row->id,
						'title' 		=> $row->title,
						'deep' 			=> $row->deep,
						'lineage' 		=> $row->lineage ? explode('-', $row->lineage) : NULL,
						'parent_id' 	=> $row->parent_id ? $row->parent_id : NULL,
						'num_children' 	=> $row->num_children
					);
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
	public function get_hierarchical_list($table)
	{
		// If user has NOT  already generated a list of items
		if ( ! $this->items)
		{
			// Get list of all items
			$this->get_list($table);
			
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
				if (count($item['lineage']) > 1  AND $count != count($item['lineage']) - 1)
				{
					
					$eval .= '[' . $lineage . ']' . '["children"]';
				}
				else
				{
					$eval .= '[' . $lineage . ']';
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
			echo $a['title'] . $b['title'];
		}
		
		usort($heirarchy, 'sortify');
		
		// Return heirarchial list of all elements
		return $heirarchy;
	}
	
	public function item_exists($id, $table)
	{
		$query = $this->db
			->where('id', $id)
			->limit(1)
			->get($table);
		
		if ($query->num_rows() > 0)
		{
			$row = $query->row(); 
			
			$data = array(
				'id' 		=> $row->id,
				'parent_id' => $row->parent_id,
				'lineage' 	=> explode('-', $row->lineage),
				'deep' 		=> $row->deep
			);
			
			return $data;
		}
		else
		{
			return FALSE;
		}
	}
	
	public function add_item($data, $table)
	{
		// Make sure the parent_id is set
		if ( ! isset($data['parent_id']) )
		{
			return FALSE;
		}
		
		// Make sure this item exists in the given table
		$item = $this->item_exists($data['parent_id'], $table);
		
		if ( ! $item )
		{
			return FALSE;
		}

		$this->db->insert($table, $data);
		
		$insert_id = $this->db->insert_id();
		
		$item['lineage'][] = $insert_id;
		
		$data = array(
			'lineage' 	=> implode('-', $item['lineage']),
			'deep' 		=> $item['deep'] + 1
		);
		
		$this->db
			->where('id', $insert_id)
			->update($table, $data);
	}
	
}