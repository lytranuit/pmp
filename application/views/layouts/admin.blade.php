<!doctype html>
<html>

<head lang="en">
    @include("include.head")
</head>
<!--/head-->

<body>
    <div class="page-loader-wrapper" style="display: none; opacity: 0.5;">
        <div class="loader">
            <div class="spinner-border"></div>
            <p>Please wait...</p>
        </div>
    </div>
    <!-- ============================================================== -->
    <!-- main wrapper -->
    <!-- ============================================================== -->
    <div class="app-container app-theme-white body-tabs-shadow fixed-sidebar fixed-header closed-sidebar">
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