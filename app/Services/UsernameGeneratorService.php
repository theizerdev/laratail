<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class UsernameGeneratorService
{
    /**
     * Generate a unique username from a full name
     * 
     * @param string $fullName Full name of the user (e.g., "Theizer Gabriel Gonzalez Lugo")
     * @param int|null $excludeUserId User ID to exclude from uniqueness check (for updates)
     * @return string Unique username
     */
    public function generate(string $fullName, ?int $excludeUserId = null): string
    {
        // Normalize and clean the full name
        $fullName = trim(preg_replace('/\s+/', ' ', $fullName));
        $nameParts = explode(' ', $fullName);
        
        if (count($nameParts) === 0) {
            return $this->generateFallbackUsername($excludeUserId);
        }

        // Extract first names (excluding last last name)
        $firstNames = array_slice($nameParts, 0, -1);
        $lastName = end($nameParts);
        $lastNameClean = strtolower($this->removeAccents(preg_replace('/[^a-zA-Z]/', '', $lastName)));
        
        // Build username progressively adding more initials as needed
        $initials = '';
        foreach ($firstNames as $index => $firstName) {
            $initials .= strtolower(mb_substr($firstName, 0, 1));
            $candidateUsername = $initials . $lastNameClean;
            
            if (!$this->usernameExists($candidateUsername, $excludeUserId)) {
                return $candidateUsername;
            }
        }

        // If we've used all initials and it still exists, add a numeric suffix
        $candidateUsername = $initials . $lastNameClean;
        $counter = 1;
        while ($this->usernameExists($candidateUsername, $excludeUserId)) {
            $candidateUsername = $initials . $lastNameClean . $counter;
            $counter++;
        }

        return $candidateUsername;
    }

    /**
     * Ensure the generated username is unique
     */
    private function ensureUniqueUsername(string $baseUsername, ?int $excludeUserId = null): string
    {
        $username = $baseUsername;
        $counter = 1;
        $middleInitials = '';
        
        while ($this->usernameExists($username, $excludeUserId)) {
            // First try adding middle initials if we have them, then add numbers
            if ($counter === 1) {
                // If first attempt fails, try with second name initial
                $username = $baseUsername;
            } else {
                // If still not unique, append counter
                $username = $baseUsername . $counter;
            }
            $counter++;
        }

        return $username;
    }

    /**
     * Check if username already exists in the database
     */
    private function usernameExists(string $username, ?int $excludeUserId = null): bool
    {
        $query = User::where('username', $username);
        
        if ($excludeUserId) {
            $query->where('id', '!=', $excludeUserId);
        }
        
        return $query->exists();
    }

    /**
     * Generate a fallback username if name parsing fails
     */
    private function generateFallbackUsername(?int $excludeUserId = null): string
    {
        $username = 'user' . Str::random(5);
        
        while ($this->usernameExists($username, $excludeUserId)) {
            $username = 'user' . Str::random(6);
        }
        
        return $username;
    }

    /**
     * Remove accents from characters
     */
    private function removeAccents(string $string): string
    {
        $accents = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u',
            'ñ' => 'n', 'Ñ' => 'n', 'ü' => 'u', 'Ü' => 'u'
        ];
        
        return strtr($string, $accents);
    }
}