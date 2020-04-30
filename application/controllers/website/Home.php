<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Home extends CI_Controller {

	function __construct() {
	    parent::__construct();
        date_default_timezone_set('America/Mexico_City');
		$this->load->library('website/Lhome');
        $this->load->library('Lorder');
		$this->load->library('paypal_lib');
		$this->load->model('website/Homes');
        $this->load->model('website/Homes');
		$this->load->model('Subscribers');
		$this->qrgenerator();
		$this->load->model('Customer_dashboards');
        $this->load->model('website/Products_model');
        $this->load->model('website/Settings');
        $this->load->model('Orders');
        $this->load->model('Stores');
        $this->load->model('Products');
        $this->load->model('Catalogues');
        $this->load->model('Soft_settings');
    }

	//Default loading for Home Index.
	public function index()
	{
        $this->session->unset_userdata('last_url');
        $this->session->set_userdata('last_url',base_url('home'));

        $content = $this->lhome->home_page();
        $this->template->full_website_html_view($content);
	}

    public function stores()
    {
        $content = $this->lhome->stores_page();
        $this->template->full_website_html_view($content);
    }
	
	public function clear_cache()
	{
		$CI =& get_instance();
		$path = $CI->config->item('cache_path');

		$cache_path = ($path == '') ? APPPATH.'cache/' : $path;

		$handle = opendir($cache_path);
		while (($file = readdir($handle))!== FALSE) 
		{
			//Leave the directory protection alone
			if ($file != '.htaccess' && $file != 'index.html')
			{
			   @unlink($cache_path.'/'.$file);
			}
		}
		closedir($handle);  
		echo "Cache cleared";
	}

	//Submit a subcriber.
	public function add_subscribe()
	{
		$data=array(
			'subscriber_id' => $this->generator(15),
			'apply_ip' 		=> $this->input->ip_address(),
			'email' 		=> $this->input->post('sub_email'),
			'status' 		=> 1
		);

		$result=$this->Subscribers->subscriber_entry($data);

		if ($result) {
			echo "2";
		}else{
			echo "3";
		}
	}

	//Add to cart 
	public function add_to_cart(){

		$data = array(
	        'id'      => $this->input->post('product_id'),
	        'qty'     => $this->input->post('qnty'),
	        'price'   => $this->input->post('price'),
	        'supplier_price'   => $this->input->post('supplier_price'),
	        'name'    => $this->input->post('name'),
	        'discount'=> $this->input->post('discount'),
	        'options' => array(
	        	'image' => $this->input->post('image'), 
	        	'model' => $this->input->post('model'),
	        	'cgst' 	=> $this->input->post('cgst'),
	        	'sgst' 	=> $this->input->post('sgst'),
	        	'igst' 	=> $this->input->post('igst'),
	        	'cgst_id' 	=> $this->input->post('cgst_id'),
	        	'sgst_id' 	=> $this->input->post('sgst_id'),
	        	'igst_id' 	=> $this->input->post('igst_id'),
	        )
		);
		$result = $this->cart->insert($data);
		if ($result) {
			echo "1";
		}
	}	

	//Add to cart for details
	public function add_to_cart_details(){

		$product_id = $this->input->post('product_id');
		$qnty 		= $this->input->post('qnty');
		$variant    = $this->input->post('variant');
        $store_id    = $this->input->post('store_id');

        $discount = 0;
		$onsale_price = 0;
		$cgst = 0;
		$cgst_id = 0;

		$sgst = 0;
		$sgst_id = 0;	

		$igst = 0;
		$igst_id = 0;

		if ($product_id) {
			$product_details = $this->Homes->product_details($product_id,$store_id);

            //CGST product tax
            $this->db->select('*');
            $this->db->from('tax_product_service');
            $this->db->where('product_id',$product_details->product_id);
            $this->db->where('tax_id','H5MQN4NXJBSDX4L');
            $tax_info = $this->db->get()->row();

            if (!empty($tax_info)) {
                if (($product_details->onsale_store == 1)) {
                    $cgst = $product_details->onsale_price_store - ($product_details->onsale_price_store/($tax_info->tax_percentage/100+1));
					$cgst = number_format((float)$cgst,'4','.','');
                    $cgst_id = $tax_info->tax_id;
                }else{
                    $cgst = $product_details->price_store - ($product_details->price_store/($tax_info->tax_percentage/100+1));
                    $cgst = number_format((float)$cgst,'4','.','');
					$cgst_id = $tax_info->tax_id;
                }
            }

			if ($product_details->onsale_store) {
			 	$price = $product_details->onsale_price_store /*- $cgst*/;
			 	$onsale_price = $product_details->onsale_price_store /*- $cgst*/;
			 	$discount = $product_details->price_store - $product_details->onsale_price_store;
			}else{
				$price = $product_details->price_store /*- $cgst*/;
			}

			//SGST product tax
			$this->db->select('*');
            $this->db->from('tax_product_service');
            $this->db->where('product_id',$product_details->product_id);
            $this->db->where('tax_id','52C2SKCKGQY6Q9J');
            $tax_info = $this->db->get()->row();
            
            if (!empty($tax_info)) {
            	if (($product_details->onsale == 1)) {
			 		//$sgst = ($tax_info->tax_percentage * $product_details->onsale_price_store)/100;
					$sgst = $product_details->onsale_price_store - ($product_details->onsale_price_store/($tax_info->tax_percentage/100+1));
					$sgst = number_format((float)$sgst,'4','.','');
			 		$sgst_id = $tax_info->tax_id;
			 	}else{
			 		//$sgst = ($tax_info->tax_percentage * $product_details->price_store)/100;
					$sgst = $product_details->price_store - ($product_details->price_store/($tax_info->tax_percentage/100+1));
			 		$sgst = number_format((float)$sgst,'4','.','');
					$sgst_id = $tax_info->tax_id;
			 	}
		 	}

		 	//IGST product tax
			$this->db->select('*');
            $this->db->from('tax_product_service');
            $this->db->where('product_id',$product_details->product_id);
            $this->db->where('tax_id','5SN9PRWPN131T4V');
            $tax_info = $this->db->get()->row();
            
            if (!empty($tax_info)) {
            	if (($product_details->onsale == 1)) {
			 		$igst = $product_details->onsale_price_store - ($product_details->onsale_price_store/($tax_info->tax_percentage/100+1));
					$igst = number_format((float)$igst,'4','.','');
			 		$igst_id = $tax_info->tax_id;
			 	}else{
			 		$igst = $product_details->price_store - ($product_details->price_store/($tax_info->tax_percentage/100+1));
					$igst = number_format((float)$igst,'4','.','');
			 		$igst_id = $tax_info->tax_id;
			 	}
		 	}

            //Shopping cart validation
		 	$flag = TRUE;
        	$dataTmp = $this->cart->contents();

		 	foreach ($dataTmp as $item) {
	            if (($item['product_id'] == $product_id) && ($item['variant'] == $variant)) {
	            	$data = array(
					        'rowid' => $item['rowid'],
					        'qty'   => $item['qty'] + $qnty
					);
					$this->cart->update($data);
	                $flag = FALSE;
	                break;
	            }
	        }

            if($flag) {
	        	$data = array(
			        'id'      => $this->generator(15),
			        'product_id'      => $product_details->product_id,
			        'qty'     => $qnty,
			        'price'   => $price,
			        'actual_price'   => $product_details->price_store,
			        'supplier_price' => $product_details->supplier_price_store,
			        'onsale_price'   => $onsale_price,
			        'name'    => $product_details->product_name,
			        'discount'=> $discount,
			        'variant' => $variant,
                    'promo' => $product_details->promo_store,
			        'options' => array(
			        	'image' => $product_details->image_thumb, 
			        	'model' => $product_details->product_model,
			        	'cgst' 	=> $cgst,
			        	'sgst' 	=> $sgst,
			        	'igst' 	=> $igst,
			        	'cgst_id' 	=> $cgst_id,
			        	'sgst_id' 	=> $sgst_id,
			        	'igst_id' 	=> $igst_id,
			        )
				);
				$result = $this->cart->insert($data);
            }
			echo "1";
		}
	}

    public function add_to_cart_one_details(){

        $product_id = $this->input->post('product_id');
        $qnty 		= $this->input->post('qnty');
        $variant    = $this->input->post('variant');
        $store_id    = $this->input->post('store_id');

        $discount = 0;
        $onsale_price = 0;
        $cgst = 0;
        $cgst_id = 0;

        $sgst = 0;
        $sgst_id = 0;

        $igst = 0;
        $igst_id = 0;

        if ($product_id) {
			$product_details = $this->Homes->product_details($product_id,$store_id);

            if($product_details->stock_store<$qnty)
            {
                echo '2';
            }
            else
            {
                //CGST product tax
                $this->db->select('*');
                $this->db->from('tax_product_service');
                $this->db->where('product_id',$product_details->product_id);
                $this->db->where('tax_id','H5MQN4NXJBSDX4L');
                $tax_info = $this->db->get()->row();

                if (!empty($tax_info)) {
                    if (($product_details->onsale_store == 1)) {
                        $cgst = $product_details->onsale_price_store - ($product_details->onsale_price_store/($tax_info->tax_percentage/100+1));
                        $cgst = number_format((float)$cgst,'4','.','');
                        $cgst_id = $tax_info->tax_id;
                    }else{
                        $cgst = $product_details->price_store - ($product_details->price_store/($tax_info->tax_percentage/100+1));
                        $cgst = number_format((float)$cgst,'4','.','');
                        $cgst_id = $tax_info->tax_id;
                    }
                }

                if ($product_details->onsale_store) {
                    $price = $product_details->onsale_price_store /*- $cgst*/;
                    $onsale_price = $product_details->onsale_price_store /*- $cgst*/;
                    $discount = $product_details->price_store - $product_details->onsale_price_store;
                }else{
                    $price = $product_details->price_store /*- $cgst*/;
                }

                //SGST product tax
                $this->db->select('*');
                $this->db->from('tax_product_service');
                $this->db->where('product_id',$product_details->product_id);
                $this->db->where('tax_id','52C2SKCKGQY6Q9J');
                $tax_info = $this->db->get()->row();

                if (!empty($tax_info)) {
                    if (($product_details->onsale == 1)) {
                        //$sgst = ($tax_info->tax_percentage * $product_details->onsale_price_store)/100;
                        $sgst = $product_details->onsale_price_store - ($product_details->onsale_price_store/($tax_info->tax_percentage/100+1));
                        $sgst = number_format((float)$sgst,'4','.','');
                        $sgst_id = $tax_info->tax_id;
                    }else{
                        //$sgst = ($tax_info->tax_percentage * $product_details->price_store)/100;
                        $sgst = $product_details->price_store - ($product_details->price_store/($tax_info->tax_percentage/100+1));
                        $sgst = number_format((float)$sgst,'4','.','');
                        $sgst_id = $tax_info->tax_id;
                    }
                }

                //IGST product tax
                $this->db->select('*');
                $this->db->from('tax_product_service');
                $this->db->where('product_id',$product_details->product_id);
                $this->db->where('tax_id','5SN9PRWPN131T4V');
                $tax_info = $this->db->get()->row();

                if (!empty($tax_info)) {
                    if (($product_details->onsale == 1)) {
                        $igst = $product_details->onsale_price_store - ($product_details->onsale_price_store/($tax_info->tax_percentage/100+1));
                        $igst = number_format((float)$igst,'4','.','');
                        $igst_id = $tax_info->tax_id;
                    }else{
                        $igst = $product_details->price_store - ($product_details->price_store/($tax_info->tax_percentage/100+1));
                        $igst = number_format((float)$igst,'4','.','');
                        $igst_id = $tax_info->tax_id;
                    }
                }

                //Shopping cart validation
                $flag = TRUE;
                $dataTmp = $this->cart->contents();

                foreach ($dataTmp as $item) {
                    if (($item['product_id'] == $product_id) && ($item['variant'] == $variant)) {
                        $data = array(
                            'rowid' => $item['rowid'],
                            'qty'   => $item['qty'] + $qnty
                        );
                        $this->cart->update($data);
                        $flag = FALSE;
                        break;
                    }
                }

                if ($flag) {
                    $data = array(
                        'id'      => $this->generator(15),
                        'product_id'      => $product_details->product_id,
                        'qty'     => $qnty,
                        'price'   => $price,
                        'actual_price'   => $product_details->price_store,
                        'supplier_price' => $product_details->supplier_price_store,
                        'onsale_price'   => $onsale_price,
                        'name'    => $product_details->product_name,
                        'discount'=> $discount,
                        'variant' => $variant,
                        'promo' => $product_details->promo_store,
                        'options' => array(
                            'image' => $product_details->image_thumb,
                            'model' => $product_details->product_model,
                            'cgst' 	=> $cgst,
                            'sgst' 	=> $sgst,
                            'igst' 	=> $igst,
                            'cgst_id' 	=> $cgst_id,
                            'sgst_id' 	=> $sgst_id,
                            'igst_id' 	=> $igst_id,
                        )
                    );
                    $result = $this->cart->insert($data);
                }
                echo "1";
            }


		}
		else
        {
            echo '2';
        }
    }

	//Delete item on your cart
	public function delete_cart(){
		$rowid = $this->input->post('row_id');
		$result = $this->cart->remove($rowid);
		if ($result) {
			echo "1";
		}
	}	


	//Delete item on your cart
	public function delete_cart_by_click($rowid){
		$result = $this->cart->remove($rowid);
		if ($result) {
			$this->session->set_userdata(array('message'=>display('successfully_updated')));
			redirect('view_cart');
		}
	}	

	//Update your cart
	public function update_cart(){

		$inputs = $this->input->post();
        $this->cart->update($inputs);
		$this->session->set_userdata(array('message'=>display('successfully_updated')));
		redirect('view_cart');
	}	

	//Set ship cost to cart
	public function set_ship_cost_cart(){

		$shipping_cost  = $this->input->post('shipping_cost');
		$ship_cost_name = $this->input->post('ship_cost_name');
		$method_id 		= $this->input->post('method_id');

        $this->session->set_userdata(
        						array(
							        	'cart_ship_cost' => $shipping_cost, 
							        	'cart_ship_name' => $ship_cost_name, 
							        	'method_id' 	 => $method_id, 
							        )
    							);
	}	

	//account_type_save
	public function account_type_save(){
		$account_type 	= $this->input->post('account_type');
		$this->session->set_userdata('account_info',$account_type);
	}

	//Account info save
	public function account_info_save($account_id){

        $payment_details  = $this->input->post('payment_details');
        $customer_variant  = $this->input->post('customer_variant');
        $payment_method  = $this->input->post('payment_method');
        $delivery_details  = $this->input->post('delivery_details');
        $fac_fp = $this->input->post('fac_fp');

        if($customer_variant=="2")
        {
            $customer_name  = $this->input->post('customer_name');
            $customer_phone_number  = $this->input->post('customer_phone_number');
            $customer_street  = $this->input->post('customer_street');
            $customer_exter_number  = $this->input->post('customer_exter_number');
            $customer_inter_number  = $this->input->post('customer_inter_number');
            $customer_between1  = $this->input->post('customer_between1');
            $customer_between2  = $this->input->post('customer_between2');
            $customer_colony  = $this->input->post('customer_colony');
            $customer_delegation  = $this->input->post('customer_delegation');
            $customer_state  = $this->input->post('customer_state');
            $customer_zip  = $this->input->post('customer_zip');
            $customer_refer  = $this->input->post('customer_refer');
        }
        else
        {
            $customer_name  = "";
            $customer_phone_number  = "";
            $customer_street  = "";
            $customer_exter_number  = "";
            $customer_inter_number  = "";
            $customer_between1  = "";
            $customer_between2  = "";
            $customer_colony  = "";
            $customer_delegation  = "";
            $customer_state  = "";
            $customer_zip  = "";
            $customer_refer  = "";
        }

        $this->session->set_userdata(
            array(
                'account_info' => $account_id,
                'customer_variant' => $customer_variant,
                //'customer_name' => $customer_name,
                'customer_phone_number' => $customer_phone_number,
                'customer_street' => $customer_street,
                'customer_exter_number' => $customer_exter_number,
                'customer_inter_number' => $customer_inter_number,
                'customer_between1' => $customer_between1,
                'customer_between2' => $customer_between2,
                'customer_colony' => $customer_colony,
                'customer_delegation' => $customer_delegation,
                'customer_state' => $customer_state,
                'customer_zip' => $customer_zip,
                'customer_refer' => $customer_refer,
                'payment_method' => $payment_method,
                'delivery_details' => $delivery_details,
                'payment_details' => $payment_details,
                'fac_fp' => $fac_fp
            )
        );

        /*if ($ship_and_bill == 1) {

			$this->session->set_userdata(
				array(
				 	'account_info' => $account_id, 
				 	'first_name'   => $first_name, 
				 	'last_name'    => $last_name, 
				 	'customer_email'  => $customer_email, 
				 	'customer_mobile' => $customer_mobile, 
				 	'customer_address_1'  => $customer_address_1, 
				 	'customer_address_2'  => $customer_address_2, 
				 	'company'  	=> $company, 
				 	'city'  	=> $city, 
				 	'zip'  		=> $zip, 
				 	'country'  	=> $country, 
				 	'state'  	=> $state, 
				 	'password'  => $password,
				 	'ship_and_bill'  => $ship_and_bill, 
				 	'privacy_policy' => $privacy_policy, 

				 	'ship_first_name'=> $first_name,  
				 	'ship_last_name' => $last_name,  
				 	'ship_company' 	 => $company,  
				 	'ship_mobile' 	 => $customer_mobile,  
				 	'ship_address_1' => $customer_address_1,  
				 	'ship_address_2' => $customer_address_2,  
				 	'ship_city' 	 => $city,  
				 	'ship_zip' 		 => $zip,  
				 	'ship_country' 	 => $country,  
				 	'ship_state' 	 => $state,  
				 	'payment_method' => $payment_method,  
				 	'delivery_details' => $delivery_details, 
				 	'payment_details' => $payment_details, 
				)
			);
			
		}else{
			$this->session->set_userdata(
				array(
				 	'account_info' => $account_id, 
				 	'first_name'   => $first_name, 
				 	'last_name'    => $last_name, 
				 	'customer_email'  => $customer_email, 
				 	'customer_mobile' => $customer_mobile, 
				 	'customer_address_1'  => $customer_address_1, 
				 	'customer_address_2'  => $customer_address_2, 
				 	'company'  	=> $company, 
				 	'city'  	=> $city, 
				 	'zip'  		=> $zip, 
				 	'country'  	=> $country, 
				 	'state'  	=> $state, 
				 	'password'  => $password, 
				 	'ship_and_bill'  => $ship_and_bill, 
				 	'privacy_policy' => $privacy_policy, 
				 	'ship_first_name'=> '',  
				 	'ship_last_name' => '',  
				 	'ship_company' 	 => '',  
				 	'ship_mobile' 	 => '',  
				 	'ship_address_1' => '',  
				 	'ship_address_2' => '',  
				 	'ship_city' 	 => '',  
				 	'ship_zip' 		 => '',  
				 	'ship_country' 	 => '',  
				 	'ship_state' 	 => '',  
				 	'payment_method' => '',  
				 	'delivery_details' => $delivery_details, 
				 	'payment_details' => $payment_details, 
				)
			);
		}*/
	}

	//Apply Coupon
	public function apply_coupon(){

		$this->form_validation->set_rules('coupon_code', display('coupon_code'), 'required');
		if ($this->form_validation->run() == FALSE){
            $this->session->set_userdata(array('error_msg'=>  validation_errors()));
	        redirect('view_cart');
        }else{
        	$coupon_code = $this->input->post('coupon_code');
			$result = $this->db->select('*')
								->from('coupon')
								->where('coupon_discount_code',$coupon_code)
								->where('status',1)
								->get()
								->row();

			if ($result) {
				$diff = abs(strtotime($result->end_date) - strtotime($result->start_date));
				$years 	= floor($diff / (365*60*60*24));
				$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
				$days 	= floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));

				if ((!empty($days) || !empty($months) || !empty($years))) {

					if ($result->discount_type == 1) {

						$this->db->set('status', '2');
						$this->db->where('coupon_discount_code', $result->coupon_discount_code);
						$this->db->update('coupon');

						$this->session->unset_userdata('coupon_amnt');
				    	$this->session->set_userdata('coupon_amnt',$result->discount_amount); 
				    	$this->session->set_userdata('message','Your coupon is used'); 
						redirect('view_cart');
					}elseif ($result->discount_type == 2) {
						$this->db->set('status', '2');
						$this->db->where('coupon_discount_code', $result->coupon_discount_code);
						$this->db->update('coupon');

						$total_dis = ($this->cart->total() * $result->discount_percentage)/100;
						$this->session->unset_userdata('coupon_amnt');
				    	$this->session->set_userdata('coupon_amnt',$total_dis); 
				    	$this->session->set_userdata('message',display('your_coupon_is_used')); 
						redirect('view_cart');
					}

				}else{
					$this->session->set_userdata('error_msg', display('coupon_is_expired'));
					redirect('view_cart');
				}
			}else{
				$this->session->set_userdata('error_msg',display('your_coupon_is_used')); 
				redirect('view_cart');
			}
        }
	}

	//Add Wish list
	public function add_wishlist(){

		if (!$this->user_auth->is_logged()) {
			echo '3';
		}else{
			$data = array(
				'wishlist_id'=> $this->generator(15),
				'user_id'	 => $this->input->post('customer_id'), 
				'product_id' => $this->input->post('product_id'), 
				'status' 	 => '1', 
			);
			$add_wishlist 	= $this->Homes->add_wishlist($data);

			if ($add_wishlist) {
				echo '1';
			}else{
				echo '2';
			}	
		}
	}		

	//Add Review
	public function add_review(){

		if (!$this->user_auth->is_logged()) {
			echo '3';
		}else{
			$data = array(
				'product_review_id'	=> $this->generator(15),
				'reviewer_id'	 	=> $this->input->post('customer_id'), 
				'product_id' 		=> $this->input->post('product_id'), 
				'comments' 	 		=> $this->input->post('review_msg'), 
				'rate' 	 			=> $this->input->post('rate'), 
				'status' 	 		=> '0', 
			);
			$add_review 	= $this->Homes->add_review($data);
			if ($add_review) {
				echo '1';
			}else{
				echo '2';
			}	
		}
	}	

	//Change Language
	public function change_language(){
		$language = $this->input->post('language');
		if ($language) {
			$this->session->unset_userdata('language');
			$this->session->set_userdata('language',$language);
			echo '2';
		}else{
			echo '3';
		}
	}

    //Change Language
    public function change_store(){
        $store_id = $this->input->post('store_id');
        $this->session->set_userdata('store_id',$store_id);
        $this->cart->destroy();
        echo $this->session->userdata('store_id');
    }

	//Change Currency
	public function change_currency(){
		$currency_id = $this->input->post('currency_id');
		if ($currency_id) {
			$this->session->unset_userdata('currency_new_id');
			$this->session->set_userdata('currency_new_id',$currency_id);
			echo '2';
		}else{
			echo '3';
		}
	}	

	//View Cart
	public function view_cart(){
		//echo count($this->cart->contents());die();
		$items = count($this->cart->contents());
		if($items==0)
		{
			redirect('home');
		}

        $this->session->unset_userdata('last_url');
        $this->session->set_userdata('last_url',base_url('view_cart'));

		$content = $this->lhome->view_cart();
		$this->template->full_website_html_view($content);
	}

    public function redirect_pay(){
        //echo count($this->cart->contents());die();
        $items = count($this->cart->contents());
        if($items==0)
        {
            redirect('home');
        }

        $content = $this->lhome->redirect_pay();
        $this->template->full_website_html_view($content);
    }

	//Retrive district
	public function retrive_district(){
		$country_id = $this->input->post('country_id');

		if ($country_id) {
			$select_district_info = $this->Homes->select_district_info($country_id);

			$html = "";
			if ($select_district_info) {
				$html .= "<select name=\"zone_id\" id=\"input-payment-zone\" class=\"form-control\" required=\"\">";
				foreach ($select_district_info as $district) {
					$html .= "<option value=".$district->name.">$district->name</option>";
				}
				$html .= "</select>";
				echo $html;
			}
		}
	}

	//Checkout
	public function checkout(){

        $this->load->model('Stores');

        $store_lock = $this->Stores->store_search_item($this->session->userdata('store_id'))['lock'];

        if($store_lock==0)
        {
            redirect('home');
        }

		if ($this->user_auth->is_logged())
		{
			$items = count($this->cart->contents());
			if($items==0)
			{
				redirect('home');
			}
			
			$content = $this->lhome->checkout();
			$this->template->full_website_html_view($content);
		}
		else
		{
		    $this->session->unset_userdata('last_url');
            $this->session->set_userdata('last_url',base_url('checkout'));
			redirect('login');
        }
	}

    //Checkout
    public function checkout_invoice($idOrder){

        $this->load->model('Stores');

        $store_lock = $this->Stores->store_search_item($this->session->userdata('store_id'))['lock'];

        if($store_lock==0)
        {
            redirect('home');
        }

        if ($this->user_auth->is_logged())
        {
            $content = $this->lhome->checkout_invoice($idOrder);
            $this->template->full_website_html_view($content);
        }
        else
        {
            redirect('login');
        }
    }

    public function submit_checkout_invoice(){
        $this->load->library('facturacion/Facturacion');
        $this->load->model('Stores');

        $store_lock = $this->Stores->store_search_item($this->session->userdata('store_id'))['lock'];

        if($store_lock==0)
        {
            redirect('home');
        }
		
        $idOrder = $this->input->post('idOrder');
        $order  = $this->lorder->order_by_id($idOrder);

        if($this->input->post('customer_variant')==1)
        {
            $nombreReceptor = $this->input->post('customer_name').' '.$this->input->post('customer_first_lastname').' '.$this->input->post('customer_secon_lastname');
        }
        else if($this->input->post('customer_variant')==2)
        {
            $nombreReceptor = $this->input->post('customer_social_reason');
        }

        $rfcReceptor = $this->input->post('customer_rfc');

        $rfcFacUso = $this->input->post('fac_uso');

        $receptor = array();
        $receptor['nombreReceptor'] = $nombreReceptor;
        $receptor['rfcReceptor'] = $rfcReceptor;
        $receptor['rfcFacUso'] = $rfcFacUso;

        $formaPago = $this->input->post('fac_fp');
        $metodoPago = $this->input->post('fac_mp');

        $comprobante = array();
        $comprobante['formaPago'] = $formaPago;
        $comprobante['metodoPago'] = $metodoPago;

        if ($this->user_auth->is_logged())
        {
            if($this->input->post('customer_save_data')=="1")
            {
                $data=array(
                    'customer_information_invoice_data_id' => $this->auth->generator(20),
                    'customer_id' => $this->session->userdata('customer_id'),
                    'customer_social_reason' 		=> $this->input->post('customer_social_reason'),
                    'customer_name' 		=> $this->input->post('customer_name'),
                    'customer_first_lastname' 		=> $this->input->post('customer_first_lastname'),
                    'customer_secon_lastname' 		=> $this->input->post('customer_secon_lastname'),
                    'customer_rfc' 		=> $this->input->post('customer_rfc'),
                    'customer_variant' 		=> $this->input->post('customer_variant')
                );

                $this->Customer_dashboards->insert_invoice_data($data);
            }

            $datos = $this->Soft_settings->retrieve_setting_invoice();

            $key = $this->config->item('santander_key');

            $this->load->library('santander/AESCrypto');

            $datos[0]['pac_pass'] = $this->aescrypto->desencriptar($datos[0]['pac_pass'], $key);

            $datos[0]['conf_pass'] = $this->aescrypto->desencriptar($datos[0]['conf_pass'], $key);

            $datos[0]['conf_rfc'] = $this->aescrypto->desencriptar($datos[0]['conf_rfc'], $key);
			
			$datos_fact = $this->facturacion->facturar($datos,$order,$receptor,$comprobante);

            if(is_array($datos_fact))
			{
			//if($this->facturacion->facturar($order,$receptor,$comprobante))
			//{
                $CI =& get_instance();
                $CI->load->model('Orders');
                $data = array();
                $data['timbrado'] = 1;
                $CI->Orders->update_timbre($idOrder,$datos_fact);

                $user_email = $this->session->userdata('customer_email');

                $user_name = $this->session->userdata('customer_name');

                $texto = "";
                $texto .= '<div>APRECIABLE SR. '.$user_name.'</div></br>';
                $texto .= '<div>AGRADECEMOS SU PREFERENCIA</div></br>';
                $texto .= '<div>ADJUNTAMOS PDF Y XML DE LA FACTURA # '.$order['order']->order.' </div></br>';
                $texto .= '<div>TE ESPERAMOS DE NUEVO EN  www.dipepsa.mx</div>';

                $path_pdf = array();
                $path_pdf[0] = FCPATH.'assets/timbrados/'.$idOrder.'.xml';
                $path_pdf[1] = FCPATH.'assets/timbrados/'.$idOrder.'.pdf';

                $this->Settings->send_mail_file($user_email,'Confirmación de facturación', $texto, $path_pdf);

                redirect('/customer/order/manage_order');
			}
			else
			{
				$this->session->set_userdata('error_message', "Error al realizar el timbrado electrónico, revice los datos de facturación");
            	redirect('/checkout_invoice/'.$idOrder);
			}
        }
        else
        {
            redirect('login');
        }
    }

	//Submit checkout
	public function submit_checkout(){
        //For page settings start
		$this->load->model('website/Homes');
		$this->load->model('Web_settings');
		$this->load->model('Soft_settings');
		$this->load->model('Blocks');
        $this->load->model('Stores');

        $store_lock = $this->Stores->store_search_item($this->session->userdata('store_id'))['lock'];

        if($store_lock==0)
        {
            redirect('home');
        }

        if ($this->user_auth->is_logged())
        {
            //$this->session->sess_expiration = 86400;

            $parent_category_list 	= $this->Homes->parent_category_list();
            $pro_category_list 		= $this->Homes->category_list();
            $best_sales 			= $this->Homes->best_sales();
            $footer_block 			= $this->Homes->footer_block();
            $slider_list 			= $this->Web_settings->slider_list();
            $block_list 			= $this->Blocks->block_list();
            $currency_details 		= $this->Soft_settings->retrieve_currency_info();
            $Soft_settings 			= $this->Soft_settings->retrieve_setting_editdata();
            $languages 				= $this->Homes->languages();
            $currency_info 			= $this->Homes->currency_info();
            $selected_currency_info = $this->Homes->selected_currency_info();
            $select_home_adds 		= $this->Homes->select_home_adds();

            //Settings code start
            $data['category_list'] 	= $parent_category_list;
            $data['pro_category_list'] 	= $pro_category_list;
            $data['slider_list'] 	= $slider_list;
            $data['block_list'] 	= $block_list;
            $data['best_sales'] 	= $best_sales;
            $data['footer_block'] 	= $footer_block;
            $data['languages'] 		= $languages;
            $data['currency_info'] 	= $currency_info;
            $data['select_home_adds'] 	= $select_home_adds;
            $data['selected_cur_id'] 	= (($selected_currency_info->currency_id)?$selected_currency_info->currency_id:"");
            $data['Soft_settings'] 	= $Soft_settings;
            $data['currency'] 		= $currency_details[0]['currency_icon'];
            $data['position'] 		= $currency_details[0]['currency_position'];
            //Setting code end

            //Payment method
            $order_id 		= $this->auth->generator(15);
            $payment_method = $this->session->userdata('payment_method');

            //Customer existing check
            $email 					 = $this->input->post('customer_email');
            $customer_existing_check = $this->Homes->check_customer($email);

            if ($customer_existing_check) {
                $customer_id = $customer_existing_check->customer_id;

                if($this->input->post('customer_save_data')=="1" && $this->input->post('customer_variant')=="2")
                {
                    $data=array(
                        'customer_information_send_data_id' => $this->auth->generator(20),
                        'customer_id' => $customer_id,
                        'customer_name' 		=> $this->input->post('customer_name'),
                        'customer_phone_number' 		=> $this->input->post('customer_phone_number'),
                        'customer_street' 		=> $this->input->post('customer_street'),
                        'customer_inter_number' 		=> $this->input->post('customer_inter_number'),
                        'customer_exter_number' 		=> $this->input->post('customer_exter_number'),
                        'customer_colony' 		=> $this->input->post('customer_colony'),
                        'customer_delegation' 		=> $this->input->post('customer_delegation'),
                        'customer_state' 		=> $this->input->post('customer_state'),
                        'customer_between1' 		=> $this->input->post('customer_between1'),
                        'customer_between2' 		=> $this->input->post('customer_between2'),
                        'customer_refer' 		=> $this->input->post('customer_refer'),
                        'customer_zip' 		=> $this->input->post('customer_zip'),
                    );

                    $this->Customer_dashboards->insert_send_data($data);
                }
                //Shipping data entry
                $ship_country_id = $this->session->userdata('ship_country');
                $ship_country 	 = $this->db->select('*')
                    ->from('countries')
                    ->where('id',$ship_country_id)
                    ->get()
                    ->row();
                //$ship_short_address = $this->session->userdata('ship_city').','.$this->session->userdata('ship_state').','.$ship_country->name.','.$this->session->userdata('ship_zip');
                $ship_short_address = $this->input->post('customer_street').', entre '.$this->input->post('customer_between1').' y '.$this->input->post('customer_between2').','.
                    $this->input->post('customer_zip');

                //New customer shipping entry
                $shipping=array(
                    'customer_id' 	=> $customer_id,
                    'customer_name' => $this->input->post('customer_name'),
                    'first_name' 	=> $this->input->post('customer_first_lastname'),
                    'last_name' 	=> $this->input->post('customer_secon_lastname'),
                    'customer_short_address'=> $ship_short_address,
                    'state' 		=> $this->input->post('customer_state'),
                    'zip' 			=> $this->input->post('customer_zip'),
                    'customer_mobile'=> $this->input->post('customer_phone_number'),
                    'customer_email' => $this->input->post('customer_email'),
                );

                //Shipping information entry
                $this->Homes->shipping_entry($shipping);

            }

            if ($payment_method == 4) {
                $store_id = $this->session->userdata('store_id');

                $return_order_id = $this->Homes->order_entry($customer_id, $order_id, $store_id);

                $id_company = $this->config->item('santander_id_company');
                $id_branch = $this->config->item('santander_id_branch');
                $id_user = $this->config->item('santander_id_user');
                $pass = $this->config->item('santander_pass');
                $key = $this->config->item('santander_key');
                $data_0 = $this->config->item('santander_data_0');
                $url = $this->config->item('santander_url');

                $cadena = '';
                $cadena = $cadena.'<P>';
                $cadena = $cadena.'<business>';
                $cadena = $cadena.'<id_company>'.$id_company.'</id_company>';
                $cadena = $cadena.'<id_branch>'.$id_branch.'</id_branch>';
                $cadena = $cadena.'<user>'.$id_user.'</user>';
                $cadena = $cadena.'<pwd>'.$pass.'</pwd>';
                $cadena = $cadena.'</business>';
                $cadena = $cadena.'<url>';
                $date = new DateTime();
                $tokend = $order_id.'-'.$customer_id.'-'.strtotime("now");
                $cadena = $cadena.'<reference>'.$tokend.'</reference>';
                $cadena = $cadena.'<amount>'.$this->session->userdata('cart_total').'</amount>';
                $cadena = $cadena.'<moneda>MXN</moneda>';
                $cadena = $cadena.'<canal>W</canal>';
                $cadena = $cadena.'<omitir_notif_default>1</omitir_notif_default>';
                $cadena = $cadena.'<promociones>C</promociones>';
                $cadena = $cadena.'<st_correo>1</st_correo>';
                $cadena = $cadena.'<fh_vigencia>'.$date->format('d/m/Y').'</fh_vigencia>';
                $cadena = $cadena.'<mail_cliente>'.$this->session->userdata('customer_email').'</mail_cliente>';
                $cadena = $cadena.'</url>';
                $cadena = $cadena.'</P>';

                $key = $key;

                $this->load->library('santander/AESCrypto');

                $cadenaEncriptada = $this->aescrypto->encriptar($cadena, $key);

                $encodedString = urlencode('<pgs><data0>'.$data_0.'</data0><data>'.$cadenaEncriptada.'</data></pgs>');

                $url = $url;

                $params['xml'] = $encodedString;

                $ch = curl_init();


                curl_setopt($ch,CURLOPT_URL,$url);
                curl_setopt($ch,CURLOPT_HTTPHEADER,array('content-type: application/x-www-form-urlencoded'));
                curl_setopt($ch,CURLOPT_POST,1);
                curl_setopt($ch,CURLOPT_POSTFIELDS,'xml='.$encodedString);
                curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

                try{
                    $ouput = curl_exec($ch);
                }
                catch (HttpException $ex)
                {
                    echo $ex;
                }

                curl_close($ch);

                $cadenaDesencriptadaXml = $this->aescrypto->desencriptar($ouput, $key);

                $liga = new DOMDocument;

                $liga->loadXML($cadenaDesencriptadaXml);

                $ligaUrl = $liga->getElementsByTagName('nb_url')->item(0)->nodeValue;
				
                if($liga->getElementsByTagName('nb_response')->item(0)->nodeValue=="")
				{
					$this->session->unset_userdata('token');
                	$this->session->set_userdata('token',$tokend);
					redirect($ligaUrl);
				}
				else
				{
					$this->session->set_userdata('error_message','Error al generar el pago, contacte al administrador!');
                    redirect('/checkout');
                }
			}
        }
        else
        {
            redirect('login');
        }
    }

	public function payeerSuccess() 
	{
		//Redirect value from get method
        /*$payment_method = $this->session->userdata('payment_method');

        if($payment_method == 10)
        {
            $request  = $this->input->get();
            $order_id = $request['m_orderid'];
            // $order_id = explode('_', $orderID);

            //Order entry
            $customer_id 	 = $this->session->userdata('customer_id');
            $return_order_id = $this->Homes->order_entry($customer_id,$order_id);
            $result   		 = $this->order_inserted_data($return_order_id);

            if ($result) {
                $this->cart->destroy();
                $this->session->set_userdata('message', display('product_successfully_order'));
                redirect("website/customer/home/");
            }
        }
        else{
            $request  = $this->input->post();
            $strResponse = $request['strResponse'];
            echo $strResponse;
            die();
        }*/

    }

	//Payeer fail
	public function payeerFail() 
	{
		$this->session->set_userdata('error_message', display('order_cancel'));
		redirect("website/customer/home/");
	}

	//Paypal success
	public function success()
    {
        //http://localhost/website/home/success/TJKZNBJKFJOHTWC/UOS82HBMKQ8ZOC8
        $data['title'] = display('order');
        #--------------------------------------
        //get the transaction data
        // $paypalInfo = $this->input->get();

        //Get order id and customer id
        $order_id = $this->uri->segment('4');
        $customer_id = $this->uri->segment('5');
        $store_id = $this->session->userdata('store_id');

        //session token
        $token = $this->session->userdata('_tran_token');

        /*if ($token != $order_id)
        {
            redirect('website/home/cancel');
        }*/

        //pass the transaction data to view
        $return_order_id = $this->Homes->order_entry($customer_id, $order_id, $store_id);
        $result = $this->order_inserted_data($return_order_id);

        $order = $this->lorder->order_by_id($order_id);

        $html = $this->generate_html($order);

        $user_email = $this->session->userdata('customer_email');
        $this->Settings->send_mail($user_email, 'Confirmación de compra', $html);

        if ($result) {
            $this->cart->destroy();
            $this->session->set_userdata('message', display('product_successfully_order'));
            redirect('/checkout_invoice/' . $order_id);
        }


    }

    public function integrarExistenciasPrecios()
    {
		$this->integrarUpdatePrecios('sucursal_prec_exist_001.txt','001');
		$this->integrarUpdatePrecios('sucursal_prec_exist_003.txt','003');
		$this->integrarUpdatePrecios('sucursal_prec_exist_004.txt','004');
		$this->integrarUpdatePrecios('sucursal_prec_exist_005.txt','005');
		$this->integrarUpdatePrecios('sucursal_prec_exist_008.txt','008');
		$this->integrarUpdatePrecios('sucursal_prec_exist_009.txt','009');
        $this->integrarUpdatePrecios('sucursal_prec_exist_011.txt','011');
		$this->integrarUpdatePrecios('sucursal_prec_exist_012.txt','012');
		$this->integrarUpdatePrecios('sucursal_prec_exist_013.txt','013');
		$this->integrarUpdatePrecios('sucursal_prec_exist_014.txt','014');
		die('Succeful');
    }
	
	public function integrarExistenciasPrecios1()
    {
		$this->integrarUpdatePrecios('sucursal_prec_exist_015.txt','015');
		$this->integrarUpdatePrecios('sucursal_prec_exist_016.txt','016');
		$this->integrarUpdatePrecios('sucursal_prec_exist_017.txt','017');
		$this->integrarUpdatePrecios('sucursal_prec_exist_022.txt','022');
		$this->integrarUpdatePrecios('sucursal_prec_exist_026.txt','026');
		$this->integrarUpdatePrecios('sucursal_prec_exist_027.txt','027');
        $this->integrarUpdatePrecios('sucursal_prec_exist_028.txt','028');
		$this->integrarUpdatePrecios('sucursal_prec_exist_029.txt','029');
		die('Succeful');
    }


    public function unlockStores()
    {
        $this->Stores->unlock_stores();
        die('Succeful');
    }

    public function integrarUpdatePrecios($fileName,$store)
    {
        $path = FCPATH.'/integracion/';
        $file = $path.$fileName;
        $produc_information = $this->Products->product_list_withOut_paginate($store);

        if($produc_information)
        {
            if(file_exists($file) && is_readable($file))
            {
                $archivo = fopen($file,"r");
                if($archivo)
                {
                    while(($line = fgets($archivo)) !==false){
                        $arrayLine = explode('|',$line);
                        $clave_interna = $arrayLine[1];
                        $stock = $arrayLine[2];
                        $precio_salto_linea = $arrayLine[3];
                        $precio = preg_replace("/[\n\r]/","",$precio_salto_linea);
                        foreach ($produc_information as $pro)
                        {
                            if($pro['clave_interna']==$clave_interna)
                            {
                                if($pro['price']!=$precio || $pro['stock']!=$stock)
                                {
                                    /*die($pro['catalogue_product_id']);*/
                                    $data = array('price' => $precio,'stock' => $stock);
                                    $this->Products->update_precio_stock($pro['catalogue_product_id'],$data);
                                    write_file($path.'update_price_'.$fileName,$clave_interna.' '.$precio."\r\n",'a');
                                }
                            }
                        }
                    }
                    fclose($archivo);
                }
                else
                {
                    die('Archivo inválido');
                }

            }
            else{
                die('FCPATH inválida');
            }
        }
        else
        {
            die('No existen productos');
        }
    }



    public function integrarVentas()
    {
        /*$orders = $this->Homes->order_today();
        $this->load->helper('file');
        if($orders)
        {
            foreach ($orders as $order)
            {
                $orderObj = $this->Orders->order_by_id($order['order_id']);
                $path = FCPATH.'/integracion';
                $file_name = 'VTA'.$orderObj['order']->store_id.'_'.date('Y').date('m').date('d').date('h').date('i').date('s').date('u').'.txt';
				$folio_timbrado = '';
				$date_timbrado ='';
                if($orderObj['order']->timbrado=='0')
                {
                    $timbrado = 'TI';
                    $date_timbrado = '|';
                }
                else
                {
                    $timbrado = 'TIF';
					$folio_timbrado = $orderObj['order']->folio_timbrado;
					//$date_timbrado = $orderObj['order']->date_timbrado;
					$date_timbrado = date('Ymd',strtotime($orderObj['order']->date_timbrado)).'|'.date('His',strtotime($orderObj['order']->date_timbrado));
                }
                $subTotal = number_format((float)$orderObj['order']->total_amount - $orderObj['order']->tax_amount,4,'.','');
                $impuestos = number_format((float)$orderObj['order']->tax_amount,4,'.','');
                $total = number_format((float)$orderObj['order']->total_amount,4,'.','');
				$forma_pago = $orderObj['order']->forma_pago;
                $cadenaHeader = 'HDR'.'|'.$orderObj['order']->store_id.'|'.date('Y').date('m').date('d').'|'.$orderObj['order']->order.'|'.$timbrado.'|'.$date_timbrado.'|'.$subTotal.'|'.$impuestos.'|'.$total.'|'.$forma_pago;;
                write_file($path.'/'.$file_name,$cadenaHeader."\r\n",'a');

                $order_details = $orderObj['order_details'];
                //var_dump($order_details);die();
                foreach ($order_details as $od)
                {
                    if($od['amount'])
                    {
                        $amount = number_format((float)$od['amount'],4,'.','');
                        $totalWithOutTax = number_format((float)($od['total_price']-($od['quantity']*$od['discount']))-$od['amount'],4,'.','');
                    }
                    else
                    {
                        $amount = 0;
                        $totalWithOutTax = number_format((float)($od['total_price']-($od['quantity']*$od['discount'])),4,'.','');
                    }
                    //$promo = $this->Catalogues->product_by_store($orderObj['order']->store_id,$od['product_id']);

                    $total = number_format((float)($od['total_price']-($od['quantity']*$od['discount'])),4,'.','');
                    $precioUnitario = number_format((float)$od['rate']-$od['discount'],4,'.','');
                    $cadenaDetalles = 'DET'.'|'.$orderObj['order']->store_id.'|'.date('Y').date('m').date('d').'|'.$orderObj['order']->order.'|'.$od['clave_interna'].'|'.$od['product_name'].'|'.$od['quantity'].'|'.$amount.'|'.$precioUnitario.'|'.$totalWithOutTax.'|'.$total;
                    if($od['promo']!='')
                    {
                        $cadenaDetalles = $cadenaDetalles.'|'.$od['promo'];
                    }
                    write_file($path.'/'.$file_name,$cadenaDetalles."\r\n",'a');
                }
            }
        }*/
    }

    public function try_pay_egain()
    {
        if ($this->user_auth->is_logged())
        {
            $id_company = $this->config->item('santander_id_company');
            $id_branch = $this->config->item('santander_id_branch');
            $id_user = $this->config->item('santander_id_user');
            $pass = $this->config->item('santander_pass');
            $key = $this->config->item('santander_key');
            $data_0 = $this->config->item('santander_data_0');
            $url = $this->config->item('santander_url');

            $token = $this->session->userdata('token');

            $token_array = explode('-',$token);

            $cadena = '';
            $cadena = $cadena.'<P>';
            $cadena = $cadena.'<business>';
            $cadena = $cadena.'<id_company>'.$id_company.'</id_company>';
            $cadena = $cadena.'<id_branch>'.$id_branch.'</id_branch>';
            $cadena = $cadena.'<user>'.$id_user.'</user>';
            $cadena = $cadena.'<pwd>'.$pass.'</pwd>';
            $cadena = $cadena.'</business>';
            $cadena = $cadena.'<url>';
            $date = new DateTime();
            $tokend = $token_array[0].'-'.$token_array[1].'-'.strtotime("now");
            $cadena = $cadena.'<reference>'.$tokend.'</reference>';
            $cadena = $cadena.'<amount>'.$this->session->userdata('cart_total').'</amount>';
            $cadena = $cadena.'<moneda>MXN</moneda>';
            $cadena = $cadena.'<canal>W</canal>';
            $cadena = $cadena.'<omitir_notif_default>1</omitir_notif_default>';
            $cadena = $cadena.'<promociones>C</promociones>';
            $cadena = $cadena.'<st_correo>1</st_correo>';
            $cadena = $cadena.'<fh_vigencia>'.$date->format('d/m/Y').'</fh_vigencia>';
            $cadena = $cadena.'<mail_cliente>'.$this->session->userdata('customer_email').'</mail_cliente>';
            /*$cadena = $cadena.'<datos_adicionales>';
                $cadena = $cadena.'<data id="1" display="false">';
                    $cadena = $cadena.'<label>Customer id</label>';
                    $cadena = $cadena.'<value>'.$customer_id.'</value>';
                $cadena = $cadena.'</data>';
                $cadena = $cadena.'<data id="2" display="false">';
                    $cadena = $cadena.'<label>Store id</label>';
                    $cadena = $cadena.'<value>'.$store_id.'</value>';
                $cadena = $cadena.'</data>';
            $cadena = $cadena.'</datos_adicionales>';*/
            $cadena = $cadena.'</url>';
            $cadena = $cadena.'</P>';

            $key = $key;

            $this->load->library('santander/AESCrypto');

            $cadenaEncriptada = $this->aescrypto->encriptar($cadena, $key);

            $encodedString = urlencode('<pgs><data0>'.$data_0.'</data0><data>'.$cadenaEncriptada.'</data></pgs>');

            $url = $url;

            $params['xml'] = $encodedString;

            $ch = curl_init();


            curl_setopt($ch,CURLOPT_URL,$url);
            curl_setopt($ch,CURLOPT_HTTPHEADER,array('content-type: application/x-www-form-urlencoded'));
            curl_setopt($ch,CURLOPT_POST,1);
            curl_setopt($ch,CURLOPT_POSTFIELDS,'xml='.$encodedString);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

            try{
                $ouput = curl_exec($ch);
            }
            catch (HttpException $ex)
            {
                echo $ex;
            }

            curl_close($ch);

            $cadenaDesencriptadaXml = $this->aescrypto->desencriptar($ouput, $key);

            $liga = new DOMDocument;

            $liga->loadXML($cadenaDesencriptadaXml);

            $ligaUrl = $liga->getElementsByTagName('nb_url')->item(0)->nodeValue;

            //echo $liga->getElementsByTagName('nb_response')->item(0)->nodeValue;die();
            if($liga->getElementsByTagName('nb_response')->item(0)->nodeValue=="")
            {
                $this->session->unset_userdata('token');
                $this->session->set_userdata('token',$tokend);
                redirect($ligaUrl);
            }
            else
            {
                $this->session->set_flashdata('error_message','Error al generar el pago, contacte al administrador!');
                redirect('/redirect_pay');
            }
        }
        else
        {
            redirect('/login');
        }

    }
	
	public function redirectSantander()
	{
        
            if($this->input->get('nbResponse')=="Rechazado")
            {
                $this->session->set_flashdata('message','¡Su pago por $'.$this->input->get('importe').' ha sido rechazado por el banco!');
                //$this->session->set_flashdata('message','¡Su pago por $'.$this->input->get('importe').' '.$this->input->get('nb_error'));
                redirect('/redirect_pay');
            }
            else if($this->input->get('nbResponse')=="Aprobado")
            {
                /*die('La order No. '.$this->input->get('referenciaPayment').' ha sido aprobada con exito !');*/
                //http://localhost/website/home/success/TJKZNBJKFJOHTWC/UOS82HBMKQ8ZOC8
                $data['title'] = display('order');
                #--------------------------------------
                //get the transaction data
                // $paypalInfo = $this->input->get();

                //Get order id and customer id
                $datos = explode('-',$this->input->get('referenciaPayment'));
                //$order_id = $this->input->get('referenciaPayment');
                //$customer_id = $this->session->userdata('customer_id');
                //$store_id = $this->session->userdata('store_id');

                $order_id = $datos[0];
                $customer_id = $datos[1];
                $date_time = $datos[2];
				
				/*$check = $this->Homes->order_check_exist($this->input->get('referenciaPayment'));
				
				if($check==false)
				{
					$this->session->set_userdata('error_message','Error de seguridad en la compra, contacte al administardor !');
                redirect('/checkout');
				}*/

                //session token
                $token = $this->session->userdata('token');
				
				$log = 'Envio: '.$token.' / Recibo '.$order_id.'-'.$customer_id.'-'.$date_time;

				$this->load->helper('file');

				$path = FCPATH.'/integracion';
				$file_name = 'log_error_seguridad_no_borrar';

				write_file($path.'/'.$file_name,$log."\r\n",'a');	

                if ($token != $order_id.'-'.$customer_id.'-'.$date_time)
                {
					$this->session->set_userdata('error_message','Error de seguridad en la compra, contacte al administardor !');
                    redirect('/checkout');
                }

                //pass the transaction data to view
                //$return_order_id = $this->Homes->order_entry($customer_id, $order_id, $store_id);
                //$result = $this->order_inserted_data($return_order_id);

                //$order = $this->lorder->order_by_id($order_id);

                //$html = $this->generate_html($order);

                //$user_email = $this->session->userdata('customer_email');
                //$this->Settings->send_mail($user_email, 'Confirmación de compra', $html);

                /*if ($result) {
                    $this->cart->destroy();
                    $this->session->set_userdata('message', display('product_successfully_order'));
                    redirect('/checkout_invoice/' . $order_id);
                }*/
                $referencia = $this->input->get('referencia');
                $importe = $this->input->get('importe');
                $auth = $this->input->get('nuAut');
                $empresa = $this->input->get('empresa');
                $this->cart->destroy();
                $msg = 'Tu pago con refrencia '.$referencia.' e importe $'.$importe.' ha sido aprobado por '.$empresa.' con número de autorización '.$auth.' y fecha '.date('d-m-Y H:i:s');
                $this->session->set_userdata('message', $msg);
                $this->session->unset_userdata('token');
                redirect('/checkout_invoice/' . $order_id);
            }
            else
            {
                $this->session->set_userdata('error_message','Lo sentimos, no podemos procesar la compra, si el error se repite, contacte al administardor !');
                redirect('/checkout');
            }
        /*}
        else
        {
            redirect('/login');
        }*/

	}

    /*public function successSantander()
    {
		$request  = $this->input->post();
        $strResponse = $request['strResponse'];
        if($strResponse)
        {
            $key = 'A538C3AE407B29A15F949674E4C6FD79';

			$this->load->library('santander/AESCrypto');

			$cadenaDesencriptadaXml = $this->aescrypto->desencriptar($strResponse, $key);

			$liga = new DOMDocument;

			$liga->loadXML($cadenaDesencriptadaXml);

			$reponse = $liga->getElementsByTagName('response')->item(0)->nodeValue;
			
			if($reponse=="approved")
			{
				$order_id = $liga->getElementsByTagName('reference')->item(0)->nodeValue;
				
				$data = array(
					'order_id' => $order_id
				); 
				$this->Homes->order_check($data);
			}
        }
        
    }*/
	
	public function successSantander()
    {
		//die('oka');
		/*$data = array(
			'order_id' => 'adasdada'
		); 
		$this->Homes->order_check($data);*/
        //http://localhost/website/home/success/TJKZNBJKFJOHTWC/UOS82HBMKQ8ZOC8
        $request  = $this->input->post();
        $strResponse = $request['strResponse'];
        if($strResponse)
        {
            //$key = 'A538C3AE407B29A15F949674E4C6FD79';

            $key = $this->config->item('santander_key');

			$this->load->library('santander/AESCrypto');

			$cadenaDesencriptadaXml = $this->aescrypto->desencriptar($strResponse, $key);

			$liga = new DOMDocument;

			$liga->loadXML($cadenaDesencriptadaXml);

			$reponse = $liga->getElementsByTagName('response')->item(0)->nodeValue;
            $reference = $liga->getElementsByTagName('reference')->item(0)->nodeValue;
            $email = $liga->getElementsByTagName('email')->item(0)->nodeValue;
            $cd_error = $liga->getElementsByTagName('cd_response')->item(0)->nodeValue;
            $nb_error = $liga->getElementsByTagName('nb_error')->item(0)->nodeValue;
            $time = $liga->getElementsByTagName('time')->item(0)->nodeValue;
            $date = $liga->getElementsByTagName('date')->item(0)->nodeValue;

            $log = $reference.' / '.$reponse.' / '.$cd_error.' / '.$nb_error.' / '.$date.' / '.$time.' / '.$this->session->userdata('customer_name');

            $this->load->helper('file');

            $path = FCPATH.'/integracion';
            $file_name = 'log_ventas_no_borrar';

            write_file($path.'/'.$file_name,$log."\r\n",'a');

            $database_log = array(
                'order_id' => explode('-',$reference)[0],
                'reference' => $reference,
                'state' => $reponse,
                'date' => date('Y-m-d H:i:s'),
                'xml' => $cadenaDesencriptadaXml
            );

            $this->Homes->add_order_santander_pay_log($database_log);
			
			if($reponse=="approved")
			{
			    $reference = $liga->getElementsByTagName('reference')->item(0)->nodeValue;
                $reference_array = explode('-',$reference);
				$this->Homes->pagar_order($reference_array[0]);

                $order = $this->lorder->order_by_id($reference_array[0]);

                $html = $this->generate_html($order);

                $this->Settings->send_mail($email, 'Confirmación de compra', $html);
			}
        }
    }



    public function generate_html($order)
    {
        $this->load->library('occational');
        $fechaCompra = $this->occational->dateConvert($order['order']->date);
        $user_name = $this->session->userdata('customer_name');
        $html = "";
        $html .= '<div>HOLA: '.$user_name.'</div></br>';
        $html .= '<div>Gracias por tu compra el '.$fechaCompra.'</div></br>';
        $html .= '<div>DATOS GENERALES DEL PEDIDO</div></br>';
        $html .= '<div>Número de pedido: '.$order['order']->order.'</div></br>';
        $html .= '<table>';
        $html .= '<tbody>';
        $html .= '<tr>';
        $html .= '<td style="width: 100px">Código</td>';
        $html .= '<td style="width: 400px">Descripción</td>';
        $html .= '<td style="width: 100px">Precio</td>';
        $html .= '</tr>';
        $orderDetails = $order['order_details'];
        foreach($orderDetails as $o)
        {
            $html .= '<tr>';
            $html .= '<td style="width: 100px">'.$o['product_id'].'</td>';
            $html .= '<td style="width: 100px">'.$o['product_name'].'</td>';
            $importe = round(($o['total_price']-($o['quantity']*$o['discount'])),2);
            $html .= '<td style="width: 100px">'.$importe.'</td>';
            $html .= '</tr></br>';
        }
        $html .= '</tbody>';
        $html .= '</table></br>';
        $html .= '<div>TOTAL: $'.$order['order']->total_amount.'</div></br>';
        /*$html .= '<div>ADJUNTAMOS PDF Y XML DE LA FACTURA # '.$order['order']->order.' </div></br>';
        $html .= '<div>TE ESPERAMOS DE NUEVO EN  www.dipepsa.mx</div>';*/
        return $html;
    }

    //Paypal cancel
    public function cancel($order_id = null)
    {  
       $this->session->set_userdata('error_message',display('transaction_faild'));
       redirect('/customer/order/manage_order');
    }

    /*
    * Add this ipn url to your paypal account
    * Profile and Settings > My selling tools > 
    * Instant Payment Notification (IPN) > update 
    * Notification URL: (eg:- http://domain.com/website/home/ipn/)
    * Receive IPN messages (Enabled) 
    */
    public function ipn()
    {
        //paypal return transaction details array
        $paypalInfo    = $this->input->post(); 

        $data['user_id']        = $paypalInfo['custom'];
        $data['product_id']     = $paypalInfo["item_number"];
        $data['txn_id']         = $paypalInfo["txn_id"];
        $data['payment_gross']  = $paypalInfo["mc_gross"];
        $data['currency_code']  = $paypalInfo["mc_currency"];
        $data['payer_email']    = $paypalInfo["payer_email"];
        $data['payment_status'] = $paypalInfo["payment_status"];

        $paypalURL = $this->paypal_lib->paypal_url;        
        $result    = $this->paypal_lib->curlPost($paypalURL,$paypalInfo);
        
        //check whether the payment is verified
        if(preg_match("/VERIFIED/i",$result)){
            //insert the transaction data into the database
            $this->load->model('paypal_model');
            $this->paypal_model->insertTransaction($data);
        }
    }


	//Retrive right now inserted data to create html
	public function order_inserted_data($order_id)
	{	
		$CI =& get_instance();
		$CI->load->library('website/Lhome');
		return $content = $CI->lhome->order_html_data($order_id,$this->session->userdata('customer_between1'));
	}

	//QR-Code Generator
	public function qrgenerator(){
		$this->load->library('ciqrcode');
		$config['cacheable']    = true; //boolean, the default is true
		$config['cachedir']     = ''; //string, the default is application/cache/
		$config['errorlog']     = ''; //string, the default is application/logs/
		$config['quality']      = true; //boolean, the default is true
		$config['size']         = '1024'; //interger, the default is 1024
		$config['black']        = array(224,255,255); // array, default is array(255,255,255)
		$config['white']        = array(70,130,180); // array, default is array(0,0,0)
		$this->ciqrcode->initialize($config);
		//Create QR code image create

		$params['data']  = 'https://play.google.com/store/apps/details?id=com.dipepsa&site='.base_url().'&valid=Dipepsa';
		$params['level'] = 'H';
		$params['size']  = 10;
		$image_name 	 = 'dipepsa_qr.png';
		$params['savename'] = FCPATH.'my-assets/image/qr/'.$image_name;
		$this->ciqrcode->generate($params);
		return true;
	}

	//This function is used to Generate Key
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
}