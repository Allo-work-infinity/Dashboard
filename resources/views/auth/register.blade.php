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
   </head>
   <body>
      <!-- loader Start -->
        <div id="loading">
        <div id="loading-center"></div>
        </div>
        <!-- loader END -->

        <!-- Register Start -->
        <section class="sign-in-page">
        <div id="container-inside">
            <div class="cube"></div>
            <div class="cube"></div>
            <div class="cube"></div>
            <div class="cube"></div>
            <div class="cube"></div>
        </div>

        <div class="container p-0">
            <div class="row no-gutters height-self-center">
            <div class="col-sm-12 align-self-center bg-primary rounded">
                <div class="row m-0">

                <div class="col-md-5 bg-white sign-in-page-data">
                    <div class="sign-in-from">

                    <h1 class="mb-0 text-center">Create account</h1>
                    <p class="text-center text-dark">Fill in the details below to create your account.</p>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('status'))
                        <div class="alert alert-info">{{ session('status') }}</div>
                    @endif

                    <form method="POST" action="{{ route('register') }}" novalidate>
                        @csrf

                        <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="first_name">First name</label>
                            <input
                            type="text"
                            name="first_name"
                            id="first_name"
                            class="form-control mb-0 @error('first_name') is-invalid @enderror"
                            placeholder="First name"
                            value="{{ old('first_name') }}"
                            required
                            autocomplete="given-name"
                            >
                            @error('first_name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group col-md-6">
                            <label for="last_name">Last name</label>
                            <input
                            type="text"
                            name="last_name"
                            id="last_name"
                            class="form-control mb-0 @error('last_name') is-invalid @enderror"
                            placeholder="Last name"
                            value="{{ old('last_name') }}"
                            required
                            autocomplete="family-name"
                            >
                            @error('last_name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        </div>

                        <div class="form-group">
                        <label for="email">Email address</label>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            class="form-control mb-0 @error('email') is-invalid @enderror"
                            placeholder="Enter email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="email"
                        >
                        @error('email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        </div>

                        <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="password">Password</label>
                            <input
                            type="password"
                            name="password"
                            id="password"
                            class="form-control mb-0 @error('password') is-invalid @enderror"
                            placeholder="Minimum 8 characters"
                            required
                            autocomplete="new-password"
                            >
                            @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group col-md-6">
                            <label for="password_confirmation">Confirm password</label>
                            <input
                            type="password"
                            name="password_confirmation"
                            id="password_confirmation"
                            class="form-control mb-0"
                            placeholder="Re-enter password"
                            required
                            autocomplete="new-password"
                            >
                        </div>
                        </div>

                        <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="phone">Phone (optional)</label>
                            <input
                            type="text"
                            name="phone"
                            id="phone"
                            class="form-control mb-0 @error('phone') is-invalid @enderror"
                            placeholder="e.g. +216 12 345 678"
                            value="{{ old('phone') }}"
                            autocomplete="tel"
                            >
                            @error('phone')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group col-md-6">
                            <label for="city">City (optional)</label>
                            <input
                            type="text"
                            name="city"
                            id="city"
                            class="form-control mb-0 @error('city') is-invalid @enderror"
                            placeholder="City"
                            value="{{ old('city') }}"
                            autocomplete="address-level2"
                            >
                            @error('city')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        </div>

                        <div class="form-group">
                        <label for="governorate">Governorate (optional)</label>
                        <input
                            type="text"
                            name="governorate"
                            id="governorate"
                            class="form-control mb-0 @error('governorate') is-invalid @enderror"
                            placeholder="Governorate"
                            value="{{ old('governorate') }}"
                            autocomplete="address-level1"
                        >
                        @error('governorate')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        </div>

                        <div class="sign-info text-center">
                        <button type="submit" class="btn btn-primary d-block w-100 mb-2">Create account</button>
                        <small class="d-block">
                            Already have an account?
                            <a href="{{ route('login') }}">Sign in</a>
                        </small>
                        </div>
                    </form>

                    </div>
                </div>

                <div class="col-md-7 text-center sign-in-page-image">
                    <div class="sign-in-detail text-white">
                    <a class="sign-in-logo mb-5" href="#"><img src="/images/logo-full.png" class="img-fluid" alt="logo"></a>
                    <div class="owl-carousel" data-autoplay="true" data-loop="true" data-nav="false" data-dots="true" data-items="1" data-items-laptop="1" data-items-tab="1" data-items-mobile="1" data-items-mobile-sm="1" data-margin="0">
                        <div class="item">
                        <img src="/images/login/1.png" class="img-fluid mb-4" alt="slide 1">
                        <h4 class="mb-1 text-white">Join the community</h4>
                        <p>Create your account and get started in minutes.</p>
                        </div>
                        <div class="item">
                        <img src="/images/login/1.png" class="img-fluid mb-4" alt="slide 2">
                        <h4 class="mb-1 text-white">Discover opportunities</h4>
                        <p>Find roles, companies, and subscriptions tailored to you.</p>
                        </div>
                        <div class="item">
                        <img src="/images/login/1.png" class="img-fluid mb-4" alt="slide 3">
                        <h4 class="mb-1 text-white">Stay connected</h4>
                        <p>Track applications and manage your profile with ease.</p>
                        </div>
                    </div>
                    </div>
                </div>

                </div><!-- /.row -->
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
