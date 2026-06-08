@extends('errors.layout')

@section('content')
    <div class="code">429</div>
    <h1>Too many requests</h1>
    <p>Please wait a moment before trying again.</p>
    <a href="{{ url('/') }}">Back to home</a>
@endsection
