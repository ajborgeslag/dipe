<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Corder extends CI_Controller {
	
	function __construct() {
      parent::__construct();
	  
    }
	public function index()
	{
		$CI =& get_instance();
		$CI->auth->check_admin_auth();
		$CI->load->library('Lorder');
		$content = $CI->lorder->order_add_form();
		$this->template->full_admin_html_view($content);
	}
	//Add new order
	public function new_order()
	{
		$CI =& get_instance();
		$CI->auth->check_admin_auth();
		$CI->load->library('Lorder');
		$content = $CI->lorder->order_add_form();
		$this->template->full_admin_html_view($content);
	}
	
	public function create_interface_form()
	{
        $CI =& get_instance();
		$CI->auth->check_admin_auth();
		$CI->load->library('Lorder');
		$content = $CI->lorder->create_interface_form();
		$this->template->full_admin_html_view($content);
	}

	public function save_interface()
	{
		date_default_timezone_set('America/Mexico_City');

		$date = $_POST['date'];
		$store_id = $_POST['store_id'];

		if($date != date('d-m-Y'))
		{
			if (!$this->auth->is_logged() )
			{
				$this->output->set_header("Location: ".base_url().'admin', TRUE, 302);
			}
			$this->auth->check_admin_store_auth();//

			$CI =& get_instance();
			$CI->load->model('Orders');
			$date_array = explode('-',$date);
			$date_d = $date_array[0];
			$date_m = $date_array[1];
			$date_Y = $date_array[2];
			$date = $date_array[1].'-'.$date_array[0].'-'.$date_array[2];
			$orders = $CI->Orders->order_date_by_store($date,$store_id);
			$this->load->helper('file');
			if($orders)
			{
				foreach ($orders as $order)
				{
					$orderObj = $this->Orders->order_by_id($order['order_id']);
					$path = FCPATH.'/integracion';
					$path_salva = FCPATH.'/integracion_salva';
					$file_name = 'VTA'.$orderObj['order']->store_id.'_'.$date_Y.$date_m.$date_d.'.txt';
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
					$cadenaHeader = 'HDR'.'|'.$orderObj['order']->store_id.'|'.$date_Y.$date_m.$date_d.'|'.$orderObj['order']->order.'|'.$timbrado.'|'.$date_timbrado.'|'.$subTotal.'|'.$impuestos.'|'.$total.'|'.$forma_pago;;
					write_file($path.'/'.$file_name,$cadenaHeader."\r\n",'a');
					write_file($path_salva.'/'.$file_name,$cadenaHeader."\r\n",'a');

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
						$cadenaDetalles = 'DET'.'|'.$orderObj['order']->store_id.'|'.$date_Y.$date_m.$date_d.'|'.$orderObj['order']->order.'|'.$od['clave_interna'].'|'.$od['product_name'].'|'.$od['quantity'].'|'.$amount.'|'.$precioUnitario.'|'.$totalWithOutTax.'|'.$total;
						if($od['promo']!='')
						{
							$cadenaDetalles = $cadenaDetalles.'|'.$od['promo'];
						}
						else
						{
							$cadenaDetalles = $cadenaDetalles.'|';
						}
						write_file($path.'/'.$file_name,$cadenaDetalles."\r\n",'a');
						write_file($path_salva.'/'.$file_name,$cadenaDetalles."\r\n",'a');
					}
				}
			}

			$this->session->set_userdata(array('message'=>'Interfaz generada con éxito'));
			redirect(base_url('Corder/create_interface_form'));

		}
		else
		{
			$this->session->set_userdata(array('error_message'=>'Solo puede generar una interfaz de días anteriores'));
			redirect(base_url('Corder/create_interface_form'));
		}
	}
	
	//Insert product and upload
	public function insert_order()
	{
		$CI =& get_instance();
		$CI->auth->check_admin_auth();
		$CI->load->model('Orders');
		$order_id = $CI->Orders->order_entry();
		$this->session->set_userdata(array('message'=>display('successfully_added')));
		$this->order_inserted_data($order_id);

		if(isset($_POST['add-order'])){
			redirect(base_url('Corder/manage_order'));
		}elseif(isset($_POST['add-order-another'])){
			redirect(base_url('Corder'));
		}
	}
	//Retrive right now inserted data to cretae html
	public function order_inserted_data($order_id)
	{	
		$CI =& get_instance();
		$CI->auth->check_admin_auth();
		$CI->load->library('Lorder');
		$content = $CI->lorder->order_html_data($order_id);	
		$this->template->full_admin_html_view($content);
	}

	//Retrive right now inserted data to cretae html
	public function order_details_data($order_id)
	{	
		$CI =& get_instance();
		$CI->auth->check_admin_auth();
		$CI->load->library('Lorder');
		$content = $CI->lorder->order_details_data($order_id);	
		$this->template->full_admin_html_view($content);
	}

	//Manage order
	public function manage_order()
	{	
		$CI =& get_instance();
		$this->auth->check_admin_cont_auth();
		$CI->load->library('Lorder');
		$CI->load->model('Orders');

        $content = $CI->lorder->order_list();
        $this->template->full_admin_html_view($content);
	}
	
	//order Update Form
	public function order_update_form($order_id)
	{	
		$CI =& get_instance();
		$CI->auth->check_admin_auth();
		$CI->load->library('Lorder');
		$content = $CI->lorder->order_edit_data($order_id);
		$this->template->full_admin_html_view($content);
	}
	//Search Inovoice Item
	public function search_inovoice_item()
	{
		$CI =& get_instance();
		$this->auth->check_admin_auth();
		$CI->load->library('Lorder');
		
		$customer_id = $this->input->post('customer_id');
        $content = $CI->lorder->search_inovoice_item($customer_id);
		$this->template->full_admin_html_view($content);
	}
	// order Update
	public function order_update()
	{
		$CI =& get_instance();
		$CI->auth->check_admin_auth();
		$CI->load->model('Orders');
		$order_id = $CI->Orders->update_order();
		$this->session->set_userdata(array('message'=>display('successfully_updated')));
		$this->order_inserted_data($order_id);
	}
	public function order_state_update()
    {
        $order_state = $this->input->post('order_state');
        $order_id = $this->input->post('order_id');
        $data = array();
        $data['order_state_id'] = $order_state;
        $CI =& get_instance();
        $CI->load->model('Orders');
        $CI->load->model('website/Settings');
        $CI->Orders->update_order_state($order_id,$data);
        $customer_email = $CI->Orders->order_customer_id($order_id)[0]['customer_email'];

        $order_num = $CI->Orders->order_customer_id($order_id)[0]['order'];

        $order_state_id = $CI->Orders->order_customer_id($order_id)[0]['order_state_id'];

        $variante_entrega = $CI->Orders->order_customer_id($order_id)[0]['variante_entrega'];

        $state = $CI->Orders->order_customer_id($order_id)[0]['state'];

        if($order_state_id==3){
            if($variante_entrega==1){
                $state = $state.' recoger';
            }
            else
            {
                $state = $state.' enviar';
            }
        }


        $html = '';
        $html .= '<div> Estimado cliente, su pedido No '.$order_num.' '.$state.'</div></br>';

        $this->session->set_userdata(array('message'=>display('successfully_updated')));

        $CI->Settings->send_mail($customer_email,'Cambio de estado del pedido No.'.$order_num,$html);


        redirect('Corder/store_show_order/'.$order_id);
    }
	// order paid data
	public function order_paid_data($order_id){
		$CI =& get_instance();
		$CI->auth->check_admin_auth();
		$CI->load->model('Orders');
		$order_id = $CI->Orders->order_paid_data($order_id);
		$this->session->set_userdata(array('message'=>display('successfully_added')));
		redirect('Corder/manage_order');
	}

	// retrieve_product_data
	public function retrieve_product_data()
	{	
		$CI =& get_instance();
		$this->auth->check_admin_auth();
		$CI->load->model('Orders');
		$product_id = $this->input->post('product_id');
		$product_info = $CI->Orders->get_total_product($product_id);
		echo json_encode($product_info);
	}
	// product_delete
	public function order_delete($order_id)
	{	
		$CI =& get_instance();
		$this->auth->check_admin_auth();
		$CI->load->model('Orders');
		$result = $CI->Orders->delete_order($order_id);
		if ($result) {
			$this->session->set_userdata(array('message'=>display('successfully_delete')));
			redirect('Corder/manage_order');
		}	
	}
	//AJAX order STOCKs
	public function product_stock_check($product_id)
	{
		$CI =& get_instance();
		$this->auth->check_admin_auth();
		$CI->load->model('Orders');

		$purchase_stocks = $CI->Orders->get_total_purchase_item($product_id);	
		$total_purchase = 0;		
		if(!empty($purchase_stocks)){	
			foreach($purchase_stocks as $k=>$v){
				$total_purchase = ($total_purchase + $purchase_stocks[$k]['quantity']);
			}
		}
		$sales_stocks = $CI->Orders->get_total_sales_item($product_id);
		$total_sales = 0;	
		if(!empty($sales_stocks)){	
			foreach($sales_stocks as $k=>$v){
				$total_sales = ($total_sales + $sales_stocks[$k]['quantity']);
			}
		}
		
		$final_total = ($total_purchase - $total_sales);
		return $final_total ;
	}

	//Search product by product name and category
	public function search_product(){
		$CI =& get_instance();
		$this->auth->check_admin_auth();
		$CI->load->model('Orders');
		$product_name = $this->input->post('product_name');
		$category_id  = $this->input->post('category_id');
		$product_search = $this->Orders->product_search($product_name,$category_id);
        if ($product_search) {
            foreach ($product_search as $product) {
            echo "<div class=\"col-xs-6 col-sm-4 col-md-2 col-p-3\">";
                echo "<div class=\"panel panel-bd product-panel select_product\">";
                    echo "<div class=\"panel-body\">";
                        echo "<img src=\"$product->image_thumb\" class=\"img-responsive\" alt=\"\">";
                        echo "<input type=\"hidden\" name=\"select_product_id\" class=\"select_product_id\" value='".$product->product_id."'>";
                    echo "</div>";
                    echo "<div class=\"panel-footer\">$product->product_model - $product->product_name</div>";
                echo "</div>";
            echo "</div>";
        	}
        }else{
        	echo "420";
        }
	}

	//Insert new customer
	public function insert_customer(){
		$CI =& get_instance();
		$this->auth->check_admin_auth();
		$CI->load->model('Orders');

		$customer_id=$this->auth->generator(15);

	  	//Customer  basic information adding.
		$data=array(
			'customer_id' 		=> $customer_id,
			'customer_name' 	=> $this->input->post('customer_name'),
			'customer_mobile' 	=> $this->input->post('mobile'),
			'customer_email' 	=> $this->input->post('email'),
			'status' 			=> 1
			);

		$result=$this->Orders->customer_entry($data);
		
		if ($result == TRUE) {		
			$this->session->set_userdata(array('message'=>display('successfully_added')));
			redirect(base_url('Corder/pos_order'));
		}else{
			$this->session->set_userdata(array('error_message'=>display('already_exists')));
			redirect(base_url('Corder/pos_order'));
		}
	}

	//This function is used to Generate Key
	public function generator($lenth)
	{
		$number=array("1","2","3","4","5","6","7","8","9");
	
		for($i=0; $i<$lenth; $i++)
		{
			$rand_value=rand(0,8);
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

    //Add new order
    public function store_show_order($order_id)
    {
        $CI =& get_instance();
        $this->auth->check_admin_cont_auth();
        $CI->load->library('Lorder');
        $CI->load->model('Orders');
        $content = $CI->lorder->order_show_data($order_id);
        $this->template->full_admin_html_view($content);
    }

    public function store_print_order($order_id)
    {
        $CI =& get_instance();
        $this->auth->check_admin_cont_auth();
        $CI->load->library('Lorder');
        $CI->load->model('Orders');
        $CI->load->library('Pdfgenerator');

        /*$content = $CI->lorder->order_show_data($order_id);
        $this->template->full_admin_html_view($content);*/
        //$html = "welcome";
        $data = $CI->lorder->order_print_data($order_id);
        $dompdf = new DOMPDF();

        $html = '';

        if($data['timbrado']=="0")
        {
            $facturado = "NO";
        }
        else
        {
            $facturado = "SI";
        }

        $html.='<div style="width:724px; margin:0 auto; font-family: Lato Black !important;">';
        $html.='<table border="1" style="width: 100%; height: 100px;" cellspacing="0" cellpadding="0">';
        $html .= '<tbody>';
        $html .= '<tr>';
        $html .= '<td style="padding: 5px">NO. PEDIDO: </td>';
        $html .= '<td style="padding: 5px">'.$data['order'].'</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td style="padding: 5px">TIENDA: </td>';
        $html .= '<td style="padding: 5px">'.$data['store_name'].'</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td style="padding: 5px">ESTADO: </td>';
        $html .= '<td style="padding: 5px">'.$data['state'].'</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td style="padding: 5px">FECHA: </td>';
        $html .= '<td style="padding: 5px">'.$data['date'].'</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td style="padding: 5px">FORMA DE PAGO: </td>';
        $html .= '<td style="padding: 5px">'.$data['forma_pago'].'</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td style="padding: 5px">FACTURA: </td>';
        $html .= '<td style="padding: 5px">'.$facturado.'</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td style="padding: 5px">NOMBRE CLIENTE: </td>';
        $html .= '<td style="padding: 5px">'.$data['customer_name_default'].'</td>';
        $html .= '</tr>';
        $html .= '</tbody>';
        $html.='</table>';

        $html.='<table border="1" style="margin-top:25px; width: 100%;" cellspacing="0" cellpadding="0">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<td style="padding: 5px">Clave interna</td>';
        $html .= '<td style="padding: 5px">Cantidad</td>';
        $html .= '<td style="padding: 5px">Unidad</td>';
        $html .= '<td style="padding: 5px">Nombre del producto</td>';
        $html .= '<td style="padding: 5px">Precio</td>';
        $html .= '<td style="padding: 5px">Total</td>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';
        $i = 1; $cgst = 0; $sgst = 0; $igst = 0; $discount = 0;$coupon_amnt=0;
        foreach ($data['order_all_data'] as $items):
            if (!empty($items['options']['cgst'])) {
                $cgst = $cgst + ($items['options']['cgst'] * $items['qty']);
            }

            if (!empty($items['options']['sgst'])) {
                $sgst = $sgst + ($items['options']['sgst'] * $items['qty']);
            }

            if (!empty($items['options']['igst'])) {
                $igst = $igst + ($items['options']['igst'] * $items['qty']);
            }

            //Calculation for discount
            if (!empty($items['discount'])) {
                $discount = $discount + ($items['discount'] * $items['qty']) + $this->session->userdata('coupon_amnt');
                $this->session->set_userdata('total_discount',$discount);
            }
        $html .= '<tr>';
        $html .= '<td style="padding: 5px">'.$items['clave_interna'].'</td>';
        $html .= '<td style="padding: 5px">'.$items['quantity'].'</td>';
        $html .= '<td style="padding: 5px">'.$items['unit_short_name'].'</td>';
        $html .= '<td style="padding: 5px">'.$items['product_name'].'</td>';
        $html .= '<td style="padding: 5px">'.(($data['position']==0)?$data['currency']." ". $this->cart->format_number($items['rate']-$items['discount']):$this->cart->format_number($items['rate']-$items['discount'])." ". $data['currency']).'</td>';
        $html .= '<td style="padding: 5px">'.(($data['position']==0)?$data['currency'] ." ". $this->cart->format_number(($items['rate']-$items['discount']) * $items['quantity']):$this->cart->format_number(($items['rate']-$items['discount']) * $items['quantity'])." ". $data['currency']).'</td>';
        $html .= '</tr>';
        endforeach;
        $html .= '</tbody>';
        $total_tax = $cgst+$sgst+$igst;
        $html .= '<tfoot>';
        if ($total_tax > 0) {

            $html .= '<tr>';
            $html .= '<td colspan="5" style="text-align: right;padding: 5px"><strong>IVA 16% :</strong></td>';
            $html .= '<td style="text-align: right;padding: 5px">'.(($data['position']==0)?$data['currency'] ." ". number_format($total_tax, 2, '.', ','):number_format($total_tax, 2, '.', ',')." ". $data['currency']).'</strong></td>';
            $html .= '</tr>';
        }
        $html .= '<tr>';
        $html .= '<td colspan="5" style="text-align: right;padding: 5px"><strong>Total :</strong></td>';

        $cart_total = $this->cart->total() + $this->_cart_contents['cart_ship_cost'] + $total_tax - $coupon_amnt;

        $this->session->set_userdata('cart_total',$cart_total);

        $total_amnt = $this->_cart_contents['cart_total'] = $cart_total;

        $html .= '<td style="text-align: right;padding: 5px">'.(($data['position']==0)?$data['currency'] ." ". number_format($data['total_amount'], 2, '.', ','):number_format($data['total_amount'], 2, '.', ',')." ". $data['currency']).'</strong></td>';
        $html .= '</tr>';
        $html .= '</tfoot>';
        $html.='</table>';

        $html.='<table border="1" style="margin-top:25px; width: 100%;" cellspacing="0" cellpadding="0">';
        $html .= '<thead>';
        if($data['variante_entrega']=="1")
        {
            $html .= '<tr>';
            $html .= '<td style="text-align: center;padding: 5px">El cliente pasa a recoger el producto</td>';
            $html .= '</tr>';
        }
        else
        {
            $html .= '<tr>';
            $html .= '<td colspan="2" style="text-align: center;padding: 5px">Datos de envío</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<td style="padding: 5px">Quién Recibe: </td>';
            $html .= '<td style="padding: 5px">'.$data['customer_name'].'</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<td style="padding: 5px">Número Telefónico: </td>';
            $html .= '<td style="padding: 5px">'.$data['customer_phone_number'].'</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<td style="padding: 5px">Calle: </td>';
            $html .= '<td style="padding: 5px">'.$data['customer_street'].'</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<td style="padding: 5px">Número Exterior: </td>';
            $html .= '<td style="padding: 5px">'.$data['customer_exter_number'].'</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<td style="padding: 5px">Número Interior: </td>';
            $html .= '<td style="padding: 5px">'.$data['customer_inter_number'].'</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<td style="padding: 5px">Entre: </td>';
            $html .= '<td style="padding: 5px">'.$data['customer_between1'].'</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<td style="padding: 5px">Entre: </td>';
            $html .= '<td style="padding: 5px">'.$data['customer_between2'].'</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<td style="padding: 5px">Colonia: </td>';
            $html .= '<td style="padding: 5px">'.$data['customer_colony'].'</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<td style="padding: 5px">Delegación: </td>';
            $html .= '<td style="padding: 5px">'.$data['customer_delegation'].'</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<td style="padding: 5px">Estado: </td>';
            $html .= '<td style="padding: 5px">'.$data['customer_state'].'</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<td style="padding: 5px">Código Postal: </td>';
            $html .= '<td style="padding: 5px">'.$data['customer_zip'].'</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<td style="padding: 5px">Lugar de Referencia: </td>';
            $html .= '<td style="padding: 5px">'.$data['customer_refer'].'</td>';
            $html .= '</tr>';
        }
        $html .= '</thead>';
        $html .= '</table>';

        $html.='<div style="margin-top: 70px">';
        $html.='<p style="text-align: justify;padding: 30px"> ANTES DE FIRMAR SUPLICAMOS CHECAR Y REVISAR SU MERCANCÍA, NO ACEPTAMOS RECLAMACIONES. EL RESGUARDO DE SU MERCACÍA SERÁ DE 72 HRS A PARTIR DE SU CONFIRMACIÓN DE ENTREGA. RECUERDE QUE CONTAMOS CON SERVICIO A DOMICILIO';
        $html.='</p>';

        $html.='<div style="margin-top: 20px">';
        $html.='<p style="text-align: center"> ____________________________________________';
        $html.='</p>';

        $html.='<div style="margin-top: 10px">';
        $html.='<p style="text-align: center"> FIRMA';
        $html.='</p>';

        $html.='</div>';

        $html.='</div>';

        $dompdf->load_html($html);


        $dompdf->render();
        $dompdf->stream("pedido_".$order_id,array("Attachment"=>0));
    }
}