<?php

namespace App\Services\GitHub;

use Exception;

class ReadmeParserService
{
    /**
     * Parse README content and extract structured data.
     */
    public function parse(string $readme): array
    {
        if (empty($readme)) {
            return $this->getDefaultStructure();
        }

        return [
            'title' => $this->extractTitle($readme),
            'description' => $this->extractDescription($readme),
            'short_description' => $this->extractShortDescription($readme),
            'features' => $this->extractFeatures($readme),
            'installation' => $this->extractInstallation($readme),
            'requirements' => $this->extractRequirements($readme),
            'technologies' => $this->extractTechnologies($readme),
            'links' => $this->extractLinks($readme),
            'images' => $this->extractImages($readme),
            'license' => $this->extractLicense($readme),
        ];
    }

    /**
     * Extract title from README.
     */
    private function extractTitle(string $readme): ?string
    {
        // Try to find first H1
        if (preg_match('/^#\s+(.+)$/m', $readme, $matches)) {
            return trim($matches[1]);
        }

        // Try to find title in frontmatter
        if (preg_match('/^---\s*\n.*?title:\s*(.+?)\n.*?---/s', $readme, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /**
     * Extract description from README.
     */
    private function extractDescription(string $readme): ?string
    {
        // Get first paragraph after title
        $lines = explode("\n", $readme);
        $description = [];
        $started = false;

        foreach ($lines as $line) {
            $line = trim($line);
            
            // Skip empty lines and headers at start
            if (empty($line) || preg_match('/^#+\s+/', $line)) {
                if ($started) break;
                continue;
            }

            // Skip badges and images
            if (preg_match('/^!\[|^<img|^\[!\[/', $line)) {
                continue;
            }

            // Start collecting description
            if (!empty($line) && !preg_match('/^[-*+]\s+|^\d+\.\s+/', $line)) {
                $started = true;
                $description[] = $line;
                
                // Stop after first paragraph (2-3 sentences)
                if (count($description) >= 3) {
                    break;
                }
            }
        }

        $desc = implode(' ', $description);
        return !empty($desc) ? $desc : null;
    }

    /**
     * Extract short description (first sentence or 150 chars).
     */
    private function extractShortDescription(string $readme): ?string
    {
        $description = $this->extractDescription($readme);
        if (!$description) {
            return null;
        }

        // Get first sentence
        if (preg_match('/^([^.!?]+[.!?])/', $description, $matches)) {
            return trim($matches[1]);
        }

        // Or first 150 characters
        return mb_substr($description, 0, 150);
    }

    /**
     * Extract features from README.
     */
    private function extractFeatures(string $readme): array
    {
        $features = [];

        // Look for Features section
        if (preg_match('/##\s+Features?\s*\n(.*?)(?=\n##|\Z)/is', $readme, $matches)) {
            $content = $matches[1];
            
            // Extract list items
            if (preg_match_all('/^[-*+]\s+(.+)$/m', $content, $matches)) {
                $features = array_map('trim', $matches[1]);
            }
        }

        // Also check for bullet points in general
        if (empty($features)) {
            if (preg_match_all('/^[-*+]\s+(.+)$/m', $readme, $matches)) {
                $features = array_slice(array_map('trim', $matches[1]), 0, 10);
            }
        }

        return array_filter($features, fn($f) => mb_strlen($f) > 10 && mb_strlen($f) < 200);
    }

    /**
     * Extract installation guide.
     */
    private function extractInstallation(string $readme): ?string
    {
        // Look for Installation section
        if (preg_match('/##\s+Install(?:ation)?\s*\n(.*?)(?=\n##|\Z)/is', $readme, $matches)) {
            $content = trim($matches[1]);
            
            // Extract code blocks
            if (preg_match_all('/```[\s\S]*?```/', $content, $codeMatches)) {
                return implode("\n\n", $codeMatches[0]);
            }
            
            return $content;
        }

        return null;
    }

    /**
     * Extract requirements.
     */
    private function extractRequirements(string $readme): ?string
    {
        // Look for Requirements/Prerequisites section
        if (preg_match('/##\s+(?:Requirements?|Prerequisites?|Dependencies?)\s*\n(.*?)(?=\n##|\Z)/is', $readme, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /**
     * Extract technologies mentioned in README.
     */
    private function extractTechnologies(string $readme): array
    {
        $technologies = [];
        
        // Common tech keywords
        $techKeywords = [
            'Laravel', 'Vue', 'React', 'Angular', 'Node.js', 'Python', 'PHP', 'JavaScript',
            'TypeScript', 'Java', 'Go', 'Rust', 'Swift', 'Kotlin', 'Django', 'Flask',
            'Express', 'Next.js', 'Nuxt', 'Svelte', 'Tailwind', 'Bootstrap', 'MySQL',
            'PostgreSQL', 'MongoDB', 'Redis', 'Docker', 'Kubernetes', 'AWS', 'Azure',
        ];

        foreach ($techKeywords as $tech) {
            if (stripos($readme, $tech) !== false) {
                $technologies[] = $tech;
            }
        }

        return array_unique($technologies);
    }

    /**
     * Extract links from README.
     */
    private function extractLinks(string $readme): array
    {
        $links = [];

        // Extract markdown links
        if (preg_match_all('/\[([^\]]+)\]\(([^)]+)\)/', $readme, $matches)) {
            foreach ($matches[2] as $index => $url) {
                $text = $matches[1][$index];
                $url = trim($url);
                
                // Filter out relative links and anchors
                if (filter_var($url, FILTER_VALIDATE_URL)) {
                    $links[] = [
                        'text' => $text,
                        'url' => $url,
                    ];
                }
            }
        }

        // Extract raw URLs
        if (preg_match_all('/https?:\/\/[^\s\)]+/', $readme, $matches)) {
            foreach ($matches[0] as $url) {
                $url = rtrim($url, '.,;!?)');
                if (filter_var($url, FILTER_VALIDATE_URL)) {
                    $links[] = [
                        'text' => parse_url($url, PHP_URL_HOST),
                        'url' => $url,
                    ];
                }
            }
        }

        return $links;
    }

    /**
     * Extract images from README.
     */
    private function extractImages(string $readme): array
    {
        $images = [];

        // Extract markdown images
        if (preg_match_all('/!\[([^\]]*)\]\(([^)]+)\)/', $readme, $matches)) {
            foreach ($matches[2] as $url) {
                $url = trim($url);
                if (filter_var($url, FILTER_VALIDATE_URL)) {
                    $images[] = $url;
                }
            }
        }

        // Extract HTML img tags
        if (preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/', $readme, $matches)) {
            foreach ($matches[1] as $url) {
                if (filter_var($url, FILTER_VALIDATE_URL)) {
                    $images[] = $url;
                }
            }
        }

        return array_unique($images);
    }

    /**
     * Extract license information.
     */
    private function extractLicense(string $readme): ?string
    {
        // Look for License section
        if (preg_match('/##\s+License\s*\n(.*?)(?=\n##|\Z)/is', $readme, $matches)) {
            return trim($matches[1]);
        }

        // Look for license badges
        if (preg_match('/license[:\-]?\s*([^\s\)]+)/i', $readme, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /**
     * Get default structure when README is empty.
     */
    private function getDefaultStructure(): array
    {
        return [
            'title' => null,
            'description' => null,
            'short_description' => null,
            'features' => [],
            'installation' => null,
            'requirements' => null,
            'technologies' => [],
            'links' => [],
            'images' => [],
            'license' => null,
        ];
    }
}

