<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Orders extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}

	public function customer_order_list($customer_id)
	{
        $this->db->select('a.*,b.customer_name,o.state,s.store_name');
		$this->db->from('order a');
		$this->db->join('customer_information b','b.customer_id = a.customer_id');
        $this->db->join('order_state o','o.id = a.order_state_id');
		$this->db->join('store_set s','s.store_id = a.store_id');
		$this->db->where('b.customer_id',$customer_id);
        $this->db->where('a.pagado',1);
		$this->db->order_by('a.order','desc');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result_array();
		}
		return false;
	}
	
	public function order_total($order_id)
	{
		$this->db->select('a.total_amount');
		$this->db->from('order a');
		$this->db->where('a.order_id',$order_id);
		
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result_array()[0];
		}
		return false;
	}

	public function order_santander_state($token)
	{
		$this->db->select('a.*');
		$this->db->from('order_santander_pay_log a');
		$this->db->where('a.reference',$token);
		
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result_array()[0];
		}
		return false;
	}
	
	public function order_entry_app($cart_content,$customer_id,$order_id,$store_id,$total,$total_discount,$variante_entrega,$customer_data,$payment_method,$fac_fp){
        //Retrive default store
        $default_store = $this->db->select('store_id')
            ->from('store_set')
            ->where('default_status','1')
            ->get()
            ->row();
        $this->db->select('*');
        $this->db->from('web_setting');
        $this->db->where('setting_id',1);
        $query = $this->db->get();

        $cost_send = $query->result_array()[0]['cost_send'];
        $amount_free_send = $query->result_array()[0]['amount_free_send'];

        $amount_cost_send = 0;

        $cart_total = $total;

        $payment_method = 4;


        //Data inserting into order table for payment gateway
        date_default_timezone_set('America/Mexico_City');
        if($variante_entrega=='2')
        {
            $data=array(
                'order_id'			=>	$order_id,
                'customer_id'		=>	$customer_id,
                'store_id'			=>	$store_id/*(!empty($default_store->store_id)?$default_store->store_id:null)*/,
                'date'				=>	date("m-d-Y h:i:s A"),
                'date_only'				=>	date("m-d-Y"),
                'total_amount'		=>	$cart_total,
                'total_discount' 	=> 	$total_discount,
                'order_discount' 	=> 	$total_discount,
                'due_amount' 		=> 	$cart_total,
                'service_charge' 	=> 	null,
                'status'			=>	1,
                'customer_env_name' 	=> 	$this->session->userdata('customer_name'),
                'customer_env_phone_number' 	=> 	$this->session->userdata('customer_phone_number'),
                'customer_env_street' 	=> 	$this->session->userdata('customer_street'),
                'customer_env_exter_number' 	=> 	$this->session->userdata('customer_exter_number'),
                'customer_env_inter_number' 	=> 	$this->session->userdata('customer_inter_number'),
                'customer_env_between1' 	=> 	$this->session->userdata('customer_between1'),
                'customer_env_between2' 	=> 	$this->session->userdata('customer_between2'),
                'customer_env_colony' 	=> 	$this->session->userdata('customer_colony'),
                'customer_env_delegation' 	=> 	$this->session->userdata('customer_delegation'),
                'customer_env_state' 	=> 	$this->session->userdata('customer_state'),
                'customer_env_zip' 	=> 	$this->session->userdata('customer_zip'),
                'customer_env_refer' 	=> 	$this->session->userdata('customer_refer'),
                'metodo_pago' 	=> 	$payment_method,
                'variante_entrega' 	=> 	$variante_entrega,
                'amount_cost_send' => $amount_cost_send,
                'forma_pago' => $fac_fp,
				'mobile' => 1
            );
            $this->db->insert('order',$data);
        }
        else
        {
            $data=array(
                'order_id'			=>	$order_id,
                'customer_id'		=>	$customer_id,
                'store_id'			=>	$store_id/*(!empty($default_store->store_id)?$default_store->store_id:null)*/,
                'date'				=>	date("m-d-Y h:i:s A"),
                'date_only'				=>	date("m-d-Y"),
                'total_amount'		=>	$cart_total,
                'total_discount' 	=> 	$total_discount,
                'order_discount' 	=> 	$total_discount,
                'due_amount' 		=> 	$cart_total,
                'service_charge' 	=> 	null,
                'status'			=>	1,
                'metodo_pago' 	=> 	$payment_method,
                'variante_entrega' 	=> 	$variante_entrega,
                'amount_cost_send' => $amount_cost_send,
                'forma_pago' => $fac_fp,
				'mobile' => 1
            );
            $this->db->insert('order',$data);
        }

        //Insert order to order details

        if ($cart_content) {
			foreach ($cart_content as $items){
                $order_details = array(
					'order_details_id'	=>	$this->generator(15),
					'order_id'			=>	$order_id,
					'product_id'		=>	$items['product_id'],
					'store_id'			=>	$store_id,
					'quantity'			=>	$items['qty'],
					'rate'				=>	$items['actual_price'],
					'supplier_rate'     =>	$items['supplier_price'],
					'total_price'       =>	$items['actual_price'] * $items['qty'],
					'discount'          =>	$items['discount'],
					'status'			=>	1,
                    'promo'             => $items['promo']
				);

				if(!empty($items))
				{
					$this->db->insert('order_details',$order_details);
				}

				//CGST Tax summary
				$cgst_summary = array(
					'order_tax_col_id'	=>	$this->generator(15),
					'order_id'			=>	$order_id,
					'tax_amount' 		=> 	$items['options']['cgst'] * $items['qty'],
					'tax_id' 			=> 	$items['options']['cgst_id'],
					'date'				=>	date("m-d-Y"),
				);

				if(!empty($items['options']['cgst_id'])){
					$result= $this->db->select('*')
								->from('order_tax_col_summary')
								->where('order_id',$order_id)
								->where('tax_id',$items['options']['cgst_id'])
								->get()
								->num_rows();

					if ($result > 0) {
						$this->db->set('tax_amount', 'tax_amount+'.$items['options']['cgst'] * $items['qty'], FALSE);
						$this->db->where('order_id', $order_id);
						$this->db->where('tax_id',$items['options']['cgst_id']);
						$this->db->update('order_tax_col_summary');
					}else{
						$this->db->insert('order_tax_col_summary',$cgst_summary);
					}
				}
				//CGST Summary End

				//IGST Tax summary
				$igst_summary = array(
					'order_tax_col_id'	=>	$this->generator(15),
					'order_id'			=>	$order_id,
					'tax_amount' 		=> 	$items['options']['igst'] * $items['qty'],
					'tax_id' 			=> 	$items['options']['igst_id'],
					'date'				=>	date("m-d-Y"),
				);

				if(!empty($items['options']['igst_id'])){
					$result= $this->db->select('*')
								->from('order_tax_col_summary')
								->where('order_id',$order_id)
								->where('tax_id',$items['options']['igst_id'])
								->get()
								->num_rows();

					if ($result > 0) {
						$this->db->set('tax_amount', 'tax_amount+'.$items['options']['igst'] * $items['qty'], FALSE);
						$this->db->where('order_id', $order_id);
						$this->db->where('tax_id',$items['options']['igst_id']);
						$this->db->update('order_tax_col_summary');
					}else{
						$this->db->insert('order_tax_col_summary',$igst_summary);
					}
				}
				//IGST Tax summary end

				//SGST Tax summary
				$sgst_summary = array(
					'order_tax_col_id'	=>	$this->generator(15),
					'order_id'			=>	$order_id,
					'tax_amount' 		=> 	$items['options']['sgst'] * $items['qty'],
					'tax_id' 			=> 	$items['options']['sgst_id'],
					'date'				=>	date("m-d-Y"),
				);

				if(!empty($items['options']['sgst_id'])){
					$result= $this->db->select('*')
								->from('order_tax_col_summary')
								->where('order_id',$order_id)
								->where('tax_id',$items['options']['sgst_id'])
								->get()
								->num_rows();

					if ($result > 0) {
						$this->db->set('tax_amount', 'tax_amount+'.$items['options']['sgst'] * $items['qty'], FALSE);
						$this->db->where('order_id', $order_id);
						$this->db->where('tax_id',$items['options']['sgst_id']);
						$this->db->update('order_tax_col_summary');
					}else{
						$this->db->insert('order_tax_col_summary',$sgst_summary);
					}
				}
				//SGST Tax summary end

				//CGST Details
				$cgst_details = array(
					'order_tax_col_de_id'	=>	$this->generator(15),
					'order_id'			=>	$order_id,
					'amount' 			=> 	$items['options']['cgst'] * $items['qty'],
					'product_id' 		=> 	$items['product_id'],
					'tax_id' 			=> 	$items['options']['cgst_id'],
					'date'				=>	date("m-d-Y"),
				);

				if(!empty($items['options']['cgst_id'])){
					$this->db->insert('order_tax_col_details',$cgst_details);
				}
				//CGST Details End

				//IGST Details
				$igst_details = array(
					'order_tax_col_de_id'	=>	$this->generator(15),
					'order_id'			=>	$order_id,
					'amount' 			=> 	$items['options']['igst'] * $items['qty'],
					'product_id' 		=> 	$items['product_id'],
					'tax_id' 			=> 	$items['options']['igst_id'],
					'date'				=>	date("m-d-Y"),
				);
				if(!empty($items['options']['igst_id'])){
					$this->db->insert('order_tax_col_details',$igst_details);
				}
				//IGST Details End

				//SGST Details
				$sgst_details = array(
					'order_tax_col_de_id'	=>	$this->generator(15),
					'order_id'			=>	$order_id,
					'amount' 			=> 	$items['options']['sgst'] * $items['qty'],
					'product_id' 		=> 	$items['product_id'],
					'tax_id' 			=> 	$items['options']['sgst_id'],
					'date'				=>	date("m-d-Y"),
				);
				if(!empty($items['options']['sgst_id'])){
					$this->db->insert('order_tax_col_details',$sgst_details);
				}
				//SGST Details End
			}
		}

        return true;
    }

    public function generator($lenth)
    {
        $number=array("A","B","C","D","E","F","G","H","I","J","K","L","N","M","O","P","Q","R","S","U","V","T","W","X","Y","Z","1","2","3","4","5","6","7","8","9","0");

        for($i=0; $i<$lenth; $i++)
        {
            $rand_value=rand(0,34);
            $rand_number=$number["$rand_value"];

            if(empty($con))
            {
                $con=$rand_number;
            }
            else
            {
                $con="$con"."$rand_number";}
        }
        return $con;
    }

    //Retrieve order Edit Data
    public function retrieve_order_editdata($order_id)
    {
        $this->db->select('
                a.*,
                b.customer_name,
                c.*,
                c.product_id,
                d.product_name,
                d.product_model,
				d.image_thumb,
                a.status,
				o.state
            ');



        $this->db->from('order a');
        $this->db->join('customer_information b','b.customer_id = a.customer_id');
		$this->db->join('order_state o','o.id = a.order_state_id');
        $this->db->join('order_details c','c.order_id = a.order_id');
        $this->db->join('product_information d','d.product_id = c.product_id');
        $this->db->where('a.order_id',$order_id);
        $query = $this->db->get();




        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return false;
    }
} 
