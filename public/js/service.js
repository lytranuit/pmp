$(document).ready(function () {

    if ($(".responsive-tabs").length > 0) {
        var i;
        for (i = 0; i < $(".responsive-tabs").length; i++) {
            var responsiveTabsItem = $($(".responsive-tabs")[i]);
            responsiveTabsItem.easyResponsiveTabs({ type: responsiveTabsItem.attr("data-type") === "accordion" ? "accordion" : "default" });
            if (responsiveTabsItem.find('.owl-carousel').length) {
                responsiveTabsItem.find('.resp-tab-item').on('click', $.proxy(function (event) {
                    var $this = $(this), carouselObj = ($this.find('.resp-tab-content-active .owl-carousel').owlCarousel()).data('owlCarousel');
                    if (carouselObj && typeof carouselObj.onResize === "function") {
                        carouselObj.onResize();
                    }
                }, responsiveTabsItem));
                responsiveTabsItem.find('.resp-accordion').on('click', $.proxy(function (event) {
                    var $this = $(this), carouselObj = ($this.find('.resp-tab-content-active .owl-carousel').owlCarousel()).data('owlCarousel');
                    if (carouselObj && typeof carouselObj.onResize === "function") {
                        carouselObj.onResize();
                    }
                }, responsiveTabsItem));
            }
        }
    }
    $(".down").click(function () {
        var parent = $(this).parents(".product-item");
        var value = $(".stepper-input", parent).val();
        if (value == 1)
            return
        $(".stepper-input", parent).val(parseInt(value) - 1);
    });
    $(".up").click(function () {
        var parent = $(this).parents(".product-item");
        var value = $(".stepper-input", parent).val();
        $(".stepper-input", parent).val(parseInt(value) + 1);
    });
    $(".add-cart").click(function () {
        var select_table = $.cookies.get('SELECT_CART') || "1";
        var data_cart = $.cookies.get('DATA_CART') || {};
        var cart = data_cart[select_table] || [];
        /*
         * 
         */
        //        var count = $('.shop-cart .cart_count span');
        var product = $(this).parents(".product-item");
        var id = product.data("id");
        var qty = parseInt($(".stepper-input", product).val());
        var price = product.data("price");
        var amount = qty * price;
        var index = -1;
        $.each(cart, function (i, v) {
            if (v.id == id)
                index = i;
        });
        if (index != -1) {
            cart[index].qty = parseInt(cart[index].qty) + qty;
            cart[index].amount = cart[index].price * cart[index].qty;
        } else {
            cart.push({ id: id, qty: qty, price: price, amount: amount });
        }
        data_cart[select_table] = cart;
        $.cookies.set('DATA_CART', data_cart);
        init_table()


        var cart = $('.fa-shopping-cart:visible').last();
        var imgtodrag = product.find("img").eq(0);
        if (imgtodrag.length) {
            var imgclone = imgtodrag.clone()
                .offset({
                    top: imgtodrag.offset().top,
                    left: imgtodrag.offset().left
                })
                .css({
                    'opacity': '0.5',
                    'position': 'absolute',
                    'height': '150px',
                    'width': '150px',
                    'z-index': '100'
                })
                .appendTo($('body'))
                .animate({
                    'top': cart.offset().top + 10,
                    'left': cart.offset().left + 10,
                    'width': 75,
                    'height': 75
                }, 1000, 'easeInOutExpo');

            //            setTimeout(function () {
            //                cart.effect("shake", {
            //                    times: 2
            //                }, 200);
            //            }, 1500);

            imgclone.animate({
                'width': 0,
                'height': 0
            }, function () {
                $(this).detach()
            });
        }
        return false;
    });
    $(".order-cart").click(async function () {

        var select_table = $.cookies.get('SELECT_CART') || "1";
        var data_cart = $.cookies.get('DATA_CART') || {};
        var cart = data_cart[select_table] || [];

        $(".cart-list").empty()
        if (!cart.length) {
            alert("Xin mời chọn sản phẩm");
        }
        var cart = await $.ajax({
            url: path + "ajax/getcart",
            data: { data: JSON.stringify(cart) },
            dataType: "JSON",
            type: "POST"
        });
        var amount_product = formatCurrency(cart['amount_product'], 0, ",", ".");
        var details = cart['details'];
        $("#cart-tc").text(amount_product);
        $.each(details, function (k, v) {
            var id = v['id'];
            var name = v['name'];
            var image = v['image_url'];
            var price = formatCurrency(v['price'], 0, ",", ".");
            var qty = v['qty'];
            var amount = formatCurrency(v['amount_product'], 0, ",", ".");
            var html = $("#template .product-cart").clone();
            $(".item-name", html).text(name);
            $(".item-image", html).attr("src", path + image);
            $(".item-qty", html).val(qty);
            $(".item-price", html).text(price);
            $(".item-amount", html).text(amount);
            $(".cart-list").prepend(html);
            html.attr("data-id", id).attr("data-price", v['price']);
        });
    });
    $(document).on("change", ".item-qty", function () {
        var parent = $(this).parents(".product-cart");
        var qty = $(this).val() || 0;
        var id = parent.data("id");
        var select_table = $.cookies.get('SELECT_CART') || "1";
        var data_cart = $.cookies.get('DATA_CART') || {};
        var cart = data_cart[select_table] || [];
        var amount_total = 0;
        $.each(cart, function (i, v) {
            if (v.id == id) {
                cart[i].qty = qty;
            }
            var item = $(".product-cart[data-id='" + v.id + "']");
            var item_price = item.data("price");
            var item_qty = cart[i].qty;
            var item_amount = item_qty * item_price;
            cart[i].price = item_price;
            cart[i].amount = item_amount;
            amount_total += item_amount;
            $(".product-cart[data-id='" + v.id + "'] .item-amount").text(formatCurrency(item_amount, 0, ",", "."));
        });

        $("#cart-tc").text(formatCurrency(amount_total, 0, ",", "."));
        data_cart[select_table] = cart;
        $.cookies.set('DATA_CART', data_cart);
        init_table()
    });
    $(document).on("click", ".item-close", function () {
        var parent = $(this).parents(".product-cart");
        var id = parent.data("id");
        var select_table = $.cookies.get('SELECT_CART') || "1";
        var data_cart = $.cookies.get('DATA_CART') || {};
        var cart = data_cart[select_table] || [];
        var amount_total = 0;
        var index = -1;
        $.each(cart, function (i, v) {
            if (v.id == id) {
                index = i;
                return;
            }
            var item = $(".product-cart[data-id='" + v.id + "']");
            var item_price = item.data("price");
            var item_qty = cart[i].qty;
            var item_amount = item_qty * item_price;
            cart[i].price = item_price;
            cart[i].amount = item_amount;
            amount_total += item_amount;
            $(".product-cart[data-id='" + v.id + "'] .item-amount").text(formatCurrency(item_amount, 0, ",", "."));
        });
        if (index != -1) {
            cart.splice(index, 1);
        }
        $("#cart-tc").text(formatCurrency(amount_total, 0, ",", "."));
        data_cart[select_table] = cart;
        $.cookies.set('DATA_CART', data_cart);
        parent.remove();
        init_table();
    })
    $(".finish-cart").click(function () {
        var select_table = $.cookies.get('SELECT_CART') || "1";
        var data_cart = $.cookies.get('DATA_CART') || {};
        var cart = data_cart[select_table] || [];
        if (!cart.length) {
            alert("Xin mời chọn sản phẩm");
            return false;
        }
        var note = $.cookies.get('SELECT_CART_NAME') || "1";
        $.ajax({
            url: path + "ajax/cart",
            data: { data: JSON.stringify(cart), note: note, table_id: select_table },
            dataType: "JSON",
            type: "POST",
            success: function (data) {
                data_cart[select_table] = [];
                $.cookies.set('DATA_CART', data_cart);
                if (is_print_bill) {
                    window.open('/index/printbill?id=' + data, '_blank');
                }
                location.reload();

            },
            error: function () {
                alert("Lỗi thanh toán!");
            }
        });
    });
    $(".table_order").click(function () {
        var text = $(this).text();
        var i = $(this).data("id");
        var table_name = $(this).data("name");

        $(".table_order").removeClass("btn-danger");
        $(this).addClass("btn-danger");

        $(".table-cart").text(text).addClass("btn-warning");
        if ($(this).hasClass("btn-success")) {
            $(".table-cart").removeClass("btn-warning").addClass("btn-success");
        }
        // console.log(table_name);
        $.cookies.set('SELECT_CART_NAME', table_name);
        $.cookies.set('SELECT_CART', i);
        $('#table-modal').modal('hide');


        var data_cart = $.cookies.get('DATA_CART') || {};
        $(".product-item .qty-product").text("");
        if (data_cart[i] && data_cart[i].length) {

            $(".open_table").prop("disabled", true);
            $.each(data_cart[i], function (k, v) {
                var id = v.id;
                var qty = v.qty;
                $(".product-item[data-id=" + id + "] .qty-product").text("(" + qty + ")");
            });
        } else {

            $(".open_table").prop("disabled", false);
        }
        ///EXchange
        // $(".table_exchange").removeClass("btn-danger");
        // $(".table_exchange[data-id=" + i + "]").addClass("btn-danger");
    });
    $(".table_order").dblclick(function () {
        location.href = "/index/service";
    });
    $("#search").keydown(function (e) {
        $(".product-item").addClass("d-none");
        var val = $(this).val().toLowerCase();
        if (val == "") {
            $(".product-item").removeClass("d-none");
        } else {
            $(".resp-tab-content").show();
            $(".product-item").each(function () {
                var name = xoa_dau($(this).data("search").toLowerCase());
                if (name.search(val) != -1) {
                    $(this).removeClass("d-none");
                }
            })
        }
    });
    $(".qty-product").click(function () {
        var r = confirm("Xóa sản phẩm!");
        if (r == true) {
            var parent = $(this).parents(".product-item");
            var id = parent.data("id");
            var select_table = $.cookies.get('SELECT_CART') || "1";
            var data_cart = $.cookies.get('DATA_CART') || {};
            var cart = data_cart[select_table] || [];
            var index = -1;
            $.each(cart, function (i, v) {
                if (v.id == id) {
                    index = i;
                    return;
                }
            });
            if (index != -1) {
                cart.splice(index, 1);
            }
            data_cart[select_table] = cart;
            $.cookies.set('DATA_CART', data_cart);

            init_table();
        }
    })
    $(".table_exchange:not(.btn-danger)").click(function () {
        var old_name = $.cookies.get('SELECT_CART_NAME');
        var old_id = $.cookies.get('SELECT_CART');
        var new_id = $(this).data("id");
        var new_name = $(this).data("name");
        var r = confirm("Chuyển từ " + old_name + " sang " + new_name);
        if (r == true) {
            var data_cart = $.cookies.get('DATA_CART') || {};
            var cart = data_cart[old_id];
            if (!cart)
                return;
            delete data_cart[old_id];
            data_cart[new_id] = cart;
            $.cookies.set('DATA_CART', data_cart);
            location.reload();
        }

    });
    $('#table-exchange-modal').on('shown.bs.modal', function () {
        var select_table = $.cookies.get('SELECT_CART');
        $(".table_exchange").removeClass("btn-danger");
        $(".table_exchange[data-id=" + select_table + "]").addClass("btn-danger");
        var data_cart = $.cookies.get('DATA_CART') || {};
        if (!data_cart[select_table] || data_cart[select_table].length == 0) {
            $(this).modal('hide');
            alert("Chưa gọi món!");
            return;
        }
        for (var table_id in data_cart) {
            if (data_cart[table_id].length > 0) {
                console.log(table_id);
                $(".table_exchange[data-id=" + table_id + "]").prop("disabled", true);
            }
        }
    });
    $(".open_table").click(async function () {
        var select_table = $.cookies.get('SELECT_CART') || "1";
        var data_cart = $.cookies.get('DATA_CART') || {};
        var cart_open = await $.ajax({
            url: path + "ajax/getopencart",
            data: {},
            dataType: "JSON",
            type: "GET"
        });
        let cart = [];
        $.each(cart_open, function (k, v) {
            let id = v['product_id'];
            let quantity = v['quantity'];

            let product = $(".product-item[data-id=" + id + "]");
            let price = parseInt(product.data("price"));
            let amount = quantity * price;
            cart.push({ id: id, qty: quantity, price: price, amount: amount });
        })
        data_cart[select_table] = cart;
        $.cookies.set('DATA_CART', data_cart);
        init_table();
    })
    init_table();

});
function xoa_dau(str) {
    str = str.replace(/à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ/g, "a");
    str = str.replace(/è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ/g, "e");
    str = str.replace(/ì|í|ị|ỉ|ĩ/g, "i");
    str = str.replace(/ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ/g, "o");
    str = str.replace(/ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ/g, "u");
    str = str.replace(/ỳ|ý|ỵ|ỷ|ỹ/g, "y");
    str = str.replace(/đ/g, "d");
    str = str.replace(/À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ/g, "A");
    str = str.replace(/È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ/g, "E");
    str = str.replace(/Ì|Í|Ị|Ỉ|Ĩ/g, "I");
    str = str.replace(/Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ/g, "O");
    str = str.replace(/Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ/g, "U");
    str = str.replace(/Ỳ|Ý|Ỵ|Ỷ|Ỹ/g, "Y");
    str = str.replace(/Đ/g, "D");
    return str;
}
function init_table() {
    var data_cart = $.cookies.get('DATA_CART') || {};
    if (!$.isEmptyObject(data_cart)) {
        for (var i in data_cart) {
            if (data_cart[i].length) {
                var total_amount = 0;

                $(".product-item .qty-product").text("");
                $.each(data_cart[i], function (k, v) {
                    total_amount += v.amount;
                    var id = v.id;
                    var qty = v.qty;
                    $(".product-item[data-id=" + id + "] .qty-product").text("(" + qty + ")");
                });
                $(".table_order[data-id=" + i + "]").addClass("btn-success").removeClass("btn-warning");
                $(".table_order[data-id=" + i + "] .table_amount").html(formatCurrency(total_amount, 0, ",", "."));
            } else {
                $(".product-item .qty-product").text("");

                $(".table_order[data-id=" + i + "]").addClass("btn-warning").removeClass("btn-success");
                $(".table_order[data-id=" + i + "] .table_amount").html("")
            }
        }
    } else {
        $(".open_table").prop("disabled", false);
        $(".table_order .table_amount").html("")
        $(".table_order").addClass("btn-warning").removeClass("btn-success");
    }

    var select_table = $.cookies.get('SELECT_CART') || "1";
    $(".table_order[data-id=" + select_table + "]").trigger("click");


}
function formatCurrency(n, c, d, t) {
    var c = isNaN(c = Math.abs(c)) ? 2 : c,
        d = d == undefined ? "." : d,
        t = t == undefined ? "," : t,
        s = n < 0 ? "-" : "",
        i = String(parseInt(n = Math.abs(Number(n) || 0).toFixed(c))),
        j = (j = i.length) > 3 ? j % 3 : 0;

    return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
}
