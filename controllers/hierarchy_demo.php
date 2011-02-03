<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Hierarchy_demo extends CI_Controller {
	
	public $comment_config;
	
	function __construct()
	{
		parent::__construct();
		
		// Load models
		$this->load->model('hierarchy_model');
		
		// Load libraries
		$this->load->library('hierarchy');
		$this->load->library('form_validation');
		
		// Load helpers
		$this->load->helper('url');
	}

	function index()
	{		 
		$menu = new $this->hierarchy;		
		$comments = new $this->hierarchy;
		
		//$extra_data = $menu->items_array;
		
		// Generate menu
		$data['menu'] = $menu
			->table('menu')
			->order_by('hierarchy_order')
			->get_items_array()
			->extra_data(array('elements' => $menu->items_array))
			->generate_hierarchial_list('hierarchy_menu_template', 'ul', 'id="menu"');
		
		$data['comments'] = $comments
			->table('comments')
			->generate_hierarchial_list('hierarchy_comments_template', 'ul', 'id="comments"');
		
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
	
	function new_parent($table, $hierarchy_id)
	{
		$parent_id = $this->input->post('parent_id');
		$this->hierarchy
				->table($table)
				->new_parent($hierarchy_id, $parent_id);
	}
	
	function new_order($hierarchy_id, $new_order)
	{
		$this->hierarchy->table('menu')->new_order($hierarchy_id, $new_order);
	}
	
	function reorder($table, $order_by = 'lineage', $order_by_order = 'ASC')
	{
		$this->hierarchy
			->table($table)
			->order_by($order_by)
			->order_by_order($order_by_order)
			->reorder();
	}
	
	function test($test)
	{
		echo $this->hierarchy_model->test($test);
	}
	
	function delete($hierarchy_id, $delete_children = TRUE)
	{
		$this->hierarchy_model->delete_item($hierarchy_id, $delete_children);
	}
	
	function valid_url($url)
	{
		// If this field has a value
		if ( $url )
		{
			if (filter_var($url, FILTER_VALIDATE_URL))
			{
				return TRUE;
			}
			else
			{
				
				$this->form_validation->set_message('valid_url', 'Invalid %s');
				return FALSE;
			}
		}
		else
		{
			return TRUE;
		}
	}
	
	function add_comment()
	{

		if ( ! $_POST)
		{
			redirect('hierarchy_demo');
		}
		
		// Form validation rules
		$this->comment_config = array(
			array(
				'field' => 'title',
				'label' => 'Title',
				'rules' => 'trim|required'
			),
			array(
				'field' => 'comment',
				'label' => 'Comment',
				'rules' => 'trim|required|min_length[10]'
			),
			array(
				'field' => 'author',
				'label' => 'Name',
				'rules' => 'trim|required'
			),
			array(
				'field' => 'email',
				'label' => 'Email',
				'rules' => 'trim|required|valid_email'
			),
			array(
				'field' => 'url',
				'label' => 'URL',
				'rules' => 'trim|prep_url|callback_valid_url'
			)
		);
		
		$this->form_validation->set_rules($this->comment_config);
		
		
		// If form validation was successful
		if ($this->form_validation->run())
		{	
			$data = array(
				'parent_id' => $this->input->post('parent_id') ? $this->input->post('parent_id') : NULL,
				'title' 	=> $this->input->post('title'),
				'comment' 	=> $this->input->post('comment'),
				'author' 	=> $this->input->post('author'),
				'email' 	=> $this->input->post('email'),
				'url' 		=> $this->input->post('url'),
				'timestamp' => time()
			);
			
			// Add comment
			$this->hierarchy
				->table('comments')
				->add_item($data);
					
			// If this is an AJAX request
			if ($this->input->is_ajax_request())
			{
				echo json_encode(array('result', 'success'));
			}
			
			// If this is not an AJAX request
			else
			{
				redirect('hierarchy_demo#comment_id_' . $this->db->insert_id);
			}
		}
		
		// If page was not POSTed to or form validation failed
		else
		{
			// If this is an AJAX request
			if ($this->input->is_ajax_request())
			{
				$data = array(
					'result' => 'failure',
					'errors' => $this->form_validation->_error_array
				);
				
				// Set headers
				$this->output->set_header('Cache-Control: no-cache, must-revalidate');
				$this->output->set_header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
				$this->output->set_header('Content-type: application/json');
				
				// Echo out JSON error messages
				echo json_encode($data);
			}
			
			// This is not an AJAX request
			else
			{
				// Show error messages
				echo validation_errors();
			}
		}

	}
}

/* End of file hierarchy_demo.php */
/* Location: ./application/controllers/hierarchy_demo.php */