<!DOCTYPE html>
<html lang="en">
    <head>
        @include("include.head")
    </head>
    <body class="homepage">
        @include("include.header")
        <section class='{{$func or ""}}'>
            <div class="container">
                <div class="row">
                    <div class="col-md-3">
                        <div class="row">
                            @yield("left-side")
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="row">
                            @yield("content")
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @include("include.footer")
    </body>
</html>