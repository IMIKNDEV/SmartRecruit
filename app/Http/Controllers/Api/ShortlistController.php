<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApplicationResource;
use App\Models\JobOffer;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ShortlistController extends Controller
{
    /**
     * Top-5 applications for a job offer, sorted by matching score (highest
     * first). Recruiter, own job offer only.
     */
    public function index(Request $request, int $id)
    {
        $jobOffer = JobOffer::findOrFail($id);

        if ($jobOffer->recruiter_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $applications = $jobOffer->applications()
            ->with(['candidate', 'analysis', 'interviews'])
            ->leftJoin('application_analysis', 'application_analysis.application_id', '=', 'applications.id')
            ->select('applications.*')
            ->orderByDesc('application_analysis.matching_score')
            ->take(5)
            ->get();

        return ApplicationResource::collection($applications);
    }

    /**
     * Export the shortlist as a CSV file (semicolon-separated for Excel FR)
     * for sharing with an external hiring manager.
     */
    public function export(Request $request, int $id): StreamedResponse
    {
        $jobOffer = JobOffer::findOrFail($id);

        if ($jobOffer->recruiter_id !== $request->user()->id) {
            abort(403);
        }

        $applications = $jobOffer->applications()
            ->with(['candidate', 'analysis'])
            ->leftJoin('application_analysis', 'application_analysis.application_id', '=', 'applications.id')
            ->select('applications.*')
            ->orderByDesc('application_analysis.matching_score')
            ->take(5)
            ->get();

        $filename = 'shortlist-'.$jobOffer->id.'-'.now()->format('Ymd').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        return response()->streamDownload(function () use ($applications) {
            $out = fopen('php://output', 'w');

            fputcsv($out, [
                'Candidat',
                'Email',
                'Score',
                'Competences trouvees',
                'Competences manquantes',
                'Statut',
                'CV',
            ], ';');

            foreach ($applications as $application) {
                $analysis = $application->analysis;

                fputcsv($out, [
                    $application->candidate?->name ?? '',
                    $application->candidate?->email ?? '',
                    $analysis?->matching_score ?? 0,
                    is_array($analysis?->matched_keywords) ? implode(', ', $analysis->matched_keywords) : '',
                    is_array($analysis?->missing_keywords) ? implode(', ', $analysis->missing_keywords) : '',
                    $application->status,
                    $application->cv_path,
                ], ';');
            }

            fclose($out);
        }, $filename, $headers);
    }
}
