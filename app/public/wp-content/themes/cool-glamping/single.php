<?php

  get_header();

  while(have_posts()) {
    the_post();
    // pageBanner();
     ?>

    <div class="container container--narrow page-section">
      <div class="post-content t-center">
        <h3><?php the_title() ?></h3>
        <p>By <?php the_author_posts_link() ?> on <?php the_time('F j, Y') ?></p>
      </div>

      <div class="post-content__featured-image">
        <img src="<?php
        if (has_post_thumbnail()) {
          the_post_thumbnail_url('large');
        } else {
          //TODO: Create default post feature image for posts
        }

         ?>" alt="Featured Image">
        <?php if (get_post(get_post_thumbnail_id())->post_excerpt) { ?>
          <p class="post-content__featured-image-caption">
          <?php echo wp_kses_post(get_post(get_post_thumbnail_id())->post_excerpt); // displays the image caption ?>
        </p>
        <?php } ?>
      </div>
      <div class="post-content"><?php the_content(); ?></div>
      <?php
      // If comments are open or we have at least one comment, load up the comment template.
        if ( comments_open() || get_comments_number() ) : comments_template();
        endif;
      ?>
    </div>

  <?php }

  get_footer();

?>
