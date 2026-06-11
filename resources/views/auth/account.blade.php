@extends('layouts.app')

@section('title', 'My Account | ' . $siteName)
@section('description', 'Manage your STECI UK Parish member account.')

@section('content')
    @php
        $user = auth()->user();
        $family = $user->family?->loadCount('members');
        $showFamilyTab = $user->canBelongToHousehold();

        $accountTabs = [
            ['id' => 'overview', 'label' => 'Overview'],
            ['id' => 'messages', 'label' => 'Messages'],
            ['id' => 'photo', 'label' => 'Photo'],
            ['id' => 'contact', 'label' => 'Contact'],
            ['id' => 'password', 'label' => 'Password'],
            ['id' => 'privacy', 'label' => 'Privacy'],
        ];

        if ($showFamilyTab) {
            $accountTabs[] = ['id' => 'family', 'label' => 'Family'];
        }

        $accountTabs[] = ['id' => 'giving', 'label' => 'Giving'];
        $accountTabs[] = ['id' => 'parish', 'label' => 'Parish life'];

        $unreadMessages = \App\Models\Conversation::query()
            ->where('user_id', $user->id)
            ->where('unread_by_member', true)
            ->count();

        $allowedTabIds = array_column($accountTabs, 'id');
        $initialTab = in_array(request('tab'), $allowedTabIds, true) ? request('tab') : 'overview';
    @endphp

    <section class="member-portal py-8 sm:py-12 md:py-14">
        <div class="member-portal-shell mx-auto w-full max-w-[90rem] px-4 sm:px-6 lg:px-8 xl:px-10">
            <div class="member-portal-hero" x-data="{ avatarUrl: @js($user->avatarUrl()) }" @avatar-updated.window="avatarUrl = $event.detail.url">
                <div class="member-portal-hero-main">
                    <div class="member-portal-avatar-wrap member-portal-avatar--xl">
                        <template x-if="avatarUrl">
                            <img
                                :src="avatarUrl"
                                alt=""
                                class="member-portal-avatar-img"
                                width="144"
                                height="144"
                                loading="eager"
                                decoding="async"
                                x-on:error="avatarUrl = null"
                            >
                        </template>
                        <span class="member-portal-avatar member-portal-avatar--xl" x-show="!avatarUrl" aria-hidden="true">{{ $user->initials() }}</span>
                    </div>
                    <div>
                        <p class="member-portal-kicker">Member portal</p>
                        <h1 class="member-portal-title">{{ $user->displayFullName() }}</h1>
                        <p class="member-portal-subtitle">
                            {{ $family?->name ? $family->memberPortalLabel().' · ' : '' }}{{ \App\Models\Role::labelForSlug($user->roleSlug()) }}
                            @if ($user->formattedPronouns())
                                · {{ $user->formattedPronouns() }}
                            @endif
                        </p>
                        <div class="member-portal-meta">
                            @if ($user->isMember())
                                <span @class([
                                    'member-status-badge',
                                    'member-status-badge--approved' => $user->isAccountApproved(),
                                    'member-status-badge--pending' => ! $user->isAccountApproved(),
                                ])>{{ $user->isAccountApproved() ? 'Active member' : 'Membership pending' }}</span>
                            @else
                                <span class="member-status-badge member-status-badge--approved">{{ \App\Models\Role::labelForSlug($user->roleSlug()) }}</span>
                            @endif
                            @if ($user->preferred_worship_location)
                                <span class="member-portal-chip">{{ $user->preferred_worship_location }}</span>
                            @endif
                            @if ($user->usesGravatar() && ! $user->hasUploadedProfilePhoto())
                                <span class="member-portal-chip member-portal-chip--soft">Gravatar</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="member-portal-hero-actions">
                    @if ($user->canAccessPanel(filament()->getCurrentOrDefaultPanel()))
                        <a href="{{ \App\Support\AdminPanelConfig::url() }}" class="btn btn-outline !text-sm">Admin panel</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline !text-sm">Sign out</button>
                    </form>
                </div>
            </div>

            <div class="member-portal-grid mt-8" x-data="{ tab: @js($initialTab) }">
                <nav class="member-portal-tabs" aria-label="Account sections">
                    @foreach ($accountTabs as $accountTab)
                        <button
                            type="button"
                            class="member-portal-tab"
                            :class="tab === @js($accountTab['id']) && 'is-active'"
                            @click="tab = @js($accountTab['id'])"
                        >
                            <span class="member-portal-tab__label">{{ $accountTab['label'] }}</span>
                            @if ($accountTab['id'] === 'messages' && $unreadMessages > 0)
                                <span class="member-portal-tab__badge" aria-label="{{ $unreadMessages }} unread">{{ $unreadMessages }}</span>
                            @endif
                        </button>
                    @endforeach
                </nav>

                <div class="member-portal-panels">
                    <div x-show="tab === 'overview'" x-cloak class="member-portal-panel-stack">
                        <div class="member-portal-card">
                            <h2 class="member-portal-panel-title">Your details</h2>
                            <dl class="member-portal-dl">
                                <div><dt>First name</dt><dd>{{ $user->displayFirstName() }}</dd></div>
                                @if ($user->displayLastName())
                                    <div><dt>Last name</dt><dd>{{ $user->displayLastName() }}</dd></div>
                                @endif
                                @if ($user->formattedPronouns())
                                    <div><dt>Pronouns</dt><dd>{{ $user->formattedPronouns() }}</dd></div>
                                @endif
                                <div><dt>Role</dt><dd>{{ \App\Models\Role::labelForSlug($user->roleSlug()) }}</dd></div>
                                @if ($family)
                                    <div><dt>Family household</dt><dd>{{ $family->memberPortalLabel() }} · {{ $family->members_count }} {{ str('member')->plural($family->members_count) }}</dd></div>
                                    @if ($user->familyRelationship())
                                        <div><dt>Your relationship</dt><dd>{{ $user->familyRelationship()->label() }}{{ $user->isFamilyAdmin() ? ' · Family administrator' : '' }}</dd></div>
                                    @endif
                                @endif
                                @if ($user->email)
                                    <div><dt>Email</dt><dd>{{ $user->email }}</dd></div>
                                @endif
                                @if ($user->phone)
                                    <div><dt>Phone</dt><dd>{{ $user->phone }}</dd></div>
                                @endif
                                @if ($user->date_of_birth)
                                    <div><dt>Date of birth</dt><dd>{{ $user->date_of_birth->format('j F Y') }}</dd></div>
                                @endif
                                @if ($user->formattedAddress())
                                    <div><dt>Address</dt><dd>{{ $user->formattedAddress() }}</dd></div>
                                @endif
                            </dl>
                        </div>

                        <div class="member-portal-quick-grid">
                            <a href="{{ url('/service-times') }}" class="member-portal-quick-card">
                                <span class="member-portal-quick-label">Worship</span>
                                <span class="member-portal-quick-desc">Service times across five UK locations</span>
                            </a>
                            <a href="{{ url('/prayer-request') }}" class="member-portal-quick-card">
                                <span class="member-portal-quick-label">Prayer</span>
                                <span class="member-portal-quick-desc">Submit a confidential prayer request</span>
                            </a>
                            <a href="{{ url('/events') }}" class="member-portal-quick-card">
                                <span class="member-portal-quick-label">Events</span>
                                <span class="member-portal-quick-desc">Parish calendar and gatherings</span>
                            </a>
                            <a href="{{ url('/resources') }}" class="member-portal-quick-card">
                                <span class="member-portal-quick-label">Resources</span>
                                <span class="member-portal-quick-desc">Liturgy, lectionary, and downloads</span>
                            </a>
                        </div>
                    </div>

                    <template x-if="tab === 'messages'">
                        <div>
                            @livewire('account.parish-messages-manager')
                        </div>
                    </template>

                    <template x-if="tab === 'photo'">
                        <div class="member-portal-card member-portal-card--profile">
                            <h2 class="member-portal-panel-title">Photo & identity</h2>
                            <p class="member-portal-panel-intro">Make your profile feel like you — upload, replace, or remove your photo any time.</p>
                            <div class="mt-6">
                                @livewire('account.profile-avatar-form')
                            </div>
                        </div>
                    </template>

                    <template x-if="tab === 'contact'">
                        <div class="member-portal-card">
                            <h2 class="member-portal-panel-title">Contact & address</h2>
                            <p class="member-portal-panel-intro">Keep your contact details and UK address up to date for parish communications.</p>
                            <div class="mt-6">
                                @livewire('account.profile-form')
                            </div>
                        </div>
                    </template>

                    <template x-if="tab === 'password'">
                        <div class="member-portal-card">
                            <h2 class="member-portal-panel-title">Password</h2>
                            <p class="member-portal-panel-intro">Change your sign-in password separately from your profile details.</p>
                            <div class="mt-6">
                                @livewire('account.profile-password-form')
                            </div>
                        </div>
                    </template>

                    <template x-if="tab === 'privacy'">
                        <div class="member-portal-card">
                            <h2 class="member-portal-panel-title">Privacy & data</h2>
                            <p class="member-portal-panel-intro">Exercise your UK data protection rights — download your data, manage marketing preferences, or request deletion.</p>
                            <div class="mt-6">
                                @livewire('account.profile-privacy-form')
                            </div>
                        </div>
                    </template>

                    @if ($showFamilyTab)
                        <template x-if="tab === 'family'">
                            <div>
                                @livewire('account.family-members-manager')
                            </div>
                        </template>
                    @endif

                    <template x-if="tab === 'giving'">
                        <div>
                            @livewire('account.donation-manager')
                        </div>
                    </template>

                    <div x-show="tab === 'parish'" x-cloak class="member-portal-panel-stack">
                        <div class="member-portal-card">
                            <h2 class="member-portal-panel-title">Parish life</h2>
                            <ul class="member-portal-link-list" role="list">
                                <li><a href="{{ url('/service-times') }}">Service times & locations</a></li>
                                <li><a href="{{ url('/online-worship') }}">Online worship</a></li>
                                <li><a href="{{ url('/events') }}">Events calendar</a></li>
                                <li><a href="{{ url('/news') }}">Parish news</a></li>
                                <li><a href="{{ url('/resources') }}">Resources & liturgy</a></li>
                                <li><a href="{{ route('give') }}">Support our parish (Give)</a></li>
                                <li><a href="{{ url('/prayer-request') }}">Prayer request</a></li>
                                <li><button type="button" class="member-portal-inline-link" @click="tab = 'messages'">Message the parish office</button></li>
                                <li><a href="{{ url('/contact') }}">Public contact form</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
