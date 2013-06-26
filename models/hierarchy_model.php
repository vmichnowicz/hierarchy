<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Hierarchy_model extends CI_Model {

	public $table;

	/**
	 * Get all items from a provided table and generate an array
	 *
	 * @access public
	 * @param string		Name of table
	 * @param string		Name of row to order by
	 * @param string		Order ASC or DESC
	 * @return array
	 */
	public function get_items_array($table, $order_by, $order_by_order, $parent_id = NULL)
	{
		if ($table)
		{
			$parent_id = ( is_int($parent_id) || ctype_digit($parent_id) ) ? (int)$parent_id : NULL;

			$query = "
				SELECT *,
					(
						SELECT COUNT(*)
						FROM hierarchy
						WHERE hierarchy.lineage LIKE (CONCAT(h.lineage,'-%')) AND hierarchy.lineage != h.lineage
					) AS num_children
				FROM
					hierarchy as h,
					$table as j
				WHERE h.hierarchy_id = j.hierarchy_id";

			if ($parent_id)
			{
				$query .= " AND ( h.lineage LIKE '{$parent_id}-' OR h.lineage = $parent_id ) ";
			}

			$query .= " ORDER BY deep ASC, $order_by $order_by_order";

			$result = $this->db->query($query);

			// If we got some results
			if ($result->num_rows() > 0)
			{
				// Keep a counter so we can add in extra data later
				$counter = 1;

				foreach ($result->result() as $row)
				{
					$items_array[$row->hierarchy_id] = array(
						'hierarchy_id' 	=> $row->hierarchy_id,
						'deep' 			=> $row->deep,
						'lineage' 		=> explode('-', $row->lineage),
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
	 * @access public
	 * @param array			Array of items
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
					$eval .= '[' . $items_array[$lineage]['surrogate_id'] . ']' . '["children"]';
				}
				else
				{
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
	 * @access public
	 * @param int			Item ID
	 * @param string		Table name (optional)
	 * @return mixed
	 */
	public function item_exists($hierarchy_id, $table = NULL)
	{
		// If a table name was provided
		if ($table)
		{
			$query = $this->db
				->where('hierarchy.hierarchy_id', $hierarchy_id)
				->join($table, $table . '.hierarchy_id = hierarchy.hierarchy_id')
				->limit(1)
				->get('hierarchy');

			if ($query->num_rows() > 0)
			{
				$row = $query->row();

				// Get all data from query and place it in data array
				$data = $query->row_array();

				// Make our lineage an array
				$data['lineage'] = explode('-', $row->lineage);

				// Return data array
				return $data;
			}
			else
			{
				return FALSE;
			}
		}

		// If no table name was provided
		else
		{
			$query = $this->db
				->where('hierarchy_id', $hierarchy_id)
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
	}

	/**
	 * Add a hierarchy item
	 *
	 * @access public
	 * @param array			Item data
	 * @param string		Extra table name
	 * @param bool			Is this table ordered?
	 * @return bool
	 */
	public function add_item($data, $table, $is_ordered)
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

		$this->db->trans_start();

		// If we want to set an order
		if ($is_ordered)
		{
			$data['hierarchy_order'] = $this->highest_order($data['parent_id'], $table) + 1;
		}

		// Insert item into hierarchy table
		$this->db->insert('hierarchy', array('parent_id' => $data['parent_id']));

		// Get insert ID
		$insert_id = $this->db->insert_id();

		// Update extra data array
		foreach ($this->config->item('hierarchy_' . $table) as $extra_row)
		{
			$extra_data[$extra_row] = $data[$extra_row];
		}

		// Add hierarchy ID to extra data array
		$extra_data['hierarchy_id'] = $insert_id;

		// Add extra data into linked table
		$this->db->insert($table, $extra_data);

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

		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	/**
	 * Delete a hierarchy item
	 *
	 * @access public
	 * @param int			Item ID
	 * @param bool			Shall we delete all of this items children?
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
			$this->db->trans_start();
			
			$row = $query->row();

			$parent_id = $row->parent_id;
			$lineage = $row->lineage;

			$query = $this->db
				->where('lineage LIKE', $lineage . '-%')
				->order_by('parent_id', 'DESC') // Foreign key constraints strike again
				->order_by('hierarchy_id', 'DESC') // Foreign key constraints strike again
				->get('hierarchy');

			// If we have children
			if ($query->num_rows() > 0)
			{

				$order = $this->highest_order($parent_id, $this->table);

				foreach ($query->result() as $row)
				{
					// If we DO want to delte the children
					if ( $delete_children )
					{
						$this->db->where('hierarchy_id', $row->hierarchy_id)->delete('hierarchy');
					}
					
					// If we DO NOT want to delete the children (shift left)
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

			$this->db->trans_complete();

			return $this->db->trans_status();
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
	 * @access public
	 * @param int			Item ID
	 * @param string		Table name
	 * @param bool			Is this table ordered?
	 * @return bool
	 */
	public function shift_left($hierarchy_id, $table, $is_ordered)
	{
		// Get item info and make sure this item really exists
		if ($item = $this->item_exists($hierarchy_id))
		{
			// Make sure this is not a root element (root elements can not be shifted left)
			if ( ! $parent = $this->item_exists($item['parent_id']) )
			{
				return FALSE;
			}

			// Get the parent ID of the parent element
			$parent_parent_id = $parent['parent_id'] ? $parent['parent_id'] : NULL;

			// Get all items that share this lineage (that includes the item with the ID passed thru)
			$query = $this->db
				->where('lineage LIKE', implode('-', $item['lineage']) . '-%')
				->or_where('lineage LIKE', '%-' . $item['hierarchy_id'])
				->or_where('lineage', $item['hierarchy_id'])
				->order_by('hierarchy_id', 'DESC') // Foreign key constraints strike again
				->get('hierarchy');

			$next_order = $is_ordered ? $this->highest_order($parent_parent_id, $table) + 1 : NULL;

			// Loop through each result (we are guaranteed at least one result)
			foreach ($query->result() as $row)
			{
				$lineage_array = explode('-', $row->lineage);

				/*
				 * Remove parent id from lineage array
				 *
				 * 1
				 * -2
				 * --3
				 * ---4
				 *
				 * Assume we are shifting element 3 left. Element 3s current
				 * lineage is 1-2-3. This will make it so it is 1-3. Resulting
				 * in:
				 *
				 * 1
				 * -2
				 * -3
				 * --4
				 */
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

				// maybe just $row->deep - 1 ?
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
		// This item does not exist
		else
		{
			return FALSE;
		}
	}

	/**
	 * Give an element a new parent (and bring all its children along with it)
	 *
	 * @access public
	 * @param int			Item ID
	 * @param int 			New parent ID
	 * @param string		Table to join with
	 * @param bool			Is this hierarchy group ordered?
	 * @return bool
	 */
	public function update_item_parent($hierarchy_id, $parent_id, $table, $is_ordered)
	{
		if ( ! $item = $this->item_exists($hierarchy_id) )
		{
			return FALSE;
		}

		$parent = $parent_id ? $this->item_exists($parent_id) : NULL;

		// Can not be its own parent
		if ($hierarchy_id === $parent_id)
		{
			return FALSE;
		}

		// Next, we have to see if the new parent is in this items lineage
		$lineage = $this->item_lineage($parent_id, $table);

		// If our new parent is already in this current items lineage
		if ( array_key_exists($hierarchy_id, $lineage) )
		{
			$this->update_item_parent_related($hierarchy_id, $item, $parent_id, $parent, $table, $is_ordered);
		}
		// If our new parent is not in this items lineage
		else
		{
			$this->update_item_parent_unrelated($hierarchy_id, $item, $parent_id, $parent, $table, $is_ordered);
		}
	}

	/**
	 * Update an items parent when the new parent is witin the items current
	 * lineage
	 *
	 * @access protected
	 * @param int			Item ID
	 * @param array			Item
	 * @param int 			New parent ID
	 * @param array 		New parent
	 * @param string		Table to join with
	 * @param bool			Is this hierarchy group ordered?
	 * @return bool
	 * @todo				Put on thinking cap & make this work...
	 */
	protected function update_item_parent_related($hierarchy_id, $item, $parent_id, $parent, $table, $is_ordered)
	{
		return TRUE;
	}

	/**
	 * Update an items parent when the new parent is not witin the items current
	 * lineage
	 *
	 * @access protected
	 * @param int			Item ID
	 * @param array			Item
	 * @param int 			New parent ID
	 * @param array 		New parent
	 * @param string		Table to join with
	 * @param bool			Is this hierarchy group ordered?
	 * @return bool
	 */
	protected function update_item_parent_unrelated($hierarchy_id, $item, $parent_id, $parent, $table, $is_ordered)
	{
		$this->db->trans_start();

		// Get all items that share this lineage (that includes the item with the ID passed through)
		$query = $this->db
			->where('lineage LIKE', implode('-', $item['lineage']) . '-%')
			->or_where('lineage LIKE', '%-' . $item['hierarchy_id'])
			->or_where('lineage', $item['hierarchy_id'])
			->order_by('hierarchy_id', 'DESC') // Foreign key constraints strike again
			->get('hierarchy');

		// Loop through each result (we are guaranteed at least one result)
		foreach ($query->result() as $row)
		{
			// Explode our lineage array for this item
			$lineage_array = explode('-', $row->lineage);

			// If a parent was provided, grab its lineage, if not, set as empty array
			$parent_lineage = isset($parent['lineage']) ? $parent['lineage'] : array();

			/*
			 * Create updated lineage
			 *
			 * Merge the parent's lineage with a slice of the child's lineage.
			 * This slice "subtracts" the parents lineage from the childs.
			 *
			 * EXAMPLE:
			 *
			 * 1
			 * - 5
			 * - - 9 (our new parent)
			 * 2
			 * - 3 (we are moving this along with all children below)
			 * - - 4
			 *
			 * Our new parent has a lineage of 1-5-9.
			 * We are moving element 3 along with all of its decendents.
			 * Element 3 had a lineage of 2-3 (Therefore it is 1 deep).
			 *
			 * Our foreach loop get all elements that have 3 in their lineage.
			 * This will return elements 3 and 4 in our example.
			 *
			 * Lets assume we are on element 4 in our foreach loop.
			 * Element 4 has a lineage is 2-3-4.
			 *
			 * We create a slice of this lineage by using the deep value of
			 * element 3 (1) as an offset. This removes the first part of our
			 * array and leaves us with a lineage of 3-4. We then add this
			 * lineage to our new parents lineage (1-5-9) resulting in a new
			 * lineage of 1-5-9-3-4 for element 4.
			 *
			 * That made sense, right?
			 */

			$new_lineage = array_merge($parent_lineage, array_slice($lineage_array, $item['deep']));

			$data = array(
				'lineage' => implode('-', $new_lineage),
				'deep' =>count($new_lineage) - 1,
			);

			// Update elements
			$this->db
				->where('hierarchy_id', $row->hierarchy_id)
				->update('hierarchy', $data);
		}

		// If we want to set an order
		if ($is_ordered)
		{
			$data = array(
				'hierarchy_order' => $this->highest_order($parent_id, $table) + 1
			);

			// Update table
			$this->db
				->where('hierarchy_id', $hierarchy_id)
				->update($table, $data);
		}

		$data = array(
			'parent_id' => $parent_id ? $parent_id : NULL
		);

		// Update main hierarchy element
		$this->db
			->where('hierarchy_id', $hierarchy_id)
			->update('hierarchy', $data);

		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	/**
	 * Generate full item lineage
	 *
	 * @access public
	 * @param int			Item ID
	 * @param string		Table to join with
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

			foreach ($query->result() as $row)
			{
				$data[ $row->hierarchy_id ] = array(
					'hierarchy_id' 	=> $row->hierarchy_id,
					'deep' 			=> $row->deep,
					'lineage' 		=> $row->lineage ? explode('-', $row->lineage) : NULL,
					'parent_id' 	=> $row->parent_id ? $row->parent_id : NULL,
				);

				// Add in extra data
				foreach ($this->config->item('hierarchy_' . $table) as $extra_row)
				{
					$data[ $row->hierarchy_id ][$extra_row] = $row->$extra_row;
				}
			}

			return $data;
		}
	}

	/**
	 * Give an element a new order
	 *
	 * @access public
	 * @param int			Item ID
	 * @param int 			New order
	 * @param string		Table to join with
	 * @return bool
	 */
	public function new_order($hierarchy_id, $new_order, $table)
	{
		// Get element
		$query = $this->db
			->join($table, $table . '.hierarchy_id = hierarchy.hierarchy_id')
			->where('hierarchy.hierarchy_id', $hierarchy_id)
			->limit(1)
			->get('hierarchy');

		// If this item exists
		if ($query->num_rows() > 0)
		{
			$row = $query->row();

			$parent_id = $row->parent_id;
			$current_order = $row->hierarchy_order;

			// If the new order is the same as the old
			if ($current_order == $new_order)
			{
				return TRUE;
			}

			// The lowest order must come first for our BETWEEN SQL
			if ($new_order > $current_order)
			{
				$order_low = $current_order;
				$order_high = $new_order;
			}
			else
			{
				$order_low = $new_order;
				$order_high = $current_order;
			}

			$this->db->trans_start();

			// Get all elements with an order between our old and new orders
			$orders = $this->db
				->join($table, $table . '.hierarchy_id = hierarchy.hierarchy_id')
				->where("`hierarchy_order` BETWEEN $order_low AND $order_high", NULL, FALSE)
				->where('parent_id', $parent_id)
				->get('hierarchy');

			foreach ($orders->result() as $row)
			{
				// If this is the element that we want to move
				if ($row->hierarchy_order == $current_order)
				{
					$this->db
						->where('hierarchy_id', $row->hierarchy_id)
						->update($table, array('hierarchy_order' => $new_order));
				}
				// If this is not the element that we want to move (we still need to updated its order)
				else
				{
					// If our new order is greater than its previous order (shift elements up)
					if ($new_order > $current_order)
					{
						$this->db
							->where('hierarchy_id', $row->hierarchy_id)
							->update($table, array('hierarchy_order' => $row->hierarchy_order - 1));
					}
					// If our new order is less than its previous order (shift elements down)
					else
					{
						$this->db
							->where('hierarchy_id', $row->hierarchy_id)
							->update($table, array('hierarchy_order' => $row->hierarchy_order + 1));
					}

				}
			}

			$this->db->trans_complete();

			return $this->db->trans_status();
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Reorder all hierarchy items of a given group
	 *
	 * @access public
	 * @param string		Table column to order by
	 * @param string		ASC or DESC
	 * @param string		Table to join with (hierarchy_order is stored here)
	 * @return bool
	 */
	public function reorder($order_by, $order_by_order, $table)
	{
		// Get all items
		$query = $this->db
			->order_by($order_by, $order_by_order)
			->join($table, $table . '.hierarchy_id = hierarchy.hierarchy_id')
			->get('hierarchy');

		if ($query->num_rows() > 0)
		{
			$this->db->trans_start();

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
						->update($table, array('hierarchy_order' => $key));
				}
			}

			$this->db->trans_complete();

			return $this->db->trans_status();
		}
	}

	/*
	 * Get the highest order for a set of items sharing the same parent
	 *
	 * If we have items with orders 1,2, and 3 this will return 3
	 *
	 * @param int			Hierarchy ID of parent element
	 * @param string		Table name
	 * @return int
	 */
	public function highest_order($parent_id, $table)
	{
		$query = $this->db
				->join($table, $table . '.hierarchy_id = hierarchy.hierarchy_id')
				->order_by('hierarchy_order', 'DESC')
				->where('parent_id', $parent_id)
				->limit(1)
				->get('hierarchy');

		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->hierarchy_order;
		}
	}

	/*
	 * Get next highest order for a given order and parent ID
	 *
	 * The next order for item with an order of 2 in set
	 * of orders like 0,1,2,3,4 would be 3
	 *
	 * @param int			Order
	 * @param int			ID of parent element (or NULL if root element)
	 * @param string		Table name
	 * @return int
	 */
	public function next_order($order, $parent_id, $table)
	{
		$query = $this->db
			->join($table, $table . '.hierarchy_id = hierarchy.hierarchy_id')
			->where('hierarchy_order >', $order)
			->where('parent_id', $parent_id)
			->order_by('hierarchy_order', 'ASC')
			->limit(1)
			->get('hierarchy');

		// If there is an item with a higher order
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->hierarchy_order;
		}
		// We are on the element with the highest order
		else
		{
			return FALSE;
		}
	}

	/*
	 * Get next lowest order for a given order and parent ID
	 *
	 * The previous order for item with an order of 2 in set
	 * of orders like 0,1,2,3,4 would be 1
	 *
	 * NOTE: The lowest order will not always be 0
	 *
	 * @param int			Order
	 * @param int			ID of parent element (or NULL if root element)
	 * @param string		Table name
	 * @return int
	 */
	public function prev_order($order, $parent_id, $table)
	{
		$query = $this->db
			->join($table, $table . '.hierarchy_id = hierarchy.hierarchy_id')
			->where('hierarchy_order <', $order)
			->where('parent_id', $parent_id)
			->order_by('hierarchy_order', 'DESC')
			->limit(1)
			->get('hierarchy');

		// If there is an item with a lower order
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			return $row->hierarchy_order;
		}
		// We are on the element with the lowest order
		else
		{
			return FALSE;
		}
	}

}
