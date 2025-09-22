$(document).ready(function() {
    // Toggle first-level dropdown
    $('#produseBtn').click(function(e) {
        e.stopPropagation();
        $('#produseMenu').toggleClass('show');
    });

    // Toggle submenus
    $('.dropdown-submenu > a').click(function(e) {
        e.stopPropagation();
        $(this).next('.dropdown-content').toggleClass('show');
    });

    // Close all when clicking outside
    $(document).click(function() {
        $('.dropdown-content').removeClass('show');
    });
});
