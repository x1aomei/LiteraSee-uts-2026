<?php
// app/Http/Controllers/Api/ProfileController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Resources\UserResource;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    use ApiResponse;

    public function update(ProfileUpdateRequest $request)
    {
        $user = $request->user();

        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $filename = 'avatar-' . $user->id . '-' . time() . '.' . $request->file('avatar')->extension();
            $user->avatar = $request->file('avatar')->storeAs('avatars', $filename, 'public');
        }

        $user->fill($request->validated());

        if ($user->isDirty('email')) $user->email_verified_at = null;
        $user->save();

        return $this->successResponse(
            new UserResource($user->fresh()),
            'Profil berhasil diperbarui!'
        );
    }

    public function deleteAvatar(Request $request)
    {
        $user = $request->user();
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
            $user->update(['avatar' => null]);
        }
        return $this->successResponse(new UserResource($user->fresh()), 'Foto profil dihapus.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $request->user()->update(['password' => Hash::make($validated['password'])]);

        return $this->successResponse(null, 'Password berhasil diubah.');
    }

    public function destroy(Request $request)
    {
        $request->validate(['password' => ['required', 'current_password']]);

        $user = $request->user();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->tokens()->delete();
        $user->delete();

        return $this->successResponse(null, 'Akun berhasil dihapus.');
    }
}