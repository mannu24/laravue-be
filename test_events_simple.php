<?php
/**
 * Simple Tinker Commands for Testing
 * 
 * Run: php artisan tinker
 * Then paste these commands one by one
 */

// Test Badge Unlocked - ONE LINE
event(new \App\Events\Gamification\UserUnlockedBadge(\App\Models\User::find(16), \App\Models\Badge::first()));

// Test Level Up - ONE LINE  
$u = \App\Models\User::with('level')->find(16); $l = \App\Models\Level::orderBy('xp_required', 'desc')->first(); event(new \App\Events\Gamification\UserLeveledUp($u, $l, $u->level));

