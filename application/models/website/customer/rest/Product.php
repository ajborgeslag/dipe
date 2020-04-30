<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Product extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}

    public function search_products($store_id,$product_name)
    {

        $this->db->select('
                product_information.product_id,product_information.product_name
            ')
            ->from('catalogue_products')
            ->join('store_set', 'store_set.catalogue_id = catalogue_products.catalogue_id','left')
            ->join('product_information', 'product_information.product_id = catalogue_products.product_id','left');
        $this->db->where('store_set.store_id',$store_id);

        $this->db->like('product_information.product_name', $product_name, 'both')
            ->order_by('product_information.product_name','asc')
            ->group_by('product_information.product_id');
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->result();
        }
        return false;
    }
} 
