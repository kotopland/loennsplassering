// client side validation for only numbers
window.validateAcceptOnlyNumbers = function (event) {
    var key = window.event ? event.keyCode : event.which;
    if (event.keyCode == 13  //enter
        || event.keyCode == 8 // tab
        || event.keyCode == 9  //backspace
        || event.keyCode == 46 //delete
        || event.keyCode == 37 // left arrow
        || event.keyCode == 39 // right arrow
        || event.keyCode == 16 // shift
    ) {
        return true;
    }//only accept numbers 
    else if (key < 48 || key > 57) {
        return false;
    } else return true;
};


document.addEventListener("DOMContentLoaded", () => {

    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
    const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(
        popoverTriggerEl));

    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

});

/* Cookie Concent */
document.addEventListener('DOMContentLoaded', function () {
    const banner = document.getElementById('cookie-banner');
    const acceptButton = document.getElementById('accept-cookies');
    const rejectButton = document.getElementById('reject-cookies');

    // Check if the cookie consent has been set
    const cookieConsent = document.cookie.split('; ').find(row => row.startsWith('cookie_consent='));
    if (!cookieConsent) {
        banner.style.display = 'block'; // Show the banner if consent is not set
    }

    // Set consent when the user clicks "Accept"
    acceptButton.addEventListener('click', function () {
        document.cookie = "cookie_consent=accepted; path=/; max-age=31536000"; // 1 year expiry
        banner.style.display = 'none';
    });

    // Set consent when the user clicks "Reject"
    rejectButton.addEventListener('click', function () {
        document.cookie = "cookie_consent=rejected; path=/; max-age=31536000"; // 1 year expiry
        banner.style.display = 'none';
    });
});

/* END Cookie Concent */
