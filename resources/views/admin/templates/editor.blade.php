<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Template Editor - Amantran CMS</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Rasa:ital,wght@0,300..700;1,300..700&family=Hind+Vadodara:wght@300;400;500;600;700&family=Farsan&family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">

    {{-- Lucide Icons CDN --}}
    <script src="https://unpkg.com/lucide@latest"></script>

    {{-- Vite assets --}}
    @php
        $pathPrefix = request()->getHost() === '127.0.0.1' || request()->getHost() === 'localhost' ? '' : '/public';
    @endphp
    <link rel="stylesheet" href="{{ $pathPrefix }}/css/app.css">
    <script src="{{ $pathPrefix }}/js/app.js" defer></script>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; overflow: hidden; }
        body { font-family: 'Inter', sans-serif; background: #FAF8F5; color: #1A1516; -webkit-font-smoothing: antialiased; }

        /* ===== SCROLLBARS ===== */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: rgba(184, 107, 119, 0.02); }
        ::-webkit-scrollbar-thumb { background: rgba(184, 107, 119, 0.2); border-radius: 10px; transition: background 0.2s; }
        ::-webkit-scrollbar-thumb:hover { background: #B86B77; }

        /* Range input accent */
        input[type=range] { accent-color: #FF3E5C; cursor: pointer; }

        /* ===== CANVAS AREA & GRID ===== */
        .canvas-viewport-bg {
            background-color: #F6F3EF;
            background-image:
                linear-gradient(to right, rgba(184, 107, 119, 0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(184, 107, 119, 0.03) 1px, transparent 1px);
            background-size: 20px 20px;
        }

        /* ===== HEADER TOOLBAR ===== */
        #editor-header {
            background: #0B0809; /* Deep wedding charcoal */
            border-bottom: 1px solid rgba(255, 202, 210, 0.07);
            height: 56px;
            position: relative;
            z-index: 50;
        }

        /* Modern Dark Pill Button & Zoom Groups */
        .header-btn-group, .header-zoom-group {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 202, 210, 0.08);
            border-radius: 12px;
            padding: 3px;
            gap: 2px;
        }
        .header-icon-btn, .header-zoom-group button {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            background: transparent;
            color: #9A8285;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .header-icon-btn:hover, .header-zoom-group button:hover {
            background: rgba(255, 62, 92, 0.12);
            color: #FF3E5C;
        }
        .header-icon-btn:disabled, .header-zoom-group button:disabled {
            opacity: 0.25;
            cursor: not-allowed;
            background: transparent;
            color: #9A8285 !important;
        }
        .header-zoom-group span {
            font-size: 11px;
            font-weight: 700;
            color: #C8B8BB;
            padding: 0 8px;
            min-width: 42px;
            text-align: center;
            user-select: none;
        }

        /* Autosave badge */
        #autosave-badge {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 5px 10px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            transition: all 0.2s ease;
        }
        .autosave-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #22C55E;
            box-shadow: 0 0 6px #22C55E;
            animation: pulse-green 2s infinite;
        }
        @keyframes pulse-green {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.8); }
        }

        /* ===== LEFT PANEL TAB RAIL ===== */
        .left-icon-col {
            width: 64px;
            background: #0B0809; /* Unified Dark theme */
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 16px 0;
            gap: 6px;
            flex-shrink: 0;
            user-select: none;
            border-right: 1px solid rgba(255, 202, 210, 0.05);
        }
        .tab-icon-btn {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            padding: 12px 0;
            border: none;
            background: none;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }
        .tab-icon-btn .tab-icon-circle {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #7E6B6E;
            transition: all 0.2s ease;
            background: rgba(255, 255, 255, 0.01);
        }
        .tab-icon-btn .tab-label {
            font-size: 9px;
            font-weight: 600;
            color: #7E6B6E;
            letter-spacing: 0.04em;
            transition: color 0.2s ease;
        }
        .tab-icon-btn:hover .tab-icon-circle {
            background: rgba(255, 62, 92, 0.1);
            color: #FF3E5C;
        }
        .tab-icon-btn:hover .tab-label {
            color: #FF3E5C;
        }
        .tab-icon-btn.active-tab .tab-icon-circle {
            background: linear-gradient(135deg, #FF3E5C, #B86B77);
            color: #fff;
            box-shadow: 0 4px 12px rgba(255, 62, 92, 0.35);
        }
        .tab-icon-btn.active-tab .tab-label {
            color: #FF3E5C;
            font-weight: 700;
        }
        .tab-icon-btn.active-tab::after {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 28px;
            background: #FF3E5C;
            border-radius: 0 4px 4px 0;
            box-shadow: 0 0 8px #FF3E5C;
        }

        /* ===== SIDEBAR PANELS (LEFT & RIGHT) ===== */
        .left-content-panel, .right-panel {
            background: #FFFDFB; /* Warm elegant linen white */
            color: #1A1516;
        }

        .section-label {
            font-size: 10px;
            font-weight: 800;
            color: #7E6B6E;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            display: block;
            margin-top: 14px;
            margin-bottom: 8px;
        }

        /* Custom typography controls */
        .add-custom-text-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            width: 100%;
            padding: 10px;
            background: rgba(255, 62, 92, 0.04);
            border: 1.5px dashed rgba(255, 62, 92, 0.35);
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
            color: #FF3E5C;
            cursor: pointer;
            letter-spacing: 0.05em;
            transition: all 0.2s ease;
        }
        .add-custom-text-btn:hover {
            background: rgba(255, 62, 92, 0.08);
            border-color: #FF3E5C;
            transform: translateY(-0.5px);
        }
        .add-to-card-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            width: 100%;
            padding: 11px;
            background: linear-gradient(135deg, #FF3E5C, #B86B77);
            border: none;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 700;
            color: #fff;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(255, 62, 92, 0.2);
        }
        .add-to-card-btn:hover {
            background: linear-gradient(135deg, #E62E47, #A35B67);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(255, 62, 92, 0.3);
        }

        /* Preset Standard and Premium Cards */
        .preset-card, .premium-preset-card {
            display: block;
            width: 100%;
            text-align: left;
            padding: 12px 14px;
            background: #FFFDFD;
            border: 1px solid rgba(184, 107, 119, 0.15);
            border-radius: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-bottom: 8px;
            box-shadow: 0 2px 4px rgba(184, 107, 119, 0.02);
        }
        .preset-card:hover, .premium-preset-card:hover {
            background: rgba(255, 240, 242, 0.25);
            border-color: #D4A0A7;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(184, 107, 119, 0.05);
        }
        .preset-label {
            font-size: 10px;
            color: #9A8285;
            margin-top: 4px;
            display: block;
            font-weight: 500;
        }

        /* Input elements inputs, textareas & select */
        .prop-input, .prop-select, .custom-text-area {
            background: #FFFDFD;
            border: 1px solid rgba(184, 107, 119, 0.2);
            border-radius: 12px;
            padding: 8px 12px;
            font-size: 12px;
            color: #1A1516;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s ease;
            box-shadow: inset 0 1px 2px rgba(184, 107, 119, 0.02);
            width: 100%;
        }
        .prop-input:focus, .prop-select:focus, .custom-text-area:focus {
            outline: none;
            border-color: #B86B77;
            box-shadow: 0 0 0 3px rgba(184, 107, 119, 0.12);
            background: #fff;
        }

        /* Settings Card Boxes */
        .prop-card {
            background: #FFFDFD;
            border: 1px solid rgba(184, 107, 119, 0.12);
            border-radius: 14px;
            padding: 14px;
            box-shadow: 0 2px 8px rgba(184, 107, 119, 0.01);
        }
        .prop-card-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
        }
        .prop-card-row:not(:last-child) {
            border-bottom: 1px solid rgba(184, 107, 119, 0.06);
        }
        .prop-label {
            font-size: 9px;
            font-weight: 700;
            color: #9A8285;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        .prop-value {
            font-size: 12px;
            font-weight: 600;
            color: #1A1516;
        }
        .rpanel-section-title {
            font-size: 9px;
            font-weight: 800;
            color: #9A8285;
            text-transform: uppercase;
            letter-spacing: 0.12em;
        }
        .rpanel-section-h {
            font-size: 15px;
            font-weight: 800;
            color: #1A1516;
            margin-top: 4px;
            font-family: 'Playfair Display', serif;
        }

        /* Toggle Switches */
        .toggle-switch {
            position: relative; width: 36px; height: 20px; flex-shrink: 0;
        }
        .toggle-switch input { display: none; }
        .toggle-track {
            display: block; width: 36px; height: 20px;
            border-radius: 10px; background: rgba(184, 107, 119, 0.2);
            cursor: pointer; transition: background 0.2s; position: relative;
        }
        .toggle-track::after {
            content: ''; position: absolute; top: 2px; left: 2px;
            width: 16px; height: 16px; border-radius: 50%; background: #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.15); transition: transform 0.2s;
        }
        .toggle-switch input:checked + .toggle-track { background: #FF3E5C; }
        .toggle-switch input:checked + .toggle-track::after { transform: translateX(16px); }

        /* Alignment and depth controls */
        .align-btn, .layer-btn {
            flex: 1; padding: 8px; border: none;
            background: #FFFDFD; color: #7E6B6E;
            cursor: pointer; transition: all 0.2s ease;
            display: flex; align-items: center; justify-content: center;
        }
        .align-btn + .align-btn { border-left: 1px solid rgba(184, 107, 119, 0.15); }
        .align-btn:hover, .layer-btn:hover { background: rgba(255, 62, 92, 0.05); color: #FF3E5C; }
        .align-btn.active { background: rgba(255, 62, 92, 0.08); color: #FF3E5C; }

        /* Page list item tabs */
        .pages-list-wrap { display: flex; flex-direction: column; gap: 6px; }
        .page-list-item {
            display: flex; align-items: center; justify-content: space-between;
            padding: 8px 12px; border-radius: 12px; cursor: pointer;
            transition: all 0.2s ease; border: 1px solid transparent;
        }
        
        /* Guide instruction list */
        .instruction-item {
            padding: 10px 12px; display: flex; gap: 10px; align-items: flex-start;
            background: rgba(184, 107, 119, 0.03); border: 1px solid rgba(184, 107, 119, 0.08);
            border-radius: 12px; margin-bottom: 8px;
        }
        .instruction-icon {
            width: 22px; height: 22px; border-radius: 50%;
            background: rgba(255, 62, 92, 0.08); color: #FF3E5C;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; font-size: 10px;
        }
        .instruction-text { font-size: 11px; color: #7E6B6E; line-height: 1.5; }
        .instruction-text strong { color: #1A1516; }
        .kbd {
            display: inline-block; padding: 1px 4px; background: #FFFDFD;
            border: 1px solid rgba(184, 107, 119, 0.2); border-radius: 4px;
            font-size: 9px; font-family: monospace; color: #1A1516; margin: 1px;
        }

        /* ===== CANVAS CONTAINER & WORKSPACE ===== */
        #canvas-card {
            background-color: #fff;
            border: 2px solid #D4AF37; /* Royal Wedding Gold */
            box-shadow: 0 25px 65px -15px rgba(74, 46, 53, 0.25);
            transition: box-shadow 0.3s;
        }
        #canvas-card:hover {
            box-shadow: 0 35px 85px -15px rgba(74, 46, 53, 0.35);
        }

        /* SELECTION OVERLAY & RESIZE HANDLES */
        .selection-border {
            position: absolute; inset: 0;
            border: 1.5px solid #D4AF37; /* Gold accent */
            pointer-events: none; z-index: 9999; box-sizing: border-box;
            box-shadow: 0 0 8px rgba(212, 175, 55, 0.2);
        }
        .resize-handle {
            position: absolute; width: 10px; height: 10px;
            background: #fff; border: 2px solid #D4AF37; border-radius: 50%;
            z-index: 10001; box-sizing: border-box; pointer-events: auto;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
            transition: all 0.15s ease;
        }
        .resize-handle:hover {
            transform: scale(1.25);
            background: #D4AF37;
        }
        .resize-handle.rotate-handle {
            background: #C9943B; border-color: #C9943B; cursor: grab;
        }

        /* Element focus outlines */
        .canvas-element:hover { outline: 1px dashed rgba(212, 175, 55, 0.5) !important; }
        .canvas-element-selected { outline: 1.5px solid #D4AF37 !important; }

        /* ===== STORYBOARD NAVIGATOR ===== */
        #storyboard-bar {
            height: 150px; display: flex; flex-direction: column; justify-content: center;
            padding: 10px 20px; flex-shrink: 0; user-select: none;
            background: #FFFDFB; border-top: 1px solid rgba(184, 107, 119, 0.1);
        }

        /* ===== TOAST NOTIFICATIONS ===== */
        #editor-toast {
            position: fixed; bottom: 20px; right: 20px; z-index: 99999;
            display: flex; flex-direction: column; gap: 8px; pointer-events: none;
        }
        .editor-toast-item {
            padding: 10px 16px; border-radius: 12px; font-size: 12px; font-weight: 700;
            animation: slideUp 0.2s ease; opacity: 1; transition: opacity 0.3s;
            pointer-events: auto; display: flex; align-items: center; gap: 8px;
            max-width: 320px; box-shadow: 0 6px 20px rgba(74, 46, 53, 0.12);
        }
        .toast-success { background: #f0fdf4; border: 1.5px solid #86efac; color: #166534; }
        .toast-error   { background: #fef2f2; border: 1.5px solid #fca5a5; color: #991b1b; }
        .toast-warning { background: #fffbeb; border: 1.5px solid #fcd34d; color: #92400e; }
        .toast-info    { background: #eff6ff; border: 1.5px solid #93c5fd; color: #1e40af; }
        @keyframes slideUp { from { transform: translateY(16px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

        /* Translation process bar */
        #translate-toast {
            position: fixed; top: 72px; left: 50%; transform: translateX(-50%);
            z-index: 99998; display: none; background: #FFFDFB;
            border: 1.5px solid rgba(255, 62, 92, 0.25); border-radius: 12px;
            padding: 10px 18px; color: #FF3E5C; font-size: 12px; font-weight: 700;
            align-items: center; gap: 8px; box-shadow: 0 8px 24px rgba(255,62,92,0.12);
            animation: bounce 1.2s infinite alternate;
        }
        @keyframes bounce { from { transform: translateX(-50%) translateY(0); } to { transform: translateX(-50%) translateY(-4px); } }

        /* ===== PREMIUM EDITOR SHELL OVERRIDES ===== */
        :root {
            --editor-primary: #FF4D6D;
            --editor-primary-dark: #E73758;
            --editor-ink: #181114;
            --editor-muted: #75666B;
            --editor-line: #ECE3E5;
            --editor-soft: #F7F7F8;
            --editor-card: #FFFFFF;
            --editor-gold: #D6A642;
        }

        body {
            background: #F4F5F7;
        }

        #editor-header {
            height: 68px;
            padding: 0 18px;
            background: rgba(12, 10, 11, 0.96) !important;
            backdrop-filter: blur(18px);
            border-bottom: 1px solid rgba(255,255,255,0.08) !important;
            box-shadow: 0 12px 34px rgba(10, 8, 9, 0.18);
        }

        .editor-header-left,
        .editor-header-center,
        .editor-header-right {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }
        .editor-header-left { flex: 1 1 0; }
        .editor-header-center { flex: 0 0 auto; justify-content: center; }
        .editor-header-right { flex: 1 1 0; justify-content: flex-end; }

        .editor-top-btn,
        .editor-select-shell,
        .header-btn-group,
        .header-zoom-group {
            min-height: 42px;
            border-radius: 14px;
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.065);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.06), 0 8px 22px rgba(0,0,0,0.16);
            transition: all 0.2s ease;
        }

        .editor-top-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 42px;
            padding: 0 14px;
            border: none;
            color: #F8F2F3;
            font-size: 12px;
            font-weight: 800;
            white-space: nowrap;
            cursor: pointer;
        }
        .editor-top-btn:hover,
        .editor-select-shell:hover,
        .header-btn-group:hover,
        .header-zoom-group:hover {
            transform: translateY(-1px);
            border-color: rgba(255,77,109,0.32);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.08), 0 12px 28px rgba(0,0,0,0.2);
        }
        .editor-top-btn.primary {
            background: linear-gradient(135deg, var(--editor-primary), #FF2F60);
            color: #fff;
            box-shadow: 0 10px 26px rgba(255,77,109,0.34);
        }
        .editor-top-btn.gold {
            background: linear-gradient(135deg, #FFD978, #F5B841);
            color: #1B1214;
            box-shadow: 0 10px 24px rgba(245,184,65,0.28);
        }
        .editor-top-btn.publish {
            background: #FFFFFF;
            color: #181114;
        }
        .premium-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            height: 22px;
            padding: 0 9px;
            border-radius: 999px;
            background: linear-gradient(135deg, #FFE7A8, #D6A642);
            color: #21160A;
            font-size: 9px;
            font-weight: 900;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            box-shadow: 0 8px 18px rgba(214,166,66,0.25);
        }

        .editor-main-shell {
            background: #F3F4F6;
        }
        .left-shell {
            width: 320px !important;
            background: #FFF;
            border-right: 1px solid var(--editor-line) !important;
            box-shadow: 12px 0 30px rgba(20,16,18,0.05);
        }
        .left-content-panel {
            padding: 18px 16px 92px !important;
            background: linear-gradient(180deg, #FFFFFF 0%, #FBFBFC 100%) !important;
        }
        .panel-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
        }
        .panel-heading h4 {
            font-size: 12px !important;
            color: #1F171A !important;
            letter-spacing: 0.1em !important;
        }
        .panel-heading p {
            font-size: 11px !important;
            color: #7A6A70 !important;
        }
        .editor-search {
            position: relative;
        }
        .editor-search i,
        .editor-search svg {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #9A8B90;
            width: 15px;
            height: 15px;
            pointer-events: none;
            z-index: 2;
        }
        .editor-search input {
            width: 100%;
            height: 42px;
            padding: 0 12px 0 36px;
            border-radius: 14px;
            border: 1px solid var(--editor-line);
            background: #F7F7F8;
            color: #181114;
            font-size: 12px;
            font-weight: 600;
            outline: none;
            transition: all 0.2s ease;
        }
        .editor-search input:focus {
            background: #fff;
            border-color: rgba(255,77,109,0.55);
            box-shadow: 0 0 0 4px rgba(255,77,109,0.1);
        }

        .section-label {
            margin-top: 18px;
            margin-bottom: 10px;
            color: #6E6166;
            letter-spacing: 0.12em;
        }
        .preset-card,
        .premium-preset-card {
            position: relative;
            display: block;
            min-height: 76px;
            padding: 14px 14px 14px 58px !important;
            border-radius: 16px !important;
            background: #fff !important;
            border: 1px solid var(--editor-line) !important;
            box-shadow: 0 8px 22px rgba(25,18,21,0.045) !important;
        }
        .preset-card::before,
        .premium-preset-card::before {
            content: '';
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 34px;
            height: 34px;
            border-radius: 12px;
            background: linear-gradient(135deg, rgba(255,77,109,0.13), rgba(214,166,66,0.18));
            border: 1px solid rgba(255,77,109,0.14);
        }
        .premium-preset-card::after {
            content: '';
            position: absolute;
            left: 25px;
            top: 50%;
            width: 12px;
            height: 12px;
            transform: translateY(-50%);
            border-radius: 4px;
            background: var(--editor-primary);
            box-shadow: 0 0 0 4px rgba(255,77,109,0.12);
        }
        .preset-card:hover,
        .premium-preset-card:hover {
            transform: translateY(-2px) scale(1.01);
            border-color: rgba(255,77,109,0.48) !important;
            box-shadow: 0 14px 30px rgba(25,18,21,0.09) !important;
        }

        #canvas-area {
            background: #EEF0F3 !important;
            min-width: 0;
        }
        .canvas-viewport-bg {
            background-color: #F1F2F4;
            background-image:
                radial-gradient(circle at center, rgba(255,255,255,0.7) 0%, rgba(241,242,244,0.58) 44%, rgba(225,228,233,0.9) 100%),
                linear-gradient(to right, rgba(32,36,44,0.045) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(32,36,44,0.045) 1px, transparent 1px);
            background-size: auto, 24px 24px, 24px 24px;
        }
        #canvas-viewport {
            scrollbar-width: none;
        }
        #canvas-viewport::-webkit-scrollbar {
            display: none;
        }
        #canvas-wrapper {
            min-height: 100% !important;
            padding: 104px clamp(28px, 5vw, 84px) 58px !important;
            justify-content: flex-start !important;
        }
        #canvas-card {
            border: 1px solid rgba(24,17,20,0.12) !important;
            border-radius: 14px;
            box-shadow:
                0 28px 80px rgba(24,17,20,0.18),
                0 2px 0 rgba(255,255,255,0.8) inset !important;
        }
        #canvas-card:hover {
            box-shadow:
                0 34px 90px rgba(24,17,20,0.22),
                0 2px 0 rgba(255,255,255,0.8) inset !important;
        }
        #page-toolbar {
            top: -72px !important;
            height: 48px !important;
            width: clamp(420px, 45vw, 560px) !important;
            min-width: 0 !important;
            max-width: calc(100vw - 720px) !important;
        }
        #page-toolbar > div {
            height: 48px !important;
            border-radius: 18px !important;
            background: rgba(22,18,20,0.78) !important;
            backdrop-filter: blur(18px) saturate(150%);
            border: 1px solid rgba(255,255,255,0.16) !important;
            box-shadow: 0 18px 44px rgba(20,16,18,0.28), inset 0 1px 0 rgba(255,255,255,0.12) !important;
            padding: 0 10px !important;
            gap: 10px !important;
        }
        #page-toolbar button {
            min-width: 34px;
            height: 34px;
            border-radius: 11px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            white-space: nowrap;
        }
        #page-toolbar button:hover {
            transform: translateY(-1px);
        }
        #page-counter {
            display: inline-flex;
            align-items: center;
            height: 28px;
            padding: 0 10px;
            border-radius: 999px;
            background: rgba(255,255,255,0.1);
            color: #FFE2A2 !important;
            flex: 0 0 auto;
            white-space: nowrap;
        }
        #page-toolbar .page-toolbar-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 4px;
            min-width: 0;
            flex: 1;
        }
        #page-toolbar .page-action-text {
            display: inline;
        }

        #storyboard-bar {
            height: 140px !important;
            padding: 14px 20px 12px !important;
            background: rgba(255,255,255,0.86) !important;
            backdrop-filter: blur(16px);
            border-top: 1px solid rgba(24,17,20,0.08) !important;
            box-shadow: 0 -18px 40px rgba(24,17,20,0.06);
            overflow: hidden !important;
        }
        #storyboard {
            gap: 14px !important;
            padding: 2px 4px 0 !important;
            overflow: hidden !important;
            flex-wrap: nowrap;
            max-width: 100%;
            height: 92px;
        }
        #storyboard::-webkit-scrollbar,
        #storyboard-bar::-webkit-scrollbar {
            display: none;
        }
        .storyboard-card,
        .storyboard-add-card {
            width: 58px;
            height: 92px;
            flex: 0 0 auto;
            border-radius: 12px;
            position: relative;
            overflow: hidden;
            background: #F7F7F8;
            border: 1px solid #E8E1E3;
            box-shadow: 0 10px 24px rgba(24,17,20,0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
            scroll-snap-align: start;
        }
        .storyboard-card:hover,
        .storyboard-add-card:hover {
            transform: translateY(-3px);
            border-color: rgba(255,77,109,0.45);
            box-shadow: 0 16px 30px rgba(24,17,20,0.13);
        }
        .storyboard-card.is-active {
            border-color: #FF4D6D;
            box-shadow: 0 0 0 2px #FF4D6D, 0 18px 34px rgba(255,77,109,0.18);
        }
        .storyboard-action-stack,
        .storyboard-drag-handle {
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        .storyboard-card:hover .storyboard-action-stack,
        .storyboard-card:hover .storyboard-drag-handle {
            opacity: 1;
        }
        .storyboard-add-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border-style: dashed;
            color: #8B7B80;
            cursor: pointer;
        }
        .sticker-card {
            width: 100%;
            aspect-ratio: 1;
            padding: 12px;
            border-radius: 16px;
            border: 1px solid #ECE3E5;
            background: #fff;
            box-shadow: 0 8px 22px rgba(25,18,21,0.045);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .sticker-card:hover {
            transform: translateY(-2px);
            border-color: rgba(255,77,109,0.5);
            box-shadow: 0 14px 30px rgba(25,18,21,0.09);
        }
        .sticker-card img {
            width: 74px;
            height: 74px;
            object-fit: contain;
            transition: transform 0.2s ease;
        }
        .sticker-card:hover img {
            transform: scale(1.08);
        }

        .right-panel {
            width: 288px !important;
            background: #F8F8FA !important;
            border-left: 1px solid var(--editor-line) !important;
            box-shadow: -12px 0 30px rgba(20,16,18,0.045);
        }
        .right-panel > div {
            padding: 20px 16px 92px !important;
        }
        .prop-card,
        .instruction-item {
            background: #fff !important;
            border-color: var(--editor-line) !important;
            border-radius: 16px !important;
            box-shadow: 0 10px 28px rgba(25,18,21,0.045) !important;
        }
        .rpanel-section-h {
            font-family: 'Inter', sans-serif;
            font-size: 16px;
            letter-spacing: 0;
        }

        @media (max-width: 1280px) {
            .left-shell { width: 292px !important; }
            .right-panel { width: 260px !important; }
            .editor-top-btn { padding: 0 11px; }
            #page-toolbar {
                width: 440px !important;
                max-width: calc(100vw - 620px) !important;
            }
            #page-toolbar .page-action-text {
                display: none;
            }
        }

        @media (max-width: 1024px) {
            #editor-header {
                height: auto;
                min-height: 68px;
                flex-wrap: wrap;
                gap: 10px;
                padding: 10px 12px;
            }
            .editor-header-left,
            .editor-header-right {
                flex: 1 1 360px;
            }
            .editor-header-center {
                order: 3;
                flex: 1 1 100%;
            }
            .left-shell { width: 274px !important; }
            .right-panel { width: 250px !important; }
            #canvas-wrapper { padding-left: 24px !important; padding-right: 24px !important; }
            #page-toolbar {
                width: min(440px, calc(100vw - 340px)) !important;
                max-width: none !important;
            }
        }

        @media (max-width: 860px) {
            .right-panel { display: none; }
            .left-shell { width: 300px !important; }
            #storyboard-bar { height: 128px !important; }
        }
    </style>
