<?php get_header(); ?>
	<?php
	global $data;
	if(get_post_meta($post->ID, 'pyre_width', true) == 'half') {
		$portfolio_width = 'half';
	} else {
		$portfolio_width = 'full';
	}
	if($data['portfolio_featured_images'] && $portfolio_width == 'half') {
		$portfolio_width = 'full';
	}
	?>
	<?php
	$class = '';
	$sidebar_check = get_post_meta($post->ID, 'pyre_sidebar', true);
	if(get_post_meta($post->ID, 'pyre_sidebar', true) == 'no' || empty($sidebar_check) || !isset($sidebar_check)) {
		$content_css = 'width:100%';
		$sidebar_css = 'display:none';
	}
	elseif(get_post_meta($post->ID, 'pyre_sidebar_position', true) == 'left') {
		$content_css = 'float:right;';
		$sidebar_css = 'float:left;';
		$class = 'with-sidebar';
	} elseif(get_post_meta($post->ID, 'pyre_sidebar_position', true) == 'right') {
		$content_css = 'float:left;';
		$sidebar_css = 'float:right;';
		$class = 'with-sidebar';
	} elseif(get_post_meta($post->ID, 'pyre_sidebar_position', true) == 'default') {
		if($data['default_sidebar_pos'] == 'Left') {
			$content_css = 'float:right;';
			$sidebar_css = 'float:left;';
		} elseif($data['default_sidebar_pos'] == 'Right') {
			$content_css = 'float:left;';
			$sidebar_css = 'float:right;';
		}
		$class = 'with-sidebar';
	}
	?>
	<div id="content" class="portfolio-<?php echo $portfolio_width; ?> <?php echo $class; ?>" style="<?php echo $content_css; ?>">
		<?php wp_reset_query(); ?>
		<?php $paged = (get_query_var('paged')) ? get_query_var('paged') : 1; ?>
		<?php query_posts($query_string.'&paged='.$paged); ?>
		<?php
		$nav_categories = '';
		if(isset($_GET['portfolioID'])) {
			$portfolioID = array($_GET['portfolioID']);
		} else {
			$portfolioID = '';
		}
		if(isset($_GET['categoryID'])) {
			$categoryID = $_GET['categoryID'];
		} else {
			$categoryID = '';
		}
		$page_categories = get_post_meta($portfolioID, 'pyre_portfolio_category', true);
		if($page_categories && is_array($page_categories) && $page_categories[0] !== '0') {
			$nav_categories = implode(',', $page_categories);
		}
		if($categoryID) {
			$nav_categories = $categoryID;
		}
		?>
		<?php if(!$data['portfolio_pn_nav']): ?>
		<div class="single-navigation clearfix">
			<?php
			if($portfolioID || $categoryID) {
				$previous_post_link = previous_post_link_plus(array('format' => '%link', 'link' => __('Previous', 'Avada'), 'in_same_tax' => 'portfolio_category', 'in_cats' => $nav_categories, 'return' => 'href'));
			} else {
				$previous_post_link = previous_post_link_plus(array('format' => '%link', 'link' => __('Previous', 'Avada'), 'return' => 'href'));
			}
			?>
			<?php if($previous_post_link):
			if($portfolioID || $categoryID) {
				if($portfolioID) {
					$previous_post_link = tf_addUrlParameter($previous_post_link, 'portfolioID', $portfolioID);
				} else {
					$previous_post_link = tf_addUrlParameter($previous_post_link, 'categoryID', $categoryID);
				}
			}
			?>
			<a href="<?php echo $previous_post_link; ?>" rel="prev"><?php _e('Previous', 'Avada'); ?></a>
			<?php endif; ?>
			<?php
			if($portfolioID || $categoryID) {
				$next_post_link = next_post_link_plus(array('format' => '%link', 'link' => __('Next', 'Avada'), 'in_same_tax' => 'portfolio_category', 'in_cats' => $nav_categories, 'return' => 'href'));
			} else {
				$next_post_link = next_post_link_plus(array('format' => '%link', 'link' => __('Next', 'Avada'), 'return' => 'href'));
			}
			?>
			<?php if($next_post_link):
			if($portfolioID || $categoryID) {
				if($portfolioID) {
					$next_post_link = tf_addUrlParameter($next_post_link, 'portfolioID', $portfolioID);
				} else {
					$next_post_link = tf_addUrlParameter($next_post_link, 'categoryID', $categoryID);
				}
			}
			?>
			<a href="<?php echo $next_post_link; ?>" rel="next"><?php _e('Next', 'Avada'); ?></a>
			<?php endif; ?>
		</div>
		<?php endif; ?>
		<?php if(have_posts()): the_post(); ?>
		<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<?php
			if(!$data['portfolio_featured_images']):
			if($data['legacy_posts_slideshow']):
			$args = array(
			    'post_type' => 'attachment',
			    'numberposts' => $data['posts_slideshow_number']-1,
			    'post_status' => null,
			    'post_parent' => $post->ID,
				'orderby' => 'menu_order',
				'order' => 'ASC',
				'post_mime_type' => 'image',
				'exclude' => get_post_thumbnail_id()
			);
			$attachments = get_posts($args);
			if((has_post_thumbnail() || get_post_meta($post->ID, 'pyre_video', true))):
			?>
			<div class="flexslider post-slideshow">
				<ul class="slides">
					<?php if(!$data['posts_slideshow'] || (get_post_meta($post->ID, 'pyre_video', true) && get_post_meta($post->ID, 'pyre_show_first_featured_image', true) == 'no')): ?>
					<?php if(get_post_meta($post->ID, 'pyre_video', true)): ?>
					<li>
						<div class="full-video">
							<?php echo get_post_meta($post->ID, 'pyre_video', true); ?>
						</div>
					</li>
					<?php elseif(has_post_thumbnail() ): ?>
					<?php $attachment_image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full'); ?>
					<?php $full_image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full'); ?>
					<?php $attachment_data = wp_get_attachment_metadata(get_post_thumbnail_id()); ?>
					<li>
						<a href="<?php echo $full_image[0]; ?>" rel="prettyPhoto[gallery<?php the_ID(); ?>]" title="<?php echo get_post_field('post_excerpt', get_post_thumbnail_id()); ?>"><img src="<?php echo $attachment_image[0]; ?>" alt="<?php echo get_post_meta(get_post_thumbnail_id(), '_wp_attachment_image_alt', true); ?>" /></a>
					</li>
					<?php endif; ?>
					<?php else: ?>
					<?php if(get_post_meta($post->ID, 'pyre_video', true)): ?>
					<li>
						<div class="full-video">
							<?php echo get_post_meta($post->ID, 'pyre_video', true); ?>
						</div>
					</li>
					<?php endif; ?>
					<?php if(has_post_thumbnail() && !get_post_meta($post->ID, 'pyre_video', true)): ?>
					<?php $attachment_image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full'); ?>
					<?php $full_image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full'); ?>
					<?php $attachment_data = wp_get_attachment_metadata(get_post_thumbnail_id()); ?>
					<li>
						<a href="<?php echo $full_image[0]; ?>" rel="prettyPhoto[gallery<?php the_ID(); ?>]" title="<?php echo get_post_field('post_excerpt', get_post_thumbnail_id()); ?>"><img src="<?php echo $attachment_image[0]; ?>" alt="<?php echo get_post_meta(get_post_thumbnail_id(), '_wp_attachment_image_alt', true); ?>" /></a>
					</li>
					<?php endif; ?>
					<?php foreach($attachments as $attachment): ?>
					<?php $attachment_image = wp_get_attachment_image_src($attachment->ID, 'full'); ?>
					<?php $full_image = wp_get_attachment_image_src($attachment->ID, 'full'); ?>
					<?php $attachment_data = wp_get_attachment_metadata($attachment->ID); ?>
					<li>
						<a href="<?php echo $full_image[0]; ?>" rel="prettyPhoto[gallery<?php the_ID(); ?>]" title="<?php echo get_post_field('post_excerpt', $attachment->ID); ?>"><img src="<?php echo $attachment_image[0]; ?>" alt="<?php echo get_post_meta($attachment->ID, '_wp_attachment_image_alt', true); ?>" /></a>
					</li>
					<?php endforeach; ?>
					<?php endif; ?>
				</ul>
			</div>
			<?php endif; ?>
			<?php else: ?>
			<?php
			if((has_post_thumbnail() || get_post_meta($post->ID, 'pyre_video', true))):
			?>
			<div class="flexslider post-slideshow">
				<ul class="slides">
					<?php if(!$data['posts_slideshow'] || (get_post_meta($post->ID, 'pyre_video', true) && get_post_meta($post->ID, 'pyre_show_first_featured_image', true) == 'no')): ?>
					<?php if(get_post_meta($post->ID, 'pyre_video', true)): ?>
					<li>
						<div class="full-video">
							<?php echo get_post_meta($post->ID, 'pyre_video', true); ?>
						</div>
					</li>
					<?php elseif(has_post_thumbnail() ): ?>
					<?php $attachment_image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full'); ?>
					<?php $full_image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full'); ?>
					<?php $attachment_data = wp_get_attachment_metadata(get_post_thumbnail_id()); ?>
					<li>
						<a href="<?php echo $full_image[0]; ?>" rel="prettyPhoto[gallery<?php the_ID(); ?>]" title="<?php echo get_post_field('post_excerpt', get_post_thumbnail_id()); ?>"><img src="<?php echo $attachment_image[0]; ?>" alt="<?php echo get_post_meta(get_post_thumbnail_id(), '_wp_attachment_image_alt', true); ?>" /></a>
					</li>
					<?php endif; ?>
					<?php else: ?>
					<?php if(get_post_meta($post->ID, 'pyre_video', true)): ?>
					<li>
						<div class="full-video">
							<?php echo get_post_meta($post->ID, 'pyre_video', true); ?>
						</div>
					</li>
					<?php endif; ?>
					<?php if(has_post_thumbnail() ): ?>
					<?php $attachment_image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full'); ?>
					<?php $full_image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full'); ?>
					<?php $attachment_data = wp_get_attachment_metadata(get_post_thumbnail_id()); ?>
					<li>
						<a href="<?php echo $full_image[0]; ?>" rel="prettyPhoto[gallery<?php the_ID(); ?>]" title="<?php echo get_post_field('post_excerpt', get_post_thumbnail_id()); ?>"><img src="<?php echo $attachment_image[0]; ?>" alt="<?php echo get_post_meta(get_post_thumbnail_id(), '_wp_attachment_image_alt', true); ?>" /></a>
					</li>
					<?php endif; ?>
					<?php
					$i = 2;
					while($i <= $data['posts_slideshow_number']):
					$attachment_new_id = kd_mfi_get_featured_image_id('featured-image-'.$i, 'avada_portfolio');
					if($attachment_new_id):
					?>
					<?php $attachment_image = wp_get_attachment_image_src($attachment_new_id, 'full'); ?>
					<?php $full_image = wp_get_attachment_image_src($attachment_new_id, 'full'); ?>
					<?php $attachment_data = wp_get_attachment_metadata($attachment_new_id); ?>
					<li>
						<a href="<?php echo $full_image[0]; ?>" rel="prettyPhoto[gallery<?php the_ID(); ?>]" title="<?php echo get_post_field('post_excerpt', $attachment_new_id); ?>"><img src="<?php echo $attachment_image[0]; ?>" alt="<?php echo get_post_meta($attachment_new_id, '_wp_attachment_image_alt', true); ?>" /></a>
					</li>
					<?php endif; $i++; endwhile; ?>
					<?php endif; ?>
				</ul>
			</div>
			<?php endif; ?>
			<?php endif; ?>
			<?php endif; // portfolio single image theme option check ?>
			<?php
			$project_info_style = '';
			$project_desc_style = '';
			$project_desc_title_style = '';
			if(get_post_meta($post->ID, 'pyre_project_details', true) == 'no') {
				$project_info_style = 'display:none;';
			}
			if($portfolio_width == 'full' && get_post_meta($post->ID, 'pyre_project_details', true) == 'no') {
				$project_desc_style = 'width:100%;';
			}
			if(get_post_meta($post->ID, 'pyre_project_desc_title', true) == 'no') {
				$project_desc_title_style = 'display:none;';
			}
			?>
			<div class="project-content">
				<div class="project-description post-content" style="<?php echo $project_desc_style; ?>">
					<h3 style="<?php echo $project_desc_title_style; ?>"><?php echo __('Project Description', 'Avada') ?></h3>
					<?php the_content(); ?>
				</div>
				<div class="project-info" style="<?php echo $project_info_style; ?>">
					<?php if(get_the_term_list($post->ID, 'portfolio_skills', '', '<br />', '')): ?>
					<h3><?php echo __('Project Details', 'Avada'); ?></h3>
					<div class="project-info-box">
						<h4><?php echo __('Skills Needed', 'Avada') ?>:</h4>
						<div class="project-terms">
							<?php echo get_the_term_list($post->ID, 'portfolio_skills', '', '<br />', ''); ?>
						</div>
					</div>
					<?php endif; ?>
					<?php if(get_the_term_list($post->ID, 'portfolio_category', '', '<br />', '')): ?>
					<div class="project-info-box">
						<h4><?php echo __('Categories', 'Avada') ?>:</h4>
						<div class="project-terms">
							<?php echo get_the_term_list($post->ID, 'portfolio_category', '', '<br />', ''); ?>
						</div>
					</div>
					<?php endif; ?>
					<?php if(get_the_term_list($post->ID, 'portfolio_tags', '', '<br />', '')): ?>
					<div class="project-info-box">
						<h4><?php echo __('Tags', 'Avada') ?>:</h4>
						<div class="project-terms">
							<?php echo get_the_term_list($post->ID, 'portfolio_tags', '', '<br />', ''); ?>
						</div>
					</div>
					<?php endif; ?>
					<?php if(get_post_meta($post->ID, 'pyre_project_url', true) && get_post_meta($post->ID, 'pyre_project_url_text', true)): ?>
					<div class="project-info-box">
						<h4><?php echo __('Project URL', 'Avada') ?>:</h4>
						<span><a href="<?php echo get_post_meta($post->ID, 'pyre_project_url', true); ?>"><?php echo get_post_meta($post->ID, 'pyre_project_url_text', true); ?></a></span>
					</div>
					<?php endif; ?>
					<?php if(get_post_meta($post->ID, 'pyre_copy_url', true) && get_post_meta($post->ID, 'pyre_copy_url_text', true)): ?>
					<div class="project-info-box">
						<h4><?php echo __('Copyright', 'Avada'); ?>:</h4>
						<span><a href="<?php echo get_post_meta($post->ID, 'pyre_copy_url', true); ?>"><?php echo get_post_meta($post->ID, 'pyre_copy_url_text', true); ?></a></span>
					</div>
					<?php endif; ?>
					<?php if($data['portfolio_author']): ?>
					<div class="project-info-box vcard">
						<h4><?php echo __('By', 'Avada'); ?>:</h4><span class="fn"><?php the_author_posts_link(); ?></span>
					</div>
					<?php endif; ?>
				</div>
			</div>
			<div style="clear:both;"></div>
			<?php $projects = get_related_projects($post->ID, $data['number_related_posts']); ?>
			<?php if($projects->have_posts() && get_post_meta($post->ID, 'pyre_related_posts', true) != 'no'): ?>
			<div class="related-posts related-projects">
				<div class="title"><h2><?php echo __('Related Projects', 'Avada'); ?></h2><div class="title-sep-container"><div class="title-sep"></div></div></div>
				<div id="carousel" class="es-carousel-wrapper">
					<div class="es-carousel">
						<ul>
							<?php while($projects->have_posts()): $projects->the_post(); ?>
							<?php if(has_post_thumbnail()): ?>
							<li>
								<div class="image" aria-haspopup="true">
										<?php if($data['image_rollover']): ?>
										<?php the_post_thumbnail('related-img'); ?>
										<?php else: ?>
										<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_post_thumbnail('related-img'); ?></a>
										<?php endif; ?>
										<?php
										if(get_post_meta($post->ID, 'pyre_image_rollover_icons', true) == 'link') {
											$link_icon_css = 'display:inline-block;';
											$zoom_icon_css = 'display:none;';
										} elseif(get_post_meta($post->ID, 'pyre_image_rollover_icons', true) == 'zoom') {
											$link_icon_css = 'display:none;';
											$zoom_icon_css = 'display:inline-block;';
										} elseif(get_post_meta($post->ID, 'pyre_image_rollover_icons', true) == 'no') {
											$link_icon_css = 'display:none;';
											$zoom_icon_css = 'display:none;';
										} else {
											$link_icon_css = 'display:inline-block;';
											$zoom_icon_css = 'display:inline-block;';
										}

										$icon_url_check = get_post_meta(get_the_ID(), 'pyre_link_icon_url', true); if(!empty($icon_url_check)) {
											$icon_permalink = get_post_meta($post->ID, 'pyre_link_icon_url', true);
										} else {
											$icon_permalink = get_permalink($post->ID);
										}
										?>
										<div class="image-extras">
											<div class="image-extras-content">
							<?php $full_image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full'); ?>
							<a style="<?php echo $link_icon_css; ?>" class="icon link-icon" href="<?php echo $icon_permalink; ?>">Permalink</a>
							<?php
							if(get_post_meta($post->ID, 'pyre_video_url', true)) {
								$full_image[0] = get_post_meta($post->ID, 'pyre_video_url', true);
							}
							?>
							<a style="<?php echo $zoom_icon_css; ?>" class="icon gallery-icon" href="<?php echo $full_image[0]; ?>" rel="prettyPhoto[galleryrelated]">Gallery</a>
												<h3><?php the_title(); ?></h3>
											</div>
										</div>
								</div>
							</li>
							<?php endif; endwhile; ?>
						</ul>
					</div>
					<div class="es-nav"><span class="es-nav-prev">Previous</span><span class="es-nav-next">Next</span></div>
				</div>
			</div>
			<?php endif; ?>
			<?php if($data['portfolio_comments']): ?>
				<?php wp_reset_query(); ?>
				<?php comments_template(); ?>
			<?php endif; ?>
		</div>
		<?php endif; ?>
	</div>
	<div id="sidebar" style="<?php echo $sidebar_css; ?>"><?php generated_dynamic_sidebar(); ?></div>
<?php get_footer(); ?>