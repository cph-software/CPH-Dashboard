<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApprovalColumnsToTyreExaminations extends Migration
{
    public function up()
    {
        Schema::table('tyre_examinations', function (Blueprint $table) {
            if (!Schema::hasColumn('tyre_examinations', 'exam_type')) {
                $table->enum('exam_type', ['Sales', 'Customer'])->default('Customer')->after('notes');
            }
            if (!Schema::hasColumn('tyre_examinations', 'approval_status')) {
                $table->enum('approval_status', ['Pending', 'Approved', 'Rejected'])->default('Approved')->after('exam_type');
            }
            if (!Schema::hasColumn('tyre_examinations', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('approval_status');
            }
            if (!Schema::hasColumn('tyre_examinations', 'reject_reason')) {
                $table->text('reject_reason')->nullable()->after('approved_by');
            }
        });
    }

    public function down()
    {
        Schema::table('tyre_examinations', function (Blueprint $table) {
            $table->dropColumn(['exam_type', 'approval_status', 'approved_by', 'reject_reason']);
        });
    }
}
