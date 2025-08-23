<!doctype html>
<html lang="en">
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
   <title>Allo work infinity - Modifier une transaction </title>
   <link rel="shortcut icon" href="/images/favicon.ico" />
   <link rel="stylesheet" href="/css/bootstrap.min.css">
   <link rel="stylesheet" href="/css/typography.css">
   <link rel="stylesheet" href="/css/style.css">
   <link rel="stylesheet" href="/css/responsive.css">
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
                    <h4 class="card-title">Modifier une transaction</h4>
                    </div>
                    <div class="col-sm-12 col-md-6 text-right">
                    <a href="{{ route('payment-transactions.index') }}" class="btn btn-secondary">← Retour à la liste</a>
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

                    <form method="POST" action="{{ route('payment-transactions.update', $txn) }}" novalidate>
                    @csrf
                    @method('PUT')

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
                            <option value="{{ $u->id }}" {{ (string) old('user_id', $txn->user_id) === (string) $u->id ? 'selected' : '' }}>
                                {{ $u->first_name }} {{ $u->last_name }} — {{ $u->email }}
                            </option>
                            @endforeach
                        </select>
                        @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group col-md-6">
                        <label for="subscription_id">Souscription</label>
                        <select
                            id="subscription_id"
                            name="subscription_id"
                            class="form-control select2 @error('subscription_id') is-invalid @enderror"
                            required
                            data-placeholder="Sélectionner une souscription"
                        >
                            <option value=""></option>
                            @foreach($subs as $s)
                            <option value="{{ $s->id }}" {{ (string) old('subscription_id', $txn->subscription_id) === (string) $s->id ? 'selected' : '' }}>
                                #{{ $s->id }}
                            </option>
                            @endforeach
                        </select>
                        @error('subscription_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                        <label for="konnect_payment_id">Konnect Payment ID</label>
                        <input
                            type="text"
                            id="konnect_payment_id"
                            name="konnect_payment_id"
                            class="form-control @error('konnect_payment_id') is-invalid @enderror"
                            value="{{ old('konnect_payment_id', $txn->konnect_payment_id) }}"
                            required
                        >
                        @error('konnect_payment_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group col-md-6">
                        <label for="konnect_transaction_id">Konnect Transaction ID (optionnel)</label>
                        <input
                            type="text"
                            id="konnect_transaction_id"
                            name="konnect_transaction_id"
                            class="form-control @error('konnect_transaction_id') is-invalid @enderror"
                            value="{{ old('konnect_transaction_id', $txn->konnect_transaction_id) }}"
                        >
                        @error('konnect_transaction_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-3">
                        <label for="amount">Montant</label>
                        <input
                            type="number"
                            step="0.001"
                            min="0"
                            id="amount"
                            name="amount"
                            class="form-control @error('amount') is-invalid @enderror"
                            value="{{ old('amount', $txn->amount) }}"
                            required
                        >
                        @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group col-md-2">
                        <label for="currency">Devise</label>
                        <input
                            type="text"
                            id="currency"
                            name="currency"
                            class="form-control @error('currency') is-invalid @enderror"
                            value="{{ old('currency', $txn->currency ?? 'TND') }}"
                            maxlength="3"
                        >
                        @error('currency') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group col-md-3">
                        <label for="payment_method">Méthode de paiement</label>
                        <input
                            type="text"
                            id="payment_method"
                            name="payment_method"
                            class="form-control @error('payment_method') is-invalid @enderror"
                            value="{{ old('payment_method', $txn->payment_method) }}"
                            required
                        >
                        @error('payment_method') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group col-md-4">
                        <label for="status">Statut</label>
                        <select
                            id="status"
                            name="status"
                            class="form-control @error('status') is-invalid @enderror"
                        >
                            @foreach($statuses as $st)
                            <option value="{{ $st }}" {{ old('status', $txn->status) === $st ? 'selected' : '' }}>
                                {{ ucfirst($st) }}
                            </option>
                            @endforeach
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="konnect_response">Réponse Konnect (JSON, optionnel)</label>
                        @php
                        $jsonOld = old('konnect_response');
                        $pretty  = $jsonOld !== null
                            ? $jsonOld
                            : ( $txn->konnect_response
                                ? json_encode($txn->konnect_response, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)
                                : ''
                            );
                        @endphp
                        <textarea
                        id="konnect_response"
                        name="konnect_response"
                        rows="4"
                        class="form-control @error('konnect_response') is-invalid @enderror"
                        placeholder='{"status":"OK","payload":{...}}'
                        >{{ $pretty }}</textarea>
                        @error('konnect_response') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="form-text text-muted">
                        Vous pouvez coller ici la réponse JSON complète de Konnect (sera parsée et stockée).
                        </small>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-8">
                        <label for="failure_reason">Raison d’échec (optionnel)</label>
                        <textarea
                            id="failure_reason"
                            name="failure_reason"
                            rows="2"
                            class="form-control @error('failure_reason') is-invalid @enderror"
                            placeholder="Message d’échec côté passerelle, etc."
                        >{{ old('failure_reason', $txn->failure_reason) }}</textarea>
                        @error('failure_reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group col-md-4">
                        <label for="processed_at">Traitée le (optionnel)</label>
                        <input
                            type="datetime-local"
                            id="processed_at"
                            name="processed_at"
                            class="form-control @error('processed_at') is-invalid @enderror"
                            value="{{ old('processed_at', optional($txn->processed_at)->format('Y-m-d\TH:i')) }}"
                        >
                        @error('processed_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="form-text text-muted">
                            Si vous choisissez un statut final (complétée/échouée/annulée) et laissez ce champ vide,
                            il sera défini automatiquement à maintenant.
                        </small>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Mettre à jour</button>
                    <a href="{{ route('payment-transactions.index') }}" class="btn btn-secondary">Annuler</a>
                    </form>

                    <hr class="my-4">

                    {{-- Optionnel : bouton rapide pour changer le statut (si vous avez une route setStatus) --}}
                    {{-- 
                    <form action="{{ route('payment-transactions.set-status', $txn) }}" method="POST" class="d-inline-block">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="completed">
                    <button type="submit" class="btn btn-success">Marquer complétée</button>
                    </form>
                    --}}

                </div>
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
<script src="/js/bootstrap.min.js"></script>
<script src="/js/select2.min.js"></script>
<script>
   $('select').select2();
</script>
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
