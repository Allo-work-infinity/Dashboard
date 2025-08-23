<!doctype html>
<html lang="en">
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
   <title>Allo work infinity - Modifier un utilisateur</title>
   <link rel="shortcut icon" href="/images/favicon.ico" />
   <link rel="stylesheet" href="/css/bootstrap.min.css">
   <link rel="stylesheet" href="/css/typography.css">
   <link rel="stylesheet" href="/css/style.css">
   <link rel="stylesheet" href="/css/responsive.css">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" crossorigin="anonymous" />
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" crossorigin="anonymous" />
</head>
<body>
<div id="loading"><div id="loading-center"></div></div>

<div class="wrapper">
   @include("layouts.sidebar")
   @include("layouts.header")

   <!-- Toast Errors -->
   <div aria-live="polite" aria-atomic="true" class="position-fixed top-0 end-0 p-3" style="z-index: 9999; right: 0; top: 60px;">
      @if ($errors->any())
         <div class="toast show bg-danger text-white" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-danger text-white">
               <strong class="me-auto">Erreurs de Validation</strong>
               <button type="button" class="ml-2 mb-1 close text-white" data-bs-dismiss="toast" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
               </button>
            </div>
            <div class="toast-body">
               <ul class="mb-0">
                  @foreach ($errors->all() as $error)
                     <li>{{ $error }}</li>
                  @endforeach
               </ul>
            </div>
         </div>
      @endif
   </div>
        <div id="content-page" class="content-page">
        <div class="container-fluid">
            <div class="row">
            <div class="col-sm-12 col-lg-12">
                <div class="iq-card">
                <div class="iq-card-header d-flex justify-content-between">
                    <div class="iq-header-title">
                    <h4 class="card-title">Modifier un utilisateur</h4>
                    </div>
                    <div class="col-sm-12 col-md-6 text-right">
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">← Retour à la liste</a>
                    </div>
                </div>

                <div class="iq-card-body">
                    @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                        </ul>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('users.update', $user) }}" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="form-row">
                        <div class="form-group col-md-6">
                        <label for="first_name">Prénom</label>
                        <input type="text" id="first_name" name="first_name"
                                class="form-control @error('first_name') is-invalid @enderror"
                                value="{{ old('first_name', $user->first_name) }}" required>
                        @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group col-md-6">
                        <label for="last_name">Nom</label>
                        <input type="text" id="last_name" name="last_name"
                                class="form-control @error('last_name') is-invalid @enderror"
                                value="{{ old('last_name', $user->last_name) }}" required>
                        @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', $user->email) }}" required autocomplete="email">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group col-md-3">
                        <label for="password">Mot de passe (laisser vide pour conserver)</label>
                        <input type="password" id="password" name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                autocomplete="new-password">
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group col-md-3">
                        <label for="password_confirmation">Confirmer le mot de passe</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                                class="form-control" autocomplete="new-password">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                        <label for="phone">Téléphone (optionnel)</label>
                        <input type="text" id="phone" name="phone"
                                class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone', $user->phone) }}">
                        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group col-md-4">
                        <label for="date_of_birth">Date de naissance (optionnel)</label>
                        <input type="date" id="date_of_birth" name="date_of_birth"
                                class="form-control @error('date_of_birth') is-invalid @enderror"
                                value="{{ old('date_of_birth', optional($user->date_of_birth)->format('Y-m-d')) }}">
                        @error('date_of_birth') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group col-md-4">
                        <label for="status">Statut</label>
                        <select id="status" name="status"
                                class="form-control select2 @error('status') is-invalid @enderror" required>
                            <option value="active" {{ old('status', $user->status ?? 'active')==='active' ? 'selected' : '' }}>Actif</option>
                            <option value="suspended" {{ old('status', $user->status)==='suspended' ? 'selected' : '' }}>Suspendu</option>
                            <option value="banned" {{ old('status', $user->status)==='banned' ? 'selected' : '' }}>Banni</option>
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                        <label for="city">Ville (optionnel)</label>
                        <input type="text" id="city" name="city"
                                class="form-control @error('city') is-invalid @enderror"
                                value="{{ old('city', $user->city) }}">
                        @error('city') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group col-md-6">
                        <label for="governorate">Gouvernorat (optionnel)</label>
                        <input type="text" id="governorate" name="governorate"
                                class="form-control @error('governorate') is-invalid @enderror"
                                value="{{ old('governorate', $user->governorate) }}">
                        @error('governorate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="address">Adresse (optionnel)</label>
                        <textarea id="address" name="address"
                                class="form-control @error('address') is-invalid @enderror"
                                rows="2">{{ old('address', $user->address) }}</textarea>
                        @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                        <label for="profile_picture_url">URL photo de profil (optionnel)</label>
                        <input type="url" id="profile_picture_url" name="profile_picture_url"
                                class="form-control @error('profile_picture_url') is-invalid @enderror"
                                value="{{ old('profile_picture_url', $user->profile_picture_url) }}"
                                placeholder="https://…">
                        @error('profile_picture_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group col-md-6">
                        <label for="cv_file_url">URL CV (optionnel)</label>
                        <input type="url" id="cv_file_url" name="cv_file_url"
                                class="form-control @error('cv_file_url') is-invalid @enderror"
                                value="{{ old('cv_file_url', $user->cv_file_url) }}"
                                placeholder="https://…">
                        @error('cv_file_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- is_admin is enforced to false in the controller --}}

                    <button type="submit" class="btn btn-primary">Mettre à jour</button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">Annuler</a>
                    </form>

                    <hr class="my-4">

                    <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Supprimer cet utilisateur ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Supprimer l’utilisateur</button>
                    </form>
                </div>

                </div>
            </div>
            </div>
        </div>
        </div>


   @include("layouts.footer")
</div>

<!-- JS -->
<script src="/js/jquery.min.js"></script>
<script>
   $(document).ready(function () {
      $('.toast').toast({ delay: 5000 }).toast('show');
   });
</script>
<script>
   $(document).ready(function () {
   $('.toast').toast({ delay: 5000 }).toast('show');

      $('#role').select2({
         placeholder: "Sélectionner un rôle",
         allowClear: true,
         width: '100%'
      });

      $('#status').select2({
         placeholder: "Sélectionner un statut",
         allowClear: true,
         width: '100%'
      });
   });

</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js" crossorigin="anonymous"></script>
<script src="/js/popper.min.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/jquery.appear.js"></script>
<script src="/js/countdown.min.js"></script>
<script src="/js/waypoints.min.js"></script>
<script src="/js/jquery.counterup.min.js"></script>
<script src="/js/wow.min.js"></script>
<script src="/js/apexcharts.js"></script>
<script src="/js/slick.min.js"></script>
<script src="/js/owl.carousel.min.js"></script>
<script src="/js/jquery.magnific-popup.min.js"></script>
<script src="/js/smooth-scrollbar.js"></script>
<script src="/js/lottie.js"></script>
<script src="/js/core.js"></script>
<script src="/js/charts.js"></script>
<script src="/js/animated.js"></script>
<script src="/js/kelly.js"></script>
<script src="/js/maps.js"></script>
<script src="/js/worldLow.js"></script>
<script src="/js/raphael-min.js"></script>
<script src="/js/morris.js"></script>
<script src="/js/morris.min.js"></script>
<script src="/js/flatpickr.js"></script>
<script src="/js/style-customizer.js"></script>
<script src="/js/chart-custom.js"></script>
<script src="/js/custom.js"></script>
</body>
</html>
