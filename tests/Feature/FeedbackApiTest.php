<?php

namespace Tests\Feature;

use App\Models\Feedback;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_valid_feedback_is_stored(): void
    {
        $this->postJson('/api/feedback', $this->validPayload())->assertCreated();

        $this->assertDatabaseHas('feedback', ['email' => 'sara@example.com', 'category' => 'support']);
    }

    public function test_categories_endpoint_lists_allowed_categories(): void
    {
        $this->getJson('/api/feedback/categories')
            ->assertOk()
            ->assertExactJson(['general', 'support', 'product', 'bug']);
    }

    public function test_unknown_category_is_rejected(): void
    {
        $this->postJson('/api/feedback', $this->validPayload(['category' => 'anything']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('category');

        $this->assertDatabaseCount('feedback', 0);
    }

    public function test_unknown_category_filter_is_rejected(): void
    {
        $this->getJson('/api/feedback?category=anything')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('category');
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
