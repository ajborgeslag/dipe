<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<?php echo form_open('home/update_cart'); ?>
<!-- ========================= SECTION CARRITO ========================= -->
<section class="section-content section-carrito padding-y-sm">
    <div class="container p-sm-0">
        <div class="alert alert-danger" role="alert">
            <?php echo $this->session->flashdata('message'); ?>
        </div>
        <div class="card p-2">
            <div class="justify-content-center">
                <div class="sw-main sw-theme-dots wizard_form_style">
                    <ul class="p-3 step-anchor">
                        <li><a href="<?php echo base_url('home')?>"><?php echo 'Seguir comprando';?></a></li>
                        <li><a href="<?php echo base_url('view_cart')?>"><?php echo 'Ir al carrito';?></a></li>
                        <li class="active"><a href="<?php echo base_url('website/home/try_pay_egain')?>""><?php echo 'Intentar nuevamente';?></a></li>
                    </ul>
                </div>
            </div>
        </div> <!-- card.// -->
    </div> <!-- container .//  -->
</section>
<?php echo form_close()?>
<!-- ========================= SECTION POPULAR END// ========================= -->

