<!doctype html>
<html lang="en">
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
   <title>Allo work infinity - Liste des plans d’abonnement</title>
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
                        <h4 class="card-title">Liste des plans d’abonnement</h4>
                        </div>
                        <div class="col-sm-12 col-md-6 text-right">
                        <a href="{{ route('subscription-plans.create') }}" class="btn btn-primary">Ajouter un plan</a>
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
                                <input type="text" id="searchInput" class="form-control" placeholder="Rechercher (nom, description, statut)…">
                            </div>
                            </div>
                        </div>

                        <div id="planTableContainer">
                            <table class="table table-bordered table-striped mt-4" id="planTable">
                            <thead>
                                <tr>
                                <th>Nom</th>
                                <th>Prix</th>
                                <th>Durée (jours)</th>
                                <th>Statut</th>
                                <th style="width: 220px;">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            </table>
                            <nav>
                            <ul class="pagination justify-content-end" id="pagination"></ul>
                            </nav>
                        </div>
                        </div>

                    </div>
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
let allPlans = [];
let currentPage = 1;
const perPage = 5;

function fetchPlans() {
  $.ajax({
    url: "{{ route('subscription-plans.data') }}",
    method: "GET",
    success: function (data) {
      allPlans = Array.isArray(data) ? data : [];
      currentPage = 1;
      renderFilteredPlans();
    },
    error: function () {
      alert("Erreur lors du chargement des plans.");
    }
  });
}

function renderFilteredPlans() {
  const search = ($('#searchInput').val() || '').toLowerCase();

  const filtered = allPlans.filter(plan => {
    const name = (plan.name || '').toLowerCase();
    const description = (plan.description || '').toLowerCase();
    const status = (plan.status || '').toLowerCase();  // active | inactive
    const priceStr = String(plan.price || '').toLowerCase();
    const durStr = String(plan.duration_days || '').toLowerCase();

    return name.includes(search)
        || description.includes(search)
        || status.includes(search)
        || priceStr.includes(search)
        || durStr.includes(search);
  });

  renderTable(filtered);
  renderPagination(filtered);
}

function renderTable(plans) {
  const start = (currentPage - 1) * perPage;
  const end = start + perPage;
  const paginated = plans.slice(start, end);

  const csrf = $('meta[name="csrf-token"]').attr('content');
  let html = '';

  if (paginated.length === 0) {
    html = `<tr><td colspan="5" class="text-center">Aucun plan trouvé.</td></tr>`;
  } else {
    paginated.forEach(plan => {
      const statusClass = plan.status === 'active' ? 'success' : 'secondary';
      html += `
        <tr>
          <td>
            <strong>${escapeHtml(plan.name || '')}</strong>
            <div class="text-muted small">${escapeHtml(plan.description || '')}</div>
          </td>
          <td>${formatPrice(plan.price)}</td>
          <td>${Number(plan.duration_days || 0)}</td>
          <td><span class="badge badge-${statusClass}">${capitalize(plan.status || '')}</span></td>
          <td>
            <div class="d-flex flex-wrap" style="gap: 0.5rem;">
              <!-- Edit -->
              <a href="/subscription-plans/${plan.id}/edit"
                class="iq-bg-primary btn btn-sm"
                data-toggle="tooltip" data-placement="top" title="Modifier">
                <i class="ri-pencil-line"></i>
              </a>

              <!-- Toggle active -->
              <form action="/subscription-plans/${plan.id}/toggle" method="POST" style="display:inline-block;">
                <input type="hidden" name="_token" value="${csrf}">
                <input type="hidden" name="_method" value="PATCH">
                <button type="submit"
                        class="iq-bg-primary btn btn-sm"
                        data-toggle="tooltip" data-placement="top"
                        title="${(plan.status ? plan.status === 'active' : plan.is_active) ? 'Désactiver' : 'Activer'}">
                  <i class="${(plan.status ? plan.status === 'active' : plan.is_active) ? 'ri-toggle-line' : 'ri-toggle-fill'}"></i>
                </button>
              </form>

              <!-- Delete -->
              <form action="/subscription-plans/${plan.id}" method="POST" style="display:inline-block;"
                    onsubmit="return confirm('Supprimer ce plan ?');">
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

  $('#planTable tbody').html(html);
  $('[data-toggle="tooltip"]').tooltip();
}

function renderPagination(plans) {
  const totalPages = Math.max(1, Math.ceil(plans.length / perPage));
  currentPage = Math.min(currentPage, totalPages);

  let html = '';
  html += `
    <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
      <a class="page-link" href="#" data-page="${currentPage - 1}">Précédent</a>
    </li>
  `;
  html += `
    <li class="page-item active">
      <a class="page-link" href="#">${currentPage}</a>
    </li>
  `;
  html += `
    <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
      <a class="page-link" href="#" data-page="${currentPage + 1}">Suivant</a>
    </li>
  `;

  $('#pagination').html(html);
}

function capitalize(text) {
  return text ? text.charAt(0).toUpperCase() + text.slice(1) : '';
}

function formatPrice(val) {
  const n = Number(val || 0);
  return n.toLocaleString(undefined, { minimumFractionDigits: 3, maximumFractionDigits: 3 });
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
    renderFilteredPlans();
  }
});

$('#searchInput').on('keyup', function () {
  currentPage = 1;
  renderFilteredPlans();
});

$(function () { fetchPlans(); });
</script>
</body>
</html>
