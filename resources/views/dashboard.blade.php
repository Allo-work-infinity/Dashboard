<!doctype html>
<html lang="en">
   <head>
      <!-- Required meta tags -->
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <title>Allo work infinity - Dashboard</title>
      <link rel="shortcut icon" href="/images/favicon.ico" />
      <link rel="stylesheet" href="/css/bootstrap.min.css">
      <link rel="stylesheet" href="/css/typography.css">
      <link rel="stylesheet" href="/css/style.css">
      <link rel="stylesheet" href="/css/responsive.css">
      <link rel="stylesheet" href="/css/flatpickr.min.css">
      <!-- Favicon -->
      <link rel="shortcut icon" href="/images/favicon.ico" />
      <!-- Bootstrap CSS -->
      <link rel="stylesheet" href="/css/bootstrap.min.css">
      <!-- Typography CSS -->
      <link rel="stylesheet" href="/css/typography.css">
      <!-- Style CSS -->
      <link rel="stylesheet" href="/css/style.css">
      <!-- Responsive CSS -->
      <link rel="stylesheet" href="/css/responsive.css">
      <!-- Full calendar -->
      <link href='fullcalendar/core/main.css' rel='stylesheet' />
      <link href='fullcalendar/daygrid/main.css' rel='stylesheet' />
      <link href='fullcalendar/timegrid/main.css' rel='stylesheet' />
      <link href='fullcalendar/list/main.css' rel='stylesheet' />

      <link rel="stylesheet" href="/css/flatpickr.min.css">

   </head>
   <body>
      <!-- loader Start -->
@php
  // Tiny helpers
  $currency = function($v, $cur = 'TND') { return $cur.' '.number_format((float)$v, 3); };
  $badge = function($status) {
      return [
        'pending'   => 'badge-warning',
        'completed' => 'badge-success',
        'failed'    => 'badge-danger',
        'cancelled' => 'badge-secondary',
      ][$status] ?? 'badge-light';
  };
  $growthPill = function($pct) {
      if ($pct === null) return '<span class="text-muted"><b>—</b></span>';
      $cls = $pct >= 0 ? 'text-primary' : 'text-danger';
      $icon = $pct >= 0 ? 'ri-arrow-up-fill' : 'ri-arrow-down-fill';
      return '<span class="'.$cls.'"><b>'.number_format($pct, 2).'% <i class="'.$icon.'"></i></b></span>';
  };
@endphp

<!-- loader Start -->
<div id="loading"><div id="loading-center"></div></div>
<!-- loader END -->

