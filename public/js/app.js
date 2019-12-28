$(document).ready(function() {
    // Dropdown
    $('.ui.menu .ui.dropdown').dropdown({
        on: 'hover'
    });
    $('.ui.menu a.item').on('click', function() {
        $(this).addClass('active')
            .siblings()
            .removeClass('active');
    });

    // Sticky menu
    $('.ui.sticky_menu').sticky({
        context: 'body#body > div.ui.container'
    });
    // Sticky right sidebar
    $('.ui.sticky_page').sticky({
        context: '#page'
    });
});
function copyToClipboard(input_id) {
    /* Get the text field */
    var copyText = document.getElementById(input_id);
    
    /* Select the text field */
    copyText.select();
    copyText.setSelectionRange(0, 99999); /*For mobile devices*/

    /* Copy the text inside the text field */
    document.execCommand("copy");
}