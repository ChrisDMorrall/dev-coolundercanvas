<?php get_header();
pageBanner(array(
  'title' => 'Welcome To Our Blog',
  'subtitle' => 'Keep up with our latest news'
));
?>

<section class="blog-section">
  <div class="container container__flex container--flex-last">
    <?php
      while(have_posts()) {
        the_post(); ?>
        <div class="flip-container flip-container--tall" ontouchstart="this.classList.toggle('hover');">
          <div class="flipper">
            <div class="front front--tall">
              <div class="flip-container__inner flip-container__inner-front flip-container__inner-front--tall t-center">
                <img class="flip-container__img" src="<?php
                if (has_post_thumbnail()) {
                  the_post_thumbnail_url('medium');
                } else {
                  //TODO: Create default post feature image for post cards
                }

                 ?>" alt="Thumbnail Image">
                <h2><?php the_title(); ?></h2>
                <p>Posted by <?php the_author_posts_link(); ?> on <?php the_time('F j, Y'); ?></p>
                <i class="fab fa-readme flip-container__icon-large t-center generic-content--icon-fix-bottom" aria-hidden="true"></i>
              </div>
            </div>
            <div class="back back--tall">
              <div class="flip-container__inner flip-container__inner-back flip-container__inner-back--tall">
                <p>Posted by <?php the_author_posts_link(); ?> on <?php the_time('F j, Y'); ?></p>
                <p><?php the_excerpt(); ?></p>
                <p class="t-center no-margin"><a href="<?php the_permalink(); ?>" class="btn btn--dark-green btn--fix-bottom">Read more</a></p>
              </div>
            </div>
          </div>
        </div>

      <?php }
     ?>
  </div>
</section>

<?php get_footer(); ?>
