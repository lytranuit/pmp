
<!-- ============================================================== -->
<!-- pageheader -->
<!-- ============================================================== -->
<div class="row">
    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
        <div class="page-header">
            <h2 class="pageheader-title">Danh mục</h2>
            <p class="pageheader-text"></p>
            <div class="page-breadcrumb">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#" class="breadcrumb-link">Trang chủ</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Danh mục</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>
<div class="row clearfix">
    <div class="col-12">
        <section class="card card-fluid">
            <h5 class="card-header drag-handle">
                <a class="btn btn-sm btn-success" href="{{base_url()}}admin/themcategory">Thêm mới</a>
                <a class="btn btn-sm btn-primary float-right" id='save' href="#">Save</a>
            </h5>
            <div class="card-body">
                <div class="dd" id="nestable2">
                    <?= $html_nestable ?>
                </div>
            </div>
        </section>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $('#nestable').nestedSortable({
            forcePlaceholderSize: true,
            items: 'li',
            opacity: .6,
            placeholder: 'dd-placeholder',
        });
        $("#save").click(function () {
            var arraied = $('#nestable').nestedSortable('toArray', {excludeRoot: true});
            $.each(arraied, function (k, v) {
                var id = v['id'];
                var is_home = +$("#show" + id).is(":checked");
                var is_menu = +$("#switch" + id).is(":checked");
                arraied[k]['is_home'] = is_home;
                arraied[k]['is_menu'] = is_menu;
            });

            $.ajax({
                type: "POST",
                data: {data: JSON.stringify(arraied)},
                url: path + "ajax/saveordercategory",
                success: function (msg) {
                    alert("Success!");
                }
            })
        });
        $(document).off("click", ".dd-item-delete").on("click", ".dd-item-delete", async function () {
            var parent = $(this).closest(".dd-item");
            var id = parent.data("id");
            var array = [id];
            $(".dd-item", parent).each(function () {
                var id = $(this).data("id");
                array.push(id);
            });
            var r = confirm("Delete it?");
            if (r == true) {
                var promiseAll = [];
                for (var i = 0; i < array.length; i++) {
                    var id = array[i]
                    var promise = $.ajax({
                        type: "POST",
                        data: {data: JSON.stringify({id: id, deleted: 1})},
                        url: path + "ajax/savecategory"
                    })
                    promiseAll.push(promise);
                }
                await Promise.all(promiseAll);
                location.reload();
            }
        })
    });
</script>