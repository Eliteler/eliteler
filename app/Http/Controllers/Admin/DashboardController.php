<?php

/*
 |--------------------------------------------------------------------------
 | GoBiz vCard SaaS
 |--------------------------------------------------------------------------
 | Developed by NativeCode © 2021 - https://nativecode.in
 | All rights reserved
 | Unauthorized distribution is prohibited
 |--------------------------------------------------------------------------
*/

namespace App\Http\Controllers\Admin;

use App\Classes\AvailableVersion;
use App\Http\Controllers\Controller;
use App\Services\Admin\DashboardMetricsService;
use App\Services\Admin\MarketplaceCatalog;
use App\Services\PluginManager;
use App\Services\TemplateManager;
use App\Support\Admin\AdminPermissions;
use App\Support\Admin\AdminSharedProps;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(private DashboardMetricsService $metrics)
    {
        $this->middleware('auth');
    }

    // Dashboard
    public function index(Request $request, MarketplaceCatalog $marketplace)
    {
        $period = in_array($request->query('period'), DashboardMetricsService::PERIODS, true)
            ? $request->query('period')
            : DashboardMetricsService::DEFAULT_PERIOD;

        $window = $this->metrics->period($period);

        $permissions = AdminPermissions::for($request->user());
        $features    = AdminSharedProps::features();
        $withRevenue = ! AdminPermissions::hidesRevenue($request->user());

        $props = [
            'period'          => $period,
            'periods'         => DashboardMetricsService::PERIODS,
            'hideRevenue'     => ! $withRevenue,
            // Software update / support notices, for admins who can open Software Updates
            // (lazy: skipped when only the period dependent props are reloaded)
            'systemNotice'    => fn () => ($permissions['software_update'] ?? false) ? (new AvailableVersion)->dashboardStatus() : null,
            'marketplaceNotice' => fn () => $this->marketplaceNotice($marketplace, $permissions),
            // Period dependent (reloaded when the period changes)
            'kpis'            => fn () => $this->metrics->kpis($window, $withRevenue),
            'growthSeries'    => fn () => $this->metrics->growthSeries($window),
            'usage'           => fn () => $this->metrics->usage($window),
            // Current state
            'attention'       => fn () => $this->metrics->attention($permissions, $features, $withRevenue),
            'planMix'         => fn () => $this->metrics->planMix($withRevenue),
            'recentCustomers' => fn () => $this->metrics->recentCustomers(),
            'expiringSoon'    => fn () => $this->metrics->expiringSoon(),
            'links'           => [
                'transactions' => $withRevenue && ($permissions['transactions'] ?? false) ? route('admin.transactions.index') : null,
                'customers'    => ($permissions['customers'] ?? false) ? route('admin.customers', ['status' => 'all']) : null,
                'customer'     => ($permissions['customers'] ?? false) ? route('admin.view.customer', ['id' => '__ID__']) : null,
                'plans'        => ($permissions['plans'] ?? false) ? route('admin.plans') : null,
            ],
        ];

        // Revenue and payment widgets are never sent to admins with revenue hidden
        if ($withRevenue) {
            $props += [
                'revenueSeries'  => fn () => $this->metrics->revenueSeries($window),
                'gateways'       => fn () => $this->metrics->gateways($window),
                'recentPayments' => fn () => $this->metrics->recentPayments(),
            ];
        }

        return Inertia::render('dashboard/index', $props);
    }

    private function marketplaceNotice(MarketplaceCatalog $marketplace, array $permissions): ?array
    {
        $pluginsUrl = empty($permissions['plugins']) ? null : route('admin.plugins');
        $templatesUrl = empty($permissions['web_templates']) ? null : route('admin.web-templates');
        if ($pluginsUrl === null && $templatesUrl === null) {
            return null;
        }

        $catalogs = $marketplace->dashboardCatalogs();
        $pluginCount = 0;
        $templateCount = 0;

        if ($pluginsUrl !== null && $catalogs['plugins'] !== null) {
            $plugins = app(PluginManager::class);
            $plugins->loadPlugins();
            $pluginCount = count($marketplace->availableUpdates('plugins', $catalogs['plugins']['versions'], $plugins->getPlugins()));
        }
        if ($templatesUrl !== null && $catalogs['templates'] !== null) {
            $templates = app(TemplateManager::class);
            $templates->loadTemplates();
            $templateCount = count($marketplace->availableUpdates('templates', $catalogs['templates']['versions'], $templates->getTemplates()));
        }

        if ($pluginCount === 0 && $templateCount === 0) {
            return null;
        }

        return [
            'plugins' => $pluginCount > 0 ? ['count' => $pluginCount, 'url' => $pluginsUrl] : null,
            'templates' => $templateCount > 0 ? ['count' => $templateCount, 'url' => $templatesUrl] : null,
        ];
    }
}
