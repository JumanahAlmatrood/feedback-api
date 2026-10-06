<?php

namespace Tests\Feature;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FeedbackApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Sara',
            'email' => 'sara@example.com',
            'rating' => 4,
            'category' => 'support',
            'comment' => 'Quick reply, thanks.',
        ], $overrides);
    }

    // ---------- POST /api/feedback ----------

    public function test_valid_feedback_is_stored(): void
    {
        $this->postJson('/api/feedback', $this->validPayload())->assertCreated();

        $this->assertDatabaseHas('feedback', ['email' => 'sara@example.com', 'category' => 'support']);
    }

    public function test_required_fields_are_required(): void
    {
        $this->postJson('/api/feedback', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'rating', 'category']);
    }

    public function test_rating_must_be_a_whole_number_from_one_to_five(): void
    {
        foreach ([0, 6, 2.5, 'abc'] as $rating) {
            $this->postJson('/api/feedback', $this->validPayload(['rating' => $rating]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors('rating');
        }

        $this->assertDatabaseCount('feedback', 0);
    }

    public function test_invalid_email_is_rejected(): void
    {
        $this->postJson('/api/feedback', $this->validPayload(['email' => 'not-an-email']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_comment_is_optional(): void
    {
        $this->postJson('/api/feedback', $this->validPayload(['comment' => null]))
            ->assertCreated();
    }

    public function test_unknown_category_is_rejected(): void
    {
        $this->postJson('/api/feedback', $this->validPayload(['category' => 'anything']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('category');

        $this->assertDatabaseCount('feedback', 0);
    }

    public function test_text_fields_have_length_limits(): void
    {
        $this->postJson('/api/feedback', $this->validPayload([
            'name' => str_repeat('a', 101),
            'email' => str_repeat('a', 250).'@example.com',
            'comment' => str_repeat('a', 1001),
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'comment']);

        $this->assertDatabaseCount('feedback', 0);
    }

    public function test_fields_at_the_limit_are_accepted(): void
    {
        $this->postJson('/api/feedback', $this->validPayload([
            'name' => str_repeat('a', 100),
            'comment' => str_repeat('a', 1000),
        ]))->assertCreated();
    }

    public function test_submissions_are_rate_limited(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/feedback', $this->validPayload())->assertCreated();
        }

        $this->postJson('/api/feedback', $this->validPayload())->assertTooManyRequests();
    }

    // ---------- GET /api/feedback ----------

    public function test_feedback_list_is_paginated(): void
    {
        Feedback::factory(20)->create();

        $this->getJson('/api/feedback')
            ->assertOk()
            ->assertJsonCount(15, 'data')
            ->assertJsonPath('total', 20)
            ->assertJsonPath('last_page', 2);

        $this->getJson('/api/feedback?page=2')
            ->assertOk()
            ->assertJsonCount(5, 'data');
    }

    public function test_feedback_list_can_be_filtered(): void
    {
        Feedback::factory(3)->create(['category' => 'bug', 'rating' => 5]);
        Feedback::factory(2)->create(['category' => 'bug', 'rating' => 1]);
        Feedback::factory(4)->create(['category' => 'general', 'rating' => 5]);

        $this->getJson('/api/feedback?category=bug')->assertOk()->assertJsonPath('total', 5);
        $this->getJson('/api/feedback?rating=5')->assertOk()->assertJsonPath('total', 7);
        $this->getJson('/api/feedback?category=bug&rating=5')->assertOk()->assertJsonPath('total', 3);
    }

    public function test_unknown_category_filter_is_rejected(): void
    {
        $this->getJson('/api/feedback?category=anything')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('category');
    }

    public function test_invalid_filters_are_rejected(): void
    {
        $this->getJson('/api/feedback?rating=9')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('rating');

        $this->getJson('/api/feedback?page=0')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('page');
    }

    // ---------- GET /api/feedback/{id} ----------

    public function test_single_feedback_can_be_shown(): void
    {
        $feedback = Feedback::factory()->create(['name' => 'Noura']);

        $this->getJson("/api/feedback/{$feedback->id}")
            ->assertOk()
            ->assertJsonPath('id', $feedback->id)
            ->assertJsonPath('name', 'Noura');
    }

    public function test_missing_feedback_returns_not_found(): void
    {
        $this->getJson('/api/feedback/999')->assertNotFound();
    }

    // ---------- DELETE /api/feedback/{id} ----------

    public function test_feedback_can_be_deleted(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $feedback = Feedback::factory()->create();

        $this->deleteJson("/api/feedback/{$feedback->id}")->assertNoContent();

        $this->assertDatabaseMissing('feedback', ['id' => $feedback->id]);
    }

    public function test_deleting_missing_feedback_returns_not_found(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->deleteJson('/api/feedback/999')->assertNotFound();
    }

    public function test_guests_cannot_delete_feedback(): void
    {
        $feedback = Feedback::factory()->create();

        $this->deleteJson("/api/feedback/{$feedback->id}")->assertUnauthorized();

        $this->assertDatabaseHas('feedback', ['id' => $feedback->id]);
    }

    // ---------- GET /api/feedback/stats and /categories ----------

    public function test_stats_are_calculated(): void
    {
        Feedback::factory(2)->create(['rating' => 5, 'category' => 'bug']);
        Feedback::factory()->create(['rating' => 1, 'category' => 'general']);

        $this->getJson('/api/feedback/stats')
            ->assertOk()
            ->assertJsonPath('total', 3)
            ->assertJsonPath('average_rating', 3.67)
            ->assertJsonPath('rating_distribution.0.count', 1)
            ->assertJsonPath('rating_distribution.2.count', 0)
            ->assertJsonPath('rating_distribution.4.count', 2)
            ->assertJsonPath('by_category.0.category', 'bug')
            ->assertJsonPath('by_category.0.count', 2);
    }

    public function test_categories_endpoint_lists_allowed_categories(): void
    {
        $this->getJson('/api/feedback/categories')
            ->assertOk()
            ->assertExactJson(['general', 'support', 'product', 'bug']);
    }
    // ---------- GET /api/feedback/export ----------

    public function test_feedback_can_be_exported_as_csv(): void
    {
        Sanctum::actingAs(User::factory()->create());
        Feedback::factory()->create([
            'name' => 'Noura',
            'email' => 'noura@example.com',
            'rating' => 5,
            'category' => 'bug',
            'comment' => 'Excellent',
        ]);

        $response = $this->get('/api/feedback/export')
            ->assertOk()
            ->assertDownload('feedback.csv');

        $this->assertStringStartsWith('text/csv', $response->headers->get('Content-Type'));

        $csv = $response->streamedContent();

        $this->assertStringContainsString('id,name,rating,category,comment,created_at', $csv);
        $this->assertStringContainsString(',Noura,5,bug,Excellent,', $csv);
        $this->assertStringNotContainsString('noura@example.com', $csv);
    }

    public function test_csv_export_neutralises_spreadsheet_formulas(): void
    {
        Sanctum::actingAs(User::factory()->create());
        Feedback::factory()->create([
            'name' => '=HYPERLINK("http://evil.test")',
            'comment' => '+1+1',
        ]);

        $csv = $this->get('/api/feedback/export')->streamedContent();

        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertStringContainsString("'+1+1", $csv);
    }

    public function test_guests_cannot_export_feedback(): void
    {
        $this->getJson('/api/feedback/export')->assertUnauthorized();
    }

    // ---------- Malicious input ----------

    public function test_script_tags_are_stored_and_returned_as_plain_text(): void
    {
        $payload = '<script>alert("xss")</script>';

        $this->postJson('/api/feedback', $this->validPayload(['comment' => $payload]))
            ->assertCreated()
            ->assertJsonPath('comment', $payload);

        $this->assertDatabaseHas('feedback', ['comment' => $payload]);

        $this->getJson('/api/feedback')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonPath('data.0.comment', $payload);
    }
    // ---------- Privacy and seeding ----------

    public function test_emails_are_never_returned(): void
    {
        $feedback = Feedback::factory()->create();

        $this->getJson('/api/feedback')
            ->assertOk()
            ->assertJsonMissingPath('data.0.email');

        $this->getJson("/api/feedback/{$feedback->id}")
            ->assertOk()
            ->assertJsonMissingPath('email');

        $this->postJson('/api/feedback', $this->validPayload())
            ->assertCreated()
            ->assertJsonMissingPath('email');
    }

    public function test_default_seeder_creates_feedback(): void
    {
        $this->seed();

        $this->assertDatabaseCount('feedback', 50);
    }
}
