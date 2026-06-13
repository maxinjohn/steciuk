@extends('layouts.app')

@section('title', 'Forgot Password | ' . $siteName)
@section('description', 'Request a password reset link for your STECI UK Parish member account.')

@section('content')
    <x-hero
        title="Forgot password"
        subtitle="Request a secure link to reset your sign-in password"
        eyebrow="Member account"
        badge="UK Parish"
        size="small"
        art-slug="forgot-password"
        art-title="Forgot password"
        art-content="Reset your parish member account sign-in password"
    />
    <x-parish-action-strip class="parish-action-strip--compact" />

    <section class="page-section page-section--article py-10 sm:py-12 md:py-14">
        <div class="page-section-inner mx-auto max-w-xl">
            <div class="form-gen-z card-modern">
                @livewire('auth.forgot-password-form')
            </div>
        </div>
    </section>
@endsection
