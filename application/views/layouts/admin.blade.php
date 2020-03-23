<!doctype html>
<html>
    <head lang="en">
        @include("include.head")
    </head><!--/head-->

    <body>
        <!-- ============================================================== -->
        <!-- main wrapper -->
        <!-- ============================================================== -->
        <div class="app-container app-theme-white body-tabs-shadow fixed-sidebar fixed-header">
            <!-- ============================================================== -->
            <!-- header -->
            <!-- ============================================================== -->
            @include("include.header")
            <div class="app-main">
                @include("include.sidebar-left")
                <div class="app-main__outer">
                    <div class="app-main__inner">
                        @yield("content")
                    </div>
                </div>
            </div>

        </div>
    </body>
</html>