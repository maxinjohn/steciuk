@extends('errors.layout')

@section('content')
    <div class="code">503</div>
    <h1>Temporarily unavailable</h1>
    <p>We are performing maintenance. Please check back shortly.</p>
    <a href="{{ url('/') }}">Back to home</a>
@endsection
