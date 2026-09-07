<?php

namespace App\Helpers;

use App\Models\TyreCompany;
use Illuminate\Support\Facades\Auth;

class SessionCompanyHelper
{
    /**
     * Check if the current user is a Super Admin.
     */
    public static function isSuperAdmin()
    {
        $user = Auth::user();
        return $user && ($user->role_id == 1);
    }

    /**
     * Check if the current user is a Workshop Admin (has child companies or role_id == 5).
     */
    public static function isWorkshopAdmin()
    {
        $user = Auth::user();
        if (!$user) return false;
        
        if ($user->role_id == 5) return true;

        return $user->tyreCompany && $user->tyreCompany->children()->count() > 0;
    }

    /**
     * Get the active company ID safely based on user role and session.
     */
    public static function getActiveCompanyId()
    {
        $user = Auth::user();
        if (!$user) return null;

        $userCompanyId = $user->tyre_company_id;
        $sessionCompanyId = session('active_company_id');

        if (self::isSuperAdmin()) {
            // If super admin has no active company in session, it means Global View (return null to skip filter)
            return session()->has('active_company_id') ? $sessionCompanyId : null;
        }

        if (self::isWorkshopAdmin()) {
            if ($sessionCompanyId && $sessionCompanyId !== 'ALL_CLIENTS') {
                // Global Klien mode: session is an array of company IDs
                if (is_array($sessionCompanyId)) {
                    return $sessionCompanyId;
                }
                // If user explicitly selected Induk ($userCompanyId):
                // Induk manages itself AND all its client companies!
                if ($sessionCompanyId == $userCompanyId) {
                    $clientIds = $user->tyreCompany ? $user->tyreCompany->getAllClientIds() : [];
                    $clientIds[] = (int) $userCompanyId;
                    return array_values(array_unique(array_filter($clientIds)));
                }
                // Single client company selected
                if (self::isValidClient($sessionCompanyId)) {
                    return (int) $sessionCompanyId;
                }
            } else {
                // Default mode for Workshop Admin: Global Klien (Array of all clients + own)
                $clientIds = $user->tyreCompany ? $user->tyreCompany->getAllClientIds() : [];
                $clientIds[] = (int) $userCompanyId;
                return array_values(array_unique(array_filter($clientIds)));
            }
        }

        // Default to user's own company
        return $userCompanyId;
    }

    /**
     * Validate if the requested company ID is a valid client for the current user.
     */
    public static function isValidClient($companyId)
    {
        $user = Auth::user();
        if (!$user) return false;
        
        // They can always access their own company
        if ($companyId == $user->tyre_company_id) return true;

        // Check if it's a child company
        return TyreCompany::where('id', $companyId)
            ->where('parent_company_id', $user->tyre_company_id)
            ->exists();
    }

    /**
     * Get list of accessible companies for the current user (e.g. for dropdowns).
     */
    public static function getAccessibleCompanies()
    {
        $user = Auth::user();
        if (!$user) return collect();

        if (self::isSuperAdmin()) {
            return TyreCompany::where('status', 'Active')->orderBy('company_name')->get();
        }

        if (self::isWorkshopAdmin()) {
            $clientIds = $user->tyreCompany ? $user->tyreCompany->getAllClientIds() : [];
            if ($user->tyre_company_id) {
                $clientIds[] = $user->tyre_company_id;
            }
            return TyreCompany::whereIn('id', array_unique($clientIds))
                ->where('status', 'Active')
                ->orderBy('company_name')
                ->get();
        }

        if ($user->tyre_company_id) {
            return TyreCompany::where('id', $user->tyre_company_id)->get();
        }

        return collect();
    }
}
