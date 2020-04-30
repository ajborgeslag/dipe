<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
<!-- Add new customer start -->
<div class="content-wrapper">
    <section class="content-header">
        <div class="header-icon">
            <i class="pe-7s-note2"></i>
        </div>
        <div class="header-title">
            <h1><?php echo display('update_setting') ?></h1>
            <small><?php echo 'Datos de facturación'; ?></small>
            <ol class="breadcrumb">
                <li><a href="#"><i class="pe-7s-home"></i> <?php echo display('home') ?></a></li>
                <li><a href="#"><?php echo display('software_settings') ?></a></li>
                <li class="active"><?php echo 'Datos de facturación'; ?></li>
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

        <!-- New customer -->
        <div class="row">
            <div class="col-sm-12">
                <div class="panel panel-bd lobidrag">
                    <div class="panel-heading">
                        <div class="panel-title">
                            <h4><?php echo 'Datos de facturación'; ?> </h4>
                        </div>
                    </div>
                  <?php echo form_open_multipart('Csoft_setting/update_invoice_setting', array('class' => 'form-vertical','id' => 'validate'))?>
                    <div class="panel-body">
                        <div class="form-group row">
                            <label for="footer_text" class="col-sm-3 col-form-label"><?php echo 'Pac Usuario' ?></label>
                            <div class="col-sm-6">
                                <input name ="id" type="hidden" value="{id}">
                                <input class="form-control" name ="pac_usuario" id="pac_usuario" type="text" placeholder="Pac Usuario" value="{pac_usuario}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="footer_text" class="col-sm-3 col-form-label"><?php echo 'Pac Contraseña' ?></label>
                            <div class="col-sm-6">
                                <input class="form-control" name ="pac_pass" id="pac_pass" type="password" placeholder="Pac Contraseña" value="{pac_pass}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="conf_cert_path" class="col-sm-3 col-form-label"><?php echo 'Conf Cert Path'; ?></label>
                            <div class="col-sm-3">
                                <input class="form-control" name ="conf_cert_path" id="conf_cert_path" type="file" >
                                <input name ="old_conf_cert_path" type="hidden" value="{conf_cert_path}">
                            </div>
                            <div class="col-sm-3">
                                <h5>{conf_cert_path}</h5>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="conf_key_path" class="col-sm-3 col-form-label"><?php echo 'Conf Key Path'; ?></label>
                            <div class="col-sm-3">
                                <input class="form-control" name ="conf_key_path" id="conf_key_path" type="file" >
                                <input name ="old_conf_key_path" type="hidden" value="{conf_key_path}">
                            </div>
                            <div class="col-sm-3">
                                <h5>{conf_key_path}</h5>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="footer_text" class="col-sm-3 col-form-label"><?php echo 'Conf Contraseña' ?></label>
                            <div class="col-sm-6">
                                <input class="form-control" name ="conf_pass" id="conf_pass" type="password" placeholder="Conf Contraseña" value="{conf_pass}">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="footer_text" class="col-sm-3 col-form-label"><?php echo 'Conf Rfc' ?></label>
                            <div class="col-sm-6">
                                <input class="form-control" name ="conf_rfc" id="conf_rfc" type="text" placeholder="Conf Rfc" value="{conf_rfc}">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="example-text-input" class="col-sm-4 col-form-label"></label>
                            <div class="col-sm-6">
                                <input type="submit" id="add-customer" class="btn btn-success btn-large" name="add-customer" value="<?php echo display('save_changes') ?>" />
                            </div>
                        </div>
                    </div>
                    <?php echo form_close()?>
                </div>
            </div>
        </div>
    </section>
</div>
<!-- Add new customer end -->



