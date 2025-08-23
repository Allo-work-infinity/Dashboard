<!doctype html>
<html lang="en">
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
   <title>Allo work infinity - Modifier une candidature</title>
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
                            <h4 class="card-title">Modifier une candidature</h4>
                            </div>
                            <div class="col-sm-12 col-md-6 text-right">
                            <a href="{{ route('job-applications.index') }}" class="btn btn-secondary">← Retour à la liste</a>
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

                            {{-- Basic meta --}}
                            <div class="mb-3">
                            <span class="badge badge-light">Créée le: {{ optional($app->created_at)->format('Y-m-d H:i') }}</span>
                            <span class="badge badge-light">Dernière maj: {{ optional($app->updated_at)->format('Y-m-d H:i') }}</span>
                            </div>

                            <form method="POST" action="{{ route('job-applications.update', $app) }}" enctype="multipart/form-data" novalidate>
                            @csrf
                            @method('PUT')

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                <label for="user_id">Candidat</label>
                                <select
                                    id="user_id"
                                    name="user_id"
                                    class="form-control select2 @error('user_id') is-invalid @enderror"
                                    required
                                    data-placeholder="Sélectionner un utilisateur"
                                >
                                    <option value=""></option>
                                    @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ old('user_id', $app->user_id) == $u->id ? 'selected' : '' }}>
                                        {{ $u->first_name }} {{ $u->last_name }} — {{ $u->email }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="form-group col-md-6">
                                <label for="job_offer_id">Offre d’emploi</label>
                                <select
                                    id="job_offer_id"
                                    name="job_offer_id"
                                    class="form-control select2 @error('job_offer_id') is-invalid @enderror"
                                    required
                                    data-placeholder="Sélectionner une offre"
                                >
                                    <option value=""></option>
                                    @foreach($offers as $o)
                                    <option value="{{ $o->id }}" {{ old('job_offer_id', $app->job_offer_id) == $o->id ? 'selected' : '' }}>
                                        {{ $o->title }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('job_offer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-4">
                                <label for="status">Statut</label>
                                <select
                                    id="status"
                                    name="status"
                                    class="form-control @error('status') is-invalid @enderror"
                                >
                                    @foreach($statuses as $st)
                                    <option value="{{ $st }}" {{ old('status', $app->status)===$st ? 'selected' : '' }}>
                                        {{ str_replace('_',' ', ucfirst($st)) }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="form-group col-md-4">
                                <label for="cv">Remplacer le CV (optionnel)</label>
                                <input
                                    type="file"
                                    id="cv"
                                    name="cv"
                                    class="form-control-file @error('cv') is-invalid @enderror"
                                    accept=".pdf,.doc,.docx"
                                >
                                @error('cv') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                <small class="form-text text-muted">PDF/DOC/DOCX, max 4 Mo.</small>
                                </div>

                                <div class="form-group col-md-4">
                                <label for="cv_file_url">ou URL du CV (optionnel)</label>
                                <input
                                    type="url"
                                    id="cv_file_url"
                                    name="cv_file_url"
                                    class="form-control @error('cv_file_url') is-invalid @enderror"
                                    value="{{ old('cv_file_url', $app->cv_file_url) }}"
                                    placeholder="https://…"
                                >
                                @error('cv_file_url') <div class="invalid-feedback">{{ $message }}</div> @enderror

                                @if($app->cv_file_url)
                                    <small class="form-text">
                                    CV actuel: <a href="{{ $app->cv_file_url }}" target="_blank" rel="noopener">ouvrir</a>
                                    </small>
                                @endif
                                </div>
                            </div>

                            @php
                                $additionalDocsValue = old('additional_documents');
                                if ($additionalDocsValue === null) {
                                $additionalDocsValue = is_array($app->additional_documents)
                                    ? implode(', ', $app->additional_documents)
                                    : ($app->additional_documents ?? '');
                                }
                            @endphp

                            <div class="form-group">
                                <label for="additional_documents">Documents supplémentaires (optionnel)</label>
                                <textarea
                                id="additional_documents"
                                name="additional_documents"
                                rows="3"
                                class="form-control @error('additional_documents') is-invalid @enderror"
                                placeholder='JSON ou liste: ["Lettre de motivation","Portfolio"]  OU  Lettre de motivation, Portfolio'
                                >{{ $additionalDocsValue }}</textarea>
                                @error('additional_documents') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="form-text text-muted">
                                Accepte un JSON (ex. <code>["Lettre","Portfolio"]</code>) ou une liste séparée par virgules/retours à la ligne.
                                </small>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                <label for="response_message">Message de réponse (optionnel)</label>
                                <textarea
                                    id="response_message"
                                    name="response_message"
                                    rows="2"
                                    class="form-control @error('response_message') is-invalid @enderror"
                                >{{ old('response_message', $app->response_message) }}</textarea>
                                @error('response_message') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="form-group col-md-6">
                                <label for="admin_notes">Notes admin (optionnel)</label>
                                <textarea
                                    id="admin_notes"
                                    name="admin_notes"
                                    rows="2"
                                    class="form-control @error('admin_notes') is-invalid @enderror"
                                >{{ old('admin_notes', $app->admin_notes) }}</textarea>
                                @error('admin_notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                <label for="reviewed_by">Revu par (optionnel)</label>
                                <select
                                    id="reviewed_by"
                                    name="reviewed_by"
                                    class="form-control select2 @error('reviewed_by') is-invalid @enderror"
                                    data-placeholder="Sélectionner un relecteur"
                                >
                                    <option value=""></option>
                                    @foreach($reviewers as $r)
                                    <option value="{{ $r->id }}" {{ old('reviewed_by', $app->reviewed_by) == $r->id ? 'selected' : '' }}>
                                        {{ $r->first_name }} {{ $r->last_name }} — {{ $r->email }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('reviewed_by') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="form-text text-muted">Si renseigné sans date, la date de revue sera définie automatiquement à maintenant.</small>
                                </div>

                                <div class="form-group col-md-6">
                                <label for="reviewed_at">Date de revue (optionnel)</label>
                                <input
                                    type="datetime-local"
                                    id="reviewed_at"
                                    name="reviewed_at"
                                    class="form-control @error('reviewed_at') is-invalid @enderror"
                                    value="{{ old('reviewed_at', optional($app->reviewed_at)->format('Y-m-d\TH:i')) }}"
                                >
                                @error('reviewed_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Mettre à jour</button>
                            <a href="{{ route('job-applications.index') }}" class="btn btn-secondary">Annuler</a>
                            </form>

                            <hr class="my-4">

                            {{-- Quick actions (optional, if routes are defined) --}}
                            <div class="d-flex flex-wrap align-items-center">
                            <form action="{{ route('job-applications.set-status', $app) }}" method="POST" class="form-inline mr-2">
                                @csrf
                                @method('PATCH')
                                <div class="form-group mb-2 mr-2">
                                <label for="quick_status" class="mr-2">Changer statut:</label>
                                <select name="status" id="quick_status" class="form-control">
                                    @foreach($statuses as $st)
                                    <option value="{{ $st }}">{{ str_replace('_',' ', ucfirst($st)) }}</option>
                                    @endforeach
                                </select>
                                </div>
                                <button type="submit" class="btn btn-info mb-2">Appliquer</button>
                            </form>

                            <form action="{{ route('job-applications.mark-reviewed', $app) }}" method="POST" class="form-inline mt-2 mt-md-0">
                                @csrf
                                @method('PATCH')
                                <div class="form-group mb-2 mr-2">
                                <input type="text" name="response_message" class="form-control" placeholder="Message (optionnel)">
                                </div>
                                <div class="form-group mb-2 mr-2">
                                <input type="text" name="admin_notes" class="form-control" placeholder="Notes (optionnel)">
                                </div>
                                <button type="submit" class="btn btn-success mb-2">Marquer comme revu</button>
                            </form>
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
