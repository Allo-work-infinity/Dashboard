<!doctype html>
<html lang="en">
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
   <title>Allo work infinity - Liste des offres d’emploi</title>
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
                        <h4 class="card-title">Liste des offres d’emploi</h4>
                        </div>
                        <div class="col-sm-12 col-md-6 text-right">
                        <a href="{{ route('job-offers.create') }}" class="btn btn-primary">Ajouter une offre</a>
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
                                placeholder="Rechercher (titre, entreprise, type, niveau, ville, statut, vedette)…">
                            </div>
                            </div>
                        </div>

                        <div id="planTableContainer">
                            <table class="table table-bordered table-striped mt-4" id="planTable">
                            <thead>
                                <tr>
                                <th>Titre / Entreprise</th>
                                <th>Type / Niveau</th>
                                <th>Localisation</th>
                                <th>Salaire</th>
                                <th>Statut</th>
                                <th>Vedette</th>
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
let allPlans = []; // = job offers payload
let currentPage = 1;
const perPage = 5;

function fetchPlans() {
  $.ajax({
    url: "{{ route('job-offers.data') }}",
    method: "GET",
    success: function (data) {
      allPlans = Array.isArray(data) ? data : [];
      currentPage = 1;
      renderFilteredPlans();
    },
    error: function () {
      alert("Erreur lors du chargement des offres.");
    }
  });
}

function renderFilteredPlans() {
  const search = ($('#searchInput').val() || '').toLowerCase();

  const filtered = allPlans.filter(o => {
    const title   = (o.title || '').toLowerCase();
    const company = (o.company || '').toLowerCase();
    const type    = (o.job_type || '').toLowerCase();
    const level   = (o.experience_level || '').toLowerCase();
    const city    = (o.city || '').toLowerCase();
    const gov     = (o.governorate || '').toLowerCase();
    const status  = (o.status || '').toLowerCase(); // draft|active|paused|closed
    const featuredStr = o.is_featured ? 'featured vedette yes' : 'not featured no';

    const salaryMin = String(o.salary_min ?? '').toLowerCase();
    const salaryMax = String(o.salary_max ?? '').toLowerCase();
    const currency  = (o.currency || '').toLowerCase();

    return title.includes(search)
        || company.includes(search)
        || type.includes(search)
        || level.includes(search)
        || city.includes(search)
        || gov.includes(search)
        || status.includes(search)
        || featuredStr.includes(search)
        || salaryMin.includes(search)
        || salaryMax.includes(search)
        || currency.includes(search);
  });

  renderTable(filtered);
  renderPagination(filtered);
}

function renderTable(offers) {
  const start = (currentPage - 1) * perPage;
  const end = start + perPage;
  const paginated = offers.slice(start, end);

  const csrf = $('meta[name="csrf-token"]').attr('content');
  let html = '';

  if (paginated.length === 0) {
    html = `<tr><td colspan="7" class="text-center">Aucune offre trouvée.</td></tr>`;
  } else {
    paginated.forEach(o => {
      const statusClass = (function(s) {
        switch ((s || '').toLowerCase()) {
          case 'active': return 'success';
          case 'paused': return 'warning';
          case 'closed': return 'dark';
          default:       return 'secondary'; // draft or anything else
        }
      })(o.status);

      const featuredBadge = o.is_featured
        ? '<span class="badge badge-success">Oui</span>'
        : '<span class="badge badge-secondary">Non</span>';

      const salaryText = formatSalary(o.salary_min, o.salary_max, o.currency);

      // NEW: plan badges (if any)
      let planBadges = '';
      if (Array.isArray(o.subscription_plan_names) && o.subscription_plan_names.length) {
        planBadges = `
          <div class="mt-1" style="gap:.25rem; display:flex; flex-wrap:wrap;">
            ${o.subscription_plan_names.map(n =>
              `<span class="badge badge-info">${escapeHtml(n)}</span>`
            ).join(' ')}
          </div>`;
      }

      // NEW: reference line (if present)
      const referenceLine = o.reference
        ? `<div class="text-muted small">Réf: ${escapeHtml(o.reference)}</div>`
        : '';

      html += `
        <tr>
          <td>
            <strong>${escapeHtml(o.title || '')}</strong>
            <div class="text-muted small">${escapeHtml(o.company || '')}</div>
            ${referenceLine}
            ${o.created_at ? `<div class="text-muted small">Créée le ${escapeHtml(o.created_at)}</div>` : ''}
            ${planBadges}
          </td>
          <td>${escapeHtml(capitalize(o.job_type || ''))} / ${escapeHtml(capitalize(o.experience_level || ''))}</td>
          <td>${escapeHtml(o.city || '')}${o.governorate ? ', ' + escapeHtml(o.governorate) : ''}</td>
          <td>${salaryText}</td>
          <td><span class="badge badge-${statusClass}">${capitalize(o.status || '')}</span></td>
          <td>${featuredBadge}</td>
          <td>
            <div class="d-flex flex-wrap" style="gap: 0.5rem;">
              <!-- Edit -->
              <a href="/job-offers/${o.id}/edit"
                class="iq-bg-primary btn btn-sm"
                data-toggle="tooltip" data-placement="top" title="Modifier">
                <i class="ri-pencil-line"></i>
              </a>

              <!-- Toggle status -->
              <form action="/job-offers/${o.id}/toggle-status" method="POST" style="display:inline-block;">
                <input type="hidden" name="_token" value="${csrf}">
                <input type="hidden" name="_method" value="PATCH">
                <button type="submit"
                        class="iq-bg-primary btn btn-sm"
                        data-toggle="tooltip" data-placement="top" title="Changer statut">
                  <i class="ri-refresh-line"></i>
                </button>
              </form>

              <!-- Toggle featured -->
              <!-- <form action="/job-offers/${o.id}/toggle-featured" method="POST" style="display:inline-block;">
                <input type="hidden" name="_token" value="${csrf}">
                <input type="hidden" name="_method" value="PATCH">
                <button type="submit"
                        class="iq-bg-primary btn btn-sm"
                        data-toggle="tooltip" data-placement="top"
                        title="${o.is_featured ? 'Retirer vedette' : 'Mettre en vedette'}">
                  <i class="${o.is_featured ? 'ri-star-off-line' : 'ri-star-line'}"></i>
                </button>
              </form> -->

              <!-- Delete -->
              <form action="/job-offers/${o.id}" method="POST" style="display:inline-block;"
                    onsubmit="return confirm('Supprimer cette offre ?');">
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

  $('#planTable tbody').html(html); // keep your existing table id
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

function formatSalary(min, max, currency) {
  const c = (currency || 'TND').toUpperCase();
  const hasMin = min !== null && min !== undefined && min !== '';
  const hasMax = max !== null && max !== undefined && max !== '';

  const fmt = v => Number(v).toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 0 });

  if (hasMin && hasMax) return `${fmt(min)} – ${fmt(max)} ${c}`;
  if (hasMin)           return `≥ ${fmt(min)} ${c}`;
  if (hasMax)           return `≤ ${fmt(max)} ${c}`;
  return '—';
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
</script>
</body>
</html>
