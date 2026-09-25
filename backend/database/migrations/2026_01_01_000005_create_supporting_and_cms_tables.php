<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 22. Reviews
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->cascadeOnDelete();
            $table->foreignId('farmer_id')->nullable()->constrained('farmers')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->unsignedTinyInteger('rating'); // 1 to 5
            $table->string('title')->nullable();
            $table->text('comment');
            $table->boolean('is_verified_purchase')->default(false);
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED'])->default('APPROVED');
            $table->timestamps();
        });

        // 23. Articles (CMS)
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->longText('content');
            $table->string('featured_image_url')->nullable();
            $table->string('category')->default('General Agriculture'); // Agronomy, Pest Control, Market Insights, Farming Tips
            $table->json('tags')->nullable();
            $table->enum('status', ['DRAFT', 'PUBLISHED', 'ARCHIVED'])->default('PUBLISHED');
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('view_count')->default(0);
            $table->timestamps();
        });

        // 24. Pages
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique(); // about-us, privacy-policy, terms-and-conditions, farmer-guide
            $table->longText('content');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        // 25. Banners
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('image_url');
            $table->string('link_url')->nullable();
            $table->string('cta_text')->nullable();
            $table->string('position')->default('HOME_HERO'); // HOME_HERO, CATEGORY_TOP, SIDEBAR
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 26. Notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type'); // ORDER_PLACED, PAYMENT_RECEIVED, ORDER_DISPATCHED, ORDER_DELIVERED, PRODUCT_APPROVED
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // 27. Job Posts
        Schema::create('job_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('department'); // Agronomy, Logistics, Tech, Customer Care
            $table->string('location_type')->default('Hybrid'); // On-site, Remote, Hybrid
            $table->string('county')->nullable();
            $table->string('employment_type')->default('Full-Time');
            $table->longText('description');
            $table->longText('requirements');
            $table->date('deadline_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 28. Job Applications
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_post_id')->constrained('job_posts')->cascadeOnDelete();
            $table->string('full_name');
            $table->string('email');
            $table->string('phone_number');
            $table->string('resume_url');
            $table->text('cover_letter')->nullable();
            $table->enum('status', ['SUBMITTED', 'UNDER_REVIEW', 'SHORTLISTED', 'REJECTED'])->default('SUBMITTED');
            $table->timestamps();
        });

        // 29. Enquiries & Contact Forms
        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email');
            $table->string('phone_number')->nullable();
            $table->string('subject');
            $table->string('category')->default('General'); // Bulk Produce, Farmer Onboarding, Logistics, Technical
            $table->text('message');
            $table->enum('status', ['NEW', 'IN_PROGRESS', 'RESOLVED'])->default('NEW');
            $table->text('internal_notes')->nullable();
            $table->timestamps();
        });

        // 30. Audit Logs
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action'); // CREATED_PRODUCT, UPDATED_ORDER_STATUS, PROCESSED_PAYMENT, BANNED_USER
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('enquiries');
        Schema::dropIfExists('job_applications');
        Schema::dropIfExists('job_posts');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('banners');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('articles');
        Schema::dropIfExists('reviews');
    }
};
