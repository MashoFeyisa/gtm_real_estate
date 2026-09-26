<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PageController extends Controller
{
    public function show(string $slug): View
    {
        $pages = [
            'buy' => ['title' => 'Buy', 'intro' => 'Explore homes and investment opportunities tailored to your budget and goals.', 'page' => 'buy'],
            'rent' => ['title' => 'Rent', 'intro' => 'Discover flexible rental homes and premium living spaces in prime locations.', 'page' => 'rent'],
            'land' => ['title' => 'Land', 'intro' => 'Find land for residential, commercial, and future development investment.', 'page' => 'land'],
            'commercial' => ['title' => 'Commercial', 'intro' => 'Invest in strategic commercial spaces built for business growth.', 'page' => 'commercial'],
            'projects' => ['title' => 'Projects / Developments', 'intro' => 'Explore our flagship developments and upcoming projects across key districts.', 'page' => 'projects'],
            'project-details' => ['title' => 'Project Details', 'intro' => 'See the details behind each development, location, and investment outlook.', 'page' => 'project-details'],
            'services' => ['title' => 'Services', 'intro' => 'From buying and leasing to project management, we help you move with confidence.', 'page' => 'services'],
            'about' => ['title' => 'About GTP', 'intro' => 'GTP Real Estate is shaping trusted property experiences in Ethiopia with expertise, integrity, and local insight.', 'page' => 'about'],
            'leadership' => ['title' => 'Leadership / Team', 'intro' => 'Meet the experienced leaders and advisors guiding our market reputation.', 'page' => 'leadership'],
            'agents' => ['title' => 'Agents', 'intro' => 'Connect with property consultants who understand your local goals and investment priorities.', 'page' => 'agents'],
            'agent-details' => ['title' => 'Agent Details', 'intro' => 'Learn more about the agent supporting your search and negotiation journey.', 'page' => 'agent-details'],
            'news' => ['title' => 'News', 'intro' => 'Read real estate updates, market insights, and local development trends.', 'page' => 'news'],
            'blog' => ['title' => 'Blog', 'intro' => 'Insights, home tips, and notes from the property market.', 'page' => 'blog'],
            'article-details' => ['title' => 'Article Details', 'intro' => 'A detailed article from our market experts and local analysts.', 'page' => 'article-details'],
            'careers' => ['title' => 'Careers', 'intro' => 'Build your future with a team passionate about real estate and client care.', 'page' => 'careers'],
            'job-details' => ['title' => 'Job Details', 'intro' => 'Explore role responsibilities, requirements, and how you can join our team.', 'page' => 'job-details'],
            'contact' => ['title' => 'Contact', 'intro' => 'Ask for a consultation, schedule a visit, or speak with an advisor today.', 'page' => 'contact'],
            'search-results' => ['title' => 'Search Results', 'intro' => 'Explore listings that match your location, budget, and lifestyle priorities.', 'page' => 'search-results'],
            'privacy-policy' => ['title' => 'Privacy Policy', 'intro' => 'Your information is handled with care and transparency across our customer journey.', 'page' => 'privacy-policy'],
            'terms' => ['title' => 'Terms', 'intro' => 'Read the terms governing our services, transactions, and customer interactions.', 'page' => 'terms'],
        ];

        $page = $pages[$slug] ?? ['title' => ucfirst(str_replace('-', ' ', $slug)), 'intro' => 'Explore our page content.', 'page' => $slug];

        return view('public.page', $page);
    }
}
