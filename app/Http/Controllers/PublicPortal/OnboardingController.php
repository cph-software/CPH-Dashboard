<?php

namespace App\Http\Controllers\PublicPortal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\OnboardingProject;
use Carbon\Carbon;

class OnboardingController extends Controller
{
    public function index()
    {
        return view('public.onboarding.index');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'project_code' => 'required|string',
        ]);

        $project = OnboardingProject::where('project_code', $request->project_code)->first();

        if (!$project) {
            return redirect()->back()->withErrors(['project_code' => 'Kode Project tidak ditemukan atau sudah tidak berlaku.']);
        }

        return redirect()->route('public.onboarding.show', $project->project_code);
    }

    public function show($code)
    {
        $project = OnboardingProject::where('project_code', $code)->firstOrFail();
        
        // If already Go-Live, redirect to success or login
        if ($project->status === 'Go-Live') {
            return redirect()->route('public.onboarding.success', $code);
        }

        // Update last interaction
        $project->update(['last_interaction_at' => Carbon::now()]);
        
        return view('public.onboarding.form', compact('project'));
    }

    public function success($code)
    {
        $project = OnboardingProject::where('project_code', $code)->firstOrFail();
        return view('public.onboarding.success', compact('project'));
    }

    public function save(Request $request, $code)
    {
        $project = OnboardingProject::where('project_code', $code)->firstOrFail();

        // Update Data
        $project->update([
            'questionnaire_answers' => $request->input('answers'),
            'pics_data' => $request->input('pics'),
            'status' => 'Data Collection',
            'progress_percent' => $this->calculateProgress($request->all())
        ]);

        return redirect()->route('public.onboarding.success', $code);
    }

    public function upload(Request $request, $code)
    {
        $project = OnboardingProject::where('project_code', $code)->firstOrFail();

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('onboarding/' . $code, 'public');
            
            $uploadedFiles = $project->uploaded_files ?? [];
            $uploadedFiles[] = [
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'uploaded_at' => Carbon::now()->toDateTimeString()
            ];

            $project->update(['uploaded_files' => $uploadedFiles]);

            return response()->json(['success' => true, 'path' => $path]);
        }

        return response()->json(['success' => false, 'message' => 'No file uploaded.'], 400);
    }

    private function calculateProgress($data)
    {
        $answers = $data['answers'] ?? [];
        $pics = $data['pics'] ?? [];
        
        $fields = [
            'company_name', 'site_name', 'op_hours', 'site_address', 
            'input_method', 'internet', 'marking_method', 'vehicle_count',
            'current_system', 'target_date', 'major_brand'
        ];
        
        $filledCount = 0;
        foreach ($fields as $field) {
            if (!empty($answers[$field])) $filledCount++;
        }
        
        if (!empty($pics[0]['name'])) $filledCount++;
        if (!empty($pics[0]['email'])) $filledCount++;
        if (!empty($pics[0]['whatsapp'])) $filledCount++;
        
        $totalFields = count($fields) + 3; // 11 answer fields + 3 main pic fields
        return floor(($filledCount / $totalFields) * 100);
    }
}
