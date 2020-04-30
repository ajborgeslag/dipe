<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
<!-- Manage unit Start -->
<div class="content-wrapper">
	<section class="content-header">
	    <div class="header-icon">
	        <i class="pe-7s-note2"></i>
	    </div>
	    <div class="header-title">
	        <h1><?php echo display('catalogue') ?></h1>
	        <small><?php echo "Productos del Catálogo"; ?></small>
	        <ol class="breadcrumb">
	            <li><a href=""><i class="pe-7s-home"></i> <?php echo display('home') ?></a></li>
	            <li><a href="#"><?php echo display('catalogue') ?></a></li>
	            <li class="active"><?php echo "Productos del Catálogo"; ?></li>
	        </ol>
	    </div>
	</section>

	<section class="content">

		<!-- Alert Message -->
	    <?php
	        $message = $this->session->userdata('message');
	        if (isset($message)) {
	    ?>
	    <div class="alert alert-info alert-dismissable">
	        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
	        <?php echo $message ?>                    
	    </div>
	    <?php 
	        $this->session->unset_userdata('message');
	        }
	        $error_message = $this->session->userdata('error_message');
	        if (isset($error_message)) {
	    ?>
	    <div class="alert alert-danger alert-dismissable">
	        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
	        <?php echo $error_message ?>                    
	    </div>
	    <?php 
	        $this->session->unset_userdata('error_message');
	        }
	    ?>


        <div class="row">
            <div class="col-sm-12">
                <div class="column">

                    <a href="<?php echo base_url('Ccatalogue/manage_catalogue')?>" class="btn btn-success m-b-5 m-r-2"><i class="ti-align-justify"> </i> <?php echo display('manage_catalogue')?></a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <form action="<?php echo base_url('Cproduct/product_by_search')?>" class="form-inline" method="post" accept-charset="utf-8">
                            <label class="select"><?php echo display('department')?>:</label>
                            <select class="form-control" name="parent_department" style="width: 200px;">
                                <option value=""><?php echo display('select_options')?></option>
                                <?php if($category_department_list){ ?>
                                {category_department_list}
                                <option value="{category_id}">{category_name}</option>
                                {/category_department_list}
                                <?php } ?>
                            </select>
                            <label class="select"><?php echo display('family')?>:</label>
                            <select class="form-control" name="parent_family" style="width: 200px;">
                            </select>
                            <label class="select"><?php echo display('subfamily')?>:</label>
                            <select class="form-control" name="category_id" id="category_id" style="width: 200px;">
                            </select>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <form action="<?php echo base_url('Cproduct/product_by_search')?>" class="form-inline" method="post" accept-charset="utf-8">
                            <label class="select"><?php echo "Productos"?>:</label>
                            <input type="hidden" id="catalogue_id" value="{catalogue_id}">
                            <input type="text" class="form-control" style="width: 400px;" name="product_name" id="product_name" value="" required="" placeholder="<?php echo display('search_product_name_here')?>">
                            <div class="search_results scrollbar" id="style-1"></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <form action="<?php echo base_url('Ccatalogue/product_catalogue_by_search_clave')?>" class="form-inline" method="post" accept-charset="utf-8">
                            <div class="rows">
                            <div class="col-sm-4">
                            <label class="select"><?php echo 'Clave interna'?>:</label>
                            <input class="form-control" name="clave_interna" type="text" id="clave_interna" placeholder="<?php echo 'Clave interna'; ?>">
                            </div>
                            <div class="col-sm-5">
                            <label class="select"><?php echo 'Nombre del producto'?>:</label>
                            <input class="form-control" name="product_name" type="text" id="product_name" placeholder="<?php echo 'Nombre del producto'; ?>">
                            <input type="hidden" id="catalogue_id" name="catalogue_id" value="{catalogue_id}">
                            </div>
                            <button type="submit" class="btn btn-primary"><?php echo display('submit')?></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>


        <!-- Manage unit -->
		<div class="row">
		    <div class="col-sm-12">
		        <div class="panel panel-bd lobidrag">
		            <div class="panel-heading">
		                <div class="panel-title">
		                    <h4><?php echo "Productos del Catálogo"; ?></h4>
		                </div>
		            </div>
		            <div class="panel-body">
		                <div class="table-responsive">
		                    <table id="dataTableExample3" class="table table-bordered table-striped table-hover">
								<thead>
									<tr>
										<th class="text-center"><?php echo display('sl') ?></th>
                                        <th class="text-center"><?php echo "Clave interna"; ?></th>
										<th class="text-center"><?php echo display('name') ?></th>
										<th class="text-center"><?php echo "Categoría"; ?></th>
                                        <th class="text-center"><?php echo display('action') ?></th>
                                        <th class="text-center"><?php echo "Precio"; ?></th>
                                        <th class="text-center"><?php echo "Rebaja"; ?></th>
                                        <th class="text-center"><?php echo "Precio Rebaja"; ?></th>
                                        <th class="text-center"><?php echo "Promoción"; ?></th>
                                        <th class="text-center"><?php echo display('action') ?></th>
									</tr>
								</thead>
								<tbody>
								<?php
									if ($product_list) {
								    foreach ($product_list as $pro){
								?>
								    <tr>
										<td class="text-center"><?php echo $pro['sl']?></td>
                                        <td class="text-center"><?php echo $pro['clave_interna']?></td>
										<td class="text-center"><?php echo $pro['product_name']?></td>
										<td class="text-center"><?php echo $pro['category_name']?></td>
                                        <td>
											<center>
											<?php echo form_open()?>
                                                <a href="<?php echo base_url().'Ccatalogue/catalogue_delete_product/'.$catalogue_id.'/'.$pro['catalogue_product_id']; ?>" class="btn btn-danger btn-sm"  data-toggle="tooltip" data-placement="right" data-original-title="<?php echo display('delete') ?> "><i class="fa fa-trash-o" aria-hidden="true"></i></a>
											<?php echo form_close()?>
                                            </center>
										</td>
                                        <td class="text-center"><?php echo $pro['price_store']?></td>
                                        <td class="text-center">
                                            <?php
                                            if($pro['onsale_store']=='1')
                                            {
                                                if($pro['price_store']<=$pro['onsale_price_store'])
                                                {
                                                    echo "Error";
                                                }
                                                else
                                                {
                                                    echo "Si";
                                                }
                                            }
                                            else
                                            {
                                                echo "No";
                                            }
                                            ?>
                                        </td>
                                        <td class="text-center"><input class="form-control" name ="<?php echo 'onsale_price_'.$pro['catalogue_product_id']; ?>" id ="<?php echo 'onsale_price_'.$pro['catalogue_product_id']; ?>" value="<?php echo $pro['onsale_price_store']; ?>" type="text"></td>
                                        <td class="text-center"><input class="form-control" name ="<?php echo 'promo_'.$pro['catalogue_product_id']; ?>" id ="<?php echo 'promo_'.$pro['catalogue_product_id']; ?>" value="<?php echo $pro['promo_store']; ?>" type="text"></td>
                                        <td>
                                            <center>
                                                <button class="btn btn-primary btn-sm" onclick="update_onsale_price('<?php echo $catalogue_id;?>','<?php echo $pro['catalogue_product_id']?>','<?php echo $pro['price_store']?>')" data-toggle="tooltip" data-placement="right" data-original-title="<?php echo display('update') ?> "><i class="fa fa-save" aria-hidden="true"></i></button>
                                            </center>
                                        </td>
                                    </tr>
								<?php
                                    }
									}
								?>
								</tbody>
		                    </table>
                            <div class="text-right"><?php echo @$links?></div>
		                </div>
		            </div>
		        </div>
		    </div>
		</div>
	</section>
