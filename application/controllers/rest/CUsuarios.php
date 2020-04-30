<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use \Firebase\JWT\JWT;

class CUsuarios extends MY_Controller
{
    function __construct() {
        parent::__construct();
		
		$this->load->model('website/customer/rest/Customer');
		$this->load->model('website/Settings');

    }
	
	public function activate_user_post()
    {
        $user_id = $this->input->post('user_id');

        $kunci = $this->config->item('thekey');

        $activate_user = $this->Customer->check_user_id($user_id);

        if ($activate_user)
        {
            $token['customer_id'] = $user_id;  //From here
            $token['customer_name'] = $activate_user[0]['customer_name'];
            $date = new DateTime();
            $token['iat'] = $date->getTimestamp();
            $token['exp'] = $date->getTimestamp() + 60*60*5; //To here is to generate token
            $output['token'] = JWT::encode($token,$kunci ); //This is the output token

            $result['result'] = '1';
            $result['data'] = $output;

            $this->set_response($result, REST_Controller::HTTP_OK); //This is the respon if success
        }
        else
        {
            $result['result'] = '2';
            $result['message'] = 'Error al activar el usuario';

            $this->response($result, REST_Controller::HTTP_OK);
        }
    }
	
	public function do_reset_password_post()
    {
        $email 		= $this->input->post('email');

        if($this->Customer->user_exist($email))
        {
            $password_code = $this->auth->generator(15);
            $data = array(
                'reset_password_code'=> $password_code
            );
            $customer_id = $this->Customer->user($email);
            if($this->Customer->update_reset_password_code($customer_id,$data))
            {
                $html = "";
                $html .= '<div>HOLA:</div></br>';
                $html .= '<div>Nos has solicitado un código para restablecer tu contraseña si no hace caso omiso a este email</div></br>';
                $html .= '<div>Código: '.$password_code.'</div></br>';
                $html .= '<div>Para ello, pulsa en el siguiente enlace: <a href="http://dipepsa.mx/code_password">Restablecer contraseña</a></div></br>';
                $this->Settings->send_mail($email,'Código: ', $html);

                $result['result'] = '1';
                $result['message'] = 'Se le ha enviado un enlace para restablecer contrasena';

                $this->response($result, REST_Controller::HTTP_OK);
            }
            else
            {
                $result['result'] = '2';
                $result['message'] = 'Error al generar código de restablecer contraseña';

                $this->response($result, REST_Controller::HTTP_OK);
            }

        }

        $result['result'] = '2';
        $result['message'] = 'Correo incorrecto';

        $this->response($result, REST_Controller::HTTP_OK);
    }

    public function do_login_post()
    {
		//$this->response($this->post('email'), REST_Controller::HTTP_OK);
		
        $email = $this->post('email'); //Username Posted
        $password = $this->post('password'); //Pasword Posted
        $kunci = $this->config->item('thekey');

        $user = $this->user_auth->login_app($email, $password);
        if($user === FALSE)
        {
            $result['result'] = '2';
            $result['message'] = 'Credenciales invalidas';

            $this->response($result, REST_Controller::HTTP_OK);
        }
        else
        {
            $token['customer_id'] = $user['customer_id'];  //From here
            $token['customer_name'] = $user['customer_name'];
            $date = new DateTime();
            $token['iat'] = $date->getTimestamp();
            $token['exp'] = $date->getTimestamp() + 60*60*5; //To here is to generate token
            $output['token'] = JWT::encode($token,$kunci ); //This is the output token

            $result['result'] = '1';
            $result['data'] = $output;

            $this->set_response($result, REST_Controller::HTTP_OK); //This is the respon if success
        }


        /*$val = $this->M_main->get_user($q)->row(); //Model to get single data row from database base on username*/
        /*if($this->M_main->get_user($q)->num_rows() == 0){$this->response($invalidLogin, REST_Controller::HTTP_NOT_FOUND);}
        $match = $val->password;   //Get password for user from database
        if($p == $match){  //Condition if password matched
            $token['id'] = $val->id;  //From here
            $token['username'] = $u;
            $date = new DateTime();
            $token['iat'] = $date->getTimestamp();
            $token['exp'] = $date->getTimestamp() + 60*60*5; //To here is to generate token
            $output['token'] = JWT::encode($token,$kunci ); //This is the output token
            $this->set_response($output, REST_Controller::HTTP_OK); //This is the respon if success
        }
        else {
            $this->set_response($invalidLogin, REST_Controller::HTTP_NOT_FOUND); //This is the respon if failed
        }*/
    }
	
