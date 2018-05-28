import $ from 'jquery';

class SectionOneTop {
  constructor() {
    this.head = $(".site-header");
    this.sectionOne = $(".section-one");
    this.initTop();
  }

  initTop() {
    var height = this.head.outerHeight();
    this.sectionOne.css('padding-top', height + 'px');
    console.log(height);
  }
}

export default SectionOneTop;
