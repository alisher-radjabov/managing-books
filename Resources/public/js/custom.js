// Custom Scripts
// -----------------------------------------------------------------------------------------
$( function() {
    $.widget( "custom.catcomplete", $.ui.autocomplete, {
        _create: function() {
            this._super();
            this.widget().menu( "option", "items", "> :not(.ui-autocomplete-category)" );
        },
        _renderMenu: function( ul, items ) {
            var that = this,
                currentCategory = "";
            $.each( items, function( index, item ) {
                var li;
                if ( item.category != currentCategory ) {
                    ul.append( "<li class='ui-autocomplete-category'>" + item.category + "</li>" );
                    currentCategory = item.category;
                }
                li = that._renderItemData( ul, item );
                if ( item.category ) {
                    li.attr( "aria-label", item.category + " : " + item.label );
                }
            });
        }
    });
    var data = [
        { label: "Doctrines of the Twelver Shiite (Enquiry and Response)", category: "" },
        { label: "Answering the Baseless Shīʿī Allegations Against", category: "" },
        { label: "book of pure monotheism", category: "" },

        { label: "Doctrines of the Twelver Shiite (Enquiry and Response)", category: "Books and Categories" },
        { label: "Answering the Baseless Shīʿī Allegations Against", category: "Books and Categories" },
        { label: "book of pure monotheism", category: "Books and Categories" },

        { label: "Abdul Shakoor Farooqui of lucknow", category: "Authors and translators" },
        { label: "Saleh Sindi", category: "Authors and translators" },
    ];

    $( "#search" ).catcomplete({
        delay: 0,
        source: data
    });
} );








// Hide Header on on scroll down
var didScroll;
var lastScrollTop = 0;
var delta = 5;
var navbarHeight = $('header').outerHeight();

$(window).scroll(function(event){
    didScroll = true;
});

setInterval(function() {
    if (didScroll) {
        hasScrolled();
        didScroll = false;
    }
}, 250);

function hasScrolled() {
    var st = $(this).scrollTop();

    // Make sure they scroll more than delta
    if(Math.abs(lastScrollTop - st) <= delta)
        return;

    if (st > lastScrollTop && st > navbarHeight){
        // Scroll Down
        $('body').removeClass('nav-down').addClass('nav-up');
    } else {
        // Scroll Up
        if(st + $(window).height() < $(document).height()) {
            $('body').removeClass('nav-up').addClass('nav-down');
        }
    }

    lastScrollTop = st;
}


