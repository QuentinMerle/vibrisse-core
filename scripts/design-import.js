#!/usr/bin/env node
/**
 * design-import.js — Design Tokens to vibrisse-core converter
 *
 * Supports:
 *   - W3C Design Tokens JSON  (standard format, tool-agnostic)
 *   - CSS Custom Properties    (universal export from any design tool)
 *
 * For visual formats (PNG, PDF, URL), use the AI Skill `.ai/skills/design-import/`.
 *
 * Usage:
 *   node scripts/design-import.js tokens.json
 *   node scripts/design-import.js styles.css
 *   node scripts/design-import.js tokens.json --dry-run
 *
 * @package VibrisseCore
 */

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname( fileURLToPath( import.meta.url ) );
const ROOT      = path.resolve( __dirname, '..' );
const INPUT     = process.argv[2];
const DRY_RUN   = process.argv.includes( '--dry-run' );

// ─────────────────────────────────────────────────────────
// Utilities
// ─────────────────────────────────────────────────────────

const log   = ( msg ) => console.log( `  ${msg}` );
const ok    = ( msg ) => console.log( `  ✅ ${msg}` );
const warn  = ( msg ) => console.log( `  ⚠️  ${msg}` );
const error = ( msg ) => { console.error( `  ❌ ${msg}` ); process.exit( 1 ); };
const hr    = ()      => console.log( '─'.repeat( 45 ) );

// ─────────────────────────────────────────────────────────
// Parsers
// ─────────────────────────────────────────────────────────

/**
 * Parse W3C Design Tokens JSON.
 * Spec: https://design-tokens.github.io/community-group/format/
 * Compatible with: Token Studio, Style Dictionary, Theo, Penpot export, etc.
 */
