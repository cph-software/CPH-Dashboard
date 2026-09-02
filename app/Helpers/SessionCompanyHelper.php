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
        return $user && ($user->role_id == 1 || $user->tyre_company_id == 1 || empty($user->tyre_company_id));
    }

    /**
     * Check if the current user is a Workshop Admin (has child companies).
     */
    public static function isWorkshopAdmin()
    {
        $user = Auth::user();
        if (!$user || !$user->tyreCompany) return false;
        
        return $user->tyreCompany->children()->count() > 0;
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
}
