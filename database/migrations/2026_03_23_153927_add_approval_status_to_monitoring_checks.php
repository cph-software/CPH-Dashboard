<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApprovalStatusToMonitoringChecks extends Migration
{
    public function up()
    {
        Schema::table('tyre_monitoring_check', function (Blueprint $table) {
            if (!Schema::hasColumn('tyre_monitoring_check', 'approval_status')) {
                $table->enum('approval_status', ['Pending', 'Approved', 'Rejected'])->default('Approved')->after('notes');
            }
            if (!Schema::hasColumn('tyre_monitoring_check', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('approval_status');
            }
        });
    }

    public function down()
    {
        Schema::table('tyre_monitoring_check', function (Blueprint $table) {
            $table->dropColumn(['approval_status', 'approved_by']);
        });
    }
}
