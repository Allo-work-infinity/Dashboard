<!doctype html>
<html lang="en">
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
   <title>Allo work infinity - Ajouter un abonnement utilisateurt</title>
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
                <h4 class="card-title">Ajouter un abonnement utilisateur</h4>
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

                <form method="POST" action="{{ route('user-subscriptions.store') }}" novalidate>
                @csrf

                {{-- User & Plan --}}
                <div class="form-row">
                    <div class="form-group col-md-6">
                    <label for="user_id">Utilisateur</label>
                    <select
                        id="user_id"
                        name="user_id"
                        class="form-control select2 @error('user_id') is-invalid @enderror"
                        required
                        data-placeholder="Sélectionner un utilisateur"
                    >
                        <option value=""></option>
                        @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>
                            {{ $u->first_name }} {{ $u->last_name }} — {{ $u->email }}
                        </option>
                        @endforeach
                    </select>
                    @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group col-md-6">
                    <label for="plan_id">Plan d’abonnement</label>
                    <select
                        id="plan_id"
                        name="plan_id"
                        class="form-control select2 @error('plan_id') is-invalid @enderror"
                        required
                        data-placeholder="Sélectionner un plan"
                    >
                        <option value=""></option>
                        @foreach($plans as $p)
                        <option value="{{ $p->id }}" {{ old('plan_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('plan_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Statuses --}}
                <div class="form-row">
                    <div class="form-group col-md-6">
                    <label for="status">Statut d’abonnement</label>
                    <select
                        id="status"
                        name="status"
                        class="form-control @error('status') is-invalid @enderror"
                        required
                    >
                        @foreach(\App\Models\UserSubscription::STATUSES as $st)
                        <option value="{{ $st }}" {{ old('status', \App\Models\UserSubscription::STATUS_ACTIVE) === $st ? 'selected' : '' }}>
                            {{ ucfirst($st) }}
                        </option>
                        @endforeach
                    </select>
                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    @php use App\Models\UserSubscription; @endphp

                        <div class="form-group col-md-6">
                        <label for="payment_status">Statut de paiement</label>
                        <select
                            id="payment_status"
                            name="payment_status"
                            class="form-control @error('payment_status') is-invalid @enderror"
                            required
                        >
                            @foreach(UserSubscription::PAYMENT_STATUSES as $pst)
                                <option value="{{ $pst }}"
                                    {{ old('payment_status', UserSubscription::PAY_PENDING) === $pst ? 'selected' : '' }}>
                                    {{ ucfirst($pst) }}
                                </option>
                            @endforeach
                        </select>
                        @error('payment_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                </div>

                {{-- Amount & Auto renewal --}}
                <div class="form-row">
                    <div class="form-group col-md-4">
                    <label for="amount_paid">Montant payé (optionnel)</label>
                    <input
                        type="number"
                        step="0.001"
                        min="0"
                        id="amount_paid"
                        name="amount_paid"
                        class="form-control @error('amount_paid') is-invalid @enderror"
                        value="{{ old('amount_paid') }}"
                        placeholder="Laisse vide pour utiliser le prix du plan"
                    >
                    @error('amount_paid') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <small class="form-text text-muted">
                        Si vide, le prix du plan sera utilisé.
                    </small>
                    </div>

                    <div class="form-group col-md-4">
                    <label for="payment_method">Méthode de paiement (optionnel)</label>
                    <input
                        type="text"
                        id="payment_method"
                        name="payment_method"
                        class="form-control @error('payment_method') is-invalid @enderror"
                        value="{{ old('payment_method') }}"
                        placeholder="ex. carte, virement…"
                    >
                    @error('payment_method') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group col-md-4 d-flex align-items-center">
                    <div class="form-check mt-4">
                        <input
                        type="checkbox"
                        id="auto_renewal"
                        name="auto_renewal"
                        value="1"
                        class="form-check-input"
                        {{ old('auto_renewal', false) ? 'checked' : '' }}
                        >
                        <label for="auto_renewal" class="form-check-label">Renouvellement automatique</label>
                    </div>
                    </div>
                </div>

                {{-- Period --}}
                <div class="form-row">
                    <div class="form-group col-md-6">
                    <label for="start_date">Date de début (optionnel)</label>
                    <input
                        type="date"
                        id="start_date"
                        name="start_date"
                        class="form-control @error('start_date') is-invalid @enderror"
                        value="{{ old('start_date') }}"
                    >
                    @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <small class="form-text text-muted">
                        Si statut « active » et vide, sera défini à aujourd’hui.
                    </small>
                    </div>

                    <div class="form-group col-md-6">
                    <label for="end_date">Date de fin (optionnel)</label>
                    <input
                        type="date"
                        id="end_date"
                        name="end_date"
                        class="form-control @error('end_date') is-invalid @enderror"
                        value="{{ old('end_date') }}"
                    >
                    @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <small class="form-text text-muted">
                        Si vide et une date de début est fournie, sera calculée depuis la durée du plan.
                    </small>
                    </div>
                </div>

                {{-- Payment identifiers (optional) --}}
                <div class="form-row">
                    <div class="form-group col-md-4">
                    <label for="payment_id">Payment ID (optionnel)</label>
                    <input
                        type="text"
                        id="payment_id"
                        name="payment_id"
                        class="form-control @error('payment_id') is-invalid @enderror"
                        value="{{ old('payment_id') }}"
                    >
                    @error('payment_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group col-md-4">
                    <label for="transaction_id">Transaction ID (optionnel)</label>
                    <input
                        type="text"
                        id="transaction_id"
                        name="transaction_id"
                        class="form-control @error('transaction_id') is-invalid @enderror"
                        value="{{ old('transaction_id') }}"
                    >
                    @error('transaction_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="{{ route('user-subscriptions.index') }}" class="btn btn-secondary">Annuler</a>
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