</head>
<body class="flex flex-col h-screen overflow-hidden bg-[#FAF8F5]">


{{-- Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬ HEADER TOOLBAR Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬ --}}
<div id="editor-header" class="text-white flex items-center justify-between shrink-0">
    {{-- Left: Back + Template name --}}
    <div class="editor-header-left">
        <button onclick="goBack()" title="Back to Templates" class="editor-top-btn" style="width:42px;padding:0;flex-shrink:0;">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
        </button>
        <div style="min-width:0;">
            <div style="display:flex;align-items:center;gap:6px;">
                <span style="width:6px;height:6px;border-radius:50%;background:#FF3E5C;flex-shrink:0;"></span>
                <h3 id="editor-tpl-name" style="font-size:12px;font-weight:800;color:#FFF4E6;text-transform:uppercase;letter-spacing:0.06em;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"></h3>
                <span id="editor-tpl-premium-badge" class="premium-badge" style="display: none;"><i data-lucide="crown" style="width:11px;height:11px;"></i> Premium</span>
            </div>
            <p id="editor-tpl-slug" style="font-size:9px;color:#9E878A;font-family:monospace;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"></p>
        </div>
    </div>

    {{-- Center: Undo/Redo + Zoom --}}
    <div class="editor-header-center">
        <div class="header-btn-group">
            <button onclick="editorUndo()" id="undo-btn" disabled class="header-icon-btn" title="Undo (Ctrl+Z)">
                <i data-lucide="undo-2" class="w-4 h-4"></i>
            </button>
            <button onclick="editorRedo()" id="redo-btn" disabled class="header-icon-btn" title="Redo (Ctrl+Y)">
                <i data-lucide="redo-2" class="w-4 h-4"></i>
            </button>
        </div>

        <div class="header-zoom-group">
            <button onclick="setZoom(editorState.zoom - 5)" title="Zoom Out">
                <i data-lucide="zoom-out" class="w-3.5 h-3.5"></i>
            </button>
            <span id="zoom-display" style="width:38px;text-align:center;user-select:none;color:#C8B8BB;">40%</span>
            <button onclick="setZoom(editorState.zoom + 5)" title="Zoom In">
                <i data-lucide="zoom-in" class="w-3.5 h-3.5"></i>
            </button>
        </div>
    </div>

    {{-- Right: Autosave + Language + Preview + Save --}}
    <div class="editor-header-right">
        <span id="autosave-badge" class="autosave-badge">
            <span class="autosave-dot"></span>
            <span class="hidden lg:inline">Auto-Save Active</span>
        </span>

        <div class="editor-select-shell" style="display:flex;align-items:center;gap:6px;padding:0 12px;">
            <i data-lucide="languages" style="width:14px;height:14px;color:#9A8285;flex-shrink:0;"></i>
            <select id="lang-select" onchange="handleLanguageChange(this.value)" style="background:transparent;font-size:11px;font-weight:700;color:#C8B8BB;border:none;outline:none;cursor:pointer;">
                <option value="English" style="background:#1C1416;">English</option>
            </select>
        </div>

        <button onclick="openPreview()" title="Live Preview" class="editor-top-btn primary">
            <i data-lucide="eye" class="w-4 h-4"></i>
            <span class="hidden md:inline">Live Preview</span>
        </button>

        <button onclick="manualSave()" id="save-btn" title="Save Draft" class="editor-top-btn gold">
            <i data-lucide="save" class="w-4 h-4"></i>
            <span class="hidden md:inline">Save Draft</span>
        </button>
        <button type="button" title="Publish action is not configured in this editor yet" class="editor-top-btn publish" disabled style="opacity:0.72;cursor:not-allowed;">
            <i data-lucide="send" class="w-4 h-4"></i>
            <span class="hidden xl:inline">Publish</span>
        </button>
    </div>
</div>

{{-- Auto-translate notification --}}
<div id="translate-toast" class="flex">
    <i data-lucide="languages" class="w-4 h-4 text-[#FFF4E6] mr-2"></i>
    <span>✨ Translating card elements to <span id="translate-lang" class="text-[#FFF4E6]">English</span>...</span>
</div>

