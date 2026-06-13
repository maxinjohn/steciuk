@extends('layouts.app')

@section('title', 'Sign In | ' . $siteName)
@section('description', 'Sign in to your STECI UK Parish member account.')

@section('content')
    <x-page-intro
        title="Sign In"
        subtitle="Access your parish member account"
        kicker="Member account"
        scripture="The Lord is near to all who call on him, to all who call on him in truth."
        scripture-ref="Psalm 145:18"
        :show-strips="true"
    />

    <section class="page-section page-section--article py-10 sm:py-12 md:py-14">
        <div class="page-section-inner mx-auto max-w-xl">
            <div class="form-gen-z card-modern">
                @livewire('auth.login-form')
            </div>
        </div>
    </section>
@endsection
