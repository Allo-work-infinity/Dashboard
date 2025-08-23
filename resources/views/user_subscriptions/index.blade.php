<!doctype html>
<html lang="en">
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
   <title>Allo work infinity -Liste des abonnements utilisateurs</title>
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
                    <h4 class="card-title">Liste des abonnements utilisateurs</h4>
                    </div>
                    <div class="col-sm-12 col-md-6 text-right">
                    <a href="{{ route('user-subscriptions.create') }}" class="btn btn-primary">Ajouter un abonnement</a>
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
                                placeholder="Rechercher (utilisateur, email, plan, statut, paiement, période, montant)…">
                        </div>
                        </div>
                    </div>

                    <div id="subTableContainer">
                        <table class="table table-bordered table-striped mt-4" id="subTable">
                        <thead>
                            <tr>
                            <th>Utilisateur</th>
                            <th>Plan</th>
                            <th>Montant</th>
                            <th>Période</th>
                            <th>Statuts</th>
                            <th style="width: 280px;">Action</th>
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
let allSubs = [];
let currentPage = 1;
const perPage = 5;

function fetchSubs() {
  $.ajax({
    url: "{{ route('user-subscriptions.data') }}",
    method: "GET",
    success: function (data) {
      allSubs = Array.isArray(data) ? data : [];
      currentPage = 1;
      renderFilteredSubs();
    },
    error: function () {
      alert("Erreur lors du chargement des abonnements.");
    }
  });
}

function renderFilteredSubs() {
  const search = ($('#searchInput').val() || '').toLowerCase();

  const filtered = allSubs.filter(s => {
    const name   = (s.user_name || '').toLowerCase();
    const email  = (s.user_email || '').toLowerCase();
    const plan   = (s.plan_name || '').toLowerCase();
    const st     = (s.status || '').toLowerCase();
    const paySt  = (s.payment_status || '').toLowerCase();
    const start  = (s.start_date || '').toLowerCase();
    const end    = (s.end_date || '').toLowerCase();
    const amount = String(s.amount_paid || '').toLowerCase();
    const auto   = s.auto_renewal ? 'auto renew yes true' : 'no false';

    return name.includes(search)
        || email.includes(search)
        || plan.includes(search)
        || st.includes(search)
        || paySt.includes(search)
        || start.includes(search)
        || end.includes(search)
        || amount.includes(search)
        || auto.includes(search);
  });

  renderTable(filtered);
  renderPagination(filtered);
}

function renderTable(subs) {
  const start = (currentPage - 1) * perPage;
  const end = start + perPage;
  const paginated = subs.slice(start, end);

  const csrf = $('meta[name="csrf-token"]').attr('content');
  let html = '';

  if (paginated.length === 0) {
    html = `<tr><td colspan="6" class="text-center">Aucun abonnement trouvé.</td></tr>`;
  } else {
    paginated.forEach(s => {
      const statusClass =
        (s.status || '').toLowerCase() === 'active'   ? 'success'  :
        (s.status || '').toLowerCase() === 'expired'  ? 'secondary':
        (s.status || '').toLowerCase() === 'cancelled'? 'dark'     : 'warning';

      const payClass =
        (s.payment_status || '').toLowerCase() === 'paid'     ? 'success'  :
        (s.payment_status || '').toLowerCase() === 'pending'  ? 'warning'  :
        (s.payment_status || '').toLowerCase() === 'failed'   ? 'danger'   : 'secondary';

      html += `
        <tr>
          <td>
            <strong>${escapeHtml(s.user_name || '—')}</strong>
            ${s.user_email ? `<div class="text-muted small">${escapeHtml(s.user_email)}</div>` : ''}
          </td>
          <td>
            ${escapeHtml(s.plan_name || '—')}
            ${s.auto_renewal ? `<div class="badge badge-info ml-1">Renouvellement auto</div>` : ''}
          </td>
          <td><strong>${escapeHtml(s.amount_paid ?? '—')}</strong></td>
          <td>
            ${escapeHtml(s.start_date || '—')}
            ${s.end_date ? `<div class="text-muted small">→ ${escapeHtml(s.end_date)}</div>` : ''}
          </td>
          <td>
            <div>
              <span class="badge badge-${statusClass}" style="margin-right:4px;">${capitalize(s.status || '')}</span>
              <span class="badge badge-${payClass}">${capitalize(s.payment_status || '')}</span>
            </div>
          </td>
          <td>
            <div class="d-flex flex-wrap" style="gap: 0.5rem;">
              <!-- View -->
              <a href="/user-subscriptions/${s.id}"
                 class="iq-bg-primary btn btn-sm"
                 data-toggle="tooltip" data-placement="top" title="Voir">
                <i class="ri-eye-line"></i>
              </a>

              <!-- Edit -->
              <a href="/user-subscriptions/${s.id}/edit"
                 class="iq-bg-primary btn btn-sm"
                 data-toggle="tooltip" data-placement="top" title="Modifier">
                <i class="ri-pencil-line"></i>
              </a>

              <!-- Delete -->
              <form action="/user-subscriptions/${s.id}" method="POST" style="display:inline-block;"
                    onsubmit="return confirm('Supprimer cet abonnement ?');">
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

  $('#subTable tbody').html(html);
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
    renderFilteredSubs();
  }
});

$('#searchInput').on('keyup', function () {
  currentPage = 1;
  renderFilteredSubs();
});

$(function () { fetchSubs(); });
</script>
</body>
</html>
