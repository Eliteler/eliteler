<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AssetsController extends Controller
{
    public function cleanupUserBusinessCards()
    {
        $protectedUserId = '115462851703180043069';

        DB::transaction(function () use ($protectedUserId) {

            // Track users whose media is already deleted
            $processedMediaUsers = [];

            // Get all business cards except protected user
            $cards = DB::table('business_cards')
                ->where('user_id', '!=', $protectedUserId)
                ->get();

            foreach ($cards as $card) {

                // Absolute safety guard
                if ($card->user_id === $protectedUserId) {
                    continue;
                }

                // Delete profile & cover for THIS card
                $this->deletePublicFile($card->profile, $protectedUserId);
                $this->deletePublicFile($card->cover, $protectedUserId);

                /**
                 * Media is USER-LEVEL (by schema)
                 * Delete media FILES + DB rows ONCE per user
                 */
                if (! in_array($card->user_id, $processedMediaUsers, true)) {

                    $assets = DB::table('medias')
                        ->where('user_id', $card->user_id)
                        ->get();

                    foreach ($assets as $asset) {
                        $this->deletePublicFile($asset->media_url, $protectedUserId);
                    }

                    // Delete media DB rows once per user
                    DB::table('medias')
                        ->where('user_id', $card->user_id)
                        ->delete();

                    $processedMediaUsers[] = $card->user_id;
                }

                // Delete business card
                DB::table('business_cards')
                    ->where('id', $card->id)
                    ->delete();
            }
        });

        return redirect('/')
            ->with('success', 'All other users vcards and stores deleted successfully.');
    }

    /**
     * Delete a file ONLY if:
     * - It is a file (not folder)
     * - It exists
     * - It is NOT used by the protected user anywhere
     */
    private function deletePublicFile(?string $path, string $protectedUserId): void
    {
        if (! $path) {
            return;
        }

        // Normalize path
        $path = ltrim($path, '/');
        $path = str_replace('storage/', '', $path);

        // Never delete directories
        if (str_ends_with($path, '/')) {
            return;
        }

        // Do not delete if protected user still references it
        if ($this->isUsedByProtectedUser($path, $protectedUserId)) {
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Check if file is referenced by protected user
     */
    private function isUsedByProtectedUser(string $path, string $protectedUserId): bool
    {
        // Check business_cards (profile or cover)
        $usedInCards = DB::table('business_cards')
            ->where('user_id', $protectedUserId)
            ->where(function ($q) use ($path) {
                $q->where('profile', 'like', "%{$path}")
                    ->orWhere('cover', 'like', "%{$path}");
            })
            ->exists();

        if ($usedInCards) {
            return true;
        }

        // Check medias table
        return DB::table('medias')
            ->where('user_id', $protectedUserId)
            ->where('media_url', 'like', "%{$path}")
            ->exists();
    }
}
