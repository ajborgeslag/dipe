<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <title><?php echo (isset($title)) ? $title :"Online & Offline Inventory System" ?></title>
        <?php
            $CI =& get_instance();
            $CI->load->model('Soft_settings');
            $Soft_settings = $CI->Soft_settings->retrieve_setting_editdata();
        ?>
        <!-- Favicon and touch icons -->
        <link rel="shortcut icon" href="<?php if (isset($Soft_settings[0]['logo'])) {
               echo $Soft_settings[0]['favicon']; }?>" type="image/x-icon">
        <link rel="apple-touch-icon" type="image/x-icon" href="<?php echo base_url()?>assets/dist/img/ico/apple-touch-icon-57-precomposed.png">
        <link rel="apple-touch-icon" type="image/x-icon" sizes="72x72" href="<?php echo base_url()?>assets/dist/img/ico/apple-touch-icon-72-precomposed.png">
        <link rel="apple-touch-icon" type="image/x-icon" sizes="114x114" href="<?php echo base_url()?>assets/dist/img/ico/apple-touch-icon-114-precomposed.png">
        <link rel="apple-touch-icon" type="image/x-icon" sizes="144x144" href="<?php echo base_url()?>assets/dist/img/ico/apple-touch-icon-144-precomposed.png">
        <!-- Start Global Mandatory Style-->

        <!-- jquery-ui css -->
        <link href="<?php echo base_url()?>assets/plugins/jquery-ui-1.12.1/jquery-ui.min.css" rel="stylesheet" type="text/css"/>
        <!-- Bootstrap -->
        <link href="<?php echo base_url()?>assets/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
        <!-- Validator css -->
        <link href="<?php echo base_url()?>assets/css/cmxform.css" rel="stylesheet" type="text/css"/>
        <!-- iCheck -->
        <link href="<?php echo base_url()?>assets/plugins/icheck/skins/all.css" rel="stylesheet" type="text/css"/>
        <?php
            if ($Soft_settings[0]['rtr'] == 1) {
        ?>
        <!-- Bootstrap rtl -->
        <link href="<?php echo base_url()?>assets/bootstrap-rtl/bootstrap-rtl.min.css" rel="stylesheet" type="text/css"/>
        <?php
            }
        ?>
        <!-- Font Awesome -->
        <link href="<?php echo base_url()?>assets/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
        <!-- Themify icons -->
        <link href="<?php echo base_url()?>assets/themify-icons/themify-icons.min.css" rel="stylesheet" type="text/css"/>
        <!-- Pe-icon -->
        <link href="<?php echo base_url()?>assets/pe-icon-7-stroke/css/pe-icon-7-stroke.css" rel="stylesheet" type="text/css"/>
        <!-- Data Tables -->
        <link href="<?php echo base_url()?>assets/plugins/datatables/dataTables.min.css" rel="stylesheet" type="text/css"/>
        <!-- Theme style -->
        <link href="<?php echo base_url()?>assets/dist/css/styleBD.min.css" rel="stylesheet" type="text/css"/>
        <!-- modals css -->
        <link href="<?php echo base_url()?>assets/plugins/modals/component.css" rel="stylesheet" type="text/css"/>
        <!-- summernote css -->
        <link href="<?php echo base_url()?>assets/plugins/summernote/summernote.css" rel="stylesheet" type="text/css"/>
        <!-- Select2 min.css -->
        <link href="<?php echo base_url()?>assets/css/select2.min.css" rel="stylesheet" type="text/css"/> 
        <!-- Input tag css -->
        <link href="<?php echo base_url()?>assets/css/bootstrap-tagsinput.css" rel="stylesheet" type="text/css"/>
        <!-- Toastr -->
        <link href="<?php echo base_url()?>assets/plugins/toastr/toastr.css" rel="stylesheet" type="text/css"/>
        <?php
            if ($Soft_settings[0]['rtr'] == 1) {
        ?>
        <!-- Theme style rtl -->
        <link href="<?php echo base_url()?>assets/dist/css/styleBD-rtl.css" rel="stylesheet" type="text/css"/>
        <?php
            }
        ?>
        <!-- Custom css -->
        <link href="<?php echo base_url()?>assets/dist/css/custom.css" rel="stylesheet" type="text/css"/>
        <!-- jQuery -->
        <script src="<?php echo base_url()?>assets/plugins/jQuery/jquery-1.12.4.min.js" type="text/javascript"></script>
        <script src="<?php echo base_url()?>assets/js/jquery.validate.min.js" type="text/javascript"></script>

        <!-- Format javascript date-time -->
        <script type="text/javascript">
            $(function(){
                $( ".datepicker" ).datepicker({ dateFormat: 'mm-dd-yy' });
                $( ".coupon_date" ).datepicker({ dateFormat: 'dd-mm-yy' });
            });
        </script>

    </head>
    <body class="hold-transition sidebar-mini <?php if (isset($sidebar_collapse)) { echo $sidebar_collapse;}?>">
        <div class="se-pre-con"></div>
        
        <!-- Site wrapper -->
        <div class="wrapper">
            <?php 
            $url=$this->uri->segment(1); 
            if ($url != "admin"){
                $this->load->view('include/admin_header');
            }?>
                {content}
            <?php
            if ($url != "admin") {
                $this->load->view('include/admin_footer');
            }?>
        </div>
        <!-- ./wrapper -->

        <!-- Start Core Plugins-->
        <!-- jquery-ui --> 
        <script src="<?php echo base_url()?>assets/plugins/jquery-ui-1.12.1/jquery-ui.min.js" type="text/javascript"></script>
        <!-- Bootstrap -->
        <script src="<?php echo base_url()?>assets/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
        <!-- SlimScroll -->
        <script src="<?php echo base_url()?>assets/plugins/slimScroll/jquery.slimscroll.min.js" type="text/javascript"></script>
        <!-- FastClick -->
        <script src="<?php echo base_url()?>assets/plugins/fastclick/fastclick.min.js" type="text/javascript"></script>
        <!-- AdminBD frame -->
        <script src="<?php echo base_url()?>assets/dist/js/frame.min.js" type="text/javascript"></script>
        <!-- Sparkline js -->
        <script src="<?php echo base_url()?>assets/plugins/sparkline/sparkline.min.js" type="text/javascript"></script>
        <!-- Counter js -->
        <script src="<?php echo base_url()?>assets/plugins/counterup/waypoints.min.js" type="text/javascript"></script>
        <script src="<?php echo base_url()?>assets/plugins/counterup/jquery.counterup.min.js" type="text/javascript"></script>
        <!-- iCheck js -->
        <script src="<?php echo base_url()?>assets/plugins/icheck/icheck.min.js" type="text/javascript"></script>
        <!-- DataTables js -->
        <script src="<?php echo base_url()?>assets/plugins/datatables/dataTables.min.js" type="text/javascript"></script>
        <script src="<?php echo base_url()?>assets/plugins/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js" type="text/javascript"></script>
        <!-- Dashboard js -->
        <script src="<?php echo base_url()?>assets/dist/js/dashboard.min.js" type="text/javascript"></script>
        <script src="<?php echo base_url()?>assets/js/select2.min.js" type="text/javascript"></script>
        <!-- Modal js -->
        <script src="<?php echo base_url()?>assets/plugins/modals/classie.js" type="text/javascript"></script>
        <!-- Summernote js -->
        <script src="<?php echo base_url()?>assets/plugins/summernote/summernote.min.js" type="text/javascript"></script>
        <script src="<?php echo base_url()?>assets/plugins/modals/modalEffects.js" type="text/javascript"></script>  
        <!-- Bootstrap tag inputs js -->
        <script src="<?php echo base_url()?>assets/js/bootstrap-tagsinput.js" type="text/javascript"></script>
        <!-- Toastr js -->
        <script src="<?php echo base_url()?>assets/plugins/toastr/toastr.min.js" type="text/javascript"></script>
        <!-- Custom js -->
        <script src="<?php echo base_url()?>my-assets/js/admin_js/custom.js" type="text/javascript"></script>

        <script type="text/javascript">
            $(document).ready(function () {
                
                "use strict"; // Start of use strict

                $('.skin-square .i-check input').iCheck({
                    checkboxClass: 'icheckbox_square-green',
                    radioClass: 'iradio_square-green'
                });

                /*$("#dataTableCatalogue").DataTable( {
                        dom:"<'row'<'col-sm-4 text-center'><'col-sm-4 text-center'B><'col-sm-4'f>>tp", lengthMenu:[[10, 25, 50, -1], [10, 25, 50, "All"]],
                        serverSide:true,
                        processing:true,
                        ajax:"/Ccatalogue/catalogue_product",

                        buttons:[
                            {
                                extend: "excel", title: "ExampleFile", className: "btn-sm"
                            }
                            , {
                                extend: "pdf", title: "ExampleFile", className: "btn-sm"
                            }
                            , {
                                extend: "print", className: "btn-sm"
                            }
                        ],
                    }
                );*/

            });
        </script>
    </body>
</html>