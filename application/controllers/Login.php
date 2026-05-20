<?php
class Login extends CI_Controller
{
    public function index()
    {
        if ($this->session->userdata(SESS_HEAD . '_logged_in')) {
            redirect('dash');
        }

        if ($this->input->post('mode') == 'Login') {
            $email    = $this->input->post('email');
            $password = $this->input->post('password');

            $sql = "SELECT * FROM tm_users WHERE email = ? AND status = 'Active' LIMIT 1";
            $query = $this->db->query($sql, array($email));

            if ($query->num_rows() > 0) {
                $user = $query->row_array();
                if (password_verify($password, $user['password'])) {
                    $this->session->set_userdata(array(
                        SESS_HEAD . '_logged_in'  => TRUE,
                        SESS_HEAD . '_user_id'    => $user['user_id'],
                        SESS_HEAD . '_user_name'  => $user['name'],
                        SESS_HEAD . '_user_email' => $user['email'],
                        SESS_HEAD . '_role'       => $user['role'],
                    ));
                    redirect('dash');
                } else {
                    $this->session->set_flashdata('alert_error', 'Invalid password.');
                    redirect('login');
                }
            } else {
                $this->session->set_flashdata('alert_error', 'No active account found with this email.');
                redirect('login');
            }
        }

        $this->load->view('page/auth/login');
    }
}
