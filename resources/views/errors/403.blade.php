@extends('errors.layout')

@section('content')
    <div class="code">403</div>
    <h1>Access denied</h1>
    <p>You do not have permission to view this page.</p>
    <a href="{{ url('/') }}">Back to home</a>
@endsection
