<?php

class GeminiEngine {
    private $apiKey;
    private $model = 'gemini-flash-latest';

    // Each keyword only belongs to ONE interest pillar
    // Matched against event TITLE (30pts) and DESCRIPTION (10pts) only
    private $keywordMap = [
        'AI & Data Intelligence'     => [
            'ai', 'artificial intelligence', 'machine learning', 'data science',
            'data analysis', 'data visualization', 'generative ai', 'genai',
            'python', 'sql', 'analytics', 'deep learning', 'neural network',
            'nlp', 'big data', 'llm', 'gpt', 'chatbot', 'data analytics',
            'blockchain', 'data science overview',
        ],
        'Cybersecurity & Systems'    => [
            'cyber', 'cybersecurity', 'ethical hacking', 'hacking',
            'network security', 'web security', 'owasp', 'penetration testing',
            'incident response', 'cloud security', 'cyber threat', 'vulnerability',
            'ctf', 'forensics', 'firewall', 'cyber law', 'cyber defense',
        ],
        'UX/UI & Creative Media'     => [
            'ux', 'ui', 'user experience', 'user interface', 'usability testing',
            'usability', 'wireframe', 'figma', 'prototype', 'user research',
            'digital art', 'product design', 'ui design', 'branding',
            'mobile ux', 'ui prototyping', 'ux case', 'game ui',
        ],
        'Game Dev & Virtual Reality' => [
            'game development', 'game dev', 'gaming', 'unity', 'unreal engine',
            '3d asset', 'virtual reality', 'vr', 'ar', 'augmented reality',
            'metaverse', 'game demo', 'game physics', 'game monetization',
            'level design', 'intro to game',
        ],
        'Startups & Innovation'      => [
            'startup', 'entrepreneurship', 'entrepreneur',
            'pitch deck', 'pitch', 'startup funding', 'innovation challenge',
            'idea validation', 'business model', 'venture', 'mvp',
            'hackathon', 'innovation meetup', 'startup pitch', 'startup mentorship',
            'green technology',
        ],
        'Career & Leadership'        => [
            'career', 'careers', 'leadership', 'mentorship', 'mentor',
            'interview', 'resume', 'cv', 'linkedin', 'networking',
            'personal branding', 'internship', 'job readiness', 'job',
            'negotiation', 'communication skills', 'soft skills',
            'portfolio building', 'time management', 'public relations',
            'professional development', 'tech career', 'cloud careers',
        ],
    ];

    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
    }

    /**
     * Local scoring ALWAYS selects events (accurate, no hallucination).
     * Gemini ONLY rewrites the reason text if available.
     */
    public function getRecommendations(array $interests, array $events): array {
        if (empty($interests) || empty($events)) return [];

        // Step 1: local scoring — each event assigned to ONE primary interest
        $selected = $this->localKeywordScore($interests, $events);
        if (empty($selected)) return [];

        // Step 2: optionally improve reason sentences via Gemini
        if (!empty($this->apiKey)) {
            $selected = $this->enrichReasonText($selected, $interests);
        }

        return array_map(fn($i) => ['id' => $i['id'], 'reason' => $i['reason']], $selected);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // LOCAL SCORING  — one event, one interest, title + description only
    // ─────────────────────────────────────────────────────────────────────────

    private function localKeywordScore(array $interests, array $events): array {
        $allPillars = array_keys($this->keywordMap);
        $results    = [];

        foreach ($events as $event) {
            $titleLower = strtolower($event['title']       ?? '');
            $descLower  = strtolower($event['description'] ?? '');

            // Score against ALL pillars
            $scores = [];
            foreach ($allPillars as $pillar) {
                $score = 0;
                foreach ($this->keywordMap[$pillar] as $kw) {
                    $kw = strtolower($kw);
                    if (strpos($titleLower, $kw) !== false)    $score += 30;
                    elseif (strpos($descLower, $kw) !== false) $score += 10;
                }
                $scores[$pillar] = $score;
            }

            // Primary interest = pillar with highest score
            // Ties broken by order in $keywordMap (first one wins)
            $primaryInterest = '';
            $primaryScore    = 0;
            foreach ($allPillars as $pillar) {
                if ($scores[$pillar] > $primaryScore) {
                    $primaryScore    = $scores[$pillar];
                    $primaryInterest = $pillar;
                }
            }

            // Include only if: has a keyword hit AND primary interest was selected by student
            if ($primaryScore >= 30 && in_array($primaryInterest, $interests, true)) {
                $results[] = [
                    'id'         => $event['id'],
                    'reason'     => "Matches your {$primaryInterest} interest.",
                    '_score'     => $primaryScore,
                    '_interest'  => $primaryInterest,
                ];
            }
        }

        usort($results, fn($a, $b) => $b['_score'] - $a['_score']);
        return $results;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GEMINI — rewrites reason text only, never changes event selection
    // ─────────────────────────────────────────────────────────────────────────

    private function enrichReasonText(array $selected, array $interests): array {
        if (empty($selected)) return $selected;

        $interestList = implode(', ', $interests);
        $payload = array_map(fn($i) => [
            'id'       => $i['id'],
            'interest' => $i['_interest'],
        ], $selected);

        $prompt = "Student's selected interests: [{$interestList}]\n\n"
                . "For each item, write ONE concise sentence (max 10 words) explaining "
                . "why the event matches the student's interest in the 'interest' field.\n"
                . "Items: " . json_encode($payload, JSON_UNESCAPED_UNICODE) . "\n\n"
                . "Output ONLY a JSON array: [{\"id\": int, \"reason\": \"...\"}]. No markdown.";

        $url  = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";
        $body = [
            'contents'         => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => ['temperature' => 0, 'maxOutputTokens' => 1024],
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($body),
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) return $selected;

        $result  = json_decode($response, true);
        $aiText  = trim(preg_replace('/```(?:json)?|```/i', '', $result['candidates'][0]['content']['parts'][0]['text'] ?? ''));
        $parsed  = json_decode($aiText, true);
        
        if (!is_array($parsed)) return $selected;

        $reasonMap = [];
        foreach ($parsed as $p) {
            if (!empty($p['id']) && !empty($p['reason'])) {
                $reasonMap[(int)$p['id']] = trim($p['reason']);
            }
        }

        foreach ($selected as &$item) {
            if (isset($reasonMap[$item['id']])) {
                $item['reason'] = $reasonMap[$item['id']];
            }
        }

        return $selected;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CHATBOT — answers questions using event context
    // ─────────────────────────────────────────────────────────────────────────

    public function chat(string $userMessage, array $eventsContext): string {
        if (empty($this->apiKey)) return "⚠️ Gemini API key is not configured.";

        $catalog = !empty($eventsContext) ? implode("\n", $eventsContext) : "No upcoming events.";

        $systemPrompt = "You are IU Assistant, the official chatbot for IU EVENTS (Islamic University).\n"
        . "Use ONLY the event data below to answer event questions.\n\n"
        . "UPCOMING EVENTS:\n{$catalog}\n\n"
        . "RULES: Be concise. Use markdown. For registration direct to Student Portal. "
        . "If info is not in the data say you don't know.\n\n"
        . "User: {$userMessage}";

        $url  = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";
        $body = [
            'contents'         => [['parts' => [['text' => $systemPrompt]]]],
            'generationConfig' => ['temperature' => 0.3, 'maxOutputTokens' => 512],
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($body),
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            $result = json_decode($response, true);
            return $result['candidates'][0]['content']['parts'][0]['text'] ?? "Sorry, I couldn't generate a response right now.";
        }
        
        return "Error: AI connection failed. HTTP {$httpCode}";
    }
}
