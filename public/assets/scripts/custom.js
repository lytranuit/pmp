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
    /// Chọn đối tượng
    $(".object_select").click(function () {
        var i = $(this).data("id");
        var name = $(this).data("name");

        // console.log(table_name);
        $.cookies.set('SELECT_NAME', name);
        $.cookies.set('SELECT_ID', i);
        location.reload();
    });
    //// Lấy data
    $("#form-dang-tin [name=area_id]").change(async function () {
        let value = $(this).val();
        let department = await $.ajax({
            url: path + "department/getbyparent/" + value,
            dataType: "JSON"
        });
        let html = "";
        $.each(department, function (k, item) {
            html += "<option value='" + item.id + "'>" + item.name + "</option>";
        })
        $("#form-dang-tin [name=department_id]").html(html);
        $("#form-dang-tin [name=department_id]").trigger("change");
    });
    $("#form-dang-tin [name=workshop_id]").change(async function () {
        let value = $(this).val();
        let area = await $.ajax({
            url: path + "area/getbyparent/" + value,
            dataType: "JSON"
        });
        let html = "";
        $.each(area, function (k, item) {
            html += "<option value='" + item.id + "'>" + item.name + "</option>";
        })
        $("#form-dang-tin [name=area_id]").html(html);
        if ($("#form-dang-tin [name=department_id]").length)
            $("#form-dang-tin [name=area_id]").trigger("change");
    });
    $("#form-dang-tin [name=factory_id]").change(async function () {
        let value = $(this).val();
        let workshop = await $.ajax({
            url: path + "workshop/getbyparent/" + value,
            dataType: "JSON"
        });
        let html = "";
        $.each(workshop, function (k, item) {
            html += "<option value='" + item.id + "'>" + item.name + "</option>";
        })
        $("#form-dang-tin [name=workshop_id]").html(html);
        if ($("#form-dang-tin [name=area_id]").length)
            $("#form-dang-tin [name=workshop_id]").trigger("change");
    });
    init();
})

function init() {
    var select_id = $.cookies.get('SELECT_ID') || "1";
    var object_name = $.cookies.get('SELECT_NAME') || "";

    $(".object_select").addClass("btn-success").removeClass("btn-primary");
    $(".object_select[data-id=" + select_id + "]").removeClass("btn-success").addClass("btn-primary");
    $("#btn_object_select").text(object_name);
}
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