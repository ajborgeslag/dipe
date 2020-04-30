<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
<!-- ALl Advertisement Start -->
<div class="content-wrapper">
    <section class="content-header">
        <div class="header-icon">
            <i class="pe-7s-note2"></i>
        </div>
        <div class="header-title">
            <h1><?php echo display('manage_advertise')?></h1>
            <small><?php echo display('manage_advertise_information')?></small>
            <ol class="breadcrumb">
                <li><a href="#"><i class="pe-7s-home"></i> <?php echo display('home')?></a></li>
                <li><a href="#"><?php echo display('web_settings')?></a></li>
                <li class="active"><?php echo display('manage_advertise')?></li>
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

        if (validation_errors()) {
            ?>
        <div class="alert alert-danger alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <?php echo validation_errors(); ?>
        </div>
        <?php
            }
        ?>

            
        <div class="row">
            <div class="col-sm-12">
                <div class="column">
                
                  <a href="<?php echo base_url('Cweb_setting/submit_add')?>" class="btn btn-success m-b-5 m-r-2"><i class="ti-plus"> </i> <?php echo display('add_advertise')?></a>

                </div>
            </div>
        </div>
        
        <!-- ALl Advertisement -->
        <div class="row">
            <div class="col-sm-12">
                <div class="panel panel-bd lobidrag">
                    <div class="panel-heading">
                        <div class="panel-title">
                            <h4><?php echo display('manage_advertise')?> </h4>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover" id="dataTableExample2">
                                <thead>
                                    <tr>
                                        <th><?php echo display('sl')?>.</th>
                                        <th><?php echo display('add_page')?></th>
                                        <th><?php echo display('ads_position')?></th>
                                        <th><?php echo display('embed_code')?></th>
                                        <th><?php echo display('action')?></th>
                                    </tr>
                                </thead>

                                <tbody>
                              	    <?php
			                        $i=1;
			                        #------All data from data base---#
			                        if($add_list){
			                        foreach ($add_list as $value){
			                        ?>
						            <tr class="odd gradeX">
			                            <td><?= $i?></td>
										<td><?php echo $value['add_page']; ?></td>
										<td><?= $value['adv_position']?></td>
										<td><?= $value['adv_code']?></td>
                                        <td>
                                            <?php
                                            #----status change start---#
                                            $status=$value['status'];
                                            if ($status==1) {
                                                ?>
                                                <a href="<?= base_url();?>Cweb_setting/inactive_add/<?= $value['adv_id']?>">
                                                    <button class="btn btn-danger" data-toggle="tooltip" data-placement="left" title="" data-original-title="<?php echo display('inactive')?>"><i class="fa fa-times" aria-hidden="true"></i></button>
                                                </a>
                                                <?php
                                            }else{
                                                ?>
                                                <a href="<?= base_url();?>Cweb_setting/active_add/<?= $value['adv_id']?>">
                                                    <button class="btn btn-success" data-toggle="tooltip" data-placement="left" title="" data-original-title="<?php echo display('active')?>"><i class="fa fa-check" aria-hidden="true"></i></button>
                                                </a>
                                                <?php
                                            }
                                             #----status change end---#
                                            ?>

                                            <a href="<?= base_url()?>Cweb_setting/edit_add_form/<?= $value['adv_id']?>">
                                                <button class="btn btn-warning" data-toggle="tooltip" data-placement="left" title="" data-original-title="<?php echo display('update')?>"><i class="fa fa-pencil" aria-hidden="true"></i></button>
                                            </a>
                                     
                                            <a href="<?= base_url()?>Cweb_setting/delete_add/<?= $value['adv_id']?>" onclick="return confirm('<?php echo display('are_you_sure_want_to_delete')?>');">
                                                <button class="btn btn-danger" data-toggle="tooltip" data-placement="right" title="" data-original-title="<?php echo display('delete')?> "><i class="fa fa-trash-o" aria-hidden="true"></i></button>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php
									$i++;
										}
									}
									?>
                                </tbody>
                            </table>  <!-- /.table-responsive -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<!-- ALl Advertisement End -->