$(document).ready(function(){

    $('.dropdown-menu').on('click', function(event){
        event.stopPropagation();
    });



// Multi levels Menu

    $( '.multiLevels-menu li' ).each( function() {
        if( $( this ).children( 'ul' ).length > 0 ) {
            $( this ).addClass( 'parent' );
        }
    });
    $( '.multiLevels-menu li.parent > .ml-item .mlItem-expand' ).click( function( ) {
        $( this ).closest('li').toggleClass( 'active' );
        $( this ).closest('li').children( 'ul' ).slideToggle( 'fast' );
    });



    $("[data-fancybox]").fancybox();



    $('.search-scrollBox').slimscroll({
        height: '200px',
        width: '100%',
        touchScrollStep: 85,
        allowPageScroll: true,
    });


    $('.side-navigation-inner').slimscroll({
        height: 'auto',
        width: '100%',
        touchScrollStep: 85,
        alwaysVisible : true,
        size : '3px',
    });

    $(window).resize(function(){
        var h=$(window).height();
        $('.side-navigation-inner').height(h);
    });



    //  Rattings
    $('.kv-svg').rating({
        theme: 'krajee-svg',
        filledStar: '<span class="krajee-icon krajee-icon-star"></span>',
        emptyStar: '<span class="krajee-icon krajee-icon-star"></span>',
        clearButton:'<i class="ti-na"></i>',
        clearCaption:"قم باختيار التقييم المناسب",
        rtl:false
    });
    $('.kv-svg-heart').rating({
        theme: 'krajee-svg',
        filledStar: '<span class="krajee-icon krajee-icon-heart"></span>',
        emptyStar: '<span class="krajee-icon krajee-icon-heart"></span>',
        clearButton:'<i class="ti-na"></i>',
        defaultCaption: '{rating} hearts',
        clearCaption:"قم باختيار التقييم المناسب",
        rtl:false,
        starCaptions: function (rating) {
            return rating <= 2 ? 'سيء جدا' : rating ;
        },
        containerClass: 'is-heart'
    });


    $('.kv-svg').on(
        'change', function () {
            console.log('Rating selected: ' + $(this).val());
        });






// scroll-to-top animate
    jQuery(window).scroll(function () {
        if (jQuery(this).scrollTop() > 100) {
            jQuery('.scrollup').addClass('visible');
        } else {
            jQuery('.scrollup').removeClass('visible');
        }
    });
    jQuery('.scrollup').click(function () {
        jQuery("html, body").animate({
            scrollTop: 0
        }, 600);
        return false;
    });



    
// Close any div by target "href" =====================================================
	$('.openclose').click(function() {
        $($(this).attr('href')).fadeToggle();
        return false;
    });

	$('.div-toggle').click(function() {
        $($(this).attr('data-toggle')).slideToggle();
        return false;
    });


    $('.dropdown-item-Del').click(function() {
        $(this).closest('li').fadeToggle();
        return false;
    });



    $("input[name=testRes-options]").on( "change", function() {
        var test = $(this).val();
        $(".testRes-form").hide();
        $("#"+test).show();
    } );




    // List triggers

    $('.mainView-compact-trig').click(function(){
        $('#mainView').removeClass("mainView-normal").removeClass("mainView-list").addClass('mainView-compact');
    });

    $('.mainView-list-trig').click(function(){
        $('#mainView').removeClass("mainView-normal").removeClass("mainView-compact").addClass('mainView-list');
    });
    $('.mainView-normal-trig').click(function(){
        $('#mainView').removeClass("mainView-compact").removeClass("mainView-list").addClass('mainView-normal');
    });

// login box forget the password
    $('.forget-pass').click(function(){
        $('#forgetPass-form, #login-form').slideToggle();
    });


// fav trigg





// Search, advanced search





// Search, advanced search
    $('.menu-trigger').click(function(){
        $('body').toggleClass('side-menu-hidden');
    });

    $('.side-filters-trigger').click(function(){
        $('body').toggleClass('side-filters-hidden');
    });

    $('.page-expand-trigger').click(function(){
        $('body').toggleClass('page-expanded');
    });


    $('#search-trigger').click(function(){
        $('.mainSearch').addClass('mainSearch-visible');
        $('input.ui-autocomplete-input').focus();
    });

    $('#search-close').click(function(){
        $('.mainSearch').removeClass('mainSearch-visible');
    });




// tooltip
    $('[data-toggle="tooltip"]').tooltip();


    $(".select-filter").select2({
        placeholder: "Select a state",
        language: "ar"
    });

    $(".select-filter-nosearch").select2({
        placeholder: "Select a state",
        language: "ar",
        minimumResultsForSearch: Infinity
    });



// hide side menu on mobile size
    var $window = $(window);
    function checkWidth() {
        var windowsize = $window.width();
        if (windowsize <= 1199) {
            $('body').addClass('side-menu-hidden side-filters-hidden');
        }
        if (windowsize >= 991) {
            $(".dropdown-hover").hover(
                function() {
                    $(this).addClass("show");
                    $('.dropdown-menu', this).stop().fadeIn("fast");
                },
                function() {
                    $('.dropdown-menu', this).stop().fadeOut("fast");
                    $(this).removeClass("show");
                });

        }
    }
    checkWidth();
    // Bind event listener
    $(window).resize(checkWidth);




// Parallax Effect =====================================================
$window = $(window);
$('.parallax').each(function(){
     var $bgobj = $(this); // assigning the object
      $(window).scroll(function() {
		var yPos = -($window.scrollTop() / $bgobj.data('speed'));
		var coords = '50% '+ yPos + 'px';
		$bgobj.css({ backgroundPosition: coords });
}); // window scroll Ends
});



// parallax
    $(document).scroll(function(){
        var scrollPos = $(this).scrollTop();
        $('.parallax-over').css({
            'top' : (scrollPos/3)+'px',
            'opacity' : 1-(scrollPos/500)
        });
        $('.image-banner').css({
            'background-position' : 'center ' + (-scrollPos/2)+'px'
        });
    });







});



// Select2 arabic lang
(function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;return e.define("select2/i18n/ar",[],function(){return{errorLoading:function(){return"لا يمكن تحميل النتائج"},inputTooLong:function(e){var t=e.input.length-e.maximum,n="الرجاء حذف "+t+" عناصر";return n},inputTooShort:function(e){var t=e.minimum-e.input.length,n="الرجاء إضافة "+t+" عناصر";return n},loadingMore:function(){return"جاري تحميل نتائج إضافية..."},maximumSelected:function(e){var t="تستطيع إختيار "+e.maximum+" بنود فقط";return t},noResults:function(){return"لم يتم العثور على أي نتائج"},searching:function(){return"جاري البحث…"}}}),{define:e.define,require:e.require}})();
