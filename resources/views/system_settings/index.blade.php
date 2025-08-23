<!doctype html>
<html lang="en">
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
   <title>Allo work infinity - Liste des paramètres</title>
   <link rel="shortcut icon" href="/images/favicon.ico" />
   <link rel="stylesheet" href="/css/bootstrap.min.css">
   <link rel="stylesheet" href="/css/typography.css">
   <link rel="stylesheet" href="/css/style.css">
   <link rel="stylesheet" href="/css/responsive.css">
   <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
   {{-- <div id="loading"><div id="loading-center"></div></div> --}}
   <div class="wrapper">
      @include("layouts.sidebar")
      @include("layouts.header")

      {{-- If your main layout already sets the CSRF meta, you can remove this --}}


        <div id="content-page" class="content-page">
        <div class="container-fluid">
            <div class="row">
            <div class="col-sm-12 col-lg-12">
                <div class="iq-card">
                <div class="iq-card-header d-flex justify-content-between">
                    <div class="iq-header-title">
                    <h4 class="card-title">Liste des paramètres</h4>
                    </div>
                    <div class="col-sm-12 col-md-6 text-right">
                    <a href="{{ route('system-settings.create') }}" class="btn btn-primary">Ajouter un paramètre</a>
                    </div>
                </div>

                <div class="iq-card-body">
                    @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="table-responsive">
                    <div class="row justify-content-between mb-2">
                        <div class="col-sm-12 col-md-6">
                        <div class="form-group mb-0">
                            <input type="text" id="searchInput" class="form-control"
                            placeholder="Rechercher (clé, description, type, valeur, public)…">
                        </div>
                        </div>
                    </div>

                    <div id="settingTableContainer">
                        <table class="table table-bordered table-striped mt-4" id="settingTable">
                        <thead>
                            <tr>
                            <th>Clé / Description</th>
                            <th>Type</th>
                            <th>Valeur</th>
                            <th>Public</th>
                            <th>Créée / Modifiée</th>
                            <th style="width: 320px;">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        </table>
                        <nav>
                        <ul class="pagination justify-content-end" id="pagination"></ul>
                        </nav>
                    </div>
                    </div>

                </div> <!-- /.iq-card-body -->
                </div>
            </div>
            </div>
        </div>
        </div>




      @include("layouts.footer")
   </div>



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
      <!-- lottie JavaScript -->
      <script src="/js/lottie.js"></script>
      <!-- am core JavaScript -->
      <script src="/js/core.js"></script>
      <!-- am charts JavaScript -->
      <script src="/js/charts.js"></script>
      <!-- am animated JavaScript -->
      <script src="/js/animated.js"></script>
      <!-- am kelly JavaScript -->
      <script src="/js/kelly.js"></script>
      <!-- am maps JavaScript -->
      <script src="/js/maps.js"></script>
      <!-- am worldLow JavaScript -->
      <script src="/js/worldLow.js"></script>
      <!-- Raphael-min JavaScript -->
      <script src="/js/raphael-min.js"></script>
      <!-- Morris JavaScript -->
      <script src="/js/morris.js"></script>
      <!-- Morris min JavaScript -->
      <script src="/js/morris.min.js"></script>
      <!-- Flatpicker Js -->
      <script src="/js/flatpickr.js"></script>
      <!-- Style Customizer -->
      <script src="/js/style-customizer.js"></script>
      <!-- Chart Custom JavaScript -->
      <script src="/js/chart-custom.js"></script>
      <!-- Custom JavaScript -->
      <script src="/js/custom.js"></script>
<script>
let allSettings = [];
let currentPage = 1;
const perPage = 5;

function fetchSettings() {
  $.ajax({
    url: "{{ route('system-settings.data') }}",
    method: "GET",
    success: function (data) {
      allSettings = Array.isArray(data) ? data : [];
      currentPage = 1;
      renderFilteredSettings();
    },
    error: function () {
      alert("Erreur lors du chargement des paramètres.");
    }
  });
}

function renderFilteredSettings() {
  const search = ($('#searchInput').val() || '').toLowerCase();

  const filtered = allSettings.filter(s => {
    const key   = (s.key || '').toLowerCase();
    const desc  = (s.description || '').toLowerCase();
    const type  = (s.data_type || '').toLowerCase();
    const value = (s.value_text || '').toLowerCase();
    const pub   = s.is_public ? 'oui public true' : 'non privé false';

    return key.includes(search)
        || desc.includes(search)
        || type.includes(search)
        || value.includes(search)
        || pub.includes(search);
  });

  renderTable(filtered);
  renderPagination(filtered);
}

