<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class COrder extends MY_Controller
{
    function __construct()
    {
		/*header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Headers: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');*/
		//header('Access-Control-Allow-Origin: *');
    	//header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
		//header("Access-Control-Allow-Origin: *");
        //header("Access-Control-Allow-Headers: token");
        //header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");*/
		//header("Access-Control-Allow-Credentials: true");
		/*header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Authorization");*/
        // Construct the parent class
        parent::__construct();
		
		
		
		//header("Access-Control-Allow-Methods: GET, OPTIONS");
		//header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
        $this->auth();
        //$this->response($this->user_data, 200); // OK (200) being the HTTP response code
        //$this->load->model('website/customer/Orders');
		date_default_timezone_set('America/Mexico_City');
        $this->load->model('website/Homes');
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
	
	public function comprar_error_post()
    {
        $theCredential = $this->user_data;
        $customer_id = $theCredential->customer_id;

        $store_id = $this->post('store_id');
        if(!$store_id)
        {
            $data['error'] = 'El campo store_id es obligatorio';
            $this->response($data, 200);
        }

        $order_id = $this->post('order_id');
        if(!$order_id)
        {
            $data['error'] = 'El campo order_id es obligatorio';
            $this->response($data, 200);
        }

        date_default_timezone_set('America/Mexico_City');

        $this->load->model('website/Homes');
		$this->load->model('Web_settings');
		$this->load->model('Soft_settings');
		$this->load->model('Blocks');
        $this->load->model('Stores');

        $array_data = explode("-",$order_id);

        $return_order_id = $array_data[0];

        $this->load->model('website/customer/rest/Orders');
        $cart_total =  $this->Orders->order_total($return_order_id)['total_amount'];
        

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
        $tokend = $return_order_id.'-'.$customer_id.'-'.strtotime("now");
        $cadena = $cadena.'<reference>'.$tokend.'</reference>';
        $cadena = $cadena.'<amount>'.$cart_total.'</amount>';
        $cadena = $cadena.'<moneda>MXN</moneda>';
        $cadena = $cadena.'<canal>W</canal>';
        $cadena = $cadena.'<omitir_notif_default>1</omitir_notif_default>';
        $cadena = $cadena.'<promociones>C</promociones>';
        $cadena = $cadena.'<st_correo>1</st_correo>';
        $cadena = $cadena.'<fh_vigencia>'.$date->format('d/m/Y').'</fh_vigencia>';
        $cadena = $cadena.'<mail_cliente>'.'testapi@gmail.com'.'</mail_cliente>';
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
            $result['result'] = '1';
            $result['order_id'] = $tokend;
            $result['data'] = $ligaUrl;
            $result['cart_total'] = $cart_total;
            $this->response($result, 200); // OK (200) being the HTTP response code
        }
        else
        {
            $result['result'] = '2';
            $result['message'] = 'Error al generar la liga de pago';
			$this->response($result, 200); // OK (200) being the HTTP response code
        }
    }

    public function verificar_compra_post()
	{
        $order_id = $this->post('order_id');
        if(!$order_id)
        {
            $data['error'] = 'El campo order_id es obligatorio';
            $this->response($data, 200);
        }
        $this->load->model('website/customer/rest/Orders');
        $order_log = $this->Orders->order_santander_state($order_id);

        if($order_log)
        {
            if($order_log['state']=='denied')
            {

                $data['result'] = 2;
                $data['xml'] = $order_log['xml'];
                $data['order_id'] = $order_id;
		        $this->response($data, 200);    
            }
            else if($order_log['state']=='approved')
            {
                $data['result'] = 1;
                $data['xml'] = $order_log['xml'];
                $data['order_id'] = $order_id;
		        $this->response($data, 200);    
            }
            
        }
        else
        {
            $data['result'] = 3;
		    $this->response($data, 200);
        }
	}

    public function comprar_post()
    {

        $theCredential = $this->user_data;
        $customer_id = $theCredential->customer_id;

        $store_id = $this->post('store_id');
        if(!$store_id)
        {
            $data['error'] = 'El campo store_id es obligatorio';
            $this->response($data, 200);
        }

        $variante_entrega = $this->post('variante_entrega');
        if(!$variante_entrega)
        {
            $data['error'] = 'El campo variante_entrega es obligatorio';
            $this->response($data, 200);
        }
        
        if($variante_entrega=='2')
        {
            $customer_data = $this->post('customer_data');
            if(!$customer_data || is_array($customer_data)==false)
            {
                $data['error'] = 'El campo customer_data es un array obligatorio';
                $this->response($data, 200);
            }
        }

        $payment_method = $this->post('payment_method');
        if(!$payment_method)
        {
            $data['error'] = 'El campo payment_method es obligatorio';
            $this->response($data, 200);
        }

        $fac_fp = $this->post('fac_fp');
        if(!$fac_fp)
        {
            $data['error'] = 'El campo fac_fp es obligatorio';
            $this->response($data, 200);
        }

        $products = $this->post('products');
        if(!$products || is_array($products)==false)
        {
            $data['error'] = 'El campo products es un array obligatorio';
            $this->response($data, 200);
        }

        $order_id = $this->generator(15);

        $products = $this->post('products');

        $cart_content = array();
        $indice = 0;
        foreach($products as $product)
        {
            $data = $this->create_row_data($product,$store_id);

            $cart_content[$indice] = $data;
            $indice = $indice + 1;
        }

        $total_discount = 0; $total = 0;
        $cgst = 0; $sgst = 0; $igst = 0;

        foreach($cart_content as $items)
        {
            $total = $total + ($items['price'] * $items['qty']);
            $total_discount = $total_discount + ($items['discount'] * $items['qty']);
            $cgst = $cgst + ($items['options']['cgst'] * $items['qty']);
            $sgst = $sgst + ($items['options']['sgst'] * $items['qty']);
            $igst = $igst + ($items['options']['igst'] * $items['qty']);
        }

        $this->load->model('website/customer/rest/Orders');

        //$this->response($this->Orders->order_entry_app($customer_id,$order_id,$store_id,$total,$total_discount,$variante_entrega,$customer_data,$payment_method,$fac_fp), 200);

        /*if($this->Orders->order_entry_app($customer_id,$order_id,$store_id,$total,$total_discount,$variante_entrega,$customer_data,$payment_method,$fac_fp)==false)
        {
            $data['error'] = 'Error al procesar la compra';
            $this->response($data, 200);
        }*/
        $this->Orders->order_entry_app($cart_content,$customer_id,$order_id,$store_id,$total,$total_discount,$variante_entrega,$customer_data,$payment_method,$fac_fp);

        $this->generate_liga_pago($order_id,$store_id,$customer_id,$total);
    }
	
	public function facturar_post(){
		
        $this->load->library('facturacion/Facturacion');
        $this->load->model('Stores');
		
		
		
		$this->response(true, 200); // OK (200) being the HTTP response code

        /*$store_lock = $this->Stores->store_search_item($this->session->userdata('store_id'))['lock'];

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
        }*/
    }

    public function create_row_data($product,$store_id)
    {
        
        $product_id = $product['product'];
        $qnty 		= $product['qty'];
        
        if(!$product_id)
        {
            $data['error'] = 'Error en los datos del carro de compra, product_id desconocido';
            $this->response($data, 200);
        }

        if(!$qnty)
        {
            $data['error'] = 'Error en los datos del carro de compra, qnty desconocido';
            $this->response($data, 200);
        }
		
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

            //$this->response($product_details, 200);

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
             
             $data = array(
                /*'id'      => $this->generator(15),*/
                'product_id'      => $product_details->product_id,
                'qty'     => $qnty,
                'price'   => $price,
                'actual_price'   => $product_details->price_store,
                'supplier_price' => $product_details->supplier_price_store,
                'onsale_price'   => $onsale_price,
                'name'    => $product_details->product_name,
                'discount'=> $discount,
                'variant' => '',
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
            
            return $data; 
        }
    }

    public function customer_manage_order_post()
	{
        $this->load->model('website/customer/rest/Orders');
        $theCredential = $this->user_data;
        $orders = $this->Orders->customer_order_list($theCredential->customer_id);


        $result['result'] = '1';
        $result['data'] = $orders;
        $this->response($result, 200); // OK (200) being the HTTP response code
	}
	
	public function customer_manage_order_edit_post()
    {
        $this->load->model('website/customer/rest/Orders');

        $order_id = $this->post('order_id');

        $order_details = $this->Orders->retrieve_order_editdata($order_id);


        $result['result'] = '1';
        $result['data'] = $order_details;
        $this->response($result, 200); // OK (200) being the HTTP response code
    }
	
	public function generate_liga_pago($order_id,$store_id,$customer_id,$cart_total)
    {
        date_default_timezone_set('America/Mexico_City');

        $this->load->model('website/Homes');
		$this->load->model('Web_settings');
		$this->load->model('Soft_settings');
		$this->load->model('Blocks');
        $this->load->model('Stores');

        $return_order_id = $order_id;

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
        $tokend = $return_order_id.'-'.$customer_id.'-'.strtotime("now");
        $cadena = $cadena.'<reference>'.$tokend.'</reference>';
        $cadena = $cadena.'<amount>'.$cart_total.'</amount>';
        $cadena = $cadena.'<moneda>MXN</moneda>';
        $cadena = $cadena.'<canal>W</canal>';
        $cadena = $cadena.'<omitir_notif_default>1</omitir_notif_default>';
        $cadena = $cadena.'<promociones>C</promociones>';
        $cadena = $cadena.'<st_correo>1</st_correo>';
        $cadena = $cadena.'<fh_vigencia>'.$date->format('d/m/Y').'</fh_vigencia>';
        $cadena = $cadena.'<mail_cliente>'.'testapi@gmail.com'.'</mail_cliente>';
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
            $result['result'] = '1';
            $result['order_id'] = $tokend;
            $result['data'] = $ligaUrl;
            $result['cart_total'] = $cart_total;
            $this->response($result, 200); // OK (200) being the HTTP response code
        }
        else
        {
            $result['result'] = '2';
            $result['message'] = 'Error al generar la liga de pago';
        }
    }
}