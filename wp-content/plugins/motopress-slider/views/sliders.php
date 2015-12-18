<?php if (!defined('ABSPATH')) exit; ?>
<div class="mpsl-sliders-list-wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <h3><?php _e('Sliders: ', MPSL_TEXTDOMAIN);?></h3>
                <?php if (!empty($sliders)) { ?>
                    <table class="widefat mpsl-sliders-table">
                        <thead>
                            <tr>
                                <th><?php _e('ID', MPSL_TEXTDOMAIN); ?></th>
                                <th><?php _e('Name', MPSL_TEXTDOMAIN); ?></th>
                                <th><?php _e('Shortcode', MPSL_TEXTDOMAIN); ?></th>
                                <th><?php _e('Actions', MPSL_TEXTDOMAIN); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($sliders as $slider) {
                                include 'slider_row.php';
                            }?>
                        </tbody>
                    </table>
                <?php }?>                
                <div class="mpsl_controls">
                    <a class="button-primary" href="<?php echo $this->getSliderCreateUrl(); ?>"><?php _e('Create New Slider', MPSL_TEXTDOMAIN); ?></a>
                    <button class="button-secondary" id="import-export-btn"><?php _e('Import & Export', MPSL_TEXTDOMAIN); ?></button>
                </div>
                <?php include 'import-export-dialog.php'; ?>
            </div>
        </div>
    </div>
</div>
