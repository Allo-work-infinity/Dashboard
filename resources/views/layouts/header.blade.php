<div class="iq-top-navbar">
  <div class="iq-navbar-custom">
    <nav class="navbar navbar-expand-lg navbar-light p-0">

      <div class="iq-menu-bt d-flex align-items-center">
        <div class="wrapper-menu">
          <div class="main-circle"><i class="ri-menu-line"></i></div>
          <div class="hover-circle"><i class="ri-close-fill"></i></div>
        </div>
        <div class="iq-navbar-logo d-flex justify-content-between ml-3">
          <a href="{{ route('dashboard') }}" class="header-logo">
            <img src="/images/logo.png" class="img-fluid rounded" alt="logo">
            <span>Allo work infinity</span>
          </a>
        </div>
      </div>

      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav ml-auto navbar-list">
          @auth
          @php
            $user       = Auth::user();
            $avatarUrl  = $user->profile_picture_url ?: '/images/logo.png';
            $display    = trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: ($user->name ?? 'User');
            $roleLabel  = ($user->is_admin ?? false) ? 'Admin' : 'User';
          @endphp

          <li class="line-height">
            <a href="#" class="mt-2 search-toggle iq-waves-effect d-flex align-items-center">
              <img src="{{ $avatarUrl }}" class="img-fluid rounded" alt="avatar" style="max-height: 40px;">
              <div class="caption ml-2">
                <h6 class="mb-0 line-height">{{ $display }}</h6>
                <p class="mb-0">{{ $roleLabel }}</p>
              </div>
            </a>

            <div class="iq-sub-dropdown iq-user-dropdown">
              <div class="iq-card shadow-none m-0">
                <div class="iq-card-body p-0">
                  <div class="bg-primary p-3">
                    <h5 class="mb-0 text-white line-height">Hello {{ $display }}</h5>
                    <span class="text-white font-size-12">Available</span>
                  </div>

                  @if(Route::has('users.edit'))
                    <a href="{{ route('users.edit', $user->id) }}" class="iq-sub-card iq-bg-primary-hover">
                      <div class="media align-items-center">
                        <div class="rounded iq-card-icon iq-bg-primary">
                          <i class="ri-file-user-line"></i>
                        </div>
                        <div class="media-body ml-3">
                          <h6 class="mb-0">My Profile</h6>
                          <p class="mb-0 font-size-12">View and update your profile.</p>
                        </div>
                      </div>
                    </a>
                  @endif

                  <div class="d-inline-block w-100 text-center p-3">
                    <form method="POST" action="{{ route('logout') }}">
                      @csrf
                      <button type="submit" class="bg-primary iq-sign-btn border-0 w-100">
                        Sign out <i class="ri-login-box-line ml-2"></i>
                      </button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </li>
          @endauth
        </ul>
      </div>

    </nav>
  </div>
</div>
