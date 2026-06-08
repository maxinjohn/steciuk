@extends('layouts.app')

@section('title', $page?->seo_title ?? $page?->title ?? 'Page')
@section('description', $page?->seo_description ?? $siteMotto)

@section('content')
    <x-page-shell :page="$page" />
@endsection
