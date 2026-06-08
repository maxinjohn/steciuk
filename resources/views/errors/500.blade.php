@extends('errors.layout')

@section('content')
    <div class="code">{{ $status ?? 500 }}</div>
    <h1>Something went wrong</h1>
    <p>We could not complete your request. Our team has been notified.</p>
    <a href="{{ url('/') }}">Back to home</a>
@endsection
