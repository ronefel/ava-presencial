/**
 * Created by 08429611436 on 19/02/2018.
 */
var acc = document.getElementsByClassName("accordion");
var i;

for (i = 0; i < acc.length; i++) {
    acc[i].addEventListener("click", function() {
        this.classList.toggle("active");
        var panel = this.previousElementSibling;
        var icon = $(this).find('i');
        if (panel.style.maxHeight){
            panel.style.maxHeight = null;
            icon.addClass('fa-angle-down');
            icon.removeClass('fa-angle-up');
        } else {
            panel.style.maxHeight = panel.scrollHeight + "px";
            icon.removeClass('fa-angle-down');
            icon.addClass('fa-angle-up');
        }

    });
}