	public function user_signup_post()
    {

        if($this->post('first_name')=='' || $this->post('last_name')=='' || $this->post('email')=='' || $this->post('password')=='' || $this->post('store_id')=='')
        {
            $result['result'] = '2';
            $result['data'] = 'Datos incorrectos';
            $this->response($result, 200); // OK (200) being the HTTP response code
        }

        $customer_id = $this->auth->generator(15);
        $data=array(
            'customer_id' 	=> $customer_id,
            'first_name' 	=> $this->input->post('first_name'),
            'last_name' 	=> $this->input->post('last_name'),
            'customer_name' => $this->input->post('first_name').' '.$this->input->post('last_name'),
            'customer_email'=> $this->input->post('email'),
            'image' 		=> base_url('assets/dist/img/user.png'),
            'password' 		=> md5("gef".$this->input->post('password')),
            'status' 		=> 2,
            'store_id' => $this->input->post('store_id'),
        );

        if($this->Customer->user_exist($this->input->post('email')))
        {
            $result['result'] = '2';
            $result['message'] = 'El correo ya existe en otro usuario';

            $this->response($result, REST_Controller::HTTP_OK);
        }

        $result_insert=$this->Customer->user_signup($data);

        if ($result_insert) {
            $this->Settings->send_mail($this->input->post('email'),display('activate_user'),'Gracias por registrarse en su tienda Dipepsa. Código de activación '.$customer_id);
            $result['result'] = '1';
            $result['data'] = $customer_id;
            $this->set_response($result, REST_Controller::HTTP_OK); //This is the respon if success
        }else{
            $result['result'] = '2';
            $result['message'] = 'Error en el registro';

            $this->response($result, REST_Controller::HTTP_OK);
        }
    }

    /*public function do_login_post()
    {
        $result = array();
        $email 		= $this->input->post('email');
        $password 	= $this->input->post('password');

        if($this->user_auth->is_logged())
        {
            $result['result'] = '2';
            $result['message'] = 'Error, el usuario ya esta autenticado';

            $this->response($result,201);
        }
        else if ( $email == '' || $password == ''  || $this->user_auth->login($email, $password) === FALSE){
            $result['result'] = '2';
            $result['message'] = 'Error de autentificacion';
        }
        else
        {
            $data = array();
            $data['customer_id'] = $this->session->userdata('customer_id');
            $data['customer_name'] = $this->session->userdata('customer_name');
            $data['customer_email'] = $this->session->userdata('customer_email');
            $result['result'] = '1';
            $result['data'] = $data;
            $result['pagination'] = false;
        }

        $this->response($result,201);
    }

    public function logout_get()
    {
        $result = array();
        if ($this->user_auth->logout()) {
            $result['result'] = '1';
            $result['message'] = 'Correcto';
        }
        else
        {
            $result['result'] = '2';
            $result['message'] = 'Error';
        }
        $this->response($result,201);
    }*/
	
	public function customer_get_general_data_get()
	{
		$this->auth();

		$theCredential = $this->user_data;

		$customer_general_data = $this->Customer->profile_edit_data($theCredential->customer_id);

		$result['result'] = '1';
		$result['data'] = $customer_general_data;
		$this->response($result, 200); // OK (200) being the HTTP response code
	}
	
