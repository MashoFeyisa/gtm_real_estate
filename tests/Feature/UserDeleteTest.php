<?php

use App\Models\User;

test('admin can delete a user via standard form request', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'buyer']);

    $response = $this->actingAs($admin)->delete(route('dashboard.users.delete', $user));

    $response->assertRedirect(route('dashboard').'#users');
    $response->assertSessionHas('success', 'User deleted successfully.');

    // Users are soft-deleted so financial records and audit logs keep a valid reference.
    $this->assertSoftDeleted('users', ['id' => $user->id]);
    expect(User::find($user->id))->toBeNull();
});

test('admin can delete a user via ajax json request', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'buyer']);

    $response = $this->actingAs($admin)->deleteJson(route('dashboard.users.delete', $user));

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'message' => 'User deleted successfully.',
        'section' => 'users',
    ]);

    // Users are soft-deleted so financial records and audit logs keep a valid reference.
    $this->assertSoftDeleted('users', ['id' => $user->id]);
    expect(User::find($user->id))->toBeNull();
});

test('admin cannot delete their own account', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->delete(route('dashboard.users.delete', $admin));

    $response->assertRedirect(route('dashboard').'#users');
    $response->assertSessionHasErrors('error');

    $this->assertDatabaseHas('users', ['id' => $admin->id]);
});

test('admin cannot delete their own account via ajax json request', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->deleteJson(route('dashboard.users.delete', $admin));

    $response->assertStatus(422);
    $response->assertJson([
        'success' => false,
        'message' => 'You cannot delete your own account.',
    ]);

    $this->assertDatabaseHas('users', ['id' => $admin->id]);
});

test('non-admin user cannot delete a user', function () {
    $buyer = User::factory()->create(['role' => 'buyer']);
    $targetUser = User::factory()->create(['role' => 'seller']);

    $response = $this->actingAs($buyer)->delete(route('dashboard.users.delete', $targetUser));

    $response->assertForbidden();

    $this->assertDatabaseHas('users', ['id' => $targetUser->id]);
});
