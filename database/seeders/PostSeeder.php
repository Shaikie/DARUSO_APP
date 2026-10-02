<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostReaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    private const IMAGE_BASE = 'https://images.unsplash.com/';

    public function run(): void
    {
        $authors = User::whereIn('email', [
            'secretary@daruso.local',
            'ministry@daruso.local',
            'committee@daruso.local',
            'university@daruso.local',
        ])->get()->keyBy('email');

        $students = User::whereHas('studentProfile')
            ->where('email', '!=', 'student@daruso.local')
            ->get();

        $posts = [
            [
                'author' => 'secretary@daruso.local',
                'title' => 'Welcome to the DARUSO Community',
                'excerpt' => 'A new digital space for updates, conversations, ideas and a stronger connection between students and leadership.',
                'content' => "DARUSO is building a more connected student community.\n\nUse this space to discover official stories, community updates and conversations from across the university. You can like posts, react, comment and share useful information with other students.\n\nFor personal matters, notifications and services, use your dashboard and the tools in your workspace.",
                'image' => self::IMAGE_BASE.'1523240795612-9a054b0db644?auto=format&fit=crop&w=1400&q=85',
                'days_ago' => 1,
            ],
            [
                'author' => 'ministry@daruso.local',
                'title' => 'Student Voice: Turning Concerns into Action',
                'excerpt' => 'A look at how student feedback can move from a conversation into a documented issue and an accountable response.',
                'content' => "Good representation starts with listening.\n\nDARUSO committees and ministries are using structured complaints, meetings and targeted notifications to keep issues visible from submission to resolution.\n\nIf something affects your student experience, use the appropriate service rather than posting private information publicly.",
                'image' => self::IMAGE_BASE.'1511632765486-a01980e01a18?auto=format&fit=crop&w=1400&q=85',
                'days_ago' => 2,
            ],
            [
                'author' => 'committee@daruso.local',
                'title' => 'Building a Culture of Collaboration',
                'excerpt' => 'Community work is stronger when students, representatives and committees have a shared place to exchange ideas.',
                'content' => "Leadership is not only about announcements. It is also about collaboration.\n\nThis community feed gives DARUSO a place to publish stories, explain initiatives and invite constructive discussion without mixing those conversations with private student notifications.",
                'image' => self::IMAGE_BASE.'1529156069898-49953e39b3ac?auto=format&fit=crop&w=1400&q=85',
                'days_ago' => 4,
            ],
            [
                'author' => 'university@daruso.local',
                'title' => 'Making Campus Information Easier to Find',
                'excerpt' => 'The goal is simple: important information should be easier to discover, while personal information stays personal.',
                'content' => "A modern student platform needs more than a dashboard full of numbers.\n\nCommunity posts are for shared stories and information. Announcements are for official notices. Notifications are for messages targeted to you. Complaints are for issues that need a response.\n\nKeeping those experiences distinct makes the platform easier to use and easier to trust.",
                'image' => self::IMAGE_BASE.'1541339907198-e08756dedf3f?auto=format&fit=crop&w=1400&q=85',
                'days_ago' => 6,
            ],
            [
                'author' => 'secretary@daruso.local',
                'title' => 'Small Improvements, Better Student Experience',
                'excerpt' => 'We are continuing to improve how students discover events, documents, meetings and leadership updates.',
                'content' => "The community experience is designed to grow with student needs.\n\nExpect more useful stories, better discussions and clearer links to services. The objective is not to add noise; it is to make the information that matters easier to reach.",
                'image' => self::IMAGE_BASE.'1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1400&q=85',
                'days_ago' => 9,
            ],
        ];

        foreach ($posts as $data) {
            $author = $authors->get($data['author']);

            if ($author === null) {
                continue;
            }

            $post = Post::updateOrCreate(
                ['title' => $data['title']],
                [
                    'author_id' => $author->getKey(),
                    'excerpt' => $data['excerpt'],
                    'content' => $data['content'],
                    'cover_image_url' => $data['image'],
                    'is_published' => true,
                    'published_at' => now()->subDays($data['days_ago']),
                ],
            );

            foreach ($students->take(7) as $index => $student) {
                if ($index % 2 === 0 || $post->title === 'Welcome to the DARUSO Community') {
                    $post->likes()->syncWithoutDetaching([$student->getKey()]);
                }

                if ($index < 4) {
                    PostReaction::updateOrCreate(
                        ['post_id' => $post->getKey(), 'user_id' => $student->getKey()],
                        ['type' => ['love', 'celebrate', 'support', 'insightful'][$index]],
                    );
                }
            }
        }
    }
}
