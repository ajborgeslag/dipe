<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
<!-- Customer js php -->
<!-- Add new order start -->
<div class="content-wrapper">
    <section class="content-header">
        <div class="header-icon">
            <i class="pe-7s-note2"></i>
        </div>
        <div class="header-title">
            <h1><?php echo display('order') ?></h1>
            <small><?php echo 'Generar Interfaz'; ?></small>
            <ol class="breadcrumb">
                <li><a href="#"><i class="pe-7s-home"></i> <?php echo display('home') ?></a></li>
                <li><a href="#"><?php echo display('order') ?></a></li>
                <li class="active"><?php echo 'Generar Interfaz'; ?></li>
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
                  <a href="<?php echo base_url('Corder/manage_order')?>" class="btn btn-info m-b-5 m-r-2"><i class="ti-align-justify"> </i> <?php echo display('admin_manage_order')?></a>
                  <!--<a href="<?php echo base_url('Cinvoice/pos_invoice')?>" class="btn btn-primary m-b-5 m-r-2"><i class="ti-align-justify"> </i> <?php echo display('pos_invoice')?></a>-->
                </div>
            </div>
        </div>

        <!--Add order -->
        <div class="row">
            <div class="col-sm-12">
                <div class="panel panel-bd lobidrag">
                    <div class="panel-heading">
                        <div class="panel-title">
                            <h4><?php echo 'Generar Interfaz'; ?></h4>
                        </div>
                    </div>
                    <?php echo form_open_multipart('Corder/save_interface',array('class' => 'form-vertical', 'id' => 'validate'))?>
                    <div class="panel-body">
                        <div class="row">
                        <div class="col-sm-8" id="payment_from_2">
                               <div class="form-group row">
                                    <label for="date" class="col-sm-3 col-form-label"><?php echo 'Fecha'; ?> <i class="text-danger">*</i></label>
                                    <div class="col-sm-6">
                                       <input  autofill="off" type="text" name="date" placeholder='<?php echo 'dia-mes-año'; ?>' id="customer_name_others" class="form-control coupon_date" required/>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="store_code" class="col-sm-3 col-form-label"><?php echo 'Tienda' ?> <i class="text-danger">*</i></label>
                                    <div class="col-sm-6">
                                        <select class="form-control" name="store_id" required>
                                        <option value=""><?php echo display('select_options')?></option>
                                        <?php if($stores){ ?>
                                        <?php foreach($stores as $store){ ?>
                                            <option  value="<?php echo $store['store_id']; ?>"><?php echo $store['store_name']; ?></option>
                                        <?php } ?>
                                        <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="example-text-input" class="col-sm-3 col-form-label"></label>
                                    <div class="col-sm-6">
                                        <input type="submit" class="btn btn-primary btn-large" value="<?php echo display('submit') ?>" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php echo form_close()?>
                </div>
            </div>
        </div>
    </section>
</div>
<!-- Add Order End -->