function renderTable(settings) {
  const start = (currentPage - 1) * perPage;
  const end = start + perPage;
  const paginated = settings.slice(start, end);

  const csrf = $('meta[name="csrf-token"]').attr('content');
  let html = '';

  if (paginated.length === 0) {
    html = `<tr><td colspan="6" class="text-center">Aucun paramètre trouvé.</td></tr>`;
  } else {
    paginated.forEach(s => {
      const publicClass = s.is_public ? 'success' : 'secondary';
      const publicText  = s.is_public ? 'Oui' : 'Non';

      html += `
        <tr>
          <td>
            <strong>${escapeHtml(s.key || '')}</strong>
            ${s.description ? `<div class="text-muted small">${escapeHtml(s.description)}</div>` : ''}
          </td>
          <td>${escapeHtml(capitalize(s.data_type || ''))}</td>
          <td>
            ${s.value_text ? `<div class="text-monospace small">${escapeHtml(s.value_text)}</div>` : '—'}
          </td>
          <td><span class="badge badge-${publicClass}">${publicText}</span></td>
          <td>
            ${escapeHtml(s.created_at || '')}
            ${s.updated_at ? `<div class="text-muted small">→ ${escapeHtml(s.updated_at)}</div>` : ''}
          </td>
          <td>
            <div class="d-flex flex-wrap" style="gap: 0.5rem;">
              <!-- Edit -->
              <a href="/system-settings/${s.id}/edit"
                class="iq-bg-primary btn btn-sm"
                data-toggle="tooltip" data-placement="top" title="Modifier">
                <i class="ri-pencil-line"></i>
              </a>

              <!-- Toggle public/private -->
              <form action="/system-settings/${s.id}/toggle-public" method="POST" style="display:inline-block;">
                <input type="hidden" name="_token" value="${csrf}">
                <input type="hidden" name="_method" value="PATCH">
                <button type="submit"
                        class="iq-bg-primary btn btn-sm"
                        data-toggle="tooltip" data-placement="top"
                        title="${s.is_public ? 'Rendre privé' : 'Rendre public'}">
                  <i class="${s.is_public ? 'ri-eye-off-line' : 'ri-eye-line'}"></i>
                </button>
              </form>

              <!-- Delete -->
              <form action="/system-settings/${s.id}" method="POST" style="display:inline-block;"
                    onsubmit="return confirm('Supprimer ce paramètre ?');">
                <input type="hidden" name="_token" value="${csrf}">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit"
                        class="iq-bg-primary btn btn-sm"
                        data-toggle="tooltip" data-placement="top" title="Supprimer">
                  <i class="ri-delete-bin-line"></i>
                </button>
              </form>
            </div>
          </td>

        </tr>`;
    });
  }

  $('#settingTable tbody').html(html);
  $('[data-toggle="tooltip"]').tooltip();
}

function renderPagination(items) {
  const totalPages = Math.max(1, Math.ceil(items.length / perPage));
  currentPage = Math.min(currentPage, totalPages);

  let html = '';
  html += `
    <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
      <a class="page-link" href="#" data-page="${currentPage - 1}">Précédent</a>
    </li>`;
  html += `
    <li class="page-item active">
      <a class="page-link" href="#">${currentPage}</a>
    </li>`;
  html += `
    <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
      <a class="page-link" href="#" data-page="${currentPage + 1}">Suivant</a>
    </li>`;

  $('#pagination').html(html);
}

function capitalize(text) {
  return text ? text.charAt(0).toUpperCase() + text.slice(1) : '';
}

function escapeHtml(s) {
  return (s || '').replace(/[&<>"'`=\/]/g, function (c) {
    return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#x2F;','`':'&#x60;','=':'&#x3D;'}[c];
  });
}

$(document).on('click', '#pagination a', function (e) {
  e.preventDefault();
  const next = parseInt($(this).data('page'), 10);
  if (!isNaN(next)) {
    currentPage = next;
    renderFilteredSettings();
  }
});

$('#searchInput').on('keyup', function () {
  currentPage = 1;
  renderFilteredSettings();
});

$(function () { fetchSettings(); });
</script>
</body>
</html>
