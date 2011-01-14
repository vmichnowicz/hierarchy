<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Hierarchy_demo extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		
		// Load models
		$this->load->model('hierarchy_model');
		
		// Load helpers
		$this->load->helper('hierarchy');
	}

	function index()
	{
		// Do nothing...
	}
	
	function manage($table = NULL, $order_by = NULL)
	{	
		// Make sure we have a table
		if ( ! $table )
		{
			redirect('hierarchy');
		}
		
		// TO-DO - paginate (for comments perhaps...)
		
		// Attributes
		$attributes = 'id="menu"';
		
		// Generate list
		$list = $this->hierarchy_model->get_hierarchical_list($table, 'lineage', 'ASC');
		
		// Menu template
		$template = '<div class="item deep_{deep}"><a href="{url}" title="{hierarchy_id}">{title}</a></div>';
		
		$data['menu'] = hierarchical_ul($list, $template, $attributes);
		//$data['menu'] = hierarchical_ul($list, 'hierarchy_template', $attributes);
		
		//$this->load->view('hierarchy', $data);
		
		
	}
	
	function add($table)
	{
		
		$data = array(
			'parent_id' 	=> 24,
			'title' 		=> 'Advertising',
			'url' 			=> 'contact/advertising'
		);
		
		$this->hierarchy_model->add_item($table, $data);
	}
	
	function shift_left($hierarchy_id)
	{
		$this->hierarchy_model->shift_left($hierarchy_id);
	}
	
	function new_parent($hierarchy_id, $parent_id)
	{
		echo '<pre>';
		$this->hierarchy_model->new_parent($hierarchy_id, $parent_id);
		
	}
	
	function delete($hierarchy_id, $delete_children = FALSE)
	{
		$this->hierarchy_model->delete_item($hierarchy_id, $delete_children);
	}
}

/* End of file hierarchy_demo.php */
/* Location: ./application/controllers/hierarchy_demo.php */