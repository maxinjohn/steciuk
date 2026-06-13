@extends('layouts.app')

@section('title', 'Sign In | ' . $siteName)
@section('description', 'Sign in to your STECI UK Parish member account.')

@section('content')
    <x-hero
        title="Sign In"
        subtitle="Access your parish member account"
        eyebrow="Member account"
        badge="UK Parish"
        size="small"
        art-slug="login"
        art-title="Sign In"
        art-content="Sign in to your parish member account and member portal"
    />
    <x-parish-action-strip class="parish-action-strip--compact" />
    <x-scripture-ribbon
        text="The Lord is near to all who call on him, to all who call on him in truth."
        reference="Psalm 145:18"
    />

    <section class="page-section page-section--article py-10 sm:py-12 md:py-14">
        <div class="page-section-inner mx-auto max-w-xl">
            <div class="form-gen-z card-modern">
                @livewire('auth.login-form')
            </div>
        </div>
    </section>
@endsection
