# SnapDoc Redesign — Light Peeky Theme

**Date:** 2026-04-02
**Status:** Approved
**Reference:** https://peeky.wayheming.site/
**Approach:** B — Vibrant & Bold (light theme adaptation of Peeky's dark aesthetic)

## Overview

Redesign the photo-processor web app (now **SnapDoc**) to match the visual style of peeky.wayheming.site, adapted to a light theme. The current purple gradient hero + flat white sections are replaced with a unified light design featuring gradient orbs, grid background, animated gradient text, glow cards, and a navigation bar.

## Name Change

- App name: **SnapDoc** (was "Document Photos")
- Logo text uses animated gradient (indigo → violet → blue)

## Visual System

### Background Layer
- **Base:** `#fafafe` (near-white)
- **Grid pattern:** 60×60px, `rgba(99,102,241, 0.06)` lines — covers full page (fixed)
- **Gradient orbs (fixed, behind content):**
  - Orb 1: 600px, indigo at 15% opacity, top-left, 8s float animation
  - Orb 2: 500px, violet at 12% opacity, bottom-right, 10s float animation (reverse)
  - Orb 3: 400px, blue at 10% opacity, center, 12s float animation

### Color Palette
- **Primary gradient:** `#6366f1` (indigo) → `#a78bfa` (violet) → `#60a5fa` (blue)
- **Text primary:** `#111827` / `#1f2937`
- **Text secondary:** `#6b7280`
- **Text muted:** `#9ca3af`
- **Accent backgrounds:** primary colors at 8% opacity
- **Card borders:** `rgba(99,102,241, 0.08-0.12)`
- **Card glow on hover:** `0 0 30px rgba(99,102,241, 0.08)`

### Typography
- **Font:** Inter (already in use)
- **Animated gradient text:** `background-size: 200%`, 6s infinite `gradient-shift` animation on hero heading and CTA heading
- **Hero heading:** 52px, weight 800
- **Section headings:** 36px, weight 800
- **Body text:** 14-18px, weight 400-500

## Page Structure

### 1. Navigation Bar
- Logo "SnapDoc" with animated gradient text
- Links: "How It Works", "Features", "Privacy Policy"
- No auth buttons (no user accounts)
- Sticky or static — static for now

### 2. Hero Section (replaces gradient hero)
- **No purple gradient background** — uses the global light bg + orbs + grid
- Two-column layout (text left, form right)
- Left side:
  - Badge: "100% Free — No Registration Required" (indigo tint bg, indigo border)
  - Heading: "Perfect Document Photos" (animated gradient) + "in Seconds" (muted gray)
  - Description paragraph
  - Stats row: Free / <30s / AI
- Right side:
  - Upload form card (white bg, rounded-20px, indigo border glow)
  - Glow intensifies on hover
  - **No wave SVG divider** — sections flow naturally on the grid background

### 3. How It Works Section
- 3 step cards with white bg, subtle border, hover glow effect
- Step number (large, 6% opacity indigo) in top-right corner
- Gradient icon box (indigo→violet)
- **Step 1 updated text:** "Take a selfie or upload any photo with a clear face. For best results, use a well-lit photo against a light background."
- Steps 2, 3 text unchanged

### 4. Features Section
- 4 feature cards in a grid
- Colored icon circles (indigo, violet, pink, emerald at 8% opacity)
- Hover: white bg + subtle glow shadow
- Content unchanged

### 5. Bottom CTA
- White card with glow border/shadow (not gradient bg)
- Heading uses animated gradient text
- "Get Started Now" button with gradient bg + indigo shadow
- Scrolls to top on click

### 6. Footer
- Minimal: copyright + privacy policy link
- Top border: `rgba(99,102,241, 0.08)`
- Light text on light bg (not dark footer)

## What's Removed
- Purple gradient hero background (`gradient-bg` on hero)
- Wave SVG divider between hero and content
- Dark footer (`bg-gray-900`)
- `blob-1`, `blob-2` classes (replaced by global fixed orbs)
- `glass-dark` class

## What's Added
- Fixed grid background pattern
- Fixed gradient orbs (3)
- Navigation bar with gradient logo
- Animated gradient text (hero heading, CTA heading, logo)
- Card hover glow effects
- Light footer

## Files to Modify
- `app/resources/views/layouts/app.blade.php` — styles, nav, footer, grid/orbs, rename to SnapDoc
- `app/resources/views/home.blade.php` — hero restructure, remove wave, sections styling
- `app/resources/views/livewire/photo-processor.blade.php` — form card styling adjustments (minor)

## Content Change
- Step 1 description: "Take a selfie or upload any photo with a clear face. For best results, use a well-lit photo against a light background."
