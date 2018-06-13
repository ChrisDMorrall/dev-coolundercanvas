<?php get_header();
pageBanner(array(
  'title' => 'We Look Forward To Seeing You',
  'subtitle' => 'From our family to yours'
));
?>

<section class="contact-section">
  <div class="container container--inset-box-shadow">
    <div class="hook-strip t-center">
      <h2 class="headline headline--medium">Get In Touch</h2>
      <p>Please send us a message using the the contact form below or call us for enquiries.</p>
      <hr class="headline__hr-center">
    </div>
    <div class="contact">
      <div class="contact__form-wrap">
        <div class="contact__form-inner">
          <?php echo do_shortcode( '[contact-form-7 id="69" title="CUC Contact Form"]' ); ?>
        </div>
      </div>
      <div class="contact__map-wrap">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1547.159811760459!2d1.5688737976100045!3d43.00258784774084!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x12af17649ac09421%3A0x4eec2492fa13be18!2zNDPCsDAwJzEyLjEiTiAxwrAzNCcxNS44IkU!5e0!3m2!1sen!2sfr!4v1528725592160"></iframe>
      </div>
    </div>


  </div>
</section>

<?php get_footer(); ?>
