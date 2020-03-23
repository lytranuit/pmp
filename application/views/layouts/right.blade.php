<!DOCTYPE html>
<html lang="en">
    <head>
        @include("include.head")
    </head><!--/head-->

    <body class="homepage">
        @include("include.header")
        <section class='{{$func or ""}}'>
            <div class="container">
                <div class="row">
                    <div class="col-md-8">
                        @yield("content")
                    </div>
                    <div class="col-md-4">
                        @yield("right-side")
                    </div>
                </div>
            </div>
        </section>
        @include("include.footer")
    </body>
</html>