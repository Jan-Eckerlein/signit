<?php

namespace App\Services;

use App\Models\Document;
use App\Models\MagicLink;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class MagicLinkService
{
    /**
     * Create a new magic link for a user and document
     */
    public function createMagicLink(User $user, Document $document, int $expiryHours = 24): string
    {
        // Revoke any existing magic links for this user and document
        $this->revokeExistingMagicLinks($user, $document);
        
        // Generate secure token
        $token = bin2hex(random_bytes(32)); // 64-character hex string
        
        // Create magic link (token will be automatically hashed by the model)
        MagicLink::create([
            'user_id' => $user->id,
            'document_id' => $document->id,
            'token' => $token,
            'expires_at' => now()->addHours($expiryHours),
        ]);
        
        return $token;
    }
    
    /**
     * Authenticate a user using a magic link token
     */
    public function authenticateMagicLink(string $token): ?MagicLink
    {
        $magicLink = $this->findValidMagicLink($token);
        
        if (!$magicLink) {
            return null;
        }
        
        // Log in the user
        Auth::login($magicLink->user);
        
        // Set magic link session flags
        session(['auth_via_magic_link' => true]);
        session(['magic_link_document_id' => $magicLink->document_id]);
        
        return $magicLink;
    }
    
    /**
     * Validate a magic link token without authenticating
     */
    public function validateMagicLink(string $token): ?MagicLink
    {
        return $this->findValidMagicLink($token);
    }
    
    /**
     * Revoke a specific magic link by token
     */
    public function revokeMagicLink(string $token): bool
    {
        $magicLink = $this->findValidMagicLink($token);
        
        if (!$magicLink) {
            return false;
        }
        
        $magicLink->delete();
        return true;
    }
    
    /**
     * Revoke all magic links for a user and document
     */
    public function revokeExistingMagicLinks(User $user, Document $document): void
    {
        MagicLink::where('user_id', $user->id)
            ->where('document_id', $document->id)
            ->delete();
    }
    
    /**
     * Clean up expired magic links
     */
    public function cleanupExpiredMagicLinks(): int
    {
        return MagicLink::where('expires_at', '<', now())->delete();
    }
    
    /**
     * Find a valid magic link by token
     */
    private function findValidMagicLink(string $token): ?MagicLink
    {
        // Get all magic links that haven't expired
        $magicLinks = MagicLink::where('expires_at', '>', now())->get();
        
        // Check each one for token match
        foreach ($magicLinks as $magicLink) {
            if ($magicLink->checkToken($token)) {
                return $magicLink;
            }
        }
        
        return null;
    }
} 