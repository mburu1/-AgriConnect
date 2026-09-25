<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponseTrait;
use App\Models\Article;
use App\Models\Page;
use App\Models\Banner;
use App\Models\County;
use App\Models\JobPost;
use App\Models\JobApplication;
use App\Models\Enquiry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CmsController extends Controller
{
    use ApiResponseTrait;

    // ─── Articles ─────────────────────────────────────────────────

    public function articles(Request $request): JsonResponse
    {
        $query = Article::with('author:id,name,avatar_url')
            ->where('status', 'PUBLISHED');

        if ($category = $request->string('category')) {
            $query->where('category', $category);
        }

        $articles = $query->latest('published_at')->paginate($request->integer('per_page', 10));
        return $this->successResponse($articles);
    }

    public function article(string $slug): JsonResponse
    {
        $article = Article::with('author:id,name,avatar_url')
            ->where('slug', $slug)
            ->where('status', 'PUBLISHED')
            ->firstOrFail();

        $article->increment('view_count');

        return $this->successResponse($article);
    }

    // ─── Static Pages ─────────────────────────────────────────────

    public function page(string $slug): JsonResponse
    {
        $page = Page::where('slug', $slug)->where('is_published', true)->firstOrFail();
        return $this->successResponse($page);
    }

    // ─── Banners ─────────────────────────────────────────────────

    public function banners(Request $request): JsonResponse
    {
        $banners = Banner::where('is_active', true)
            ->when($request->has('position'), fn($q) => $q->where('position', $request->input('position')))
            ->orderBy('sort_order')
            ->get();

        return $this->successResponse($banners);
    }

    // ─── Locations (public) ───────────────────────────────────────

    public function counties(): JsonResponse
    {
        return $this->successResponse(County::where('is_active', true)->orderBy('name')->get());
    }

    // ─── Jobs ────────────────────────────────────────────────────

    public function jobs(Request $request): JsonResponse
    {
        $jobs = JobPost::where('is_active', true)
            ->when($request->has('department'), fn($q) => $q->where('department', $request->input('department')))
            ->latest()
            ->paginate(10);

        return $this->successResponse($jobs);
    }

    public function job(string $slug): JsonResponse
    {
        $job = JobPost::where('slug', $slug)->where('is_active', true)->firstOrFail();
        return $this->successResponse($job);
    }

    public function applyForJob(Request $request, int $jobPostId): JsonResponse
    {
        $jobPost = JobPost::findOrFail($jobPostId);

        $data = $request->validate([
            'full_name'    => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email'],
            'phone_number' => ['required', 'string'],
            'resume'       => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'cover_letter' => ['nullable', 'string', 'max:3000'],
        ]);

        $resumeUrl = $request->file('resume')->store("job-applications/{$jobPostId}", 'public');

        $application = JobApplication::create([
            'job_post_id'  => $jobPostId,
            'full_name'    => $data['full_name'],
            'email'        => $data['email'],
            'phone_number' => $data['phone_number'],
            'resume_url'   => \Storage::url($resumeUrl),
            'cover_letter' => $data['cover_letter'] ?? null,
            'status'       => 'SUBMITTED',
        ]);

        return $this->successResponse($application, 'Application submitted successfully.', 201);
    }

    // ─── Enquiries ────────────────────────────────────────────────

    public function storeEnquiry(Request $request): JsonResponse
    {
        $data = $request->validate([
            'full_name'    => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email'],
            'phone_number' => ['nullable', 'string'],
            'subject'      => ['required', 'string', 'max:255'],
            'category'     => ['nullable', 'in:Bulk Produce,Farmer Onboarding,Logistics,Technical,General'],
            'message'      => ['required', 'string', 'min:20', 'max:3000'],
        ]);

        $enquiry = Enquiry::create($data);

        return $this->successResponse($enquiry, 'Your enquiry has been received. We\'ll be in touch shortly.', 201);
    }
}