	public function customer_update_general_data_post()
	{
		$this->auth();

		$theCredential = $this->user_data;

		$data=array(
            'first_name' 		=> $this->post('first_name'),
            'last_name' 		=> $this->post('last_name'),
            'customer_email' 		=> $this->post('customer_email'),
            'store_id' 		=> $this->post('store_id'),
        );

        if($this->post('first_name')=='' || $this->post('last_name')=='' || $this->post('customer_email')=='' || $this->post('store_id')=='')
        {
            $result['result'] = '2';
            $result['data'] = 'Datos incorrectos';
            $this->response($result, 200); // OK (200) being the HTTP response code
        }


        $this->Customer->update_general_data($theCredential->customer_id,$data);

        $result['result'] = '1';
        $result['data'] = 'Dato insertado';
        $this->response($result, 200); // OK (200) being the HTTP response code
	}
	
	public function customer_send_data_get()
    {
        $this->auth();

        $theCredential = $this->user_data;

        $customer_send_data = $this->Customer->admin_profile_send_data_list($theCredential->customer_id);

        $result['result'] = '1';
        $result['data'] = $customer_send_data;
        $this->response($result, 200); // OK (200) being the HTTP response code
    }
	
	
	
	public function customer_get_send_data_get($customer_information_send_data_id)
    {
        $this->auth();

        if($customer_information_send_data_id=='')
        {
            $result['result'] = '2';
            $result['data'] = 'Datos incorrectos';
            $this->response($result, 200); // OK (200) being the HTTP response code
        }


        $data = $this->Customer->get_send_data($customer_information_send_data_id);

        $result['result'] = '1';
        $result['data'] = $data;
        $this->response($result, 200); // OK (200) being the HTTP response code
    }

    public function customer_insert_send_data_post()
    {
        $this->auth();

        $theCredential = $this->user_data;

        $data=array(
            'customer_information_send_data_id' => $this->auth->generator(20),
            'customer_id' => $theCredential->customer_id,
            'customer_name' 		=> $this->post('customer_name'),
            'customer_phone_number' 		=> $this->post('customer_phone_number'),
            'customer_street' 		=> $this->post('customer_street'),
            'customer_inter_number' 		=> $this->post('customer_inter_number'),
            'customer_exter_number' 		=> $this->post('customer_exter_number'),
            'customer_colony' 		=> $this->post('customer_colony'),
            'customer_delegation' 		=> $this->post('customer_delegation'),
            'customer_state' 		=> $this->post('customer_state'),
            'customer_between1' 		=> $this->post('customer_between1'),
            'customer_between2' 		=> $this->post('customer_between2'),
            'customer_refer' 		=> $this->post('customer_refer'),
            'customer_zip' 		=> $this->post('customer_zip'),
        );

        if($this->post('customer_name')=='' || $this->post('customer_phone_number')=='' || $this->post('customer_street')=='' || $this->post('customer_exter_number')=='' || $this->post('customer_colony')=='' || $this->post('customer_delegation')=='' || $this->post('customer_state')=='' || $this->post('customer_state')=='' || $this->post('customer_between1')=='' || $this->post('customer_between2')=='' || $this->post('customer_zip')=='')
        {
            $result['result'] = '2';
            $result['data'] = 'Datos incorrectos';
            $this->response($result, 200); // OK (200) being the HTTP response code
        }


        $this->Customer->insert_send_data($data);

        $result['result'] = '1';
        $result['data'] = 'Dato insertado';
        $this->response($result, 200); // OK (200) being the HTTP response code
    }