{{-- Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬ MAIN 3-PANEL LAYOUT Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬ --}}
<div class="editor-main-shell flex-1 flex overflow-hidden" style="position: relative; z-index: 10;">

    {{-- Ã¢â€¢ÂÃ¢â€¢Â LEFT PANEL Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â --}}
    <div class="left-shell" style="border-right:1px solid #E5E7EB;display:flex;flex-shrink:0;z-index:10;">
        {{-- Icon tabs column --}}
        <div class="left-icon-col" style="width:64px;display:flex;flex-direction:column;align-items:center;padding:12px 0;gap:2px;flex-shrink:0;user-select:none;">
            <button onclick="setLeftTab('text')" id="tab-text" class="tab-icon-btn active-tab" title="Text">
                <div class="tab-icon-circle"><i data-lucide="type" style="width:18px;height:18px;"></i></div>
                <span class="tab-label">Text</span>
            </button>
            <button onclick="setLeftTab('stickers')" id="tab-stickers" class="tab-icon-btn" title="Stickers">
                <div class="tab-icon-circle"><i data-lucide="sticker" style="width:18px;height:18px;"></i></div>
                <span class="tab-label">Stickers</span>
            </button>
            <button onclick="setLeftTab('photos')" id="tab-photos" class="tab-icon-btn" title="Uploads">
                <div class="tab-icon-circle"><i data-lucide="image" style="width:18px;height:18px;"></i></div>
                <span class="tab-label">Uploads</span>
            </button>
            <button onclick="setLeftTab('pages')" id="tab-pages" class="tab-icon-btn" title="Pages">
                <div class="tab-icon-circle"><i data-lucide="layers" style="width:18px;height:18px;"></i></div>
                <span class="tab-label">Pages</span>
            </button>
            <button onclick="setLeftTab('info')" id="tab-info" class="tab-icon-btn" title="Details">
                <div class="tab-icon-circle"><i data-lucide="info" style="width:18px;height:18px;"></i></div>
                <span class="tab-label">Details</span>
            </button>
        </div>

        {{-- Panel content --}}
        <div class="left-content-panel" style="flex:1;overflow-y:auto;display:flex;flex-direction:column;gap:14px;color:#1A1516;">

            {{-- Text Tab --}}
            <div id="panel-text" style="display:flex;flex-direction:column;gap:14px;">
                <div class="panel-heading">
                    <div>
                    <h4 style="color:#1A1516;font-size:10px;font-weight:800;letter-spacing:0.12em;text-transform:uppercase;">Text Typographies</h4>
                    <p style="color:#6B7280;font-size:10px;margin-top:4px;line-height:1.5;">Insert custom text nodes into the current page.</p>
                    </div>
                </div>
                <div class="editor-search">
                    <i data-lucide="search" style="width:14px;height:14px;"></i>
                    <input id="left-search-input" type="search" placeholder="Search text styles">
                </div>

                <div id="translate-progress" style="padding:10px 12px;background:rgba(247,197,102,0.1);border:1px solid rgba(247,197,102,0.35);color:#C9943B;font-size:10px;font-weight:700;border-radius:10px;display:none;align-items:center;justify-content:center;gap:6px;">
                    <i data-lucide="languages" style="width:12px;height:12px;"></i>
                    <span>✨ Auto-translating text...</span>
                </div>

                {{-- Custom text box --}}
                <div style="display:flex;flex-direction:column;gap:10px;">
                    <button onclick="addDefaultCustomTextBox()" class="add-custom-text-btn" style="width:100%; margin:0;">
                        <i data-lucide="plus" style="width:13px;height:13px;"></i> ADD CUSTOM TEXT BOX
                    </button>
                    <textarea id="custom-text-input" class="custom-text-area" rows="3" placeholder="Type your own custom wedding invitation text here..." style="width:100%; margin:0;"></textarea>
                    <button onclick="addCustomText()" class="add-to-card-btn" style="width:100%; margin:0;">
                        <i data-lucide="plus" style="width:13px;height:13px;"></i> Add to Card
                    </button>
                </div>

                {{-- Preset styles --}}
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <span class="section-label">Standard Styles</span>
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        <button onclick="addPresetText('heading')" class="preset-card">
                            <span style="display:block;font-weight:800;font-size:16px;color:#C9943B;line-height:1;letter-spacing:0.03em;">MANGAL PARINAY</span>
                            <span class="preset-label">Luxury Heading (Kap011 style)</span>
                        </button>
                        <button onclick="addPresetText('subheading')" class="preset-card">
                            <span style="display:block;font-weight:700;font-size:13px;color:#FF3E5C;letter-spacing:0.08em;line-height:1;">Save The Date</span>
                            <span class="preset-label">Subheading (Hind Vadodara style)</span>
                        </button>
                        <button onclick="addPresetText('body')" class="preset-card">
                            <span style="display:block;font-size:11px;color:#6B7280;line-height:1.5;">We cordially invite you to celebrate the wedding ceremony...</span>
                            <span class="preset-label">Body Invitation details (Rasa style)</span>
                        </button>
                    </div>
                </div>

                {{-- Premium presets --}}
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <span class="section-label">👑 Premium Wedding Card Presets</span>
                    <div id="premium-presets" style="display:flex;flex-direction:column;gap:6px;"></div>
                </div>

            </div>

            {{-- Stickers Tab --}}
            <div id="panel-stickers" style="display:none;flex-direction:column;gap:14px;">
                <div>
                    <h4 style="color:#1A1516;font-size:10px;font-weight:800;letter-spacing:0.12em;text-transform:uppercase;">Wedding Stickers</h4>
                    <p style="color:#6B7280;font-size:10px;margin-top:4px;line-height:1.5;">Add stamps, decals, frames, and vectors.</p>
                </div>
                <div id="stickers-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:10px;padding-top:4px;"></div>

            </div>

            {{-- Photos Tab --}}
            <div id="panel-photos" style="display:none;flex-direction:column;gap:14px;">
                <div>
                    <h4 style="color:#1A1516;font-size:10px;font-weight:800;letter-spacing:0.12em;text-transform:uppercase;">Custom Decals</h4>
                    <p style="color:#6B7280;font-size:10px;margin-top:4px;line-height:1.5;">Upload transparent PNG ornaments, banners, or family pictures.</p>
                </div>
                <label style="width:100%;border:2px dashed rgba(255,62,92,0.25);border-radius:14px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;padding:24px 12px;cursor:pointer;transition:all 0.2s;background:rgba(255,62,92,0.04);" onmouseover="this.style.borderColor='rgba(255,62,92,0.5)';this.style.background='rgba(255,62,92,0.08)'" onmouseout="this.style.borderColor='rgba(255,62,92,0.25)';this.style.background='rgba(255,62,92,0.04)'">
                    <i data-lucide="upload" style="width:22px;height:22px;color:#FF3E5C;"></i>
                    <span id="photo-upload-label" style="font-size:11px;font-weight:700;color:#9A8285;">Upload Image Ornament</span>
                    <input type="file" accept="image/*" onchange="handlePhotoUpload(event)" style="display:none;">
                </label>
                <div id="uploaded-photos" style="display:none;grid-template-columns:1fr 1fr;gap:8px;"></div>

            </div>

            {{-- Pages Tab --}}
            <div id="panel-pages" style="display:none;flex-direction:column;gap:14px;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <h4 style="color:#1A1516;font-size:10px;font-weight:800;letter-spacing:0.12em;text-transform:uppercase;">Page List</h4>
                    <button onclick="addPage()" style="display:flex;align-items:center;gap:4px;font-size:10px;font-weight:700;color:#FF3E5C;background:none;border:none;cursor:pointer;" onmouseover="this.style.opacity='0.7'" onmouseout="this.style.opacity='1'">
                        <i data-lucide="plus-circle" style="width:12px;height:12px;"></i> Add Page
                    </button>
                </div>
                <div id="pages-list" style="display:flex;flex-direction:column;gap:6px;"></div>

                {{-- Current page background --}}
                <div style="padding-top:14px;border-top:1px solid #E5E7EB;display:flex;flex-direction:column;gap:10px;">
                    <span class="section-label">Page Background</span>
                    <div id="bg-preview-container" class="hidden" style="position:relative;border-radius:12px;overflow:hidden;border:1px solid rgba(255,62,92,0.2);aspect-ratio:9/16;max-height:180px;background:#FAF8F5;">
                        <img id="bg-preview-img" src="" alt="Background" style="width:100%;height:100%;object-fit:cover;">
                        <button onclick="clearPageBackground()" style="position:absolute;inset:0;background:rgba(220,38,38,0.4);color:#fff;font-size:10px;font-weight:700;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0'">Clear Background</button>
                    </div>
                    <div id="bg-placeholder" style="height:60px;border-radius:12px;border:2px dashed #E5E7EB;display:flex;align-items:center;justify-content:center;font-size:11px;color:#6B7280;font-weight:600;">
                        No Page Background
                    </div>
                    <label style="display:flex;align-items:center;justify-content:center;gap:6px;padding:10px;border:1px solid #E5E7EB;border-radius:10px;cursor:pointer;font-size:11px;font-weight:700;color:#FF3E5C;transition:all 0.2s;background:#F9FAFB;" onmouseover="this.style.borderColor='#FF3E5C';this.style.background='#FFF1F2'" onmouseout="this.style.borderColor='#E5E7EB';this.style.background='#F9FAFB'">
                        <i data-lucide="upload" style="width:13px;height:13px;"></i>
                        <span>Replace BG Image</span>
                        <input type="file" accept="image/*" onchange="handleBgUpload(event)" style="display:none;">
                    </label>
                </div>

            </div>

            {{-- Info Tab --}}
            <div id="panel-info" style="display:none;flex-direction:column;gap:14px;">
                <div>
                    <h4 style="color:#1A1516;font-size:10px;font-weight:800;letter-spacing:0.12em;text-transform:uppercase;">Template Information</h4>
                </div>
                <div class="prop-card" style="display:flex;flex-direction:column;gap:12px;">
                    <div class="prop-card-row">
                        <span class="prop-label">Invitation Name</span>
                        <span id="info-name" class="prop-value">-</span>
                    </div>
                    <div class="prop-card-row">
                        <span class="prop-label">Identifier Slug</span>
                        <span id="info-slug" class="prop-value" style="font-family:monospace; font-size:11px;">-</span>
                    </div>
                    <div class="prop-card-row">
                        <span class="prop-label">Assigned Fonts</span>
                        <span id="info-fonts" class="prop-value">-</span>
                    </div>
                    <div class="prop-card-row">
                        <span class="prop-label">Assigned Languages</span>
                        <span id="info-langs" class="prop-value">-</span>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ═ Center Canvas ═ --}}
    <div id="canvas-area" class="flex-1 bg-[#FAF8F5] flex flex-col relative overflow-hidden">

        {{-- Canvas viewport --}}
        <div id="canvas-viewport" class="flex-1 overflow-auto relative canvas-viewport-bg" onclick="deselectElement()">
            {{-- Wrapper to center the canvas and prevent top-clipping --}}
            <div id="canvas-wrapper" style="min-height: 100%; width: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 112px 16px 48px 16px; box-sizing: border-box;">
                {{-- Canvas anchor container --}}
                <div id="canvas-anchor" style="position: relative;" onclick="event.stopPropagation()">

                {{-- Floating page actions toolbar (Positioned above the card, scrolling with it) --}}
                <div id="page-toolbar" class="absolute" style="top: -48px; left: 50%; transform: translateX(-50%); width: max-content; min-width: 320px; height: 40px; display: flex; align-items: center; justify-content: space-between; z-index: 10005;">
                    <div class="w-full h-full bg-[rgba(15,10,12,0.92)] backdrop-blur-md border border-[rgba(255,202,210,0.12)] rounded-xl px-3 text-white shadow-xl select-none flex items-center justify-between gap-6">
                        <span id="page-counter" class="text-[11px] font-extrabold text-[#E6C280] tracking-wider uppercase font-mono">Page 1 of 1</span>
                        <div class="page-toolbar-actions">
                            <button onclick="movePageUp()" id="page-up-btn" class="p-1.5 hover:bg-white/10 disabled:opacity-30 rounded-lg transition-colors text-gray-300 hover:text-white" title="Move Page Up">
                                <i data-lucide="chevron-up" class="w-4 h-4"></i>
                            </button>
                            <button onclick="movePageDown()" id="page-down-btn" class="p-1.5 hover:bg-white/10 disabled:opacity-30 rounded-lg transition-colors text-gray-300 hover:text-white" title="Move Page Down">
                                <i data-lucide="chevron-down" class="w-4 h-4"></i>
                            </button>
                            <span class="w-px h-4 bg-white/10 mx-1"></span>
                            <button onclick="duplicateCurrentPage()" class="p-1.5 hover:bg-white/10 rounded-lg transition-colors text-gray-300 hover:text-white flex items-center gap-1 text-[11px] font-bold" title="Duplicate Page">
                                <i data-lucide="copy" class="w-3.5 h-3.5"></i> <span class="page-action-text">Duplicate</span>
                            </button>
                            <button onclick="deleteCurrentPage()" id="page-delete-btn" class="p-1.5 hover:bg-red-500/20 hover:text-red-300 rounded-lg transition-colors text-gray-300 flex items-center gap-1 text-[11px] font-bold" title="Delete Page">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> <span class="page-action-text">Delete</span>
                            </button>
                            <span class="w-px h-4 bg-white/10 mx-1"></span>
                            <button onclick="addPage()" class="p-1.5 hover:bg-[rgba(255,62,92,0.3)] text-[#FFF4E6] hover:text-white rounded-lg transition-colors flex items-center gap-1 text-[11px] font-extrabold" title="Add New Page">
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i> <span class="page-action-text">Add Page</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Canvas card --}}
                <div id="canvas-card" style="width: 100%; height: 100%; position: absolute; top: 0; left: 0; overflow: hidden;" class="bg-white border border-[rgba(255,202,210,0.5)] shadow-2xl">

                    {{-- Logical 1080×1920 layer --}}
                    <div id="canvas-layer" style="width: 1080px; height: 1920px; transform-origin: top left; position: absolute; top: 0; left: 0; overflow: hidden;">

                        {{-- Background image --}}
                        <img id="canvas-bg" src="" alt="" style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; pointer-events: none; user-select: none; display: none;">

                        {{-- Center snap guide --}}
                        <div id="snap-guide" style="display: none; position: absolute; top: 0; bottom: 0; left: 540px; width: 1px; border-left: 2px dashed #AA820A; z-index: 9998; pointer-events: none;"></div>

                        {{-- Elements layer --}}
                        <div id="elements-layer" style="position: absolute; inset: 0;"></div>
                    </div>
                </div>

                {{-- Selection overlay layer --}}
                <div id="selection-overlay" style="width: 1080px; height: 1920px; transform-origin: top left; position: absolute; top: 0; left: 0; pointer-events: none; overflow: visible; z-index: 10000;"></div>
            </div>
            </div>
        </div>

        {{-- Storyboard navigator --}}
        <div id="storyboard-bar" style="height:150px;display:flex;flex-direction:column;justify-content:center;padding:10px 20px;flex-shrink:0;user-select:none;background:#ffffff;border-top:1px solid #E5E7EB;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                <div style="display:flex;align-items:center;gap:8px;">
                    <i data-lucide="film" style="width:12px;height:12px;color:#FF3E5C;"></i>
                    <span style="font-size:9px;font-weight:800;letter-spacing:0.14em;color:#1A1516;text-transform:uppercase;font-family:monospace;">Storyboard / Slide Navigator</span>
                </div>
                <span style="font-size:9px;color:#9CA3AF;font-family:monospace;">Drag cards to reorder pages</span>
            </div>
            <div id="storyboard" style="display:flex;align-items:center;gap:10px;overflow-x:auto;padding-bottom:2px;"></div>
        </div>
    </div>

    {{-- ═ Right Panel ═ --}}
    <div class="right-panel" style="width:272px;padding-bottom:80px;overflow-y:auto;flex-shrink:0;z-index:10;border-left:1px solid #E5E7EB;background:#ffffff;color:#1A1516;">

        {{-- No selection state --}}
        <div id="right-no-selection" style="padding:18px 16px;display:flex;flex-direction:column;gap:16px;">
            <div>
                <span class="rpanel-section-title">Workspace Settings</span>
                <h4 class="rpanel-section-h" style="margin-top:4px;">Document Properties</h4>
            </div>
            <div class="prop-card" style="display:flex;flex-direction:column;gap:12px;">
                <div class="prop-card-row">
                    <span class="prop-label">Canvas Dimensions</span>
                    <span class="prop-value">1080 × 1920 pixels (9:16)</span>
                </div>
                <div class="prop-card-row">
                    <span class="prop-label">Target Aspect Ratio</span>
                    <span class="prop-value">Full HD Portrait Mobile Screen</span>
                </div>
                <div class="prop-card-row">
                    <span class="prop-label">Editing Language</span>
                    <span id="doc-lang-label" style="color:#FF3E5C;font-size:12px;font-weight:700;">English</span>
                </div>
            </div>

            <div style="display:flex;flex-direction:column;gap:8px;">
                <span class="section-label">Reference Instructions</span>
                <div class="instruction-item">
                    <div class="instruction-icon"><i data-lucide="mouse-pointer-2" style="width:10px;height:10px;"></i></div>
                    <p class="instruction-text">Click on any text box element or sticker inside the active slide viewport to edit its positioning, sizing, font spacing, weight, and drop shadow presets.</p>
                </div>
                <div class="instruction-item" style="margin-top:2px;">
                    <div class="instruction-icon"><i data-lucide="keyboard" style="width:10px;height:10px;"></i></div>
                    <div class="instruction-text">
                        <strong>Keyboard Shortcuts:</strong><br>
                        <span class="kbd">Ctrl+Z</span> / <span class="kbd">Ctrl+Y</span> Undo / Redo<br>
                        <span class="kbd">Ctrl+D</span> Duplicate element<br>
                        <span class="kbd">Delete</span> / <span class="kbd">Backspace</span> Delete<br>
                        <span class="kbd">↑↓←→</span> 1px nudge &bull; <span class="kbd">Shift+↑↓</span> 10px
                    </div>
                </div>
            </div>

        </div>

        {{-- Element selected state --}}
        <div id="right-element-props" style="padding:18px 16px;display:none;flex-direction:column;gap:16px;">
            {{-- Header --}}
            <div style="display:flex;align-items:flex-start;justify-content:space-between;padding-bottom:14px;border-bottom:1px solid #E5E7EB;">
                <div>
                    <span id="el-type-label" class="rpanel-section-title">Text Box Element</span>
                    <h4 class="rpanel-section-h" style="margin-top:4px;">Properties Inspector</h4>
                </div>
                <div style="display:flex;gap:4px;">
                    <button onclick="toggleLockSelected()" id="lock-btn" title="Lock/Unlock" style="width:30px;height:30px;border-radius:8px;background:#F9FAFB;border:1px solid #E5E7EB;display:flex;align-items:center;justify-content:center;color:#6B7280;cursor:pointer;transition:all 0.15s;" onmouseover="this.style.color='#FF3E5C';this.style.borderColor='#FECDD3';this.style.background='#FFF1F2';" onmouseout="this.style.color='#6B7280';this.style.borderColor='#E5E7EB';this.style.background='#F9FAFB';">
                        <i data-lucide="unlock" style="width:13px;height:13px;"></i>
                    </button>
                    <button onclick="duplicateSelected()" title="Duplicate" style="width:30px;height:30px;border-radius:8px;background:#F9FAFB;border:1px solid #E5E7EB;display:flex;align-items:center;justify-content:center;color:#6B7280;cursor:pointer;transition:all 0.15s;" onmouseover="this.style.color='#FF3E5C';this.style.borderColor='#FECDD3';this.style.background='#FFF1F2';" onmouseout="this.style.color='#6B7280';this.style.borderColor='#E5E7EB';this.style.background='#F9FAFB';">
                        <i data-lucide="copy" style="width:13px;height:13px;"></i>
                    </button>
                    <button onclick="deleteSelected()" title="Delete" style="width:30px;height:30px;border-radius:8px;background:rgba(220,38,38,0.08);border:1px solid rgba(220,38,38,0.15);display:flex;align-items:center;justify-content:center;color:#f87171;cursor:pointer;transition:all 0.15s;" onmouseover="this.style.background='rgba(220,38,38,0.15)'" onmouseout="this.style.background='rgba(220,38,38,0.08)'">
                        <i data-lucide="trash-2" style="width:13px;height:13px;"></i>
                    </button>
                </div>
            </div>

            {{-- Text-specific properties --}}
            <div id="text-props" style="display:none;flex-direction:column;gap:14px;">
                {{-- Text content --}}
                <div style="display:flex;flex-direction:column;gap:6px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <label id="text-content-label" class="prop-label">Text Content (English)</label>
                        <button onclick="autoTranslateSelected()" id="auto-translate-btn" style="font-size:9px;font-weight:700;color:#FF3E5C;display:flex;align-items:center;gap:3px;background:none;border:none;cursor:pointer;letter-spacing:0.06em;text-transform:uppercase;" onmouseover="this.style.opacity='0.7'" onmouseout="this.style.opacity='1'">
                            <i data-lucide="languages" style="width:10px;height:10px;"></i> Translate
                        </button>
                    </div>
                    <textarea id="el-text" class="prop-input" rows="3" oninput="updateSelectedText(this.value)" style="resize:none;"></textarea>
                </div>

                {{-- Font family + size --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div style="display:flex;flex-direction:column;gap:5px;">
                        <label class="prop-label">Font Family</label>
                        <select id="el-font-family" class="prop-select" onchange="updateSelectedStyle('fontFamily', this.value)"></select>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:5px;">
                        <label class="prop-label">Size (px)</label>
                        <input type="number" id="el-font-size" class="prop-input" oninput="updateSelectedStyle('fontSize', parseInt(this.value)||12)">
                    </div>
                </div>

                {{-- Font weight + letter spacing --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div style="display:flex;flex-direction:column;gap:5px;">
                        <label class="prop-label">Font Weight</label>
                        <select id="el-font-weight" class="prop-select" onchange="updateSelectedStyle('fontWeight', this.value)">
                            <option value="300">Light (300)</option>
                            <option value="normal">Regular (400)</option>
                            <option value="500">Medium (500)</option>
                            <option value="600">Semi-Bold (600)</option>
                            <option value="bold">Bold (700)</option>
                            <option value="800">Extra-Bold (800)</option>
                        </select>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:5px;">
                        <label class="prop-label">Letter Spacing</label>
                        <input type="number" step="0.5" id="el-letter-spacing" class="prop-input" oninput="updateSelectedStyle('letterSpacing', parseFloat(this.value)||0)">
                    </div>
                </div>

                {{-- Shadow preset --}}
                <div style="display:flex;flex-direction:column;gap:5px;">
                    <label class="prop-label">Premium Shadow Presets</label>
                    <select id="el-shadow" class="prop-select" onchange="updateSelectedStyle('textShadow', this.value)">
                        <option value="">None (Clean Matte)</option>
                        <option value="0 2px 4px rgba(74,46,53,0.15)">Subtle Blush Glow</option>
                        <option value="2px 2px 4px rgba(184,107,119,0.4)">Rose Gold Shadow</option>
                        <option value="1px 1px 0px #FFF, 2px 2px 0px #AA820A, 3px 3px 5px rgba(0,0,0,0.25)">Royal Gold Emboss</option>
                        <option value="2px 4px 6px rgba(0,0,0,0.3)">Vintage Charcoal Drop</option>
                    </select>
                </div>

                {{-- Text color --}}
                <div style="display:flex;flex-direction:column;gap:5px;">
                    <label class="prop-label">Text Color</label>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <input type="color" id="el-color-picker" oninput="updateSelectedStyle('color', this.value); document.getElementById('el-color-hex').value = this.value;" style="width:34px;height:34px;border-radius:8px;border:1px solid #E5E7EB;cursor:pointer;padding:2px;background:transparent;">
                        <input type="text" id="el-color-hex" class="prop-input" oninput="updateSelectedStyle('color', this.value); document.getElementById('el-color-picker').value = this.value;" placeholder="#4A2E35" style="font-family:monospace;text-transform:uppercase;">
                    </div>
                </div>

                {{-- Line height --}}
                <div style="display:flex;flex-direction:column;gap:6px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <label class="prop-label">Line Height</label>
                        <span id="line-height-display" style="font-size:10px;font-weight:700;color:#9A8285;">1.2</span>
                    </div>
                    <input type="range" min="0.8" max="2.5" step="0.1" id="el-line-height" oninput="updateSelectedStyle('lineHeight', parseFloat(this.value)); document.getElementById('line-height-display').textContent = this.value;">
                </div>

                {{-- Text alignment --}}
                <div style="display:flex;flex-direction:column;gap:5px;">
                    <label class="prop-label">Alignment</label>
                    <div style="display:flex;border:1px solid #E5E7EB;border-radius:9px;overflow:hidden;">
                        <button onclick="updateSelectedStyle('alignment', 'left'); highlightAlign('left')" id="align-left" class="align-btn" title="Left">
                            <i data-lucide="align-left" style="width:14px;height:14px;"></i>
                        </button>
                        <button onclick="updateSelectedStyle('alignment', 'center'); highlightAlign('center')" id="align-center" class="align-btn active" title="Center">
                            <i data-lucide="align-center" style="width:14px;height:14px;"></i>
                        </button>
                        <button onclick="updateSelectedStyle('alignment', 'right'); highlightAlign('right')" id="align-right" class="align-btn" title="Right">
                            <i data-lucide="align-right" style="width:14px;height:14px;"></i>
                        </button>
                        <button onclick="updateSelectedStyle('alignment', 'justify'); highlightAlign('justify')" id="align-justify" class="align-btn" title="Justify">
                            <i data-lucide="align-justify" style="width:14px;height:14px;"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Layout position section --}}
            <div style="display:flex;flex-direction:column;gap:12px;padding-top:14px;border-top:1px solid #E5E7EB;">
                <label class="section-label">Layout Position Vectors</label>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                    <div style="display:flex;flex-direction:column;gap:4px;">
                        <span class="prop-label">Position X (px)</span>
                        <input type="number" id="el-x" class="prop-input" oninput="updateSelectedCoord('x', this.value)">
                    </div>
                    <div style="display:flex;flex-direction:column;gap:4px;">
                        <span class="prop-label">Position Y (px)</span>
                        <input type="number" id="el-y" class="prop-input" oninput="updateSelectedCoord('y', this.value)">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                    <div style="display:flex;flex-direction:column;gap:4px;">
                        <span class="prop-label">Width W (px)</span>
                        <input type="number" id="el-width" class="prop-input" oninput="updateSelectedCoord('width', this.value)">
                    </div>
                    <div style="display:flex;flex-direction:column;gap:4px;">
                        <span class="prop-label">Height H (px)</span>
                        <input type="number" id="el-height" class="prop-input" oninput="updateSelectedCoord('height', this.value)">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                    <div style="display:flex;flex-direction:column;gap:4px;">
                        <span class="prop-label">Rotation (deg)</span>
                        <input type="number" min="0" max="360" id="el-rotation" class="prop-input" oninput="updateSelectedCoord('rotation', this.value)">
                    </div>
                    <div style="display:flex;flex-direction:column;gap:4px;">
                        <span class="prop-label">Layer Depth (Z)</span>
                        <div style="display:flex;border:1px solid #E5E7EB;border-radius:9px;overflow:hidden;">
                            <button onclick="bringToFront()" class="layer-btn" title="Bring to Front" style="border-right:1px solid #E5E7EB;">
                                <i data-lucide="arrow-up" style="width:13px;height:13px;"></i>
                            </button>
                            <button onclick="sendToBack()" class="layer-btn" title="Send to Back">
                                <i data-lucide="arrow-down" style="width:13px;height:13px;"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div style="display:flex;flex-direction:column;gap:6px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <label class="prop-label">Opacity Alpha</label>
                        <span id="opacity-display" style="font-size:10px;font-weight:700;color:#9A8285;">100%</span>
                    </div>
                    <input type="range" min="0" max="1" step="0.05" id="el-opacity" oninput="updateSelectedCoord('opacity', this.value); document.getElementById('opacity-display').textContent = Math.round(this.value*100)+'%';" style="width:100%;">
                </div>
            </div>

        </div>
    </div>
</div>


{{-- Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬ Toast container Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬ --}}
<div id="editor-toast"></div>

{{-- Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬ Preview Modal Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬ --}}
<div id="preview-modal" class="fixed inset-0 bg-black/95 z-[99990] hidden flex items-center justify-center" onclick="closePreview()">
    <div class="relative flex flex-col items-center gap-4" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between gap-4 w-full">
            <div class="flex items-center gap-2">
                <button onclick="previewPrev()" class="p-2 bg-white/10 hover:bg-white/20 text-white rounded-xl transition-colors">
                    <i data-lucide="chevron-left" class="w-5 h-5"></i>
                </button>
                <span id="preview-counter" class="text-white text-xs font-bold font-mono">1 / 1</span>
                <button onclick="previewNext()" class="p-2 bg-white/10 hover:bg-white/20 text-white rounded-xl transition-colors">
                    <i data-lucide="chevron-right" class="w-5 h-5"></i>
                </button>
            </div>
            <button onclick="closePreview()" class="p-2 bg-white/10 hover:bg-white/20 text-white rounded-xl transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <div id="preview-canvas" class="relative overflow-hidden rounded-2xl shadow-2xl" style="width: 280px; height: 497px; background: #111;"></div>
        <p id="preview-page-name" class="text-white/60 text-xs font-mono"></p>
    </div>
</div>

{{-- Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬ Template ID placeholder (Laravel) Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬ --}}
<script>
window.__TEMPLATE_ID__ = @json($id ?? null);
window.CurrentUser = @json(session('admin_user') ?? null);
</script>

{{-- Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬ Editor JavaScript Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬ --}}
<script>

const LOGICAL_W = 1080;
const LOGICAL_H = 1920;

const editorState = {
    template: null,
    selectedPageIndex: 0,
    selectedElementId: null,
    zoom: 40,
    userZoomed: false,
    selectedLanguage: 'English',
    undoStack: [],
    redoStack: [],
    autosaveTimer: null,
    autosaveStatus: 'idle',
    systemLanguages: ['English'],
    availableFonts: [],
    isTranslating: false,
    dragStartPageIndex: null
};

const PRESETS = [
    { name: "Ganesh Mantra", text: "OM SHREE GANESHAYA NAMAH", description: "Opening mantra for auspicious beginnings", fontFamily: "Rasa", fontSize: 28, fontWeight: "600", color: "#4A2E35", letterSpacing: 2 },
    { name: "Wedding Title (Subh)", text: "SHUBH VIVAH", description: "Traditional Shubh Vivah header", fontFamily: "KAP011", fontSize: 48, fontWeight: "700", color: "#AA820A", letterSpacing: 1 },
    { name: "Ceremony Header", text: "MANGAL PARINAY", description: "Mangal Parinay headline block", fontFamily: "KAP011", fontSize: 48, fontWeight: "700", color: "#AA820A", letterSpacing: 1 },
    { name: "Couple Display Name", text: "Harmi  Weds  Kishan", description: "Perfect couples name placeholder", fontFamily: "Rasa", fontSize: 42, fontWeight: "700", color: "#B86B77" },
    { name: "Invitation Phrase", text: "Together with their families, they invite you to celebrate the wedding ceremony of their children.", description: "Formal wedding invitation detail block", fontFamily: "Rasa", fontSize: 26, fontWeight: "400", color: "#3D3B3C" },
    { name: "Events & Timeline", text: "Ganesh Sthapana - May 24, 2026\nMandap Muhurat - Time: 10:30 AM", description: "Ganesh sthapana schedule", fontFamily: "Rasa", fontSize: 26, fontWeight: "500", color: "#3D3B3C" },
    { name: "Ceremony Schedule", text: "Hast Melap: 5:30 PM\nWedding Feast: 7:00 PM Onwards", description: "Ceremony and feast timings", fontFamily: "Rasa", fontSize: 26, fontWeight: "500", color: "#3D3B3C" },
    { name: "Regards & RSVP Block", text: "With Warm Regards\nPatel Family | RSVP: +91 98765 43210", description: "Warm regards RSVP panel", fontFamily: "Rasa", fontSize: 26, fontWeight: "500", color: "#4A2E35" }
];

const stickers = [
    '/assets/images/stickers/ganesh1.png',
    '/assets/images/stickers/ganesh2.png',
    '/assets/images/stickers/ganesh3.png',
    '/assets/images/stickers/ganesh4.png'
];


document.addEventListener('DOMContentLoaded', async () => {
    lucide.createIcons();
    renderPremiumPresets();
    renderStickersGrid();
    setupLeftPanelSearch();

    if (!window.__TEMPLATE_ID__) {
        showToast('No template ID provided.', 'error');
        return;
    }

    // Load template
    await loadTemplate(window.__TEMPLATE_ID__);

    // Load system languages
    await loadLanguages();

    // Load available fonts
    await loadFonts();

    // Keyboard shortcuts
    document.addEventListener('keydown', handleKeyDown);

    // Window resize
    window.addEventListener('resize', () => {
        if (!editorState.userZoomed) fitCanvasToViewport();
        else renderCanvas();
        renderStoryboard();
    });
});

function setupLeftPanelSearch() {
    const input = document.getElementById('left-search-input');
    if (!input) return;
    input.addEventListener('input', () => {
        const query = input.value.trim().toLowerCase();
        document.querySelectorAll('#panel-text .preset-card, #panel-text .premium-preset-card').forEach(card => {
            const matches = !query || card.textContent.toLowerCase().includes(query);
            card.style.display = matches ? 'block' : 'none';
        });
    });
}

async function loadTemplate(id) {
    try {
        const userId = window.CurrentUser ? window.CurrentUser.id : 'admin_super';
        const res = await fetch(`/api/templates/${id}`, { headers: { 'x-user-id': userId } });
        if (!res.ok) throw new Error('Template not found');
        const data = await res.json();
        editorState.template = data;

        document.getElementById('editor-tpl-name').textContent = data.name || 'Template';
        document.getElementById('editor-tpl-name').title = data.name || '';
        document.getElementById('editor-tpl-slug').textContent = data.slug || '';
        document.getElementById('editor-tpl-slug').title = data.slug || '';

        // Show/hide premium badge dynamically
        const badge = document.getElementById('editor-tpl-premium-badge');
        if (badge) {
            badge.style.display = (data.isPremium === true || data.isPremium === 1 || data.isPremium === 'true') ? 'inline-flex' : 'none';
        }

        document.getElementById('info-name').textContent = data.name || '-';
        document.getElementById('info-slug').textContent = data.slug || '-';
        document.getElementById('info-fonts').textContent = (data.fonts || []).join(', ') || '-';
        document.getElementById('info-langs').textContent = (data.languages || []).join(', ') || '-';

        fitCanvasToViewport();
        renderStoryboard();
        renderPagesList();
        updatePageToolbar();

        showToast('Template loaded.', 'success');
    } catch (err) {
        console.error(err);
        showToast('Failed to load template.', 'error');
    }
}

async function loadLanguages() {
    try {
        const userId = window.CurrentUser ? window.CurrentUser.id : 'admin_super';
        const res = await fetch('/api/languages', { headers: { 'x-user-id': userId } });
        if (res.ok) {
            const data = await res.json();
            if (Array.isArray(data)) {
                const activeLangs = data.filter(l => l.isActive).map(l => l.name);
                const langs = activeLangs.includes('English') ? activeLangs : ['English', ...activeLangs];
                editorState.systemLanguages = langs;
                const sel = document.getElementById('lang-select');
                sel.innerHTML = langs.map(l => `<option value="${escapeHtml(l)}">${escapeHtml(l)}</option>`).join('');
                sel.value = 'English';
            }
        }
    } catch (err) { console.error(err); }
}

async function loadFonts() {
    try {
        const userId = window.CurrentUser ? window.CurrentUser.id : 'admin_super';
        const res = await fetch('/api/fonts', { headers: { 'x-user-id': userId } });
        if (res.ok) {
            const data = await res.json();
            if (Array.isArray(data)) {
                const fontFamilies = data.filter(f => f.isActive).map(f => f.family);
                const templateFonts = editorState.template?.fonts || [];
                editorState.availableFonts = [...new Set([...templateFonts, ...fontFamilies])];

                // Populate font family dropdown
                const sel = document.getElementById('el-font-family');
                sel.innerHTML = editorState.availableFonts.map(f => `<option value="${escapeHtml(f)}">${escapeHtml(f)}</option>`).join('')
                    + '<option value="sans-serif">System Sans</option>';
            }
        }
    } catch (err) { console.error(err); }
}

// Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â
// ZOOM & CANVAS SIZING
// Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â
function fitCanvasToViewport() {
    const viewport = document.getElementById('canvas-viewport');
    if (!viewport) return;
    const availableW = Math.max(320, viewport.clientWidth - 96);
    const availableH = Math.max(420, viewport.clientHeight - 144);
    const widthFit = availableW / LOGICAL_W;
    const heightComfort = (availableH / LOGICAL_H) * 1.35;
    const fit = Math.min(widthFit, Math.max(widthFit * 0.82, heightComfort));
    const nextZoom = Math.max(28, Math.min(35, Math.floor(fit * 100)));
    setZoom(nextZoom, true);
}

function setZoom(val, isAuto = false) {
    if (!isAuto) editorState.userZoomed = true;
    editorState.zoom = Math.max(10, Math.min(200, val));
    document.getElementById('zoom-display').textContent = `${editorState.zoom}%`;
    renderCanvas();
    renderStoryboard();
}

function getDisplayScale() {
    return editorState.zoom / 100;
}

function renderCanvas() {
    if (!editorState.template) return;
    const scale = getDisplayScale();
    const displayW = LOGICAL_W * scale;
    const displayH = LOGICAL_H * scale;

    const anchor = document.getElementById('canvas-anchor');
    anchor.style.width = `${displayW}px`;
    anchor.style.height = `${displayH}px`;

    const card = document.getElementById('canvas-card');
    card.style.width = `${displayW}px`;
    card.style.height = `${displayH}px`;

    const layer = document.getElementById('canvas-layer');
    layer.style.transform = `scale(${scale})`;

    const overlayLayer = document.getElementById('selection-overlay');
    overlayLayer.style.width = `${LOGICAL_W}px`;
    overlayLayer.style.height = `${LOGICAL_H}px`;
    overlayLayer.style.transform = `scale(${scale})`;

    renderCurrentPage();
    renderSelectionOverlay();
    updatePageToolbar();
}

// Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â
// PAGE RENDERING
// Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â
function getImageUrl(path) {
    if (!path) return '';
    try {
        if (path.startsWith('http://') || path.startsWith('https://')) {
            const parsed = new URL(path);
            if (parsed.origin !== window.location.origin) {
                return window.location.origin + parsed.pathname + parsed.search;
            }
            return path;
        }
    } catch(e) {}
    return path.startsWith('/') ? path : '/' + path;
}

function getCurrentPage() {
    if (!editorState.template) return null;
    return editorState.template.pages[editorState.selectedPageIndex] || null;
}

function renderCurrentPage() {
    const page = getCurrentPage();
    if (!page) return;

    // Background
    const bgImg = document.getElementById('canvas-bg');
    if (page.backgroundImage) {
        bgImg.src = getImageUrl(page.backgroundImage);
        bgImg.style.display = 'block';
    } else {
        bgImg.style.display = 'none';
    }

    // Elements
    const elemLayer = document.getElementById('elements-layer');
    elemLayer.innerHTML = '';

    const scale = getDisplayScale();
    (page.elements || []).forEach(elem => {
        const el = createElementDOM(elem, scale);
        elemLayer.appendChild(el);
    });

    lucide.createIcons({ nodeList: [elemLayer] });

    // Update page background preview in panel
    const bgPreview = document.getElementById('bg-preview-container');
    const bgPlaceholder = document.getElementById('bg-placeholder');
    const bgPreviewImg = document.getElementById('bg-preview-img');
    if (page.backgroundImage) {
        bgPreviewImg.src = getImageUrl(page.backgroundImage);
        bgPreview.classList.remove('hidden');
        bgPlaceholder.classList.add('hidden');
    } else {
        bgPreview.classList.add('hidden');
        bgPlaceholder.classList.remove('hidden');
    }
}

function getDisplayText(elem) {
    const lang = editorState.selectedLanguage;
    return (elem.translations && elem.translations[lang] !== undefined)
        ? elem.translations[lang]
        : (elem.text || '');
}

function getStyleVal(elem, key, fallback) {
    const lang = editorState.selectedLanguage;
    const langStyles = elem.languageStyles && elem.languageStyles[lang];
    if (langStyles && langStyles[key] !== undefined) return langStyles[key];
    return elem[key] !== undefined ? elem[key] : fallback;
}

function createElementDOM(elem, scale) {
    const isText = elem.type === 'text';
    const displayText = getDisplayText(elem);

    const container = document.createElement('div');
    container.id = elem.id;
    container.className = 'canvas-element';
    container.style.cssText = `
        position: absolute;
        left: 0; top: 0;
        width: ${elem.width}px;
        ${isText ? 'height: auto;' : `height: ${elem.height}px;`}
        ${isText ? '' : `min-height: ${elem.height}px;`}
        transform: translate(${elem.x}px, ${elem.y}px) rotate(${elem.rotation || 0}deg);
        transform-origin: center;
        opacity: ${elem.opacity !== undefined ? elem.opacity : 1};
        z-index: ${elem.zIndex || 1};
        cursor: ${elem.isLocked ? 'not-allowed' : 'move'};
        box-sizing: border-box;
        overflow: visible;
        ${isText ? 'display: inline-block;' : 'display: block;'}
        user-select: none;
        -webkit-user-select: none;
    `;

    if (isText) {
        const fontFamily = getStyleVal(elem, 'fontFamily', 'Rasa');
        const fontSize = getStyleVal(elem, 'fontSize', 36);
        const color = getStyleVal(elem, 'color', '#4A2E35');
        const lineHeight = getStyleVal(elem, 'lineHeight', 1.2);
        const alignment = getStyleVal(elem, 'alignment', 'center');
        const fontWeight = getStyleVal(elem, 'fontWeight', 'normal');
        const letterSpacing = getStyleVal(elem, 'letterSpacing', 0);
        const textShadow = getStyleVal(elem, 'textShadow', '');

        const inner = document.createElement('div');
        inner.className = 'text-actual-content';
        inner.style.cssText = `
            font-family: ${fontFamily};
            font-size: ${fontSize}px;
            color: ${color};
            line-height: ${lineHeight};
            text-align: ${alignment};
            font-weight: ${fontWeight};
            letter-spacing: ${letterSpacing}px;
            text-shadow: ${textShadow || 'none'};
            white-space: pre-wrap;
            word-break: break-word;
            overflow-wrap: break-word;
            display: block;
            width: 100%;
            height: auto;
            overflow: visible;
            box-sizing: border-box;
            user-select: none;
            -webkit-user-select: none;
        `;
        inner.textContent = displayText;
        container.appendChild(inner);
    } else {
        const img = document.createElement('img');
        img.src = getImageUrl(elem.imagePath);
        img.alt = 'Element';
        img.draggable = false;
        img.style.cssText = 'width: 100%; height: 100%; object-fit: contain; pointer-events: none; user-select: none; display: block;';
        img.onerror = () => {
            img.src = "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='100' height='100'><rect width='100' height='100' fill='%23FFD1D7'/><text x='50' y='55' font-size='24' text-anchor='middle'>🌺</text></svg>";
        };
        container.appendChild(img);
    }

    // Mouse events for drag
    if (!elem.isLocked) {
        container.addEventListener('mousedown', (e) => {
            e.stopPropagation();
            selectElement(elem.id);
            startDrag(e, elem);
        });
    } else {
        container.addEventListener('mousedown', (e) => {
            e.stopPropagation();
            selectElement(elem.id);
        });
    }
    container.addEventListener('click', (e) => e.stopPropagation());

    return container;
}

// Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â
// SELECTION
// Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â
function selectElement(id) {
    editorState.selectedElementId = id;
    renderSelectionOverlay();
    updateRightPanel();
}

function deselectElement() {
    editorState.selectedElementId = null;
    renderSelectionOverlay();
    updateRightPanel();
}

function renderSelectionOverlay() {
    const overlay = document.getElementById('selection-overlay');
    overlay.innerHTML = '';

    if (!editorState.selectedElementId) return;

    const page = getCurrentPage();
    if (!page) return;

    const elem = page.elements.find(e => e.id === editorState.selectedElementId);
    if (!elem || elem.isLocked) return;

    const scale = getDisplayScale();

    // Measure actual height for text elements
    const domEl = document.getElementById(elem.id);
    const actualHeight = (elem.type === 'text' && domEl) ? domEl.clientHeight : elem.height;

    const wrapper = document.createElement('div');
    wrapper.style.cssText = `
        position: absolute; left: 0; top: 0;
        width: ${elem.width}px; height: ${actualHeight}px;
        transform: translate(${elem.x}px, ${elem.y}px) rotate(${elem.rotation || 0}deg);
        transform-origin: center;
        overflow: visible; pointer-events: none;
    `;

    // Selection border
    const border = document.createElement('div');
    const bw = 1.5 / scale;
    border.style.cssText = `
        position: absolute; inset: 0;
        border: ${bw}px solid #C55B6C;
        pointer-events: none; z-index: 9999; box-sizing: border-box;
    `;
    wrapper.appendChild(border);

    // Coord badge
    const badge = document.createElement('div');
    const badgeScale = 1 / scale;
    badge.style.cssText = `
        position: absolute; bottom: ${-32/scale}px; left: 50%;
        transform: translateX(-50%) scale(${badgeScale});
        transform-origin: center top;
        background: #1E1E1E; color: #fff;
        padding: 2px 8px; border-radius: 4px; font-size: 10px;
        font-weight: 500; font-family: monospace; white-space: nowrap;
        box-shadow: 0 2px 4px rgba(0,0,0,0.25);
        z-index: 10002; pointer-events: none;
    `;
    badge.textContent = `W: ${Math.round(elem.width)} | H: ${Math.round(actualHeight)} | X: ${Math.round(elem.x)} | Y: ${Math.round(elem.y)}`;
    wrapper.appendChild(badge);

    // Resize handles
    const half = 4 / scale;
    const handles = [
        { pos: 'nw', style: `left: ${-half}px; top: ${-half}px; cursor: nw-resize;` },
        { pos: 'n', style: `left: calc(50% - ${half}px); top: ${-half}px; cursor: n-resize;` },
        { pos: 'ne', style: `right: ${-half}px; top: ${-half}px; cursor: ne-resize;` },
        { pos: 'w', style: `left: ${-half}px; top: calc(50% - ${half}px); cursor: w-resize;` },
        { pos: 'e', style: `right: ${-half}px; top: calc(50% - ${half}px); cursor: e-resize;` },
        { pos: 'sw', style: `left: ${-half}px; bottom: ${-half}px; cursor: sw-resize;` },
        { pos: 's', style: `left: calc(50% - ${half}px); bottom: ${-half}px; cursor: s-resize;` },
        { pos: 'se', style: `right: ${-half}px; bottom: ${-half}px; cursor: se-resize;` },
    ];

    const hSize = 8 / scale;
    const hBorder = 1.5 / scale;
    handles.forEach(h => {
        const dot = document.createElement('div');
        dot.style.cssText = `
            position: absolute; width: ${hSize}px; height: ${hSize}px;
            background: #fff; border: ${hBorder}px solid #C55B6C; border-radius: 50%;
            z-index: 10001; box-sizing: border-box; pointer-events: auto;
            ${h.style}
        `;
        dot.addEventListener('mousedown', (e) => {
            e.preventDefault(); e.stopPropagation();
            startResize(e, elem, h.pos, actualHeight);
        });
        wrapper.appendChild(dot);
    });

    // Rotate handle
    const rotateDot = document.createElement('div');
    const rotateOff = 16 / scale;
    rotateDot.style.cssText = `
        position: absolute; width: ${hSize}px; height: ${hSize}px;
        background: #AA820A; border: ${hBorder}px solid #AA820A; border-radius: 50%;
        z-index: 10001; box-sizing: border-box; pointer-events: auto; cursor: grab;
        left: calc(50% - ${half}px); top: ${-half - rotateOff}px;
    `;
    rotateDot.addEventListener('mousedown', (e) => {
        e.preventDefault(); e.stopPropagation();
        startRotate(e, elem);
    });
    wrapper.appendChild(rotateDot);

    overlay.appendChild(wrapper);
}

// Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â
// DRAG / RESIZE / ROTATE
// Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â
function startDrag(e, elem) {
    const scale = getDisplayScale();
    const startX = e.clientX, startY = e.clientY;
    const initX = elem.x, initY = elem.y;
    const SNAP = 12;

    pushHistory();

    const onMove = (mv) => {
        const dx = (mv.clientX - startX) / scale;
        const dy = (mv.clientY - startY) / scale;
        let newX = Math.round(initX + dx);
        let newY = Math.round(initY + dy);

        // Snap to center
        const centerX = newX + elem.width / 2;
        const snapGuide = document.getElementById('snap-guide');
        if (Math.abs(centerX - 540) < SNAP) {
            newX = Math.round(540 - elem.width / 2);
            snapGuide.style.display = 'block';
        } else {
            snapGuide.style.display = 'none';
        }

        updateElement(elem.id, { x: newX, y: newY }, true);
    };

    const onUp = () => {
        document.getElementById('snap-guide').style.display = 'none';
        window.removeEventListener('mousemove', onMove);
        window.removeEventListener('mouseup', onUp);
    };

    window.addEventListener('mousemove', onMove);
    window.addEventListener('mouseup', onUp);
}

function startResize(e, elem, pos, actualHeight) {
    const scale = getDisplayScale();
    const startX = e.clientX, startY = e.clientY;
    const initX = elem.x, initY = elem.y;
    const initW = elem.width, initH = actualHeight;
    const initFontSize = getStyleVal(elem, 'fontSize', 36);

    pushHistory();

    const isCorner = pos === 'nw' || pos === 'ne' || pos === 'sw' || pos === 'se';
    const isText = elem.type === 'text';

    const onMove = (mv) => {
        const dx = (mv.clientX - startX) / scale;
        const dy = (mv.clientY - startY) / scale;
        let x = initX, y = initY, w = initW, h = initH;

        if (isText && isCorner) {
            // Proportional resizing for text elements based purely on horizontal movement (dx)
            // This avoids axis switching which causes violent jumping/glitching
            let scaleFactor = 1;
            if (pos.includes('e')) {
                scaleFactor = (initW + dx) / initW;
            } else if (pos.includes('w')) {
                scaleFactor = (initW - dx) / initW;
            }
            
            // Limit minimum scale
            scaleFactor = Math.max(0.15, scaleFactor);
            w = initW * scaleFactor;
            h = initH * scaleFactor;

            // Adjust coordinates based on fixed anchor points
            if (pos.includes('w')) {
                x = (initX + initW) - w;
            }
            if (pos.includes('n')) {
                y = (initY + initH) - h;
            }

            // Calculate new font size
            const newFontSize = Math.max(8, Math.round(initFontSize * scaleFactor));
            
            // Build style update for the current language
            const lang = editorState.selectedLanguage;
            const langStyles = {
                ...(elem.languageStyles || {}),
                [lang]: {
                    ...((elem.languageStyles || {})[lang] || {}),
                    fontSize: newFontSize
                }
            };
            const updates = { 
                x: Math.round(x), 
                y: Math.round(y), 
                width: Math.max(40, Math.round(w)), 
                height: Math.max(20, Math.round(h)),
                languageStyles: langStyles
            };
            if (lang === 'English') {
                updates.fontSize = newFontSize;
            }

            updateElement(elem.id, updates, true);
        } else {
            // Standard non-proportional resizing
            if (pos.includes('e')) w = Math.max(40, initW + dx);
            if (pos.includes('s')) h = Math.max(20, initH + dy);
            if (pos.includes('w')) { x = initX + dx; w = Math.max(40, initW - dx); }
            if (pos.includes('n')) { y = initY + dy; h = Math.max(20, initH - dy); }

            updateElement(elem.id, { 
                x: Math.round(x), 
                y: Math.round(y), 
                width: Math.max(40, Math.round(w)), 
                height: Math.max(20, Math.round(h)) 
            }, true);
        }
    };

    const onUp = () => {
        window.removeEventListener('mousemove', onMove);
        window.removeEventListener('mouseup', onUp);
    };

    window.addEventListener('mousemove', onMove);
    window.addEventListener('mouseup', onUp);
}

function startRotate(e, elem) {
    const scale = getDisplayScale();
    const layer = document.getElementById('canvas-layer');
    const rect = layer.getBoundingClientRect();

    // The actual screen position of center of element
    const centerX = rect.left + (elem.x + elem.width / 2) * scale;
    const centerY = rect.top + (elem.y + elem.height / 2) * scale;

    pushHistory();

    const onMove = (mv) => {
        const angle = Math.atan2(mv.clientY - centerY, mv.clientX - centerX) * (180 / Math.PI) + 90;
        updateElement(elem.id, { rotation: Math.round(angle) }, true);
    };

    const onUp = () => {
        window.removeEventListener('mousemove', onMove);
        window.removeEventListener('mouseup', onUp);
    };

    window.addEventListener('mousemove', onMove);
    window.addEventListener('mouseup', onUp);
}

// Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â
// ELEMENT CRUD
// Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â
function genId() {
    return `elem_${Math.random().toString(36).substr(2, 9)}_${Date.now()}`;
}

function updateElement(id, props, skipHistory) {
    const page = getCurrentPage();
    if (!page) return;

    page.elements = page.elements.map(el => {
        if (el.id !== id) return el;
        return { ...el, ...props };
    });

    // Re-render canvas elements only
    renderCurrentPage();
    renderSelectionOverlay();
    updateRightPanel();

    if (!skipHistory) {
        triggerAutosave();
    }
}

function addElement(elemData) {
    const page = getCurrentPage();
    if (!page) return;
    pushHistory();
    const elem = {
        id: genId(),
        zIndex: (page.elements || []).length + 1,
        rotation: 0,
        opacity: 1,
        isLocked: false,
        translations: {},
        ...elemData
    };
    page.elements = [...(page.elements || []), elem];
    renderCurrentPage();
    renderStoryboard();
    selectElement(elem.id);
    triggerAutosave();
}

function deleteElement(id) {
    const page = getCurrentPage();
    if (!page) return;
    pushHistory();
    page.elements = page.elements.filter(el => el.id !== id);
    if (editorState.selectedElementId === id) deselectElement();
    renderCurrentPage();
    renderSelectionOverlay();
    renderStoryboard();
    triggerAutosave();
}

function duplicateElement(id) {
    const page = getCurrentPage();
    if (!page) return;
    const elem = page.elements.find(el => el.id === id);
    if (!elem) return;
    pushHistory();
    const newElem = { ...JSON.parse(JSON.stringify(elem)), id: genId(), x: elem.x + 30, y: elem.y + 30, zIndex: (page.elements.length + 1) };
    page.elements.push(newElem);
    renderCurrentPage();
    renderStoryboard();
    selectElement(newElem.id);
    triggerAutosave();
}

function bringToFront() {
    const page = getCurrentPage();
    const id = editorState.selectedElementId;
    if (!page || !id) return;
    const maxZ = Math.max(...page.elements.map(el => el.zIndex || 1));
    updateElement(id, { zIndex: maxZ + 1 });
    triggerAutosave();
}

function sendToBack() {
    const page = getCurrentPage();
    const id = editorState.selectedElementId;
    if (!page || !id) return;
    updateElement(id, { zIndex: 0 });
    triggerAutosave();
}

function toggleLockSelected() {
    const page = getCurrentPage();
    const id = editorState.selectedElementId;
    if (!page || !id) return;
    const elem = page.elements.find(el => el.id === id);
    if (!elem) return;
    updateElement(id, { isLocked: !elem.isLocked });
    updateRightPanel();
}

function deleteSelected() {
    if (editorState.selectedElementId) deleteElement(editorState.selectedElementId);
}

function duplicateSelected() {
    if (editorState.selectedElementId) duplicateElement(editorState.selectedElementId);
}

// Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â
// TEXT MANIPULATION
// Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â
function updateSelectedText(val) {
    const id = editorState.selectedElementId;
    const page = getCurrentPage();
    if (!page || !id) return;
    const elem = page.elements.find(el => el.id === id);
    if (!elem || elem.type !== 'text') return;

    const lang = editorState.selectedLanguage;
    const translations = { ...(elem.translations || {}), [lang]: val };
    // Always update base text too
    updateElement(id, { text: val, translations });
}

function updateSelectedStyle(prop, val) {
    const id = editorState.selectedElementId;
    const page = getCurrentPage();
    if (!page || !id) return;
    const elem = page.elements.find(el => el.id === id);
    if (!elem) return;

    const lang = editorState.selectedLanguage;
    const langStyles = {
        ...(elem.languageStyles || {}),
        [lang]: {
            ...((elem.languageStyles || {})[lang] || {}),
            [prop]: val
        }
    };
    const updates = { languageStyles: langStyles };
    if (lang === 'English') updates[prop] = val;
    updateElement(id, updates);
}

function updateSelectedCoord(prop, val) {
    const id = editorState.selectedElementId;
    if (!id) return;
    const num = parseFloat(val) || 0;
    updateElement(id, { [prop]: num });
}

// Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â
// RIGHT PANEL
// Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â
function updateRightPanel() {
    const page = getCurrentPage();
    const id = editorState.selectedElementId;

    document.getElementById('doc-lang-label').textContent = editorState.selectedLanguage;

    if (!id || !page) {
        document.getElementById('right-no-selection').style.display = 'flex';
        document.getElementById('right-element-props').style.display = 'none';
        return;
    }

    const elem = page.elements.find(el => el.id === id);
    if (!elem) {
        document.getElementById('right-no-selection').style.display = 'flex';
        document.getElementById('right-element-props').style.display = 'none';
        return;
    }

    document.getElementById('right-no-selection').style.display = 'none';
    document.getElementById('right-element-props').style.display = 'flex';

    const typeLabel = elem.type === 'text' ? 'Text Box Element' : elem.type === 'sticker' ? 'Sticker Asset' : 'Image Graphic';
    document.getElementById('el-type-label').textContent = typeLabel;

    // Lock button
    const lockBtn = document.getElementById('lock-btn');
    lockBtn.innerHTML = `<i data-lucide="${elem.isLocked ? 'lock' : 'unlock'}" style="width:13px;height:13px;"></i>`;
    lockBtn.style.background = elem.isLocked ? 'rgba(247,197,102,0.15)' : 'rgba(255,255,255,0.04)';
    lockBtn.style.borderColor = elem.isLocked ? 'rgba(247,197,102,0.4)' : 'rgba(255,202,210,0.1)';
    lockBtn.style.color = elem.isLocked ? '#C9943B' : '#9A8285';
    lucide.createIcons({ nodeList: [lockBtn] });

    // Text properties
    const textProps = document.getElementById('text-props');
    if (elem.type === 'text') {
        textProps.style.display = 'flex';
        const lang = editorState.selectedLanguage;
        const displayText = (elem.translations && elem.translations[lang] !== undefined) ? elem.translations[lang] : (elem.text || '');
        document.getElementById('el-text').value = displayText;
        document.getElementById('text-content-label').textContent = `Text Content (${lang})`;

        const fontFamily = getStyleVal(elem, 'fontFamily', 'Rasa');
        const fontSize = getStyleVal(elem, 'fontSize', 36);
        const fontWeight = getStyleVal(elem, 'fontWeight', 'normal');
        const letterSpacing = getStyleVal(elem, 'letterSpacing', 0);
        const color = getStyleVal(elem, 'color', '#4A2E35');
        const lineHeight = getStyleVal(elem, 'lineHeight', 1.2);
        const alignment = getStyleVal(elem, 'alignment', 'center');
        const textShadow = getStyleVal(elem, 'textShadow', '');

        document.getElementById('el-font-family').value = fontFamily;
        document.getElementById('el-font-size').value = fontSize;
        document.getElementById('el-font-weight').value = fontWeight;
        document.getElementById('el-letter-spacing').value = letterSpacing;
        document.getElementById('el-color-picker').value = color;
        document.getElementById('el-color-hex').value = color;
        document.getElementById('el-line-height').value = lineHeight;
        document.getElementById('line-height-display').textContent = lineHeight;
        document.getElementById('el-shadow').value = textShadow;
        highlightAlign(alignment);
    } else {
        textProps.style.display = 'none';
    }

    // Position
    document.getElementById('el-x').value = Math.round(elem.x);
    document.getElementById('el-y').value = Math.round(elem.y);
    document.getElementById('el-width').value = Math.round(elem.width);
    document.getElementById('el-height').value = Math.round(elem.height);
    document.getElementById('el-rotation').value = Math.round(elem.rotation || 0);
    document.getElementById('el-opacity').value = elem.opacity !== undefined ? elem.opacity : 1;
    document.getElementById('opacity-display').textContent = Math.round((elem.opacity || 1) * 100) + '%';
}

function highlightAlign(align) {
    ['left', 'center', 'right', 'justify'].forEach(a => {
        const btn = document.getElementById(`align-${a}`);
        if (!btn) return;
        btn.classList.toggle('active', a === align);
    });
}

// Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â
// PAGES MANAGEMENT
// Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â
function selectPage(idx) {
    editorState.selectedPageIndex = idx;
    editorState.selectedElementId = null;
    renderCurrentPage();
    renderSelectionOverlay();
    renderPagesList();
    renderStoryboard();
    updatePageToolbar();
    updateRightPanel();
}

function addPage() {
    if (!editorState.template) return;
    pushHistory();
    const newPage = {
        id: `page_${Math.random().toString(36).substr(2, 9)}`,
        name: `Page ${editorState.template.pages.length + 1}`,
        backgroundImage: '',
        elements: [{
            id: genId(), type: 'text',
            x: 100, y: 400, width: 880, height: 100,
            rotation: 0, opacity: 1, zIndex: 1, isLocked: false,
            text: 'Double click to edit text', fontFamily: 'Rasa',
            fontSize: 36, color: '#4A2E35', lineHeight: 1.2, alignment: 'center',
            translations: { English: 'Double click to edit text' }
        }]
    };
    editorState.template.pages.push(newPage);
    const newIdx = editorState.template.pages.length - 1;
    selectPage(newIdx);
    triggerAutosave();
}

function deleteCurrentPage() {
    if (!editorState.template || editorState.template.pages.length <= 1) return;
    pushHistory();
    editorState.template.pages.splice(editorState.selectedPageIndex, 1);
    const newIdx = Math.min(editorState.selectedPageIndex, editorState.template.pages.length - 1);
    selectPage(newIdx);
    triggerAutosave();
}

function duplicateCurrentPage() {
    if (!editorState.template) return;
    pushHistory();
    const page = JSON.parse(JSON.stringify(getCurrentPage()));
    page.id = `page_${Math.random().toString(36).substr(2, 9)}`;
    page.name = `${page.name} (Copy)`;
    page.elements = page.elements.map(el => ({ ...el, id: genId() }));
    const insertAt = editorState.selectedPageIndex + 1;
    editorState.template.pages.splice(insertAt, 0, page);
    selectPage(insertAt);
    triggerAutosave();
}

function movePageUp() {
    const idx = editorState.selectedPageIndex;
    if (!editorState.template || idx === 0) return;
    pushHistory();
    const pages = editorState.template.pages;
    const tmp = pages[idx]; pages[idx] = pages[idx-1]; pages[idx-1] = tmp;
    selectPage(idx - 1);
    triggerAutosave();
}

function movePageDown() {
    const idx = editorState.selectedPageIndex;
    if (!editorState.template || idx >= editorState.template.pages.length - 1) return;
    pushHistory();
    const pages = editorState.template.pages;
    const tmp = pages[idx]; pages[idx] = pages[idx+1]; pages[idx+1] = tmp;
    selectPage(idx + 1);
    triggerAutosave();
}

function clearPageBackground() {
    const page = getCurrentPage();
    if (!page) return;
    page.backgroundImage = '';
    renderCurrentPage();
    renderStoryboard();
    triggerAutosave();
}

function updatePageToolbar() {
    if (!editorState.template) return;
    const idx = editorState.selectedPageIndex;
    const total = editorState.template.pages.length;
    document.getElementById('page-counter').textContent = `Page ${idx+1} of ${total}`;
    document.getElementById('page-up-btn').disabled = idx === 0;
    document.getElementById('page-down-btn').disabled = idx >= total - 1;
    document.getElementById('page-delete-btn').disabled = total <= 1;
}

function renderPagesList() {
    if (!editorState.template) return;
    const list = document.getElementById('pages-list');
    list.innerHTML = '';

    editorState.template.pages.forEach((page, idx) => {
        const isActive = idx === editorState.selectedPageIndex;
        const div = document.createElement('div');
        div.onclick = () => selectPage(idx);
        div.className = `p-3.5 border rounded-2xl cursor-pointer flex justify-between items-center group transition-all duration-300 ${isActive
            ? 'border-[#FF3E5C] bg-[rgba(255,240,242,0.35)] shadow-sm font-bold text-[#FF3E5C]'
            : 'border-[rgba(255,202,210,0.25)] hover:border-[rgba(255,202,210,0.6)] text-[#231A1C]'}`;

        div.innerHTML = `
            <div class="flex items-center gap-2">
                <span class="w-5 h-5 bg-white border border-[rgba(255,202,210,0.4)] rounded-full flex items-center justify-center text-[10px] font-bold text-[#231A1C]">${idx+1}</span>
                <span class="text-xs truncate max-w-[80px]">${escapeHtml(page.name)}</span>
            </div>
            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                <button onclick="event.stopPropagation(); movePageUpByIdx(${idx})" ${idx===0?'disabled':''} class="p-1 text-[#231A1C] hover:bg-[rgba(255,240,242,0.8)] disabled:opacity-20 rounded-lg transition-colors" title="Move Up"><i data-lucide="arrow-up" class="w-3.5 h-3.5"></i></button>
                <button onclick="event.stopPropagation(); movePageDownByIdx(${idx})" ${idx>=editorState.template.pages.length-1?'disabled':''} class="p-1 text-[#231A1C] hover:bg-[rgba(255,240,242,0.8)] disabled:opacity-20 rounded-lg transition-colors" title="Move Down"><i data-lucide="arrow-down" class="w-3.5 h-3.5"></i></button>
                <button onclick="event.stopPropagation(); duplicatePageByIdx(${idx})" class="p-1 text-[#231A1C] hover:bg-[rgba(255,240,242,0.8)] rounded-lg transition-colors" title="Duplicate"><i data-lucide="copy" class="w-3.5 h-3.5"></i></button>
                ${editorState.template.pages.length > 1
                    ? `<button onclick="event.stopPropagation(); deletePageByIdx(${idx})" class="p-1 text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Delete"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>`
                    : ''}
            </div>
        `;
        list.appendChild(div);
    });
    lucide.createIcons({ nodeList: [list] });
}

function movePageUpByIdx(idx) {
    if (idx === 0) return;
    pushHistory();
    const pages = editorState.template.pages;
    const tmp = pages[idx]; pages[idx] = pages[idx-1]; pages[idx-1] = tmp;
    if (editorState.selectedPageIndex === idx) selectPage(idx - 1);
    else if (editorState.selectedPageIndex === idx - 1) selectPage(idx);
    else renderPagesList();
    triggerAutosave();
}

function movePageDownByIdx(idx) {
    if (idx >= editorState.template.pages.length - 1) return;
    pushHistory();
    const pages = editorState.template.pages;
    const tmp = pages[idx]; pages[idx] = pages[idx+1]; pages[idx+1] = tmp;
    if (editorState.selectedPageIndex === idx) selectPage(idx + 1);
    else if (editorState.selectedPageIndex === idx + 1) selectPage(idx);
    else renderPagesList();
    triggerAutosave();
}

function duplicatePageByIdx(idx) {
    pushHistory();
    const page = JSON.parse(JSON.stringify(editorState.template.pages[idx]));
    page.id = `page_${Math.random().toString(36).substr(2, 9)}`;
    page.name = `${page.name} (Copy)`;
    page.elements = page.elements.map(el => ({ ...el, id: genId() }));
    editorState.template.pages.splice(idx + 1, 0, page);
    selectPage(idx + 1);
    triggerAutosave();
}

function deletePageByIdx(idx) {
    if (editorState.template.pages.length <= 1) return;
    pushHistory();
    editorState.template.pages.splice(idx, 1);
    const newIdx = Math.min(editorState.selectedPageIndex, editorState.template.pages.length - 1);
    selectPage(newIdx);
    triggerAutosave();
}

// Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â
// STORYBOARD NAVIGATOR
// Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â
function renderStoryboard() {
    if (!editorState.template) return;
    const storyboard = document.getElementById('storyboard');
    storyboard.innerHTML = '';

    const thumbScale = 0.046;

    editorState.template.pages.forEach((page, idx) => {
        const isSelected = idx === editorState.selectedPageIndex;

        const card = document.createElement('div');
        card.draggable = true;
        card.className = `storyboard-card${isSelected ? ' is-active' : ''}`;

        if (page.backgroundImage) {
            const img = document.createElement('img');
            img.src = getImageUrl(page.backgroundImage);
            img.style.cssText = 'position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; pointer-events: none;';
            card.appendChild(img);
        }

        (page.elements || []).forEach(el => {
            const dot = document.createElement('div');
            dot.style.cssText = `
                position: absolute;
                left: ${el.x * thumbScale}px; top: ${el.y * thumbScale}px;
                width: ${el.width * thumbScale}px; height: ${(el.type === 'text' ? 40 : el.height) * thumbScale}px;
                background: ${el.type === 'text' ? (el.color || '#AA820A') : '#CCCCCC'};
                border-radius: 1px; opacity: 0.6; pointer-events: none;
            `;
            card.appendChild(dot);
        });

        const dragHandle = document.createElement('div');
        dragHandle.className = 'storyboard-drag-handle';
        dragHandle.style.cssText = 'position:absolute;top:6px;left:6px;width:22px;height:22px;border-radius:8px;background:rgba(255,255,255,0.92);border:1px solid #E8E1E3;display:flex;align-items:center;justify-content:center;color:#8B7B80;z-index:20;';
        dragHandle.innerHTML = '<i data-lucide="grip-vertical" class="w-3 h-3"></i>';
        card.appendChild(dragHandle);

        const actions = document.createElement('div');
        actions.className = 'storyboard-action-stack';
        actions.style.cssText = 'position:absolute;top:6px;right:6px;display:flex;flex-direction:column;gap:4px;z-index:20;';
        actions.innerHTML = `
            <button onclick="event.stopPropagation(); duplicatePageByIdx(${idx})" style="width:22px;height:22px;border-radius:8px;background:rgba(255,255,255,0.96);border:1px solid #E8E1E3;display:flex;align-items:center;justify-content:center;color:#181114;cursor:pointer;" title="Duplicate"><i data-lucide="copy" class="w-3 h-3"></i></button>
            ${editorState.template.pages.length > 1 ? `<button onclick="event.stopPropagation(); deletePageByIdx(${idx})" style="width:22px;height:22px;border-radius:8px;background:rgba(255,255,255,0.96);border:1px solid #E8E1E3;display:flex;align-items:center;justify-content:center;color:#EF4444;cursor:pointer;" title="Delete"><i data-lucide="trash-2" class="w-3 h-3"></i></button>` : ''}
        `;
        card.appendChild(actions);

        const badge = document.createElement('div');
        badge.style.cssText = 'position:absolute;right:6px;bottom:6px;z-index:10;background:#fff;border:1px solid #E5E7EB;border-radius:7px;padding:2px 6px;font-size:8px;font-weight:900;color:#1A1516;font-family:monospace;box-shadow:0 2px 6px rgba(24,17,20,0.08);';
        badge.textContent = idx + 1;
        card.appendChild(badge);

        card.onclick = () => selectPage(idx);
        card.addEventListener('dragstart', (e) => { editorState.dragStartPageIndex = idx; e.dataTransfer.effectAllowed = 'move'; });
        card.addEventListener('dragover', (e) => { e.preventDefault(); });
        card.addEventListener('drop', (e) => {
            e.preventDefault();
            const from = editorState.dragStartPageIndex;
            if (from === null || from === idx) return;
            pushHistory();
            const pages = editorState.template.pages;
            const dragged = pages[from];
            pages.splice(from, 1);
            pages.splice(idx, 0, dragged);
            editorState.dragStartPageIndex = null;
            selectPage(idx);
            triggerAutosave();
        });

        storyboard.appendChild(card);
    });

    // Add page button
    const addBtn = document.createElement('button');
    addBtn.type = 'button';
    addBtn.onclick = addPage;
    addBtn.className = 'storyboard-add-card';
    addBtn.innerHTML = `<span style="width:34px;height:34px;border-radius:999px;background:#fff;border:1px solid #E8E1E3;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(24,17,20,0.08);"><i data-lucide="plus" class="w-4 h-4"></i></span><span style="font-size:8px;font-weight:900;text-transform:uppercase;letter-spacing:0.08em;">Add</span>`;
    storyboard.appendChild(addBtn);

    lucide.createIcons({ nodeList: [storyboard] });
}

// Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â
// TEXT ADDITION
// Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â
async function addPresetText(type) {
    let textVal = '', fontFamily = 'Rasa', fontSize = 32, fontWeight = 'normal', color = '#4A2E35', yPos = 500;
    const tpl = editorState.template;

    if (type === 'heading') {
        textVal = 'MANGAL PARINAY';
        fontFamily = tpl?.fonts?.[0] || 'KAP011';
        fontSize = 54; fontWeight = '700'; color = '#D4AF37'; yPos = 350;
    } else if (type === 'subheading') {
        textVal = 'Save The Date';
        fontFamily = tpl?.fonts?.[1] || 'Hind Vadodara';
        fontSize = 32; fontWeight = '600'; color = '#B86B77'; yPos = 500;
    } else {
        textVal = 'We cordially invite you to celebrate the wedding ceremony of our children. Please join us for dinner at 8 PM onwards.';
        fontSize = 28; yPos = 700;
    }

    const translations = await translateToAll(textVal, 'English');
    addElement({ type: 'text', x: 100, y: yPos, width: 880, height: type === 'body' ? 200 : 100, fontFamily, fontSize, fontWeight, color, alignment: 'center', lineHeight: 1.2, letterSpacing: 0, text: textVal, translations });
}

async function addPresetFromTemplate(preset) {
    const translations = await translateToAll(preset.text, 'English');
    addElement({
        type: 'text', x: 100, y: 450,
        width: 880, height: preset.text.includes('\n') ? 220 : 120,
        fontFamily: preset.fontFamily, fontSize: preset.fontSize,
        fontWeight: preset.fontWeight || 'normal', color: preset.color,
        alignment: 'center', lineHeight: 1.2,
        letterSpacing: preset.letterSpacing || 0,
        text: preset.text, translations
    });
}

function addDefaultCustomTextBox() {
    addElement({
        type: 'text', x: 100, y: 600, width: 880, height: 150,
        fontFamily: 'Rasa', fontSize: 32, fontWeight: 'normal', color: '#4A2E35',
        alignment: 'center', lineHeight: 1.2, letterSpacing: 0,
        text: 'Type your own custom wedding invitation text here...',
        translations: { English: 'Type your own custom wedding invitation text here...' }
    });
}

async function addCustomText() {
    const text = document.getElementById('custom-text-input').value.trim();
    if (!text) return;
    const translations = await translateToAll(text, editorState.selectedLanguage || 'English');
    addElement({
        type: 'text', x: 100, y: 600, width: 880, height: 150,
        fontFamily: 'Rasa', fontSize: 32, fontWeight: 'normal', color: '#4A2E35',
        alignment: 'center', lineHeight: 1.2, letterSpacing: 0,
        text: translations['English'] || text, translations
    });
    document.getElementById('custom-text-input').value = '';
}

// -----------------------------------------------------------------------------------------------------------------------
// STICKERS & PHOTOS
// -----------------------------------------------------------------------------------------------------------------------
function renderStickersGrid() {
    const grid = document.getElementById('stickers-grid');
    grid.innerHTML = '';
    stickers.forEach(st => {
        const btn = document.createElement('button');
        btn.className = 'aspect-square p-4 bg-[rgba(255,240,242,0.2)] hover:bg-[rgba(255,240,242,0.4)] border border-[rgba(255,202,210,0.3)] hover:border-[#FF3E5C] rounded-2xl flex items-center justify-center transition-all group relative';
        btn.onclick = () => {
            addElement({ type: 'sticker', x: 440, y: 200, width: 200, height: 200, imagePath: st });
        };
        const img = document.createElement('img');
        img.src = getImageUrl(st);
        img.alt = 'Sticker';
        img.className = 'w-16 h-16 object-contain group-hover:scale-110 transition-transform duration-300';
        img.onerror = () => { img.src = "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='100' height='100'><rect width='100' height='100' fill='%23FFD1D7'/><text x='50' y='55' font-size='24' text-anchor='middle'>🌺</text></svg>"; };
        btn.appendChild(img);
        grid.appendChild(btn);
    });
}

function renderPremiumPresets() {
    const container = document.getElementById('premium-presets');
    container.innerHTML = '';
    PRESETS.forEach((p, idx) => {
        const btn = document.createElement('button');
        btn.className = 'premium-preset-card group';
        btn.onclick = () => addPresetFromTemplate(p);
        btn.innerHTML = `
            <span style="display:block;font-size:10px;font-weight:900;color:#FF4D6D;text-transform:uppercase;letter-spacing:0.08em;">${escapeHtml(p.name)}</span>
            <span style="display:block;font-size:12px;color:#181114;font-weight:700;margin-top:4px;line-height:1.35;white-space:pre-wrap;">${escapeHtml(p.text)}</span>
            <span class="preset-label">${escapeHtml(p.description)}</span>
        `;
        container.appendChild(btn);
    });
}

async function handlePhotoUpload(e) {
    const file = e.target.files[0];
    if (!file) return;
    const label = document.getElementById('photo-upload-label');
    label.textContent = 'Uploading...';

    const tpl = editorState.template;
    const catSlug = tpl?.categoryId || 'wedding';
    const tplSlug = tpl?.slug || 'template';
    const formData = new FormData();
    formData.append('file', file);

    try {
        const res = await fetch(`/api/uploads/single?type=template&categorySlug=${catSlug}&templateSlug=${tplSlug}`, {
            method: 'POST', body: formData
        });
        const data = await res.json();
        if (data.success) {
            addElement({ type: 'image', x: 340, y: 400, width: 400, height: 400, imagePath: data.filePath });
            showUploadedPhoto(data.filePath);
        } else {
            showToast(data.error || 'Upload failed', 'error');
        }
    } catch (err) {
        showToast('Photo upload failed.', 'error');
    } finally {
        label.textContent = 'Upload Image Ornament';
    }
}

function showUploadedPhoto(path) {
    const container = document.getElementById('uploaded-photos');
    container.classList.remove('hidden');
    const btn = document.createElement('button');
    btn.className = 'aspect-square bg-gray-50 border border-[rgba(255,202,210,0.3)] hover:border-[#FF3E5C] rounded-xl overflow-hidden shadow-sm flex items-center justify-center p-1 transition-all group';
    btn.onclick = () => addElement({ type: 'image', x: 340, y: 400, width: 400, height: 400, imagePath: path });
    const img = document.createElement('img');
    img.src = getImageUrl(path);
    img.alt = 'Uploaded';
    img.className = 'w-full h-full object-cover rounded-lg group-hover:scale-105 transition-transform';
    btn.appendChild(img);
    container.insertBefore(btn, container.firstChild);
}

async function handleBgUpload(e) {
    const file = e.target.files[0];
    if (!file) return;
    const tpl = editorState.template;
    const formData = new FormData();
    formData.append('file', file);

    try {
        const res = await fetch(`/api/uploads/single?type=template&categorySlug=${tpl?.categoryId || 'wedding'}&templateSlug=${tpl?.slug || 'template'}`, {
            method: 'POST', body: formData
        });
        const data = await res.json();
        if (data.success) {
            const page = getCurrentPage();
            if (page) {
                page.backgroundImage = data.filePath;
                renderCurrentPage();
                renderStoryboard();
                triggerAutosave();
            }
        } else {
            showToast(data.error || 'Upload failed', 'error');
        }
    } catch (err) {
        showToast('Background upload failed.', 'error');
    }
}

// Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â
// TRANSLATION
// Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â
async function translateToAll(text, sourceLang) {
    const langs = editorState.systemLanguages.includes('English')
        ? editorState.systemLanguages
        : ['English', ...editorState.systemLanguages];

    const translations = {};
    translations[sourceLang] = text;
    if (sourceLang !== 'English') translations['English'] = text;

    await Promise.all(langs.filter(l => l !== sourceLang && l !== 'English').map(async lang => {
        try {
            const res = await fetch('/api/translate', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ text: translations['English'] || text, targetLang: lang, sourceLang: 'English' })
            });
            if (res.ok) {
                const data = await res.json();
                translations[lang] = data.translatedText || data.translation || text;
            } else {
                translations[lang] = text;
            }
        } catch (err) {
            translations[lang] = text;
        }
    }));

    return translations;
}

