/**
 * Инициализация слайдов
 * @param $el object контейнер слайдов
 * @return null
 */
function slides_init($el) {
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
                    var slideWidth = slides.eq(0).width();
                    if (!slideWidth)
                        slideWidth = 400;
                    slides.wrapAll("<div class='slideInner'></div>");
                    var slideInner = slides.parent("div.slideInner");
                    $width = slideWidth * numberOfSlides;
                    slideInner.css({
                        'float' : 'left',
                        'width' : $width + "px !important"
                    });
                    slideshow.append('<span class="control leftControl">Clicking moves left</span>').prepend(
                        '<span class="control rightControl">Clicking moves right</span>');
                    $subel = slideshow.children('.control');
                    manageControls(currentPosition, $subel);
                    $subel.bind('click', function() {
                        currentPosition = (jQuery(this).is('.rightControl')
                            ? currentPosition + 1
                            : currentPosition - 1);
                        manageControls(currentPosition, jQuery(this));
                        jQuery(this).parent('.slideshow').children('div.slidesContainer').children(
                            'div.slideInner').animate({
                            'marginLeft' : slideWidth * (-currentPosition)
                        });
                    });
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