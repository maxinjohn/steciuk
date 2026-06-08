@extends('errors.layout')

@section('content')
    <div class="code">404</div>
    <h1>Page not found</h1>
    <p>The page you requested could not be found. It may have moved or no longer exists.</p>
    <a href="{{ url('/') }}">Back to home</a>
@endsection
