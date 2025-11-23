<?php
/**
 * Tinker Commands to Test Badge Unlocked and Level Up Events
 * 
 * Run these commands in Laravel Tinker:
 * php artisan tinker
 * 
 * Then copy and paste the commands below
 */

// ============================================
// ONE-LINER: Test Badge Unlocked Event for User ID 16
// ============================================
event(new \App\Events\Gamification\UserUnlockedBadge(\App\Models\User::find(16), \App\Models\Badge::first()));

// ============================================
// ONE-LINER: Test Level Up Event for User ID 16
// ============================================
$user = \App\Models\User::with('level')->find(16); $currentLevel = $user->level; $nextLevel = \App\Models\Level::where('xp_required', '>', $currentLevel->xp_required ?? 0)->orderBy('xp_required')->first() ?? \App\Models\Level::where('id', '!=', $currentLevel->id ?? 0)->first(); event(new \App\Events\Gamification\UserLeveledUp($user, $nextLevel, $currentLevel));

// ============================================
// DETAILED VERSION: Test Badge Unlocked Event
// ============================================
$user = \App\Models\User::find(16);
$badge = \App\Models\Badge::first(); // Or: \App\Models\Badge::where('slug', 'first-login')->first();
event(new \App\Events\Gamification\UserUnlockedBadge($user, $badge));
echo "✓ Badge unlocked event fired for user {$user->id}!\n";

// ============================================
// DETAILED VERSION: Test Level Up Event
// ============================================
$user = \App\Models\User::with('level')->find(16);
$currentLevel = $user->level;
$nextLevel = \App\Models\Level::where('xp_required', '>', $currentLevel->xp_required ?? 0)
    ->orderBy('xp_required', 'asc')
    ->first();

// If no next level, get any different level for testing
if (!$nextLevel) {
    $nextLevel = \App\Models\Level::where('id', '!=', $currentLevel->id ?? 0)->first();
}

event(new \App\Events\Gamification\UserLeveledUp($user, $nextLevel, $currentLevel));
echo "✓ Level up event fired for user {$user->id}!\n";

// ============================================
// QUICK TEST: Fire Both Events
// ============================================
$user = \App\Models\User::with('level')->find(16);
$badge = \App\Models\Badge::first();
$level = \App\Models\Level::orderBy('xp_required', 'desc')->first();

event(new \App\Events\Gamification\UserUnlockedBadge($user, $badge));
echo "✓ Badge unlocked event fired\n";

event(new \App\Events\Gamification\UserLeveledUp($user, $level, $user->level));
echo "✓ Level up event fired\n";

