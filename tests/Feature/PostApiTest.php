<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class PostApiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_list_all_posts()
    {
        Post::factory()->count(3)->create();

        $response = $this->getJson('/api/posts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'description', 'published', 'user_id', 'created_at', 'updated_at']
                ],
            ]);
    }

    #[Test]
    public function authenticated_user_can_create_post()
    {
        //fail 
        $invalidPayload = [
            'title'   => '', 
            'description' => 'This is the content of the new post',
            'published' => true,
        ];

        $response = $this->postJson('/api/posts', $invalidPayload);
        $response->assertStatus(401); 

        // success
        $user = User::factory()->create();

        $payload = [
            'title'   => 'New Post',
            'description' => 'This is the content of the new post',
            'published' => true,
        ];

        $response = $this->actingAs($user)
            ->postJson('/api/posts', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Post created successfully',
            ]);

        $this->assertDatabaseHas('posts', [
            'title'   => 'New Post',
            'description' => 'This is the content of the new post',
            'published' => true,
            'user_id' => $user->id,
        ]);
    }

    #[Test]
    public function it_can_show_a_post()
    {
        $post = Post::factory()->create();
        $response = $this->getJson("/api/posts/{$post->id}");
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $post->id,
                    'title' => $post->title,
                    'description' => $post->description,
                    'published' => $post->published,
                    'user_id' => $post->user_id,
                ],
                'message' => 'Posts retrieved successfully',
            ]);
    }
    #[Test]
    public function it_returns_404_if_post_not_found()
    {
        $response = $this->getJson('/api/posts/999');
        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Post not found',
            ]);
    }
    #[Test]
    public function authenticated_user_can_update_own_post()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $payload = [
            'title'   => 'Updated Post',
            'description' => 'This is the updated content of the post',
            'published' => false,
        ];

        $response = $this->actingAs($user)
            ->putJson("/api/posts/{$post->id}", $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Post updated successfully',
            ]);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title'   => 'Updated Post',
            'description' => 'This is the updated content of the post',
            'published' => false,
            'user_id' => $user->id,
        ]);
    }

    #[Test]
    public function user_cannot_update_others_post()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $otherUser->id]);

        $payload = [
            'title'   => 'Updated Post',
            'description' => 'This is the updated content of the post',
            'published' => false,
        ];

        $response = $this->actingAs($user)
            ->putJson("/api/posts/{$post->id}", $payload);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'You are not authorized to update this post',
            ]);
        $this->assertDatabaseMissing('posts', [
            'id' => $post->id,
            'title'   => 'Updated Post',
            'description' => 'This is the updated content of the post',
            'published' => false,
        ]);
    }

    #[Test]
    public function it_returns_404_when_updating_non_existent_post()
    {
        $user = User::factory()->create();

        $payload = [
            'title'   => 'Updated Post',
            'description' => 'This is the updated content of the post',
            'published' => false,
        ];

        $response = $this->actingAs($user)
            ->putJson('/api/posts/999', $payload);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Post not found',
            ]);
    }

    #[Test]
    public function it_can_delete_a_post()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->deleteJson("/api/posts/{$post->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Post deleted successfully',
            ]);

        $this->assertDatabaseMissing('posts', [
            'id' => $post->id,
        ]);
    }
}
