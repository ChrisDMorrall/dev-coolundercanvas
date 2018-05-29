import $ from 'jquery';

// Sets up slick-carousel slider for section-one

class HeroSlider {
  constructor() {
    this.els = $('.hero-slider');
    this.initSlider();
  }

  initSlider() {
    this.els.slick({
      autoplay: true,
      arrows: false,
      dots: true,
      autoplaySpeed: 6000,
    });
  }
}

export default HeroSlider;
