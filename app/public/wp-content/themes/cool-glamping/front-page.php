<?php get_header(); ?>
<!--
Section 1 - Hero Slider Section
***** Code will need to be changed to include new 'slide' post format to add slides in via WP Admin and change Title and Subtilte contents *****
-->
<section class="section-one">
  <div class="hero-slider">
    <div class="hero-slider__slide" style="background-image: url(<?php echo get_theme_file_uri('/img/slider-1.jpg'); ?>)">
      <div class="hero-slider__interior container">
        <div class="hero-slider__overlay">
          <h2 class="headline headline--medium t-center">Explore Our Accomodation</h2>
          <p class="t-center">We have several luxury yurts and bell tents set in our peacefull glade and forest setting</p>
          <p class="t-center no-margin"><a href="#" class="btn btn--dark-green">Learn more</a></p>
        </div>
      </div>
    </div>
    <div class="hero-slider__slide" style="background-image: url(<?php echo get_theme_file_uri('/img/slider-2.jpg'); ?>)">
      <div class="hero-slider__interior container">
        <div class="hero-slider__overlay">
          <h2 class="headline headline--medium t-center">Discover The Area</h2>
          <p class="t-center">A stones throw from the spectacular Pyrenean peaks and unspoilt countryside</p>
          <p class="t-center no-margin"><a href="#" class="btn btn--dark-green">Learn more</a></p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Section Two - Glamping In the Pyrenees - Tagline and words to draw people in  -->

<section class="section-two">
  <div class="container container--narrow">
    <div class="hook-strip t-center">
      <h2 class="headline headline--medium">A Pyrenean Glamping Experience</h2>
      <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Tempora laboriosam harum, dignissimos repudiandae a explicabo quod iure esse non minima.</p>
      <hr class="headline__hr-center">
    </div>
  </div>
</section>

<!-- Flipping Card Section for key information -->

