<!DOCTYPE html>
<html lang="en">
<head>
    @include('portfolio.partials.head')
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #0a0a0f; color: #c9d1d9; line-height: 1.7; }
        code, .mono { font-family: 'JetBrains Mono', monospace; }
        a { color: #41B883; text-decoration: none; }
        a:hover { text-decoration: underline; }

        .container { max-width: 800px; margin: 0 auto; padding: 2rem 1.5rem; }

        /* Terminal header */
        .terminal-bar { background: #161b22; border: 1px solid #30363d; border-radius: 0.75rem 0.75rem 0 0; padding: 0.75rem 1rem; display: flex; align-items: center; gap: 0.5rem; }
        .terminal-dot { width: 12px; height: 12px; border-radius: 50%; }
        .dot-red { background: #ff5f57; }
        .dot-yellow { background: #febc2e; }
        .dot-green { background: #28c840; }
        .terminal-title { margin-left: 0.75rem; font-size: 0.8rem; color: #8b949e; font-family: 'JetBrains Mono', monospace; }
        .terminal-body { background: #0d1117; border: 1px solid #30363d; border-top: none; border-radius: 0 0 0.75rem 0.75rem; padding: 2rem; }

        /* Header */
        .header { padding: 2rem 0 2.5rem; }
        .prompt { color: #41B883; font-family: 'JetBrains Mono', monospace; font-size: 0.85rem; }
        .name { font-size: 2.5rem; font-weight: 700; color: #f0f6fc; margin-top: 0.5rem; letter-spacing: -0.02em; }
        .tagline { font-size: 1.15rem; color: #8b949e; margin-top: 0.25rem; }
        .tagline .cursor { display: inline-block; width: 2px; height: 1.2em; background: #41B883; margin-left: 2px; animation: blink 1s step-end infinite; vertical-align: text-bottom; }
        @keyframes blink { 50% { opacity: 0; } }

        .meta-row { display: flex; gap: 1.5rem; margin-top: 1rem; flex-wrap: wrap; align-items: center; }
        .meta-item { font-size: 0.85rem; color: #8b949e; }
        .meta-item .icon { margin-right: 0.25rem; }
        .hire-badge { padding: 0.2rem 0.6rem; background: rgba(65, 184, 131, 0.15); color: #41B883; border: 1px solid rgba(65, 184, 131, 0.3); border-radius: 0.25rem; font-size: 0.8rem; font-weight: 600; font-family: 'JetBrains Mono', monospace; }

        /* Avatar */
        .avatar-row { display: flex; align-items: center; gap: 1.5rem; margin-bottom: 1rem; }
        .avatar { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 2px solid #30363d; }
        .avatar-placeholder { width: 80px; height: 80px; border-radius: 50%; background: #161b22; border: 2px solid #30363d; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 700; color: #41B883; font-family: 'JetBrains Mono', monospace; }

        /* Social */
        .social-links { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 1rem; }
        .social-links a { padding: 0.3rem 0.7rem; background: #161b22; border: 1px solid #30363d; border-radius: 0.375rem; font-size: 0.8rem; color: #8b949e; font-family: 'JetBrains Mono', monospace; transition: all 0.2s; }
        .social-links a:hover { border-color: #41B883; color: #41B883; text-decoration: none; }

        /* Sections */
        .section { padding: 2rem 0; border-top: 1px solid #21262d; }
        .section-title { font-family: 'JetBrains Mono', monospace; font-size: 0.8rem; font-weight: 700; color: #41B883; margin-bottom: 1.25rem; }
        .section-title::before { content: '## '; color: #484f58; }

        /* Bio */
        .bio { color: #8b949e; white-space: pre-wrap; }

        /* Skills */
        .skills-grid { display: flex; flex-wrap: wrap; gap: 0.5rem; }
        .skill-tag { padding: 0.3rem 0.7rem; background: #161b22; border: 1px solid #30363d; border-radius: 0.25rem; font-size: 0.8rem; color: #c9d1d9; font-family: 'JetBrains Mono', monospace; }
        .skill-tag .proficiency { color: #484f58; font-size: 0.7rem; }

        /* Timeline */
        .timeline-item { margin-bottom: 1.5rem; padding-left: 1rem; border-left: 2px solid #21262d; }
        .timeline-item:last-child { margin-bottom: 0; }
        .timeline-role { font-weight: 600; color: #f0f6fc; }
        .timeline-company { color: #8b949e; font-size: 0.9rem; }
        .timeline-dates { font-size: 0.8rem; color: #484f58; font-family: 'JetBrains Mono', monospace; margin-top: 0.15rem; }
        .timeline-desc { font-size: 0.9rem; color: #8b949e; margin-top: 0.35rem; }

        /* Projects */
        .projects-grid { display: grid; grid-template-columns: 1fr; gap: 1rem; }
        .project-card { background: #161b22; border: 1px solid #30363d; border-radius: 0.5rem; overflow: hidden; transition: border-color 0.2s; }
        .project-card:hover { border-color: #41B883; }
        .project-img { width: 100%; height: 160px; object-fit: cover; }
        .project-body { padding: 1rem; }
        .project-title { font-weight: 600; color: #f0f6fc; margin-bottom: 0.25rem; }
        .project-desc { font-size: 0.85rem; color: #8b949e; margin-bottom: 0.5rem; }
        .project-tech { display: flex; flex-wrap: wrap; gap: 0.35rem; margin-bottom: 0.5rem; }
        .project-tech span { font-size: 0.7rem; padding: 0.1rem 0.4rem; background: #0d1117; border: 1px solid #30363d; border-radius: 0.2rem; color: #8b949e; font-family: 'JetBrains Mono', monospace; }
        .project-links { display: flex; gap: 0.75rem; }
        .project-links a { font-size: 0.8rem; font-family: 'JetBrains Mono', monospace; }

        /* Testimonials */
        .testimonial { padding: 1rem; background: #161b22; border-left: 3px solid #41B883; border-radius: 0 0.375rem 0.375rem 0; margin-bottom: 1rem; }
        .testimonial:last-child { margin-bottom: 0; }
        .testimonial-content { font-size: 0.9rem; color: #8b949e; font-style: italic; margin-bottom: 0.5rem; }
        .testimonial-content::before { content: '> '; color: #484f58; font-family: 'JetBrains Mono', monospace; font-style: normal; }
        .testimonial-author { font-size: 0.85rem; font-weight: 600; color: #c9d1d9; }
        .testimonial-role { font-size: 0.8rem; color: #484f58; }

        /* Custom sections */
        .custom-content { color: #8b949e; white-space: pre-wrap; }

        /* Resume */
        .resume-link { display: inline-block; margin-top: 1rem; padding: 0.4rem 1rem; background: #161b22; border: 1px solid #30363d; border-radius: 0.375rem; font-size: 0.8rem; font-family: 'JetBrains Mono', monospace; color: #c9d1d9; transition: all 0.2s; }
        .resume-link:hover { border-color: #41B883; color: #41B883; text-decoration: none; }

        /* Footer */
        .footer { text-align: center; padding: 2rem 0; font-size: 0.75rem; color: #484f58; font-family: 'JetBrains Mono', monospace; }
        .footer a { color: #484f58; }

        @media (min-width: 640px) {
            .projects-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>
    @include('portfolio.partials.grace-banner')

    <div class="container">
        {{-- Terminal window --}}
        <div class="terminal-bar">
            <span class="terminal-dot dot-red"></span>
            <span class="terminal-dot dot-yellow"></span>
            <span class="terminal-dot dot-green"></span>
            <span class="terminal-title">{{ $portfolio->subdomain }}@laravue ~ portfolio</span>
        </div>
        <div class="terminal-body">

            {{-- Header --}}
            <header class="header">
                <div class="avatar-row">
                    @if($portfolio->photo_path)
                        <img src="{{ asset($portfolio->photo_path) }}" alt="{{ $portfolio->user->name }}" class="avatar">
                    @elseif($portfolio->user->profile_photo)
                        <img src="{{ $portfolio->user->profile_photo }}" alt="{{ $portfolio->user->name }}" class="avatar">
                    @else
                        <div class="avatar-placeholder">{{ strtoupper(substr($portfolio->user->name, 0, 1)) }}</div>
                    @endif
                    <div>
                        <div class="prompt">~/portfolio $</div>
                        <h1 class="name">{{ $portfolio->title ?? $portfolio->user->name }}</h1>
                        @if($portfolio->tagline)
                            <p class="tagline">{{ $portfolio->tagline }}<span class="cursor"></span></p>
                        @endif
                    </div>
                </div>

                <div class="meta-row">
                    @if($portfolio->location_city || $portfolio->location_country)
                        <span class="meta-item"><span class="icon">📍</span>{{ collect([$portfolio->location_city, $portfolio->location_country])->filter()->implode(', ') }}</span>
                    @endif
                    @if($portfolio->available_for_hire)
                        <span class="hire-badge">open_to_work</span>
                    @endif
                </div>

                @if($portfolio->socialLinks->count())
                    <div class="social-links">
                        @foreach($portfolio->socialLinks as $link)
                            <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer">{{ $link->platform }}</a>
                        @endforeach
                    </div>
                @endif

                @if($portfolio->resume_path)
                    <a href="{{ asset($portfolio->resume_path) }}" target="_blank" class="resume-link">cat resume.pdf ↗</a>
                @endif
            </header>

            {{-- Bio --}}
            @if($portfolio->bio)
            <section class="section">
                <h2 class="section-title">about</h2>
                <div class="bio">{{ $portfolio->bio }}</div>
            </section>
            @endif

            {{-- Skills --}}
            @if($portfolio->skills->count())
            <section class="section">
                <h2 class="section-title">tech_stack</h2>
                <div class="skills-grid">
                    @foreach($portfolio->skills as $skill)
                        <span class="skill-tag">
                            {{ $skill->name }}
                            @if($skill->proficiency)
                                <span class="proficiency"> // {{ $skill->proficiency->value }}</span>
                            @endif
                        </span>
                    @endforeach
                </div>
            </section>
            @endif

            {{-- Experience --}}
            @if($portfolio->experiences->count())
            <section class="section">
                <h2 class="section-title">experience</h2>
                @foreach($portfolio->experiences as $exp)
                    <div class="timeline-item">
                        <div class="timeline-role">{{ $exp->role }}</div>
                        <div class="timeline-company">{{ $exp->company }}</div>
                        <div class="timeline-dates">{{ $exp->start_date->format('Y.m') }} → {{ $exp->is_current ? 'present' : $exp->end_date?->format('Y.m') }}</div>
                        @if($exp->description)
                            <p class="timeline-desc">{{ $exp->description }}</p>
                        @endif
                    </div>
                @endforeach
            </section>
            @endif

            {{-- Education --}}
            @if($portfolio->educations->count())
            <section class="section">
                <h2 class="section-title">education</h2>
                @foreach($portfolio->educations as $edu)
                    <div class="timeline-item">
                        <div class="timeline-role">{{ collect([$edu->degree, $edu->field])->filter()->implode(' in ') ?: $edu->institution }}</div>
                        <div class="timeline-company">{{ $edu->degree ? $edu->institution : '' }}</div>
                        <div class="timeline-dates">{{ $edu->start_year }}{{ $edu->end_year ? ' → ' . $edu->end_year : '' }}</div>
                    </div>
                @endforeach
            </section>
            @endif

            {{-- Projects --}}
            @if($portfolio->projects->count())
            <section class="section">
                <h2 class="section-title">projects</h2>
                <div class="projects-grid">
                    @foreach($portfolio->projects as $project)
                        <div class="project-card">
                            @if($project->image_path)
                                <img src="{{ asset($project->image_path) }}" alt="{{ $project->title }}" class="project-img">
                            @endif
                            <div class="project-body">
                                <div class="project-title">{{ $project->title }}</div>
                                @if($project->description)
                                    <p class="project-desc">{{ Str::limit($project->description, 100) }}</p>
                                @endif
                                @if($project->tech_stack && count($project->tech_stack))
                                    <div class="project-tech">
                                        @foreach($project->tech_stack as $tech)
                                            <span>{{ $tech }}</span>
                                        @endforeach
                                    </div>
                                @endif
                                <div class="project-links">
                                    @if($project->live_url)
                                        <a href="{{ $project->live_url }}" target="_blank">demo ↗</a>
                                    @endif
                                    @if($project->source_url)
                                        <a href="{{ $project->source_url }}" target="_blank">src ↗</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
            @endif

            {{-- Testimonials --}}
            @if($portfolio->testimonials->count())
            <section class="section">
                <h2 class="section-title">testimonials</h2>
                @foreach($portfolio->testimonials as $testimonial)
                    <div class="testimonial">
                        <p class="testimonial-content">{{ $testimonial->content }}</p>
                        <div class="testimonial-author">— {{ $testimonial->author_name }}</div>
                        @if($testimonial->author_role || $testimonial->author_company)
                            <div class="testimonial-role">{{ collect([$testimonial->author_role, $testimonial->author_company])->filter()->implode(' @ ') }}</div>
                        @endif
                    </div>
                @endforeach
            </section>
            @endif

            {{-- Custom Sections --}}
            @foreach($portfolio->customSections as $section)
            <section class="section">
                <h2 class="section-title">{{ Str::snake($section->title) }}</h2>
                <div class="custom-content">{{ $section->content }}</div>
            </section>
            @endforeach

        </div>{{-- end terminal-body --}}

        {{-- Footer --}}
        <footer class="footer">
            built with <a href="https://{{ config('portfolio.domain') }}" target="_blank">laravue</a> // {{ date('Y') }}
        </footer>
    </div>
</body>
</html>
