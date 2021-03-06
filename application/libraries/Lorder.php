<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Lorder {

	//Order Add Form
	public function order_add_form()
	{
		$CI =& get_instance();
		$CI->load->model('Orders');
		$CI->load->model('Stores');
		$CI->load->model('Variants');

		$store_list 		= $CI->Stores->store_list();
		$variant_list 		= $CI->Variants->variant_list();
		$terminal_list    	= $CI->Orders->terminal_list();
		
		$data = array(
				'title' 		=> display('new_order'),
				'store_list' 	=> $store_list,
				'variant_list' 	=> $variant_list,
				'terminal_list' => $terminal_list,
			);
		$orderForm = $CI->parser->parse('order/add_order_form',$data,true);
		return $orderForm;
	}
	
	public function create_interface_form()
	{
		$CI =& get_instance();
		$CI->load->model('Stores');
		$store_list 		= $CI->Stores->store_list();
		$data = array(
				'title' 		=> 'Crear interface',
				'stores' 	=> $store_list,
			);
		$orderForm = $CI->parser->parse('order/create_interface_form',$data,true);
		return $orderForm;
	}

    public function order_by_id($idOrder)
    {
        $CI =& get_instance();
        $CI->load->model('Orders');
        $order = $CI->Orders->order_by_id($idOrder);
        return $order;
    }

	//Retrieve  order List
	public function order_list()
	{
		$CI =& get_instance();
		$CI->load->model('Orders');
		$CI->load->model('Soft_settings');
		$CI->load->library('occational');
        $CI->load->model('Stores');

        $store_lock = 1;


        if($CI->session->userdata('user_type') == 4)
        {
            if($CI->session->userdata('store_id')=='' || $CI->session->userdata('store_id')==null)
            {
                $CI->logout();
                redirect('admin','refresh');
            }

            $store_lock = $CI->Stores->store_search_item($CI->session->userdata('store_id'))['lock'];

        }

        $orders_list = $CI->Orders->order_list();

        if(!empty($orders_list)){
			foreach($orders_list as $k=>$v){
				$orders_list[$k]['final_date'] = $CI->occational->dateConvert($orders_list[$k]['date']);
			}
			$i=0;
			foreach($orders_list as $k=>$v){$i++;
			   $orders_list[$k]['sl']=$i;
			}
		}

		$currency_details = $CI->Soft_settings->retrieve_currency_info();
		$data = array(
				'title'    => display('manage_order'),
				'orders_list' => $orders_list,
				'currency' => $currency_details[0]['currency_icon'],
				'position' => $currency_details[0]['currency_position'],
                'store_lock'        => $store_lock,
                'store_id'          => $CI->session->userdata('store_id')
			);
		$orderList = $CI->parser->parse('order/order',$data,true);
		return $orderList;
	}

    //Retrieve  order List
    /*public function order_list_by_store()
    {
        $CI =& get_instance();
        $CI->load->model('Orders');
        $CI->load->model('Soft_settings');
        $CI->load->library('occational');

        $orders_list = $CI->Orders->order_list_by_store($store_id);

        if(!empty($orders_list)){
            foreach($orders_list as $k=>$v){
                $orders_list[$k]['final_date'] = $CI->occational->dateConvert($orders_list[$k]['date']);
            }
            $i=0;
            foreach($orders_list as $k=>$v){$i++;
                $orders_list[$k]['sl']=$i;
            }
        }

        $currency_details = $CI->Soft_settings->retrieve_currency_info();
        $data = array(
            'title'    => display('manage_order'),
            'orders_list' => $orders_list,
            'currency' => $currency_details[0]['currency_icon'],
            'position' => $currency_details[0]['currency_position'],
        );
        $orderList = $CI->parser->parse('order/order',$data,true);
        return $orderList;
    }*/


	//Insert order
	public function insert_order($data)
	{
		$CI =& get_instance();
		$CI->load->model('Orders');
        $CI->Orders->order_entry($data);
		return true;
	}

	//order Edit Data
	public function order_edit_data($order_id)
	{
		$CI =& get_instance();
		$CI->load->model('Orders');
		$CI->load->model('Stores');
		$order_detail 	  = $CI->Orders->retrieve_order_editdata($order_id);
		$store_id 		  = $order_detail[0]['store_id'];
		$store_list 	  = $CI->Stores->store_list();
		$store_list_selected = $CI->Stores->store_list_selected($store_id);
		$terminal_list    = $CI->Orders->terminal_list();

		$i=0;
		foreach($order_detail as $k=>$v){$i++;
		   $order_detail[$k]['sl']=$i;
		}

		$data=array(
			'title'				=> 	display('order_update'),
			'order_id'			=>	$order_detail[0]['order_id'],
			'customer_id'		=>	$order_detail[0]['customer_id'],
			'store_id'			=>	$order_detail[0]['store_id'],
			'customer_name'		=>	$order_detail[0]['customer_name'],
			'date'				=>	$order_detail[0]['date'],
			'total_amount'		=>	$order_detail[0]['total_amount'],
			'paid_amount'		=>	$order_detail[0]['paid_amount'],
			'due_amount'		=>	$order_detail[0]['due_amount'],
			'total_discount'	=>	$order_detail[0]['total_discount'],
			'order_discount'	=>	$order_detail[0]['order_discount'],
			'service_charge'	=>	$order_detail[0]['service_charge'],
			'details'			=>	$order_detail[0]['details'],
			'order'				=>	$order_detail[0]['order'],
			'status'			=>	$order_detail[0]['status'],
			'order_all_data'	=>	$order_detail,
			'store_list'		=>	$store_list,
			'store_list_selected'=>	$store_list_selected,
			'terminal_list'     =>	$terminal_list,
			);

		$chapterList = $CI->parser->parse('order/edit_order_form',$data,true);
		return $chapterList;
	}




	//Order Html Data
	public function order_html_data($order_id)
	{
		$CI =& get_instance();
		$CI->load->model('Orders');
		$CI->load->model('Soft_settings');
		$CI->load->library('occational');
		$CI->load->library('Pdfgenerator');
		$order_detail = $CI->Orders->retrieve_order_html_data($order_id);

		$subTotal_quantity 	= 0;
		$subTotal_cartoon 	= 0;
		$subTotal_discount 	= 0;

		if(!empty($order_detail)){
			foreach($order_detail as $k=>$v){
				$order_detail[$k]['final_date'] = $CI->occational->dateConvert($order_detail[$k]['date']);
				$subTotal_quantity = $subTotal_quantity+$order_detail[$k]['quantity'];
			}
			$i=0;
			foreach($order_detail as $k=>$v){$i++;
			   $order_detail[$k]['sl']=$i;
			}
		}

		$currency_details = $CI->Soft_settings->retrieve_currency_info();
		$company_info = $CI->Orders->retrieve_company();
		$data=array(
			'title'				=>	display('order_details'),
			'order_id'			=>	$order_detail[0]['order_id'],
			'order_no'			=>	$order_detail[0]['order'],
			'customer_address'	=>	$order_detail[0]['customer_short_address'],
			'customer_name'		=>	$order_detail[0]['customer_name'],
			'customer_mobile'	=>	$order_detail[0]['customer_mobile'],
			'customer_email'	=>	$order_detail[0]['customer_email'],
			'final_date'		=>	$order_detail[0]['final_date'],
			'total_amount'		=>	$order_detail[0]['total_amount'],
			'order_discount' 	=>	$order_detail[0]['order_discount'],
			'service_charge' 	=>	$order_detail[0]['service_charge'],
			'paid_amount'		=>	$order_detail[0]['paid_amount'],
			'due_amount'		=>	$order_detail[0]['due_amount'],
			'details'			=>	$order_detail[0]['details'],
			'subTotal_quantity'	=>	$subTotal_quantity,
			'order_all_data' 	=>	$order_detail,
			'company_info'		=>	$company_info,
			'currency' 			=> 	$currency_details[0]['currency_icon'],
			'position' 			=> 	$currency_details[0]['currency_position'],
			);

		$chapterList = $CI->parser->parse('order/order_pdf',$data,true);

		//PDF Generator 
		$dompdf = new DOMPDF();
	    $dompdf->load_html($chapterList);
	    $dompdf->render();
	    $output = $dompdf->output();
	    file_put_contents('my-assets/pdf/'.$order_id.'.pdf', $output);
	    $file_path = 'my-assets/pdf/'.$order_id.'.pdf';

	    //File path save to database
	    $CI->db->set('file_path',base_url($file_path));
	    $CI->db->where('order_id',$order_id);
	    $CI->db->update('order');

	    $send_email = '';
	    if (!empty($data['customer_email'])) {
	    	$send_email = $this->setmail($data['customer_email'],$file_path);
	    }

	    if ($send_email != null) {
	    	return true;
	    }else{
	    	return false;
	    }
	}


	//Send Customer Email with invoice
	public function setmail($email,$file_path)
	{

		$CI =& get_instance();
		$CI->load->model('Soft_settings');
		$setting_detail = $CI->Soft_settings->retrieve_email_editdata();

		$subject = display("order_information");
		$message = display("order_info_details").'<br>'.base_url();

	    $config = Array(
	      	'protocol' 		=> $setting_detail[0]['protocol'],
	      	'smtp_host' 	=> $setting_detail[0]['smtp_host'],
	      	'smtp_port' 	=> $setting_detail[0]['smtp_port'],
	      	'smtp_user' 	=> $setting_detail[0]['sender_email'], 
	      	'smtp_pass' 	=> $setting_detail[0]['password'], 
	      	'mailtype' 		=> $setting_detail[0]['mailtype'],
	      	'charset' 		=> 'utf-8'
	    );
	    
	    $CI->load->library('email', $config);
	    $CI->email->set_newline("\r\n");
	    $CI->email->from($setting_detail[0]['sender_email']);
	    $CI->email->to($email);
	    $CI->email->subject($subject);
	    $CI->email->message($message);
	    $CI->email->attach($file_path);

	    $check_email = $this->test_input($email);
		if (filter_var($check_email, FILTER_VALIDATE_EMAIL)) {
		    if($CI->email->send())
		    {
		      	$CI->session->set_userdata(array('message'=>display('email_send_to_customer')));
		      	return true;
		    }else{
		     	$CI->session->set_userdata(array('error_message'=> display('email_not_send')));
		     	redirect(base_url('Corder/manage_order'));
		    }
		}else{
			$CI->session->set_userdata(array('message'=>display('successfully_added')));
		    redirect(base_url('Corder/manage_order'));
		}
	}

	//Email testing for email
	public function test_input($data) {
	  	$data = trim($data);
	  	$data = stripslashes($data);
	  	$data = htmlspecialchars($data);
	  	return $data;
	}
	//Order Details Data
	public function order_details_data($order_id)
	{
		$CI =& get_instance();
		$CI->load->model('Orders');
		$CI->load->model('Soft_settings');
		$CI->load->library('occational');
		$CI->load->library('Pdfgenerator');
		$order_detail = $CI->Orders->retrieve_order_html_data($order_id);

		$subTotal_quantity 	= 0;
		$subTotal_cartoon 	= 0;
		$subTotal_discount 	= 0;

		if(!empty($order_detail)){
			foreach($order_detail as $k=>$v){
				$order_detail[$k]['final_date'] = $CI->occational->dateConvert($order_detail[$k]['date']);
				$subTotal_quantity = $subTotal_quantity+$order_detail[$k]['quantity'];
			}
			$i=0;
			foreach($order_detail as $k=>$v){$i++;
			   $order_detail[$k]['sl']=$i;
			}
		}

		$currency_details = $CI->Soft_settings->retrieve_currency_info();
		$company_info = $CI->Orders->retrieve_company();
		$data=array(
			'title'				=>	display('order_details'),
			'order_id'			=>	$order_detail[0]['order_id'],
			'order_no'			=>	$order_detail[0]['order'],
			'customer_address'	=>	$order_detail[0]['customer_short_address'],
			'customer_name'		=>	$order_detail[0]['customer_name'],
			'customer_mobile'	=>	$order_detail[0]['customer_mobile'],
			'customer_email'	=>	$order_detail[0]['customer_email'],
			'final_date'		=>	$order_detail[0]['final_date'],
			'total_amount'		=>	$order_detail[0]['total_amount'],
			'order_discount' 	=>	$order_detail[0]['order_discount'],
			'service_charge' 	=>	$order_detail[0]['service_charge'],
			'paid_amount'		=>	$order_detail[0]['paid_amount'],
			'due_amount'		=>	$order_detail[0]['due_amount'],
			'details'			=>	$order_detail[0]['details'],
			'subTotal_quantity'	=>	$subTotal_quantity,
			'order_all_data' 	=>	$order_detail,
			'company_info'		=>	$company_info,
			'currency' 			=> 	$currency_details[0]['currency_icon'],
			'position' 			=> 	$currency_details[0]['currency_position'],
			);

		$chapterList = $CI->parser->parse('order/order_html',$data,true);
		return $chapterList;
	}

	//POS order html Data
	public function pos_order_html_data($order_id)
	{
		$CI =& get_instance();
		$CI->load->model('Orders');
		$CI->load->model('Soft_settings');
		$CI->load->library('occational');
		$order_detail = $CI->Orders->retrieve_order_html_data($order_id);
		$subTotal_quantity = 0;
		$subTotal_cartoon = 0;
		$subTotal_discount = 0;

		if(!empty($order_detail)){
			foreach($order_detail as $k=>$v){
				$order_detail[$k]['final_date'] = $CI->occational->dateConvert($order_detail[$k]['date']);
				$subTotal_quantity = $subTotal_quantity+$order_detail[$k]['quantity'];
			}
			$i=0;
			foreach($order_detail as $k=>$v){$i++;
			   $order_detail[$k]['sl']=$i;
			}
		}

		$currency_details = $CI->Soft_settings->retrieve_currency_info();
		$company_info = $CI->Orders->retrieve_company();
		$data=array(
			'title'				=> display('order_detail'),
			'order_id'			=>	$order_detail[0]['order_id'],
			'order_no'			=>	$order_detail[0]['order'],
			'customer_name'		=>	$order_detail[0]['customer_name'],
			'customer_address'	=>	$order_detail[0]['customer_short_address'],
			'customer_mobile'	=>	$order_detail[0]['customer_mobile'],
			'customer_email'	=>	$order_detail[0]['customer_email'],
			'final_date'		=>	$order_detail[0]['final_date'],
			'total_amount'		=>	$order_detail[0]['total_amount'],
			'subTotal_discount'	=>	$order_detail[0]['total_discount'],
			'paid_amount'		=>	$order_detail[0]['paid_amount'],
			'due_amount'		=>	$order_detail[0]['due_amount'],
			'subTotal_quantity'	=>	$subTotal_quantity,
			'order_all_data' 	=>	$order_detail,
			'company_info'		=>	$company_info,
			'currency' 			=> $currency_details[0]['currency_icon'],
			'position' 			=> $currency_details[0]['currency_position'],
			);
		$chapterList = $CI->parser->parse('order/pos_order_html',$data,true);
		return $chapterList;
	}

    //order Edit Data
    public function order_show_data($order_id)
    {
        $CI =& get_instance();
        $CI->load->model('Orders');
        $CI->load->model('Stores');
        $CI->load->library('occational');
        $CI->load->model('Stores');

        $store_lock = 1;


        if($CI->session->userdata('user_type') == 4)
        {
            if($CI->session->userdata('store_id')=='' || $CI->session->userdata('store_id')==null)
            {
                $CI->logout();
                redirect('admin','refresh');
            }

            $store_lock = $CI->Stores->store_search_item($CI->session->userdata('store_id'))['lock'];

        }


        $order_detail 	  = $CI->Orders->retrieve_order_editdata($order_id);

        $order_state 	  = $CI->Orders->order_state();

        $i=0;
        foreach($order_detail as $k=>$v){$i++;
            $order_detail[$k]['sl']=$i;
        }

        $state = $order_detail[0]['state'];

        if($order_detail[0]['order_state_id']==3)
        {
            if($order_detail[0]['variante_entrega']==1)
            {
                $state = $state.' recoger';
            }
            else
                $state = $state.' enviar';
        }

        $data=array(
            'title'				=> 	"Pedido",
            'order_id'			=>	$order_detail[0]['order_id'],
            'customer_id'		=>	$order_detail[0]['customer_id'],
            'store_id'			=>	$order_detail[0]['store_id'],
            'customer_name'		=>	$order_detail[0]['customer_name'],
            'customer_cash_name'		=>	$order_detail[0]['customer_cash_name'],
            'customer_cash_phone'		=>	$order_detail[0]['customer_cash_phone'],
            'date'				=>	$CI->occational->dateConvert($order_detail[0]['date']),
            'total_amount'		=>	$order_detail[0]['total_amount'],
            'paid_amount'		=>	$order_detail[0]['paid_amount'],
            'due_amount'		=>	$order_detail[0]['due_amount'],
            'total_discount'	=>	$order_detail[0]['total_discount'],
            'order_discount'	=>	$order_detail[0]['order_discount'],
            'service_charge'	=>	$order_detail[0]['service_charge'],
            'details'			=>	$order_detail[0]['details'],
            'order'				=>	$order_detail[0]['order'],
            'status'			=>	$order_detail[0]['status'],
            'timbrado'			=>	$order_detail[0]['timbrado'],
            'order_state_id'			=>	$order_detail[0]['order_state_id'],
            'variante_entrega'  =>  $order_detail[0]['variante_entrega'],
            'order_all_data'	=>	$order_detail,
            'customer_name'	=>	$order_detail[0]['customer_env_name'],
            'customer_phone_number'	=>	$order_detail[0]['customer_env_phone_number'],
            'customer_street'	=>	$order_detail[0]['customer_env_street'],
            'customer_exter_number'	=>	$order_detail[0]['customer_env_exter_number'],
            'customer_inter_number'	=>	$order_detail[0]['customer_env_inter_number'],
            'customer_between1'	=>	$order_detail[0]['customer_env_between1'],
            'customer_between2'	=>	$order_detail[0]['customer_env_between2'],
            'customer_colony'	=>	$order_detail[0]['customer_env_colony'],
            'customer_delegation'	=>	$order_detail[0]['customer_env_delegation'],
            'customer_state'	=>	$order_detail[0]['customer_env_state'],
            'customer_zip'	=>	$order_detail[0]['customer_env_zip'],
            'customer_refer'	=>	$order_detail[0]['customer_env_refer'],
            'order_state' => $order_state,
            'state' => $state,
            'store_lock'        => $store_lock,
            'store_id'          => $CI->session->userdata('store_id')
        );

        $chapterList = $CI->parser->parse('order/show_order',$data,true);
        return $chapterList;
    }

    public function order_print_data($order_id)
    {
        $CI =& get_instance();
        $CI->load->model('Orders');
        $CI->load->model('Stores');
        $CI->load->library('occational');


        $order_detail 	  = $CI->Orders->retrieve_order_editdata($order_id);

        $order_state 	  = $CI->Orders->order_state();

        $i=0;
        foreach($order_detail as $k=>$v){$i++;
            $order_detail[$k]['sl']=$i;
        }

        $state = $order_detail[0]['state'];

        if($order_detail[0]['order_state_id']==3)
        {
            if($order_detail[0]['variante_entrega']==1)
            {
                $state = $state.' recoger';
            }
            else
                $state = $state.' enviar';
        }

        $data=array(
            'title'				=> 	"Pedido",
            'order_id'			=>	$order_detail[0]['order_id'],
            'customer_id'		=>	$order_detail[0]['customer_id'],
            'store_id'			=>	$order_detail[0]['store_id'],
            'store_name'		=>	$order_detail[0]['store_name'],
            'customer_name'		=>	$order_detail[0]['customer_name'],
            'date'				=>	$CI->occational->dateConvert($order_detail[0]['date']),
            'total_amount'		=>	$order_detail[0]['total_amount'],
            'paid_amount'		=>	$order_detail[0]['paid_amount'],
            'due_amount'		=>	$order_detail[0]['due_amount'],
            'total_discount'	=>	$order_detail[0]['total_discount'],
            'order_discount'	=>	$order_detail[0]['order_discount'],
            'service_charge'	=>	$order_detail[0]['service_charge'],
            'details'			=>	$order_detail[0]['details'],
            'order'				=>	$order_detail[0]['order'],
            'status'			=>	$order_detail[0]['status'],
            'timbrado'			=>	$order_detail[0]['timbrado'],
            'order_state_id'			=>	$order_detail[0]['order_state_id'],
            'variante_entrega'  =>  $order_detail[0]['variante_entrega'],
            'forma_pago'  =>  $order_detail[0]['forma_pago'],
            'order_all_data'	=>	$order_detail,
            'customer_name'	=>	$order_detail[0]['customer_env_name'],
            'customer_name_default'	=>	$order_detail[0]['customer_name'],
            'customer_phone_number'	=>	$order_detail[0]['customer_env_phone_number'],
            'customer_street'	=>	$order_detail[0]['customer_env_street'],
            'customer_exter_number'	=>	$order_detail[0]['customer_env_exter_number'],
            'customer_inter_number'	=>	$order_detail[0]['customer_env_inter_number'],
            'customer_between1'	=>	$order_detail[0]['customer_env_between1'],
            'customer_between2'	=>	$order_detail[0]['customer_env_between2'],
            'customer_colony'	=>	$order_detail[0]['customer_env_colony'],
            'customer_delegation'	=>	$order_detail[0]['customer_env_delegation'],
            'customer_state'	=>	$order_detail[0]['customer_env_state'],
            'customer_zip'	=>	$order_detail[0]['customer_env_zip'],
            'customer_refer'	=>	$order_detail[0]['customer_env_refer'],
            'order_state' => $order_state,
            'state' => $state,
        );

        //$chapterList = $CI->parser->parse('order/show_order',$data,true);
        return $data/*$chapterList*/;
    }
}
?>