async function autoTranslateSelected() {
    const page = getCurrentPage();
    const id = editorState.selectedElementId;
    if (!page || !id) return;
    const elem = page.elements.find(el => el.id === id);
    if (!elem || elem.type !== 'text') return;

    const lang = editorState.selectedLanguage;
    const text = (elem.translations && elem.translations[lang]) || elem.text || '';
    if (!text.trim()) return;

    document.getElementById('auto-translate-btn').innerHTML = '<i data-lucide="languages" class="w-3 h-3 animate-spin"></i> Translating...';
    lucide.createIcons({ nodeList: [document.getElementById('auto-translate-btn')] });

    try {
        const translations = await translateToAll(text, lang);
        updateElement(id, { text: translations['English'] || text, translations });
    } catch (err) {
        showToast('Auto-translate failed.', 'error');
    } finally {
        document.getElementById('auto-translate-btn').innerHTML = '<i data-lucide="languages" class="w-3 h-3"></i> Auto-Translate';
        lucide.createIcons({ nodeList: [document.getElementById('auto-translate-btn')] });
    }
}

async function handleLanguageChange(targetLang) {
    editorState.selectedLanguage = targetLang;
    document.getElementById('doc-lang-label').textContent = targetLang;

    if (!editorState.template) return;
    if (targetLang === 'English') {
        renderCurrentPage();
        updateRightPanel();
        return;
    }

    // Check if any elements need translation
    const page = getCurrentPage();
    if (!page) return;

    const needsTranslation = (elem) => {
        if (elem.type !== 'text') return false;
        const current = elem.translations && elem.translations[targetLang];
        const baseText = elem.text || '';
        const englishText = (elem.translations && elem.translations['English']) || baseText;
        if (!current || current.trim() === '') return true;
        if (current === englishText) return true;
        if (targetLang !== 'Gujarati' && /[\u0A80-\u0AFF]/.test(current)) return true;
        return false;
    };

    let hasUntranslated = false;
    for (const p of editorState.template.pages) {
        if ((p.elements || []).some(needsTranslation)) {
            hasUntranslated = true; break;
        }
    }

    if (hasUntranslated) {
        document.getElementById('translate-toast').style.display = 'flex';
        document.getElementById('translate-lang').textContent = targetLang;

        try {
            for (const p of editorState.template.pages) {
                await Promise.all((p.elements || []).map(async el => {
                    if (!needsTranslation(el)) return;
                    const sourceText = (el.translations && el.translations['English']) || el.text || '';
                    if (!sourceText.trim()) return;
                    try {
                        const res = await fetch('/api/translate', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ text: sourceText, targetLang, sourceLang: 'English' })
                        });
                        if (res.ok) {
                            const data = await res.json();
                            el.translations = { ...(el.translations || {}), [targetLang]: data.translatedText || data.translation || sourceText };
                        }
                    } catch (err) { console.error(err); }
                }));
            }
        } finally {
            document.getElementById('translate-toast').style.display = 'none';
        }
    }

    renderCurrentPage();
    updateRightPanel();
}

// Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â
// SAVE & AUTOSAVE
// Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â
function triggerAutosave() {
    clearTimeout(editorState.autosaveTimer);
    setAutosaveStatus('saving');
    editorState.autosaveTimer = setTimeout(async () => {
        await doSave();
    }, 2000);
}

function setAutosaveStatus(status) {
    editorState.autosaveStatus = status;
    const badge = document.getElementById('autosave-badge');
    switch (status) {
        case 'saving':
            badge.innerHTML = '<i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-blue-400 animate-spin"></i><span class="hidden lg:inline text-blue-300">Auto-Saving...</span>';
            badge.className = 'flex items-center gap-1.5 px-2 py-1 sm:px-3 sm:py-1 bg-blue-950 border border-blue-700 text-blue-300 text-xs font-bold rounded-lg uppercase shadow-sm';
            break;
        case 'saved':
            badge.innerHTML = '<i data-lucide="check" class="w-3.5 h-3.5 text-green-400"></i><span class="hidden lg:inline text-green-300">Saved</span>';
            badge.className = 'flex items-center gap-1.5 px-2 py-1 sm:px-3 sm:py-1 bg-green-950 border border-green-700 text-green-300 text-xs font-bold rounded-lg uppercase shadow-sm';
            setTimeout(() => setAutosaveStatus('idle'), 2000);
            break;
        case 'error':
            badge.innerHTML = '<span class="font-bold text-red-400">Ã¢Å“â€¢</span><span class="hidden lg:inline ml-0.5 text-red-300">Save Failed</span>';
            badge.className = 'flex items-center gap-1.5 px-2 py-1 sm:px-3 sm:py-1 bg-red-950 border border-red-700 text-red-300 text-xs font-bold rounded-lg uppercase shadow-sm';
            break;
        default:
            badge.innerHTML = '<span class="text-[#FFF4E6] font-mono text-[10px]">Ã¢Å¡Â¡</span><span class="hidden lg:inline">Auto-Save Active</span>';
            badge.className = 'flex items-center gap-1.5 px-2 py-1 sm:px-3 sm:py-1 bg-[#231A1C] border border-white/5 text-gray-300 text-xs font-bold rounded-lg uppercase shadow-sm';
    }
    lucide.createIcons({ nodeList: [badge] });
}

