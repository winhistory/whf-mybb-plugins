Event.observe(window, "load", function() {

var MAX_WIDTH  = 640;
var MAX_HEIGHT = 800;

if(Prototype.Browser.WebKit) {
    var ZOOM_IN = '-webkit-zoom-in';
    var ZOOM_OUT = '-webkit-zoom-out';
} else if(Prototype.Browser.Gecko) {
    var ZOOM_IN = '-moz-zoom-in';
    var ZOOM_OUT = '-moz-zoom-out';
} else {
    var ZOOM_IN = 'pointer';
    var ZOOM_OUT = 'pointer';
}

/**
 *  main execution code
 */
 
if(WHFIMGRESIZE_PAGE) {
    var images = [];

    switch(WHFIMGRESIZE_PAGE) {
        case 'showthread':
        case 'editpost':
        case 'private':
            images = $$('.post_body img');
            break;
        case 'newreply':
            images = $$('.post_body img', 'table.tborder tbody img');
            break;
    }
    
    images.each(resizeImage);
    
    if($('quick_reply_form') && WHFIMGRESIZE_PAGE == "showthread") {
		$('posts').addEventListener('DOMNodeInserted', function() {
		    $$('.post_body img').each(resizeImage);
		});
	}
}

function resizeImage(img) {
    if(img.getAttribute('data-imgresized')) return;

    var orig_width = img.getWidth();
    var orig_height = img.getHeight();
    
    if(orig_width <= MAX_WIDTH && orig_height <= MAX_HEIGHT) {
        /* no resize needed */
        return;
    }
    
    /* flag as processed */    
    img.setAttribute('data-imgresized', 'true');
    
    /* new tab/window link */
    var link = new Element('a', {
        href: img.readAttribute('src'),
        target: '_blank'
    });
        
    if(img.up('blockquote:not(.spoiler)')) {
        /* quoted image, replace with text link */
        
        if(img.up('a')) {
            /* image is already linked, replace image with text */
            img.replace(img.readAttribute('alt'));
        } else {
            /* otherwise use new link */
            link.update(img.readAttribute('alt'));
            img.replace(link);
        }
        
        return;
    }
    
    /* calculate small size */
    if(orig_width > MAX_WIDTH) {
        var small_width = MAX_WIDTH;
        var small_height = Math.ceil((orig_height/orig_width)*small_width);
    } else {    
        var small_height = MAX_HEIGHT;
        var small_width = Math.ceil((orig_width/orig_height)*small_height);
    }
    
    /* set small size */
    var resized = true;
    img.style.width = small_width + 'px';
    img.style.height = small_height + 'px';
    
    var big_width = 0, big_height = 0;
    if(!img.up('a')) {
        /* surround image with created link */
        link.style.cursor = ZOOM_IN;
        
        img.wrap(link);
        img.observe('click', function (event) {
            if(event.isMiddleClick() || event.ctrlKey) return;
        
            /* prevent default */
            event.stop();
            
            /* calculate big size */
            if(big_width == 0 && big_height == 0) {
                img.style.width = '100%';

                if(img.measure('width') > orig_width) {
                    big_width = orig_width;
                    big_height = orig_height;
                } else {
                    big_width = img.measure('width');
                    big_height = Math.ceil((orig_height/orig_width)*big_width);
                }
            }
        
            if(resized) {
                img.style.width = big_width + 'px';;
                img.style.height = big_height + 'px';;
                link.style.cursor = ZOOM_OUT;
                resized = false;
            } else {
                img.style.width = small_width + 'px';;
                img.style.height = small_height + 'px';;
                link.style.cursor = ZOOM_IN;
                resized = true;
            }
        });
    }
}

});
