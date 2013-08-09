/**
 * Инициализация слайдов
 * @param {object} $el контейнер слайдов
 * @param {bool} $h горизонтальный?
 * @param {bool} auto автопрокручивание?
 * @returns {null}
 */
function slides_init($el, $h, auto) {
    if (!$el)
        $el = '.slidesContainer';
    jQuery(document).ready(
            function() {
                jQuery($el + ":not(.inited_sshow)").each(
                        function() {
                            var currentPosition = 0;
                            var slidesContainer = jQuery(this);
                            slidesContainer.css('overflow', 'hidden').addClass('inited_sshow');
                            var slides = slidesContainer.children('.slide');
                            var numberOfSlides = slides.length;
                            slidesContainer.wrap('<div class="slideshow"></div>');
                            var slideshow = slidesContainer.parent(".slideshow");
                            slides.show();
                            if (!$h)
                                var slideWidth = slides.eq(0).width();
                            else
                                var slideWidth = slides.eq(0).height();
                            if (!slideWidth)
                                slideWidth = 400;
                            slides.wrapAll("<div class='slideInner'></div>");
                            var slideInner = slides.parent("div.slideInner");
                            $width = slideWidth * numberOfSlides;
                            if (!$h) {
                                slideInner.css({
                                    'float': 'left',
                                    'width': $width + "px !important"
                                });
                            } else {
                                slideInner.css({
                                    'float': 'left',
                                    'height': $width + "px !important"
                                });
                            }
                            slideshow.append('<span class="control leftControl">Clicking moves left</span>').prepend(
                                    '<span class="control rightControl">Clicking moves right</span>');
                            $subel = slideshow.children('.control');
                            //manageControls(currentPosition, $subel);
                            $subel.bind('click', {'h': $h, 'slideWidth': slideWidth, 'numberOfSlides': numberOfSlides},
                            function(event) {
                                var $h = event.data.h;
                                var slideWidth = event.data.slideWidth;
                                var numberOfSlides = event.data.numberOfSlides;
                                slide(jQuery(this), slideWidth, numberOfSlides, $h, jQuery(this).is('.rightControl'));
                            });

                            if (auto)
                                setInterval(slide, 10000, $subel, slideWidth, numberOfSlides, $h, true);

                            function slide($subel, slideWidth, numberOfSlides, $h, right) {
                                currentPosition += right ? 1 : -1;
                                if (currentPosition < 0)
                                    currentPosition = numberOfSlides - 1;
                                if (currentPosition > numberOfSlides - 1)
                                    currentPosition = 0;
                                //manageControls(currentPosition, jQuery(this));
                                var o = $subel.parent('.slideshow').children('div.slidesContainer').children(
                                        'div.slideInner');
                                var $ml = slideWidth * (-currentPosition);
                                if (!$h)
                                    o.animate({
                                        'marginLeft': $ml
                                    });
                                else
                                    o.animate({
                                        'marginTop': $ml
                                    });
                            }

                            function manageControls(position, el) {
                                if (position == 0) {
                                    el.parent(".slideshow").children('.leftControl').hide();
                                } else {
                                    el.parent(".slideshow").children('.leftControl').show();
                                }
                                if (position == numberOfSlides - 1) {
                                    el.parent(".slideshow").children('.rightControl').hide();
                                } else {
                                    el.parent(".slideshow").children('.rightControl').show();
                                }
                            }
                        });
            });
}