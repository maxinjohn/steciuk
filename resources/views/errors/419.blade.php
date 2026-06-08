@extends('errors.layout')

@section('content')
    <div class="code">419</div>
    <h1>Session expired</h1>
    <p>Your session has expired. Please refresh the page and try again.</p>
    <a href="{{ url()->current() }}">Refresh page</a>
@endsection
