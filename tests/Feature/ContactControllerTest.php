<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\KingdomHall;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_contacts_index(): void
    {
        $response = $this->getJson(route('contacts.index'));

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_get_contacts_index(): void
    {
        $user = User::factory()->create();
        Contact::factory()->count(3)->create();

        $response = $this->actingAs($user)->getJson(route('contacts.index'));

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    public function test_contacts_index_includes_kingdom_hall_relationship(): void
    {
        $user = User::factory()->create();
        $kingdomHall = KingdomHall::factory()->create(['name' => 'Test Hall']);
        Contact::factory()->create(['kingdom_hall_id' => $kingdomHall->id]);

        $response = $this->actingAs($user)->getJson(route('contacts.index'));

        $response->assertStatus(200);
        $contacts = $response->json()['data'];
        $this->assertEquals('Test Hall', $contacts[0]['kingdom_hall']['name']);
    }

    public function test_authenticated_user_can_create_contact(): void
    {
        $user = User::factory()->create();
        $kingdomHall = KingdomHall::factory()->create();

        $response = $this->actingAs($user)->postJson(route('contacts.store'), [
            'kingdom_hall_id' => $kingdomHall->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '555-1234',
            'role' => 'coordinator',
            'notify_email' => true,
            'notify_sms' => false,
            'active' => true,
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.name', 'John Doe');
        $this->assertDatabaseHas('contacts', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'kingdom_hall_id' => $kingdomHall->id,
        ]);
    }

    public function test_contact_name_is_required(): void
    {
        $user = User::factory()->create();
        $kingdomHall = KingdomHall::factory()->create();

        $response = $this->actingAs($user)->postJson(route('contacts.store'), [
            'kingdom_hall_id' => $kingdomHall->id,
            'name' => '',
            'email' => 'john@example.com',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    public function test_contact_kingdom_hall_id_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('contacts.store'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('kingdom_hall_id');
    }

    public function test_contact_kingdom_hall_must_exist(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('contacts.store'), [
            'kingdom_hall_id' => 99999,
            'name' => 'John Doe',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('kingdom_hall_id');
    }

    public function test_contact_email_must_be_valid(): void
    {
        $user = User::factory()->create();
        $kingdomHall = KingdomHall::factory()->create();

        $response = $this->actingAs($user)->postJson(route('contacts.store'), [
            'kingdom_hall_id' => $kingdomHall->id,
            'name' => 'John Doe',
            'email' => 'not-a-valid-email',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    public function test_authenticated_user_can_view_single_contact(): void
    {
        $user = User::factory()->create();
        $contact = Contact::factory()->create(['name' => 'Jane Doe']);

        $response = $this->actingAs($user)->getJson(route('contacts.show', $contact));

        $response->assertStatus(200);
        $response->assertJsonPath('data.name', 'Jane Doe');
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'email',
                'phone',
                'role',
                'kingdom_hall',
                'notifications',
            ]
        ]);
    }

    public function test_authenticated_user_can_update_contact(): void
    {
        $user = User::factory()->create();
        $contact = Contact::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($user)->putJson(route('contacts.update', $contact), [
            'name' => 'New Name',
            'email' => 'newemail@example.com',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.name', 'New Name');
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'name' => 'New Name',
            'email' => 'newemail@example.com',
        ]);
    }

    public function test_authenticated_user_can_update_contact_kingdom_hall(): void
    {
        $user = User::factory()->create();
        $oldKingdomHall = KingdomHall::factory()->create();
        $newKingdomHall = KingdomHall::factory()->create();
        $contact = Contact::factory()->create(['kingdom_hall_id' => $oldKingdomHall->id]);

        $response = $this->actingAs($user)->putJson(route('contacts.update', $contact), [
            'kingdom_hall_id' => $newKingdomHall->id,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'kingdom_hall_id' => $newKingdomHall->id,
        ]);
    }

    public function test_authenticated_user_can_deactivate_contact(): void
    {
        $user = User::factory()->create();
        $contact = Contact::factory()->create(['active' => true]);

        $response = $this->actingAs($user)->deleteJson(route('contacts.destroy', $contact));

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Contact deactivated successfully.');
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'active' => false,
        ]);
    }

    public function test_contacts_are_ordered_by_name(): void
    {
        $user = User::factory()->create();
        $kingdomHall = KingdomHall::factory()->create();
        Contact::factory()->create(['kingdom_hall_id' => $kingdomHall->id, 'name' => 'Charlie']);
        Contact::factory()->create(['kingdom_hall_id' => $kingdomHall->id, 'name' => 'Alice']);
        Contact::factory()->create(['kingdom_hall_id' => $kingdomHall->id, 'name' => 'Bob']);

        $response = $this->actingAs($user)->getJson(route('contacts.index'));

        $response->assertStatus(200);
        $contacts = $response->json()['data'];
        $this->assertEquals('Alice', $contacts[0]['name']);
        $this->assertEquals('Bob', $contacts[1]['name']);
        $this->assertEquals('Charlie', $contacts[2]['name']);
    }

    public function test_contact_can_be_created_with_minimal_data(): void
    {
        $user = User::factory()->create();
        $kingdomHall = KingdomHall::factory()->create();

        $response = $this->actingAs($user)->postJson(route('contacts.store'), [
            'kingdom_hall_id' => $kingdomHall->id,
            'name' => 'Minimal Contact',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('contacts', [
            'name' => 'Minimal Contact',
            'kingdom_hall_id' => $kingdomHall->id,
        ]);
    }

    public function test_contact_notification_preferences_can_be_updated(): void
    {
        $user = User::factory()->create();
        $contact = Contact::factory()->create([
            'notify_email' => true,
            'notify_sms' => false,
        ]);

        $response = $this->actingAs($user)->putJson(route('contacts.update', $contact), [
            'notify_email' => false,
            'notify_sms' => true,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'notify_email' => false,
            'notify_sms' => true,
        ]);
    }
}
