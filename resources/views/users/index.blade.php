<!doctype html>
<html lang="en">
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
   <title>Allo work infinity - Utilisateurs</title>
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
            <div class="col-sm-12">
                <div class="iq-card">
                <div class="iq-card-header d-flex justify-content-between">
                    <div class="iq-header-title">
                    <h4 class="card-title">Liste des utilisateurs</h4>
                    </div>
                    <div class="col-sm-12 col-md-6 text-right">
                    <a href="{{ route('users.create') }}" class="btn btn-primary">Ajouter utilisateur</a>
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
                            <input type="text" id="searchInput" class="form-control" placeholder="Rechercher...">
                        </div>
                        </div>
                    </div>

                    <div id="userTableContainer">
                        <table class="table table-bordered table-striped mt-4" id="userTable">
                        <thead>
                            <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Status</th>
                            <th style="width:160px;">Action</th>
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
let allUsers = [];
let currentPage = 1;
const perPage = 5;

function fetchUsers() {
  $.ajax({
    url: "{{ route('users.data') }}",
    method: "GET",
    success: function (data) {
      allUsers = Array.isArray(data) ? data : [];
      currentPage = 1;
      renderFilteredUsers();
    },
    error: function () {
      alert("Erreur lors du chargement.");
    }
  });
}

function renderFilteredUsers() {
  const search = ($('#searchInput').val() || '').toLowerCase();

  const filtered = allUsers.filter(user => {
    const name = (user.name || '').toLowerCase();
    const email = (user.email || '').toLowerCase();
    const role = (user.role || '').toLowerCase();          // 'user' (forced non-admin)
    const status = (user.status || '').toLowerCase();      // active|suspended|banned
    return name.includes(search) || email.includes(search) || role.includes(search) || status.includes(search);
  });

  renderTable(filtered);
  renderPagination(filtered);
}

function renderTable(users) {
  const start = (currentPage - 1) * perPage;
  const end = start + perPage;
  const paginated = users.slice(start, end);

  const csrf = $('meta[name="csrf-token"]').attr('content');
  let html = '';

  if (paginated.length === 0) {
    html = `<tr><td colspan="5" class="text-center">Aucun utilisateur trouvé.</td></tr>`;
  } else {
    paginated.forEach(user => {
      const statusClass =
        user.status === 'active' ? 'success' :
        (user.status === 'suspended' ? 'warning' :
        (user.status === 'banned' ? 'danger' : 'secondary'));

      html += `
        <tr>
          <td>${escapeHtml(user.name || '')}</td>
          <td>${escapeHtml(user.email || '')}</td>
          <td>${capitalize(user.role || 'user')}</td>
          <td><span class="badge badge-${statusClass}">${capitalize(user.status || '')}</span></td>
          <td>
            <div class="d-flex flex-wrap" style="gap: 0.5rem;">
              <!-- Edit -->
              <a href="/users/${user.id}/edit"
                 class="iq-bg-primary btn btn-sm"
                 data-toggle="tooltip" data-placement="top" title="Modifier">
                <i class="ri-pencil-line"></i>
              </a>

              <!-- Delete -->
              <form action="/users/${user.id}" method="POST" style="display:inline-block;"
                    onsubmit="return confirm('Supprimer cet utilisateur ?');">
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

  $('#userTable tbody').html(html);
}

function renderPagination(users) {
  const totalPages = Math.max(1, Math.ceil(users.length / perPage));
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
    renderFilteredUsers();
  }
});

$('#searchInput').on('keyup', function () {
  currentPage = 1;
  renderFilteredUsers();
});

$(function () { fetchUsers(); });
</script>
</body>
</html>
