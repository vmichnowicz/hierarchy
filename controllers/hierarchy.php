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
		
		// TO-DO
		$list_item = '<a href="{link}">{title}</a> count: {count}';
		$paginate; // for comments...
		
		// Generate list
		$list = $this->hierarchy_model->get_hierarchical_list($table, $sort);
		
		$data['menu'] = $list;
		
		$this->load->view('hierarchy', $data);
	}
	
	function add()
	{
		$data = array(
			'parent_id' 	=> 11,
			'title' 		=> 'Used'
		);
	}
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/hierarchy.php */