<section class="section-three">
  <div class="container container__flex container__flex--front-page">
    <div class="flip-container" ontouchstart="this.classList.toggle('hover');">
      <div class="flipper">
        <div class="front">
          <div class="flip-container__inner flip-container__inner-front t-center">
            <img class="flip-container__img" src="https://images.pexels.com/photos/264109/pexels-photo-264109.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=750&w=1260" alt="family">
            <h2>Family Friendly</h2>
            <i class="fa fa-arrow-circle-right flip-container__icon-large t-center" aria-hidden="true"></i>
          </div>
        </div>
        <div class="back">
          <div class="flip-container__inner flip-container__inner-back t-center">
            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Distinctio placeat laboriosam doloribus, sed consequatur provident pariatur aliquid labore! Et aspernatur veritatis sunt eos doloribus dicta, voluptatibus ad labore quia. Earum!</p>
            <p class="t-center no-margin"><a href="#" class="btn btn--dark-green btn--fix-bottom">Learn more</a></p>
          </div>
        </div>
      </div>
    </div>
    <div class="flip-container" ontouchstart="this.classList.toggle('hover');">
      <div class="flipper">
        <div class="front">
          <div class="flip-container__inner flip-container__inner-front t-center">
            <img class="flip-container__img" src="https://images.unsplash.com/photo-1510034141778-a4d065653d92?ixlib=rb-0.3.5&ixid=eyJhcHBfaWQiOjEyMDd9&s=e45b24be03d6d375ad51287db3f8d961&auto=format&fit=crop&w=500&q=60" alt="family">
            <h2>Eco Conscious</h2>
            <i class="fa fa-arrow-circle-right flip-container__icon-large t-center" aria-hidden="true"></i>
          </div>
        </div>
        <div class="back">
          <div class="flip-container__inner flip-container__inner-back t-center">
            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Distinctio placeat laboriosam doloribus, sed consequatur provident pariatur aliquid labore! Et aspernatur veritatis sunt eos doloribus dicta, voluptatibus ad labore quia. Earum!</p>
            <p class="t-center no-margin"><a href="#" class="btn btn--dark-green btn--fix-bottom">Learn more</a></p>
          </div>
        </div>
      </div>
    </div>
    <div class="flip-container" ontouchstart="this.classList.toggle('hover');">
      <div class="flipper">
        <div class="front">
          <div class="flip-container__inner flip-container__inner-front t-center">
            <img class="flip-container__img" src="https://images.unsplash.com/photo-1523297741243-79e695ee9a44?ixlib=rb-0.3.5&ixid=eyJhcHBfaWQiOjEyMDd9&s=5ca9f6871b71be7ec386093475dca981&auto=format&fit=crop&w=500&q=60" alt="family">
            <h2>Relax or Explore</h2>
            <i class="fa fa-arrow-circle-right flip-container__icon-large t-center" aria-hidden="true"></i>
          </div>
        </div>
        <div class="back">
          <div class="flip-container__inner flip-container__inner-back t-center">
            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Distinctio placeat laboriosam doloribus, sed consequatur provident pariatur aliquid labore! Et aspernatur veritatis sunt eos doloribus dicta, voluptatibus ad labore quia. Earum!</p>
            <p class="t-center no-margin"><a href="#" class="btn btn--dark-green btn--fix-bottom">Learn more</a></p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="container container__flex container__flex--front-page container--flex-last">
    <div class="flip-container" ontouchstart="this.classList.toggle('hover');">
      <div class="flipper">
        <div class="front">
          <div class="flip-container__inner flip-container__inner-front t-center">
            <img class="flip-container__img" src="https://images.pexels.com/photos/264109/pexels-photo-264109.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=750&w=1260" alt="family">
            <h2>Family Friendly</h2>
            <i class="fa fa-arrow-circle-right flip-container__icon-large t-center" aria-hidden="true"></i>
          </div>
        </div>
        <div class="back">
          <div class="flip-container__inner flip-container__inner-back t-center">
            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Distinctio placeat laboriosam doloribus, sed consequatur provident pariatur aliquid labore! Et aspernatur veritatis sunt eos doloribus dicta, voluptatibus ad labore quia. Earum!</p>
            <p class="t-center no-margin"><a href="#" class="btn btn--dark-green btn--fix-bottom">Learn more</a></p>
          </div>
        </div>
      </div>
    </div>
    <div class="flip-container" ontouchstart="this.classList.toggle('hover');">
      <div class="flipper">
        <div class="front">
          <div class="flip-container__inner flip-container__inner-front t-center">
            <img class="flip-container__img" src="https://images.unsplash.com/photo-1510034141778-a4d065653d92?ixlib=rb-0.3.5&ixid=eyJhcHBfaWQiOjEyMDd9&s=e45b24be03d6d375ad51287db3f8d961&auto=format&fit=crop&w=500&q=60" alt="family">
            <h2>Eco Conscious</h2>
            <i class="fa fa-arrow-circle-right flip-container__icon-large t-center" aria-hidden="true"></i>
          </div>
        </div>
        <div class="back">
          <div class="flip-container__inner flip-container__inner-back t-center">
            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Distinctio placeat laboriosam doloribus, sed consequatur provident pariatur aliquid labore! Et aspernatur veritatis sunt eos doloribus dicta, voluptatibus ad labore quia. Earum!</p>
            <p class="t-center no-margin"><a href="#" class="btn btn--dark-green btn--fix-bottom">Learn more</a></p>
          </div>
        </div>
      </div>
    </div>
    <div class="flip-container" ontouchstart="this.classList.toggle('hover');">
      <div class="flipper">
        <div class="front">
          <div class="flip-container__inner flip-container__inner-front t-center">
            <img class="flip-container__img" src="https://images.unsplash.com/photo-1523297741243-79e695ee9a44?ixlib=rb-0.3.5&ixid=eyJhcHBfaWQiOjEyMDd9&s=5ca9f6871b71be7ec386093475dca981&auto=format&fit=crop&w=500&q=60" alt="family">
            <h2>Relax or Explore</h2>
            <i class="fa fa-arrow-circle-right flip-container__icon-large t-center" aria-hidden="true"></i>
          </div>
        </div>
        <div class="back">
          <div class="flip-container__inner flip-container__inner-back t-center">
            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Distinctio placeat laboriosam doloribus, sed consequatur provident pariatur aliquid labore! Et aspernatur veritatis sunt eos doloribus dicta, voluptatibus ad labore quia. Earum!</p>
            <p class="t-center no-margin"><a href="#" class="btn btn--dark-green btn--fix-bottom">Learn more</a></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>


<?php get_footer(); ?>
