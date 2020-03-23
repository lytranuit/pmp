<!DOCTYPE html>
<html lang="en">
    <head>
        @include("include.head")
    </head><!--/head-->

    <body class='{{$template or ""}}'>
        @include("include.header")
        <section>
            <div class="container">
                <div class="row">
                    @yield("content")
                </div>
            </div>
        </section>
        @include("include.footer")
    </body>
</html>