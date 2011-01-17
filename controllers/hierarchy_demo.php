<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Hierarchy_demo extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		
		// Load models
		$this->load->model('hierarchy_model');
		
		// Load libraries
		$this->load->library('hierarchy');
	}

	function index()
	{
		// Do nothing...
	}
	
	function manage($table, $order_by = 'lineage', $order_by_order = NULL)
	{	
		// TO-DO - paginate (for comments perhaps...)
		
		// Generage menu
		$data['menu'] = $this->hierarchy
			->table('menu')
			->order_by($order_by)
			->order_by_order($order_by_order)
			->get_hierarchical_items_array()
			->generate_hierarchial_list('hierarchy_template', 'ul', 'id="menu"');
		
		$this->load->view('hierarchy', $data);
	}
	
	function add($table)
	{
		
		$data = array(
			'parent_id' 	=> 18,
			'title' 		=> 'Mexico',
			'url' 			=> 'contact/advertising/tv'
		);
		
		$this->hierarchy
			->table($table)
			->add_item($data);
	}
	
	function item_lineage($hierarchy_id, $table)
	{
		print_r(
			$this->hierarchy
				->table($table)
				->item_lineage($hierarchy_id)
			);
	}
	
	function shift_left($hierarchy_id)
	{
		$this->hierarchy_model->shift_left($hierarchy_id);
	}
	
	function new_parent($hierarchy_id, $parent_id)
	{
		$this->hierarchy_model->new_parent($hierarchy_id, $parent_id);
	}
	
	function new_order($hierarchy_id, $new_order)
	{
		$this->hierarchy->table('menu')->new_order($hierarchy_id, $new_order);
	}
	
	function reorder($table)
	{
		$this->hierarchy->table($table)->reorder();
	}
	
	function test($test)
	{
		echo $this->hierarchy_model->test($test);
	}
	
	function delete($hierarchy_id, $delete_children = FALSE)
	{
		$this->hierarchy_model->delete_item($hierarchy_id, $delete_children);
	}
}

/* End of file hierarchy_demo.php */
/* Location: ./application/controllers/hierarchy_demo.php */