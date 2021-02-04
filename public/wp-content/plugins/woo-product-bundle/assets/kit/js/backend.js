'use strict';

jQuery(document).ready(function($) {
  $('body').on('click', '.wpckit-install-now', function(e) {
    var _this = $(this);
    var _href = _this.attr('href');

    _this.addClass('updating-message').html('Installing...');

    $.get(_href, function(data) {
      location.reload();
    });

    e.preventDefault();
  });
});
