<?php

namespace App\Http\Middleware;

use App\Models\AddonModule;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckUserPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $routeName = $request->route()->getName();
        $userType = Auth::user()->user_type;

        if ($userType == 4) {

            $user = Auth::user();
            $permissions = DB::table('permissions')->join('roles','roles.id','=','permissions.role_id')->where(['roles.status' => 1, 'permissions.role_id' => $user->role_id])->get();
            $moduleName = '';
            $action = 'view';

            if ($routeName == 'staff.dashboard') {
                $moduleName = 'Dashboard';
            } elseif ($routeName == 'provider.leads') {
                $moduleName = 'Leads';
            } elseif ($routeName == 'provider.transaction') {
                $moduleName = 'Transaction';
            } elseif ($routeName == 'provider.payouts') {
                $moduleName = 'Payout';
            } elseif ($routeName == 'provider.service') {
                $moduleName = 'Service';
            } elseif ($routeName == 'staff.bookinglist') {
                $moduleName = 'Bookings';
            } elseif ($routeName == 'staff.calendar') {
                $moduleName = 'Calendar';
            } elseif ($routeName == 'provider.subscription') {
                $moduleName = 'Subscription';
            } elseif ($routeName == 'provider.reviews') {
                $moduleName = 'Reviews';
            } elseif ($routeName == 'provider.chat' || $routeName == 'provider.chat.with-user') {
                $moduleName = 'Chat';
            } elseif ($routeName == 'provider.notification') {
                $moduleName = 'Notifications';
            } elseif ($routeName == 'provider.branch') {
                $moduleName = 'Branch';
            } elseif ($routeName == 'provider.staff') {
                $moduleName = 'Staff';
            } elseif ($routeName == 'provider.roles-permissions') {
                $moduleName = 'Roles & Permission';
            } elseif ($routeName == 'provider.profile') {
                $moduleName = 'Profile Settings';
            } elseif ($routeName == 'provider.security') {
                $moduleName = 'Security Settings';
            } elseif ($routeName == 'provider.subscriptionhistory') {
                $moduleName = 'Plan & Billings';
            } elseif ($routeName == 'staff.ticket') {
                $moduleName = 'Tickets';
            } elseif ($routeName == 'staff.ticket_details') {
                $moduleName = 'Tickets';
            } elseif ($routeName == 'provider.addbrancch') {
                $moduleName = 'Branch';
                $action = 'create';
            } elseif ($routeName == 'provider.editbrancch') {
                $moduleName = 'Branch';
                $action = 'edit';
            } elseif ($routeName == 'provider.coupon') {
                $moduleName = 'Coupon';
            } elseif ($routeName == 'provider.create-coupon') {
                $moduleName = 'Coupon';
                $action = 'create';
            } elseif ($routeName == 'provider.edit-coupon') {
                $moduleName = 'Coupon';
                $action = 'edit';
            }

            if(hasPermission($permissions, $moduleName, $action)) {
                return $next($request);
            } else {
                if(hasPermission($permissions, 'Dashboard', 'view')) {
                    return redirect()->route('staff.dashboard')->with('permission-error', 'Permission denied!');
                } 
                return redirect()->route('home')->with('permission-error', 'Permission denied!');
            }
        } else if ($userType == 5) {

            $user = Auth::user();
            $permissions = DB::table('permissions')->join('roles','roles.id','=','permissions.role_id')->where(['roles.status' => 1, 'permissions.role_id' => $user->role_id])->get();
            $moduleName = '';
            $action = 'view';

            $user = Auth::user();

            $permissions = DB::table('permissions')
                ->join('roles', 'roles.id', '=', 'permissions.role_id')
                ->where('permissions.role_id', $user->role_id)
                ->get();

            $routeModules = [
                'admin.dashboard' => ['module' => 'Dashboard', 'action' => 'view'],
                'admin.footer-builder' => ['module' => 'Footer Builder', 'action' => 'view'],
                'admin.bookinglist' => ['module' => 'Bookings', 'action' => 'view'],
                'admin.userlist' => ['module' => 'Users', 'action' => 'view'],
                'admin.providerslist' => ['module' => 'Providers', 'action' => 'view'],
                'admin.services' => ['module' => 'Service', 'action' => 'view'],
                'admin.products' => ['module' => 'Product', 'action' => 'view'],
                'admin.servicecategories' => ['module' => 'Categories', 'action' => 'view'],
                'admin.reviews' => ['module' => 'Reviews', 'action' => 'view'],
                'admin.request.dispute' => ['module' => 'Request Dispute', 'action' => 'view'],
                'admin.providerrequest' => ['module' => 'Provider Request', 'action' => 'view'],
                'admin.providertransaction' => ['module' => 'Provider Earning', 'action' => 'view'],
                'admin.transaction' => ['module' => 'Transactions', 'action' => 'view'],
                'admin.staffs' => ['module' => 'Staffs', 'action' => 'view'],
                'admin.calendar' => ['module' => 'Calendar', 'action' => 'view'],
                'admin.faq' => ['module' => 'FAQ', 'action' => 'view'],
                'admin.staffs' => ['module' => 'Staffs', 'action' => 'view'],
                'staff.tickets' => ['module' => 'Tickets', 'action' => 'view'],
                'staff.ticketdetails' => ['module' => 'Tickets', 'action' => 'view'],
                'admin.refund' => ['module' => 'Refund', 'action' => 'view'],
                'admin.subscriptionlist' => ['module' => 'Subscription', 'action' => 'view'],
                'admin.chat' => ['module' => 'Chat', 'action' => 'view'],
                'admin.leads' => ['module' => 'Leads', 'action' => 'view'],
                'admin.leadsinfo' => ['module' => 'Leads', 'action' => 'view'],
                'admin.page-builder' => ['module' => 'Pages', 'action' => 'view'],
                'admin.add_page_builder' => ['module' => 'Pages', 'action' => 'create'],
                'admin.edit_page_builder' => ['module' => 'Pages', 'action' => 'edit'],
                'admin.page-section' => ['module' => 'Pages', 'action' => 'view'],
                'admin.how-it-work' => ['module' => 'Pages', 'action' => 'view'],
                'content.menu-builder' => ['module' => 'Menu Builder', 'action' => 'view'],
                'admin.testimonials' => ['module' => 'Testimonials', 'action' => 'view'],
                'admin.subscriber-list' => ['module' => 'Newsletter', 'action' => 'view'],
                'admin.notification' => ['module' => 'Notifications', 'action' => 'view'],
                'admin.blog-category' => ['module' => 'Blogs', 'action' => 'view'],
                'admin.blog-post' => ['module' => 'Blogs', 'action' => 'view'],
                'admin.roles-permissions' => ['module' => 'Roles & Permissions', 'action' => 'view'],
                'admin.form-categories' => ['module' => 'Categories', 'action' => 'view'],
                'admin.coupon' => ['module' => 'Coupon', 'action' => 'view'],
                'admin.create-coupon' => ['module' => 'Coupon', 'action' => 'create'],
                'admin.edit-coupon' => ['module' => 'Coupon', 'action' => 'edit'],
                'admin.addons' => ['module' => 'Addons', 'action' => 'view'],
                'admin.advadisment' => ['module' => 'Advertisement', 'action' => 'view'],
                'admin.advertisement' => ['module' => 'Advertisement', 'action' => 'view'],
                'admin.addservice' => ['module' => 'Service', 'action' => 'create'],
                'editservice' => ['module' => 'Service', 'action' => 'edit'],
                'admin.payment-settings' => ['module' => 'General Settings', 'action' => 'view'],
                'listkeywords' => ['module' => 'General Settings', 'action' => 'view'],
                'dbdownload' => ['module' => 'General Settings', 'action' => 'view'],
                'admin.language-settings' => ['module' => 'General Settings', 'action' => 'view'],
                'admin.db-settings' => ['module' => 'General Settings', 'action' => 'view'],
                'admin.credential-settings' => ['module' => 'General Settings', 'action' => 'view'],
                'admin.subscription-package' => ['module' => 'General Settings', 'action' => 'view'],
                'admin.file-storage' => ['module' => 'General Settings', 'action' => 'view'],
                'settings.email-settings' => ['module' => 'Communication Settings', 'action' => 'view'],
                'settings.email-templates' => ['module' => 'Communication Settings', 'action' => 'view'],
                'settings.sms-settings' => ['module' => 'Communication Settings', 'action' => 'view'],
                'settings.notification-settings' => ['module' => 'Communication Settings', 'action' => 'view'],
                'settings.custom-settings' => ['module' => 'General Settings', 'action' => 'view'],
                'settings.apperance-settings' => ['module' => 'General Settings', 'action' => 'view'],
                'admin.general-settings' => ['module' => 'General Settings', 'action' => 'view'],
                'admin.logo-settings' => ['module' => 'General Settings', 'action' => 'view'],
                'admin.bread-image-settings' => ['module' => 'General Settings', 'action' => 'view'],
                'admin.copyright-settings' => ['module' => 'General Settings', 'action' => 'view'],
                'admin.otp-settings' => ['module' => 'General Settings', 'action' => 'view'],
                'admin.dt-settings' => ['module' => 'General Settings', 'action' => 'view'],
                'admin.search-settings' => ['module' => 'General Settings', 'action' => 'view'],
                'admin.cookies-settings' => ['module' => 'General Settings', 'action' => 'view'],
                'admin.maintenance-settings' => ['module' => 'General Settings', 'action' => 'view'],
                'admin.currency-settings' => ['module' => 'General Settings', 'action' => 'view'],
                'admin.commission' => ['module' => 'General Settings', 'action' => 'view'],
                'admin.tax-options' => ['module' => 'General Settings', 'action' => 'view'],
                'admin.preference' => ['module' => 'General Settings', 'action' => 'view'],
                'admin.invoice-settings' => ['module' => 'General Settings', 'action' => 'view'],
                'admin.appointment-settings' => ['module' => 'General Settings', 'action' => 'view'],
                'admin.invoice-template' => ['module' => 'General Settings', 'action' => 'view'],
                'admin.sitemap-settings' => ['module' => 'General Settings', 'action' => 'view'],
            ];

            $moduleDetails = $routeModules[$routeName] ?? null;

            if ($moduleDetails && hasPermission($permissions, $moduleDetails['module'], $moduleDetails['action'])) {
                return $next($request);
            }

            $redirectRoute = hasPermission($permissions, 'Dashboard', 'view') ? 'admin.dashboard' : 'admin.profile';
            return redirect()->route($redirectRoute)->with('permission-error', 'Permission Access Denied!');

        } else if ($userType == 1) {

            $addonModules = Cache::remember('addonModules', 86400, function () {
                return AddonModule::get(['id', 'slug', 'name', 'status']);
            });

            $addonRouteModules = [
                'admin.payment-report' => ['module' => 'Report'],
                'admin.coupon' => ['module' => 'Coupon'],
                'admin.create-coupon' => ['module' => 'Coupon'],
                'admin.edit-coupon' => ['module' => 'Coupon'],
                'admin.advertisement' => ['module' => 'Advertisement'],
                'admin.googlecalendarsync' => ['module' => 'GoogleCalendarSync'],
            ];

            if (array_key_exists($routeName, $addonRouteModules)) {
                $addonModuleDetails = $addonRouteModules[$routeName] ?? null;

                if ($addonModuleDetails && hasAddonModule($addonModules, $addonModuleDetails['module'])) {
                    return $next($request);
                }
                return redirect()->route('admin.dashboard')->with('permission-error', 'Module Access Denied!');
            }

        } else if ($userType == 2) {

            $addonModules = Cache::remember('addonModules', 86400, function () {
                return AddonModule::get(['id', 'slug', 'name', 'status']);
            });
            $addonRouteModules = [
                'provider.payment-report' => ['module' => 'Report'],
                'provider.coupon' => ['module' => 'Coupon'],
                'provider.create-coupon' => ['module' => 'Coupon'],
                'provider.edit-coupon' => ['module' => 'Coupon'],
            ];

            if (array_key_exists($routeName, $addonRouteModules)) {
                $addonModuleDetails = $addonRouteModules[$routeName] ?? null;

                if ($addonModuleDetails && hasAddonModule($addonModules, $addonModuleDetails['module'])) {
                    return $next($request);
                }
                return redirect()->route('provider.dashboard')->with('permission-error', 'Module Access Denied!');
            }

        }
        return $next($request);
    }
}
