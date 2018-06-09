<?php get_header();
pageBanner(array(
  'title' => 'Welcome To Our Gallery',
  'subtitle' => 'Discover our photo albums below'
));
?>

<section class="album-section">
  <!-- <div class="container container--album"> -->
   <?php if ( function_exists( 'envira_album' ) ) { envira_album( '54' ); } ?>
  <!-- </div> -->
</section>

<?php get_footer(); ?>
