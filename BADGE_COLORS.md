# Badge Type Color Gradients

This document outlines the color gradients and styling used for each badge type in the gamification system.

## Badge Types

### 1. Quality Badges
- **Color Scheme**: Blue/Cyan
- **Unlocked (Earned)**:
  - Card Background: `from-blue-500/15 to-cyan-500/15`
  - Border: `border-blue-500/30`
  - Shadow: `shadow-blue-500/30`
  - Image Shadow: `rgba(59,130,246,0.4)` (Blue)
- **Locked**:
  - Card Background: `from-blue-100/50 to-cyan-100/50`
  - Border: `border-blue-300/40`
  - Lock Icon Container: `from-blue-200/60 to-cyan-200/60`
  - Lock Icon Color: `text-blue-500`
  - Overlay: `bg-blue-900/40` (dark mode)

### 2. Contribution Badges
- **Color Scheme**: Green/Emerald (Light)
- **Unlocked (Earned)**:
  - Card Background: `from-green-500/15 to-emerald-500/15`
  - Border: `border-green-500/30`
  - Shadow: `shadow-green-500/30`
  - Image Shadow: `rgba(34,197,94,0.4)` (Green)
- **Locked**:
  - Card Background: `from-green-100/50 to-emerald-100/50`
  - Border: `border-green-300/40`
  - Lock Icon Container: `from-green-200/60 to-emerald-200/60`
  - Lock Icon Color: `text-green-500`
  - Overlay: `bg-green-900/40` (dark mode)

### 3. Rare Badges
- **Color Scheme**: Red/Rose
- **Unlocked (Earned)**:
  - Card Background: `from-red-500/15 to-rose-500/15`
  - Border: `border-red-500/30`
  - Shadow: `shadow-red-500/30`
  - Image Shadow: `rgba(239,68,68,0.4)` (Red)
- **Locked**:
  - Card Background: `from-red-100/50 to-rose-100/50`
  - Border: `border-red-300/40`
  - Lock Icon Container: `from-red-200/60 to-rose-200/60`
  - Lock Icon Color: `text-red-500`
  - Overlay: `bg-red-900/40` (dark mode)
- **Type Indicator**: `from-red-400 to-rose-500` (star icon)

### 4. Event Badges
- **Color Scheme**: Purple/Violet
- **Unlocked (Earned)**:
  - Card Background: `from-purple-500/15 to-violet-500/15`
  - Border: `border-purple-500/30`
  - Shadow: `shadow-purple-500/30`
  - Image Shadow: `rgba(168,85,247,0.4)` (Purple)
- **Locked**:
  - Card Background: `from-purple-100/50 to-violet-100/50`
  - Border: `border-purple-300/40`
  - Lock Icon Container: `from-purple-200/60 to-violet-200/60`
  - Lock Icon Color: `text-purple-500`
  - Overlay: `bg-purple-900/40` (dark mode)
- **Type Indicator**: `from-purple-400 to-violet-500` (star icon)

### 5. Participation Badges
- **Color Scheme**: Yellow/Amber
- **Unlocked (Earned)**:
  - Card Background: `from-yellow-500/15 to-amber-500/15`
  - Border: `border-yellow-500/30`
  - Shadow: `shadow-yellow-500/30`
  - Image Shadow: `rgba(234,179,8,0.4)` (Yellow)
- **Locked**:
  - Card Background: `from-yellow-100/50 to-amber-100/50`
  - Border: `border-yellow-300/40`
  - Lock Icon Container: `from-yellow-200/60 to-amber-200/60`
  - Lock Icon Color: `text-yellow-500`
  - Overlay: `bg-yellow-900/40` (dark mode)

### 6. Consistency Badges
- **Color Scheme**: Dark Green/Emerald
- **Unlocked (Earned)**:
  - Card Background: `from-green-700/15 to-emerald-800/15`
  - Border: `border-green-700/30`
  - Shadow: `shadow-green-700/30`
  - Image Shadow: `rgba(21,128,61,0.4)` (Dark Green)
- **Locked**:
  - Card Background: `from-green-700/30 to-emerald-800/30`
  - Border: `border-green-700/40`
  - Lock Icon Container: `from-green-700/50 to-emerald-800/50`
  - Lock Icon Color: `text-green-700`
  - Overlay: `bg-green-900/50` (dark mode)

## Color Reference

### Tailwind Color Scale
- **100**: Very light (for locked badges)
- **200**: Light (for lock icon containers)
- **300**: Light-medium (for borders)
- **400**: Medium (for type indicators)
- **500**: Standard (for unlocked badges)
- **600-700**: Dark (for consistency badges and dark mode)
- **800-900**: Very dark (for dark mode overlays)

### RGB Values for Drop Shadows
- **Quality (Blue)**: `rgba(59,130,246,0.4)` - Tailwind `blue-500`
- **Contribution (Green)**: `rgba(34,197,94,0.4)` - Tailwind `green-500`
- **Rare (Red)**: `rgba(239,68,68,0.4)` - Tailwind `red-500`
- **Event (Purple)**: `rgba(168,85,247,0.4)` - Tailwind `purple-500`
- **Participation (Yellow)**: `rgba(234,179,8,0.4)` - Tailwind `yellow-500`
- **Consistency (Dark Green)**: `rgba(21,128,61,0.4)` - Tailwind `green-700`

## Visual States

### Unlocked Badges
- Display badge image directly (no circular border)
- Type-specific shadow on image
- Vibrant gradient background (500 shades)
- Full opacity

### Locked Badges
- Display lock icon in circular container
- Type-specific soft gradient background (100 shades)
- Faded overlay with type-specific tint
- Reduced opacity (70%)
- Type-specific lock icon color

## Notes

- All gradients use `bg-gradient-to-br` (top-left to bottom-right)
- Opacity values are applied using Tailwind's opacity modifiers (`/15`, `/30`, etc.)
- Dark mode uses darker shades (800-900) for better contrast
- Type indicators (rare/event) use star icon with type-specific gradient
- Quality and Contribution badges have checkmark and people icons respectively

