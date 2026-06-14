@extends('layouts.app')

@section('title', \App\Support\Seo::documentTitle('Reset Password', null, $siteName))
@section('description', 'Choose a new password for your STECI UK Parish member account.')

@section('content')
    <x-hero
        title="Reset password"
        subtitle="Choose a new password for your parish account"
        eyebrow="Member account"
        badge="UK Parish"
        size="small"
        art-slug="reset-password"
        art-title="Reset password"
        art-content="Choose a new secure password for your parish member account"
    />
    <x-faith-page-bridge />
    <x-scripture-ribbon
        text="The Lord is near to all who call on him, to all who call on him in truth."
        reference="Psalm 145:18"
    />

    <section class="page-section page-section--article py-10 sm:py-12 md:py-14">
        <div class="page-section-inner mx-auto max-w-xl">
            <div class="form-gen-z card-modern">
                @livewire('auth.reset-password-form', ['token' => $token])
            </div>
        </div>
    </section>
@endsection
