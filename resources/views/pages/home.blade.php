@extends('layouts.app')

@section('title', \App\Support\Seo::documentTitle($page?->seo_title, null, $siteName))
@section('description', $page?->seo_description ?? $siteMotto)

@section('content')
    <x-page-shell
        :page="$page"
        :services="$services ?? collect()"
        :ministries="$ministries ?? collect()"
        :events="$events ?? collect()"
        :news="$news ?? collect()"
        :sermons="$sermons ?? collect()"
        :albums="$albums ?? collect()"
    />
@endsection
