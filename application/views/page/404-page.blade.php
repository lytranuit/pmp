@extends("layouts.template")

@section("title")
Website @parent
@stop
@section("content")
<section id="error" class="container text-center">
    <h1>404, Page not found</h1>
    <p>The Page you are looking for doesn't exist or an other error occurred.</p>
    <a class="btn btn-primary" href="{{base_url()}}">GO BACK TO THE HOMEPAGE</a>
</section><!--/#error-->
@stop
