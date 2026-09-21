# DESIGN SYSTEM & GUIDANCE: Senat Akademik POLINES
(Compliant with Impeccable Design Standards & Anti-AI Slop Rules)

## 1. Brand Tokens & Color Palette
- **Primary Navy:** `#003366` (Identitas Polines, wibawa institusional)
- **Secondary Blue:** `#0A66C2` (Vibrant tech blue, link aktif, highlight)
- **Accent Orange:** `#F37021` (Aksen resmi Polines, call-to-action, status alert)
- **Background Base:** `#F8FAFC` (Slate-50, bersih, profesional)
- **Surface Card:** `#FFFFFF` (Elevated clean white)
- **Border Subtle:** `#E2E8F0` (`border-slate-200/80` 1px crisp border)
- **Text Primary:** `#0F172A` (Slate-900)
- **Text Secondary:** `#475569` (Slate-600)
- **Text Muted:** `#94A3B8` (Slate-400)

## 2. Typography Rules
- **Font Family:** 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif
- **Headings:** Bold / Extrabold, letter-spacing `tracking-tight` (-0.025em)
- **Numeric Precision:** ALWAYS use `font-variant-numeric: tabular-nums;` (Tailwind class: `tabular-nums`) for dates, timestamps, session codes, vote percentages, and counters to eliminate horizontal jitter.

## 3. Strict Anti-AI Slop Rules
1. **NO Generic Tailwind Gradients:** Never use `from-indigo-500 to-purple-600` or arbitrary neon colors. All gradients must be subtle Polines navy variations (`from-[#00284D] to-[#003366]`).
2. **NO Heavy Blurry Drop-Shadows:** Never use `shadow-2xl` or black blur blobs. Use crisp `1px` subtle borders (`border border-slate-200/80`) paired with micro ambient shadow (`shadow-xs` / `shadow-sm`).
3. **NO Card-in-Card Nesting:** Do not put bordered cards inside bordered cards. Use clean whitespace hierarchy and horizontal divider lines (`divide-y divide-slate-100`).
4. **NO Arbitrary Floating Badges:** Badges must carry semantic value (Category: `bg-blue-50 text-blue-700`, Status: `bg-emerald-50 text-emerald-700`, Urgent: `bg-amber-50 text-amber-700`).
5. **NO Bounce/Elastic Animations:** Use calm, swift cubic transitions (150ms-200ms ease-out).

## 4. Component Visual Language
- **Navbar:** Sticky with subtle glassmorphism (`bg-white/90 backdrop-blur-md border-b border-slate-200/80`).
- **Hero Section:** High-density institutional typography, badge pengumuman rapat terdekat, call-to-action bersih.
- **Card Elements:** `rounded-xl border border-slate-200/80 bg-white p-6 hover:border-slate-300 transition-all`.
- **Status Pills:** `rounded-full px-2.5 py-0.5 text-xs font-semibold`.
