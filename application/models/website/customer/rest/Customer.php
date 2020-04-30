<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Customer extends CI_Model {
	public function __construct()
	{
		parent::__construct();
    }
	
	public function user_exist($email)
    {
        $query = $this->db->select('*')
            ->from('customer_information')
            ->where('customer_email',$email)
            ->get();

        if ($query->num_rows() > 0) {
            return true;
        }
        return false;
    }
	
	public function user($email)
    {
        $query = $this->db->select('*')
            ->from('customer_information')
            ->where('customer_email',$email)
            ->get();

        if ($query->num_rows() > 0) {
            return $query->result_array()[0]['customer_id'];
        }
        return false;
    }

    public function update_reset_password_code($customer_id,$data)
    {
        $this->db->where('customer_id',$customer_id);
        $this->db->update('customer_information',$data);

        return true;
    }
	
	function check_user_id($user_id)
    {
        $query = $this->db->where(array('customer_id'=>$user_id,'status'=>2))
            ->get('customer_information');
        $result = $query->result_array();

        if (count($result) == 1)
        {
            $data = array('status'=>1);
            $this->db->where('customer_id',$user_id);
            $this->db->update('customer_information',$data);

            $this->db->select('*');
            $this->db->from('customer_information');
            $this->db->where('customer_id',$result[0]['customer_id']);
            $query = $this->db->get();
            return $query->result_array();
        }
        return false;
    }

    public function user_signup($data)
    {
        $result = $this->db->insert('customer_information',$data);
        if ($result) {
            $this->db->select('*');
            $this->db->from('customer_information');
            $query = $this->db->get();
            foreach ($query->result() as $row) {
                $json_customer[] = array('label'=>$row->customer_name,'value'=>$row->customer_id);
            }
            $cache_file ='./my-assets/js/admin_js/json/customer.json';
            $customerList = json_encode($json_customer);
            file_put_contents($cache_file,$customerList);
            return TRUE;
        }
        return false;
    }
	
	//Retruve profile data
    public function profile_edit_data($customer_id)
    {
        $customer_id = $customer_id;
        $this->db->select('*');
        $this->db->from('customer_information');
        $this->db->where('customer_id',$customer_id);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        return false;
    }
	
	public update_general_data($customer_id,$data)
	{
		$this->db->where('customer_id',$customer_id);
		$this->db->update('customer_information',$data);
		return true;
	}
    
    public function admin_profile_send_data_list($customer_id)
    {
        $this->db->select('*');
        $this->db->from('customer_information_send_data');
        $this->db->where('customer_id',$customer_id);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return false;
    }

	public function insert_send_data($data)
    {
        $this->db->insert('customer_information_send_data',$data);
        return true;
    }

    public function insert_invoice_data($data)
    {
        $this->db->insert('customer_information_invoice_data',$data);
        return true;
    }

    public function update_send_data($customer_information_send_data_id,$data)
    {
        $this->db->where('customer_information_send_data_id',$customer_information_send_data_id);
        $this->db->update('customer_information_send_data',$data);
        return true;
    }
	
	public function get_send_data($customer_information_send_data_id)
    {
        $this->db->select('*');
        $this->db->from('customer_information_send_data');
        $this->db->where('customer_information_send_data_id',$customer_information_send_data_id);

        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        return false;
    }

    public function delete_send_data($customer_information_send_data_id)
    {
        $this->db->where('customer_information_send_data_id',$customer_information_send_data_id);
        $this->db->delete('customer_information_send_data');
        return true;
    }

    public function admin_profile_invoice_data_list($customer_id)
    {
        $this->db->select('*');
        $this->db->from('customer_information_invoice_data');
        $this->db->where('customer_id',$customer_id);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return false;
    }

    public function get_invoice_data($customer_information_invoice_data_id)
    {
        $this->db->select('*');
        $this->db->from('customer_information_invoice_data');
        $this->db->where('customer_information_invoice_data_id',$customer_information_invoice_data_id);

        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        return false;
    }

    public function update_invoice_data($customer_information_invoice_data_id,$data)
    {
        $this->db->where('customer_information_invoice_data_id',$customer_information_invoice_data_id);
        $this->db->update('customer_information_invoice_data',$data);
        return true;
    }

    public function delete_invoice_data($customer_information_invoice_data_id)
    {
        $this->db->where('customer_information_invoice_data_id',$customer_information_invoice_data_id);
        $this->db->delete('customer_information_invoice_data');
        return true;
    }
} 
