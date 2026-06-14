@extends('layouts.app')

@section('title', \App\Support\Seo::documentTitle('Create Account', null, $siteName))
@section('description', 'Register for a parish member account on the STECI UK Parish website.')

@section('content')
    <x-hero
        title="Create Your Parish Account"
        subtitle="Register as a member of the St. Thomas Evangelical Church of India – UK Parish"
        eyebrow="Parish membership · UK"
        badge="UK Parish"
        size="small"
        art-slug="register"
        art-title="Create Your Parish Account"
        art-content="Register and join the parish family as a new member"
    />
    <x-faith-page-bridge />
    <x-scripture-ribbon
        text="For it is by grace you have been saved, through faith — and this is not from yourselves, it is the gift of God."
        reference="Ephesians 2:8"
    />

    <section class="page-section page-section--article py-10 sm:py-12 md:py-14">
        <div class="page-section-inner mx-auto max-w-2xl">
            <div class="prose-church prose-church--page mb-8 text-center">
                <p>Create your own parish member account. After approval, you can add household members from the Family tab in your account — each person has their own profile.</p>
            </div>

            <div class="form-gen-z card-modern">
                @livewire('auth.register-form')
            </div>
        </div>
    </section>
@endsection
