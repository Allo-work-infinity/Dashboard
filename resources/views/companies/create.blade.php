<!doctype html>
<html lang="en">
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
   <title>Allo work infinity - Ajouter une entreprise</title>
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
                <h4 class="card-title">Ajouter une entreprise</h4>
                </div>
            </div>

            <div class="iq-card-body">
                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                    </ul>
                </div>
                @endif

                <form method="POST" action="{{ route('companies.store') }}" novalidate>
                @csrf

                <div class="form-row">
                    <div class="form-group col-md-6">
                    <label for="name">Nom de l’entreprise</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name') }}"
                        required
                    >
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group col-md-3">
                    <label for="industry">Secteur (optionnel)</label>
                    <input
                        type="text"
                        id="industry"
                        name="industry"
                        class="form-control @error('industry') is-invalid @enderror"
                        value="{{ old('industry') }}"
                        maxlength="100"
                    >
                    @error('industry') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group col-md-3">
                    <label for="company_size">Taille (optionnel)</label>
                    <select
                        id="company_size"
                        name="company_size"
                        class="form-control @error('company_size') is-invalid @enderror"
                    >
                        <option value="">— Sélectionner —</option>
                        @foreach($sizes as $size)
                        <option value="{{ $size }}" {{ old('company_size')===$size ? 'selected' : '' }}>
                            {{ ucfirst($size) }}
                        </option>
                        @endforeach
                    </select>
                    @error('company_size') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description (optionnel)</label>
                    <textarea
                    id="description"
                    name="description"
                    rows="3"
                    class="form-control @error('description') is-invalid @enderror"
                    placeholder="Courte description de l’entreprise…"
                    >{{ old('description') }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                    <label for="website">Site Web (optionnel)</label>
                    <input
                        type="url"
                        id="website"
                        name="website"
                        class="form-control @error('website') is-invalid @enderror"
                        value="{{ old('website') }}"
                        placeholder="https://…"
                    >
                    @error('website') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group col-md-6">
                    <label for="logo_url">Logo URL (optionnel)</label>
                    <input
                        type="url"
                        id="logo_url"
                        name="logo_url"
                        class="form-control @error('logo_url') is-invalid @enderror"
                        value="{{ old('logo_url') }}"
                        placeholder="https://…"
                    >
                    @error('logo_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="address">Adresse (optionnel)</label>
                    <textarea
                    id="address"
                    name="address"
                    rows="2"
                    class="form-control @error('address') is-invalid @enderror"
                    >{{ old('address') }}</textarea>
                    @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                    <label for="city">Ville (optionnel)</label>
                    <input
                        type="text"
                        id="city"
                        name="city"
                        class="form-control @error('city') is-invalid @enderror"
                        value="{{ old('city') }}"
                        maxlength="100"
                    >
                    @error('city') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group col-md-4">
                    <label for="governorate">Gouvernorat (optionnel)</label>
                    <input
                        type="text"
                        id="governorate"
                        name="governorate"
                        class="form-control @error('governorate') is-invalid @enderror"
                        value="{{ old('governorate') }}"
                        maxlength="100"
                    >
                    @error('governorate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group col-md-4">
                    <label for="contact_email">Email de contact (optionnel)</label>
                    <input
                        type="email"
                        id="contact_email"
                        name="contact_email"
                        class="form-control @error('contact_email') is-invalid @enderror"
                        value="{{ old('contact_email') }}"
                        maxlength="255"
                    >
                    @error('contact_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                    <label for="contact_phone">Téléphone (optionnel)</label>
                    <input
                        type="text"
                        id="contact_phone"
                        name="contact_phone"
                        class="form-control @error('contact_phone') is-invalid @enderror"
                        value="{{ old('contact_phone') }}"
                        maxlength="20"
                    >
                    @error('contact_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group col-md-4">
                    <label for="status">Statut</label>
                    <select
                        id="status"
                        name="status"
                        class="form-control @error('status') is-invalid @enderror"
                    >
                        @foreach($statuses as $st)
                        <option value="{{ $st }}" {{ old('status', \App\Models\Company::STATUS_ACTIVE)===$st ? 'selected' : '' }}>
                            {{ $st === 'active' ? 'Actif' : 'Suspendu' }}
                        </option>
                        @endforeach
                    </select>
                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group col-md-4 d-flex align-items-center">
                    <div class="form-check mt-4">
                        <input
                        type="checkbox"
                        id="is_verified"
                        name="is_verified"
                        value="1"
                        class="form-check-input"
                        {{ old('is_verified', false) ? 'checked' : '' }}
                        >
                        <label for="is_verified" class="form-check-label">Entreprise vérifiée</label>
                    </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="{{ route('companies.index') }}" class="btn btn-secondary">Annuler</a>
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