function parseW3CTokens( json ) {
	const result = { colors: {}, fonts: {} };

	function walk( node, path = [] ) {
		for ( const [ key, value ] of Object.entries( node ) ) {
			if ( key.startsWith( '$' ) ) continue; // Metadata

			const currentPath = [ ...path, key ];

			if ( value && typeof value === 'object' && '$value' in value ) {
				const type  = value.$type || inferType( value.$value );
				const token = { name: currentPath.join( '.' ), value: value.$value, type };

				if ( type === 'color' ) {
					result.colors[ token.name ] = token.value;
				} else if ( type === 'fontFamily' ) {
					result.fonts[ token.name ] = token.value;
				}
			} else if ( value && typeof value === 'object' ) {
				walk( value, currentPath );
			}
		}
	}

	function inferType( value ) {
		if ( typeof value === 'string' && /^#[0-9a-f]{3,8}$/i.test( value ) ) return 'color';
		if ( typeof value === 'string' && /^rgb/i.test( value ) ) return 'color';
		if ( typeof value === 'string' && /^hsl/i.test( value ) ) return 'color';
		return 'unknown';
	}

	walk( json );
	return result;
}

/**
 * Parse CSS Custom Properties.
 * Compatible with any tool that exports CSS (Figma, Penpot, XD, Zeplin, etc.)
 */
function parseCSSVars( css ) {
	const result = { colors: {}, fonts: {} };

	// Colors
	const colorRegex = /--([\w-]+)\s*:\s*(#[0-9a-f]{3,8}|rgb[a]?\([^)]+\)|hsl[a]?\([^)]+\))/gi;
	let match;
	while ( ( match = colorRegex.exec( css ) ) !== null ) {
		result.colors[ match[1] ] = match[2];
	}

	// Fonts
	const fontRegex = /--([\w-]*font[\w-]*)\s*:\s*['"]?([^;'"]+?)['"]?\s*[,;]/gi;
	while ( ( match = fontRegex.exec( css ) ) !== null ) {
		result.fonts[ match[1] ] = match[2].trim();
	}

	return result;
}

// ─────────────────────────────────────────────────────────
// Mapping to vibrisse-core slots
// ─────────────────────────────────────────────────────────

/**
 * Attempts to automatically map found tokens to the 4 slots.
 * Uses heuristics on token names.
 */
function mapToSlots( tokens ) {
	const slots     = { base: null, contrast: null, muted: null, accent: null };
	const fontSlots = { sans: null, serif: null };
	const unmapped  = { colors: [], fonts: [] };

	// Color heuristics
	const colorHeuristics = {
		base:     [ 'base', 'background', 'bg', 'white', 'light', 'surface', 'canvas' ],
		contrast: [ 'contrast', 'text', 'foreground', 'fg', 'dark', 'black', 'primary-text', 'body' ],
		muted:    [ 'muted', 'secondary', 'subtle', 'neutral', 'gray', 'grey', 'disabled', 'border' ],
		accent:   [ 'accent', 'primary', 'brand', 'action', 'cta', 'interactive', 'link', 'highlight' ],
	};

	for ( const [ tokenName, value ] of Object.entries( tokens.colors ) ) {
		const lower = tokenName.toLowerCase();
		let mapped  = false;

		for ( const [ slot, keywords ] of Object.entries( colorHeuristics ) ) {
			if ( keywords.some( k => lower.includes( k ) ) && ! slots[ slot ] ) {
				slots[ slot ] = value;
				mapped = true;
				break;
			}
		}

		if ( ! mapped ) unmapped.colors.push( { name: tokenName, value } );
	}

	// Font heuristics
	const fontHeuristics = {
		sans:  [ 'sans', 'body', 'text', 'ui', 'regular', 'base' ],
		serif: [ 'serif', 'heading', 'display', 'title', 'h1', 'editorial' ],
	};

	for ( const [ tokenName, value ] of Object.entries( tokens.fonts ) ) {
		const lower = tokenName.toLowerCase();
		let mapped  = false;

		for ( const [ slot, keywords ] of Object.entries( fontHeuristics ) ) {
			if ( keywords.some( k => lower.includes( k ) ) && ! fontSlots[ slot ] ) {
				fontSlots[ slot ] = value.replace( /['"]/g, '' ).split( ',' )[0].trim();
				mapped = true;
				break;
			}
		}

		if ( ! mapped ) unmapped.fonts.push( { name: tokenName, value } );
	}

	return { slots, fontSlots, unmapped };
}

// ─────────────────────────────────────────────────────────
// theme.json patch generation
// ─────────────────────────────────────────────────────────

function generateThemeJsonPatch( slots, fontSlots ) {
	const palette = Object.entries( slots )
		.filter( ( [ , v ] ) => v )
		.map( ( [ slug, color ] ) => ( {
			slug,
			color,
			name: slug.charAt( 0 ).toUpperCase() + slug.slice( 1 ),
		} ) );

	const fontFamilies = Object.entries( fontSlots )
		.filter( ( [ , v ] ) => v )
		.map( ( [ slug, name ] ) => ( {
			slug,
			name,
			fontFamily: `'${name}', ${ slug === 'serif' ? 'Georgia, serif' : 'system-ui, sans-serif' }`,
			fontFace: [ {
				fontFamily: name,
				// Replace this URL with the actual Google Fonts woff2 URL for the chosen typeface.
				// Find it via: https://fonts.googleapis.com/css2?family=[FontName]&display=swap
				src: [ `https://fonts.gstatic.com/TODO_replace_with_actual_url_for_${name.replace( /\s+/g, '_' )}` ],
				fontWeight: slug === 'serif' ? '700' : '300 700',
				fontStyle: 'normal',
			} ],
		} ) );

	return {
		$schema: 'https://schemas.wp.org/trunk/theme.json',
		version: 3,
		settings: {
			color: { palette },
			typography: { fontFamilies },
		},
	};
}

// ─────────────────────────────────────────────────────────
// Main
// ─────────────────────────────────────────────────────────

if ( ! INPUT ) {
	error( 'Usage: node scripts/design-import.js [tokens.json | styles.css] [--dry-run]' );
}

const inputPath = path.resolve( process.cwd(), INPUT );
if ( ! fs.existsSync( inputPath ) ) {
	error( `File not found: ${inputPath}` );
}

const ext     = path.extname( INPUT ).toLowerCase();
const content = fs.readFileSync( inputPath, 'utf-8' );

console.log( '' );
console.log( '🎨 Vibrisse Core — Design Import' );
hr();
log( `Source : ${INPUT}` );
log( `Format : ${ext === '.json' ? 'W3C Design Tokens JSON' : ext === '.css' ? 'CSS Custom Properties' : 'Unknown'}` );
log( `Mode   : ${DRY_RUN ? 'Dry-run (no writes)' : 'Production'}` );
hr();

// Parsing
let rawTokens;
if ( ext === '.json' ) {
	rawTokens = parseW3CTokens( JSON.parse( content ) );
} else if ( ext === '.css' ) {
	rawTokens = parseCSSVars( content );
} else {
	error( `Unsupported format: ${ext}. Use .json (W3C Tokens) or .css (CSS vars). For PNG/PDF/URL, use the AI Skill design-import.` );
}

log( `Colors found : ${Object.keys( rawTokens.colors ).length}` );
log( `Fonts found  : ${Object.keys( rawTokens.fonts ).length}` );
console.log( '' );

// Mapping
const { slots, fontSlots, unmapped } = mapToSlots( rawTokens );

log( '📍 Mapping to vibrisse-core slots:' );
for ( const [ slot, value ] of Object.entries( slots ) ) {
	if ( value ) {
		ok( `${slot.padEnd( 10 )} → ${value}` );
	} else {
		warn( `${slot.padEnd( 10 )} → not detected (fill in manually)` );
	}
}
for ( const [ slot, value ] of Object.entries( fontSlots ) ) {
	if ( value ) {
		ok( `font-${slot.padEnd( 6 )} → ${value}` );
	} else {
		warn( `font-${slot.padEnd( 6 )} → not detected (fill in manually)` );
	}
}

if ( unmapped.colors.length > 0 ) {
	console.log( '' );
	warn( `${unmapped.colors.length} unmapped color(s) (add manually to CLIENT.md):` );
	unmapped.colors.forEach( t => log( `  • ${t.name}: ${t.value}` ) );
}

// Generation
console.log( '' );
hr();
const patch = generateThemeJsonPatch( slots, fontSlots );

if ( DRY_RUN ) {
	log( '📄 theme.json patch (dry-run):' );
	console.log( JSON.stringify( patch, null, 2 ) );
} else {
	const parentThemeJsonPath = path.resolve( ROOT, 'theme.json' );
	const outputPath          = parentThemeJsonPath;

	const existing = JSON.parse( fs.readFileSync( outputPath, 'utf-8' ) );

	// Deep merge: overwrite only palette and fontFamilies
	existing.settings = existing.settings || {};
	existing.settings.color = existing.settings.color || {};
	existing.settings.color.palette = patch.settings.color.palette;
	existing.settings.typography = existing.settings.typography || {};
	existing.settings.typography.fontFamilies = patch.settings.typography.fontFamilies;

	fs.writeFileSync( outputPath, JSON.stringify( existing, null, 4 ) );
	ok( `theme.json updated → ${outputPath}` );

	if ( fontSlots.sans || fontSlots.serif ) {
		warn( 'Remember to replace TODO Google Fonts URLs in theme.json with actual woff2 URLs.' );
	}
}

console.log( '' );
ok( 'Import complete. Next step: trigger the "theme-hydration" Skill in your IDE.' );
console.log( '' );
