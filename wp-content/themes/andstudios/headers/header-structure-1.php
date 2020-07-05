<div class="<?php echo esc_attr($header_classes); ?>">
    <?php //!-- Top bar --
    elessi_header_topbar();
    //!-- End Top bar --
    
    //!-- Masthead --?>
    <div class="sticky-wrapper">
        <header id="masthead" class="site-header">
            <div class="row">
                <div class="large-12 columns header-container">
                        <div style="padding: 0" class="large-12 columns nasa-wrap-event-search">
                            <div class="nasa-relative nasa-elements-wrap nasa-wrap-width-main-menu">
								<div class="nasa-transition nasa-left-main-header nasa-float-left">
                                  
									<a href="#" class="burger-menu vertical-align">MENU</a>
									
										<nav class="menumobileinattivo" id="mySidenav">
										 <div class="table-align">
											  <div class="menulinguawinefully nav-list">
												  <a href="#"><strong>ITA / ENG</strong></a>
										   </div> 
										   <div class="menuwinefully nav-list">
												<a href="https://winefully.com"><strong>Homepage</strong></a>
												<a href="https://winefully.com/about"><strong>Chi Siamo</strong></a>
												<a href="https://winefully.com/shop"><strong>Shop</strong></a>
												<a href="https://winefully.com/club"><strong>Club</strong></a>
											    <a href="https://www.winefully.com/articoli/"><strong>Magazine</strong></a>
												<a href="https://winefully.com/contacts"><strong>Contatti</strong></a>
										   </div> 
										   <div class="menu2winefully nav-list">
											   	<?php echo elessi_tiny_account(true); ?>
												<a href="https://winefully.com/contacts/#newsletter"><strong>Wineletter</strong></a>
										   </div> 
										 </div>
									   </nav>
                                </div>
								
								<div class="nasa-transition nasa-center-main-header nasa-float-left">
                                    <!-- Logo -->
                                    <div class="logo-wrapper nasa-float-left">
                                        <h1 class="logonero nasa-logo-img"><a class="logo nasa-logo-retina" href="https://winefully.com/" rel="Home"><img src="https://winefully.com/wp-content/uploads/2019/11/logo-nero.png" class="header_logo nasa-inited" alt="Winefully" data-src-retina="https://winefully.com/wp-content/uploads/2019/11/logo-nero.png"></a></h1>
										<h1 class="logobianco nasa-logo-img"><a class="logo nasa-logo-retina" href="https://winefully.com/" rel="Home"><img src="https://winefully.com/wp-content/uploads/2019/10/logo-bianco.png" class="header_logo nasa-inited" alt="Winefully" data-src-retina="https://winefully.com/wp-content/uploads/2019/10/logo-bianco.png"></a></h1>
                                    </div>

                                </div>
								
                                <div class="nasa-transition nasa-left-main-header nasa-float-right">
								
                                  <?php echo ($nasa_header_icons); ?>

                                </div>
                                
                                <div class="nasa-clear-both"></div>
                            </div>
                            
                            <!-- Search form in header -->
                            <div class="nasa-header-search-wrap nasa-hide-for-mobile">
                                <?php echo elessi_search('icon'); ?>
                            </div>
                        </div>
                </div>
            </div>
            
            <?php if(isset($show_cat_top_filter) && $show_cat_top_filter) : ?>
                <div class="nasa-top-cat-filter-wrap">
                    <?php echo elessi_get_all_categories(false, true); ?>
                    <a href="javascript:void(0);" title="<?php esc_attr_e('Close categories filter', 'elessi-theme'); ?>" class="nasa-close-filter-cat"><i class="pe-7s-close"></i></a>
                </div>
            <?php endif; ?>
        </header>
    </div>
</div>
