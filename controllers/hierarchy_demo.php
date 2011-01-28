<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Hierarchy_demo extends CI_Controller {

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
		
		// Generage menu
		$data['menu'] = $menu
			->table('menu')
			->order_by('hierarchy_order')
			->generate_hierarchial_list('hierarchy_template', 'ul', 'id="menu"');
		
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
	
	function new_parent($hierarchy_id, $parent_id)
	{
		$this->hierarchy_model->new_parent($hierarchy_id, $parent_id);
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
	
	function delete($hierarchy_id, $delete_children = FALSE)
	{
		$this->hierarchy_model->delete_item($hierarchy_id, $delete_children);
	}
	
	function url_check($url)
	{
		if (filter_var('example.com', FILTER_VALIDATE_URL))
		{
			return TRUE;
		}
		else
		{
			$this->form_validation->set_message('url_check', 'Invalid %s');
			return FALSE;
		}
	}
	
	function add_comment()
	{

		// Form validation rules
		$config = array(
			array(
				'filed' => 'title',
				'label' => 'Title',
				'rules' => 'trim|required'
			),
			array(
				'filed' => 'comment',
				'label' => 'Comment',
				'rules' => 'trim|required|min_length[10]'
			),
			array(
				'filed' => 'author',
				'label' => 'Author',
				'rules' => 'trim|required'
			),
			array(
				'filed' => 'email',
				'label' => 'Email',
				'rules' => 'trim|required|valid_email'
			),
			array(
				'filed' => 'url',
				'label' => 'URL',
				'rules' => 'callback_valid_url'
			)
		);
		
		$data = array(
			'parent_id' => $this->input->post('parent_id') ? $this->input->post('parent_id') : NULL,
			'title' 	=> htmlentities($this->input->post('title')),
			'comment' 	=> htmlentities($this->input->post('comment')),
			'author' 	=> htmlentities($this->input->post('name')),
			'email' 	=> htmlentities($this->input->post('email')),
			'url' 		=> htmlentities($this->input->post('url')),
			'timestamp' => time()
		);
		

		
		// If form validation was successful
		if ($this->form_validation->run())
		{
		
			// Add comment
			$this->hierarchy
				->table('comments')
				->add_item($data);
					
			// If this is an AJAX request
			if ($this->input->is_ajax_request())
			{
						
			}
			
			// If this is not an AJAX request
			else
			{
				echo 'comment added!';
			}
		}
		
		// If page was not POSTed to or form validation failed
		else
		{
			// If form validation was successful
			if ($this->form_validation->run())
			{
				// Add comment
				$this->hierarchy
					->table('comments')
					->add_item($data);
			}
			
			// If form validation failed (or was not run)
			else
			{
				redirect('hierarchy_demo');
			}
		}

		
	}
}

/* End of file hierarchy_demo.php */
/* Location: ./application/controllers/hierarchy_demo.php */