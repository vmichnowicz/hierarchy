<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Hierarchy extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		
		// Load models
		$this->load->model('hierarchy_model');
	}

	function index()
	{
		$list = $this->hierarchy_model->get_hierarchical_list('menu');
		
		$data['menu'] = $list;
		
		$this->load->view('hierarchy', $data);
	}
	
	function add()
	{
		$data = array(
			'parent_id' 	=> 11,
			'title' 		=> 'Used'
		);
		
		//$this->hierarchy_model->add_item($data, 'menu');
	}
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */