<!DOCTYPE html>
<html lang="en">
<head>
    @include('portfolio.partials.head')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #ffffff; color: #1a1a2e; line-height: 1.7; }
        a { color: #41B883; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .container { max-width: 720px; margin: 0 auto; padding: 3rem 1.5rem; }

        /* Header */
        .header { text-align: center; padding: 4rem 0 3rem; border-bottom: 1px solid #e5e7eb; }
        .avatar { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin-bottom: 1.5rem; border: 3px solid #f3f4f6; }
        .avatar-placeholder { width: 100px; height: 100px; border-radius: 50%; background: #f3f4f6; display: inline-flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 700; color: #9ca3af; margin-bottom: 1.5rem; }
        .name { font-size: 2rem; font-weight: 700; letter-spacing: -0.02em; }
        .tagline { font-size: 1.1rem; color: #6b7280; margin-top: 0.25rem; }
        .location { font-size: 0.9rem; color: #9ca3af; margin-top: 0.5rem; }
        .hire-badge { display: inline-block; margin-top: 0.75rem; padding: 0.25rem 0.75rem; background: #ecfdf5; color: #059669; border-radius: 9999px; font-size: 0.8rem; font-weight: 600; }

        /* Social links */
        .social-links { display: flex; gap: 1rem; justify-content: center; margin-top: 1.25rem; flex-wrap: wrap; }
        .social-links a { color: #6b7280; font-size: 0.85rem; font-weight: 500; padding: 0.35rem 0.75rem; border: 1px solid #e5e7eb; border-radius: 0.375rem; transition: all 0.2s; }
        .social-links a:hover { color: #41B883; border-color: #41B883; text-decoration: none; }

        /* Sections */
        .section { padding: 2.5rem 0; border-bottom: 1px solid #f3f4f6; }
        .section:last-child { border-bottom: none; }
        .section-title { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #9ca3af; margin-bottom: 1.5rem; }

        /* Bio */
        .bio { font-size: 1rem; color: #4b5563; white-space: pre-wrap; }

        /* Skills */
        .skills-grid { display: flex; flex-wrap: wrap; gap: 0.5rem; }
        .skill-tag { padding: 0.35rem 0.85rem; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 0.375rem; font-size: 0.85rem; color: #374151; }
        .skill-tag .proficiency { color: #9ca3af; font-size: 0.75rem; margin-left: 0.25rem; }

        /* Experience & Education */
        .timeline-item { margin-bottom: 1.75rem; }
        .timeline-item:last-child { margin-bottom: 0; }
        .timeline-header { display: flex; justify-content: space-between; align-items: baseline; flex-wrap: wrap; gap: 0.5rem; }
        .timeline-role { font-weight: 600; font-size: 1rem; }
        .timeline-company { color: #6b7280; font-size: 0.9rem; }
        .timeline-dates { font-size: 0.8rem; color: #9ca3af; white-space: nowrap; }
        .timeline-desc { font-size: 0.9rem; color: #6b7280; margin-top: 0.35rem; }

        /* Projects */
        .projects-grid { display: grid; grid-template-columns: 1fr; gap: 1.5rem; }
        .project-card { border: 1px solid #e5e7eb; border-radius: 0.5rem; overflow: hidden; transition: box-shadow 0.2s; }
        .project-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
        .project-img { width: 100%; height: 180px; object-fit: cover; background: #f9fafb; }
        .project-body { padding: 1.25rem; }
        .project-title { font-weight: 600; font-size: 1rem; margin-bottom: 0.35rem; }
        .project-desc { font-size: 0.85rem; color: #6b7280; margin-bottom: 0.75rem; }
        .project-tech { display: flex; flex-wrap: wrap; gap: 0.35rem; margin-bottom: 0.75rem; }
        .project-tech span { font-size: 0.75rem; padding: 0.15rem 0.5rem; background: #f3f4f6; border-radius: 0.25rem; color: #6b7280; }
        .project-links { display: flex; gap: 0.75rem; }
        .project-links a { font-size: 0.85rem; font-weight: 500; }

        /* Testimonials */
        .testimonial { padding: 1.25rem; background: #f9fafb; border-radius: 0.5rem; margin-bottom: 1rem; }
        .testimonial:last-child { margin-bottom: 0; }
        .testimonial-content { font-size: 0.95rem; color: #4b5563; font-style: italic; margin-bottom: 0.75rem; }
        .testimonial-author { font-size: 0.85rem; font-weight: 600; }
        .testimonial-role { font-size: 0.8rem; color: #9ca3af; }

        /* Custom sections */
        .custom-content { font-size: 0.95rem; color: #4b5563; white-space: pre-wrap; }

        /* Footer */
        .footer { text-align: center; padding: 2rem 0; font-size: 0.8rem; color: #d1d5db; }
        .footer a { color: #d1d5db; }

        /* Resume */
        .resume-link { display: inline-block; margin-top: 1rem; padding: 0.5rem 1.25rem; border: 1px solid #e5e7eb; border-radius: 0.375rem; font-size: 0.85rem; font-weight: 500; color: #374151; transition: all 0.2s; }
        .resume-link:hover { border-color: #41B883; color: #41B883; text-decoration: none; }

        @media (min-width: 640px) {
            .projects-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>
    @include('portfolio.partials.grace-banner')

    <div class="container">
        {{-- Header --}}
        <header class="header">
            @if($portfolio->photo_path)
                <img src="{{ asset($portfolio->photo_path) }}" alt="{{ $portfolio->title ?? $portfolio->user->name }}" class="avatar">
            @elseif($portfolio->user->profile_photo)
                <img src="{{ $portfolio->user->profile_photo }}" alt="{{ $portfolio->user->name }}" class="avatar">
            @else
                <div class="avatar-placeholder">{{ strtoupper(substr($portfolio->user->name, 0, 1)) }}</div>
            @endif

            <h1 class="name">{{ $portfolio->title ?? $portfolio->user->name }}</h1>
            @if($portfolio->tagline)
                <p class="tagline">{{ $portfolio->tagline }}</p>
            @endif
            @if($portfolio->location_city || $portfolio->location_country)
                <p class="location">📍 {{ collect([$portfolio->location_city, $portfolio->location_country])->filter()->implode(', ') }}</p>
            @endif
            @if($portfolio->available_for_hire)
                <span class="hire-badge">✓ Available for hire</span>
            @endif

            @if($portfolio->socialLinks->count())
                <div class="social-links">
                    @foreach($portfolio->socialLinks as $link)
                        <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer">{{ $link->platform }}</a>
                    @endforeach
                </div>
            @endif

            @if($portfolio->resume_path)
                <a href="{{ asset($portfolio->resume_path) }}" target="_blank" class="resume-link">📄 Download Resume</a>
            @endif
        </header>

        {{-- Bio --}}
        @if($portfolio->bio)
        <section class="section">
            <h2 class="section-title">About</h2>
            <div class="bio">{{ $portfolio->bio }}</div>
        </section>
        @endif

        {{-- Skills --}}
        @if($portfolio->skills->count())
        <section class="section">
            <h2 class="section-title">Skills</h2>
            <div class="skills-grid">
                @foreach($portfolio->skills as $skill)
                    <span class="skill-tag">
                        {{ $skill->name }}
                        @if($skill->proficiency)
                            <span class="proficiency">· {{ $skill->proficiency->value }}</span>
                        @endif
                    </span>
                @endforeach
            </div>
        </section>
        @endif

        {{-- Experience --}}
        @if($portfolio->experiences->count())
        <section class="section">
            <h2 class="section-title">Experience</h2>
            @foreach($portfolio->experiences as $exp)
                <div class="timeline-item">
                    <div class="timeline-header">
                        <div>
                            <div class="timeline-role">{{ $exp->role }}</div>
                            <div class="timeline-company">{{ $exp->company }}</div>
                        </div>
                        <div class="timeline-dates">
                            {{ $exp->start_date->format('M Y') }} — {{ $exp->is_current ? 'Present' : $exp->end_date?->format('M Y') }}
                        </div>
                    </div>
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
            <h2 class="section-title">Education</h2>
            @foreach($portfolio->educations as $edu)
                <div class="timeline-item">
                    <div class="timeline-header">
                        <div>
                            <div class="timeline-role">{{ collect([$edu->degree, $edu->field])->filter()->implode(' in ') ?: $edu->institution }}</div>
                            <div class="timeline-company">{{ $edu->degree ? $edu->institution : '' }}</div>
                        </div>
                        <div class="timeline-dates">
                            {{ $edu->start_year }}{{ $edu->end_year ? ' — ' . $edu->end_year : '' }}
                        </div>
                    </div>
                </div>
            @endforeach
        </section>
        @endif

        {{-- Projects --}}
        @if($portfolio->projects->count())
        <section class="section">
            <h2 class="section-title">Projects</h2>
            <div class="projects-grid">
                @foreach($portfolio->projects as $project)
                    <div class="project-card">
                        @if($project->image_path)
                            <img src="{{ asset($project->image_path) }}" alt="{{ $project->title }}" class="project-img">
                        @endif
                        <div class="project-body">
                            <div class="project-title">{{ $project->title }}</div>
                            @if($project->description)
                                <p class="project-desc">{{ Str::limit($project->description, 120) }}</p>
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
                                    <a href="{{ $project->live_url }}" target="_blank">Live ↗</a>
                                @endif
                                @if($project->source_url)
                                    <a href="{{ $project->source_url }}" target="_blank">Source ↗</a>
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
            <h2 class="section-title">Testimonials</h2>
            @foreach($portfolio->testimonials as $testimonial)
                <div class="testimonial">
                    <p class="testimonial-content">"{{ $testimonial->content }}"</p>
                    <div class="testimonial-author">{{ $testimonial->author_name }}</div>
                    @if($testimonial->author_role || $testimonial->author_company)
                        <div class="testimonial-role">{{ collect([$testimonial->author_role, $testimonial->author_company])->filter()->implode(' at ') }}</div>
                    @endif
                </div>
            @endforeach
        </section>
        @endif

        {{-- Custom Sections --}}
        @foreach($portfolio->customSections as $section)
        <section class="section">
            <h2 class="section-title">{{ $section->title }}</h2>
            <div class="custom-content">{{ $section->content }}</div>
        </section>
        @endforeach

        {{-- Footer --}}
        <footer class="footer">
            Built with <a href="https://{{ config('portfolio.domain') }}" target="_blank">LaraVue</a>
        </footer>
    </div>
</body>
</html>
