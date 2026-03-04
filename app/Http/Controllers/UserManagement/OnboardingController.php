<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\OnboardingProject;
use Illuminate\Support\Str;

class OnboardingController extends Controller
{
    public function index()
    {
        $projects = OnboardingProject::with('internalPic')->orderBy('created_at', 'desc')->get();
        return view('user-management.onboarding.index', compact('projects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
        ]);

        // Generate unique project code: CPH-[NAME]-RANDOM
        $shortName = strtoupper(Str::slug(substr($request->customer_name, 0, 5)));
        $code = 'CPH-' . $shortName . '-' . Str::upper(Str::random(4));

        OnboardingProject::create([
            'project_code' => $code,
            'customer_name' => $request->customer_name,
            'internal_pic_id' => auth()->id(),
            'status' => 'Prospect'
        ]);

        return redirect()->back()->with('success', 'Project Onboarding berhasil dibuat dengan kode: ' . $code);
    }

    public function show($id)
    {
        $project = OnboardingProject::findOrFail($id);
        return view('user-management.onboarding.show', compact('project'));
    }

    public function update(Request $request, $id)
    {
        $project = OnboardingProject::findOrFail($id);
        $project->update($request->all());
        return redirect()->back()->with('success', 'Status project berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $project = OnboardingProject::findOrFail($id);
        $project->delete();
        return redirect()->back()->with('success', 'Project berhasil dihapus.');
    }

    public function generateAccounts($id)
    {
        $project = OnboardingProject::findOrFail($id);
        
        if (!$project->pics_data || count($project->pics_data) == 0) {
            return redirect()->back()->with('error', 'Data PIC belum diisi oleh customer.');
        }

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            // 1. Create Tyre Company if not exists
            $company = \App\Models\TyreCompany::firstOrCreate(
                ['company_name' => $project->customer_name],
                ['description' => $project->questionnaire_answers['site_name'] ?? '-']
            );

            // 2. Create User Accounts for each PIC
            $accountCount = 0;
            foreach ($project->pics_data as $pic) {
                if (empty($pic['email']) || empty($pic['name'])) continue;

                \App\Models\User::updateOrCreate(
                    ['name' => $pic['email']], // Using email as name/username
                    [
                        'password' => bcrypt('CPH12345'), // Default password
                        'role_id' => 9, // Role Fleet
                        'tyre_company_id' => $company->id,
                        'name' => $pic['email'], // Username
                        'foto' => 'default.png',
                        // 'full_name' => $pic['name'],
                    ]
                );
                $accountCount++;
            }

            if ($accountCount == 0) {
                throw new \Exception('Gagal membuat akun: Email PIC tidak valid.');
            }

            // 3. Update Status
            $project->update([
                'status' => 'Go-Live',
                'progress_percent' => 100
            ]);

            \Illuminate\Support\Facades\DB::commit();
            return redirect()->route('onboarding-projects.index')->with('success', 'Selamat! Akun user (' . $accountCount . ') berhasil digenerate dan status project menjadi Go-Live. Password default: CPH12345');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->back()->with('error', 'Gagal generate akun: ' . $e->getMessage());
        }
    }

    public function downloadChecklist($id)
    {
        $project = OnboardingProject::findOrFail($id);
        
        // Execute the python script to generate the Excel file
        $scriptPath = base_path('docs/generate_checklist.py');
        $outputPath = base_path('docs/CPH_Checklist_Onboarding_V2.xlsx');
        
        // Run script
        shell_exec("python3 \"$scriptPath\"");
        
        if (file_exists($outputPath)) {
            $fileName = 'CPH_Onboarding_Checklist_' . Str::slug($project->customer_name) . '.xlsx';
            return response()->download($outputPath, $fileName);
        }
        
        return redirect()->back()->with('error', 'Gagal membuat file excel checklist.');
    }
}
