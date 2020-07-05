<?php
/**
 * @package nasatheme
 */
global $nasa_opt;
$nasa_parallax = isset($nasa_opt['blog_parallax']) && $nasa_opt['blog_parallax'] ? true : false;
$data= get_post_meta($post->ID, 'data', true);
$luogo= get_post_meta($post->ID, 'luogo', true);
$azienda= get_post_meta($post->ID, 'azienda', true);
$indirizzo= get_post_meta($post->ID, 'indirizzo', true);
$tipo= get_post_meta($post->ID, 'tipo', true);
$partecipanti= get_post_meta($post->ID, 'partecipanti', true);
do_action('nasa_before_single_post');
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	
	<?php if ( in_category('educational') ) { ?>
		<?php if (has_post_thumbnail()) : ?>
        <div class="entry-image margin-bottom-40">
            <?php if ($nasa_parallax) : ?>
                <div class="parallax_img" style="overflow:hidden">
                    <div class="parallax_img_inner" data-velocity="0.15">
                        <?php the_post_thumbnail(); ?>
                        <div class="image-overlay"></div>
                    </div>
                </div>
            <?php else : ?>
                <?php the_post_thumbnail(); ?>
                <div class="image-overlay"></div>
            <?php endif; ?>
        </div>
    	<?php endif; ?>
		<div class="categoriapost categoriaeducational"><?php the_category(', '); ?></div>
		<header class="entry-header text-center">
			<h1 class="entry-title nasa-title-single-post"><?php the_title(); ?></h1>
			<div class="entry-meta">
				<?php elessi_posted_on(); ?>
			</div>
		</header>
	<?php } ?>
	
	<?php if ( in_category('articoli') ) { ?>
		<?php if (has_post_thumbnail()) : ?>
        <div class="entry-image margin-bottom-40">
            <?php if ($nasa_parallax) : ?>
                <div class="parallax_img" style="overflow:hidden">
                    <div class="parallax_img_inner" data-velocity="0.15">
                        <?php the_post_thumbnail(); ?>
                        <div class="image-overlay"></div>
                    </div>
                </div>
            <?php else : ?>
                <?php the_post_thumbnail(); ?>
                <div class="image-overlay"></div>
            <?php endif; ?>
        </div>
    	<?php endif; ?>
		<div class="categoriapost"><?php the_category(', '); ?></div>
		<header class="entry-header text-center">
			<h1 class="entry-title nasa-title-single-post"><?php the_title(); ?></h1>
			<div class="entry-meta">
				<?php elessi_posted_on(); ?>
			</div>
		</header>
	<?php } ?>
	
	<?php if ( in_category('eventi') ) { ?>
		<div class="categoriapost"><?php the_category(', '); ?></div>
		<header class="entry-header text-center">
			<h1 class="entry-title nasa-title-single-post"><?php the_title(); ?></h1>
			<div class="entry-meta">
				<?php elessi_posted_on(); ?>
			</div>
		</header>
		<?php if (has_post_thumbnail()) : ?>
			<div class="entry-image margin-bottom-40">
				<?php if ($nasa_parallax) : ?>
					<div class="parallax_img" style="overflow:hidden">
						<div class="parallax_img_inner" data-velocity="0.15">
							<?php the_post_thumbnail(); ?>
							<div class="image-overlay"></div>
						</div>
					</div>
				<?php else : ?>
					<?php the_post_thumbnail(); ?>
					<div class="image-overlay"></div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	<?php } ?>
    
	
    <div class="entry-content">
		<div class="minidescarticolo">
			<div class="large-6 column">
				<?php if ( in_category('eventi') ) { ?>
					<img class="loghettinoeventi" src="https://winefully.com/wp-content/uploads/2019/11/logo_home.png">
				<?php } ?>
				<div style="height:1px"></div>
			</div>
			<div class="large-6 column">
				<?php if($luogo !== '') { ?> 
					  <p class="locationarticolo">Location: <span class="redarticolo"><?php echo ($luogo); ?></span> </p>
				<?php } ?>
				<?php if($azienda !== '') { ?> 
					  <p class="aziendaarticolo">Azienda: <span class="redarticolo"><?php echo ($azienda); ?></span> </p>
				<?php } ?>
				<?php if($tipo !== '') { ?> 
					  <p class="tipoarticolo">Tipo: <?php echo ($tipo); ?></p>
				<?php } ?>
				<?php if($partecipanti !== '') { ?> 
					  <p class="partecipantiarticolo">Partecipanti: <?php echo ($partecipanti); ?></p>
				<?php } ?>
				<?php if($data !== '') { ?> 
					  <p class="dataarticolo">Data: <?php echo ($data); ?></p>
				<?php } ?>
				<?php if($indirizzo !== '') { ?> 
					  <p class="indirizzoarticolo">Indirizzo: <?php echo ($indirizzo); ?></p>
				<?php } ?>
			</div>
		</div>
        <?php
        the_content();
        wp_link_pages(array(
            'before' => '<div class="page-links">' . esc_html__('Pages:', 'elessi-theme'),
            'after' => '</div>',
        ));
        ?>
    </div>
	
	
	<?php if ( in_category('eventi') ) { ?>
		<div class="viewall">
			<a href="https://winefully.com/eventi">VIEW ALL EVENTS</a>
		</div>
	<?php } ?>
	<?php if ( in_category('articoli') ) { ?>
		<div class="viewall">
			<a href="https://winefully.com/articoli">VIEW ALL ARTICLES</a>
		</div>
	<?php } ?>
	

    <footer class="entry-meta footer-entry-meta">
        <?php
        $category_list = get_the_category_list(esc_html__(', ', 'elessi-theme'));
        $tag_list = get_the_tag_list('', esc_html__(', ', 'elessi-theme'));
        $allowed_html = array(
            'a' => array('href' => array(), 'rel' => array(), 'title' => array())
        );

        if ('' != $tag_list) :
            $meta_text = esc_html__('Posted in %1$s and tagged %2$s.', 'elessi-theme');
        else :
            $meta_text = wp_kses(__('Posted in %1$s. Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.', 'elessi-theme'), $allowed_html);
        endif;

        printf($meta_text, $category_list, $tag_list, get_permalink(), the_title_attribute('echo=0'));
        ?>
    </footer>

</article>

<div class="nasa-post-navigation">
    <?php
    the_post_navigation(array(
        'prev_text' => '<span class="screen-reader-text">' . esc_html__('Previous Post', 'elessi-theme') . '</span><span aria-hidden="true" class="nav-subtitle">' . esc_html__('Previous', 'elessi-theme') . '</span>',
        'next_text' => '<span class="screen-reader-text">' . esc_html__('Next Post', 'elessi-theme') . '</span><span aria-hidden="true" class="nav-subtitle">' . esc_html__('Next', 'elessi-theme') . '</span>',
    ));
    ?>
</div>

<?php
if (comments_open() || '0' != get_comments_number()):
    comments_template();
endif;

do_action('nasa_after_single_post');
