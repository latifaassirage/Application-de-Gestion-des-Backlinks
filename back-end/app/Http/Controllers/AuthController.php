<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
   public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json([
            'message' => 'Invalid credentials'
        ], 401);
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'user' => $user,
        'token' => $token
    ]);
}

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message'=>'Logged out']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Aucun utilisateur trouvé avec cette adresse email'
            ], 404);
        }

        // Générer un token de réinitialisation
        $token = bin2hex(random_bytes(32));
        $expiry = now()->addHours(1); // Token valide 1 heure

        // Stocker le token (vous pourriez utiliser une table password_resets)
        // Pour simplifier, on utilise directement une notification par email
        // Dans un vrai projet, vous enverriez un email avec un lien de réinitialisation
        
        // Simulation d'envoi d'email - dans la vraie vie, utilisez Mail::send()
        \Log::info("Password reset token for {$user->email}: {$token}");
        
        // Pour cette démo, on retourne juste un message de succès
        // En production, vous enverriez un email avec le lien: 
        // https://votre-app.com/reset-password?token={$token}&email={$user->email}
        
        return response()->json([
            'message' => 'Un email de réinitialisation a été envoyé à votre adresse email.',
            'debug_token' => app()->environment('local') ? $token : null // Debug uniquement en local
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Utilisateur non trouvé'
            ], 404);
        }

        // Vérifier le token (dans un vrai projet, vous vérifieriez dans la table password_resets)
        // Pour cette démo, on accepte directement le reset
        
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'message' => 'Mot de passe réinitialisé avec succès'
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
        ]);

        
        if ($request->filled('name')) {
            $user->name = $request->name;
        }

       
        if ($request->filled('email')) {
            $user->email = $request->email;
        }

               if ($request->filled('current_password') && $request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'message' => 'Current password is incorrect'
                ], 422);
            }

            $user->password = Hash::make($request->new_password);
        }

               if ($user->role === 'staff') {
            if ($request->filled('position')) {
                $user->position = $request->position;
            }
            if ($request->filled('department')) {
                $user->department = $request->department;
            }
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }
}