<!-- Wrapper Start -->
<div class="wrapper">
  <!-- Sidebar  -->
  @include("layouts.sidebar")
  <!-- TOP Nav Bar -->
  @include("layouts.header")
  <!-- TOP Nav Bar END -->

  <!-- Page Content  -->
  <div id="content-page" class="content-page">
    <div class="container-fluid">
      <div class="row">
        <!-- Card 1: Total Sales -->
        <div class="col-sm-6 col-md-6 col-lg-3">
          <div class="iq-card iq-card-block iq-card-stretch iq-card-height">
            <div class="iq-card-body iq-box-relative">
              <div class="iq-box-absolute icon iq-icon-box rounded-circle iq-bg-primary">
                <i class="ri-focus-2-line"></i>
              </div>
              <p class="text-secondary">Total Sales</p>
              <div class="d-flex align-items-center justify-content-between">
                <h4><b>{{ $currency($metrics['total_sales']) }}</b></h4>
                <div id="iq-chart-box1"></div>
                {!! $growthPill($growth['total_sales']) !!}
              </div>
            </div>
          </div>
        </div>

        <!-- Card 2: Sales Today -->
        <div class="col-sm-6 col-md-6 col-lg-3">
          <div class="iq-card iq-card-block iq-card-stretch iq-card-height">
            <div class="iq-card-body iq-box-relative">
              <div class="iq-box-absolute icon iq-icon-box rounded-circle iq-bg-danger">
                <i class="ri-pantone-line"></i>
              </div>
              <p class="text-secondary">Sales Today</p>
              <div class="d-flex align-items-center justify-content-between">
                <h4><b>{{ $currency($metrics['sales_today']) }}</b></h4>
                <div id="iq-chart-box2"></div>
                {!! $growthPill($growth['sales_today']) !!}
              </div>
            </div>
          </div>
        </div>

        <!-- Card 3: Open Job Offers -->
        <div class="col-sm-6 col-md-6 col-lg-3">
          <div class="iq-card iq-card-block iq-card-stretch iq-card-height">
            <div class="iq-card-body iq-box-relative">
              <div class="iq-box-absolute icon iq-icon-box rounded-circle iq-bg-success">
                <i class="ri-database-2-line"></i>
              </div>
              <p class="text-secondary">Open Job Offers</p>
              <div class="d-flex align-items-center justify-content-between">
                <h4><b>{{ number_format($metrics['open_job_offers']) }}</b></h4>
                <div id="iq-chart-box3"></div>
                <span class="text-success"><b> Live </b></span>
              </div>
            </div>
          </div>
        </div>

        <!-- Card 4: Active Subscriptions -->
        <div class="col-sm-6 col-md-6 col-lg-3">
          <div class="iq-card iq-card-block iq-card-stretch iq-card-height">
            <div class="iq-card-body iq-box-relative">
              <div class="iq-box-absolute icon iq-icon-box rounded-circle iq-bg-warning">
                <i class="ri-pie-chart-2-line"></i>
              </div>
              <p class="text-secondary">Active Subscriptions</p>
              <div class="d-flex align-items-center justify-content-between">
                <h4><b>{{ number_format($metrics['active_subscriptions']) }}</b></h4>
                <div id="iq-chart-box4"></div>
                <span class="text-warning"><b> + </b></span>
              </div>
            </div>
          </div>
        </div>

        <!-- Spendings / Revenue chart -->
        <div class="col-lg-8">
          <div class="iq-card iq-card-block iq-card-stretch iq-card-height">
            <div class="iq-card-header d-flex justify-content-between">
              <div class="iq-header-title">
                <h4 class="card-title">Spendings Stats</h4>
              </div>
              <div class="iq-card-header-toolbar d-flex align-items-center">
                <ul class="nav nav-pills">
                  <li class="nav-item"><a href="#" class="nav-link active">Revenue</a></li>
                  <li class="nav-item"><a href="#" class="nav-link">—</a></li>
                  <li class="nav-item"><a href="#" class="nav-link">—</a></li>
                </ul>
              </div>
            </div>
            <div class="iq-card-body row m-0 align-items-center pb-0">
              <div class="col-md-8">
                <div id="iq-income-chart"
                     data-series='@json($spendingsSeries)'></div>
              </div>
              <div class="col-md-4">
                <div class="chart-data-block">
                  <h4><b>Total</b></h4>
                  <h2><b>{{ $currency(collect($spendingsSeries)->sum('value')) }}</b></h2>
                  <p>Revenue over last 7 days</p>
                  <div class="chart-box d-flex align-items-center justify-content-between mt-5 mb-5">
                    <div id="iq-chart-boxleft"></div>
                    <div id="iq-chart-boxright"></div>
                  </div>
                  <div class="mt-3 pr-3">
                    <div class="d-flex align-items-center justify-content-between">
                      <div class="d-flex align-items-center">
                        <span class="bg-primary p-1 rounded mr-2"></span>
                        <p class="mb-0">Completed Payments</p>
                      </div>
                      <h6><b>100%</b></h6>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                      <div class="d-flex align-items-center">
                        <span class="bg-danger p-1 rounded mr-2"></span>
                        <p class="mb-0">Refunds/Failures</p>
                      </div>
                      <h6><b>—</b></h6>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Banner -->
        <div class="col-lg-4">
          <div class="iq-card iq-card-block iq-card-stretch iq-card-height banner-image-block-bg position-relative"
               style="background: transparent url(/images/page-img/45.png) no-repeat scroll center bottom; background-size: contain; height: 440px; box-shadow: none;">
          </div>
        </div>

        <!-- Open Invoices (Pending Payments) -->
        <div class="col-lg-8">
          <div class="iq-card iq-card-block iq-card-stretch iq-card-height">
            <div class="iq-card-header d-flex justify-content-between">
              <div class="iq-header-title"><h4 class="card-title">Open Invoices</h4></div>
              <div class="iq-card-header-toolbar d-flex align-items-center">
                <div class="dropdown">
                  <span class="dropdown-toggle text-primary" id="dropdownMenuButton5" data-toggle="dropdown">
                    <i class="ri-more-fill"></i>
                  </span>
                  <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton5">
                    <a class="dropdown-item" href="#"><i class="ri-eye-fill mr-2"></i>View</a>
                    <a class="dropdown-item" href="#"><i class="ri-printer-fill mr-2"></i>Print</a>
                    <a class="dropdown-item" href="#"><i class="ri-file-download-fill mr-2"></i>Download</a>
                  </div>
                </div>
              </div>
            </div>
            <div class="iq-card-body">
              <div class="table-responsive">
                <table class="table mb-0">
                  <thead class="thead-light">
                  <tr>
                    <th scope="col">Customer</th>
                    <th scope="col">Date</th>
                    <th scope="col">Invoice</th>
                    <th scope="col">Amount</th>
                    <th scope="col">Status</th>
                    <th scope="col">Action</th>
                  </tr>
                  </thead>
                  <tbody>
                  @forelse($openInvoices as $inv)
                    <tr>
                      <td>{{ optional($inv->user)->first_name }} {{ optional($inv->user)->last_name }} <br><small class="text-muted">{{ optional($inv->user)->email }}</small></td>
                      <td>{{ $inv->created_at->format('d/m/Y') }}</td>
                      <td>{{ $inv->konnect_payment_id }}</td>
                      <td>{{ $currency($inv->amount, $inv->currency) }}</td>
                      <td><div class="badge badge-pill {{ $badge($inv->status) }}">{{ ucfirst($inv->status) }}</div></td>
                      <td><a href="#" class="text-primary">Send Email</a></td>
                    </tr>
                  @empty
                    <tr><td colspan="6" class="text-center text-muted">No open invoices.</td></tr>
                  @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Right column cards (kept, can be wired later) -->
        <div class="col-lg-4">
          <div class="iq-card iq-card-block iq-card-stretch iq-card-height">
            <div class="iq-card-body">
              <div class="d-flex align-items-center mt-3">
                <div class="icon iq-icon-box rounded iq-bg-danger mr-3">
                  <i class="ri-shopping-bag-line"></i>
                </div>
                <div class="iq-details col-sm-9 p-0">
                  <div class="d-flex align-items-center justify-content-between">
                    <span class="title text-dark">Users</span>
                    <div class="percentage"><b>{{ number_format($metrics['users']) }}</b></div>
                  </div>
                  <div class="d-flex align-items-center justify-content-between">
                    <span class="">Companies</span>
                    <div class="percentage">{{ number_format($metrics['companies']) }}</div>
                  </div>
                  <div class="d-flex align-items-center justify-content-between">
                    <span class="">Applications</span>
                    <div class="percentage">{{ number_format($metrics['applications']) }}</div>
                  </div>
                </div>
              </div>
              <hr class="mt-4 mb-4">
              <div class="d-flex align-items-center">
                <div class="icon iq-icon-box rounded iq-bg-primary mr-3">
                  <i class="ri-hospital-line"></i>
                </div>
                <div class="iq-details col-sm-9 p-0">
                  <div class="d-flex align-items-center justify-content-between">
                    <span class="title text-dark">User Breakdown</span>
                    <div class="percentage"><b>{{ number_format($metrics['users']) }}</b></div>
                  </div>
                  <div class="d-flex align-items-center justify-content-between">
                    <span>Verified</span>
                    <div class="percentage">{{ number_format($userBreakdown['verified']) }}</div>
                  </div>
                  <div class="d-flex align-items-center justify-content-between">
                    <span>Unverified</span>
                    <div class="percentage">{{ number_format($userBreakdown['unverified']) }}</div>
                  </div>
                  <div class="d-flex align-items-center justify-content-between">
                    <span>Admins</span>
                    <div class="percentage">{{ number_format($userBreakdown['admins']) }}</div>
                  </div>
                </div>
              </div>
              <hr class="mt-4 mb-4">
              <div class="d-flex align-items-center">
                <div class="icon iq-icon-box rounded iq-bg-info mr-3">
                  <i class="ri-bank-line"></i>
                </div>
                <div class="iq-details col-sm-9 p-0">
                  <div class="d-flex align-items-center justify-content-between">
                    <span class="title text-dark">Sales (7 days)</span>
                    <div class="percentage"><b>{{ $currency(collect($spendingsSeries)->sum('value')) }}</b></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- User Percentage chart placeholder -->
        <div class="col-lg-4">
          <div class="iq-card iq-card-block iq-card-stretch iq-card-height">
            <div class="iq-card-header d-flex justify-content-between">
              <div class="iq-header-title"><h4 class="card-title">User Percentage</h4></div>
            </div>
            <div class="iq-card-body">
              <div id="home-perfomer-chart"
                   data-user-breakdown='@json($userBreakdown)'></div>
            </div>
          </div>
        </div>

        <!-- Recent users list -->
        <div class="col-lg-4">
          <div class="iq-card iq-card-block iq-card-stretch iq-card-height">
            <div class="iq-card-header d-flex justify-content-between">
              <div class="iq-header-title"><h4 class="card-title">Recent Users</h4></div>
            </div>
            <div class="iq-card-body">
              <ul class="perfomer-lists m-0 p-0">
                @foreach($recentUsers as $u)
                  <li class="d-flex mb-4 align-items-center">
                    <div class="user-img img-fluid">
                      <img src="/images/page-img/29.png" alt="user" class="rounded avatar-40">
                    </div>
                    <div class="media-support-info ml-3">
                      <h6>{{ $u->first_name }} {{ $u->last_name }}</h6>
                      <p class="mb-0 font-size-12">{{ $u->email }}</p>
                    </div>
                    <div class="iq-card-header-toolbar d-flex align-items-center ml-auto">
                      <span class="text-primary"><b>ID #{{ $u->id }}</b></span>
                    </div>
                  </li>
                @endforeach
              </ul>
            </div>
          </div>
        </div>

        <!-- Profit/Loss chart placeholders (unchanged layout) -->
        <div class="col-lg-4">
          <div class="iq-card iq-card-block iq-card-stretch" style="position: relative;">
            <div class="iq-card-body">
              <h6>Graph Profit Margin</h6>
              <h2>—</h2>
            </div>
            <div id="home-profit-chart"></div>
          </div>
          <div class="iq-card iq-card-block iq-card-stretch" style="position: relative;">
            <div class="iq-card-body">
              <h6>Graph Loss Margin</h6>
              <h2>—</h2>
            </div>
            <div id="home-loss-chart"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

      @include("layouts.footer")
      <!-- Footer END -->
     
      <!-- Optional JavaScript -->
      <!-- jQuery first, then Popper.js, then Bootstrap JS -->
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
      <script src="/js/jquery.min.js"></script>
      
      <script src="/js/flatpickr.js"></script>
   </body>
</html>