    public function customer_update_send_data_post()
    {
        $this->auth();

        $theCredential = $this->user_data;

        $data=array(
            'customer_id' => $theCredential->customer_id,
            'customer_name' 		=> $this->post('customer_name'),
            'customer_phone_number' 		=> $this->post('customer_phone_number'),
            'customer_street' 		=> $this->post('customer_street'),
            'customer_inter_number' 		=> $this->post('customer_inter_number'),
            'customer_exter_number' 		=> $this->post('customer_exter_number'),
            'customer_colony' 		=> $this->post('customer_colony'),
            'customer_delegation' 		=> $this->post('customer_delegation'),
            'customer_state' 		=> $this->post('customer_state'),
            'customer_between1' 		=> $this->post('customer_between1'),
            'customer_between2' 		=> $this->post('customer_between2'),
            'customer_refer' 		=> $this->post('customer_refer'),
            'customer_zip' 		=> $this->post('customer_zip'),
        );

        if($this->post('customer_information_send_data_id')=='' || $this->post('customer_name')=='' || $this->post('customer_phone_number')=='' || $this->post('customer_street')=='' || $this->post('customer_exter_number')=='' || $this->post('customer_colony')=='' || $this->post('customer_delegation')=='' || $this->post('customer_state')=='' || $this->post('customer_state')=='' || $this->post('customer_between1')=='' || $this->post('customer_between2')=='' || $this->post('customer_zip')=='')
        {
            $result['result'] = '2';
            $result['data'] = 'Datos incorrectos';
            $this->response($result, 200); // OK (200) being the HTTP response code
        }


        $this->Customer->update_send_data($this->post('customer_information_send_data_id'),$data);

        $result['result'] = '1';
        $result['data'] = 'Dato modificado';
        $this->response($result, 200); // OK (200) being the HTTP response code
    }

    public function customer_delete_send_data_delete($customer_information_send_data_id)
    {
        $this->auth();

        $theCredential = $this->user_data;

        if($customer_information_send_data_id=='')
        {
            $result['result'] = '2';
            $result['data'] = 'Datos incorrectos';
            $this->response($result, 200); // OK (200) being the HTTP response code
        }


        $this->Customer->delete_send_data($customer_information_send_data_id);

        $result['result'] = '1';
        $result['data'] = 'Dato eliminado';
        $this->response($result, 200); // OK (200) being the HTTP response code
    }
	
	public function customer_invoice_data_get()
    {
        $this->auth();

        $theCredential = $this->user_data;

        $customer_invoice_data = $this->Customer->admin_profile_invoice_data_list($theCredential->customer_id);

        $result['result'] = '1';
        $result['data'] = $customer_invoice_data;
        $this->response($result, 200); // OK (200) being the HTTP response code
    }

    public function customer_get_invoice_data_get($customer_information_invoice_data_id)
    {
        $this->auth();

        if($customer_information_invoice_data_id=='')
        {
            $result['result'] = '2';
            $result['data'] = 'Datos incorrectos';
            $this->response($result, 200); // OK (200) being the HTTP response code
        }


        $data = $this->Customer->get_invoice_data($customer_information_invoice_data_id);

        $result['result'] = '1';
        $result['data'] = $data;
        $this->response($result, 200); // OK (200) being the HTTP response code
    }

    public function customer_insert_invoice_data_post()
    {
        $this->auth();

        $theCredential = $this->user_data;

        if($this->post('customer_variant')=='')
        {
            $result['result'] = '2';
            $result['data'] = 'Datos incorrectos';
            $this->response($result, 200); // OK (200) being the HTTP response code
        }

        if($this->post('customer_variant')=='1')
        {
            if($this->post('customer_name')=='' || $this->post('customer_first_lastname')=='' || $this->post('customer_secon_lastname')=='' || $this->post('customer_rfc')=='')
            {
                $result['result'] = '2';
                $result['data'] = 'Datos incorrectos';
                $this->response($result, 200); // OK (200) being the HTTP response code
            }

            $data=array(
                'customer_information_invoice_data_id' => $this->auth->generator(20),
                'customer_id' => $theCredential->customer_id,
                'customer_name' 		=> $this->post('customer_name'),
                'customer_first_lastname' 		=> $this->post('customer_first_lastname'),
                'customer_secon_lastname' 		=> $this->post('customer_secon_lastname'),
                'customer_rfc' 		=> $this->post('customer_rfc')
            );

            $this->Customer->insert_invoice_data($data);

            $result['result'] = '1';
            $result['data'] = 'Dato insertado';
            $this->response($result, 200); // OK (200) being the HTTP response code
        }
        else if($this->post('customer_variant')=='2')
        {
            if($this->post('customer_rfc')=='' || $this->post('customer_social_reason')=='')
            {
                $result['result'] = '2';
                $result['data'] = 'Datos incorrectos';
                $this->response($result, 200); // OK (200) being the HTTP response code
            }

            $data=array(
                'customer_information_invoice_data_id' => $this->auth->generator(20),
                'customer_id' => $theCredential->customer_id,
                'customer_rfc' 		=> $this->post('customer_rfc'),
                'customer_social_reason' 		=> $this->post('customer_social_reason'),
            );

            $this->Customer->insert_invoice_data($data);

            $result['result'] = '1';
            $result['data'] = 'Dato insertado';
            $this->response($result, 200); // OK (200) being the HTTP response code
        }


        $result['result'] = '2';
        $result['data'] = 'Customer variant inválido';
        $this->response($result, 200); // OK (200) being the HTTP response code

    }

