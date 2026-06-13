@extends('layouts.app')

@section('title', 'Registration Received | ' . $siteName)
@section('description', 'Your STECI UK Parish registration is awaiting approval.')

@section('content')
    <section class="member-portal member-portal--pending py-16 sm:py-20">
        <div class="member-portal-shell mx-auto max-w-2xl">
            <div class="member-portal-card member-portal-card--hero text-center">
                <div class="member-portal-icon-ring mx-auto">
                    <svg class="h-8 w-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="member-portal-kicker">Registration received</p>
                <h1 class="member-portal-title">Awaiting parish approval</h1>
                <p class="member-portal-lead mt-4">
                    Thank you for registering with the St. Thomas Evangelical Church of India – UK Parish.
                    A member of our leadership team will review your details before your account is activated.
                </p>
                <div class="member-portal-status mt-8">
                    <span class="member-status-badge member-status-badge--pending">Pending approval</span>
                </div>
                <ul class="member-portal-checklist mt-8 text-left" role="list">
                    <li>We review every registration to protect our parish family online.</li>
                    <li>You will be able to sign in once your account has been approved.</li>
                    <li>Once approved, sign in and open the Family tab in your account to add household members.</li>
                    <li>Need help sooner? <a href="{{ url('/contact') }}" class="text-brand hover:underline">Contact the parish office</a>.</li>
                </ul>
                <div class="mt-10 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('home') }}" class="btn btn-primary">Return home</a>
                    <a href="{{ route('login') }}" class="btn btn-outline">Sign in</a>
                </div>
            </div>
        </div>
    </section>
@endsection
