<!doctype html>
<html lang="en">
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
   <title>Allo work infinity - Ajouter un paramètre </title>
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
                <h4 class="card-title">Ajouter un paramètre</h4>
                </div>
                <div class="col-sm-12 col-md-6 text-right">
                <a href="{{ route('system-settings.index') }}" class="btn btn-secondary">← Retour à la liste</a>
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

                <form method="POST" action="{{ route('system-settings.store') }}" novalidate id="settingForm">
                @csrf

                <div class="form-row">
                    <div class="form-group col-md-6">
                    <label for="key">Clé</label>
                    <input
                        type="text"
                        id="key"
                        name="key"
                        class="form-control @error('key') is-invalid @enderror"
                        value="{{ old('key') }}"
                        required
                        maxlength="100"
                    >
                    @error('key') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group col-md-6">
                    <label for="data_type">Type de donnée</label>
                    <select
                        id="data_type"
                        name="data_type"
                        class="form-control @error('data_type') is-invalid @enderror"
                        required
                    >
                        @foreach($types as $t)
                        <option value="{{ $t }}" {{ old('data_type')===$t ? 'selected' : '' }}>
                            {{ ucfirst($t) }}
                        </option>
                        @endforeach
                    </select>
                    @error('data_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Hidden "value" to satisfy validation; filled from the visible field via JS --}}
                <input type="hidden" name="value" id="value_hidden" value="{{ old('value') }}">

                {{-- STRING --}}
                <div class="form-group" data-type="string">
                    <label for="value_string">Valeur (texte)</label>
                    <textarea
                    id="value_string"
                    rows="3"
                    class="form-control @error('value') is-invalid @enderror"
                    placeholder="Texte…"
                    >{{ old('data_type')==='string' ? old('value') : '' }}</textarea>
                    @error('value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- INTEGER --}}
                <div class="form-group" data-type="integer" style="display:none;">
                    <label for="value_int">Valeur (entier)</label>
                    <input
                    type="number"
                    id="value_int"
                    name="value_int"
                    class="form-control @error('value') is-invalid @enderror"
                    value="{{ old('data_type')==='integer' ? old('value_int', old('value')) : '' }}"
                    step="1"
                    >
                    @error('value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- BOOLEAN --}}
                <div class="form-group" data-type="boolean" style="display:none;">
                    <div class="form-check mt-2">
                    <input
                        type="checkbox"
                        id="value_bool"
                        name="value_bool"
                        class="form-check-input"
                        {{ old('data_type')==='boolean' ? ( old('value_bool', old('value')) ? 'checked' : '' ) : '' }}
                        value="1"
                    >
                    <label for="value_bool" class="form-check-label">Valeur booléenne (vrai/faux)</label>
                    </div>
                </div>

                {{-- JSON --}}
                <div class="form-group" data-type="json" style="display:none;">
                    <label for="value_json">Valeur (JSON)</label>
                    <textarea
                    id="value_json"
                    name="value_json"
                    rows="4"
                    class="form-control @error('value') is-invalid @enderror"
                    placeholder='["A","B"] ou {"key":"val"}  —  (ou liste séparée par virgules/retours à la ligne)'
                    >{{ old('data_type')==='json' ? old('value_json', old('value')) : '' }}</textarea>
                    @error('value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="description">Description (optionnel)</label>
                    <textarea
                    id="description"
                    name="description"
                    rows="2"
                    class="form-control @error('description') is-invalid @enderror"
                    >{{ old('description') }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group form-check">
                    <input
                    type="checkbox"
                    id="is_public"
                    name="is_public"
                    value="1"
                    class="form-check-input"
                    {{ old('is_public') ? 'checked' : '' }}
                    >
                    <label for="is_public" class="form-check-label">Paramètre public</label>
                </div>

                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="{{ route('system-settings.index') }}" class="btn btn-secondary">Annuler</a>
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
(function() {
  const typeSelect   = document.getElementById('data_type');
  const valueHidden  = document.getElementById('value_hidden');
  const blocks       = {
    string:  document.querySelector('[data-type="string"]'),
    integer: document.querySelector('[data-type="integer"]'),
    boolean: document.querySelector('[data-type="boolean"]'),
    json:    document.querySelector('[data-type="json"]'),
  };
  const inputs = {
    string:  document.getElementById('value_string'),
    integer: document.getElementById('value_int'),
    boolean: document.getElementById('value_bool'),
    json:    document.getElementById('value_json'),
  };

  function showFor(type) {
    Object.keys(blocks).forEach(k => {
      blocks[k].style.display = (k === type) ? '' : 'none';
    });
  }

  function syncHidden() {
    const t = typeSelect.value;
    switch (t) {
      case 'integer':
        valueHidden.value = inputs.integer.value ?? '';
        break;
      case 'boolean':
        valueHidden.value = inputs.boolean.checked ? '1' : '0';
        break;
      case 'json':
        valueHidden.value = inputs.json.value ?? '';
        break;
      default:
        valueHidden.value = inputs.string.value ?? '';
    }
  }

  // Init display (respect old('data_type'))
  showFor(typeSelect.value || 'string');
  syncHidden();

  typeSelect.addEventListener('change', function () {
    showFor(this.value);
    syncHidden();
  });

  // Keep hidden "value" in sync live
  Object.values(inputs).forEach(el => {
    (el.type === 'checkbox' ? 'change' : 'input');
    el.addEventListener(el.type === 'checkbox' ? 'change' : 'input', syncHidden);
  });

  // Final sync on submit
  document.getElementById('settingForm').addEventListener('submit', syncHidden);
})();
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