    public function customer_update_invoice_data_post()
    {
        $this->auth();

        $theCredential = $this->user_data;

        if($this->post('customer_variant')=='' || $this->post('customer_information_invoice_data_id')=='')
        {
            $result['result'] = '2';
            $result['data'] = 'Datos incorrectos';
            $this->response($result, 200); // OK (200) being the HTTP response code
        }

        if($this->post('customer_variant')=='1')
        {
            if($this->post('customer_name')=='' || $this->post('customer_first_lastname')=='' || $this->post('customer_secon_lastname')=='' || $this->post('customer_rfc')=='')
            {
                $result['result'] = '2';
                $result['data'] = 'Datos incorrectos';
                $this->response($result, 200); // OK (200) being the HTTP response code
            }

            $data=array(
                'customer_id' => $theCredential->customer_id,
                'customer_name' 		=> $this->post('customer_name'),
                'customer_first_lastname' 		=> $this->post('customer_first_lastname'),
                'customer_secon_lastname' 		=> $this->post('customer_secon_lastname'),
                'customer_rfc' 		=> $this->post('customer_rfc')
            );

            $this->Customer->update_invoice_data($this->post('customer_information_invoice_data_id'),$data);

            $result['result'] = '1';
            $result['data'] = 'Dato modificado';
            $this->response($result, 200); // OK (200) being the HTTP response code
        }
        else if($this->post('customer_variant')=='2')
        {
            if($this->post('customer_rfc')=='' || $this->post('customer_social_reason')=='')
            {
                $result['result'] = '2';
                $result['data'] = 'Datos incorrectos';
                $this->response($result, 200); // OK (200) being the HTTP response code
            }

            $data=array(
                'customer_id' => $theCredential->customer_id,
                'customer_rfc' 		=> $this->post('customer_rfc'),
                'customer_social_reason' 		=> $this->post('customer_social_reason'),
            );

            $this->Customer->update_invoice_data($this->post('customer_information_invoice_data_id'),$data);

            $result['result'] = '1';
            $result['data'] = 'Dato modificado';
            $this->response($result, 200); // OK (200) being the HTTP response code
        }


        $result['result'] = '2';
        $result['data'] = 'Customer variant inválido';
        $this->response($result, 200); // OK (200) being the HTTP response code
    }

    public function customer_delete_invoice_data_delete($customer_information_invoice_data_id)
    {
        $this->auth();

        $theCredential = $this->user_data;

        if($customer_information_invoice_data_id=='')
        {
            $result['result'] = '2';
            $result['data'] = 'Datos incorrectos';
            $this->response($result, 200); // OK (200) being the HTTP response code
        }


        $this->Customer->delete_invoice_data($customer_information_invoice_data_id);

        $result['result'] = '1';
        $result['data'] = 'Dato eliminado';
        $this->response($result, 200); // OK (200) being the HTTP response code
    }



}

?>