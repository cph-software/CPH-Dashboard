<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToCompany;

class TyreFailureCode extends Model
{
    use BelongsToCompany;
    protected $table = 'tyre_failure_codes';
    protected $guarded = [];
    public function movements()
    {
        return $this->hasMany(TyreMovement::class, 'failure_code_id');
    }

    public function aliases()
    {
        return $this->hasMany(TyreFailureAlias::class, 'tyre_failure_code_id');
    }

    /**
     * Get failure name or company-specific alias.
     */
    public function getDisplayNameByCompanyId($companyId = null)
    {
        if ($companyId) {
            $alias = $this->aliases()->where('tyre_company_id', $companyId)->first();
            if ($alias && !empty($alias->alias_name)) {
                return $alias->alias_name;
            }
        }

        // Return original display_name if available, then failure_name
        return $this->display_name ?: $this->failure_name;
    }
}