</div>
<!-- Manage unit End -->
<script type="text/javascript">
    /* Load family by Department */

    $('select[name="parent_department"]').on('change', function() {

    var departmentID = $(this).val();
    if(departmentID) {
    var postData = {
    'departmentID' :departmentID
    };
    $.ajax({
    url: '/Ccategory/get_category_family_by_department',
    type: "POST",
    dataType: "json",
    data: postData , //assign the var here
    success:function(data) {

    $('select[name="parent_family"]').empty();
    $('select[name="category_id"]').empty();
    $('select[name="parent_family"]').append('<option value="">'+ '' +'</option>');
    $.each(data, function(key, value) {

    $('select[name="parent_family"]').append('<option value="'+ value.category_id +'">'+ value.category_name +'</option>');

    });

    }

    });
    }else{
    $('select[name="parent_family"]').empty();
    $('select[name="category_id"]').empty();
    }
    });
    /**/

    /* Load subfamily by family */
    $('select[name="parent_family"]').on('change', function() {

        var familyID = $(this).val();
        if(familyID) {
            var postData = {
                'familyID' :familyID
            };
            $.ajax({
                url: '/Ccategory/get_category_subfamily_by_family',
                type: "POST",
                dataType: "json",
                data: postData , //assign the var here
                success:function(data) {

                    $('select[name="category_id"]').empty();
                    $.each(data, function(key, value) {

                        $('select[name="category_id"]').append('<option value="'+ value.category_id +'">'+ value.category_name +'</option>');

                    });

                }

            });
        }else{
            $('select[name="category_id"]').empty();
        }
    });
    /**/

    /* Load product by subfamily */
    /*$('select[name="category_id"]').on('change', function() {

        var subfamilyID = $(this).val();
        if(subfamilyID) {
            var postData = {
                'subfamilyID' :subfamilyID
            };
            $.ajax({
                url: '/Cproduct/get_product_by_category',
                type: "POST",
                dataType: "json",
                data: postData , //assign the var here
                success:function(data) {

                    $('select[name="product_id"]').empty();
                    $('select[name="product_id"]').append('<option value="">'+ '' +'</option>');
                    $.each(data, function(key, value) {

                        $('select[name="product_id"]').append('<option value="'+ value.product_id +'">'+ value.product_name +'</option>');

                    });

                }

            });
        }else{
            $('select[name="product_id"]').empty();
        }
    });*/
    /**/

    //Product search by product name
    $('body').on('keyup', '#product_name', function() {

        var product_name = $('#product_name').val();
        var category_id  = $('#category_id').val();
        var catalogue_id = $('#catalogue_id').val();

        //Product name check
        if (product_name == 0 || category_id==null) {
            $('.search_results').html(' ');
            return false;
        }

        console.log(category_id);

        $.ajax({
            type: "post",
            async: false,
            url: '<?php echo base_url('website/Category/catalogue_category_product_search_ajax')?>',
            data: {product_name: product_name,category_id:category_id,catalogue_id:catalogue_id},
            success: function(data) {
                $('#product_name').removeClass('loading');
                if (data) {
                    $('.search_results').html(data);
                }
            },
            error: function() {
                alert('Request Failed, Please check your code and try again!');
            }
        });
    });

    function update_onsale_price(catalogue_id,catalogue_product_id,price) {
        var onsale_price = $('#onsale_price_'+catalogue_product_id).val();
        var promo = $('#promo_'+catalogue_product_id).val();
        console.log(price);
        console.log(onsale_price);
        if(onsale_price!='' && parseFloat(onsale_price)>=parseFloat(price))
        {
            alert('El precio de rebaja tiene que ser menor que el precio');
        }
        else if(onsale_price!='' && promo=='')
        {
            alert('Entre el número de promoción');
        }
        else
        {
            $.ajax({
                type: "post",
                async: false,
                url: '<?php echo base_url('Ccatalogue/update_onsale_price')?>',
                data: {catalogue_product_id:catalogue_product_id,onsale_price:onsale_price,promo:promo},
                success: function(data) {
                    location.reload();
                },
                error: function() {
                    alert('Error al modificar el precio de rebaja del producto !');
                }
            });
        }
    }

</script>

