<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Comments extends CI_Controller {

	/*
	 * Construct
	 */
	function __construct()
	{
		parent::__construct();

		// Load libraries
		$this->load->library('hierarchy');
		$this->load->library('form_validation');
	}

	/*
	 * Load main view
	 */
	function index()
	{
		$comments = new $this->hierarchy;

		$data['comments'] = $comments
			->table('comments')
			->generate_hierarchial_list('hierarchy_comments_template', 'ul', 'id="comments"');

		$this->load->view('comments', $data);
	}

	/*
	 * Reply to a comment (used as a no JavaScript fallback)
	 *
	 * @param int		Comment ID
	 */
	function reply($hierarchy_id)
	{
		$query = $this->db
			->where('hierarchy_id', $hierarchy_id)
			->get('comments');

		if ($query->num_rows() > 0)
		{
			$data['comment'] = $query->row_array();

			$this->load->view('comment_reply', $data);
		}
	}

	/*
	 * Add a comment
	 */
	function add()
	{

		if ( ! $_POST)
		{
			redirect('hierarchy_demo');
		}

		// Form validation rules
		$config = array(
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

		$this->form_validation->set_rules($config);

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
				redirect('comments#comment_id_' . $this->db->insert_id);
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

	/*
	 * Delete a hierarchy item
	 *
	 * @param int		Hierarchy ID of item we want to delete
	 * @param bool		Do we want to delete this items children?
	 */
	function delete($hierarchy_id, $delete_children = TRUE)
	{
		$this->hierarchy_model->delete_item($hierarchy_id, $delete_children);
	}

	/*
	 * Validate a URL
	 *
	 * @param string		URL
	 * @return bool
	 */
	function valid_url($url)
	{
		// If this field has a value
		if ($url)
		{
			// This filter is quite generous...
			if (filter_var($url, FILTER_VALIDATE_URL))
			{
				return TRUE;
			}
			else
			{
				$this->form_validation->set_message('valid_url', 'Invalid %s.');
				return FALSE;
			}
		}
		// If no URL was provided
		else
		{
			return TRUE;
		}
	}

}