async function doSave() {
    if (!editorState.template) return;
    try {
        const userId = window.CurrentUser ? window.CurrentUser.id : 'admin_super';
        const res = await fetch(`/api/templates/${editorState.template.id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'x-user-id': userId },
            body: JSON.stringify(editorState.template)
        });
        if (res.ok) {
            setAutosaveStatus('saved');
        } else {
            setAutosaveStatus('error');
        }
    } catch (err) {
        setAutosaveStatus('error');
    }
}

async function manualSave() {
    const btn = document.getElementById('save-btn');
    btn.disabled = true;
    btn.innerHTML = '<i data-lucide="refresh-cw" class="w-4 h-4 animate-spin"></i><span class="hidden md:inline">Syncing...</span>';
    lucide.createIcons({ nodeList: [btn] });

    setAutosaveStatus('saving');
    await doSave();
    showToast('Template draft saved successfully!', 'success');

    btn.disabled = false;
    btn.innerHTML = '<i data-lucide="save" class="w-4 h-4"></i><span class="hidden md:inline">Save Draft</span>';
    lucide.createIcons({ nodeList: [btn] });
}

// Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â
// UNDO / REDO
// Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â
function pushHistory() {
    if (!editorState.template) return;
    editorState.undoStack.push(JSON.stringify(editorState.template));
    if (editorState.undoStack.length > 50) editorState.undoStack.shift();
    editorState.redoStack = [];
    updateUndoRedoButtons();
}

function editorUndo() {
    if (editorState.undoStack.length === 0) return;
    editorState.redoStack.push(JSON.stringify(editorState.template));
    editorState.template = JSON.parse(editorState.undoStack.pop());
    renderCanvas();
    renderStoryboard();
    renderPagesList();
    updateRightPanel();
    updateUndoRedoButtons();
}

function editorRedo() {
    if (editorState.redoStack.length === 0) return;
    editorState.undoStack.push(JSON.stringify(editorState.template));
    editorState.template = JSON.parse(editorState.redoStack.pop());
    renderCanvas();
    renderStoryboard();
    renderPagesList();
    updateRightPanel();
    updateUndoRedoButtons();
}

function updateUndoRedoButtons() {
    document.getElementById('undo-btn').disabled = editorState.undoStack.length === 0;
    document.getElementById('redo-btn').disabled = editorState.redoStack.length === 0;
}

// Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â
// LEFT TAB SWITCHING
// Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â
function setLeftTab(tab) {
    const tabs = ['text', 'stickers', 'photos', 'pages', 'info'];
    tabs.forEach(t => {
        const panel = document.getElementById(`panel-${t}`);
        const btn = document.getElementById(`tab-${t}`);
        if (t === tab) {
            panel.style.display = 'flex';
            btn.classList.add('active-tab');
        } else {
            panel.style.display = 'none';
            btn.classList.remove('active-tab');
        }
    });
}

// Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â
// KEYBOARD SHORTCUTS
// Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â
function handleKeyDown(e) {
    const activeEl = document.activeElement;
    const isEditing = ['INPUT', 'TEXTAREA', 'SELECT'].includes(activeEl.tagName) || activeEl.hasAttribute('contenteditable');
    if (isEditing) return;

    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'z') {
        e.preventDefault();
        editorUndo();
    }
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'y') {
        e.preventDefault();
        editorRedo();
    }
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'd') {
        e.preventDefault();
        if (editorState.selectedElementId) duplicateSelected();
    }
    if (['Delete', 'Backspace'].includes(e.key) && editorState.selectedElementId) {
        e.preventDefault();
        deleteSelected();
    }
    if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.key) && editorState.selectedElementId) {
        const page = getCurrentPage();
        const elem = page?.elements.find(el => el.id === editorState.selectedElementId);
        if (elem && !elem.isLocked) {
            e.preventDefault();
            const step = e.shiftKey ? 10 : 1;
            let dx = 0, dy = 0;
            if (e.key === 'ArrowUp') dy = -step;
            if (e.key === 'ArrowDown') dy = step;
            if (e.key === 'ArrowLeft') dx = -step;
            if (e.key === 'ArrowRight') dx = step;
            updateElement(elem.id, { x: elem.x + dx, y: elem.y + dy });
        }
    }
}

// Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â
// PREVIEW MODAL
// Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â
let previewPageIndex = 0;

function openPreview() {
    previewPageIndex = editorState.selectedPageIndex;
    renderPreviewPage();
    document.getElementById('preview-modal').classList.remove('hidden');
    document.getElementById('preview-modal').classList.add('flex');
}

function closePreview() {
    document.getElementById('preview-modal').classList.add('hidden');
    document.getElementById('preview-modal').classList.remove('flex');
}

function previewPrev() {
    if (!editorState.template) return;
    previewPageIndex = Math.max(0, previewPageIndex - 1);
    renderPreviewPage();
}

function previewNext() {
    if (!editorState.template) return;
    previewPageIndex = Math.min(editorState.template.pages.length - 1, previewPageIndex + 1);
    renderPreviewPage();
}

function renderPreviewPage() {
    if (!editorState.template) return;
    const page = editorState.template.pages[previewPageIndex];
    if (!page) return;

    const PREVIEW_W = 280, PREVIEW_H = 497;
    const SCALE = PREVIEW_W / LOGICAL_W;

    const canvas = document.getElementById('preview-canvas');
    canvas.style.background = page.backgroundImage ? 'transparent' : '#1a1214';
    canvas.innerHTML = '';

    if (page.backgroundImage) {
        const img = document.createElement('img');
        img.src = getImageUrl(page.backgroundImage);
        img.style.cssText = 'position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover;';
        canvas.appendChild(img);
    }

    (page.elements || []).forEach(el => {
        const displayText = (el.translations && el.translations[editorState.selectedLanguage]) || el.text || '';
        const div = document.createElement('div');
        div.style.cssText = `
            position: absolute;
            left: ${el.x * SCALE}px; top: ${el.y * SCALE}px;
            width: ${el.width * SCALE}px;
            transform: rotate(${el.rotation || 0}deg);
            opacity: ${el.opacity !== undefined ? el.opacity : 1};
            z-index: ${el.zIndex || 1};
            overflow: visible;
        `;
        if (el.type === 'text') {
            const ff = getStyleVal(el, 'fontFamily', 'Rasa');
            div.style.fontFamily = ff;
            div.style.fontSize = `${(getStyleVal(el, 'fontSize', 36)) * SCALE}px`;
            div.style.color = getStyleVal(el, 'color', '#4A2E35');
            div.style.lineHeight = getStyleVal(el, 'lineHeight', 1.2);
            div.style.textAlign = getStyleVal(el, 'alignment', 'center');
            div.style.fontWeight = getStyleVal(el, 'fontWeight', 'normal');
            div.style.letterSpacing = `${(getStyleVal(el, 'letterSpacing', 0)) * SCALE}px`;
            div.style.whiteSpace = 'pre-wrap';
            div.style.wordBreak = 'break-word';
            div.textContent = displayText;
        } else {
            const img = document.createElement('img');
            img.src = getImageUrl(el.imagePath);
            img.style.cssText = `width: ${el.width * SCALE}px; height: ${el.height * SCALE}px; object-fit: contain; display: block;`;
            div.appendChild(img);
        }
        canvas.appendChild(div);
    });

    const total = editorState.template.pages.length;
    document.getElementById('preview-counter').textContent = `${previewPageIndex + 1} / ${total}`;
    document.getElementById('preview-page-name').textContent = page.name || `Page ${previewPageIndex + 1}`;
}

// Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â
// NAVIGATION
// Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â
function goBack() {
    window.location.href = '/admin/templates';
}

// Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â
// TOAST NOTIFICATIONS
// Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â
function showToast(msg, type = 'info') {
    const container = document.getElementById('editor-toast');
    const item = document.createElement('div');
    item.className = `editor-toast-item toast-${type}`;
    item.textContent = msg;
    container.appendChild(item);
    setTimeout(() => {
        item.style.opacity = '0';
        setTimeout(() => item.remove(), 300);
    }, 3500);
}

// Override global Toast for editor
window.Toast = { show: showToast };

// Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â
// UTILS
// Ã¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢ÂÃ¢â€¢Â
function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
}
</script>
</body>
</html>
