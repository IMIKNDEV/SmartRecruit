{{-- SmartRecruit inline SVG icon set. Usage: <x-icon name="pipeline" /> --}}
@props(['name' => '', 'size' => 20, 'class' => ''])
<svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}" aria-hidden="true">
@switch($name)
@case('dashboard')
  <rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/>
  <rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/>
@break
@case('briefcase')
  <rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M3 12h18"/>
@break
@case('users')
  <path d="M17 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
@break
@case('calendar')
  <rect x="3" y="4" width="18" height="17" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
@break
@case('template')
  <rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/>
@break
@case('filter')
  <path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/>
@break
@case('chat')
  <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
@break
@case('user')
  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
@break
@case('pipeline')
  <rect x="3" y="4" width="4.5" height="16" rx="1.5"/><rect x="9.75" y="4" width="4.5" height="16" rx="1.5"/><rect x="16.5" y="4" width="4.5" height="16" rx="1.5"/>
@break
@case('target')
  <circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1"/>
@break
@case('shield')
  <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
@break
@case('note')
  <path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>
@break
@case('search')
  <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
@break
@case('check')
  <path d="M20 6 9 17l-5-5"/>
@break
@case('x')
  <path d="M18 6 6 18M6 6l12 12"/>
@break
@case('chevron-down')
  <path d="m6 9 6 6 6-6"/>
@break
@case('chevron-right')
  <path d="m9 18 6-6-6-6"/>
@break
@case('plus')
  <path d="M12 5v14M5 12h14"/>
@break
@case('edit')
  <path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>
@break
@case('trash')
  <path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
@break
@case('export')
  <path d="M12 15V3"/><path d="m7 8 5-5 5 5"/><path d="M5 21h14"/>
@break
@case('sparkles')
  <path d="M12 3l1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9Z"/><path d="M19 15l.9 2.1L22 18l-2.1.9L19 21l-.9-2.1L16 18l2.1-.9Z"/>
@break
@case('star')
  <path d="m12 2 3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
@break
@case('clock')
  <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>
@break
@case('logout')
  <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5M21 12H9"/>
@break
@case('upload')
  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m17 8-5-5-5 5"/><path d="M12 3v12"/>
@break
@case('mail')
  <rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/>
@break
@case('arrow-right')
  <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
@break
@case('arrow-left')
  <path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>
@break
@case('info')
  <circle cx="12" cy="12" r="9"/><path d="M12 16v-4M12 8h.01"/>
@break
@case('bookmark')
  <path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
@break
@case('copy')
  <rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
@break
@case('download')
  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/><path d="M12 15V3"/>
@break
@case('eye')
  <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/>
@break
@case('send')
  <path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/>
@break
@case('video')
  <rect x="2" y="6" width="14" height="12" rx="2"/><path d="m16 10 6-3v10l-6-3"/>
@break
@case('file')
  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/>
@break
@case('refresh')
  <path d="M21 12a9 9 0 1 1-2.64-6.36L21 8"/><path d="M21 3v5h-5"/>
@break
@case('link')
  <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
@break
@default
  <circle cx="12" cy="12" r="9"/><path d="M12 16v-4M12 8h.01"/>
@endswitch
</svg>
