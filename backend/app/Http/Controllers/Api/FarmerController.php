<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponseTrait;
use App\Models\Farmer;
use App\Models\FarmerLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FarmerController extends Controller
{
    use ApiResponseTrait;

    /**
     * Browse farmers with filters.
     * GET /api/farmers
     */
    public function index(Request $request): JsonResponse
    {
        $query = Farmer::with(['user', 'primaryLocation.county'])
            ->where('is_active', true)
            ->where('verification_status', 'VERIFIED');

        if ($specialization = $request->string('specialization')) {
            $query->where('primary_specialization', $specialization);
        }
        if ($countyId = $request->integer('county_id')) {
            $query->whereHas('locations', fn($q) => $q->where('county_id', $countyId));
        }
        if ($featured = $request->boolean('featured')) {
            $query->where('is_featured', true);
        }
        if ($search = $request->string('search')) {
            $query->where(fn($q) => $q
                ->where('farm_name', 'like', "%{$search}%")
                ->orWhere('bio', 'like', "%{$search}%")
            );
        }

        $farmers = $query->orderByDesc('rating_average')->paginate($request->integer('per_page', 12));

        return $this->successResponse($farmers);
    }

    /**
     * Show a farmer's public profile.
     * GET /api/farmers/{slug}
     */
    public function show(string $slug): JsonResponse
    {
        $farmer = Farmer::with([
            'user',
            'locations.county',
            'locations.subCounty',
            'products' => fn($q) => $q->where('status', 'PUBLISHED')->with('primaryImage')->latest()->limit(8),
            'reviews'  => fn($q) => $q->where('status', 'APPROVED')->latest()->limit(5),
        ])->where('slug', $slug)->where('is_active', true)->firstOrFail();

        return $this->successResponse($farmer);
    }

    /**
     * Get authenticated farmer's own dashboard profile.
     * GET /api/farmer/profile
     */
    public function myProfile(Request $request): JsonResponse
    {
        $farmer = $request->user()->farmer->load([
            'user',
            'locations.county',
            'locations.subCounty',
            'locations.ward',
        ]);

        return $this->successResponse($farmer);
    }

    /**
     * Update the authenticated farmer's profile.
     * PUT /api/farmer/profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $farmer = $request->user()->farmer;

        $data = $request->validate([
            'farm_name'               => ['sometimes', 'string', 'max:255'],
            'bio'                     => ['nullable', 'string', 'max:2000'],
            'primary_specialization'  => ['nullable', 'string', 'max:100'],
            'total_farm_size_acres'   => ['nullable', 'numeric', 'min:0.1'],
        ]);

        if (isset($data['farm_name'])) {
            $data['slug'] = Str::slug($data['farm_name']) . '-' . Str::random(5);
        }

        $farmer->update($data);

        return $this->successResponse($farmer->fresh()->load('user'), 'Profile updated.');
    }

    /**
     * Upload farmer ID document for verification.
     * POST /api/farmer/verification
     */
    public function submitVerification(Request $request): JsonResponse
    {
        $request->validate([
            'id_number'   => ['required', 'string'],
            'id_document' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $farmer = $request->user()->farmer;

        $path = $request->file('id_document')->store("farmer-verification/{$farmer->id}", 'public');

        $farmer->update([
            'id_number'           => $request->input('id_number'),
            'id_document_url'     => Storage::url($path),
            'verification_status' => 'PENDING',
        ]);

        return $this->successResponse($farmer, 'Verification documents submitted successfully.');
    }

    /**
     * Add or update a farmer location.
     * POST /api/farmer/locations
     */
    public function addLocation(Request $request): JsonResponse
    {
        $data = $request->validate([
            'county_id'           => ['required', 'exists:counties,id'],
            'sub_county_id'       => ['nullable', 'exists:sub_counties,id'],
            'ward_id'             => ['nullable', 'exists:wards,id'],
            'village_or_landmark' => ['nullable', 'string', 'max:255'],
            'postal_address'      => ['nullable', 'string', 'max:255'],
            'latitude'            => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'           => ['nullable', 'numeric', 'between:-180,180'],
            'is_pickup_point'     => ['boolean'],
        ]);

        $farmer   = $request->user()->farmer;
        $location = FarmerLocation::create(array_merge($data, ['farmer_id' => $farmer->id]));

        return $this->successResponse($location->load('county'), 'Location added.', 201);
    }
}
