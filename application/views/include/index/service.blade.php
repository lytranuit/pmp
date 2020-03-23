
<!-- Page-->
<div class="page text-center animated" style="animation-duration: 500ms;">
    <!-- Page Content-->
    <main class="page-content">
        <section class="pt-1 section-bottom-100 bg-gray-lighter" style="min-height: 1000px;">
            <div id='stsv-02'>  <h3 class="tde"><a href="/">Menu</a></h3></div>
            <!-- Actual search box -->
            <div class="container">
                <div class="row justify-content-center">
                    <div class="form-group has-search col-md-6 col-12">
                        <input type="text" class="form-control" placeholder="Search" id="search">
                    </div>
                </div>
                <div class="row justify-content-center">
                    <div class="col-12">
                        <button class="btn btn-success open_table">Mở bàn</button>
                    </div>
                </div>
            </div>

            <div class="responsive-tabs responsive-tabs-button responsive-tabs-horizontal responsive-tabs-carousel" style="display: block; width: 100%;">
                <ul class="resp-tabs-list">
                    @foreach($category as $row)
                    <li class="resp-tab-item" aria-controls="tab_item-{{$row['id']}}" role="tab">{{$row['name']}}</li>
                    @endforeach
                </ul>
                <div class="resp-tabs-container text-left">
                    @foreach($category as $row)

                    <div class="resp-tab-content" aria-labelledby="tab_item-{{$row['id']}}">
                        <div class="row">
                            @if(!empty($row['products']))
                            @foreach($row['products'] as $row1)
                            <div class="col-md-3 col-12 product-item" data-id='{{$row1['id']}}' data-price="{{$row1['price']}}" data-search="{{$row1['name']}}">
                                <div class="row">
                                    <p class="text-black font-weight-bold col-8">{{$row1['name']}} <span class="qty-product text-success"></span></p>

                                    <div class="group-sm show-hover col-4 pull-right" style="zoom: 0.6;">
                                        <div class="stepper-type-1 d-none">
                                            <div class="stepper "><input class="form-control stepper-input" type="number" data-zeros="true" value="1" min="1" max="20" readonly=""><span class="stepper-arrow up">+</span><span class="stepper-arrow down">-</span></div>
                                        </div>
                                        <a class="text-top btn btn-burnt-sienna btn-shape-circle add-cart" href="#"><span>Thêm vào</span></a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                            @endif
                        </div>
                    </div>
                    @endforeach

                </div>
            </div>
        </section>
    </main>

</div>


<div class='footer-fix'>
    <a class="btn btn-warning btn-xs table-cart" href="#" tabindex="-1" data-target='#table-modal' data-toggle='modal'>Bàn 1</a>
    <a class="btn btn-shape-circle btn-burnt-sienna offset-top-15 order-cart" href="#" tabindex="-1" data-target='#order-modal' data-toggle='modal'><i class="fas fa-shopping-cart"></i>Thanh toán</a>
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
<div aria-labelledby="table-modalLabel" class="modal fade" id="table-modal" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="table-modalLabel">
                    Chọn bàn
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
                            <a class="btn btn-warning btn-xs table_order" href="#" data-id="{{$row['id']}}" data-name="{{$row['name']}}">
                                {{$row['name']}}
                                <div class="table_amount"></div>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
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

<script type="text/javascript">
    var is_print_bill = <?php echo $is_print_bill; ?>;
    $(document).ready(function () {

    })
</script>