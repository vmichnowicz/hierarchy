<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Comments extends CI_Controller {

	public $comments, $captchas;

	/*
	 * Construct
	 */
	function __construct()
	{
		parent::__construct();

		// Load libraries
		$this->load->library('hierarchy');
		$this->load->library('form_validation');

		$this->comments = new $this->hierarchy;
		$this->comments->table('comments');

		// These may be too hard for the average person...
		$this->captchas = array(
			array(
				'question' => '1 &times; 7',
				'answer' => 7
			),
			array(
				'question' => '1 + 14',
				'answer' => 15
			),
			array(
				'question' => '1 plus 9',
				'answer' => 10
			),
			array(
				'question' => '10 - 1',
				'answer' => 9
			),
			array(
				'question' => '100 minus 50',
				'answer' => 50
			),
			array(
				'question' => '5 &times; 1',
				'answer' => 5
			),
			array(
				'question' => 'First digit of <span style="font-family: serif;" title="Pi">&pi;</span>', // No one under the age of 12 can post a comment, nice!
				'answer' => 3
			)
		);
	}

	/*
	 * Load main view
	 */
	function index()
	{
		$data['comments'] = $this->comments
			->generate_hierarchial_list('hierarchy_comments_template', 'ul', 'id="comments"');

		$data['reply_to'] = $this->comments->item_exists($this->input->get('reply_to'));

		$rand_question = array_rand($this->captchas, 1);

		$data['captcha'] = array(
			'index' => $rand_question,
			'question' => $this->captchas[$rand_question]['question']
		);

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
			),
			array(
				'field' => 'captcha',
				'label' => 'Captcha',
				'rules' => 'trim|callback_valid_captcha'
			),
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
			$this->comments->add_item($data);

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
				$this->output->set_header('Expires: Sat, 1 Jan 2000 12:00:00 GMT');
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
		$this->comments->delete_item($hierarchy_id, $delete_children);
	}

	/*
	 * Validate a URL
	 *
	 * @param string	URL
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

	/*
	 * Validate our super simple captcha
	 *
	 * @param string	User's answer to our captcha
	 * @return bool
	 */
	function valid_captcha($captcha_answer)
	{
		// Make sure this question exists
		if (array_key_exists($this->input->post('captcha_index'), $this->captchas))
		{
			// If the user entered the correct response
			if ($this->captchas[ $this->input->post('captcha_index') ]['answer'] === (int)$captcha_answer)
			{
				return TRUE;
			}
			// Really? They can not does a simplz maths questionz?
			else
			{
				$this->form_validation->set_message('valid_captcha', 'Think about it, ' . $this->captchas[ $this->input->post('captcha_index') ]['question'] . '&hellip; You got this one champ.');
				return FALSE;
			}
		}
		// If question does not exist
		else
		{
			$this->form_validation->set_message('valid_captcha', 'Homie aint gonna play that game.');
			return FALSE;
		}
	}

}