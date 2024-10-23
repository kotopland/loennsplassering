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

