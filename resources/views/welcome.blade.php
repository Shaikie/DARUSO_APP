<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#10152b">
    <meta name="description" content="The digital home for DARUSO student communication, representation and campus community.">
    <title>DARUSO · Your campus, connected</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="daruso-landing-body">
    <div class="landing-orb landing-orb-one" aria-hidden="true"></div>
    <div class="landing-orb landing-orb-two" aria-hidden="true"></div>

    <header class="landing-header">
        <div class="container">
            <nav class="landing-nav" aria-label="Main navigation">
                <a href="{{ route('home') }}" class="landing-brand">
                    <span class="landing-brand-mark"><i class="bi bi-broadcast-pin"></i></span>
                    <span><strong>DARUSO</strong><small>Student community</small></span>
                </a>

                <div class="landing-nav-links d-none d-lg-flex">
                    <a href="#platform">Platform</a>
                    <a href="#features">What you can do</a>
                    <a href="#how-it-works">Getting started</a>
                </div>

                <div class="landing-nav-actions">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-light landing-nav-cta">Open workspace <i class="bi bi-arrow-up-right ms-1"></i></a>
                    @else
                        <a href="{{ route('login') }}" class="landing-signin">Sign in</a>
                        <a href="{{ route('register') }}" class="btn btn-light landing-nav-cta">Get started <i class="bi bi-arrow-up-right ms-1"></i></a>
                    @endauth
                </div>
            </nav>
        </div>
    </header>

    <main>
        <section class="landing-hero" id="platform">
            <div class="container">
                <div class="row align-items-center g-5">
                    <div class="col-12 col-lg-6">
                        <div class="landing-eyebrow"><span></span> THE DARUSO DIGITAL COMMUNITY</div>
                        <h1>Your campus.<br><span>Your voice.</span><br>One connected space.</h1>
                        <p class="landing-hero-copy">
                            Stay close to what matters at the University of Dar es Salaam.
                            Get official updates, join the conversation, follow events and reach your representatives.
                        </p>
                        <div class="landing-hero-actions">
                            @auth
                                <a href="{{ route('dashboard') }}" class="btn landing-btn-primary">Go to your workspace <i class="bi bi-arrow-up-right ms-2"></i></a>
                            @else
                                <a href="{{ route('register') }}" class="btn landing-btn-primary">Create your account <i class="bi bi-arrow-up-right ms-2"></i></a>
                                <a href="{{ route('login') }}" class="btn landing-btn-ghost">I already have an account</a>
                            @endauth
                        </div>
                        <div class="landing-trust-line">
                            <span><i class="bi bi-shield-check"></i> Official student communication</span>
                            <span><i class="bi bi-people"></i> Built around student voices</span>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="landing-preview-wrap">
                            <div class="landing-preview-glow" aria-hidden="true"></div>
                            <div class="landing-preview">
                                <div class="landing-preview-top">
                                    <div class="landing-preview-brand"><span><i class="bi bi-broadcast-pin"></i></span><strong>DARUSO</strong></div>
                                    <div class="landing-preview-dots"><i></i><i></i><i></i></div>
                                </div>
                                <div class="landing-preview-welcome">
                                    <small>YOUR STUDENT SPACE</small>
                                    <strong>Good things happen<br>when we're connected.</strong>
                                    <span>Updates, conversations and opportunities in one place.</span>
                                </div>
                                <div class="landing-preview-grid">
                                    <div class="landing-preview-tile tile-purple"><span><i class="bi bi-megaphone"></i></span><strong>Announcements</strong><small>Official updates</small></div>
                                    <div class="landing-preview-tile tile-teal"><span><i class="bi bi-chat-square-text"></i></span><strong>Community</strong><small>Share and connect</small></div>
                                    <div class="landing-preview-tile tile-orange"><span><i class="bi bi-calendar-event"></i></span><strong>Events</strong><small>What's coming up</small></div>
                                    <div class="landing-preview-tile tile-blue"><span><i class="bi bi-life-preserver"></i></span><strong>Student support</strong><small>Make your voice heard</small></div>
                                </div>
                                <div class="landing-preview-bottom"><span class="landing-live-dot"></span> Your campus, in the loop <i class="bi bi-arrow-up-right ms-auto"></i></div>
                            </div>

                            <div class="landing-float-card landing-float-top"><span><i class="bi bi-bell-fill"></i></span><div><strong>Stay informed</strong><small>Important updates, on time</small></div></div>
                            <div class="landing-float-card landing-float-bottom"><span><i class="bi bi-people-fill"></i></span><div><strong>One community</strong><small>Every voice has a place</small></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="landing-proof">
            <div class="container">
                <div class="landing-proof-inner">
                    <div><strong>One place</strong><span>for important information</span></div>
                    <div><strong>One community</strong><span>for student connection</span></div>
                    <div><strong>One voice</strong><span>for better representation</span></div>
                </div>
            </div>
        </section>

        <section class="landing-section" id="features">
            <div class="container">
                <div class="landing-section-heading">
                    <span class="landing-section-kicker">MADE FOR CAMPUS LIFE</span>
                    <h2>Everything important,<br><span>closer to you.</span></h2>
                    <p>Less searching through scattered messages. More clarity on what's happening and how to take part.</p>
                </div>

                <div class="row g-3 g-lg-4">
                    <div class="col-12 col-md-6 col-xl-4">
                        <article class="landing-feature-card">
                            <span class="landing-feature-icon feature-purple"><i class="bi bi-megaphone"></i></span>
                            <h3>Official announcements</h3>
                            <p>Find leadership notices and important updates in one reliable place.</p>
                            <span class="landing-feature-index">01</span>
                        </article>
                    </div>
                    <div class="col-12 col-md-6 col-xl-4">
                        <article class="landing-feature-card">
                            <span class="landing-feature-icon feature-teal"><i class="bi bi-chat-square-heart"></i></span>
                            <h3>Student community</h3>
                            <p>Read stories, share ideas and take part in conversations with the community.</p>
                            <span class="landing-feature-index">02</span>
                        </article>
                    </div>
                    <div class="col-12 col-md-6 col-xl-4">
                        <article class="landing-feature-card">
                            <span class="landing-feature-icon feature-orange"><i class="bi bi-calendar2-week"></i></span>
                            <h3>Events &amp; meetings</h3>
                            <p>Keep track of schedules, venues, meeting details and upcoming activities.</p>
                            <span class="landing-feature-index">03</span>
                        </article>
                    </div>
                    <div class="col-12 col-md-6 col-xl-4">
                        <article class="landing-feature-card">
                            <span class="landing-feature-icon feature-blue"><i class="bi bi-life-preserver"></i></span>
                            <h3>Student support</h3>
                            <p>Submit complaints privately and follow their progress through the right channels.</p>
                            <span class="landing-feature-index">04</span>
                        </article>
                    </div>
                    <div class="col-12 col-md-6 col-xl-4">
                        <article class="landing-feature-card">
                            <span class="landing-feature-icon feature-pink"><i class="bi bi-folder2-open"></i></span>
                            <h3>Shared documents</h3>
                            <p>Access useful documents, policies, minutes and resources based on your access.</p>
                            <span class="landing-feature-index">05</span>
                        </article>
                    </div>
                    <div class="col-12 col-md-6 col-xl-4">
                        <article class="landing-feature-card">
                            <span class="landing-feature-icon feature-gold"><i class="bi bi-person-badge"></i></span>
                            <h3>Know your representatives</h3>
                            <p>Find the student leaders and representatives serving your community.</p>
                            <span class="landing-feature-index">06</span>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <section class="landing-how" id="how-it-works">
            <div class="container">
                <div class="row align-items-center g-5">
                    <div class="col-12 col-lg-5">
                        <span class="landing-section-kicker">A SIMPLE START</span>
                        <h2>Get connected<br>in a few steps.</h2>
                        <p>Create your account, confirm your email and complete your student details. Once your student record is verified, you'll be ready to use the student services available to you.</p>
                        <a href="{{ route('register') }}" class="landing-how-link">Create your account <i class="bi bi-arrow-up-right"></i></a>
                    </div>
                    <div class="col-12 col-lg-7">
                        <div class="landing-steps">
                            <div class="landing-step"><span>01</span><div><strong>Create an account</strong><p>Register with your name, email address and a secure password.</p></div><i class="bi bi-person-plus"></i></div>
                            <div class="landing-step"><span>02</span><div><strong>Verify your email</strong><p>Confirm that you control the email address linked to your account.</p></div><i class="bi bi-envelope-check"></i></div>
                            <div class="landing-step"><span>03</span><div><strong>Complete your student profile</strong><p>Submit your academic details for the appropriate verification process.</p></div><i class="bi bi-patch-check"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="landing-final-cta">
            <div class="container">
                <div class="landing-cta-card">
                    <div><span class="landing-section-kicker">YOUR CAMPUS IS TALKING</span><h2>Be part of the conversation.</h2><p>Stay informed. Stay involved. Make your student experience count.</p></div>
                    <div class="landing-cta-actions">
                        @auth
                            <a href="{{ route('dashboard') }}" class="btn landing-btn-primary">Open DARUSO <i class="bi bi-arrow-up-right ms-2"></i></a>
                        @else
                            <a href="{{ route('register') }}" class="btn landing-btn-primary">Join the community <i class="bi bi-arrow-up-right ms-2"></i></a>
                            <a href="{{ route('login') }}" class="landing-cta-login">Sign in instead</a>
                        @endauth
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="landing-footer">
        <div class="container">
            <a href="{{ route('home') }}" class="landing-brand"><span class="landing-brand-mark"><i class="bi bi-broadcast-pin"></i></span><span><strong>DARUSO</strong><small>Student community</small></span></a>
            <p>© {{ now()->year }} DARUSO Students Organisation. Built for a more connected student community.</p>
            <a href="#platform" class="landing-back-top">Back to top <i class="bi bi-arrow-up"></i></a>
        </div>
    </footer>
</body>
</html>
