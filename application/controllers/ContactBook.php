<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ContactBook extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
    }

    private function _auth()
    {
        if (!$this->session->userdata(SESS_HEAD . '_logged_in')) {
            redirect('login');
        }

        $role = $this->session->userdata(SESS_HEAD . '_role');
        if ($role !== 'admin') {
            $this->session->set_flashdata('alert_error', 'Access Denied: Only Administrator is authorized to view or edit the Contact Book.');
            redirect('dash');
        }
    }

    private function _uid()
    {
        return $this->session->userdata(SESS_HEAD . '_user_id');
    }

    /**
     * Auto-ensure the table exists in the database.
     */
    private function _ensure_table()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `tm_contact_book` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(150) NOT NULL,
            `company` VARCHAR(150) DEFAULT NULL,
            `job_title` VARCHAR(150) DEFAULT NULL,
            `email` VARCHAR(150) DEFAULT NULL,
            `phone` VARCHAR(50) DEFAULT NULL,
            `address` TEXT DEFAULT NULL,
            `notes` TEXT DEFAULT NULL,
            `category` VARCHAR(50) DEFAULT 'General',
            `status` TINYINT(1) DEFAULT 1,
            `created_by` INT(11) DEFAULT NULL,
            `created_at` DATETIME DEFAULT NULL,
            `updated_at` DATETIME DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_created_by` (`created_by`),
            KEY `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    /**
     * Contact book list view
     */
    public function index()
    {
        $this->_auth();
        $this->_ensure_table();

        $data['title'] = 'Contact Book';
        $data['js']    = 'contact-book.inc';

        // Filters
        $category = $this->input->get('category');
        $status   = $this->input->get('status');
        $search   = $this->input->get('search');

        $this->db->select('*');
        $this->db->from('tm_contact_book');
        
        if ($category) {
            $this->db->where('category', $category);
        }
        if ($status !== null && $status !== '') {
            $this->db->where('status', (int)$status);
        }
        if ($search) {
            $this->db->group_start()
                     ->like('name', $search)
                     ->or_like('company', $search)
                     ->or_like('phone', $search)
                     ->or_like('email', $search)
                     ->group_end();
        }

        $this->db->order_by('name', 'ASC');
        $q = $this->db->get();
        $data['contacts'] = ($q !== false) ? $q->result_array() : [];

        // Count stats
        $data['total_count']    = $this->db->count_all_results('tm_contact_book');
        
        $this->db->where('status', 1);
        $data['active_count']   = $this->db->count_all_results('tm_contact_book');
        
        $this->db->where('status', 0);
        $data['inactive_count'] = $this->db->count_all_results('tm_contact_book');

        // Available categories list for filtering/adding
        $data['categories'] = ['General', 'Client', 'Supplier', 'Team', 'Partner', 'Personal'];

        $this->load->view('page/contact-book/index', $data);
    }

    /**
     * Save / Update Contact (AJAX POST)
     */
    public function save()
    {
        $this->_auth();
        $this->_ensure_table();
        header('Content-Type: application/json');

        $id = (int)$this->input->post('id');
        $name = trim($this->input->post('name'));
        if (!$name) {
            echo json_encode(['success' => false, 'message' => 'Full Name is required.']);
            return;
        }

        $row = [
            'name'      => $name,
            'company'   => trim($this->input->post('company')) ?: null,
            'job_title' => trim($this->input->post('job_title')) ?: null,
            'email'     => trim($this->input->post('email')) ?: null,
            'phone'     => trim($this->input->post('phone')) ?: null,
            'address'   => trim($this->input->post('address')) ?: null,
            'notes'     => trim($this->input->post('notes')) ?: null,
            'category'  => trim($this->input->post('category')) ?: 'General',
            'status'    => $this->input->post('status') !== null ? (int)$this->input->post('status') : 1,
            'updated_at'=> date('Y-m-d H:i:s')
        ];

        if ($id > 0) {
            $this->db->where('id', $id);
            $this->db->update('tm_contact_book', $row);
            $msg = 'Contact updated successfully.';
        } else {
            $row['created_by'] = $this->_uid();
            $row['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('tm_contact_book', $row);
            $id = $this->db->insert_id();
            $msg = 'Contact added successfully.';
        }

        $fresh = $this->db->where('id', $id)->get('tm_contact_book')->row_array();

        echo json_encode([
            'success' => true, 
            'message' => $msg, 
            'id'      => $id, 
            'contact' => $fresh
        ]);
    }

    /**
     * Get specific contact details (AJAX GET)
     */
    public function get_contact()
    {
        $this->_auth();
        $this->_ensure_table();
        header('Content-Type: application/json');

        $id = (int)$this->input->get('id');
        $contact = $this->db->where('id', $id)->get('tm_contact_book')->row_array();

        if ($contact) {
            echo json_encode(['success' => true, 'contact' => $contact]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Contact not found.']);
        }
    }

    /**
     * Delete Contact (AJAX POST)
     */
    public function delete()
    {
        $this->_auth();
        $this->_ensure_table();
        header('Content-Type: application/json');

        $id = (int)$this->input->post('id');
        $this->db->where('id', $id);
        if ($this->db->delete('tm_contact_book')) {
            echo json_encode(['success' => true, 'message' => 'Contact deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete contact.']);
        }
    }
}
