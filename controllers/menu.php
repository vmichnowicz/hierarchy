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
		$this->menu = new $this->hierarchy;

		// Menu config
		$this->menu
				->table('menu')
				->order_by('hierarchy_order');

		// $this->output->enable_profiler(TRUE);
	}

	function index()
	{
		// Generate menu
		$data['menu'] = $this->menu
			->get_items_array()
			->extra_data(array('elements' => $elements = $this->menu->items_array))
			->generate_hierarchial_list('hierarchy_menu_template', 'ul', 'id="menu"');

		$this->load->view('menu', $data);
	}

	function new_parent($hierarchy_id)
	{
		// Get parent ID
		$parent_id = $this->input->post('parent_id');

		$this->menu->new_parent($hierarchy_id, $parent_id);
	}

	function new_order($hierarchy_id, $new_order)
	{
		$this->menu->new_order($hierarchy_id, $new_order);
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
				$this->output->set_header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
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

	function test()
	{
		var_dump($this->menu->is_ordered) . '<br>';
		echo $this->menu->test();
	}

	function order_increase($hierarchy_id)
	{
		$this->menu->order_increase($hierarchy_id);
	}

	function order_decrease($hierarchy_id)
	{
		$this->menu->order_decrease($hierarchy_id);
	}

	function trans()
	{
		$this->db->trans_begin();

		$this->db->insert("hierarchy", array('deep' => 1));

		$this->db
			->where('hierarchy_id', $this->db->insert_id())
			->update('hierarchy', array('lineage' => $this->db->insert_id()));

		$this->db->query("SELECT * FROM asdf");
		
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
	    {
			echo 'nooooo';
			$this->db->trans_rollback();
			exit('this did not work');
			return FALSE;
	    }

		echo 'it workesd';

	    $this->db->trans_commit();
	}

}