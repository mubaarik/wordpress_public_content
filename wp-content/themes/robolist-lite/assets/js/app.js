(function ($) {

    var $uploader = $('.wp-job-manager-file-upload');

    $uploader.each(function(i, obj) {
        var $input = $(obj),
            id = $(obj).attr('id'),
            $label = $('label[for="' + id + '"]'),
            $btn = $('<div class="uploader-btn"><div class="spacer"><div class="text">' + robolist_lite_params.strings['wp-job-manager-file-upload'] + '</div></div></div>').insertAfter($input);

        $btn.on('click', function() {
            $label.trigger('click');
        });
    });

    $('.testimonial-slider').slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        speed: 500,
        arrows: false,
    });

function responsiveIframe() {
    var videoSelectors = [
        'iframe[src*="player.vimeo.com"]',
        'iframe[src*="youtube.com"]',
        'iframe[src*="youtube-nocookie.com"]',
        'iframe[src*="kickstarter.com"][src*="video.html"]',
        'iframe[src*="screenr.com"]',
        'iframe[src*="blip.tv"]',
        'iframe[src*="dailymotion.com"]',
        'iframe[src*="viddler.com"]',
        'iframe[src*="qik.com"]',
        'iframe[src*="revision3.com"]',
        'iframe[src*="hulu.com"]',
        'iframe[src*="funnyordie.com"]',
        'iframe[src*="flickr.com"]',
        'embed[src*="v.wordpress.com"]',
        'iframe[src*="videopress.com"]',
        'embed[src*="videopress.com"]'
        // add more selectors here
    ];
    var allVideos = videoSelectors.join(',');
    jQuery(allVideos).wrap('<span class="media-holder" />');
}

// Responsive Iframes
responsiveIframe();

$('.listing-cat-slider').slick({
  infinite: true,
  autoplaySpeed: 7000,
  arrows: false,
  slidesToShow: 3,
  slidesToScroll: 3,
  responsive: [
      {
        breakpoint: 990,
        settings: {
          slidesToShow: 3,
          slidesToScroll: 3,
          infinite: true,
        }
      },
      {
        breakpoint: 768,
        settings: {
          slidesToShow: 2,
          slidesToScroll: 2
        }
      },
      {
        breakpoint: 600,
        settings: {
          slidesToShow: 1,
          slidesToScroll: 1
        }
      }
  ]
});

$('.listing-des-slider').slick({
  infinite: true,
  autoplaySpeed: 7000,
  arrows: false,
  slidesToShow: 3,
  slidesToScroll: 3,
  responsive: [
      {
        breakpoint: 990,
        settings: {
          slidesToShow: 3,
          slidesToScroll: 3,
          infinite: true,
        }
      },
      {
        breakpoint: 768,
        settings: {
          slidesToShow: 2,
          slidesToScroll: 2
        }
      },
      {
        breakpoint: 600,
        settings: {
          slidesToShow: 1,
          slidesToScroll: 1
        }
      }
  ]
});


$('.listing-gallery-wrap').slick({
  infinite: true,
  autoplaySpeed: 3000,
  arrows: true,
  autoplay: true,
  speed: 2000,
  centerMode: true,
  centerPadding: '300px',
  slidesToShow: 1,
  slidesToScroll: 1,
  responsive: [
      {
        breakpoint: 1990,
        settings: {
          centerMode: true,
          centerPadding: '500px',
        }
      },
      {
        breakpoint: 1400,
        settings: {
          slidesToShow: 1,
          centerMode: true,
          centerPadding: '300px',
          slidesToScroll: 1,
          infinite: true,
        }
      },
      {
        breakpoint: 990,
        settings: {
          slidesToShow: 1,
          centerMode: false,
          centerPadding: '0',
          slidesToScroll: 1,
          infinite: true,
        }
      },
      {
        breakpoint: 768,
        settings: {
          slidesToShow: 1,
          centerMode: false,
          centerPadding: '0',
          slidesToScroll: 1
        }
      },
      {
        breakpoint: 600,
        settings: {
          centerMode: false,
          centerPadding: '0',
          slidesToShow: 1,
          slidesToScroll: 1
        }
      }
  ]
});

    $(document).on('favorites-updated-single', function(event, favorites, post_id, site_id, status){
        var count = parseInt($('.rl-fav-count').html());
        if(favorites[0]['posts'][post_id]){
            $('.rl-fav-count').html(count+1);
        }
        else{
            $('.rl-fav-count').html(count-1);
        }
    });






//gallery
$('.post-format-gallery').slick({
    slidesToShow: 1,
    slidesToScroll: 1,
    speed: 500,
    autoplay: true,
    arrows: true
});
// Menu dropdown on hover
extendNav();
function extendNav() {
  jQuery('.nav-wrapper .dropdown,.signout').hover(function() {
    jQuery(this).children('.dropdown-menu').stop(true, true).show().addClass('animated-fast slfadeInDown');
    jQuery(this).toggleClass('open');
  }, function() {
    jQuery(this).children('.dropdown-menu').stop(true, true).hide().removeClass('animated-fast slfadeInDown');
    jQuery(this).toggleClass('open');
  });
}

$('.selectpicker').selectpicker();

$('[data-toggle="tooltip"]').tooltip();

$('.nav-wrapper').stickMe({
    transitionDuration: 500,
    shadow: true,
    shadowOpacity: 0.6,
    animate: true,
    topOffset: 100,
    transitionStyle: 'slide'
});
//parallax
$('.parallax').jarallax({

    // parallax effect speed. 0.0 - 1.0
    speed: 0.3,

    // path to your parallax image
    imgSrc: null,

    // width & height of your parallax image
    imgWidth: 1366,
    imgHeight: 768,

    // enable transformations for effect if supported.
    enableTransform: false,

    // z-index of parallax container.
    zIndex: -100

});

$(".listing-single-right .btn,.comments a").click(function(e) {
  e.preventDefault();

  var position = $($(this).attr("href")).offset().top;

  $("body, html").animate({
    scrollTop: position
  } /* speed */ );
});

    $(document).ready(function () {

        $('#simple-menu').sidr({
            speed: 600,
            side: 'right',
            body: '.offcanvas-wrap'
        });
        $.sidr('close', 'sidr');
        // $(window).on('resize',function(){
        //     $('#simple-menu').trigger('click');
        // });

        $(".menu-close").on("click", function (e) {
            $.sidr('close', 'sidr');
        });
    });


    $('<i class="icon ion-android-arrow-forward sidedropdown"></i>').appendTo($("#sidr li.menu-item-has-children > a"));

    $("#sidr ul .sidedropdown").on('click', function (event) {
        event.preventDefault();
        $(this).toggleClass('open');
        $(this).toggleClass('first');
        $(this).parent().next('.dropdown-menu').toggleClass('open');
    });

})(jQuery);
