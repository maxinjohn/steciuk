@extends('layouts.app')

@section('title', 'Forgot Password | ' . $siteName)
@section('description', 'Request a password reset link for your STECI UK Parish member account.')

@section('content')
    <x-page-intro
        title="Forgot password"
        subtitle="Request a secure link to reset your sign-in password"
        kicker="Member account"
        :show-strips="true"
    />

    <section class="page-section page-section--article py-10 sm:py-12 md:py-14">
        <div class="page-section-inner mx-auto max-w-xl">
            <div class="form-gen-z card-modern">
                @livewire('auth.forgot-password-form')
            </div>
        </div>
    </section>
@endsection
