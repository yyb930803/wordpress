<div class="<?php echo esc_attr($header_classes); ?>">
    <?php
    //!-- Top bar --
    elessi_header_topbar(true);
    //!-- End Top bar --
    
    //!-- Masthead --?>
    <div class="sticky-wrapper">
        <header id="masthead" class="site-header">
            <div class="row">
                <div class="large-3 medium-3 small-3 columns mini-icon-mobile elements-wrapper rtl-right rtl-text-right">
                    <a href="javascript:void(0);" class="nasa-icon nasa-mobile-menu_toggle mobile_toggle nasa-mobile-menu-icon pe-7s-menu"></a>
                    <a class="nasa-icon icon pe-7s-search mobile-search" href="javascript:void(0);"></a>
                </div>

                <!-- Logo -->
                <div class="large-6 medium-6 small-6 columns logo-wrapper elements-wrapper rtl-right text-center">
                    <?php echo elessi_logo(true); ?>
                </div>

                <div class="large-3 medium-3 small-3 columns elements-wrapper rtl-left rtl-text-left">
                    <?php
                    /**
                     * product_cat: false
                     * cart: true
                     * compare: false
                     * wishlist: true
                     * search: false
                     */
                    echo elessi_header_icons(false, true, false, true, false); ?>
                </div>
            </div>
            
            <div class="hidden-tag">
                <?php
                elessi_get_main_menu();
                if ($vertical) :
                    elessi_get_vertical_menu();
                endif;
                
                echo elessi_get_all_categories(false, true);
                ?>
            </div>
        </header>
    </div>
</div>
