<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Menu extends CI_Controller {

	public $menu;

	function __construct()
	{
		parent::__construct();

		// Load libraries
		$this->load->library('hierarchy');
		$this->load->library('form_validation');

		// Menu
		$this->menu = $this->hierarchy;

		// Menu config
		$this->menu
			->table('menu')
			->order_by('hierarchy_order');
	}

	function index()
	{
		// Generate menu
		$data['menu'] = $this->menu
			->get_items_array()
			->extra_data(array('elements' => $elements = $this->menu->items_array))
			->generate_hierarchial_list('hierarchy_menu_template', 'ul', 'id="menu"');

		// Grab parent ID (Only needed if user has JavaScript disabled)
		$data['parent'] = $this->menu->item_exists($this->input->get('parent_id'));

		$this->load->view('menu', $data);
	}

	function new_parent($hierarchy_id)
	{
		// Get parent ID
		$parent_id = $this->input->post('parent_id');

		$this->menu->new_parent($hierarchy_id, $parent_id);

		redirect('menu#hierarchy_id_' . $hierarchy_id);
	}

	function reorder($order_by = 'lineage', $order_by_order = 'ASC')
	{
		$this->menu
			->order_by($order_by)
			->order_by_order($order_by_order)
			->reorder();

		redirect('menu');
	}

	function delete($hierarchy_id, $delete_children = TRUE)
	{
		$this->menu->delete_item($hierarchy_id, $delete_children);
	}

	function add_random($num)
	{
		$random_words = array('a', 'random', 'hierarchy', 'test', 'and', 'or', 'php', 'mysql', 'php', 'javascript', 'xhtml', 'also', 'with', 'technology', 'master', 'code', 'menu', 'threaded', 'codeigniter');
		$num = (int)$num;

		for ($i = 0; $i <= $num; $i++)
		{
			$title = '';

			$random_length = rand(1, 8);
			for ($r = 0; $r <= $random_length; $r++)
			{
				$title .= ' ' . $random_words[ rand(1, count($random_words)) - 1 ];
			}

			$url = str_replace(' ', '/', $title);
			
			$query = $this->db
				->join('menu', 'menu.hierarchy_id = hierarchy.hierarchy_id')
				->order_by('parent_id', 'RANDOM')
				->limit(1)
				->get('hierarchy');

			if ($query->num_rows() > 0)
			{
				$row = $query->row();

				$parent = $row->hierarchy_id;
			}
			else
			{
				$parent = NULL;
			}

			$data = array(
				'parent_id'		=> $parent,
				'title'			=> $title,
				'url'			=> $url
			);


			$this->menu->add_item($data);
		}
	}

	function add()
	{
		if ( ! $_POST)
		{
			redirect('hierarchy_demo');
		}

		// Form validation rules
		$config = array(
			array(
				'field' => 'parent_id',
				'label' => 'Parent ID',
				'rules' => 'trim|integer|callback_valid_parent'
			),
			array(
				'field' => 'title',
				'label' => 'Title',
				'rules' => 'trim|required|min_length[1]'
			),
			array(
				'field' => 'URL',
				'label' => 'URL',
				'rules' => 'trim'
			)
		);

		$this->form_validation->set_rules($config);

		// If form validation was successful
		if ($this->form_validation->run())
		{
			$data = array(
				'parent_id' 	=> $this->input->post('parent_id') ? $this->input->post('parent_id') : NULL,
				'title' 		=> $this->input->post('title'),
				'url' 			=> $this->input->post('url')
			);

			$this->menu->add_item($data);

			// If this is an AJAX request
			if ($this->input->is_ajax_request())
			{
				echo json_encode(array('result', 'success'));
			}

			// If this is not an AJAX request
			else
			{
				redirect('menu#hierarchy_id_' . $this->db->insert_id);
			}
		}
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
				$this->output->set_header('Expires: Sat, 1 Jan 2000 12:00:00 GMT');
				$this->output->set_header('Content-type: application/json');

				// Echo out JSON error messages
				echo json_encode($data);
			}
			else
			{
				echo validation_errors();
			}
		}
	}

	function valid_parent($parent_id)
	{
		// If a parent ID was provided
		if ($parent_id)
		{
			if ( ! $this->menu->item_exists($parent_id, $this->menu->table) )
			{
				$this->form_validation->set_message('valid_parent', 'Invalid parent selected.');
				return FALSE;
			}
			else
			{
				return TRUE;
			}
		}
		// If no parent ID was provided
		else
		{
			return TRUE;
		}
	}

	function order_increase($hierarchy_id)
	{
		$this->menu->order_increase($hierarchy_id);

		redirect('menu#hierarchy_id_' . $hierarchy_id);
	}

	function order_decrease($hierarchy_id)
	{
		$this->menu->order_decrease($hierarchy_id);

		redirect('menu#hierarchy_id_' . $hierarchy_id);
	}

}