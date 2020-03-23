<div class="container">
    <div class="row">
        @foreach($table as $row)
        <div class="col-md-3 col-4 my-4 text-center">
            <a class="btn btn-warning btn-xs table_order" href="#" data-id="{{$row['id']}}" data-name="{{$row['name']}}">
                {{$row['name']}}
                <div class="table_amount"></div>
            </a>
        </div>
        @endforeach
    </div>
</div>

<div class='footer-fix'>
    <a class="btn btn-info btn-xs text-white" href="/index/service"><i class="fas fa-book"></i> <span class="d-none d-md-inline-block">Gọi món</span></a>
    <a class="btn btn-danger offset-top-15 order-cart" href="#" tabindex="-1" data-target='#order-modal' data-toggle='modal'><i class="fas fa-shopping-cart"></i> Thanh toán</a>
    <a class="btn btn-info btn-xs text-white" href="#" tabindex="-1" data-target='#table-exchange-modal' data-toggle='modal'><i class="fas fa-exchange-alt"></i> <span class="d-none d-md-inline-block">Chuyển bàn</span></a>
</div>
<div aria-labelledby="order-modalLabel" class="modal fade" id="order-modal" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="order-modalLabel">
                    Giỏ hàng           
                </h5>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                    <span aria-hidden="true">
                        x
                    </span>
                </button>
            </div>
            <div class="modal-body">
                <ul class="list-unstyled cart-list">

                </ul>
                <div class="cart-info text-right text-danger">

                    <h4>Tổng cộng: <span id='cart-tc'></span> đ</h4>
                </div>
            </div>
            <div class="modal-footer">
                <a class="btn btn-shape-circle btn-burnt-sienna offset-top-15 finish-cart" href="#" tabindex="-1" data-target='#order-modal' data-toggle='modal'>Thanh toán</a>
            </div>
        </div>
    </div>
</div>
<ul class="d-none" id="template">
    <li class="product-cart">
        <div class="cart-item">
            <div class="item-text dish-list-text">
                <h4><a href="#" class="item-name">Breakfast-3</a></h4>
                <h5>Qty: <input type="number" class="form-control item-qty" min="1" value="1"> x <span class="item-price"></span> đ</h5>
            </div><!-- end item-text -->

            <!--            <div class="item-img">
                            <a href="#"><img src="images/dish-breakfast-3.png" class="img-fluid item-image" alt="cart-item-img"></a>
                        </div> end item-img -->

            <h4 class="total">Tổng: <span class="item-amount">$45</span> đ</h4>

            <div class="item-close">
                <button class="btn"><span><i class="fa fa-times-circle"></i></span></button>
            </div><!-- end item-close -->
        </div><!-- end cart-item -->
    </li>
</ul>
<div aria-labelledby="table-modalLabel" class="modal fade" id="table-exchange-modal" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="table-modalLabel">
                    Chuyển bàn
                </h5>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                    <span aria-hidden="true">
                        x
                    </span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row">
                        @foreach($table as $row)
                        <div class="col-md-3 col-4 my-4">
                            <button class="btn btn-warning btn-xs table_exchange" href="#" data-id="{{$row['id']}}" data-name="{{$row['name']}}">
                                {{$row['name']}}
                            </button>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    var is_print_bill = <?php echo $is_print_bill; ?>;
    $(document).ready(function () {

    })
</script>