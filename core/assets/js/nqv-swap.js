$.fn.nqvSwap = function () {
    var controls = $(this).find('.nqv-swap-control');
    controls.each(function () {
        var control = $(this);
        var target = $(control.data('target'));
        if (control.hasClass('condensed')) target.hide();
        control.on({
            click: function () {
                target.slideToggle();
                control.toggleClass('condensed expanded');
            }
        })
    });
};

jQuery.fn.extend({
    slideRightShow: function (t) {
        return this.each(function () {
            if(!t) t = 1000;
            $(this).show('slide', {direction: 'right'}, t);
        });
    },
    slideLeftHide: function (t) {
        return this.each(function () {
            if(!t) t = 1000;
            $(this).hide('slide', {direction: 'left'}, t);
        });
    },
    slideRightHide: function (t) {
        return this.each(function () {
            if(!t) t = 1000;
            $(this).hide('slide', {direction: 'right'}, t);
        });
    },
    slideLeftShow: function (t) {
        return this.each(function () {
            if(!t) t = 1000;
            $(this).show('slide', {direction: 'left'}, t);
        });
    },
    slideLeftToogle: function (t) {
        return this.each(function () {
            if ($(this).is(':visible')) $(this).slideRightHide(t);
            else $(this).slideRightShow(t);
        });
    }
});