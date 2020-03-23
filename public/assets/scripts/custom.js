$(document).ready(function () {
    ///MOVE MODAL RA NGOÀI
    $(".app-main .modal").detach().appendTo("body");
    ////ACTIVE MENU
    setTimeout(function () {
        let url = window.location.href;
        let a = $(".metismenu a[href='" + url + "']");
        a.addClass("mm-active");
        if (a.parents(".mm-collapse").length) {
            let p = a.parents("li");
            p.trigger("click");
            $(">a", p).trigger("click");
        }
    }, 1000)
})


var fillForm = function (form, data) {
    $('input, select, textarea', form).not("[type=file]").each(function () {
        var type = $(this).attr('type');
        var name = $(this).attr('name');
        name = name.replace("[]", "");
        var value = "";
        if ($(this).hasClass("input-tmp"))
            return
        if ($.type(data[name]) !== "undefined") {
            value = data[name];
        } else {
            return;
        }
        switch (type) {
            case 'checkbox':
                $(this).prop('checked', false);
                if (value == true || value == 'true' || value == 1) {
                    $(this).prop('checked', true);
                }
                break;
            case 'radio':
                $(this).removeAttr('checked', 'checked');
                var rdvalue = $(this).val();
                if (rdvalue == value) {
                    $(this).prop('checked', true);
                }
                break;
            default:
                $(this).val(value);
                break;
        }
        //            $('select', form).selectpicker('render');
    });
}