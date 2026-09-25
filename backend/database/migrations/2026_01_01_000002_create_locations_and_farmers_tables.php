<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 6. Counties
        Schema::create('counties', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->unique(); // e.g. "001", "047"
            $table->string('capital')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 7. Sub Counties
        Schema::create('sub_counties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('county_id')->constrained('counties')->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
            $table->unique(['county_id', 'name']);
        });

        // 8. Wards
        Schema::create('wards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_county_id')->constrained('sub_counties')->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
            $table->unique(['sub_county_id', 'name']);
        });

        // 9. Farmers
        Schema::create('farmers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('farm_name');
            $table->string('slug')->unique();
            $table->text('bio')->nullable();
            $table->string('id_number')->nullable();
            $table->enum('verification_status', ['PENDING', 'VERIFIED', 'REJECTED'])->default('PENDING');
            $table->string('id_document_url')->nullable();
            $table->decimal('total_farm_size_acres', 8, 2)->nullable();
            $table->string('primary_specialization')->nullable(); // Tubers, Cereals, Horticulture, Dairy, Poultry, etc.
            $table->decimal('rating_average', 3, 2)->default(5.00);
            $table->unsignedInteger('rating_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // 10. Farmer Locations
        Schema::create('farmer_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->constrained('farmers')->cascadeOnDelete();
            $table->foreignId('county_id')->constrained('counties')->cascadeOnDelete();
            $table->foreignId('sub_county_id')->nullable()->constrained('sub_counties')->nullOnDelete();
            $table->foreignId('ward_id')->nullable()->constrained('wards')->nullOnDelete();
            $table->string('village_or_landmark')->nullable();
            $table->string('postal_address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_pickup_point')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farmer_locations');
        Schema::dropIfExists('farmers');
        Schema::dropIfExists('wards');
        Schema::dropIfExists('sub_counties');
        Schema::dropIfExists('counties');
    }
};
