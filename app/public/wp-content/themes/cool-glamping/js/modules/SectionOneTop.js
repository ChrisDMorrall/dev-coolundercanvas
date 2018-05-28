import $ from 'jquery';

// Moves section-one down by setting top margin dynamicaly based on calculated header outerHeight to allow for variable header heights

class SectionOneTop {
  constructor() {
    this.head = $(".site-header");
    this.sectionOne = $(".section-one");
    this.initTop();
  }

  initTop() {
    var height = this.head.outerHeight();
    this.sectionOne.css('padding-top', height + 'px');
  }
}

export default SectionOneTop;
