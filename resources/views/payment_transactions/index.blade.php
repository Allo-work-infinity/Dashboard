<!doctype html>
<html lang="en">
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
   <title>Allo work infinity - Liste des transactions</title>
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
                    <h4 class="card-title">Liste des transactions</h4>
                    </div>
                    <div class="col-sm-12 col-md-6 text-right">
                    <a href="{{ route('payment-transactions.create') }}" class="btn btn-primary">Ajouter une transaction</a>
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
                            placeholder="Rechercher (utilisateur, email, abonnement, konnect id, statut, méthode)…">
                        </div>
                        </div>
                    </div>

                    <div id="txnTableContainer">
                        <table class="table table-bordered table-striped mt-4" id="txnTable">
                        <thead>
                            <tr>
                            <th>Utilisateur</th>
                            <th>Abonnement</th>
                            <th>Montant</th>
                            <th>Statut / Méthode</th>
                            <th>Créée / Traitée</th>
                            <th style="width: 350px;">Action</th>
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
let allTxns = [];
let currentPage = 1;
const perPage = 5;

function fetchTxns() {
  $.ajax({
    url: "{{ route('payment-transactions.data') }}",
    method: "GET",
    success: function (data) {
      allTxns = Array.isArray(data) ? data : [];
      currentPage = 1;
      renderFilteredTxns();
    },
    error: function () {
      alert("Erreur lors du chargement des transactions.");
    }
  });
}

function renderFilteredTxns() {
  const search = ($('#searchInput').val() || '').toLowerCase();

  const filtered = allTxns.filter(t => {
    const uname   = (t.user_name || '').toLowerCase();
    const email   = (t.user_email || '').toLowerCase();
    const sub     = String(t.subscription_id || '');
    const payId   = (t.konnect_payment_id || '').toLowerCase();
    const txnId   = (t.konnect_transaction_id || '').toLowerCase();
    const status  = (t.status || '').toLowerCase();
    const method  = (t.payment_method || '').toLowerCase();
    const amount  = String(t.amount || '');
    return uname.includes(search)
        || email.includes(search)
        || sub.includes(search)
        || payId.includes(search)
        || txnId.includes(search)
        || status.includes(search)
        || method.includes(search)
        || amount.includes(search);
  });

  renderTable(filtered);
  renderPagination(filtered);
}

function renderTable(txns) {
  const start = (currentPage - 1) * perPage;
  const end = start + perPage;
  const paginated = txns.slice(start, end);

  const csrf = $('meta[name="csrf-token"]').attr('content');
  let html = '';

  if (paginated.length === 0) {
    html = `<tr><td colspan="6" class="text-center">Aucune transaction trouvée.</td></tr>`;
  } else {
    paginated.forEach(t => {
      const s = (t.status || '').toLowerCase();
      const statusClass =
        s === 'completed' ? 'success'  :
        s === 'failed'    ? 'danger'   :
        s === 'cancelled' ? 'secondary': 'warning';

      html += `
        <tr>
          <td>
            <strong>${escapeHtml(t.user_name || '—')}</strong>
            ${t.user_email ? `<div class="text-muted small">${escapeHtml(t.user_email)}</div>` : ''}
          </td>
          <td>
            ${t.subscription_id ? `#${t.subscription_id}` : '—'}
            ${t.konnect_payment_id ? `<div class="text-muted small">Pay: ${escapeHtml(t.konnect_payment_id)}</div>` : ''}
            ${t.konnect_transaction_id ? `<div class="text-muted small">Txn: ${escapeHtml(t.konnect_transaction_id)}</div>` : ''}
          </td>
          <td>
            <strong>${escapeHtml(t.amount_formatted || String(t.amount || '0'))} ${escapeHtml(t.currency || '')}</strong>
          </td>
          <td>
            <span class="badge badge-${statusClass}">${capitalize(t.status || '')}</span>
            ${t.payment_method ? `<div class="text-muted small">${escapeHtml(t.payment_method)}</div>` : ''}
          </td>
          <td>
            ${escapeHtml(t.created_at || '')}
            ${t.processed_at ? `<div class="text-muted small">→ ${escapeHtml(t.processed_at)}</div>` : ''}
          </td>
          <td>
          <div class="d-flex flex-wrap" style="gap: 0.5rem;">
            <!-- Edit -->
            <a href="/payment-transactions/${t.id}/edit"
              class="iq-bg-primary btn btn-sm"
              data-toggle="tooltip" data-placement="top" title="Modifier">
              <i class="ri-pencil-line"></i>
            </a>

            <!-- Mark Completed -->
            <form action="/payment-transactions/${t.id}/set-status" method="POST" style="display:inline-block;">
              <input type="hidden" name="_token" value="${csrf}">
              <input type="hidden" name="_method" value="PATCH">
              <input type="hidden" name="status" value="completed">
              <button type="submit"
                      class="iq-bg-primary btn btn-sm"
                      data-toggle="tooltip" data-placement="top" title="Marquer complétée">
                <i class="ri-check-double-line"></i>
              </button>
            </form>

            <!-- Mark Failed -->
            <form action="/payment-transactions/${t.id}/set-status" method="POST" style="display:inline-block;">
              <input type="hidden" name="_token" value="${csrf}">
              <input type="hidden" name="_method" value="PATCH">
              <input type="hidden" name="status" value="failed">
              <button type="submit"
                      class="iq-bg-primary btn btn-sm"
                      data-toggle="tooltip" data-placement="top" title="Marquer échouée">
                <i class="ri-close-circle-line"></i>
              </button>
            </form>

            <!-- Delete -->
            <form action="/payment-transactions/${t.id}" method="POST" style="display:inline-block;"
                  onsubmit="return confirm('Supprimer cette transaction ?');">
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

  $('#txnTable tbody').html(html);
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
    renderFilteredTxns();
  }
});

$('#searchInput').on('keyup', function () {
  currentPage = 1;
  renderFilteredTxns();
});

$(function () { fetchTxns(); });
</script>
</body>
</html>
