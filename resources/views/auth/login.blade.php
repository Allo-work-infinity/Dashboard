<!doctype html>
<html lang="en">
   <head>
      <!-- Required meta tags -->
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <title>Allo work infinity - Login </title>
      <!-- Favicon -->
      <link rel="shortcut icon" href="/images/favicon.ico" />
      <!-- Bootstrap CSS -->
      <link rel="stylesheet" href="/css/bootstrap.min.css">
      <!-- Typography CSS -->
      <link rel="stylesheet" href="/css/typography.css">
      <!-- Style CSS -->
      <link rel="stylesheet" href="/css/style.css">
      <!-- Responsive CSS -->
      <link rel="stylesheet" href="/css/responsive.css">
      <style>
        /* Right side panel */
                .sign-in-page-image {
                background: #000000;   /* your color */
                color: #fff;           /* optional: makes text readable */
                padding: 0;            /* avoid inner gaps */
                }
        </style>
   </head>
   <body>
      <!-- loader Start -->
     <div id="loading">
        <div id="loading-center"></div>
    </div>

        <section class="sign-in-page">
        <div id="container-inside">
            <div class="cube"></div><div class="cube"></div><div class="cube"></div><div class="cube"></div><div class="cube"></div>
        </div>

        <div class="container p-0">
            <div class="row no-gutters height-self-center">
            <div class="col-sm-12 align-self-center bg-primary rounded">
                <div class="row m-0">
                <div class="col-md-5 bg-white sign-in-page-data">
                    <div class="sign-in-from">

                    <h1 class="mb-0 text-center">Se connecter</h1>
                    <p class="text-center text-dark">Entrez votre adresse e-mail et votre mot de passe pour accéder au panneau d'administration.</p>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('status'))
                        <div class="alert alert-info">{{ session('status') }}</div>
                    @endif

                    <form method="POST" action="{{ url('/') }}" novalidate>
                        @csrf

                        <div class="form-group">
                        <label for="email">Adresse email</label>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            class="form-control mb-0 @error('email') is-invalid @enderror"
                            placeholder="Enter email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="email"
                            autofocus
                        >
                        @error('email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        </div>

                        <div class="form-group">
                        <label for="password">Mot de passe</label>

                        {{-- Show link only if the route exists --}}
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="float-right">Mot de passe oublié?</a>
                        @endif

                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="form-control mb-0 @error('password') is-invalid @enderror"
                            placeholder="Mot de passe"
                            required
                            autocomplete="current-password"
                        >
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        </div>

                        

                        <div class="sign-info text-center">
                        <button type="submit" class="btn btn-primary d-block w-100 mb-2">Sign in</button>
                        </div>
                    </form>

                    </div>
                </div>

                <div class="col-md-7 text-center sign-in-page-image">
                    <div class="sign-in-detail text-white">
                    {{-- <a class="sign-in-logo mb-5" href="#"><img src="/images/logo-full.png" class="img-fluid" alt="logo"></a> --}}
                    <div class="owl-carousel" data-autoplay="true" data-loop="true" data-nav="false" data-dots="true" data-items="1" data-items-laptop="1" data-items-tab="1" data-items-mobile="1" data-items-mobile-sm="1" data-margin="0">
                        <div class="item">
                        <img src="/images/login/logo.jpg" class="img-fluid mb-4" alt="slide 1">
                        {{-- <h4 class="mb-1 text-white">Find new friends</h4> --}}
                        {{-- <p>It is a long established fact that a reader will be distracted by the readable content.</p> --}}
                        </div>
                        {{-- <div class="item">
                        <img src="/images/login/1.png" class="img-fluid mb-4" alt="slide 2">
                        <h4 class="mb-1 text-white">Connect with the world</h4>
                        <p>It is a long established fact that a reader will be distracted by the readable content.</p>
                        </div>
                        <div class="item">
                        <img src="/images/login/1.png" class="img-fluid mb-4" alt="slide 3">
                        <h4 class="mb-1 text-white">Create new events</h4>
                        <p>It is a long established fact that a reader will be distracted by the readable content.</p>
                        </div> --}}
                    </div>
                    </div>
                </div>

                </div> <!-- /.row -->
            </div>
            </div>
        </div>
        </section>

        <!-- Sign in END -->
       
      <!-- Optional JavaScript -->
      <!-- jQuery first, then Popper.js, then Bootstrap JS -->
      <script src="/js/jquery.min.js"></script>
      <script src="/js/popper.min.js"></script>
      <script src="/js/bootstrap.min.js"></script>
      <!-- Appear JavaScript -->
      <script src="/js/jquery.appear.js"></script>
      <!-- Countdown JavaScript -->
      <script src="/js/countdown.min.js"></script>
      <!-- Counterup JavaScript -->
      <script src="/js/waypoints.min.js"></script>
      <script src="/js/jquery.counterup.min.js"></script>
      <!-- Wow JavaScript -->
      <script src="/js/wow.min.js"></script>
      <!-- Apexcharts JavaScript -->
      <script src="/js/apexcharts.js"></script>
      <!-- lottie JavaScript -->
      <script src="/js/lottie.js"></script>
      <!-- Slick JavaScript --> 
      <script src="/js/slick.min.js"></script>
      <!-- Select2 JavaScript -->
      <script src="/js/select2.min.js"></script>
      <!-- Owl Carousel JavaScript -->
      <script src="/js/owl.carousel.min.js"></script>
      <!-- Magnific Popup JavaScript -->
      <script src="/js/jquery.magnific-popup.min.js"></script>
      <!-- Smooth Scrollbar JavaScript -->
      <script src="/js/smooth-scrollbar.js"></script>
      <!-- Style Customizer -->
      <script src="/js/style-customizer.js"></script>
      <!-- Chart Custom JavaScript -->
      <script src="/js/chart-custom.js"></script>
      <!-- Custom JavaScript -->
      <script src="/js/custom.js"></script>
   </body>
</html>
