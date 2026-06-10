@extends('layouts.app')

@section('title', 'Reset Password | ' . $siteName)
@section('description', 'Choose a new password for your STECI UK Parish member account.')

@section('content')
    <x-page-intro
        title="Reset password"
        subtitle="Choose a new password for your parish account"
        kicker="Member account"
        :show-strips="true"
    />

    <section class="page-section page-section--article py-10 sm:py-12 md:py-14">
        <div class="page-section-inner mx-auto max-w-xl px-4 sm:px-6 lg:px-8">
            <div class="form-gen-z card-modern">
                @livewire('auth.reset-password-form', ['token' => $token])
            </div>
        </div>
    </section>
@endsection
