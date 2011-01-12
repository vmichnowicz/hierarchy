<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Hierarchy extends CI_Controller {

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
	
	function manage($table = NULL, $sort = NULL)
	{
		// Make sure we have a table
		if ( ! $table )
		{
			redirect('hierarchy');
		}
		
		// TO-DO - paginate (for comments perhaps...)
		
		// Generate list
		$list = $this->hierarchy_model->get_hierarchical_list($table, $sort);
		
		// Menu template
		$template = '<a href="{url}">{title}</a> id:{hierarchy_id}';
		
		$data['menu'] = hierarchical_ul($list, $template);
		
		$this->load->view('hierarchy', $data);
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

/* End of file welcome.php */
/* Location: ./application/controllers/hierarchy.php */