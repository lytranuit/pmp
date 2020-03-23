<div class="row clearfix">
    <div class="col-12">
        <form method="POST" action="" id="form-dang-tin">
            <input type="hidden" name="user_id" value="0" />
            <section class="card card-fluid">
                <h5 class="card-header">
                    Mở bàn
                    <button class="btn btn-sm btn-primary float-right save">Save</button>
                </h5>
                <div class="card-body">
                    <div class="row">
                        @if(!empty($products))
                        @foreach($products as $row1)
                        <div class="col-md-3 col-12 product-item" data-id='{{$row1['id']}}' data-quantity="0">
                            <div class="row">
                                <p class="text-black font-weight-bold col-8">{{$row1['name']}} <span class="quantity-product text-success"></span></p>

                                <div class="group-sm show-hover col-4 pull-right" style="zoom: 0.7;">
                                    <a class="btn btn-danger rounded add-cart" href="#"><span>Thêm vào</span></a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        @endif
                    </div>
                </div>
            </section>
        </form>
    </div>
</div>
<script type='text/javascript'>
    $(document).ready(function () {
        get_open_cart();
        $(".add-cart").click(function () {
            var product = $(this).parents(".product-item");
            var quantity = parseInt(product.data("quantity"));
            quantity++;
            $(".quantity-product", product).text("(" + quantity + ")");
            product.attr("data-quantity", quantity).data("quantity", quantity);
            return false;
        });
        $(".save").click(function () {
            let data = [];
            $(".product-item").each(function () {
                let product_id = $(this).data("id");
                let quantity = $(this).data("quantity");
                if (quantity == 0)
                    return;
                data.push({product_id: product_id, quantity: quantity});
//                data = [...data, ];
            });
//            console.log(data);
            $.ajax({
                url: path + "admin/saveopentable",
                data: {data: JSON.stringify(data)},
                dataType: "JSON",
                type: "POST"
            });
            return false;
        });

        $(".quantity-product").click(function () {
            var parent = $(this).parents(".product-item");
            $(".quantity-product", parent).text("");
            parent.attr("data-quantity", 0).data("quantity", 0);
        });
    });
    async function get_open_cart() {
        var cart = await $.ajax({
            url: path + "ajax/getopencart",
            data: {},
            dataType: "JSON",
            type: "GET"
        });
        $.each(cart, function (k, v) {
            let id = v['product_id'];
            let quantity = v['quantity'];
            let product = $(".product-item[data-id=" + id + "]");
            $(".quantity-product", product).text("(" + quantity + ")");
            product.attr("data-quantity", quantity).data("quantity", quantity);
        })
    }
</script>