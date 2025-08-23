<!doctype html>
<html lang="en">
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
   <title>Allo work infinity - Ajouter une offre d’emploi</title>
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
              <h4 class="card-title">Ajouter une offre d’emploi</h4>
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

            <form method="POST" action="{{ route('job-offers.store') }}" novalidate>
              @csrf

              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="company_id">Entreprise</label>
                  <select
                    id="company_id"
                    name="company_id"
                    class="form-control @error('company_id') is-invalid @enderror"
                    required
                  >
                    <option value="">— Sélectionner —</option>
                    @foreach($companies as $c)
                      <option value="{{ $c->id }}" {{ old('company_id') == $c->id ? 'selected' : '' }}>
                        {{ $c->name }}
                      </option>
                    @endforeach
                  </select>
                  @error('company_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="form-group col-md-6">
                  <label for="category_id">Catégorie</label>
                  <select
                    id="category_id"
                    name="category_id"
                    class="form-control @error('category_id') is-invalid @enderror"
                    required
                  >
                    <option value="">— Sélectionner —</option>
                    @foreach($categories as $cat)
                      <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                      </option>
                    @endforeach
                  </select>
                  @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="form-group col-md-6">
                  <label for="title">Intitulé du poste</label>
                  <input
                    type="text"
                    id="title"
                    name="title"
                    class="form-control @error('title') is-invalid @enderror"
                    value="{{ old('title') }}"
                    required
                  >
                  @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
              </div>

              {{-- 🔗 Plans d’abonnement (many-to-many) --}}
              <div class="form-group">
                <label for="subscription_plan_ids">Plans d’abonnement associés (optionnel)</label>
                <select
                  id="subscription_plan_ids"
                  name="subscription_plan_ids[]"
                  class="form-control select2 @error('subscription_plan_ids') is-invalid @enderror"
                  multiple
                  data-placeholder="Sélectionner des plans"
                >
                  @php $oldPlans = collect(old('subscription_plan_ids', []))->map(fn($v) => (int)$v); @endphp
                  @foreach($plans as $p)
                    <option value="{{ $p->id }}" {{ $oldPlans->contains($p->id) ? 'selected' : '' }}>
                      {{ $p->name }}
                    </option>
                  @endforeach
                </select>
                @error('subscription_plan_ids') <div class="invalid-feedback">{{ $message }}</div> @enderror
                @error('subscription_plan_ids.*') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                <small class="form-text text-muted">
                  L’offre sera disponible pour les abonnés des plans sélectionnés.
                </small>
              </div>

              <div class="form-row">
                <div class="form-group col-md-4">
                  <label for="job_type">Type d’emploi</label>
                  <select
                    id="job_type"
                    name="job_type"
                    class="form-control @error('job_type') is-invalid @enderror"
                    required
                  >
                    @foreach($types as $t)
                      <option value="{{ $t }}" {{ old('job_type')===$t ? 'selected' : '' }}>
                        {{ str_replace('_',' ',ucfirst($t)) }}
                      </option>
                    @endforeach
                  </select>
                  @error('job_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group col-md-4">
                  <label for="experience_level">Niveau d’expérience</label>
                  <select
                    id="experience_level"
                    name="experience_level"
                    class="form-control @error('experience_level') is-invalid @enderror"
                    required
                  >
                    @foreach($levels as $lvl)
                      <option value="{{ $lvl }}" {{ old('experience_level')===$lvl ? 'selected' : '' }}>
                        {{ ucfirst($lvl) }}
                      </option>
                    @endforeach
                  </select>
                  @error('experience_level') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group col-md-4">
                  <label for="application_deadline">Date limite (optionnel)</label>
                  <input
                    type="date"
                    id="application_deadline"
                    name="application_deadline"
                    class="form-control @error('application_deadline') is-invalid @enderror"
                    value="{{ old('application_deadline') }}"
                  >
                  @error('application_deadline') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>

              <div class="form-group">
                <label for="description">Description</label>
                <textarea
                  id="description"
                  name="description"
                  rows="4"
                  class="form-control @error('description') is-invalid @enderror"
                  required
                >{{ old('description') }}</textarea>
                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="requirements">Exigences (optionnel)</label>
                  <textarea
                    id="requirements"
                    name="requirements"
                    rows="3"
                    class="form-control @error('requirements') is-invalid @enderror"
                  >{{ old('requirements') }}</textarea>
                  @error('requirements') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group col-md-6">
                  <label for="responsibilities">Responsabilités (optionnel)</label>
                  <textarea
                    id="responsibilities"
                    name="responsibilities"
                    rows="3"
                    class="form-control @error('responsibilities') is-invalid @enderror"
                  >{{ old('responsibilities') }}</textarea>
                  @error('responsibilities') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>

              <div class="form-row">
                <div class="form-group col-md-4">
                  <label for="location">Localisation (adresse)</label>
                  <input
                    type="text"
                    id="location"
                    name="location"
                    class="form-control @error('location') is-invalid @enderror"
                    value="{{ old('location') }}"
                    required
                  >
                  @error('location') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group col-md-4">
                  <label for="city">Ville</label>
                  <input
                    type="text"
                    id="city"
                    name="city"
                    class="form-control @error('city') is-invalid @enderror"
                    value="{{ old('city') }}"
                    required
                  >
                  @error('city') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group col-md-4">
                  <label for="governorate">Gouvernorat</label>
                  <input
                    type="text"
                    id="governorate"
                    name="governorate"
                    class="form-control @error('governorate') is-invalid @enderror"
                    value="{{ old('governorate') }}"
                    required
                  >
                  @error('governorate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>

              <div class="form-row">
                <div class="form-group col-md-3">
                  <label for="salary_min">Salaire min. (optionnel)</label>
                  <input
                    type="number"
                    step="0.001"
                    min="0"
                    id="salary_min"
                    name="salary_min"
                    class="form-control @error('salary_min') is-invalid @enderror"
                    value="{{ old('salary_min') }}"
                  >
                  @error('salary_min') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group col-md-3">
                  <label for="salary_max">Salaire max. (optionnel)</label>
                  <input
                    type="number"
                    step="0.001"
                    min="0"
                    id="salary_max"
                    name="salary_max"
                    class="form-control @error('salary_max') is-invalid @enderror"
                    value="{{ old('salary_max') }}"
                  >
                  @error('salary_max') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group col-md-2">
                  <label for="currency">Devise</label>
                  <input
                    type="text"
                    id="currency"
                    name="currency"
                    class="form-control @error('currency') is-invalid @enderror"
                    value="{{ old('currency','TND') }}"
                    maxlength="3"
                  >
                  @error('currency') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group col-md-2 d-flex align-items-center">
                  <div class="form-check mt-4">
                    <input
                      type="checkbox"
                      id="remote_allowed"
                      name="remote_allowed"
                      value="1"
                      class="form-check-input"
                      {{ old('remote_allowed') ? 'checked' : '' }}
                    >
                    <label for="remote_allowed" class="form-check-label">Télétravail autorisé</label>
                  </div>
                </div>

                <div class="form-group col-md-2 d-flex align-items-center">
                  <div class="form-check mt-4">
                    <input
                      type="checkbox"
                      id="is_featured"
                      name="is_featured"
                      value="1"
                      class="form-check-input"
                      {{ old('is_featured') ? 'checked' : '' }}
                    >
                    <label for="is_featured" class="form-check-label">Mettre en vedette</label>
                  </div>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="skills_required">Compétences requises (optionnel)</label>
                  <textarea
                    id="skills_required"
                    name="skills_required"
                    rows="3"
                    class="form-control @error('skills_required') is-invalid @enderror"
                    placeholder='JSON ou liste: [ "PHP", "Laravel" ]  OU  PHP, Laravel'
                  >{{ old('skills_required') }}</textarea>
                  @error('skills_required') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  <small class="form-text text-muted">
                    Accepte un JSON (ex. <code>["PHP","Laravel"]</code>) ou une liste séparée par virgules/retours à la ligne.
                  </small>
                </div>

                <div class="form-group col-md-6">
                  <label for="benefits">Avantages (optionnel)</label>
                  <textarea
                    id="benefits"
                    name="benefits"
                    rows="3"
                    class="form-control @error('benefits') is-invalid @enderror"
                    placeholder="JSON ou liste: Tickets Restaurant, Assurance santé…"
                  >{{ old('benefits') }}</textarea>
                  @error('benefits') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>

              <div class="form-group">
                <label for="status">Statut</label>
                <select
                  id="status"
                  name="status"
                  class="form-control @error('status') is-invalid @enderror"
                >
                  @foreach($statuses as $st)
                    <option value="{{ $st }}" {{ old('status','draft')===$st ? 'selected' : '' }}>
                      {{ ucfirst($st) }}
                    </option>
                  @endforeach
                </select>
                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              <button type="submit" class="btn btn-primary">Enregistrer</button>
              <a href="{{ route('job-offers.index') }}" class="btn btn-secondary">Annuler</a>
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
