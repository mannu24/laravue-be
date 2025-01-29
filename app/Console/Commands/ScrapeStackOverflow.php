<?php

namespace App\Console\Commands;

use App\Models\Answer;
use App\Models\Question;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ScrapeStackOverflow extends Command
{
    protected $signature = 'scrape:stackoverflow';
    protected $description = 'Scrape Stack Overflow for Laravel and Vue.js questions and store them in the database';

    public function handle()
    {
        $client = new Client();
        $tags = [
            'laravel',
            'vue.js',
            'laravel-5',
        ];

        $url = "https://api.stackexchange.com/2.3/questions?order=desc&sort=activity&tagged=" . implode(';', $tags) . "&site=stackoverflow&filter=withbody";

        $response = $client->get($url);
        $data = json_decode($response->getBody(), true);

        if (isset($data['items'])) {
            foreach ($data['items'] as $item) {
                $question = Question::updateOrCreate(
                    ['source_question_id' => $item['question_id']],
                    [
                        'user_id' => null,
                        'title' => $item['title'],
                        'slug' => Str::slug($item['title']),
                        'content' => strip_tags($item['body']),
                        'content_html' => $item['body'],
                        'is_solved' => $item['is_answered'],
                        'score' => $item['score'],
                        'view_count' => $item['view_count'],
                        'last_activity_date' => date('Y-m-d H:i:s', $item['last_activity_date']),
                        'source' => 'stackoverflow',
                        'source_url' => $item['link'],
                        'source_question_id' => $item['question_id'],
                        'is_closed' => isset($item['closed_reason']),
                        'closed_reason' => $item['closed_reason'] ?? null,
                    ]
                );

                // Fetch and store answers for this question
                $this->fetchAnswers($question->id, $item['question_id']);
            }
        }

        $this->info('Scraping completed successfully!');
    }

    private function fetchAnswers($localQuestionId, $stackoverflowQuestionId)
    {
        $client = new Client();
        $url = "https://api.stackexchange.com/2.3/questions/{$stackoverflowQuestionId}/answers?order=desc&sort=activity&site=stackoverflow&filter=withbody";

        $response = $client->get($url);
        $data = json_decode($response->getBody(), true);

        if (isset($data['items'])) {
            foreach ($data['items'] as $item) {
                Answer::updateOrCreate(
                    ['source_answer_id' => $item['answer_id']],
                    [
                        'question_id' => $localQuestionId,
                        'user_id' => null, // No user mapping available
                        'content' => strip_tags($item['body']),
                        'content_html' => $item['body'],
                        'is_accepted' => $item['is_accepted'],
                        'score' => $item['score'],
                        'comment_count' => 0, // No comment count from API
                        'last_activity_date' => date('Y-m-d H:i:s', $item['last_activity_date']),
                        'source' => 'stackoverflow',
                        'source_url' => "https://stackoverflow.com/a/" . $item['answer_id'],
                        'source_answer_id' => $item['answer_id'],
                    ]
                );
            }
        }
    }
}
