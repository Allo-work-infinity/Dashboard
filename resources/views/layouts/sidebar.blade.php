@php
use Illuminate\Support\Facades\Auth;
use App\Models\Category; // ✅ ajouté

/** Helpers as closures (no global functions = no redeclare) */
$isActive = function ($patterns): string {
    foreach ((array) $patterns as $p) {
        if (request()->is($p)) return 'active';
    }
    return '';
};
$isOpen = function ($patterns): string {
    foreach ((array) $patterns as $p) {
        if (request()->is($p)) return 'show';
    }
    return '';
};

$user    = Auth::user();
$isAdmin = $user && ($user->is_admin ?? false);
@endphp

<div class="iq-sidebar">
  <div class="iq-navbar-logo d-flex justify-content-between">
    <a href="{{ route('dashboard') }}" class="header-logo">
      <img src="/images/logo.png" class="img-fluid rounded" alt="logo">
      <span>Allo work infinity</span>
    </a>
    <div class="iq-menu-bt align-self-center">
      <div class="wrapper-menu">
        <div class="main-circle"><i class="ri-menu-line"></i></div>
        <div class="hover-circle"><i class="ri-close-fill"></i></div>
      </div>
    </div>
  </div>

  <div id="sidebar-scrollbar">
    <nav class="iq-sidebar-menu">
      <ul id="iq-sidebar-toggle" class="iq-menu">

        {{-- Tableau de bord --}}
        @auth
        <li class="{{ $isActive('dashboard') }}">
          <a href="{{ route('dashboard') }}" class="iq-waves-effect">
            <i class="las la-home iq-arrow-left"></i><span>Tableau de bord</span>
          </a>
        </li>
        @endauth

        {{-- Utilisateurs (admin) --}}
        @if($isAdmin)
        <li class="{{ $isActive('users*') }}">
          <a href="#users-submenu" class="iq-waves-effect collapsed" data-toggle="collapse"
             aria-expanded="{{ request()->is('users*') ? 'true' : 'false' }}">
            <i class="ri-user-3-line iq-arrow-left"></i><span>Utilisateurs</span>
            <i class="ri-arrow-right-s-line iq-arrow-right"></i>
          </a>
          <ul id="users-submenu" class="iq-submenu collapse {{ $isOpen('users*') }}" data-parent="#iq-sidebar-toggle">
            <li><a href="{{ route('users.index') }}"><i class="ri-list-unordered"></i> Liste des utilisateurs</a></li>
            <li><a href="{{ route('users.create') }}"><i class="ri-add-fill"></i> Ajouter un utilisateur</a></li>
          </ul>
        </li>
        @endif

        {{-- Plans d’abonnement (admin) --}}
        @if($isAdmin)
        <li class="{{ $isActive('subscription-plans*') }}">
          <a href="#plans-submenu" class="iq-waves-effect collapsed" data-toggle="collapse"
             aria-expanded="{{ request()->is('subscription-plans*') ? 'true' : 'false' }}">
            <i class="ri-building-line iq-arrow-left"></i><span>Plans d’abonnement</span>
            <i class="ri-arrow-right-s-line iq-arrow-right"></i>
          </a>
          <ul id="plans-submenu" class="iq-submenu collapse {{ $isOpen('subscription-plans*') }}" data-parent="#iq-sidebar-toggle">
            <li><a href="{{ route('subscription-plans.index') }}"><i class="ri-list-unordered"></i> Liste des plans</a></li>
            <li><a href="{{ route('subscription-plans.create') }}"><i class="ri-add-fill"></i> Ajouter un plan</a></li>
          </ul>
        </li>
        @endif

        {{-- Abonnements utilisateurs (admin) --}}
        @if($isAdmin)
        <li class="{{ $isActive('user-subscriptions*') }}">
          <a href="#user-subs-submenu" class="iq-waves-effect collapsed" data-toggle="collapse"
             aria-expanded="{{ request()->is('user-subscriptions*') ? 'true' : 'false' }}">
            <i class="ri-profile-line iq-arrow-left"></i><span>Abonnements </br>utilisateurs</span>
            <i class="ri-arrow-right-s-line iq-arrow-right"></i>
          </a>
          <ul id="user-subs-submenu" class="iq-submenu collapse {{ $isOpen('user-subscriptions*') }}" data-parent="#iq-sidebar-toggle">
            <li><a href="{{ route('user-subscriptions.index') }}"><i class="ri-list-unordered"></i> Liste des abonnements</a></li>
            <li><a href="{{ route('user-subscriptions.create') }}"><i class="ri-add-fill"></i> Ajouter un abonnement</a></li>
          </ul>
        </li>
        @endif

        {{-- Journaux d’accès (admin) --}}
        {{-- @if($isAdmin)
        <li class="{{ $isActive('user-access-logs*') }}">
          <a href="#access-logs-submenu" class="iq-waves-effect collapsed" data-toggle="collapse"
             aria-expanded="{{ request()->is('user-access-logs*') ? 'true' : 'false' }}">
            <i class="ri-clipboard-line iq-arrow-left"></i><span>Journaux d’accès</span>
            <i class="ri-arrow-right-s-line iq-arrow-right"></i>
          </a>
          <ul id="access-logs-submenu" class="iq-submenu collapse {{ $isOpen('user-access-logs*') }}" data-parent="#iq-sidebar-toggle">
            <li><a href="{{ route('user-access-logs.index') }}"><i class="ri-list-unordered"></i> Liste des journaux</a></li>
            <li><a href="{{ route('user-access-logs.create') }}"><i class="ri-add-fill"></i> Ajouter un journal</a></li>
          </ul>
        </li>
        @endif --}}

        {{-- Entreprises (admin) --}}
        @if($isAdmin)
        <li class="{{ $isActive('companies*') }}">
          <a href="#companies-submenu" class="iq-waves-effect collapsed" data-toggle="collapse"
             aria-expanded="{{ request()->is('companies*') ? 'true' : 'false' }}">
            <i class="ri-building-4-line iq-arrow-left"></i><span>Entreprises</span>
            <i class="ri-arrow-right-s-line iq-arrow-right"></i>
          </a>
          <ul id="companies-submenu" class="iq-submenu collapse {{ $isOpen('companies*') }}" data-parent="#iq-sidebar-toggle">
            <li><a href="{{ route('companies.index') }}"><i class="ri-list-unordered"></i> Liste des entreprises</a></li>
            <li><a href="{{ route('companies.create') }}"><i class="ri-add-fill"></i> Ajouter une entreprise</a></li>
          </ul>
        </li>
        @endif

        {{-- Catégories (admin) --}}
        @if($isAdmin)
        <li class="{{ $isActive('categories*') }}">
          <a href="#categories-submenu" class="iq-waves-effect collapsed" data-toggle="collapse"
             aria-expanded="{{ request()->is('categories*') ? 'true' : 'false' }}">
            <i class="ri-stack-line iq-arrow-left"></i><span>Catégories</span>
            <i class="ri-arrow-right-s-line iq-arrow-right"></i>
          </a>
          <ul id="categories-submenu" class="iq-submenu collapse {{ $isOpen('categories*') }}" data-parent="#iq-sidebar-toggle">
            <li><a href="{{ route('categories.index') }}"><i class="ri-list-unordered"></i> Liste des catégories</a></li>
            <li><a href="{{ route('categories.create') }}"><i class="ri-add-fill"></i> Ajouter une catégorie</a></li>
          </ul>
        </li>
        @endif

        {{-- Offres d’emploi (admin) --}}
        @if($isAdmin)
        <li class="{{ $isActive('job-offers*') }}">
          <a href="#offers-submenu" class="iq-waves-effect collapsed" data-toggle="collapse"
             aria-expanded="{{ request()->is('job-offers*') ? 'true' : 'false' }}">
            <i class="ri-briefcase-line iq-arrow-left"></i><span>Offres d’emploi</span>
            <i class="ri-arrow-right-s-line iq-arrow-right"></i>
          </a>
          <ul id="offers-submenu" class="iq-submenu collapse {{ $isOpen('job-offers*') }}" data-parent="#iq-sidebar-toggle">
            <li><a href="{{ route('job-offers.index') }}"><i class="ri-list-unordered"></i> Liste des offres</a></li>
            <li><a href="{{ route('job-offers.create') }}"><i class="ri-add-fill"></i> Ajouter une offre</a></li>
          </ul>
        </li>
        @endif

        {{-- Candidatures (admin) --}}
        {{-- @if($isAdmin)
        <li class="{{ $isActive('job-applications*') }}">
          <a href="#applications-submenu" class="iq-waves-effect collapsed" data-toggle="collapse"
             aria-expanded="{{ request()->is('job-applications*') ? 'true' : 'false' }}">
            <i class="ri-file-list-3-line iq-arrow-left"></i><span>Candidatures</span>
            <i class="ri-arrow-right-s-line iq-arrow-right"></i>
          </a>
          <ul id="applications-submenu" class="iq-submenu collapse {{ $isOpen('job-applications*') }}" data-parent="#iq-sidebar-toggle">
            <li><a href="{{ route('job-applications.index') }}"><i class="ri-list-unordered"></i> Liste des candidatures</a></li>
            <li><a href="{{ route('job-applications.create') }}"><i class="ri-add-fill"></i> Ajouter une candidature</a></li>
          </ul>
        </li>
        @endif --}}

        {{-- Paiements (admin) --}}
        @if($isAdmin)
        <li class="{{ $isActive('payment-transactions*') }}">
          <a href="#payments-submenu" class="iq-waves-effect collapsed" data-toggle="collapse"
             aria-expanded="{{ request()->is('payment-transactions*') ? 'true' : 'false' }}">
            <i class="ri-wallet-3-line iq-arrow-left"></i><span>Paiements</span>
            <i class="ri-arrow-right-s-line iq-arrow-right"></i>
          </a>
          <ul id="payments-submenu" class="iq-submenu collapse {{ $isOpen('payment-transactions*') }}" data-parent="#iq-sidebar-toggle">
            <li><a href="{{ route('payment-transactions.index') }}"><i class="ri-list-unordered"></i> Liste des transactions</a></li>
            <li><a href="{{ route('payment-transactions.create') }}"><i class="ri-add-fill"></i> Ajouter une transaction</a></li>
          </ul>
        </li>
        @endif

        {{-- Paramètres système (admin) --}}
        @if($isAdmin)
        <li class="{{ $isActive('system-settings*') }}">
          <a href="#settings-submenu" class="iq-waves-effect collapsed" data-toggle="collapse"
             aria-expanded="{{ request()->is('system-settings*') ? 'true' : 'false' }}">
            <i class="ri-settings-3-line iq-arrow-left"></i><span>Paramètres système</span>
            <i class="ri-arrow-right-s-line iq-arrow-right"></i>
          </a>
          <ul id="settings-submenu" class="iq-submenu collapse {{ $isOpen('system-settings*') }}" data-parent="#iq-sidebar-toggle">
            <li><a href="{{ route('system-settings.index') }}"><i class="ri-list-unordered"></i> Tous les paramètres</a></li>
            <li><a href="{{ route('system-settings.create') }}"><i class="ri-add-fill"></i> Ajouter un paramètre</a></li>
          </ul>
        </li>
        @endif

        {{-- Déconnexion --}}
        @auth
        <li>
          <form method="POST" action="{{ route('logout') }}" class="mt-2 px-3">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-secondary w-100">
              <i class="ri-logout-box-r-line mr-1"></i> Déconnexion
            </button>
          </form>
        </li>
        @endauth

      </ul>
    </nav>
    <div class="p-3"></div>
  </div>
</div>
