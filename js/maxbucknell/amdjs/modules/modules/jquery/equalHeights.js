define('jquery/equalHeights', ['jquery'], function ($) {
    $.fn.equalHeights = function (minHeight, maxHeight) {
        var tallest = (minHeight) ? minHeight : 0;

        this.each(function (el) {
            var h = $(el).height();
            tallest = h > tallest ? h : tallest;
        });

        tallest = maxHeight < tallest ? maxHeight : tallest;

        this.height(tallest);
    };

    return $;
});