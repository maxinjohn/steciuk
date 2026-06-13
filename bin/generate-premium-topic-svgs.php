<?php

/**
 * Generates premium branded topic illustrations for card thumbnails.
 * Run: php bin/generate-premium-topic-svgs.php
 */

$outDir = __DIR__.'/../public/images/topics';

$illustrations = [
    'sunday-school' => <<<'SVG'
<g opacity="0.95">
  <rect x="220" y="130" width="360" height="240" rx="20" fill="none" stroke="#ece6dc" stroke-width="2.5" opacity="0.35"/>
  <path d="M280 340V175h240v165" fill="none" stroke="#b8892a" stroke-width="3.5"/>
  <path d="M300 175l120-55 120 55" fill="none" stroke="#ece6dc" stroke-width="3" opacity="0.85"/>
  <rect x="330" y="220" width="140" height="95" rx="8" fill="none" stroke="#ece6dc" stroke-width="2.5" opacity="0.5"/>
  <path d="M350 245h100M350 270h80M350 295h60" stroke="#b8892a" stroke-width="2.5" opacity="0.65"/>
  <circle cx="520" cy="155" r="22" fill="none" stroke="#b8892a" stroke-width="3"/>
  <path d="M520 133v44M498 155h44" stroke="#b8892a" stroke-width="3"/>
  <path d="M400 130l0-28M388 102h24" stroke="#ece6dc" stroke-width="2.5" opacity="0.7"/>
</g>
SVG,
    'youth-fellowship' => <<<'SVG'
<g opacity="0.95">
  <ellipse cx="400" cy="360" rx="180" ry="28" fill="#b8892a" opacity="0.12"/>
  <circle cx="320" cy="240" r="38" fill="none" stroke="#ece6dc" stroke-width="3"/>
  <circle cx="400" cy="215" r="42" fill="none" stroke="#b8892a" stroke-width="3.5"/>
  <circle cx="480" cy="240" r="38" fill="none" stroke="#ece6dc" stroke-width="3"/>
  <path d="M290 310c20-45 50-65 90-65s70 20 90 65" fill="none" stroke="#ece6dc" stroke-width="3" opacity="0.7"/>
  <path d="M370 310c15-35 35-50 60-50s45 15 60 50" fill="none" stroke="#b8892a" stroke-width="3" opacity="0.8"/>
  <path d="M250 180 Q400 80 550 180" fill="none" stroke="#b8892a" stroke-width="2" opacity="0.35"/>
</g>
SVG,
    'womens-fellowship' => <<<'SVG'
<g opacity="0.95">
  <circle cx="400" cy="250" r="90" fill="none" stroke="#b8892a" stroke-width="2" opacity="0.35"/>
  <circle cx="340" cy="230" r="28" fill="none" stroke="#ece6dc" stroke-width="3"/>
  <circle cx="400" cy="210" r="32" fill="none" stroke="#b8892a" stroke-width="3.5"/>
  <circle cx="460" cy="230" r="28" fill="none" stroke="#ece6dc" stroke-width="3"/>
  <path d="M310 310c25-40 55-55 90-55s65 15 90 55" fill="none" stroke="#ece6dc" stroke-width="3" opacity="0.75"/>
  <path d="M370 165 Q400 120 430 165" fill="none" stroke="#b8892a" stroke-width="2.5" opacity="0.6"/>
  <path d="M355 320 Q400 350 445 320" fill="none" stroke="#b8892a" stroke-width="2" opacity="0.4"/>
</g>
SVG,
    'choir' => <<<'SVG'
<g opacity="0.95">
  <path d="M280 320c0-80 50-120 120-120s120 40 120 120" fill="none" stroke="#ece6dc" stroke-width="3" opacity="0.5"/>
  <ellipse cx="400" cy="195" rx="55" ry="18" fill="none" stroke="#b8892a" stroke-width="3"/>
  <path d="M345 195v90M455 195v90" stroke="#ece6dc" stroke-width="3"/>
  <path d="M310 250 Q340 220 370 250 Q400 280 430 250 Q460 220 490 250" fill="none" stroke="#b8892a" stroke-width="2.5" opacity="0.75"/>
  <circle cx="250" cy="180" r="8" fill="#b8892a" opacity="0.8"/><circle cx="275" cy="155" r="6" fill="#ece6dc" opacity="0.6"/>
  <circle cx="525" cy="180" r="8" fill="#b8892a" opacity="0.8"/><circle cx="550" cy="155" r="6" fill="#ece6dc" opacity="0.6"/>
  <path d="M230 200 Q260 170 290 200" fill="none" stroke="#ece6dc" stroke-width="2" opacity="0.5"/>
  <path d="M510 200 Q540 170 570 200" fill="none" stroke="#ece6dc" stroke-width="2" opacity="0.5"/>
</g>
SVG,
    'prayer-groups' => <<<'SVG'
<g opacity="0.95">
  <ellipse cx="400" cy="330" rx="120" ry="18" fill="#b8892a" opacity="0.15"/>
  <path d="M340 310c0-55 25-85 60-85s60 30 60 85" fill="none" stroke="#ece6dc" stroke-width="3"/>
  <path d="M370 225v85M430 225v85" stroke="#ece6dc" stroke-width="3"/>
  <path d="M355 280c15-20 35-30 45-30s30 10 45 30" fill="none" stroke="#b8892a" stroke-width="3"/>
  <circle cx="400" cy="195" r="28" fill="none" stroke="#b8892a" stroke-width="3"/>
  <path d="M400 167v56M382 195h36" stroke="#b8892a" stroke-width="2.5" opacity="0.8"/>
  <path d="M280 250 Q320 200 360 250" fill="none" stroke="#ece6dc" stroke-width="2" opacity="0.35"/>
  <path d="M440 250 Q480 200 520 250" fill="none" stroke="#ece6dc" stroke-width="2" opacity="0.35"/>
</g>
SVG,
    'evangelism-mission' => <<<'SVG'
<g opacity="0.95">
  <circle cx="400" cy="255" r="95" fill="none" stroke="#ece6dc" stroke-width="2.5" opacity="0.35"/>
  <ellipse cx="400" cy="255" rx="95" ry="38" fill="none" stroke="#b8892a" stroke-width="2" opacity="0.45"/>
  <path d="M400 160v190M305 255h190" stroke="#ece6dc" stroke-width="2" opacity="0.3"/>
  <circle cx="520" cy="200" r="14" fill="#b8892a" opacity="0.85"/>
  <circle cx="290" cy="290" r="10" fill="#ece6dc" opacity="0.55"/>
  <circle cx="480" cy="310" r="8" fill="#b8892a" opacity="0.5"/>
  <path d="M400 130l0-35M385 95h30" stroke="#b8892a" stroke-width="3"/>
  <path d="M370 340 Q400 365 430 340" fill="none" stroke="#b8892a" stroke-width="2" opacity="0.4"/>
</g>
SVG,
    'pastoral-care' => <<<'SVG'
<g opacity="0.95">
  <path d="M400 155c-35 0-55 25-55 55 0 40 55 95 55 95s55-55 55-95c0-30-20-55-55-55z" fill="none" stroke="#b8892a" stroke-width="3.5"/>
  <path d="M320 290c25-30 55-45 80-45s55 15 80 45" fill="none" stroke="#ece6dc" stroke-width="3" opacity="0.7"/>
  <path d="M350 320 Q400 350 450 320" fill="none" stroke="#ece6dc" stroke-width="2.5" opacity="0.5"/>
  <circle cx="280" cy="220" r="18" fill="none" stroke="#ece6dc" stroke-width="2" opacity="0.4"/>
  <circle cx="520" cy="220" r="18" fill="none" stroke="#ece6dc" stroke-width="2" opacity="0.4"/>
</g>
SVG,
    'community-fellowship' => <<<'SVG'
<g opacity="0.95">
  <ellipse cx="400" cy="310" rx="160" ry="22" fill="#b8892a" opacity="0.12"/>
  <rect x="270" y="265" width="260" height="12" rx="6" fill="#ece6dc" opacity="0.35"/>
  <circle cx="310" cy="220" r="24" fill="none" stroke="#ece6dc" stroke-width="3"/>
  <circle cx="400" cy="200" r="28" fill="none" stroke="#b8892a" stroke-width="3.5"/>
  <circle cx="490" cy="220" r="24" fill="none" stroke="#ece6dc" stroke-width="3"/>
  <path d="M285 265c15-35 35-50 50-50M515 265c-15-35-35-50-50-50" fill="none" stroke="#ece6dc" stroke-width="2.5" opacity="0.6"/>
  <circle cx="340" cy="285" r="14" fill="none" stroke="#b8892a" stroke-width="2" opacity="0.7"/>
  <circle cx="460" cy="285" r="14" fill="none" stroke="#b8892a" stroke-width="2" opacity="0.7"/>
</g>
SVG,
    'ministry-default' => <<<'SVG'
<g opacity="0.95">
  <path d="M280 340V180h240v160" fill="none" stroke="#b8892a" stroke-width="3.5"/>
  <path d="M300 180l100-48 100 48" fill="none" stroke="#ece6dc" stroke-width="3" opacity="0.85"/>
  <rect x="370" y="230" width="60" height="70" rx="4" fill="none" stroke="#ece6dc" stroke-width="2.5" opacity="0.55"/>
  <circle cx="400" cy="155" r="18" fill="none" stroke="#b8892a" stroke-width="3"/>
  <path d="M400 137v36M382 155h36" stroke="#b8892a" stroke-width="3"/>
  <path d="M250 340h300" stroke="#ece6dc" stroke-width="2" opacity="0.25"/>
</g>
SVG,
    'worship' => <<<'SVG'
<g opacity="0.95">
  <path d="M400 130l0-40M385 90h30" stroke="#b8892a" stroke-width="3.5"/>
  <path d="M400 130l-35 60h70z" fill="none" stroke="#b8892a" stroke-width="3"/>
  <rect x="320" y="280" width="160" height="60" rx="8" fill="none" stroke="#ece6dc" stroke-width="2.5" opacity="0.45"/>
  <path d="M350 190v90M450 190v90" stroke="#ece6dc" stroke-width="3" opacity="0.6"/>
  <ellipse cx="400" cy="350" rx="140" ry="20" fill="#b8892a" opacity="0.1"/>
  <path d="M280 250 Q400 180 520 250" fill="none" stroke="#b8892a" stroke-width="2" opacity="0.35"/>
</g>
SVG,
    'fellowship' => <<<'SVG'
<g opacity="0.95">
  <circle cx="320" cy="240" r="45" fill="none" stroke="#ece6dc" stroke-width="3"/>
  <circle cx="480" cy="240" r="45" fill="none" stroke="#ece6dc" stroke-width="3"/>
  <path d="M365 240h70" stroke="#b8892a" stroke-width="4" opacity="0.8"/>
  <circle cx="400" cy="240" r="18" fill="#b8892a" opacity="0.35"/>
  <path d="M290 320 Q400 280 510 320" fill="none" stroke="#ece6dc" stroke-width="2.5" opacity="0.5"/>
  <path d="M310 350 Q400 390 490 350" fill="none" stroke="#b8892a" stroke-width="2" opacity="0.35"/>
</g>
SVG,
    'communion' => <<<'SVG'
<g opacity="0.95">
  <path d="M370 280c0-55 15-85 30-85s30 30 30 85" fill="none" stroke="#ece6dc" stroke-width="3"/>
  <ellipse cx="400" cy="280" rx="30" ry="8" fill="none" stroke="#b8892a" stroke-width="2.5"/>
  <path d="M430 280h60" stroke="#ece6dc" stroke-width="3"/>
  <ellipse cx="520" cy="280" rx="28" ry="10" fill="none" stroke="#b8892a" stroke-width="3"/>
  <path d="M400 130l0-35M385 95h30" stroke="#b8892a" stroke-width="3"/>
  <path d="M400 130l-28 48h56z" fill="none" stroke="#b8892a" stroke-width="2.5" opacity="0.75"/>
  <circle cx="400" cy="200" r="40" fill="none" stroke="#ece6dc" stroke-width="2" opacity="0.25"/>
</g>
SVG,
    'event' => <<<'SVG'
<g opacity="0.95">
  <rect x="260" y="155" width="280" height="210" rx="22" fill="none" stroke="#ece6dc" stroke-width="3"/>
  <path d="M260 215h280" stroke="#ece6dc" stroke-width="2.5" opacity="0.45"/>
  <path d="M310 130v50M490 130v50" stroke="#b8892a" stroke-width="4"/>
  <circle cx="340" cy="280" r="14" fill="#b8892a" opacity="0.85"/>
  <circle cx="400" cy="280" r="14" fill="#b8892a" opacity="0.55"/>
  <circle cx="460" cy="280" r="14" fill="#b8892a" opacity="0.35"/>
  <circle cx="340" cy="330" r="14" fill="#ece6dc" opacity="0.35"/>
  <circle cx="400" cy="330" r="14" fill="#ece6dc" opacity="0.25"/>
  <path d="M540 180l25-15 15 25-40-10z" fill="#b8892a" opacity="0.7"/>
</g>
SVG,
    'news' => <<<'SVG'
<g opacity="0.95">
  <rect x="270" y="150" width="260" height="210" rx="16" fill="none" stroke="#ece6dc" stroke-width="3"/>
  <path d="M300 195h200M300 235h160M300 275h180M300 315h120" stroke="#ece6dc" stroke-width="2.5" opacity="0.45"/>
  <rect x="300" y="195" width="70" height="55" rx="6" fill="none" stroke="#b8892a" stroke-width="2.5" opacity="0.75"/>
  <circle cx="520" cy="175" r="22" fill="none" stroke="#b8892a" stroke-width="3"/>
  <path d="M508 175h24M520 163v24" stroke="#b8892a" stroke-width="2.5"/>
</g>
SVG,
    'sermon' => <<<'SVG'
<g opacity="0.95">
  <rect x="290" y="200" width="220" height="130" rx="10" fill="none" stroke="#ece6dc" stroke-width="3" opacity="0.55"/>
  <path d="M320 240h160M320 275h130M320 305h100" stroke="#b8892a" stroke-width="2.5" opacity="0.65"/>
  <path d="M400 130l0-35M385 95h30" stroke="#b8892a" stroke-width="3"/>
  <path d="M250 280 Q400 160 550 280" fill="none" stroke="#ece6dc" stroke-width="2" opacity="0.25"/>
  <ellipse cx="400" cy="355" rx="100" ry="14" fill="#b8892a" opacity="0.12"/>
</g>
SVG,
    'resource' => <<<'SVG'
<g opacity="0.95">
  <rect x="300" y="160" width="120" height="160" rx="10" fill="none" stroke="#ece6dc" stroke-width="3" opacity="0.55"/>
  <rect x="340" y="140" width="120" height="160" rx="10" fill="none" stroke="#ece6dc" stroke-width="3" opacity="0.75"/>
  <rect x="380" y="120" width="120" height="160" rx="10" fill="none" stroke="#b8892a" stroke-width="3"/>
  <path d="M410 165h60M410 200h45M410 235h55" stroke="#ece6dc" stroke-width="2.5" opacity="0.5"/>
  <path d="M430 120l20-25 20 25" fill="none" stroke="#b8892a" stroke-width="2.5" opacity="0.7"/>
</g>
SVG,
    'prayer' => <<<'SVG'
<g opacity="0.95">
  <ellipse cx="400" cy="310" rx="80" ry="14" fill="#b8892a" opacity="0.2"/>
  <path d="M355 290c0-50 20-80 45-80s45 30 45 80" fill="none" stroke="#ece6dc" stroke-width="3"/>
  <path d="M385 210v80M415 210v80" stroke="#ece6dc" stroke-width="3"/>
  <path d="M370 265c15-18 30-25 45-25s30 7 45 25" fill="none" stroke="#b8892a" stroke-width="3"/>
  <path d="M400 175 Q420 140 440 175 Q460 210 400 210 Q340 210 360 175 Q380 140 400 175" fill="none" stroke="#b8892a" stroke-width="2.5" opacity="0.65"/>
</g>
SVG,
    'mission' => <<<'SVG'
<g opacity="0.95">
  <path d="M280 300 Q400 140 520 300" fill="none" stroke="#ece6dc" stroke-width="2.5" opacity="0.35"/>
  <circle cx="350" cy="260" r="16" fill="#b8892a" opacity="0.8"/>
  <circle cx="400" cy="220" r="20" fill="#b8892a"/>
  <circle cx="450" cy="260" r="16" fill="#b8892a" opacity="0.8"/>
  <path d="M400 200l0-45M385 155h30" stroke="#b8892a" stroke-width="3"/>
  <path d="M350 260 L400 220 L450 260" fill="none" stroke="#ece6dc" stroke-width="2" opacity="0.4"/>
  <ellipse cx="400" cy="330" rx="130" ry="18" fill="#b8892a" opacity="0.1"/>
</g>
SVG,
    'default' => <<<'SVG'
<g opacity="0.95">
  <circle cx="400" cy="240" r="100" fill="none" stroke="#ece6dc" stroke-width="2" opacity="0.2"/>
  <path d="M400 145l0-40M385 105h30" stroke="#b8892a" stroke-width="3.5"/>
  <path d="M400 145l-40 70h80z" fill="none" stroke="#b8892a" stroke-width="3"/>
  <path d="M320 280 Q400 200 480 280" fill="none" stroke="#ece6dc" stroke-width="2.5" opacity="0.45"/>
  <path d="M280 340 Q400 300 520 340" fill="none" stroke="#b8892a" stroke-width="2" opacity="0.3"/>
</g>
SVG,
    'worship-location' => <<<'SVG'
<g opacity="0.95">
  <path d="M280 340V185h240v155" fill="none" stroke="#b8892a" stroke-width="3.5"/>
  <path d="M300 185l100-48 100 48" fill="none" stroke="#ece6dc" stroke-width="3" opacity="0.85"/>
  <circle cx="400" cy="155" r="18" fill="none" stroke="#b8892a" stroke-width="3"/>
  <path d="M400 137v36M382 155h36" stroke="#b8892a" stroke-width="3"/>
  <path d="M520 260c0-28 18-48 40-48s40 20 40 48" fill="none" stroke="#ece6dc" stroke-width="3"/>
  <circle cx="560" cy="212" r="16" fill="none" stroke="#b8892a" stroke-width="3"/>
  <circle cx="560" cy="212" r="5" fill="#b8892a"/>
  <path d="M560 228v32" stroke="#b8892a" stroke-width="3"/>
  <ellipse cx="400" cy="355" rx="150" ry="18" fill="#b8892a" opacity="0.1"/>
</g>
SVG,
];

