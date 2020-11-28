<div class="row clearfix mt-3">
    <div class="col-12">
        <section class="wizard-card card card-fluid">
            <div class="card-body">
                <div class="float-right">
                    <button class="btn btn-success reload"><i class="fas fa-sync-alt"></i></button>
                </div>
                <div class="tree">

                </div>
            </div>
        </section>
    </div>
</div>

<script>
    $(document).ready(function() {
        $(document).off("click", ".tree li").on("click", ".tree li", function(e) {
            e.preventDefault();
            e.stopPropagation();
            let ul = $(">ul", $(this));
            ul.toggleClass("d-none");
        })

        position_tree();
        $(".reload").click(function() {
            position_tree(1);
        });
    })
    
    function position_tree(is_reload = 0) {
        $(".tree").html("<div class='text-center'><i class='fas fa-sync-alt fa-spin'></i></div>");
        $.ajax({
            url: path + "ajax/position_tree",
            data: {
                is_reload: is_reload
            },
            dataType: "Json",
            success: function(data) {
                $(".tree").empty();
                let datasource =  {
                    'title': '{{lang("position_diagram")}}',
                    'name':null,
                    'children': data    
                    }
                var oc = $('.tree').orgchart({
                'data' : datasource,
                "parentNodeSymbol":false,
                "nodeTitle":"title",
                "nodeContent":"name",
                'visibleLevel':2,
                'direction': 'l2r',
                'createNode': function($node, data) {
                    $node.on('click', function(event) {
                        var $selected = $('.tree').find('.node.focused');
                        $selected.parents('.nodes').children(':has(.focused)').find('.node:first').each(function(index, superior) {
                            //console.log(superior);
                            if (!$(superior).find('.horizontalEdge:first').closest('table').parent().siblings().is('.hidden')) {
                                $(superior).find('.horizontalEdge:first').trigger('click');
                            }
                        });
                        $(this).prop('disabled', true);
                    });
                }
                });
                oc.$chart.on('init.orgchart', function(e) {
                    $(e.target).find('.node:first .content').remove();
                });
            }
        })
    }
</script>