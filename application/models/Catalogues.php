<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Catalogues extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}
	//unit List
	public function catalogue_list()
	{
		$this->db->select('*');
		$this->db->from('catalogue');
		$this->db->order_by('catalogue_name','asc');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result_array();	
		}
		return false;
	}

	public function product_catalogue_search_item_list($catalogue_id,$product_clave,$product_name)
    {
        $this->db->select('
                    catalogue_products.catalogue_product_id,
					product_information.*,
					product_category.category_name,catalogue_products.price as price_store,catalogue_products.onsale as onsale_store,catalogue_products.onsale_price as onsale_price_store,catalogue_products.promo as promo_store,catalogue_products.end_promo as end_promo_store
				')
            ->from('catalogue_products')
            ->join('product_information', 'product_information.product_id = catalogue_products.product_id','left')
            ->join('product_category','product_category.category_id = product_information.category_id','left')
            ->where('catalogue_products.catalogue_id',$catalogue_id);
            if($product_clave!='')
            {
                $this->db->where('product_information.clave_interna',$product_clave);
            }
            if($product_name!='')
            {
                $this->db->like('product_information.product_name', $product_name, 'both');
            }
            $this->db->order_by('product_information.product_name','asc')
            ->group_by('product_information.product_id');
            $query = $this->db->get();
            if ($query->num_rows() > 0) {
                return $query->result_array();
            }
        return false;
    }

    public function product($catalogue_id,$per_page=null,$page=null)
    {

        $query=$this->db->select('
                    catalogue_products.catalogue_product_id,
					product_information.*,
					product_category.category_name,catalogue_products.price as price_store,catalogue_products.onsale as onsale_store,catalogue_products.onsale_price as onsale_price_store,catalogue_products.promo as promo_store,catalogue_products.end_promo as end_promo_store
				')
            ->from('catalogue_products')
            ->join('product_information', 'product_information.product_id = catalogue_products.product_id','left')
            ->join('product_category','product_category.category_id = product_information.category_id','left')
            ->where('catalogue_products.catalogue_id',$catalogue_id)
            ->order_by('product_information.product_name','asc')
            ->group_by('product_information.product_id')
            ->limit($per_page,$page)
            ->get();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return false;
    }

    public function product_list_count($catalogue_id)
    {

        $query=$this->db->select('
                    catalogue_products.catalogue_product_id,
					product_information.*,
					product_category.category_name,catalogue_products.price as price_store,catalogue_products.onsale as onsale_store,catalogue_products.onsale_price as onsale_price_store,catalogue_products.promo as promo_store
				')
            ->from('catalogue_products')
            ->join('product_information', 'product_information.product_id = catalogue_products.product_id','left')
            ->join('product_category','product_category.category_id = product_information.category_id','left')
            ->where('catalogue_products.catalogue_id',$catalogue_id)
            ->group_by('product_information.product_id')
            ->get();

        return $query->num_rows();
        /*if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return false;*/
    }

    public function product_by_store($store_id,$product_id)
    {

        $query=$this->db->select('
                    product_information.*,catalogue_products.price as price_store,catalogue_products.onsale_price as onsale_price_store,catalogue_products.onsale as onsale_store,catalogue_products.promo as promo_store')
            ->from('catalogue_products')
            ->join('product_information', 'product_information.product_id = catalogue_products.product_id','left')
            ->join('store_set', 'store_set.catalogue_id = catalogue_products.catalogue_id','left')
            ->where('store_set.store_id',$store_id)
            ->where('product_information.product_id',$product_id)
            ->group_by('product_information.product_id')
            ->get();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return false;
    }

    public function update_onsale_price($catalogue_product_id,$data)
    {
        $this->db->where('catalogue_product_id', $catalogue_product_id);
        $result = $this->db->update('catalogue_products',$data);
        if($result)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function products_recomend($store_id)
    {
        $query=$this->db->select('
                    product_information.*,catalogue_products.price as price_store,catalogue_products.onsale_price as onsale_price_store,catalogue_products.onsale as onsale_store')
            ->from('catalogue_products')
            ->join('product_information', 'product_information.product_id = catalogue_products.product_id','left')
            ->join('store_set', 'store_set.catalogue_id = catalogue_products.catalogue_id','left')
            ->where('store_set.store_id',$store_id)
            ->where('product_information.recomend',2)
            ->order_by('product_information.product_name','asc')
            ->group_by('product_information.product_id')
            ->get();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return false;
    }

    public function products_recomend_home($store_id)
    {
        $query=$this->db->select('
                    product_information.*,catalogue_products.price as price_store,catalogue_products.onsale_price as onsale_price_store,catalogue_products.onsale as onsale_store')
            ->from('catalogue_products')
            ->join('product_information', 'product_information.product_id = catalogue_products.product_id','left')
            ->join('store_set', 'store_set.catalogue_id = catalogue_products.catalogue_id','left')
            ->where('store_set.store_id',$store_id)
            ->where('product_information.recomend',2)
            ->limit(12,1)
            ->order_by('product_information.product_name','asc')
            ->group_by('product_information.product_id')
            ->get();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return false;
    }

    public function products_new($store_id)
    {
        $query=$this->db->select('
                    product_information.*,catalogue_products.price as price_store,catalogue_products.onsale_price as onsale_price_store,catalogue_products.onsale as onsale_store')
            ->from('catalogue_products')
            ->join('product_information', 'product_information.product_id = catalogue_products.product_id','left')
            ->join('store_set', 'store_set.catalogue_id = catalogue_products.catalogue_id','left')
            ->where('store_set.store_id',$store_id)
            ->where('product_information.date !=',Null)
            ->order_by('product_information.date','desc')
            ->group_by('product_information.product_id')
            ->limit('30')
            ->get();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return false;
    }

    public function products_new_home($store_id)
    {
        $query=$this->db->select('
                    product_information.*,catalogue_products.price as price_store,catalogue_products.onsale_price as onsale_price_store,catalogue_products.onsale as onsale_store')
            ->from('catalogue_products')
            ->join('product_information', 'product_information.product_id = catalogue_products.product_id','left')
            ->join('store_set', 'store_set.catalogue_id = catalogue_products.catalogue_id','left')
            ->where('store_set.store_id',$store_id)
            ->where('product_information.date !=',Null)
            ->order_by('product_information.date','desc')
            ->group_by('product_information.product_id')
            ->limit(10,1)
            ->get();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return false;
    }


    public function products_ofert($store_id)
    {
        $query=$this->db->select('
                    product_information.*,catalogue_products.price as price_store,catalogue_products.onsale_price as onsale_price_store,catalogue_products.onsale as onsale_store')
            ->from('catalogue_products')
            ->join('product_information', 'product_information.product_id = catalogue_products.product_id','left')
            ->join('store_set', 'store_set.catalogue_id = catalogue_products.catalogue_id','left')
            ->where('store_set.store_id',$store_id)
            ->where('catalogue_products.onsale',1)
            ->order_by('product_information.date','desc')
            ->group_by('product_information.product_id')
            ->get();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return false;
    }

    public function products_ofert_home($store_id)
    {
        $query=$this->db->select('
                    product_information.*,catalogue_products.price as price_store,catalogue_products.onsale_price as onsale_price_store,catalogue_products.onsale as onsale_store')
            ->from('catalogue_products')
            ->join('product_information', 'product_information.product_id = catalogue_products.product_id','left')
            ->join('store_set', 'store_set.catalogue_id = catalogue_products.catalogue_id','left')
            ->where('store_set.store_id',$store_id)
            ->where('catalogue_products.onsale',1)
            ->limit(10,1)
            ->order_by('product_information.date','desc')
            ->group_by('product_information.product_id')
            ->get();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return false;
    }

	//unit Search Item
	public function unit_search_item($unit_id)
	{
		$this->db->select('*');
		$this->db->from('unit');
		$this->db->where('unit_id',$unit_id);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result_array();	
		}
		return false;
	}

	//Insert unit
	public function catalogue_entry($data)
	{
        $this->db->select('*');
		$this->db->from('catalogue');
		$this->db->where('catalogue_name',$data['catalogue_name']);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return FALSE;
		}else{
			$this->db->insert('catalogue',$data);
			return TRUE;
		}
	}

    //Insert unit
    public function catalogue_product_entry($data)
    {
        $this->db->select('*');
        $this->db->from('catalogue_products');
        $this->db->where('catalogue_id',$data['catalogue_id']);
        $this->db->where('product_id',$data['product_id']);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return FALSE;
        }else{
            $this->db->insert('catalogue_products',$data);
            return TRUE;
        }
    }

	//Retrieve unit Edit Data
	public function retrieve_catalogue_editdata($catalogue_id)
	{
		$this->db->select('*');
		$this->db->from('catalogue');
		$this->db->where('catalogue_id',$catalogue_id);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->result_array();	
		}
		return false;
	}
	
	//Update Units
	public function update_catalogue($data,$catalogue_id)
	{
		$this->db->where('catalogue_id',$catalogue_id);
		$result = $this->db->update('catalogue',$data);

		if ($result) {
			return true;
		}
		return false;
	}
	// Delete unit Item
	public function delete_catalogue($catalogue_id)
	{
		$this->db->where('catalogue_id',$catalogue_id);
		$this->db->delete('catalogue');
		return true;
	}

    public function catalogue_product_delete($catalogue_product_id)
    {
        $this->db->where('catalogue_product_id',$catalogue_product_id);
        $this->db->delete('catalogue_products');
        return true;
    }
}