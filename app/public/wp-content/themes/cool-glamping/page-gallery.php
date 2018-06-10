<?php get_header();
pageBanner(array(
  'title' => 'Welcome To Our Gallery',
  'subtitle' => 'Discover our photo albums below'
));
?>

<section class="album-section">
   <?php if ( function_exists( 'envira_album' ) ) { envira_album( '54' ); } ?>
</section>

<?php get_footer(); ?>
