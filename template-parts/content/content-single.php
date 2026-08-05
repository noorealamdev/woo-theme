<?php
/**
 * Content for single blog posts — real featured image, real primary
 * category + author + date, real body content, real tags + share links,
 * real prev/next post navigation, then real comments.
 *
 * @package Noorifa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_template_part( 'template-parts/blog/breadcrumb-nav' );

$settings          = noorifa_settings();
$categories        = get_the_category();
$primary_category  = ! empty( $categories ) ? $categories[0] : null;
$post_tags         = get_the_tags();
$share_url         = rawurlencode( get_permalink() );
$share_title       = rawurlencode( get_the_title() );
$previous_post     = get_previous_post();
$next_post         = get_next_post();
?>
<section class="section-blog-single">
	<div class="main-blog-single">
		<div class="container">
			<div class="row">
				<?php if ( has_post_thumbnail() ) : ?>
					<div class="col-lg-8 mx-auto">
						<div class="blog-image">
							<?php the_post_thumbnail( 'large' ); ?>
						</div>
					</div>
				<?php endif; ?>

				<div class="col-lg-8 mx-auto">
					<article id="post-<?php the_ID(); ?>" <?php post_class( 'blog-content' ); ?>>
						<div class="blog-heading">
							<?php if ( $primary_category ) : ?>
								<a href="<?php echo esc_url( get_category_link( $primary_category ) ); ?>" class="entry-tag fw-medium">
									<?php echo esc_html( $primary_category->name ); ?>
								</a>
							<?php endif; ?>

							<?php // Real <h1> — the post's main heading; .h3 keeps the existing visual size (see main.css). ?>
							<?php the_title( '<h1 class="entry-title h3">', '</h1>' ); ?>

							<div class="entry-meta">
								<div class="meta-item meta-date">
									<?php \Noorifa\Setup\Icons::render( 'CalendarBlank' ); ?>
									<span class="text-body-1"><?php echo esc_html( get_the_date() ); ?></span>
								</div>
							</div>
						</div>

						<div class="entry-content">
							<?php
							the_content();

							wp_link_pages(
								array(
									'before' => '<nav class="page-links">' . esc_html__( 'Pages:', 'noorifa' ),
									'after'  => '</nav>',
								)
							);
							?>
						</div>

						<div class="box-social-tag">
							<?php if ( $post_tags ) : ?>
								<div class="tags-right d-flex align-items-center flex-wrap gap-8">
									<p><?php esc_html_e( 'Tags:', 'noorifa' ); ?></p>
									<?php foreach ( $post_tags as $tag ) : ?>
										<a href="<?php echo esc_url( get_tag_link( $tag ) ); ?>" class="tag-item text-caption-01"><?php echo esc_html( $tag->name ); ?></a>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>

							<?php if ( $settings['blog_share_buttons_enabled'] ) : ?>
								<div class="social-left">
									<p><?php esc_html_e( 'Share this post:', 'noorifa' ); ?></p>
									<ul class="social-icon-2">
										<?php if ( $settings['blog_share_facebook'] ) : ?>
											<li>
												<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo esc_attr( $share_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Share on Facebook', 'noorifa' ); ?>">
													<?php \Noorifa\Setup\Icons::render( 'FacebookLogo' ); ?>
												</a>
											</li>
										<?php endif; ?>
										<?php if ( $settings['blog_share_x'] ) : ?>
											<li>
												<a href="https://x.com/intent/tweet?url=<?php echo esc_attr( $share_url ); ?>&text=<?php echo esc_attr( $share_title ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Share on X', 'noorifa' ); ?>">
													<?php \Noorifa\Setup\Icons::render( 'XLogo' ); ?>
												</a>
											</li>
										<?php endif; ?>
										<?php if ( $settings['blog_share_pinterest'] ) : ?>
											<li>
												<a href="https://www.pinterest.com/pin/create/button/?url=<?php echo esc_attr( $share_url ); ?>&description=<?php echo esc_attr( $share_title ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Share on Pinterest', 'noorifa' ); ?>">
													<?php \Noorifa\Setup\Icons::render( 'ShareNetwork' ); ?>
												</a>
											</li>
										<?php endif; ?>
									</ul>
								</div>
							<?php endif; ?>
						</div>

						<?php if ( $previous_post || $next_post ) : ?>
							<div class="group-direc">
								<?php if ( $previous_post ) : ?>
									<a href="<?php echo esc_url( get_permalink( $previous_post ) ); ?>" class="btn-direc prev link">
										<p class="fw-semibold text-decoration-underline"><?php esc_html_e( 'Previous', 'noorifa' ); ?></p>
										<p class="name-post h6 fw-medium"><?php echo esc_html( get_the_title( $previous_post ) ); ?></p>
									</a>
								<?php else : ?>
									<span></span>
								<?php endif; ?>

								<span class="br-line type-vertical"></span>

								<?php if ( $next_post ) : ?>
									<a href="<?php echo esc_url( get_permalink( $next_post ) ); ?>" class="btn-direc next link">
										<p class="fw-semibold text-decoration-underline"><?php esc_html_e( 'Next', 'noorifa' ); ?></p>
										<p class="name-post h6 fw-medium"><?php echo esc_html( get_the_title( $next_post ) ); ?></p>
									</a>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</article>

					<?php
					if ( comments_open() || get_comments_number() ) {
						comments_template();
					}
					?>
				</div>
			</div>
		</div>
	</div>
</section>

<?php get_template_part( 'template-parts/blog/related-posts' ); ?>