function premiumSvg(string $id, string $illustration): string
{
    return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 500" role="img" aria-hidden="true">
  <defs>
    <linearGradient id="{$id}-bg" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#080d14"/>
      <stop offset="38%" stop-color="#121a28"/>
      <stop offset="100%" stop-color="#243044"/>
    </linearGradient>
    <linearGradient id="{$id}-mesh" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#67e8f9" stop-opacity="0.06"/>
      <stop offset="50%" stop-color="#c4b5fd" stop-opacity="0.05"/>
      <stop offset="100%" stop-color="#d4a843" stop-opacity="0.08"/>
    </linearGradient>
    <radialGradient id="{$id}-glow" cx="50%" cy="34%" r="72%">
      <stop offset="0%" stop-color="#f5dfa0" stop-opacity="0.34"/>
      <stop offset="45%" stop-color="#d4a843" stop-opacity="0.14"/>
      <stop offset="100%" stop-color="#b8892a" stop-opacity="0"/>
    </radialGradient>
    <radialGradient id="{$id}-beam" cx="82%" cy="12%" r="58%">
      <stop offset="0%" stop-color="#ece6dc" stop-opacity="0.12"/>
      <stop offset="100%" stop-color="#ece6dc" stop-opacity="0"/>
    </radialGradient>
    <radialGradient id="{$id}-aurora" cx="12%" cy="88%" r="55%">
      <stop offset="0%" stop-color="#818cf8" stop-opacity="0.12"/>
      <stop offset="100%" stop-color="#22d3ee" stop-opacity="0"/>
    </radialGradient>
    <pattern id="{$id}-grid" width="48" height="48" patternUnits="userSpaceOnUse">
      <path d="M48 0H0V48" fill="none" stroke="#ece6dc" stroke-width="0.55" opacity="0.055"/>
      <path d="M24 0v48M0 24h48" fill="none" stroke="#67e8f9" stroke-width="0.35" opacity="0.035"/>
    </pattern>
    <filter id="{$id}-soft" x="-20%" y="-20%" width="140%" height="140%">
      <feGaussianBlur stdDeviation="1.5" result="b"/>
      <feMerge><feMergeNode in="b"/><feMergeNode in="SourceGraphic"/></feMerge>
    </filter>
    <filter id="{$id}-glow-filter" x="-30%" y="-30%" width="160%" height="160%">
      <feGaussianBlur stdDeviation="8" result="blur"/>
      <feMerge><feMergeNode in="blur"/><feMergeNode in="SourceGraphic"/></feMerge>
    </filter>
  </defs>
  <rect width="800" height="500" fill="url(#{$id}-bg)"/>
  <rect width="800" height="500" fill="url(#{$id}-grid)"/>
  <rect width="800" height="500" fill="url(#{$id}-mesh)"/>
  <rect width="800" height="500" fill="url(#{$id}-glow)"/>
  <rect width="800" height="500" fill="url(#{$id}-beam)"/>
  <rect width="800" height="500" fill="url(#{$id}-aurora)"/>
  <circle cx="680" cy="85" r="140" fill="#b8892a" opacity="0.045"/>
  <circle cx="110" cy="415" r="105" fill="#ece6dc" opacity="0.03"/>
  <circle cx="620" cy="400" r="72" fill="#c4b5fd" opacity="0.035"/>
  <circle cx="180" cy="120" r="48" fill="#67e8f9" opacity="0.025"/>
  <path d="M0 420 Q200 372 400 420 T800 420" fill="none" stroke="#b8892a" stroke-width="1.5" opacity="0.14"/>
  <path d="M0 460 Q260 420 520 460 T800 460" fill="none" stroke="#818cf8" stroke-width="1" opacity="0.1"/>
  <path d="M48 48 H156 M48 48 V156 M752 48 H644 M752 48 V156 M48 452 H156 M48 452 V344 M752 452 H644 M752 452 V344" fill="none" stroke="#d4a843" stroke-width="2" opacity="0.18" stroke-linecap="round"/>
  <g filter="url(#{$id}-soft)">
    {$illustration}
  </g>
  <g filter="url(#{$id}-glow-filter)" opacity="0.85">
    <circle cx="120" cy="120" r="2.5" fill="#d4a843" opacity="0.55"/>
    <circle cx="680" cy="180" r="2" fill="#ece6dc" opacity="0.45"/>
    <circle cx="540" cy="90" r="2" fill="#c4b5fd" opacity="0.4"/>
    <circle cx="200" cy="300" r="1.5" fill="#67e8f9" opacity="0.35"/>
    <circle cx="710" cy="320" r="1.5" fill="#d4a843" opacity="0.3"/>
  </g>
</svg>
SVG;
}

if (! is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}

foreach ($illustrations as $name => $art) {
    $id = str_replace('.', '-', $name);
    file_put_contents("{$outDir}/{$name}.svg", premiumSvg($id, $art));
    echo "Wrote {$name}.svg\n";
}

echo 'Done — '.count($illustrations)." premium topic SVGs.\n";
