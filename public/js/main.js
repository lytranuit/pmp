$(document).ready(function () {


    if ($(".responsive-tabs").length > 0) {
        var i;
        for (i = 0; i < $(".responsive-tabs").length; i++) {
            var responsiveTabsItem = $($(".responsive-tabs")[i]);
            responsiveTabsItem.easyResponsiveTabs({type: responsiveTabsItem.attr("data-type") === "accordion" ? "accordion" : "default"});
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
        var parent = $(this).parents(".thumbnail-menu-modern");
        var value = $(".stepper-input", parent).val();
        if (value == 1)
            return
        $(".stepper-input", parent).val(parseInt(value) - 1);
    });
    $(".up").click(function () {
        var parent = $(this).parents(".thumbnail-menu-modern");
        var value = $(".stepper-input", parent).val();
        $(".stepper-input", parent).val(parseInt(value) + 1);
    });
    $(".add-cart").click(function () {
        var cart = $.cookies.get('CART') || [];

        /*
         * 
         */
//        var count = $('.shop-cart .cart_count span');
        var product = $(this).parents(".thumbnail-menu-modern");
        var id = product.data("id");
        var qty = $(".stepper-input", product).val();
        var index = -1;
        $.each(cart, function (i, v) {
            if (v.id == id)
                index = i;
        });
        if (index != -1) {
            cart[index].qty = qty;
        } else {
            cart.push({id: id, qty: qty});
        }
        $.cookies.set('CART', cart);



        var cart = $('.fa-shopping-cart:visible').last();
        var imgtodrag = product.find("img").eq(0);
        if (imgtodrag) {
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

        var cart = $.cookies.get('CART') || [];
        if (!cart.length) {
            alert("Xin mời chọn sản phẩm");
            return false;
        }
        var cart = await $.ajax({
            url: path + "ajax/getcart",
            data: {data: JSON.stringify(cart)},
            dataType: "JSON",
            type: "POST"
        });
        var amount_product = formatCurrency(cart['amount_product'], 0, ",", ".");
        var details = cart['details'];
        $("#cart-tc").text(amount_product);
        $(".cart-list").empty()
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
        var cart = $.cookies.get('CART') || [];
        var amount_total = 0;
        $.each(cart, function (i, v) {
            if (v.id == id) {
                cart[i].qty = qty;
            }
            var item = $(".product-cart[data-id='" + v.id + "']");
            var item_price = item.data("price");
            var item_qty = cart[i].qty;
            var item_amount = item_qty * item_price;
            amount_total += item_amount;
            $(".product-cart[data-id='" + v.id + "'] .item-amount").text(formatCurrency(item_amount, 0, ",", "."));
        });

        $("#cart-tc").text(formatCurrency(amount_total, 0, ",", "."));
        $.cookies.set('CART', cart);
    });
    $(document).on("click", ".item-close", function () {
        var parent = $(this).parents(".product-cart");
        var id = parent.data("id");
        var cart = $.cookies.get('CART') || [];
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
            amount_total += item_amount;
            $(".product-cart[data-id='" + v.id + "'] .item-amount").text(formatCurrency(item_amount, 0, ",", "."));
        });
        if (index != -1) {
            cart.splice(index, 1);
        }
        $("#cart-tc").text(formatCurrency(amount_total, 0, ",", "."));
        $.cookies.set('CART', cart);
        parent.remove();
    })
    $(".finish-cart").click(function () {
        var cart = $.cookies.get('CART') || [];
        if (!cart.length) {
            alert("Xin mời chọn sản phẩm");
            return false;
        }
        var phone = $("#phone").val();

//        if (!phone) {
//            $("#phone").addClass("border-danger").focus();
//            alert("Xin mời nhập số điện thoại liên hệ");
//            return false;
//        }
        var note = $("#note").val();
        $.ajax({
            url: path + "ajax/order",
            data: {data: JSON.stringify(cart), phone: phone, note: note},
            dataType: "JSON",
            type: "POST",
            success: function (data) {
                alert("Đặt hàng thành công! Chúng tôi sẽ liên hệ với bạn sớm nhất.");
                location.reload()
            },
            error: function () {
                alert("Lỗi đặt hàng!");
            }
        });
    })
})
function formatCurrency(n, c, d, t) {
    var c = isNaN(c = Math.abs(c)) ? 2 : c,
            d = d == undefined ? "." : d,
            t = t == undefined ? "," : t,
            s = n < 0 ? "-" : "",
            i = String(parseInt(n = Math.abs(Number(n) || 0).toFixed(c))),
            j = (j = i.length) > 3 ? j % 3 : 0;

    return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
}
