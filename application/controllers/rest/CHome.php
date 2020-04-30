<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CHome extends REST_Controller
{
    function __construct() {
        parent::__construct();
        $this->load->library('website/Lcategory');
        $this->load->model('website/Categories');
        $this->load->model('website/Products_model');
        $this->load->model('Web_settings');
        $this->load->model('Stores');

        $this->load->library('website/Lhome');
        $this->load->library('Lorder');
        $this->load->library('paypal_lib');
        $this->load->model('website/Homes');
        $this->load->model('website/Homes');
        $this->load->model('Subscribers');
        //$this->qrgenerator();
        $this->load->model('Customer_dashboards');
        $this->load->model('website/Products_model');
        $this->load->model('website/Settings');
    }

    public function slider_list_get()
    {
        $sliders = $this->Web_settings->slider_list();

        if($sliders)
        {
            $result['result'] = '1';
            $result['data'] = $sliders;
            $result['totals_rows'] = count($sliders);
            $result['pagination'] = false;
        }
        else
        {
            $result['result'] = '2';
            $result['message'] = 'No existen sliders';
        }

        $this->response($result,201);
    }





}

?>