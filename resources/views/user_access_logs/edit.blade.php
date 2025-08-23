<!doctype html>
<html lang="en">
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
   <title>Allo work infinity - Modifier un log d’accès</title>
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

    {{-- resources/views/user_access_logs/edit.blade.php --}}
    <div id="content-page" class="content-page">
    <div class="container-fluid">
        <div class="row">
        <div class="col-sm-12 col-lg-12">
            <div class="iq-card">
            <div class="iq-card-header d-flex justify-content-between">
                <div class="iq-header-title">
                <h4 class="card-title">Modifier un log d’accès</h4>
                </div>
                <div class="col-sm-12 col-md-6 text-right">
                <a href="{{ route('user-access-logs.index') }}" class="btn btn-secondary">← Retour à la liste</a>
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

                @php
                // Prefill helpers for array/JSON fields
                $pagesValue = old('pages_visited');
                if ($pagesValue === null) {
                    $pagesValue = is_array($log->pages_visited)
                        ? implode(', ', $log->pages_visited)
                        : ($log->pages_visited ?? '');
                }
                $actionsValue = old('actions_performed');
                if ($actionsValue === null) {
                    $actionsValue = is_array($log->actions_performed)
                        ? implode(', ', $log->actions_performed)
                        : ($log->actions_performed ?? '');
                }
                $accessTimeValue = old('access_time', optional($log->access_time)->format('Y-m-d\TH:i'));
                @endphp

                <form method="POST" action="{{ route('user-access-logs.update', $log) }}" novalidate>
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
                        <option value="{{ $u->id }}" {{ old('user_id', $log->user_id) == $u->id ? 'selected' : '' }}>
                            {{ $u->first_name }} {{ $u->last_name }} — {{ $u->email }}
                        </option>
                        @endforeach
                    </select>
                    @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group col-md-6">
                    <label for="access_time">Date & heure d’accès</label>
                    <input
                        type="datetime-local"
                        id="access_time"
                        name="access_time"
                        class="form-control @error('access_time') is-invalid @enderror"
                        value="{{ $accessTimeValue }}"
                        {{ $log->access_time ? '' : '' }}
                    >
                    @error('access_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <small class="form-text text-muted">
                        Si laissé vide lors de la création, la date par défaut est « maintenant ». Ici vous pouvez l’ajuster.
                    </small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                    <label for="ip_address">Adresse IP (optionnel)</label>
                    <input
                        type="text"
                        id="ip_address"
                        name="ip_address"
                        class="form-control @error('ip_address') is-invalid @enderror"
                        value="{{ old('ip_address', $log->ip_address) }}"
                        maxlength="45"
                    >
                    @error('ip_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <small class="form-text text-muted">Si vide, elle peut être déduite de la requête lors de la création.</small>
                    </div>

                    <div class="form-group col-md-4">
                    <label for="session_duration">Durée de session (secondes, optionnel)</label>
                    <input
                        type="number"
                        min="0"
                        step="1"
                        id="session_duration"
                        name="session_duration"
                        class="form-control @error('session_duration') is-invalid @enderror"
                        value="{{ old('session_duration', $log->session_duration) }}"
                    >
                    @error('session_duration') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group col-md-4">
                    <label for="user_agent">User-Agent (optionnel)</label>
                    <textarea
                        id="user_agent"
                        name="user_agent"
                        rows="2"
                        class="form-control @error('user_agent') is-invalid @enderror"
                    >{{ old('user_agent', $log->user_agent) }}</textarea>
                    @error('user_agent') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <small class="form-text text-muted">Si vide, il peut être déduit de la requête lors de la création.</small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                    <label for="pages_visited">Pages visitées (optionnel)</label>
                    <textarea
                        id="pages_visited"
                        name="pages_visited"
                        rows="3"
                        class="form-control @error('pages_visited') is-invalid @enderror"
                        placeholder='JSON ou liste: ["/", "/jobs", "/login"]  OU  /, /jobs, /login'
                    >{{ $pagesValue }}</textarea>
                    @error('pages_visited') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <small class="form-text text-muted">
                        Accepte un JSON (ex. <code>["/","/jobs"]</code>) ou une liste séparée par virgules/retours à la ligne.
                    </small>
                    </div>

                    <div class="form-group col-md-6">
                    <label for="actions_performed">Actions effectuées (optionnel)</label>
                    <textarea
                        id="actions_performed"
                        name="actions_performed"
                        rows="3"
                        class="form-control @error('actions_performed') is-invalid @enderror"
                        placeholder='JSON ou liste: ["login","apply_job"]  OU  login, apply_job'
                    >{{ $actionsValue }}</textarea>
                    @error('actions_performed') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Mettre à jour</button>
                <a href="{{ route('user-access-logs.index') }}" class="btn btn-secondary">Annuler</a>
                </form>

                <hr class="my-4">

                <form action="{{ route('user-access-logs.destroy', $log) }}" method="POST" onsubmit="return confirm('Supprimer ce log ?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Supprimer le log</button>
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
