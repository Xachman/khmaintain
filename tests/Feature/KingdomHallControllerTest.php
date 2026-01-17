<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\KingdomHall;
use App\Models\MaintenanceTask;
use App\Models\ScheduledMaintenance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KingdomHallControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_index(): void
    {
        $response = $this->get(route('kingdom-halls.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_get_kingdom_halls_index(): void
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        KingdomHall::factory()->count(3)->create();

        $response = $this->actingAs($user)->get(route('kingdom-halls.index'));
        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }


    public function test_authenticated_user_can_create_kingdom_hall(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('kingdom-halls.store'), [
            'name' => 'Central Kingdom Hall',
            'active' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('kingdom_halls', [
            'name' => 'Central Kingdom Hall',
            'active' => true,
        ]);
    }

    public function test_kingdom_hall_name_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('kingdom-halls.store'), [
            'name' => '',
            'active' => true,
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_authenticated_user_can_update_kingdom_hall(): void
    {
        $user = User::factory()->create();
        $kingdomHall = KingdomHall::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($user)->put(route('kingdom-halls.update', $kingdomHall), [
            'name' => 'New Name',
            'active' => true,
        ]);

        $response->assertRedirect(route('kingdom-halls.show', $kingdomHall));
        $this->assertDatabaseHas('kingdom_halls', [
            'id' => $kingdomHall->id,
            'name' => 'New Name',
        ]);
    }

    public function test_authenticated_user_can_deactivate_kingdom_hall(): void
    {
        $user = User::factory()->create();
        $kingdomHall = KingdomHall::factory()->create(['active' => true]);

        $response = $this->actingAs($user)->delete(route('kingdom-halls.destroy', $kingdomHall));

        $response->assertRedirect(route('kingdom-halls.index'));
        $this->assertDatabaseHas('kingdom_halls', [
            'id' => $kingdomHall->id,
            'active' => false,
        ]);
    }

    public function test_kingdom_hall_index_includes_counts(): void
    {
        $user = User::factory()->create();
        $kingdomHall = KingdomHall::factory()->create();
        Contact::factory()->count(2)->create(['kingdom_hall_id' => $kingdomHall->id]);

        $response = $this->actingAs($user)->get(route('kingdom-halls.index'));

        $response->assertStatus(200);
        $kingdomHalls = $response->json()['data'];
        $this->assertEquals(2, $kingdomHalls[0]['contacts_count']);
    }

    public function test_kingdom_hall_show_loads_relationships(): void
    {
        $user = User::factory()->create();
        $kingdomHall = KingdomHall::factory()->create();
        $contact = Contact::factory()->create(['kingdom_hall_id' => $kingdomHall->id]);
        $task = MaintenanceTask::factory()->create();
        $scheduled = ScheduledMaintenance::factory()->create([
            'kingdom_hall_id' => $kingdomHall->id,
            'maintenance_task_id' => $task->id,
        ]);

        $response = $this->actingAs($user)->get(route('kingdom-halls.show', $kingdomHall));

        $response->assertStatus(200);
        $viewKingdomHall = $response->viewData('kingdomHall');
        $this->assertTrue($viewKingdomHall->relationLoaded('contacts'));
        $this->assertTrue($viewKingdomHall->relationLoaded('scheduledMaintenances'));
    }
}
