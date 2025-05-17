<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Sakura Fest</title>
    <meta name="description" content="">
    <meta name="keywords" content="">
    <link rel="icon" href="{{ asset('favicon.png') }}" type="image/x-icon">
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/aos/aos.css') }}" rel="stylesheet">

    <!-- Main CSS File -->
    <link href="{{ asset('assets/css/main.css') }}" rel="stylesheet">
</head>

<body class="index-page">
    <header id="header" class="header d-flex align-items-center fixed-top">
        <div class="container-fluid container-xl position-relative d-flex align-items-center">
            <a href="{{ url('/') }}" class="logo d-flex align-items-center me-auto">
                <img src="{{ asset('assets/img/logo.png') }}" alt="Sakura Fest Logo">
            </a>

            <nav id="navmenu" class="navmenu">
                <ul>
                    <li><a href="#hero" class="active">Inicio</a></li>
                    <li><a href="#about">Nosotros</a></li>
                    <li><a href="#services">Servicios</a></li>
                    <li><a href="#events">Eventos</a></li>
                    <li><a href="#contact">Contactanos</a></li>
                </ul>
                <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
            </nav>
            @auth
                @php
                    $role = Auth::user()->role?->name; // Asegúrate de tener la relación role() definida en el modelo User
                @endphp

                @if ($role === 'admin')
                    <a href="{{ url('/dashboard') }}" class="cta-btn">
                        Dashboard
                    </a>
                @elseif ($role === 'emprendedor')
                    <a href="{{ url('/dashboard-emprendedor') }}" class="cta-btn">
                        Dashboard
                    </a>
                @endif
            @else
                <a href="{{ route('login') }}" class="cta-btn">
                    Log in
                </a>

                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="cta-btn">
                        Register
                    </a>
                @endif
            @endauth
        </div>
    </header>

    <main class="main">
        <!-- Hero Section -->
        <section id="hero" class="hero section dark-background">
            <img src="{{ asset('assets/img/hero-bg.jpg') }}" alt="" data-aos="fade-in">

            <div class="container d-flex flex-column align-items-center">
                <h2 data-aos="fade-up" data-aos-delay="100">Sakura Fest</h2>
                <p data-aos="fade-up" data-aos-delay="200">Posiciona tu marca en el corazón de Sakura Fest: ¡Reserva tu
                    stand hoy!</p>
                <div class="d-flex mt-4" data-aos="fade-up" data-aos-delay="300">
                    <a href="#about" class="btn-get-started">Reservar Stands</a>
                </div>
            </div>
        </section>

        <!-- Resto de las secciones (About, Stats, Services, etc.) -->
        @include('sections.about')
        @include('sections.stats')
        @include('sections.services')
        @include('sections.clients')
        @include('sections.features')
        @include('sections.events')
        @include('sections.contact')
    </main>

    <footer id="footer" class="footer dark-background">
        @include('sections.footer')
    </footer>

    <!-- Scroll Top -->
    <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>

    <!-- Preloader -->
    <div id="preloader"></div>

    <!-- Vendor JS Files -->
    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/aos/aos.js') }}"></script>
    <script src="{{ asset('assets/vendor/glightbox/js/glightbox.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/purecounter/purecounter_vanilla.js') }}"></script>
    <script src="{{ asset('assets/vendor/swiper/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/imagesloaded/imagesloaded.pkgd.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/isotope-layout/isotope.pkgd.min.js') }}"></script>

    <!-- Main JS File -->
    <script src="{{ asset('assets/js/main.js') }}"></script>
</body>

</html>
